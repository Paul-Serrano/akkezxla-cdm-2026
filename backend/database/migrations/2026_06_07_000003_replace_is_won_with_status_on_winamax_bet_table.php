<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('winamax_bet', 'status')) {
            Schema::table('winamax_bet', function (Blueprint $table) {
                $table->string('status', 16)->default('pending')->after('amountBet');
            });
        }

        if (Schema::hasColumn('winamax_bet', 'isWon')) {
            DB::table('winamax_bet')
                ->whereNull('isWon')
                ->update(['status' => 'pending']);

            DB::table('winamax_bet')
                ->where('isWon', true)
                ->update(['status' => 'won']);

            DB::table('winamax_bet')
                ->where('isWon', false)
                ->update(['status' => 'lost']);

            Schema::table('winamax_bet', function (Blueprint $table) {
                $table->dropColumn('isWon');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('winamax_bet', 'isWon')) {
            Schema::table('winamax_bet', function (Blueprint $table) {
                $table->boolean('isWon')->nullable()->after('amountBet');
            });
        }

        if (Schema::hasColumn('winamax_bet', 'status')) {
            DB::table('winamax_bet')
                ->where('status', 'pending')
                ->update(['isWon' => null]);

            DB::table('winamax_bet')
                ->where('status', 'placed')
                ->update(['isWon' => null]);

            DB::table('winamax_bet')
                ->where('status', 'won')
                ->update(['isWon' => true]);

            DB::table('winamax_bet')
                ->where('status', 'lost')
                ->update(['isWon' => false]);

            Schema::table('winamax_bet', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
