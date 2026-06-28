<?php

namespace App\Livewire;

use App\Enums\GameStage;
use App\Enums\WinamaxBetStatus;
use App\Models\Bet;
use App\Models\Game;
use App\Models\Standing;
use App\Models\User;
use App\Models\WinamaxBet;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class MatchDay extends Component
{
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

        $this->pushChunkedPages(
            $pages,
            $this->gamesForStage($games, GameStage::GroupStage),
            GameStage::GroupStage->standardGamesPerPage()
        );

        $last32Games = $this->gamesForStage($games, GameStage::Last32);
        if ($last32Games->isNotEmpty()) {
            $pages->push($last32Games->take(1)->values());
            $this->pushChunkedPages($pages, $last32Games->slice(1)->values(), GameStage::Last32->standardGamesPerPage());
        }

        $this->pushChunkedPages(
            $pages,
            $this->gamesForStage($games, GameStage::Last16),
            GameStage::Last16->standardGamesPerPage()
        );

        $this->pushChunkedPages(
            $pages,
            $this->gamesForStage($games, GameStage::QuarterFinals),
            GameStage::QuarterFinals->standardGamesPerPage()
        );

        $this->pushChunkedPages(
            $pages,
            $this->gamesForStage($games, GameStage::SemiFinals),
            GameStage::SemiFinals->standardGamesPerPage()
        );

        $knownStageValues = collect(GameStage::cases())
            ->map(fn (GameStage $stage) => $stage->value)
            ->all();

        $otherStageGames = $games
            ->filter(fn (Game $game) => !in_array($game->stage, $knownStageValues, true))
            ->values();

        $this->pushChunkedPages($pages, $otherStageGames, 3);

        $finalPageGames = $games
            ->filter(function (Game $game) {
                $stage = GameStage::fromValue($game->stage);

                return $stage?->isCombinedFinalPageStage() ?? false;
            })
            ->values();

        if ($finalPageGames->isNotEmpty()) {
            $pages->push($finalPageGames);
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

    private function gamesForStage(Collection $games, GameStage $stage): Collection
    {
        return $games
            ->filter(fn (Game $game) => GameStage::fromValue($game->stage) === $stage)
            ->values();
    }

    private function pushChunkedPages(Collection $pages, Collection $games, int $gamesPerPage): void
    {
        if ($gamesPerPage <= 0 || $games->isEmpty()) {
            return;
        }

        foreach ($games->chunk($gamesPerPage) as $chunk) {
            $pages->push($chunk->values());
        }
    }

    private function pageMeetsWinamaxRequirements(Collection $pageGames): bool
    {
        if ($pageGames->isEmpty()) {
            return false;
        }

        $count = $pageGames->count();
        $stage = GameStage::fromValue($pageGames->first()?->stage);

        if ($stage === GameStage::GroupStage) {
            return $count === GameStage::GroupStage->standardGamesPerPage();
        }

        if ($stage === GameStage::Last32 && $this->isFirstLast32Page()) {
            return $count === 1;
        }

        if ($stage === null) {
            return $count >= 1 && $count <= 3;
        }

        return $count >= 1 && $count <= $stage->standardGamesPerPage();
    }

    private function isFirstLast32Page(): bool
    {
        $currentPageIndex = max(0, $this->matchday - 1);

        $firstLast32PageIndex = $this->gamesByPage->search(function (Collection $games) {
            $stage = GameStage::fromValue($games->first()?->stage);

            return $stage === GameStage::Last32;
        });

        return $firstLast32PageIndex !== false && $currentPageIndex === $firstLast32PageIndex;
    }

    private function winamaxExpectedGamesText(Collection $pageGames): string
    {
        if ($pageGames->isEmpty()) {
            return 'This page has no games.';
        }

        $stage = GameStage::fromValue($pageGames->first()?->stage);

        if ($stage === GameStage::GroupStage) {
            return 'GROUP_STAGE pages require exactly 4 games.';
        }

        if ($stage === GameStage::Last32 && $this->isFirstLast32Page()) {
            return 'The first LAST_32 page requires exactly 1 game.';
        }

        if ($stage === null) {
            return 'This page allows up to 3 games.';
        }

        return sprintf(
            '%s pages allow up to %d games (the last page can be incomplete).',
            $stage->value,
            $stage->standardGamesPerPage()
        );
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
            'winamaxExpectedGamesText' => $this->winamaxExpectedGamesText($pageGames),
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
