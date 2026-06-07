<?php

namespace App\Livewire;

use App\Enums\WinamaxBetStatus;
use App\Models\WinamaxBet;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class WinamaxBetSummary extends Component
{
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
        ]);
    }
}
