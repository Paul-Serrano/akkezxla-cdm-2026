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
    private const COLUMNS = [
        'points' => ['label' => 'Points', 'icon' => 'o-star', 'color' => 'text-amber-500'],
        'superWins' => ['label' => 'Exact', 'icon' => 'o-check-badge', 'color' => 'text-amber-500'],
        'wins' => ['label' => 'Result', 'icon' => 'o-check-circle', 'color' => 'text-emerald-500'],
        'bets' => ['label' => 'Bets', 'icon' => 'o-bookmark', 'color' => 'text-sky-500'],
        'pointsPerBet' => ['label' => 'Pts/Bet', 'icon' => 'o-calculator', 'color' => 'text-violet-500'],
        'alias' => ['label' => 'Name', 'icon' => 'o-user', 'color' => 'text-base-content'],
    ];

    public string $sortBy = 'points';
    public string $sortDir = 'desc';
    public string $filterRole = '';

    public function isSelectedRole(string $roleName): bool
    {
        return $this->filterRole === $roleName;
    }

    public function allRoleFilterClass(): string
    {
        $base = 'btn btn-sm gap-1 transition-all';

        return $this->filterRole === ''
            ? $base . ' btn-neutral shadow-md'
            : $base . ' btn-outline';
    }

    public function roleFilterClass(string $roleName): string
    {
        $base = 'btn btn-sm gap-1 transition-all border-2';

        return $this->isSelectedRole($roleName)
            ? $base . ' shadow-md text-white'
            : $base . ' btn-outline';
    }

    public function roleFilterStyle(string $roleName, ?string $color): string
    {
        $safeColor = $color ?? '#000';

        if ($this->isSelectedRole($roleName)) {
            return "background-color: {$safeColor}; border-color: {$safeColor}; color: #fff;";
        }

        return "background-color: transparent; border-color: {$safeColor}; color: {$safeColor};";
    }

    public function sortButtonClass(string $key): string
    {
        $base = 'btn btn-sm gap-1 transition-all';

        return $this->sortBy === $key
            ? $base . ' btn-neutral shadow-md'
            : $base . ' btn-outline';
    }

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
                'name'         => $user->name,
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
            'columns'     => self::COLUMNS,
            'col'         => $this->sortBy,
            'isDesc'      => $this->sortDir === 'desc',
            'ptsSuperWin' => $ptsSuperWin,
            'ptsWin'      => $ptsWin,
            'ptsScorer'   => Config::get(ConfigKey::PointsScorer),
            'allRoles'    => Role::where('name', '!=', 'admin')->orderBy('label')->get(['name', 'label', 'color']),
            'currentUserId' => Auth::id(),
        ]);
    }
}
