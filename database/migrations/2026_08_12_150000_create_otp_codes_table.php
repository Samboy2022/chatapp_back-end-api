<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Short-lived verification codes, whatever they were sent for.
 *
 * One table serves OTP login, password reset and email verification because
 * the mechanics are identical — only `purpose` differs — and a single table
 * means one expiry sweep and one attempt-limiting code path to get right.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();

            // Who the code was sent to, already normalised: an E.164 phone or
            // a lowercased email. Looked up on every verify, hence the index.
            $table->string('identifier')->index();

            $table->enum('channel', ['sms', 'email']);
            $table->enum('purpose', ['login', 'password_reset', 'email_verification', 'phone_verification']);

            // Hashed, never stored in the clear — a leaked DB snapshot must not
            // hand over live codes.
            $table->string('code_hash');

            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();

            // Lets the reset endpoint prove the code was verified moments ago
            // without re-sending it. Cleared once the password actually changes.
            $table->string('verification_token')->nullable()->index();
            $table->timestamp('verified_at')->nullable();

            // Kept for abuse triage — who hammered which number.
            $table->string('ip_address', 45)->nullable();

            $table->timestamps();

            // The hot query: newest live code for this identifier+purpose.
            $table->index(['identifier', 'purpose', 'consumed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
