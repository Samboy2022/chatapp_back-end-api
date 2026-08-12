<?php

namespace App\Http\Controllers\Api;

use App\Helpers\PhoneNumber;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Passwordless sign-in and password recovery, over SMS or email.
 *
 * All three flows share one shape: request a code, verify it, act on the
 * result. The acting-on-it differs — login mints a token, reset hands back a
 * one-shot token to set a new password — but the code handling is common and
 * lives in OtpService.
 *
 * Note on account enumeration: these endpoints say plainly when no account
 * matches. That's deliberate. The app already exposes "is this contact on the
 * network?" when adding contacts, so hiding it here would buy no privacy while
 * making a mistyped number look like a silently lost SMS — and every failed
 * send costs real SMS credit.
 */
class OtpAuthController extends Controller
{
    public function __construct(
        private OtpService $otp,
    ) {
    }

    // ── OTP login ────────────────────────────────────────────────────────

    /**
     * Send a login code to a phone number or email address.
     */
    public function requestLoginCode(Request $request): JsonResponse
    {
        if (!$this->otp->loginEnabled()) {
            return $this->fail('OTP login is currently disabled.', 403);
        }

        $validator = Validator::make($request->all(), [
            'login' => 'required_without:phone_number|string',
            'phone_number' => 'required_without:login|string',
            'channel' => 'sometimes|in:sms,email',
        ]);

        if ($validator->fails()) {
            return $this->validationFail($validator);
        }

        $input = (string) ($request->input('login') ?? $request->input('phone_number'));
        $user = User::findByLogin($input);

        if (!$user) {
            return $this->fail('No account found with those details. Please check and try again, or create an account.', 404);
        }

        $channel = $this->resolveChannel($request->input('channel'), $input, $user);

        if ($channel === 'email' && blank($user->email)) {
            return $this->fail('This account has no email address on file. Please use SMS instead.', 422);
        }

        $identifier = $this->identifierFor($user, $channel);

        $result = $this->otp->send(
            identifier: $identifier,
            channel: $channel,
            purpose: 'login',
            userName: $user->name,
            ipAddress: $request->ip(),
        );

        return $this->fromOtpResult($result, [
            'channel' => $channel,
            // Echoed back so the app can show "code sent to +234 702 ••• 1356"
            // and put the same value on the verify call.
            'identifier' => $identifier,
            'masked_identifier' => $this->mask($identifier, $channel),
        ]);
    }

