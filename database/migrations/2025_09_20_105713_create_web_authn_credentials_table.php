<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        return;
        Schema::create('webauthn_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // WebAuthn required fields
            $table->string('credential_id')->unique(); // Base64 encoded
            $table->text('public_key'); // Public key data
            $table->unsignedInteger('counter')->default(0); // Signature counter

            // Credential metadata
            $table->string('name')->nullable(); // User-friendly name
            $table->string('type')->default('public-key'); // Credential type
            $table->json('transports')->nullable(); // Available transports

            // Authenticator info
            $table->string('aaguid')->nullable(); // Authenticator GUID
            $table->string('attestation_type')->nullable(); // none, basic, self, attca
            $table->text('attestation_object')->nullable(); // Full attestation data

            // Usage tracking
            $table->timestamp('last_used_at')->nullable();
            $table->unsignedInteger('usage_count')->default(0);

            // Security flags
            $table->boolean('user_verified')->default(false); // Supports user verification
            $table->boolean('backup_eligible')->default(false); // Can be backed up
            $table->boolean('backup_state')->default(false); // Current backup state

            $table->timestamps();
            $table->softDeletes(); // For audit trail

            $table->index(['user_id', 'credential_id']);
        });

        Schema::create('webauthn_auth_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('webauthn_credential_id')->nullable()
                ->constrained()->onDelete('set null');

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('result'); // success, failed, error
            $table->string('failure_reason')->nullable();
            $table->json('raw_data')->nullable(); // Store full WebAuthn response

            $table->timestamp('attempted_at');
            $table->timestamps();

            $table->index(['user_id', 'attempted_at']);
            $table->index(['result', 'attempted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webauthn_credentials');
        Schema::dropIfExists('webauthn_auth_logs');
    }
};
