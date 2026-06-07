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
    private const GAMES_PER_PAGE = 4;
    private const TOTAL_PAGES = 18;

    public int $matchday;
    protected $gamesByPage;

    public ?int $editGameId = null;
    public ?int $editScoreHome = null;
    public ?int $editScoreAway = null;

    public ?int $consensusGameId = null;
    public array $consensusRows = [];
    public ?string $consensusGameTitle = null;

    public $saved;

    public function boot(): void
    {
        $this->loadGamesByPage();
    }

    private function loadGamesByPage(): void
    {
        $this->gamesByPage = Game::with(['homeTeam.standing', 'awayTeam.standing'])
            ->whereNotNull('homeTeamId')
            ->whereNotNull('awayTeamId')
            ->orderBy('startDate')
            ->get()
            ->take(self::GAMES_PER_PAGE * self::TOTAL_PAGES)
            ->chunk(self::GAMES_PER_PAGE)
            ->values();

        while ($this->gamesByPage->count() < self::TOTAL_PAGES) {
            $this->gamesByPage->push(collect());
        }

        if (isset($this->matchday)) {
            $this->matchday = min(max(1, $this->matchday), max(1, $this->gamesByPage->count()));
        }
    }

    public function mount(?int $matchday = null): void
    {
        if ($matchday !== null) {
            $this->matchday = $matchday;
            return;
        }

        // Default: first page that still has at least one unplayed game.
        // Fall back to the last page if everything is already played.
        $groups = $this->gamesByPage;

        $this->matchday = $groups->count() ?: 1; // fallback = last page
        foreach ($groups as $index => $pageGames) {
            if ($pageGames->whereNull('scoreHome')->isNotEmpty()) {
                $this->matchday = $index + 1;
                break;
            }
        }
    }

    public function refreshGames(): void
    {
        $this->loadGamesByPage();
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

        $this->loadGamesByPage();
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
        $games = $this->gamesByPage;

        // $matchday is 1-based index into pages
        $pageGames = $games->get($this->matchday - 1, collect());
        $totalPages = $games->count();
        $editGame = $pageGames->firstWhere('id', $this->editGameId);
        $safeTotalPages = max(1, $totalPages);
        $date = $pageGames->first()?->startDate
            ? \Carbon\Carbon::parse($pageGames->first()->startDate)->format('l d F Y')
            : null;

        return view('livewire.match-day', [
            'games'            => $pageGames,
            'hasGames'         => $pageGames->isNotEmpty(),
            'editGame'         => $editGame,
            'editHomeCrest'    => $editGame?->homeTeam?->crest ?? '',
            'editAwayCrest'    => $editGame?->awayTeam?->crest ?? '',
            'editHomeAlt'      => $editGame?->homeTeam?->shortName ?? 'Home',
            'editAwayAlt'      => $editGame?->awayTeam?->shortName ?? 'Away',
            'date'             => $date,
            'totalPages'       => $totalPages,
            'previousMatchday' => max(1, $this->matchday - 1),
            'nextMatchday'     => min($safeTotalPages, $this->matchday + 1),
            'isFirstDay'       => $this->matchday <= 1,
            'isLastDay'        => $this->matchday >= $safeTotalPages,
            'consensusRows'    => $this->consensusRows,
            'consensusGameTitle' => $this->consensusGameTitle,
        ]);
    }
}
