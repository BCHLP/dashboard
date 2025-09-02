<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('node_metric', function (Blueprint $table) {
            $table->foreignId('node_id');
            $table->foreignId('metric_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('node_metric');
    }
};
