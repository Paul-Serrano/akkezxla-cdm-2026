<?php

namespace App\Models;

use App\Enums\BetResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

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
     * Compute the consensus bet outcome (home / draw / away) from admin+winamax bets.
     *
     * Returns:
     *   [
     *     'total'    => int,
     *     'outcomes' => Collection of ['label' => string, 'count' => int, 'result' => BetResult],
     *   ]
     */
    public function consensus(array $roles = [User::ROLE_WINAMAX]): array
    {
        $bets = Bet::whereHas('user', fn($q) => $q->whereHas('roles', fn($r) => $r->whereIn('name', $roles)))
            ->where('gameId', $this->id)
            ->whereNull('playerId')
            ->whereNotNull('scoreHome')
            ->whereNotNull('scoreAway')
            ->get(['scoreHome', 'scoreAway']);

        $total = $bets->count();

        if ($total === 0) {
            return ['total' => 0, 'outcomes' => collect()];
        }

        // Determine the actual game outcome sign (if played)
        $gameOutcome = ($this->scoreHome !== null && $this->scoreAway !== null)
            ? ((int) $this->scoreHome <=> (int) $this->scoreAway)
            : null;

        $homeName = $this->homeTeam->shortName;
        $awayName = $this->awayTeam->shortName;

        // Group bets by outcome sign: +1 home, 0 draw, -1 away
        $grouped  = $bets->groupBy(fn($b) => (int) $b->scoreHome <=> (int) $b->scoreAway);
        $maxCount = $grouped->max(fn($g) => $g->count());

        $outcomes = $grouped
            ->filter(fn($g) => $g->count() === $maxCount)
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
