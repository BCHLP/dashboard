<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pipes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->magellanLineString('path');
            $table->integer('state')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pipes');
    }
};
