<?php

use App\Models\Node;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('node_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Node::class)->constrained('nodes');
            $table->string('name');
            $table->string('value');
            $table->string('cast')->default('string');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('node_settings');
    }
};
