<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('datapoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('metric_id');
            $table->foreignId('sensor_id');
            $table->float('value');
            $table->timestamps();

            $table->index(['metric_id', 'sensor_id']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('datapoints');
    }
};
