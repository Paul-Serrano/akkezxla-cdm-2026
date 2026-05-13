<?php

namespace App\Models;

use App\Services\StandingCalculatorService;
use Illuminate\Database\Eloquent\Model;

class Standing extends Model
{
    public $timestamps = false;

    protected $table = 'standing';

    protected $fillable = [
        'apiId',
        'name',
    ];

    public function teams()
    {
        return $this->hasMany(Team::class, 'standingId');
    }

    /**
     * Recompute and persist team.rank for every team in this standing.
     */
    public static function recalculate(int $standingId): void
    {
        StandingCalculatorService::recalculate($standingId);
    }
}
