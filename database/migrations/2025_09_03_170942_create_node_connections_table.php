<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('node_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_node_id')->constrained(
                table: 'nodes'
            );
            $table->foreignId('to_node_id')->constrained(
                table: 'nodes'
            );
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('node_connections');
    }
};
