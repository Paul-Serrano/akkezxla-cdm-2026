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
        Schema::table('team', function (Blueprint $table) {
            $table->integer('standingPosition')->nullable()->after('rank');
            $table->integer('standingPlayedGames')->nullable()->after('standingPosition');
            $table->string('standingForm', 255)->nullable()->after('standingPlayedGames');
            $table->integer('standingWon')->nullable()->after('standingForm');
            $table->integer('standingDraw')->nullable()->after('standingWon');
            $table->integer('standingLost')->nullable()->after('standingDraw');
            $table->integer('standingPoints')->nullable()->after('standingLost');
            $table->integer('standingGoalsFor')->nullable()->after('standingPoints');
            $table->integer('standingGoalsAgainst')->nullable()->after('standingGoalsFor');
            $table->integer('standingGoalDifference')->nullable()->after('standingGoalsAgainst');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team', function (Blueprint $table) {
            $table->dropColumn([
                'standingPosition',
                'standingPlayedGames',
                'standingForm',
                'standingWon',
                'standingDraw',
                'standingLost',
                'standingPoints',
                'standingGoalsFor',
                'standingGoalsAgainst',
                'standingGoalDifference',
            ]);
        });
    }
};
