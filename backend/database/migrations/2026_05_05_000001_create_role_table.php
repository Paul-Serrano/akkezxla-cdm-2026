<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->string('color')->nullable();
            $table->timestamps();
        });

        DB::table('role')->insert([
            ['name' => 'admin',   'label' => 'Admin',   'created_at' => now(), 'updated_at' => now()],
            ['name' => 'akkezxla', 'label' => 'Akkezxla', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'uspeg', 'label' => 'Uspeg', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'regular', 'label' => 'Regular', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('role');
    }
};
