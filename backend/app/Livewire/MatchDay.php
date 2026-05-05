<?php

namespace App\Livewire;

use App\Models\Game;
use App\Models\Standing;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class MatchDay extends Component
{
    public int $matchday;

    public ?int $editGameId = null;
    public ?int $editScoreHome = null;
    public ?int $editScoreAway = null;

    public $saved;

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

    public function refreshGames(): void {}

    public function startEditScore(int $gameId): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $game = Game::findOrFail($gameId);
        $this->editGameId    = $gameId;
        $this->editScoreHome = $game->scoreHome;
        $this->editScoreAway = $game->scoreAway;
    }

    public function cancelEditScore(): void
    {
        $this->editGameId    = null;
        $this->editScoreHome = null;
        $this->editScoreAway = null;
    }

    public function saveScore(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $this->validate([
            'editScoreHome' => 'required|integer|min:0|max:99',
            'editScoreAway' => 'required|integer|min:0|max:99',
        ]);

        Game::findOrFail($this->editGameId)->update([
            'scoreHome' => $this->editScoreHome,
            'scoreAway' => $this->editScoreAway,
        ]);

        $game = Game::with('homeTeam')->findOrFail($this->editGameId);
        if ($game->homeTeam->standingId) {
            Standing::recalculate($game->homeTeam->standingId);
        }

        $this->cancelEditScore();
    }

    public function render()
    {
        // Games are ordered by date; we paginate by startDate buckets.
        // Since the DB has no matchday column we derive it by ordering date.
        $games = Game::with(['homeTeam.standing', 'awayTeam.standing'])
            ->whereNotNull('homeTeamId')
            ->whereNotNull('awayTeamId')
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
