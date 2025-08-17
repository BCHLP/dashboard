<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('manholes', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('sap_id')->nullable();
            $table->string('name')->nullable();
            $table->magellanPoint('coordinates');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manholes');
    }
};
