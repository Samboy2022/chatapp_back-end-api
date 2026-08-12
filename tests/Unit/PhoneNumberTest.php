<?php

namespace Tests\Unit;

use App\Helpers\PhoneNumber;
use PHPUnit\Framework\TestCase;

/**
 * These cases mirror the Flutter client's `lib/utils/phone_number.dart`
 * exactly. If one side changes without the other, an account can register in
 * one format and then fail to log in — the bug this helper exists to prevent.
 *
 * Extends PHPUnit's TestCase rather than the project's, deliberately: the
 * project base class runs `migrate:fresh` in setUp, and pure string
 * normalisation shouldn't need a database — let alone rebuild one. The helper
 * falls back to +234 when settings aren't reachable, which is exactly the
 * path exercised here.
 */
class PhoneNumberTest extends TestCase
{
    public static function normalizationCases(): array
    {
        return [
            'national with trunk zero' => ['07026591356', '+2347026591356'],
            'bare subscriber number' => ['7026591356', '+2347026591356'],
            'country code without plus' => ['2347026591356', '+2347026591356'],
            'already E.164' => ['+2347026591356', '+2347026591356'],
            'spaced international' => ['+234 702 659 1356', '+2347026591356'],
            'hyphenated national' => ['0702-659-1356', '+2347026591356'],
            'ITU 00 prefix' => ['002347026591356', '+2347026591356'],
            'different network prefix' => ['08012345678', '+2348012345678'],
            'email is not a phone' => ['user@mail.com', null],
            'empty string' => ['', null],
            'too short' => ['123', null],
        ];
    }

    /**
     * @dataProvider normalizationCases
     */
    public function test_it_normalizes_input_to_e164(string $input, ?string $expected): void
    {
        $this->assertSame($expected, PhoneNumber::normalize($input));
    }

    public function test_every_spelling_collapses_to_one_value(): void
    {
        $forms = ['07026591356', '7026591356', '2347026591356', '+2347026591356'];

        $normalized = array_unique(array_map(
            fn ($form) => PhoneNumber::normalize($form),
            $forms
        ));

        $this->assertCount(1, $normalized, 'All spellings must resolve to the same number');
        $this->assertSame('+2347026591356', reset($normalized));
    }

    public function test_variants_cover_legacy_storage_formats(): void
    {
        $variants = PhoneNumber::variants('07026591356');

        // A row written before normalisation existed could hold any of these,
        // so the login lookup has to try them all.
        foreach (['+2347026591356', '2347026591356', '07026591356', '7026591356'] as $expected) {
            $this->assertContains($expected, $variants);
        }
    }

    public function test_it_distinguishes_phones_from_emails(): void
    {
        $this->assertTrue(PhoneNumber::looksLikePhone('07026591356'));
        $this->assertTrue(PhoneNumber::looksLikePhone('+2347026591356'));
        $this->assertFalse(PhoneNumber::looksLikePhone('user@mail.com'));
        $this->assertFalse(PhoneNumber::looksLikePhone('123'));
    }

    public function test_it_strips_the_plus_for_the_sms_gateway(): void
    {
        // Termii rejects a leading "+".
        $this->assertSame('2347026591356', PhoneNumber::forSms('07026591356'));
    }

    public function test_it_formats_for_display(): void
    {
        $this->assertSame('+234 702 659 1356', PhoneNumber::format('07026591356'));
    }

    public function test_it_rejects_numbers_outside_e164_length_limits(): void
    {
        // Too long to be E.164 (max 15 digits).
        $this->assertNull(PhoneNumber::normalize('+12345678901234567890'));

        // National part too short to be a real subscriber number. The country
        // code must not pad these over the line into looking valid.
        $this->assertNull(PhoneNumber::normalize('12345'));
        $this->assertNull(PhoneNumber::normalize('012345'));

        // Seven digits is the shortest we accept.
        $this->assertSame('+2341234567', PhoneNumber::normalize('1234567'));
    }
}
