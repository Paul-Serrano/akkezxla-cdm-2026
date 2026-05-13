<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Team;
use Illuminate\Support\Collection;

class StandingCalculatorService
{
    /**
     * Compute per-team stats from a set of played games.
     *
     * Returns an array keyed by team ID:
     *   ['played', 'won', 'drawn', 'lost', 'gf', 'ga', 'pts']
     *
     * @param  Collection<Team>  $teams
     * @param  Collection<Game>  $games  Already-scored games (scoreHome/scoreAway not null)
     */
    public static function computeStats(Collection $teams, Collection $games): array
    {
        $stats = [];
        foreach ($teams as $team) {
            $stats[$team->id] = [
                'played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0,
                'gf'     => 0, 'ga'  => 0, 'pts'   => 0,
            ];
        }

        foreach ($games as $game) {
            $h  = $game->homeTeamId;
            $a  = $game->awayTeamId;
            $sh = (int) $game->scoreHome;
            $sa = (int) $game->scoreAway;

            if (isset($stats[$h])) {
                $stats[$h]['played']++;
                $stats[$h]['gf'] += $sh;
                $stats[$h]['ga'] += $sa;
                if ($sh > $sa)       { $stats[$h]['won']++;   $stats[$h]['pts'] += 3; }
                elseif ($sh === $sa) { $stats[$h]['drawn']++; $stats[$h]['pts'] += 1; }
                else                 { $stats[$h]['lost']++; }
            }

            if (isset($stats[$a])) {
                $stats[$a]['played']++;
                $stats[$a]['gf'] += $sa;
                $stats[$a]['ga'] += $sh;
                if ($sa > $sh)       { $stats[$a]['won']++;   $stats[$a]['pts'] += 3; }
                elseif ($sh === $sa) { $stats[$a]['drawn']++; $stats[$a]['pts'] += 1; }
                else                 { $stats[$a]['lost']++; }
            }
        }

        return $stats;
    }

    /**
     * Recompute and persist team.rank for every team in a standing,
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

        $stats = self::computeStats($teams, $games);

        $ranked = $teams->sortByDesc(fn ($t) => [
            $stats[$t->id]['pts'],
            $stats[$t->id]['gf'] - $stats[$t->id]['ga'],
            $stats[$t->id]['gf'],
        ])->values();

        foreach ($ranked as $i => $team) {
            $team->rank = $i + 1;
            $team->save();
        }
    }
}
