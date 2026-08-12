<?php

namespace App\Helpers;

use App\Models\Setting;

/**
 * One canonical form for phone numbers.
 *
 * Users type their number however they think of it — a Nigerian will write
 * `07026591356`, an international form will arrive as `+2347026591356`, and a
 * paste from a spreadsheet might be `234 702 659 1356`. All of those are the
 * same person, so everything that stores or looks up a number runs it through
 * here first and gets E.164 (`+2347026591356`) back.
 *
 * The rule for the ambiguous case: a national number written with a leading
 * trunk `0` gets that `0` stripped and the default country code prepended.
 */
class PhoneNumber
{
    /** Used when the DB isn't reachable (e.g. during early boot or tests). */
    private const FALLBACK_COUNTRY_CODE = '+234';

    /**
     * The dialling code applied to numbers typed in national format.
     *
     * Admin-configurable so a deployment outside Nigeria doesn't need a code
     * change.
     */
    public static function defaultCountryCode(): string
    {
        try {
            $code = Setting::get('default_country_code') ?: self::FALLBACK_COUNTRY_CODE;
        } catch (\Throwable $e) {
            $code = self::FALLBACK_COUNTRY_CODE;
        }

        $code = preg_replace('/[^0-9]/', '', (string) $code);

        return $code === '' ? self::FALLBACK_COUNTRY_CODE : '+' . $code;
    }

    /**
     * Normalise any user-entered number to E.164, or return null if it can't
     * plausibly be a phone number.
     *
     * Returning null rather than a best-effort string matters: callers use it
     * to decide "this is an email/username, not a phone", and a garbage
     * fallback would create unreachable accounts.
     */
    public static function normalize(?string $input, ?string $countryCode = null): ?string
    {
        if ($input === null) {
            return null;
        }

        $raw = trim($input);

        if ($raw === '' || str_contains($raw, '@')) {
            return null;
        }

        // Remember whether the user was explicit about international format
        // before we throw away the punctuation.
        $hadPlus = str_starts_with($raw, '+') || str_starts_with($raw, '00');

        $digits = preg_replace('/[^0-9]/', '', $raw);

        if ($digits === '') {
            return null;
        }

        // `00` is the other way of writing `+` (ITU international prefix).
        if (!str_starts_with($raw, '+') && str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        $cc = ltrim($countryCode ?? self::defaultCountryCode(), '+');
        $cc = preg_replace('/[^0-9]/', '', $cc);

        if ($hadPlus) {
            // Already international — trust it as given.
            return self::finalize($digits);
        }

        // National format with a trunk prefix: 07026591356 -> 234 + 7026591356
        if (str_starts_with($digits, '0')) {
            return self::finalizeNational($cc, ltrim($digits, '0'));
        }

        // Already carries the country code but no plus: 2347026591356
        if ($cc !== '' && str_starts_with($digits, $cc) && strlen($digits) > strlen($cc)) {
            return self::finalize($digits);
        }

        // Bare subscriber number: 7026591356 -> 234 + 7026591356
        return self::finalizeNational($cc, $digits);
    }

    /**
     * Every spelling of a number we might already have on file.
     *
     * Rows written before normalisation existed can still hold `07026591356`,
     * so lookups widen to these instead of assuming the DB is already clean.
     */
    public static function variants(?string $input, ?string $countryCode = null): array
    {
        $raw = trim((string) $input);

        if ($raw === '' || str_contains($raw, '@')) {
            return array_values(array_filter([$raw]));
        }

        $normalized = self::normalize($raw, $countryCode);
        $variants = [$raw];

        if ($normalized !== null) {
            $cc = ltrim($countryCode ?? self::defaultCountryCode(), '+');
            $national = substr($normalized, strlen($cc) + 1); // drop "+<cc>"

            $variants[] = $normalized;            // +2347026591356
            $variants[] = ltrim($normalized, '+'); // 2347026591356
            $variants[] = '0' . $national;         // 07026591356
            $variants[] = $national;               // 7026591356
        }

        return array_values(array_unique(array_filter($variants, fn ($v) => $v !== '')));
    }

    /**
     * True when the input looks like a phone number rather than an email.
     */
    public static function looksLikePhone(?string $input): bool
    {
        if ($input === null || str_contains($input, '@')) {
            return false;
        }

        $digits = preg_replace('/[^0-9]/', '', $input);

        // Shortest plausible national subscriber number; guards against a
        // stray "123" being treated as a phone and colliding on lookup.
        return strlen($digits) >= 7;
    }

    /**
     * Human-friendly rendering for emails and SMS bodies: +234 702 659 1356.
     */
    public static function format(?string $input): string
    {
        $normalized = self::normalize($input);

        if ($normalized === null) {
            return (string) $input;
        }

        $cc = ltrim(self::defaultCountryCode(), '+');

        if (!str_starts_with($normalized, '+' . $cc)) {
            return $normalized;
        }

        $national = substr($normalized, strlen($cc) + 1);

        // 3-3-rest reads naturally for the 10-digit national numbers we mostly
        // see; even chunks of 3 would leave a stray trailing digit.
        if (strlen($national) > 6) {
            return sprintf(
                '+%s %s %s %s',
                $cc,
                substr($national, 0, 3),
                substr($national, 3, 3),
                substr($national, 6)
            );
        }

        return '+' . $cc . ' ' . $national;
    }

    /**
     * Strip the leading `+`. Termii wants `2347026591356`, not `+234...`.
     */
    public static function forSms(?string $input): string
    {
        return ltrim((string) self::normalize($input), '+');
    }

    /**
     * Prepend a country code to a national number, rejecting a national part
     * that is too short to be real.
     *
     * Checking the national part separately matters: `+234` alone contributes
     * three digits, so a bare `12345` would otherwise clear the 8-digit E.164
     * floor and produce a plausible-looking but undeliverable `+23412345`.
     */
    private static function finalizeNational(string $countryCode, string $national): ?string
    {
        // Shortest subscriber number in real-world use.
        if (strlen($national) < 7) {
            return null;
        }

        return self::finalize($countryCode . $national);
    }

    private static function finalize(string $digits): ?string
    {
        // E.164 caps the whole number at 15 digits; anything under 8 can't be
        // a country code plus a subscriber number.
        $length = strlen($digits);

        if ($length < 8 || $length > 15) {
            return null;
        }

        return '+' . $digits;
    }
}
