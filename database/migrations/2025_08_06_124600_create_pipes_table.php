<?php

use Clickbar\Magellan\Schema\MagellanSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        MagellanSchema::enablePostgisIfNotExists($this->connection);

        Schema::create('pipes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->magellanLineString('path');
            $table->integer('state')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {

        Schema::dropIfExists('pipes');

        MagellanSchema::disablePostgisIfExists($this->connection);
    }
};
