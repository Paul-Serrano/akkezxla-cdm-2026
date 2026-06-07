<?php

namespace App\Livewire;

use App\Enums\BetResult;
use App\Enums\BetStatus;
use App\Enums\GameStatus;
use App\Enums\WinamaxBetStatus;
use App\Models\Bet;
use App\Models\Game;
use App\Models\WinamaxBet;
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

    private function isLockedByWinamax(Game $game): bool
    {
        $winamaxBet = WinamaxBet::query()
            ->whereHas('games', fn ($query) => $query->where('game.id', $game->id))
            ->first();

        if (!$winamaxBet) {
            return false;
        }

        return ($winamaxBet->status ?? WinamaxBetStatus::Pending)->locksGameBets();
    }

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
        abort_if($this->isLockedByWinamax($game), 403, 'Betting is locked for this game.');

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
        $this->dispatch('bet-placed');
    }

    public function render()
    {
        $game = Game::find($this->gameId);

        $gameStatus = GameStatus::fromGame($game);
        $betStatus  = ($this->scoreHome !== null && $this->scoreAway !== null)
            ? BetStatus::Placed
            : BetStatus::NotPlaced;
        $betResult  = BetResult::compute($this->scoreHome, $this->scoreAway, $game);
        $isLockedByWinamax = $this->isLockedByWinamax($game);

        return view('livewire.place-bet', compact('gameStatus', 'betStatus', 'betResult', 'isLockedByWinamax'));
    }
}
