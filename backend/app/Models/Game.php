<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    public $timestamps = false;

    protected $table = 'game';

    protected $fillable = [
        'apiId',
        'startDate',
        'scoreHome',
        'scoreAway',
        'homeTeamId',
        'awayTeamId',
    ];

    public function homeTeam()
    {
        return $this->belongsTo(Team::class, 'homeTeamId');
    }

    public function awayTeam()
    {
        return $this->belongsTo(Team::class, 'awayTeamId');
    }
}
