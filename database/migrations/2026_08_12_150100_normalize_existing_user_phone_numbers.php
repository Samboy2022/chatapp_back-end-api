<?php

use App\Helpers\PhoneNumber;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Rewrite phone numbers already on file into E.164.
 *
 * Without this, an account registered as `07026591356` stays invisible to a
 * login that normalises to `+2347026591356` — the exact bug this whole change
 * set exists to fix.
 *
 * Deliberately conservative: a row is only rewritten when the normalised form
 * is unambiguous AND no other account already holds it, because `phone_number`
 * is unique and silently merging two accounts would be far worse than leaving
 * one row un-normalised for an admin to look at.
 */
return new class extends Migration
{
    public function up(): void
    {
        $skipped = [];

        DB::table('users')
            ->select('id', 'phone_number')
            ->orderBy('id')
            ->chunkById(200, function ($users) use (&$skipped) {
                foreach ($users as $user) {
                    $current = (string) $user->phone_number;

                    if ($current === '') {
                        continue;
                    }

                    $normalized = PhoneNumber::normalize($current);

                    if ($normalized === null || $normalized === $current) {
                        continue;
                    }

                    $taken = DB::table('users')
                        ->where('phone_number', $normalized)
                        ->where('id', '!=', $user->id)
                        ->exists();

                    if ($taken) {
                        $skipped[] = "user {$user->id} ({$current} -> {$normalized})";
                        continue;
                    }

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['phone_number' => $normalized]);
                }
            });

        if ($skipped !== []) {
            // Surfaced rather than swallowed: these are duplicate accounts that
            // need a human decision about which one survives.
            Log::warning('Phone normalisation skipped rows that would collide', [
                'rows' => $skipped,
            ]);
        }
    }

    public function down(): void
    {
        // Irreversible by design: the original free-text spellings aren't kept
        // anywhere, and guessing them back would be worse than a no-op.
    }
};
