<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('datapoints', function (Blueprint $table) {
            $table->id();
            $table->integer('source_id');
            $table->string('source_type');
            $table->foreignId('metric_id');
            $table->bigInteger('time');
            $table->float('value');
            $table->timestamps();

            $table->index(['source_id', 'metric_id']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('datapoints');
    }
};
