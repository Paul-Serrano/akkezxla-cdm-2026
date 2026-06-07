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
        if (!Schema::hasColumn('winamax_bet', 'earning')) {
            Schema::table('winamax_bet', function (Blueprint $table) {
                $table->decimal('earning', 10, 2)->nullable()->after('amountBet');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('winamax_bet', 'earning')) {
            Schema::table('winamax_bet', function (Blueprint $table) {
                $table->dropColumn('earning');
            });
        }
    }
};
