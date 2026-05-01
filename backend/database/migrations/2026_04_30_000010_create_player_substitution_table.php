<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('player_substitution', function (Blueprint $table) {
            $table->id();
            $table->integer('apiId')->nullable();
            $table->foreignId('gameId')->nullable()->constrained('game');
            $table->foreignId('inPlayerId')->constrained('player');
            $table->foreignId('outPlayerId')->nullable()->constrained('player');
            $table->timestampTz('minute')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_substitution');
    }
};
