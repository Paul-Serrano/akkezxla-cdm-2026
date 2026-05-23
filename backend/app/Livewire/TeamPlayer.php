<?php

namespace App\Livewire;

use App\Enums\PlayerPosition;
use App\Models\Player;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class TeamPlayer extends Component
{
    public Team $team;

    public function mount(Team $team): void
    {
        $this->team = $team;
    }

    public function render()
    {
        $players = Player::where('teamId', $this->team->id)->get();

        $rows = $this->buildRows($players);

        return view('livewire.team-player', [
            'rows' => $rows,
        ]);
    }

    private function buildRows(Collection $players): Collection
    {
        return $players
            ->map(function (Player $player): array {
                $position = PlayerPosition::fromRole($player->role);

                return [
                    'name' => $player->name,
                    'role' => $player->role,
                    'age' => $this->computeAge($player->dateOfBirth),
                    'positionRank' => $position->rank(),
                ];
            })
            ->sortBy([
                ['positionRank', 'asc'],
                ['name', 'asc'],
            ])
            ->values();
    }

    private function computeAge(?string $dateOfBirth): ?int
    {
        if ($dateOfBirth === null || trim($dateOfBirth) === '') {
            return null;
        }

        try {
            return Carbon::parse($dateOfBirth)->age;
        } catch (\Throwable) {
            return null;
        }
    }
}
