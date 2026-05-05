<?php

namespace App\Livewire;

use App\Enums\BetResult;
use App\Enums\ConfigKey;
use App\Models\Bet;
use App\Models\Config;
use App\Models\Game;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Ranking extends Component
{
    public string $sortBy = 'points';
    public string $sortDir = 'desc';
    public string $filterRole = '';

    /** Toggle sort column; if same column, flip direction. */
    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'desc' ? 'asc' : 'desc';
        } else {
            $this->sortBy  = $column;
            $this->sortDir = 'desc';
        }
    }

    public function render()
    {
        $ptsSuperWin = Config::get(ConfigKey::PointsSuperWin);
        $ptsWin      = Config::get(ConfigKey::PointsWin);

        // Finished games (both scores set)
        $finishedGameIds = Game::whereNotNull('scoreHome')
            ->whereNotNull('scoreAway')
            ->pluck('id');

        // All bets on finished games, with game data
        $bets = Bet::with('game')
            ->whereIn('gameId', $finishedGameIds)
            ->whereNull('playerId')   // score bets only (not scorer bets)
            ->get();

        // Group bets by user
        // Winamax users only see other winamax/admin — admin sees everyone
        $baseQuery = User::with('roles');
        if ($this->filterRole !== '') {
            $baseQuery->whereHas('roles', fn($q) => $q->where('name', $this->filterRole));
        }

        $users = $baseQuery->get()->keyBy('id');

        $rows = $users->map(function (User $user) use ($bets, $ptsSuperWin, $ptsWin) {
            $userBets    = $bets->where('userId', $user->id);
            $betCount    = $userBets->count();
            $superWins   = 0;
            $wins        = 0;

            foreach ($userBets as $bet) {
                $result = BetResult::compute(
                    $bet->scoreHome,
                    $bet->scoreAway,
                    $bet->game
                );

                if ($result === BetResult::SuperWin) {
                    $superWins++;
                } elseif ($result === BetResult::Win) {
                    $wins++;
                }
            }

            $points     = ($superWins * $ptsSuperWin) + ($wins * $ptsWin);
            $pointsPerBet = $betCount > 0 ? round($points / $betCount, 2) : 0;

            return [
                'id'           => $user->id,
                'alias'        => $user->alias,
                'role'         => $user->roles->pluck('label')->join(', '),
                'bets'         => $betCount,
                'superWins'    => $superWins,
                'wins'         => $wins,
                'points'       => $points,
                'pointsPerBet' => $pointsPerBet,
            ];
        })->values();

        // Sort
        $sorted = $this->sortDir === 'desc'
            ? $rows->sortByDesc($this->sortBy)->values()
            : $rows->sortBy($this->sortBy)->values();

        // Rank (shared rank on tie for points)
        $ranked = $sorted->map(function ($row, $index) use ($sorted) {
            $row['rank'] = $index + 1;
            return $row;
        });

        return view('livewire.ranking', [
            'rows'        => $ranked,
            'ptsSuperWin' => $ptsSuperWin,
            'ptsWin'      => $ptsWin,
            'ptsScorer'   => Config::get(ConfigKey::PointsScorer),
            'allRoles'    => Role::orderBy('label')->get(['name', 'label', 'color']),
            'currentUserId' => Auth::id(),
        ]);
    }
}
