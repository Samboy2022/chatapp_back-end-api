<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Pushes the admin-managed SMTP settings into Laravel's mail config at runtime.
 *
 * The alternative — editing `.env` on the server — is exactly what the user
 * asked to get away from. Applying this immediately before a send (rather than
 * once at boot) means an admin's change takes effect on the very next email
 * without a deploy or a config:cache clear.
 */
class MailConfigService
{
    private static bool $applied = false;

    /**
     * Overlay DB settings onto config('mail'). Safe to call repeatedly; the
     * work is skipped after the first call in a request.
     */
    public static function apply(bool $force = false): void
    {
        if (self::$applied && !$force) {
            return;
        }

        try {
            $mailer = Setting::get('mail_mailer') ?: config('mail.default');
            $host = Setting::get('mail_host');
            $port = Setting::get('mail_port');
            $username = Setting::get('mail_username');
            $password = Setting::get('mail_password');
            $encryption = Setting::get('mail_encryption');
            $fromAddress = Setting::get('mail_from_address');
            $fromName = Setting::get('mail_from_name');

            // An admin who hasn't filled in SMTP yet shouldn't have their
            // working .env config overwritten with blanks.
            if (filled($host)) {
                Config::set('mail.mailers.smtp.host', $host);
            }

            if (filled($port)) {
                Config::set('mail.mailers.smtp.port', (int) $port);
            }

            if (filled($username)) {
                Config::set('mail.mailers.smtp.username', $username);
            }

            if (filled($password)) {
                Config::set('mail.mailers.smtp.password', $password);
            }

            if (filled($encryption)) {
                // "none" is how the dropdown spells "no encryption"; Laravel
                // wants null there.
                Config::set(
                    'mail.mailers.smtp.encryption',
                    $encryption === 'none' ? null : $encryption
                );
            }

            if (filled($mailer)) {
                Config::set('mail.default', $mailer);
            }

            if (filled($fromAddress)) {
                Config::set('mail.from.address', $fromAddress);
            }

            if (filled($fromName)) {
                Config::set('mail.from.name', $fromName);
            }

            // Laravel caches resolved mailers; without this the next send would
            // reuse a transport built from the pre-override config.
            Mail::purge('smtp');
            Mail::purge($mailer);

            self::$applied = true;
        } catch (\Throwable $e) {
            // Never let a settings problem take down a request that only
            // incidentally sends mail — fall back to the .env config.
            Log::warning('Could not apply mail settings from database', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /** Whether outgoing email is switched on at all. */
    public static function enabled(): bool
    {
        try {
            $enabled = Setting::get('mail_enabled');

            // Absent setting means "not configured yet" — default to on so an
            // existing .env setup keeps working.
            return $enabled === null || filter_var($enabled, FILTER_VALIDATE_BOOLEAN);
        } catch (\Throwable $e) {
            return true;
        }
    }
}
