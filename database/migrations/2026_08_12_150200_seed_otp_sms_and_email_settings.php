<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

/**
 * Everything the OTP / SMS / email stack needs, editable from the admin
 * dashboard. The settings page renders whatever groups exist in this table,
 * so seeding these rows is all it takes for them to show up as tabs.
 *
 * `is_public` is false for anything that could be abused if the mobile app
 * leaked it — API keys and SMTP credentials above all.
 */
return new class extends Migration
{
    /** [key, type, group, label, description, default, is_public, options] */
    private array $rows = [
        // ── Phone handling ────────────────────────────────────────────────
        [
            'default_country_code', 'string', 'phone',
            'Default Country Code',
            'Applied to numbers typed without one. 07026591356 becomes +2347026591356.',
            '+234', true, null,
        ],

        // ── OTP behaviour ─────────────────────────────────────────────────
        [
            'otp_enabled', 'boolean', 'otp',
            'Enable OTP Login',
            'Allow signing in with a one-time code instead of a password.',
            '1', true, null,
        ],
        [
            'otp_length', 'integer', 'otp',
            'OTP Code Length',
            'Number of digits in the code. 4 to 8.',
            '6', false, null,
        ],
        [
            'otp_expiry_minutes', 'integer', 'otp',
            'OTP Expiry (minutes)',
            'How long a code stays valid after it is sent.',
            '10', false, null,
        ],
        [
            'otp_max_attempts', 'integer', 'otp',
            'Max Verification Attempts',
            'Wrong guesses allowed before the code is burned and must be re-sent.',
            '5', false, null,
        ],
        [
            'otp_resend_cooldown_seconds', 'integer', 'otp',
            'Resend Cooldown (seconds)',
            'Minimum wait between two code requests for the same recipient.',
            '60', true, null,
        ],
        [
            'otp_default_channel', 'string', 'otp',
            'Default Delivery Channel',
            'Channel used when the app does not specify one.',
            'sms', true, ['sms', 'email'],
        ],
        [
            'otp_message_template', 'string', 'otp',
            'OTP Message Template',
            'SMS body. {code} is replaced with the code, {app} with the app name, {minutes} with the expiry.',
            'Your {app} verification code is {code}. It expires in {minutes} minutes. Do not share it with anyone.',
            false, null,
        ],

        // ── Termii (SMS provider) ─────────────────────────────────────────
        [
            'sms_provider', 'string', 'sms',
            'SMS Provider',
            'Which gateway delivers SMS. "log" writes codes to the log instead of sending — use it for testing.',
            'termii', false, ['termii', 'log'],
        ],
        [
            'termii_api_key', 'string', 'sms',
            'Termii API Key',
            'From your Termii dashboard under API settings. Never exposed to the mobile app.',
            '', false, null,
        ],
        [
            'termii_sender_id', 'string', 'sms',
            'Termii Sender ID',
            'Approved sender name shown on the SMS, 3 to 11 characters.',
            'N-Alert', false, null,
        ],
        [
            'termii_channel', 'string', 'sms',
            'Termii Channel',
            'Use "dnd" to reach numbers on the Do-Not-Disturb list (most Nigerian numbers). "generic" is cheaper but DND numbers will not receive it.',
            'dnd', false, ['dnd', 'generic', 'whatsapp'],
        ],
        [
            'termii_base_url', 'string', 'sms',
            'Termii Base URL',
            'API host. Only change this if Termii tells you to.',
            'https://api.ng.termii.com', false, null,
        ],

        // ── Email delivery ────────────────────────────────────────────────
        [
            'mail_enabled', 'boolean', 'email',
            'Enable Email Sending',
            'Turn off to stop all outgoing email, including password reset codes.',
            '1', false, null,
        ],
        [
            'mail_mailer', 'string', 'email',
            'Mail Transport',
            '"smtp" for a real mail server, "log" to write emails to the log for testing.',
            'smtp', false, ['smtp', 'log', 'sendmail'],
        ],
        [
            'mail_host', 'string', 'email',
            'SMTP Host',
            'e.g. smtp.gmail.com or your hosting provider\'s mail server.',
            '', false, null,
        ],
        [
            'mail_port', 'integer', 'email',
            'SMTP Port',
            '587 for TLS, 465 for SSL.',
            '587', false, null,
        ],
        [
            'mail_username', 'string', 'email',
            'SMTP Username',
            'Usually the full email address.',
            '', false, null,
        ],
        [
            'mail_password', 'string', 'email',
            'SMTP Password',
            'For Gmail this must be an App Password, not the account password.',
            '', false, null,
        ],
        [
            'mail_encryption', 'string', 'email',
            'SMTP Encryption',
            'tls for port 587, ssl for port 465.',
            'tls', false, ['tls', 'ssl', 'none'],
        ],
        [
            'mail_from_address', 'string', 'email',
            'From Address',
            'The address password reset and verification emails are sent from.',
            'noreply@farmersnetwork.com.ng', false, null,
        ],
        [
            'mail_from_name', 'string', 'email',
            'From Name',
            'Display name shown as the sender.',
            'Farmers Network', false, null,
        ],

        // ── Password reset / verification policy ──────────────────────────
        [
            'password_reset_enabled', 'boolean', 'otp',
            'Enable Password Reset',
            'Allow users to reset a forgotten password from the app.',
            '1', true, null,
        ],
        [
            'password_reset_channels', 'string', 'otp',
            'Password Reset Channels',
            'Which channels a user may reset through.',
            'email,sms', true, ['email', 'sms', 'email,sms'],
        ],
        [
            'email_verification_required', 'boolean', 'otp',
            'Require Email Verification',
            'Require a verified email address before a new account can sign in.',
            '0', true, null,
        ],
    ];

    public function up(): void
    {
        foreach ($this->rows as [$key, $type, $group, $label, $description, $default, $isPublic, $options]) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    // Never clobber a value an admin has already set.
                    'value' => Setting::where('key', $key)->value('value') ?: $default,
                    'type' => $type,
                    'group' => $group,
                    'label' => $label,
                    'description' => $description,
                    'options' => $options,
                    'is_public' => $isPublic,
                ]
            );
        }

        Cache::flush();
    }

    public function down(): void
    {
        Setting::whereIn('key', array_column($this->rows, 0))->delete();
        Cache::flush();
    }
};
