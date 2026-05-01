<?php

use App\Enums\ConfigKey;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old single-row table and rebuild as key-value store
        Schema::dropIfExists('config');

        DB::statement('
            CREATE TABLE config (
                id    BIGSERIAL PRIMARY KEY,
                name  VARCHAR(100) NOT NULL UNIQUE,
                value INTEGER      NOT NULL
            )
        ');

        // Seed defaults from the enum
        foreach (ConfigKey::cases() as $key) {
            DB::table('config')->insert([
                'name'  => $key->value,
                'value' => $key->default(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('config');
    }
};
