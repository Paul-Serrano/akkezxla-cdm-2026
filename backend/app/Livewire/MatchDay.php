<?php

namespace App\Livewire;

use App\Models\Bet;
use App\Models\Game;
use App\Models\Standing;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class MatchDay extends Component
{
    public int $matchday;
    protected $gamesByDay;

    public ?int $editGameId = null;
    public ?int $editScoreHome = null;
    public ?int $editScoreAway = null;

    public ?int $consensusGameId = null;
    public array $consensusRows = [];
    public ?string $consensusGameTitle = null;

    public $saved;

    public function boot(): void
    {
        $this->loadGamesByDay();
    }

    private function loadGamesByDay(): void
    {
        $this->gamesByDay = Game::with(['homeTeam.standing', 'awayTeam.standing'])
            ->whereNotNull('homeTeamId')
            ->whereNotNull('awayTeamId')
            ->orderBy('startDate')
            ->get()
            ->groupBy(fn ($g) => \Carbon\Carbon::parse($g->startDate)->toDateString())
            ->values();

        if (isset($this->matchday)) {
            $this->matchday = min(max(1, $this->matchday), max(1, $this->gamesByDay->count()));
        }
    }

    public function mount(?int $matchday = null): void
    {
        if ($matchday !== null) {
            $this->matchday = $matchday;
            return;
        }

        // Default: first day that still has at least one unplayed game.
        // Fall back to the last day if everything is already played.
        $groups = $this->gamesByDay;

        $this->matchday = $groups->count() ?: 1; // fallback = last day
        foreach ($groups as $index => $dayGames) {
            if ($dayGames->whereNull('scoreHome')->isNotEmpty()) {
                $this->matchday = $index + 1;
                break;
            }
        }
    }

    public function refreshGames(): void
    {
        $this->loadGamesByDay();
    }

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

        $this->loadGamesByDay();
        $this->cancelEditScore();
    }

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
        $games = $this->gamesByDay;

        // $matchday is 1-based index into date groups
        $dayGames = $games->get($this->matchday - 1, collect());
        $totalDays = $games->count();
        $editGame = $dayGames->firstWhere('id', $this->editGameId);
        $safeTotalDays = max(1, $totalDays);
        $date = $dayGames->first()?->startDate
            ? \Carbon\Carbon::parse($dayGames->first()->startDate)->format('l d F Y')
            : null;

        return view('livewire.match-day', [
            'games'            => $dayGames,
            'hasGames'         => $dayGames->isNotEmpty(),
            'editGame'         => $editGame,
            'editHomeCrest'    => $editGame?->homeTeam?->crest ?? '',
            'editAwayCrest'    => $editGame?->awayTeam?->crest ?? '',
            'editHomeAlt'      => $editGame?->homeTeam?->shortName ?? 'Home',
            'editAwayAlt'      => $editGame?->awayTeam?->shortName ?? 'Away',
            'date'             => $date,
            'totalDays'        => $totalDays,
            'previousMatchday' => max(1, $this->matchday - 1),
            'nextMatchday'     => min($safeTotalDays, $this->matchday + 1),
            'isFirstDay'       => $this->matchday <= 1,
            'isLastDay'        => $this->matchday >= $safeTotalDays,
            'consensusRows'    => $this->consensusRows,
            'consensusGameTitle' => $this->consensusGameTitle,
        ]);
    }
}
