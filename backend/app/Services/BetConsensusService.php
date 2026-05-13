<?php

namespace App\Services;

use App\Enums\BetResult;
use App\Models\Bet;
use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Collection;

class BetConsensusService
{
    /**
     * Compute the consensus bet outcome (home / draw / away) for a game
     * based on bets placed by users in the given roles.
     *
     * Returns:
     *   [
     *     'total'    => int,
     *     'outcomes' => Collection of ['label' => string, 'count' => int, 'result' => BetResult],
     *   ]
     */
    public static function compute(Game $game, array $roles = [User::ROLE_AKKEZXLA]): array
    {
        $bets = Bet::whereHas('user', fn ($q) => $q->whereHas('roles', fn ($r) => $r->whereIn('name', $roles)))
            ->where('gameId', $game->id)
            ->whereNull('playerId')
            ->whereNotNull('scoreHome')
            ->whereNotNull('scoreAway')
            ->get(['scoreHome', 'scoreAway']);

        $total = $bets->count();

        if ($total === 0) {
            return ['total' => 0, 'outcomes' => collect()];
        }

        $gameOutcome = ($game->scoreHome !== null && $game->scoreAway !== null)
            ? ((int) $game->scoreHome <=> (int) $game->scoreAway)
            : null;

        $homeName = $game->homeTeam->shortName;
        $awayName = $game->awayTeam->shortName;

        $grouped  = $bets->groupBy(fn ($b) => (int) $b->scoreHome <=> (int) $b->scoreAway);
        $maxCount = $grouped->max(fn ($g) => $g->count());

        $outcomes = $grouped
            ->filter(fn ($g) => $g->count() === $maxCount)
            ->map(function (Collection $group, int $sign) use ($gameOutcome, $homeName, $awayName) {
                $result = $gameOutcome === null
                    ? BetResult::Pending
                    : ($sign === $gameOutcome ? BetResult::Win : BetResult::Lose);

                return [
                    'label'  => match ($sign) {
                        1  => $homeName,
                        0  => 'Draw',
                        -1 => $awayName,
                    },
                    'count'  => $group->count(),
                    'result' => $result,
                ];
            })
            ->values();

        return ['total' => $total, 'outcomes' => $outcomes];
    }
}
