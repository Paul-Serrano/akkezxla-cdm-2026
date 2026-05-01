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
        Schema::create('player', function (Blueprint $table) {
            $table->id();
            $table->integer('apiId')->nullable();
            $table->string('lastname', 255)->nullable();
            $table->string('firstname', 255)->nullable();
            $table->string('role', 255)->nullable();
            $table->foreignId('teamId')->constrained('team');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player');
    }
};
