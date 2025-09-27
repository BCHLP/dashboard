<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_fingerprints', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable();
            $table->json('fingerprint_data');
            $table->string('hash', 64)->index();
            $table->ipAddress();
            $table->text('user_agent');
            $table->string('session_id');
            $table->boolean('is_suspicious')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['hash', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_fingerprints');
    }
};
