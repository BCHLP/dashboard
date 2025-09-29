<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mqtt_audits', function (Blueprint $table) {
            $table->id();
            $table->string('client_id');
            $table->dateTime('when');
            $table->boolean('unusual');
            $table->string('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mqtt_audits');
    }
};
