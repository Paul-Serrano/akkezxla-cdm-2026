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
        Schema::create('game', function (Blueprint $table) {
            $table->id();
            $table->integer('apiId')->nullable();
            $table->timestampTz('startDate');
            $table->integer('scoreHome')->nullable();
            $table->integer('scoreAway')->nullable();
            $table->foreignId('homeTeamId')->constrained('team');
            $table->foreignId('awayTeamId')->constrained('team');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game');
    }
};
