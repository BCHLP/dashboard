<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('metric_baselines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('metric_id');
            $table->foreignId('device_metric_id');
            $table->tinyInteger('hour');
            $table->tinyInteger('dow');
            $table->float('mean');
            $table->float('median');
            $table->float('sd');
            $table->timestamps();
            $table->index(['metric_id', 'device_metric_id', 'dow', 'hour'])->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metric_baselines');
    }
};
