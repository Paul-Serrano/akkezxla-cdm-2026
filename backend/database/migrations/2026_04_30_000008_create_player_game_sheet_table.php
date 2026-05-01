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
        Schema::create('player_game_sheet', function (Blueprint $table) {
            $table->id();
            $table->integer('apiId')->nullable();
            $table->integer('goals');
            $table->integer('passes');
            $table->boolean('yellowCard');
            $table->boolean('redCard');
            $table->foreignId('gameId')->nullable()->constrained('game');
            $table->foreignId('playerId')->unique()->constrained('player');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_game_sheet');
    }
};
