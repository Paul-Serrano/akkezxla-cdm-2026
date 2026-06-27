<?php

namespace App\Livewire;

use App\Enums\WinamaxBetStatus;
use App\Models\Bet;
use App\Models\Game;
use App\Models\Standing;
use App\Models\User;
use App\Models\WinamaxBet;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class MatchDay extends Component
{
    private const GROUP_STAGE = 'GROUP_STAGE';
    private const GROUP_STAGE_GAMES_PER_PAGE = 4;
    private const OTHER_STAGE_GAMES_PER_PAGE = 3;

    public int $matchday;
    protected $gamesByPage;

    public ?int $editGameId = null;
    public ?int $editScoreHome = null;
    public ?int $editScoreAway = null;

    public ?int $consensusGameId = null;
    public array $consensusRows = [];
    public ?string $consensusGameTitle = null;

    public ?int $winamaxBetId = null;
    public string $winamaxTotalOdds = '';
    public string $winamaxAmountBet = '';
    public string $winamaxEarning = '';
    public string $winamaxStatus = WinamaxBetStatus::Pending->value;
    public bool $winamaxSaved = false;
    public int $betRenderNonce = 0;

    public $saved;

    public function boot(): void
    {
        $this->loadGamesByPage();
    }

    private function loadGamesByPage(): void
    {
        $games = Game::with(['homeTeam.standing', 'awayTeam.standing'])
            ->whereNotNull('homeTeamId')
            ->whereNotNull('awayTeamId')
            ->orderBy('startDate')
            ->get();

        $pages = collect();
        $currentPage = collect();
        $currentStage = null;
        $currentLimit = 0;

        foreach ($games as $game) {
            $gameStage = $game->stage;
            $gameLimit = $this->gamesPerPageForStage($gameStage);

            if ($currentPage->isEmpty()) {
                $currentStage = $gameStage;
                $currentLimit = $gameLimit;
            }

            if (!$currentPage->isEmpty() && $gameStage !== $currentStage) {
                $pages->push($currentPage->values());
                $currentPage = collect();
                $currentStage = $gameStage;
                $currentLimit = $gameLimit;
            }

            $currentPage->push($game);

            if ($currentPage->count() === $currentLimit) {
                $pages->push($currentPage->values());
                $currentPage = collect();
                $currentStage = null;
                $currentLimit = 0;
            }
        }

        if ($currentPage->isNotEmpty()) {
            $pages->push($currentPage->values());
        }

        $this->gamesByPage = $pages->values();

        if (isset($this->matchday)) {
            $this->matchday = min(max(1, $this->matchday), max(1, $this->gamesByPage->count()));
        }
    }

    public function mount(?int $matchday = null): void
    {
        if ($matchday !== null) {
            $this->matchday = min(max(1, $matchday), max(1, $this->gamesByPage->count()));
        } else {
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

        $this->loadWinamaxBetForCurrentPage();
    }

    #[On('bet-placed')]
    public function refreshGames(): void
    {
        $this->loadGamesByPage();
        $this->loadWinamaxBetForCurrentPage();
    }

    public function updatedMatchday(): void
    {
        $this->matchday = min(max(1, $this->matchday), max(1, $this->gamesByPage->count()));
        $this->loadWinamaxBetForCurrentPage();
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

    public function saveWinamaxBet(): void
    {
        abort_unless(auth()->user()?->isWinamax(), 403);

        $pageGames = $this->currentPageGames();
        if (!$this->pageMeetsWinamaxRequirements($pageGames)) {
            $this->addError('winamaxBet', 'This page does not match Winamax game-count requirements for its stage.');
            return;
        }

        $validated = $this->validate([
            'winamaxTotalOdds' => ['required', 'numeric', 'min:1'],
            'winamaxAmountBet' => ['required', 'numeric', 'min:0.01'],
            'winamaxEarning' => ['required', 'numeric', 'min:0'],
            'winamaxStatus' => ['required', 'in:pending,placed,won,lost'],
        ]);

        $bet = WinamaxBet::firstOrNew(['matchdayPage' => $this->matchday]);
        $bet->totalOdds = (float) $validated['winamaxTotalOdds'];
        $bet->amountBet = (float) $validated['winamaxAmountBet'];
        $bet->earning = (float) $validated['winamaxEarning'];
        $bet->status = WinamaxBetStatus::from($validated['winamaxStatus']);
        $bet->userId = auth()->id();
        $bet->save();

        $bet->games()->sync($pageGames->pluck('id')->all());

        $this->refreshGames();
        $this->winamaxBetId = $bet->id;
        $this->winamaxSaved = true;
        $this->betRenderNonce++;
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

    private function currentPageGames()
    {
        return $this->gamesByPage->get($this->matchday - 1, collect());
    }

    private function gamesPerPageForStage(?string $stage): int
    {
        return $stage === self::GROUP_STAGE
            ? self::GROUP_STAGE_GAMES_PER_PAGE
            : self::OTHER_STAGE_GAMES_PER_PAGE;
    }

    private function pageMeetsWinamaxRequirements($pageGames): bool
    {
        if ($pageGames->isEmpty()) {
            return false;
        }

        $stage = $pageGames->first()?->stage;
        $expectedCount = $this->gamesPerPageForStage($stage);

        if ($stage === self::GROUP_STAGE) {
            return $pageGames->count() === $expectedCount;
        }

        return $pageGames->count() >= 1 && $pageGames->count() <= $expectedCount;
    }

    private function loadWinamaxBetForCurrentPage(): void
    {
        $this->winamaxSaved = false;
        $this->resetErrorBag('winamaxBet');

        $bet = WinamaxBet::query()
            ->where('matchdayPage', $this->matchday)
            ->first();

        if (!$bet) {
            $this->winamaxBetId = null;
            $this->winamaxTotalOdds = '';
            $this->winamaxAmountBet = '';
            $this->winamaxEarning = '';
            $this->winamaxStatus = WinamaxBetStatus::Pending->value;
            return;
        }

        $this->winamaxBetId = $bet->id;
        $this->winamaxTotalOdds = number_format((float) $bet->totalOdds, 2, '.', '');
        $this->winamaxAmountBet = number_format((float) $bet->amountBet, 2, '.', '');
        $this->winamaxEarning = $bet->earning !== null
            ? number_format((float) $bet->earning, 2, '.', '')
            : '';
        $this->winamaxStatus = $bet->status?->value ?? WinamaxBetStatus::Pending->value;
    }

    public function render()
    {
        $games = $this->gamesByPage;

        // $matchday is 1-based index into pages
        $pageGames = $this->currentPageGames();
        $pageStage = $pageGames->first()?->stage;
        $pageMeetsWinamaxRequirements = $this->pageMeetsWinamaxRequirements($pageGames);
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
            'canManageWinamaxBet' => auth()->user()?->isWinamax() ?? false,
            'pageMeetsWinamaxRequirements' => $pageMeetsWinamaxRequirements,
            'winamaxExpectedGamesText' => $pageStage === self::GROUP_STAGE
                ? 'GROUP_STAGE pages require exactly 4 games.'
                : 'Non-GROUP_STAGE pages allow up to 3 games (the last page can be incomplete).',
            'winamaxGamesSummary' => $pageGames->map(function ($game) {
                $home = $game->homeTeam?->shortName ?? 'Home';
                $away = $game->awayTeam?->shortName ?? 'Away';

                return $home . ' vs ' . $away;
            })->all(),
            'betRenderNonce' => $this->betRenderNonce,
            'winamaxStatusOptions' => WinamaxBetStatus::cases(),
            'consensusRows'    => $this->consensusRows,
            'consensusGameTitle' => $this->consensusGameTitle,
        ]);
    }
}
