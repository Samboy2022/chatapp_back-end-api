<?php

namespace App\Mail;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The one email that carries a verification code, whatever it's for.
 *
 * Subject and wording shift with `purpose` so a password-reset mail doesn't
 * read like a login mail — users treat those very differently when deciding
 * whether an unexpected code is suspicious.
 */
class OtpCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $code,
        public string $purpose,
        public int $expiryMinutes,
        public ?string $userName = null,
    ) {
    }

    public function envelope(): Envelope
    {
        $app = Setting::get('app_name') ?: config('app.name', 'Farmers Network');

        return new Envelope(
            subject: match ($this->purpose) {
                'password_reset' => "{$this->code} is your {$app} password reset code",
                'email_verification' => "{$this->code} is your {$app} verification code",
                default => "{$this->code} is your {$app} login code",
            },
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp-code',
            with: [
                'code' => $this->code,
                'purpose' => $this->purpose,
                'expiryMinutes' => $this->expiryMinutes,
                'userName' => $this->userName,
                'appName' => Setting::get('app_name') ?: config('app.name', 'Farmers Network'),
                'logoUrl' => Setting::get('logo_url'),
                'headline' => match ($this->purpose) {
                    'password_reset' => 'Reset your password',
                    'email_verification' => 'Verify your email address',
                    default => 'Sign in to your account',
                },
                'explainer' => match ($this->purpose) {
                    'password_reset' => 'Use this code to set a new password. If you did not ask to reset your password, you can safely ignore this email — your password will not change.',
                    'email_verification' => 'Enter this code in the app to confirm this email address belongs to you.',
                    default => 'Enter this code in the app to finish signing in.',
                },
            ],
        );
    }
}
