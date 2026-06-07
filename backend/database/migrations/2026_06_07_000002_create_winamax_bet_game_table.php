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
        Schema::create('winamax_bet_game', function (Blueprint $table) {
            $table->foreignId('winamaxBetId')->constrained('winamax_bet')->cascadeOnDelete();
            $table->foreignId('gameId')->constrained('game')->cascadeOnDelete();

            $table->primary(['winamaxBetId', 'gameId']);
            $table->unique('gameId');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('winamax_bet_game');
    }
};
