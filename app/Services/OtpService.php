<?php

namespace App\Services;

use App\Helpers\PhoneNumber;
use App\Mail\OtpCodeMail;
use App\Models\OtpCode;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Issues and checks the short-lived codes behind OTP login, password reset and
 * email verification.
 *
 * We own the code end to end — generation, hashed storage, expiry, attempt
 * limiting — and treat SMS/email purely as delivery. That gives one code path
 * for both channels and keeps verification working even if a gateway is down.
 */
class OtpService
{
    public function __construct(
        private TermiiService $termii,
    ) {
    }

    // ── Policy, all admin-configurable ───────────────────────────────────

    public function codeLength(): int
    {
        // Clamped: below 4 is trivially guessable, above 8 is a usability
        // problem on a phone keypad.
        return max(4, min(8, (int) (Setting::get('otp_length') ?: 6)));
    }

    public function expiryMinutes(): int
    {
        return max(1, (int) (Setting::get('otp_expiry_minutes') ?: 10));
    }

    public function maxAttempts(): int
    {
        return max(1, (int) (Setting::get('otp_max_attempts') ?: 5));
    }

    public function resendCooldownSeconds(): int
    {
        return max(0, (int) (Setting::get('otp_resend_cooldown_seconds') ?: 60));
    }

    public function loginEnabled(): bool
    {
        return filter_var(Setting::get('otp_enabled') ?? true, FILTER_VALIDATE_BOOLEAN);
    }

    public function passwordResetEnabled(): bool
    {
        return filter_var(Setting::get('password_reset_enabled') ?? true, FILTER_VALIDATE_BOOLEAN);
    }

