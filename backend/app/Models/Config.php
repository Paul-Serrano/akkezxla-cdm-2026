<?php

namespace App\Models;

use App\Enums\ConfigKey;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    public $timestamps = false;

    protected $table = 'config';

    protected $fillable = ['name', 'value'];

    /** Get the integer value for a config key, falling back to its enum default. */
    public static function get(ConfigKey $key): int
    {
        $row = self::where('name', $key->value)->first();

        return $row ? (int) $row->value : $key->default();
    }

    /** Upsert a config value. */
    public static function set(ConfigKey $key, int $value): void
    {
        self::updateOrCreate(
            ['name' => $key->value],
            ['value' => $value]
        );
    }
}
