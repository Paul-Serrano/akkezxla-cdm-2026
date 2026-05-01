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
        Schema::create('bet', function (Blueprint $table) {
            $table->id();
            $table->integer('apiId')->nullable();
            $table->string('bet', 255);
            $table->integer('scoreHome')->nullable();
            $table->integer('scoreAway')->nullable();
            $table->foreignId('gameId')->nullable()->constrained('game');
            $table->foreignId('userId')->constrained('user');
            $table->foreignId('playerId')->nullable()->constrained('player');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bet');
    }
};
