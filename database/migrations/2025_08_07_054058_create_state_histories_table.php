<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('state_histories', function (Blueprint $table) {
            $table->id();
            $table->string('device_class');
            $table->integer('device_id');
            $table->integer('state');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('state_histories');
    }
};
