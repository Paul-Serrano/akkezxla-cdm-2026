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
        Schema::create('team', function (Blueprint $table) {
            $table->id();
            $table->integer('apiId')->nullable();
            $table->string('name', 255);
            $table->string('shortName', 255);
            $table->timestampTz('founded');
            $table->string('crest', 255);
            $table->integer('rank')->nullable();
            $table->foreignId('standingId')->constrained('standing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team');
    }
};