    /** Channels an admin has allowed for password reset. */
    public function allowedResetChannels(): array
    {
        $raw = Setting::get('password_reset_channels') ?: 'email,sms';

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    // ── Issuing ──────────────────────────────────────────────────────────

    /**
     * Generate, store and deliver a code.
     *
     * @param  string  $identifier  E.164 phone or lowercased email.
     * @return array{success: bool, message: string, retry_after?: int, expires_in?: int, debug_code?: string}
     */
    public function send(
        string $identifier,
        string $channel,
        string $purpose,
        ?string $userName = null,
        ?string $ipAddress = null
    ): array {
        $identifier = $this->canonical($identifier, $channel);

        if ($identifier === null) {
            return ['success' => false, 'message' => 'That does not look like a valid ' . ($channel === 'email' ? 'email address' : 'phone number') . '.'];
        }

        // Rate limit before doing anything expensive. Protects both our SMS
        // credit and the recipient from being spammed.
        if ($wait = $this->cooldownRemaining($identifier, $purpose)) {
            return [
                'success' => false,
                'message' => "Please wait {$wait} seconds before requesting another code.",
                'retry_after' => $wait,
            ];
        }

        $code = $this->generateCode();
        $expiryMinutes = $this->expiryMinutes();

        // Any older live code for this purpose is retired, so only the newest
        // one can be used — otherwise a user with two SMS in hand gets
        // confusing "invalid code" errors from the older one.
        OtpCode::for($identifier, $purpose)->live()->update(['consumed_at' => now()]);

        $record = OtpCode::create([
            'identifier' => $identifier,
            'channel' => $channel,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes($expiryMinutes),
            'ip_address' => $ipAddress,
        ]);

        $delivery = $channel === 'email'
            ? $this->deliverByEmail($identifier, $code, $purpose, $expiryMinutes, $userName)
            : $this->deliverBySms($identifier, $code, $expiryMinutes);

        if (!$delivery['success']) {
            // Don't leave a live code the user never received — it would burn
            // their cooldown for a message that never arrived.
            $record->consume();

            return $delivery;
        }

        $result = [
            'success' => true,
            'message' => $channel === 'email'
                ? 'We sent a code to your email address.'
                : 'We sent a code to your phone.',
            'expires_in' => $expiryMinutes * 60,
        ];

        // With no real gateway configured the code would be undeliverable, so
        // hand it back directly — this is what makes local dev usable. Guarded
        // to non-production so a misconfigured live server can't leak codes.
        if (($delivery['logged_only'] ?? false) && !app()->environment('production')) {
            $result['debug_code'] = $code;
            $result['message'] .= ' (delivery is in log mode — check the Laravel log)';
        }

        return $result;
    }

    // ── Verifying ────────────────────────────────────────────────────────

    /**
     * Check a code.
     *
     * On success returns a short-lived `verification_token`; the caller (e.g.
     * the password reset endpoint) presents that instead of the code, so the
     * code itself is spent exactly once.
     *
     * @return array{success: bool, message: string, verification_token?: string, attempts_left?: int}
     */
    public function verify(string $identifier, string $code, string $purpose, string $channel = 'sms'): array
    {
        $identifier = $this->canonical($identifier, $channel);

        if ($identifier === null) {
            return ['success' => false, 'message' => 'Invalid request.'];
        }

        $record = OtpCode::for($identifier, $purpose)
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();

        if (!$record) {
            return ['success' => false, 'message' => 'No code was requested. Please request a new one.'];
        }

        if ($record->isExpired()) {
            $record->consume();

            return ['success' => false, 'message' => 'That code has expired. Please request a new one.'];
        }

        if ($record->attempts >= $this->maxAttempts()) {
            $record->consume();

            return ['success' => false, 'message' => 'Too many incorrect attempts. Please request a new code.'];
        }

        $record->increment('attempts');

        if (!Hash::check(trim($code), $record->code_hash)) {
            $attemptsLeft = max(0, $this->maxAttempts() - $record->attempts);

            if ($attemptsLeft === 0) {
                $record->consume();

                return ['success' => false, 'message' => 'Too many incorrect attempts. Please request a new code.'];
            }

            return [
                'success' => false,
                'message' => 'That code is not correct.',
                'attempts_left' => $attemptsLeft,
            ];
        }

        $token = Str::random(64);

        $record->forceFill([
            'verified_at' => now(),
            'verification_token' => $token,
            // Login and email verification finish here, so the code is spent
            // immediately. Password reset still needs one more request, so its
            // row stays live until the new password is actually set.
            'consumed_at' => $purpose === 'password_reset' ? null : now(),
        ])->save();

        return [
            'success' => true,
            'message' => 'Code verified.',
            'verification_token' => $token,
        ];
    }

    /**
     * Trade a verification token for the identifier it proves, then spend it.
     *
     * Returns null when the token is unknown, already spent or stale.
     */
    public function consumeVerificationToken(string $token, string $purpose, int $validForMinutes = 15): ?string
    {
        $record = OtpCode::where('verification_token', $token)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->whereNotNull('verified_at')
            ->latest('id')
            ->first();

        if (!$record) {
            return null;
        }

        // The window to *use* a verified token is separate from the code's own
        // expiry — the user still has to type a new password.
        if ($record->verified_at->addMinutes($validForMinutes)->isPast()) {
            $record->consume();

            return null;
        }

        $identifier = $record->identifier;
        $record->consume();

        return $identifier;
    }

    // ── Internals ────────────────────────────────────────────────────────

    /**
     * Seconds left before another code may be requested, or 0 if free.
     */
    public function cooldownRemaining(string $identifier, string $purpose): int
    {
        $cooldown = $this->resendCooldownSeconds();

        if ($cooldown === 0) {
            return 0;
        }

        $last = OtpCode::for($identifier, $purpose)->latest('id')->first();

        if (!$last) {
            return 0;
        }

        $elapsed = $last->created_at->diffInSeconds(now());

        return $elapsed >= $cooldown ? 0 : (int) ceil($cooldown - $elapsed);
    }

    /**
     * A numeric code with a uniform distribution.
     *
     * random_int is the cryptographically secure generator — rand()/mt_rand()
     * are predictable from a few observed codes, which for an auth code is a
     * real account-takeover path.
     */
    private function generateCode(): string
    {
        $length = $this->codeLength();
        $min = (int) str_pad('1', $length, '0'); // 100000 for length 6
        $max = (int) str_repeat('9', $length);   // 999999 for length 6

        return (string) random_int($min, $max);
    }

    /** Normalise an identifier to the exact form we store and look up by. */
    private function canonical(string $identifier, string $channel): ?string
    {
        if ($channel === 'email') {
            $email = strtolower(trim($identifier));

            return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
        }

        return PhoneNumber::normalize($identifier);
    }

    private function deliverBySms(string $phone, string $code, int $expiryMinutes): array
    {
        $message = $this->renderSmsBody($code, $expiryMinutes);

        // "log" provider is the escape hatch for dev and for staging boxes
        // without SMS credit.
        if ((Setting::get('sms_provider') ?: 'termii') === 'log' || !$this->termii->isConfigured()) {
            // Deliberately logged at error level. Info/warning are invisible
            // under the usual production LOG_LEVEL=error, which would make
            // this mode silently useless — and on a real server "codes are
            // not actually being sent" genuinely is an alarm worth raising.
            Log::error('OTP LOG DELIVERY — no SMS was actually sent', [
                'to' => $phone,
                'code' => $code,
                'message' => $message,
            ]);

            return ['success' => true, 'message' => 'Logged', 'logged_only' => true];
        }

        $result = $this->termii->send($phone, $message);

        return [
            'success' => $result['success'],
            'message' => $result['success']
                ? 'Sent'
                : 'We could not send the SMS. ' . ($result['message'] ?? ''),
        ];
    }

    private function deliverByEmail(string $email, string $code, string $purpose, int $expiryMinutes, ?string $userName): array
    {
        if (!MailConfigService::enabled()) {
            return ['success' => false, 'message' => 'Email delivery is currently disabled.'];
        }

        MailConfigService::apply();

        try {
            Mail::to($email)->send(new OtpCodeMail($code, $purpose, $expiryMinutes, $userName));

            return [
                'success' => true,
                'message' => 'Sent',
                // In log-mailer mode nothing actually leaves the server.
                'logged_only' => config('mail.default') === 'log',
            ];
        } catch (\Throwable $e) {
            Log::error('OTP email delivery failed', [
                'to' => $email,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'We could not send the email. Please check the email settings or try SMS instead.',
            ];
        }
    }

    private function renderSmsBody(string $code, int $expiryMinutes): string
    {
        $template = Setting::get('otp_message_template')
            ?: 'Your {app} verification code is {code}. It expires in {minutes} minutes. Do not share it with anyone.';

        return strtr($template, [
            '{code}' => $code,
            '{app}' => Setting::get('app_name') ?: config('app.name', 'Farmers Network'),
            '{minutes}' => (string) $expiryMinutes,
        ]);
    }
}
