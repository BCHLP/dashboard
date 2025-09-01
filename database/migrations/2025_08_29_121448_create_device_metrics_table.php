<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('device_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('metric_id');
            $table->integer('device_metric_id');
            $table->string('device_metric_type');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_metrics');
    }
};
