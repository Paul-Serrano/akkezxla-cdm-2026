<?php

namespace App\Livewire;

use App\Enums\WinamaxBetStatus;
use App\Models\Bet;
use App\Models\Game;
use App\Models\User;
use App\Models\WinamaxBet;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class WinamaxBetSummary extends Component
{
    public ?int $consensusGameId = null;
    public array $consensusRows = [];
    public ?string $consensusGameTitle = null;

    public function openConsensusModal(int $gameId): void
    {
        abort_unless(auth()->user()?->isAkkezxla(), 403);

        $game = Game::with(['homeTeam', 'awayTeam'])->findOrFail($gameId);

        $users = User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', User::ROLE_AKKEZXLA))
            ->orderByRaw("COALESCE(NULLIF(alias, ''), name) asc")
            ->get(['id', 'name', 'alias']);

        $betsByUser = Bet::query()
            ->where('gameId', $gameId)
            ->whereIn('userId', $users->pluck('id'))
            ->get(['userId', 'scoreHome', 'scoreAway'])
            ->keyBy('userId');

        $this->consensusRows = $users->map(function ($user) use ($betsByUser) {
            $bet = $betsByUser->get($user->id);
            $hasBet = $bet && $bet->scoreHome !== null && $bet->scoreAway !== null;

            return [
                'user' => $user->alias ?: $user->name,
                'bet' => $hasBet ? ((int) $bet->scoreHome . ' - ' . (int) $bet->scoreAway) : null,
                'hasBet' => $hasBet,
            ];
        })->values()->all();

        $this->consensusGameId = $gameId;
        $this->consensusGameTitle = $game->homeTeam->shortName . ' vs ' . $game->awayTeam->shortName;
    }

    public function closeConsensusModal(): void
    {
        $this->consensusGameId = null;
        $this->consensusRows = [];
        $this->consensusGameTitle = null;
    }

    public function render()
    {
        $bets = WinamaxBet::query()
            ->with([
                'games.homeTeam',
                'games.awayTeam',
                'user:id,alias,name',
            ])
            ->orderBy('matchdayPage')
            ->get();

        $totalAmount = $bets->sum(fn (WinamaxBet $bet) => (float) $bet->amountBet);
        $totalWonEarnings = $bets
            ->filter(fn (WinamaxBet $bet) => $bet->status === WinamaxBetStatus::Won)
            ->sum(fn (WinamaxBet $bet) => (float) ($bet->earning ?? 0));
        $netEarned = $totalWonEarnings - $totalAmount;

        return view('livewire.winamax-bet-summary', [
            'bets' => $bets,
            'totalAmount' => $totalAmount,
            'totalWonEarnings' => $totalWonEarnings,
            'netEarned' => $netEarned,
            'placedCount' => $bets->where('status', WinamaxBetStatus::Placed)->count(),
            'wonCount' => $bets->where('status', WinamaxBetStatus::Won)->count(),
            'lostCount' => $bets->where('status', WinamaxBetStatus::Lost)->count(),
            'pendingCount' => $bets->where('status', WinamaxBetStatus::Pending)->count(),
            'consensusRows' => $this->consensusRows,
            'consensusGameTitle' => $this->consensusGameTitle,
        ]);
    }
}
