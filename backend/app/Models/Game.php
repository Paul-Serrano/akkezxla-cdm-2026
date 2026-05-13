<?php

namespace App\Models;

use App\Services\BetConsensusService;
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

    /**
     * Compute the consensus bet outcome (home / draw / away) from bets placed by users in the given roles.
     */
    public function consensus(array $roles = [User::ROLE_AKKEZXLA]): array
    {
        return BetConsensusService::compute($this, $roles);
    }
}
