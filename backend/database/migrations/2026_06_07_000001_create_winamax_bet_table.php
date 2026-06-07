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
        Schema::create('winamax_bet', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('matchdayPage')->unique();
            $table->decimal('totalOdds', 8, 2);
            $table->decimal('amountBet', 10, 2);
            $table->decimal('earning', 10, 2);
            $table->string('status', 16)->default('pending');
            $table->foreignId('userId')->nullable()->constrained('user')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('winamax_bet');
    }
};
