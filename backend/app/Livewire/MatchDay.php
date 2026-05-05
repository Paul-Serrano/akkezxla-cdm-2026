<?php

namespace App\Livewire;

use App\Models\Game;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class MatchDay extends Component
{
    public int $matchday;

    public function mount(?int $matchday = null): void
    {
        if ($matchday !== null) {
            $this->matchday = $matchday;
            return;
        }

        // Default: first day that still has at least one unplayed game.
        // Fall back to the last day if everything is already played.
        $groups = Game::orderBy('startDate')
            ->get()
            ->groupBy(fn ($g) => \Carbon\Carbon::parse($g->startDate)->toDateString())
            ->values();

        $this->matchday = $groups->count() ?: 1; // fallback = last day
        foreach ($groups as $index => $dayGames) {
            if ($dayGames->whereNull('scoreHome')->isNotEmpty()) {
                $this->matchday = $index + 1;
                break;
            }
        }
    }

    public function render()
    {
        // Games are ordered by date; we paginate by startDate buckets.
        // Since the DB has no matchday column we derive it by ordering date.
        $games = Game::with(['homeTeam.standing', 'awayTeam.standing'])
            ->orderBy('startDate')
            ->get()
            ->groupBy(fn ($g) => \Carbon\Carbon::parse($g->startDate)->toDateString())
            ->values();

        // $matchday is 1-based index into date groups
        $dayGames = $games->get($this->matchday - 1, collect());
        $totalDays = $games->count();
        $date = $dayGames->first()?->startDate
            ? \Carbon\Carbon::parse($dayGames->first()->startDate)->format('l d F Y')
            : null;

        return view('livewire.match-day', [
            'games'     => $dayGames,
            'date'      => $date,
            'totalDays' => $totalDays,
        ]);
    }
}
