<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('router_network', function (Blueprint $table) {
            $table->foreignId('router_id');
            $table->foreignId('network_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('router_network');
    }
};
