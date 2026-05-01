<?php

namespace App\Livewire;

use App\Enums\BetResult;
use App\Enums\BetStatus;
use App\Enums\GameStatus;
use App\Models\Bet;
use App\Models\Game;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PlaceBet extends Component
{
    #[Locked]
    public int $gameId;

    public ?int $scoreHome = null;
    public ?int $scoreAway = null;

    public bool $saved = false;

    public function mount(Game $game): void
    {
        $this->gameId = $game->id;

        if (Auth::check()) {
            $existing = Bet::where('gameId', $game->id)
                ->where('userId', Auth::id())
                ->first();

            if ($existing) {
                $this->scoreHome = $existing->scoreHome;
                $this->scoreAway = $existing->scoreAway;
            }
        }
    }

    public function save(): void
    {
        $game = Game::find($this->gameId);

        // Prevent betting on finished games
        abort_if(GameStatus::fromGame($game) === GameStatus::Ended, 403, 'Game already ended.');

        $this->validate([
            'scoreHome' => ['required', 'integer', 'min:0', 'max:99'],
            'scoreAway' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        Bet::updateOrCreate(
            [
                'gameId' => $this->gameId,
                'userId' => Auth::id(),
            ],
            [
                'scoreHome' => $this->scoreHome,
                'scoreAway' => $this->scoreAway,
                'bet'       => $this->scoreHome . '-' . $this->scoreAway,
            ]
        );

        $this->saved = true;
    }

    public function render()
    {
        $game = Game::find($this->gameId);

        $gameStatus = GameStatus::fromGame($game);
        $betStatus  = ($this->scoreHome !== null && $this->scoreAway !== null)
            ? BetStatus::Placed
            : BetStatus::NotPlaced;
        $betResult  = BetResult::compute($this->scoreHome, $this->scoreAway, $game);

        return view('livewire.place-bet', compact('gameStatus', 'betStatus', 'betResult'));
    }
}
