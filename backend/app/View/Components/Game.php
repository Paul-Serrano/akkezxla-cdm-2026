<?php

namespace App\View\Components;

use App\Enums\GameStatus;
use App\Models\Game as GameModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\View\Component;

class Game extends Component
{
    public \App\Models\Team $home;
    public \App\Models\Team $away;
    public GameStatus $gameStatus;
    public bool $played;
    public string $date;
    public ?string $group;
    public ?int $homeRank;
    public ?int $awayRank;
    public bool $canSeeConsensus;
    public array $consensus;
    public bool $isAdmin;

    public function __construct(public GameModel $game)
    {
        $this->home       = $game->homeTeam;
        $this->away       = $game->awayTeam;
        $this->gameStatus = GameStatus::fromGame($game);
        $this->played     = $this->gameStatus === GameStatus::Ended;
        $this->date       = Carbon::parse($game->startDate)->format('d M · H:i');

        $this->group    = $this->home->standing?->name ?? $this->away->standing?->name;
        $this->homeRank = $this->home->rank;
        $this->awayRank = $this->away->rank;

        $this->canSeeConsensus = auth()->check()
            && (auth()->user()->isAdmin() || auth()->user()->isWinamax());

        $this->consensus = $this->canSeeConsensus
            ? $game->consensus()
            : ['total' => 0, 'outcomes' => collect()];

        $this->isAdmin = auth()->check() && auth()->user()->isAdmin();
    }

    public function render()
    {
        return view('components.game');
    }
}