    /**
     * Verify a login code and issue an API token.
     */
    public function verifyLoginCode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required_without:phone_number|string',
            'phone_number' => 'required_without:login|string',
            'code' => 'required|string',
            'channel' => 'sometimes|in:sms,email',
        ]);

        if ($validator->fails()) {
            return $this->validationFail($validator);
        }

        $input = (string) ($request->input('login') ?? $request->input('phone_number'));
        $user = User::findByLogin($input);

        if (!$user) {
            return $this->fail('No account found with those details.', 404);
        }

        $channel = $this->resolveChannel($request->input('channel'), $input, $user);
        $identifier = $this->identifierFor($user, $channel);

        $result = $this->otp->verify($identifier, (string) $request->input('code'), 'login', $channel);

        if (!$result['success']) {
            return $this->fail($result['message'], 422, array_filter([
                'attempts_left' => $result['attempts_left'] ?? null,
            ], fn ($v) => $v !== null));
        }

        // Signing in through a channel proves the user controls it, so record
        // that — it saves them a separate verification step later.
        $this->markChannelVerified($user, $channel);

        $user->updateOnlineStatus(true);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user->fresh(),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    // ── Password reset ───────────────────────────────────────────────────

    /**
     * Start a password reset: send a code to the account's email or phone.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        if (!$this->otp->passwordResetEnabled()) {
            return $this->fail('Password reset is currently disabled.', 403);
        }

        $validator = Validator::make($request->all(), [
            'login' => 'required_without:email|string',
            'email' => 'required_without:login|string',
            'channel' => 'sometimes|in:sms,email',
        ]);

        if ($validator->fails()) {
            return $this->validationFail($validator);
        }

        $input = (string) ($request->input('login') ?? $request->input('email'));
        $user = User::findByLogin($input);

        if (!$user) {
            return $this->fail('No account found with those details. Please check and try again.', 404);
        }

        $channel = $this->resolveChannel($request->input('channel'), $input, $user);
        $allowed = $this->otp->allowedResetChannels();

        if (!in_array($channel, $allowed, true)) {
            return $this->fail(
                'Password reset by ' . ($channel === 'email' ? 'email' : 'SMS') . ' is not available. Allowed: ' . implode(', ', $allowed) . '.',
                422
            );
        }

        if ($channel === 'email' && blank($user->email)) {
            return $this->fail('This account has no email address on file. Please reset by SMS instead.', 422);
        }

        $identifier = $this->identifierFor($user, $channel);

        $result = $this->otp->send(
            identifier: $identifier,
            channel: $channel,
            purpose: 'password_reset',
            userName: $user->name,
            ipAddress: $request->ip(),
        );

        return $this->fromOtpResult($result, [
            'channel' => $channel,
            'identifier' => $identifier,
            'masked_identifier' => $this->mask($identifier, $channel),
        ]);
    }

    /**
     * Check the reset code and hand back a one-shot token for the next step.
     *
     * Split from the reset itself so the app can validate the code the moment
     * the user finishes typing it, before asking for a new password.
     */
    public function verifyPasswordResetCode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required_without:email|string',
            'email' => 'required_without:login|string',
            'code' => 'required|string',
            'channel' => 'sometimes|in:sms,email',
        ]);

        if ($validator->fails()) {
            return $this->validationFail($validator);
        }

        $input = (string) ($request->input('login') ?? $request->input('email'));
        $user = User::findByLogin($input);

        if (!$user) {
            return $this->fail('No account found with those details.', 404);
        }

        $channel = $this->resolveChannel($request->input('channel'), $input, $user);
        $identifier = $this->identifierFor($user, $channel);

        $result = $this->otp->verify($identifier, (string) $request->input('code'), 'password_reset', $channel);

        if (!$result['success']) {
            return $this->fail($result['message'], 422, array_filter([
                'attempts_left' => $result['attempts_left'] ?? null,
            ], fn ($v) => $v !== null));
        }

        return response()->json([
            'success' => true,
            'message' => 'Code verified. You can now set a new password.',
            'data' => [
                'verification_token' => $result['verification_token'],
                // How long they have to actually type the new password.
                'expires_in' => 15 * 60,
            ],
        ]);
    }

    /**
     * Set the new password, given a token from verifyPasswordResetCode.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'verification_token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->validationFail($validator);
        }

        $identifier = $this->otp->consumeVerificationToken(
            (string) $request->input('verification_token'),
            'password_reset'
        );

        if ($identifier === null) {
            return $this->fail('This reset session has expired. Please start again.', 422);
        }

        $user = User::findByLogin($identifier);

        if (!$user) {
            return $this->fail('Account no longer exists.', 404);
        }

        $user->forceFill(['password' => Hash::make($request->input('password'))])->save();

        // A password reset is the standard response to "someone else is in my
        // account", so every existing session has to die with it.
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Your password has been reset. Please sign in with your new password.',
        ]);
    }

    // ── Email verification ───────────────────────────────────────────────

    /**
     * Send a verification code to the signed-in user's email address.
     */
    public function requestEmailVerification(Request $request): JsonResponse
    {
        $user = $request->user();

        if (blank($user->email)) {
            return $this->fail('Add an email address to your profile first.', 422);
        }

        if ($user->email_verified_at) {
            return response()->json([
                'success' => true,
                'message' => 'Your email address is already verified.',
                'data' => ['already_verified' => true],
            ]);
        }

        $result = $this->otp->send(
            identifier: $user->email,
            channel: 'email',
            purpose: 'email_verification',
            userName: $user->name,
            ipAddress: $request->ip(),
        );

        return $this->fromOtpResult($result, [
            'channel' => 'email',
            'masked_identifier' => $this->mask($user->email, 'email'),
        ]);
    }

    /**
     * Confirm the signed-in user's email address with the code they received.
     */
    public function confirmEmailVerification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationFail($validator);
        }

        $user = $request->user();

        $result = $this->otp->verify($user->email, (string) $request->input('code'), 'email_verification', 'email');

        if (!$result['success']) {
            return $this->fail($result['message'], 422, array_filter([
                'attempts_left' => $result['attempts_left'] ?? null,
            ], fn ($v) => $v !== null));
        }

        $user->forceFill(['email_verified_at' => now()])->save();

        return response()->json([
            'success' => true,
            'message' => 'Email address verified.',
            'data' => ['user' => $user->fresh()],
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    /**
     * The canonical address to send a code to, and to key the code on.
     *
     * Always derived from the stored account rather than from what the user
     * typed, and always normalised — an account still holding a legacy
     * `07026591356` must key its codes on `+2347026591356`, or requesting and
     * verifying would look at two different identifiers.
     */
    private function identifierFor(User $user, string $channel): string
    {
        if ($channel === 'email') {
            return strtolower(trim((string) $user->email));
        }

        return PhoneNumber::normalize($user->phone_number) ?? (string) $user->phone_number;
    }

    /**
     * Decide which channel to use.
     *
     * An explicit request wins. Otherwise we follow what the user typed — an
     * email address implies email — falling back to the configured default.
     */
    private function resolveChannel(?string $requested, string $input, User $user): string
    {
        if (in_array($requested, ['sms', 'email'], true)) {
            return $requested;
        }

        if (str_contains($input, '@')) {
            return 'email';
        }

        if (PhoneNumber::looksLikePhone($input)) {
            return 'sms';
        }

        $default = Setting::get('otp_default_channel') ?: 'sms';

        // Don't default to a channel this account can't actually receive on.
        if ($default === 'email' && blank($user->email)) {
            return 'sms';
        }

        return in_array($default, ['sms', 'email'], true) ? $default : 'sms';
    }

    /**
     * Mark the channel the user just proved control of.
     */
    private function markChannelVerified(User $user, string $channel): void
    {
        if ($channel === 'email' && !$user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        if ($channel === 'sms' && !$user->phone_verified_at) {
            $user->forceFill(['phone_verified_at' => now()])->save();
        }
    }

    /**
     * Partly hide an identifier for display: s•••@gmail.com, +234 702 ••• 1356.
     *
     * Enough for the user to recognise their own, not enough to be useful to
     * someone reading over their shoulder.
     */
    private function mask(string $identifier, string $channel): string
    {
        if ($channel === 'email') {
            [$local, $domain] = array_pad(explode('@', $identifier, 2), 2, '');

            $visible = mb_substr($local, 0, 1);
            $masked = $visible . str_repeat('•', max(3, mb_strlen($local) - 1));

            return $domain === '' ? $masked : "{$masked}@{$domain}";
        }

        $digits = ltrim($identifier, '+');

        if (strlen($digits) <= 6) {
            return $identifier;
        }

        return '+' . substr($digits, 0, 6) . str_repeat('•', 3) . substr($digits, -4);
    }

    /** Turn an OtpService result into an HTTP response. */
    private function fromOtpResult(array $result, array $extra = []): JsonResponse
    {
        if (!$result['success']) {
            // A cooldown is a "slow down", not a failure the user caused.
            $status = isset($result['retry_after']) ? 429 : 422;

            return $this->fail($result['message'], $status, array_filter([
                'retry_after' => $result['retry_after'] ?? null,
            ], fn ($v) => $v !== null));
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => array_filter(array_merge($extra, [
                'expires_in' => $result['expires_in'] ?? null,
                'debug_code' => $result['debug_code'] ?? null,
            ]), fn ($v) => $v !== null),
        ]);
    }

    private function fail(string $message, int $status = 422, array $data = []): JsonResponse
    {
        return response()->json(array_filter([
            'success' => false,
            'message' => $message,
            'data' => $data ?: null,
        ], fn ($v) => $v !== null), $status);
    }

    private function validationFail($validator): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors(),
        ], 422);
    }
}
