<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('config', function (Blueprint $table) {
            $table->integer('pointsSuperWin')->default(3)->after('totalPlayerBet');
            $table->integer('pointsWin')->default(1)->after('pointsSuperWin');
            $table->integer('pointsScorer')->default(1)->after('pointsWin');
        });
    }

    public function down(): void
    {
        Schema::table('config', function (Blueprint $table) {
            $table->dropColumn(['pointsSuperWin', 'pointsWin', 'pointsScorer']);
        });
    }
};
