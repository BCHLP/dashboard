<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_login_audits', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->uuid('user_fingerprint_id')->nullable();
            $table->string('email');
            $table->boolean('successful');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_login_audits');
    }
};
