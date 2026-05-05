<?php

namespace App\Models;

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
     * Recompute and persist team.rank for every team in this standing,
     * sorted by: points desc → goal difference desc → goals for desc.
     */
    public static function recalculate(int $standingId): void
    {
        $teams   = Team::where('standingId', $standingId)->get();
        $teamIds = $teams->pluck('id');

        $games = Game::whereNotNull('scoreHome')
            ->whereNotNull('scoreAway')
            ->where(function ($q) use ($teamIds) {
                $q->whereIn('homeTeamId', $teamIds)
                  ->orWhereIn('awayTeamId', $teamIds);
            })
            ->get();

        $stats = [];
        foreach ($teams as $team) {
            $stats[$team->id] = ['pts' => 0, 'gd' => 0, 'gf' => 0];
        }

        foreach ($games as $game) {
            $h  = $game->homeTeamId;
            $a  = $game->awayTeamId;
            $sh = (int) $game->scoreHome;
            $sa = (int) $game->scoreAway;

            if (isset($stats[$h])) {
                $stats[$h]['gf'] += $sh;
                $stats[$h]['gd'] += $sh - $sa;
                if ($sh > $sa)        $stats[$h]['pts'] += 3;
                elseif ($sh === $sa)  $stats[$h]['pts'] += 1;
            }
            if (isset($stats[$a])) {
                $stats[$a]['gf'] += $sa;
                $stats[$a]['gd'] += $sa - $sh;
                if ($sa > $sh)        $stats[$a]['pts'] += 3;
                elseif ($sh === $sa)  $stats[$a]['pts'] += 1;
            }
        }

        $ranked = $teams->sortByDesc(fn ($t) => [
            $stats[$t->id]['pts'],
            $stats[$t->id]['gd'],
            $stats[$t->id]['gf'],
        ])->values();

        foreach ($ranked as $i => $team) {
            $team->rank = $i + 1;
            $team->save();
        }
    }
}
