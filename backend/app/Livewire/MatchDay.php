<?php

namespace App\Livewire;

use App\Models\Game;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class MatchDay extends Component
{
    public int $matchday;

    public function mount(int $matchday = 1): void
    {
        $this->matchday = $matchday;
    }

    public function render()
    {
        // Games are ordered by date; we paginate by startDate buckets.
        // Since the DB has no matchday column we derive it by ordering date.
        $games = Game::with(['homeTeam', 'awayTeam'])
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
