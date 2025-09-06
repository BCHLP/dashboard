<?php

use App\Models\Node;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('treatment_lines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('stage_1')->default(0);
            $table->integer('stage_2')->default(0);
            $table->integer('stage_3')->default(0);
            $table->boolean('maintenance_mode');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_lines');
    }
};
