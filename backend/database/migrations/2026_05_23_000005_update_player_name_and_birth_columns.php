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
        Schema::table('player', function (Blueprint $table) {
            $table->string('name', 255)->nullable();
            $table->date('dateOfBirth')->nullable();
        });

        $players = DB::table('player')
            ->select('id', 'firstname', 'lastname')
            ->get();

        foreach ($players as $player) {
            $name = trim((string) ($player->firstname ?? ''));

            if ($name === '') {
                $name = trim((string) ($player->lastname ?? ''));
            }

            DB::table('player')
                ->where('id', $player->id)
                ->update(['name' => $name !== '' ? $name : null]);
        }

        Schema::table('player', function (Blueprint $table) {
            $table->dropColumn(['firstname', 'lastname']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player', function (Blueprint $table) {
            $table->string('firstname', 255)->nullable();
            $table->string('lastname', 255)->nullable();
        });

        $players = DB::table('player')
            ->select('id', 'name')
            ->get();

        foreach ($players as $player) {
            DB::table('player')
                ->where('id', $player->id)
                ->update([
                    'firstname' => $player->name,
                    'lastname' => null,
                ]);
        }

        Schema::table('player', function (Blueprint $table) {
            $table->dropColumn(['name', 'dateOfBirth']);
        });
    }
};
