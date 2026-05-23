<?php

namespace App\Livewire;

use App\Models\Standing;
use App\Models\Team;
use Livewire\Component;

class Group extends Component
{
    public Standing $standing;
    public ?int $selectedTeamId = null;

    public function mount(Standing $standing): void
    {
        $this->standing = $standing;
    }

    public function showPlayers(int $teamId): void
    {
        $existsInGroup = Team::where('standingId', $this->standing->id)
            ->where('id', $teamId)
            ->exists();

        if (! $existsInGroup) {
            return;
        }

        $this->selectedTeamId = $teamId;
    }

    public function showRanking(): void
    {
        $this->selectedTeamId = null;
    }

    public function render()
    {
        $teams = Team::where('standingId', $this->standing->id)
            ->orderByRaw('COALESCE("standingPosition", "rank", 9999) asc')
            ->get();

        $selectedTeam = null;

        if ($this->selectedTeamId !== null) {
            $selectedTeam = $teams->firstWhere('id', $this->selectedTeamId);

            if (! $selectedTeam) {
                $this->selectedTeamId = null;
            }
        }

        // Use persisted standing stats imported from football-data API.
        $teamsWithStats = $teams->map(function ($team) {
            $played = (int) ($team->standingPlayedGames ?? 0);
            $won = (int) ($team->standingWon ?? 0);
            $drawn = (int) ($team->standingDraw ?? 0);
            $lost = (int) ($team->standingLost ?? 0);
            $gf = (int) ($team->standingGoalsFor ?? 0);
            $ga = (int) ($team->standingGoalsAgainst ?? 0);
            $pts = (int) ($team->standingPoints ?? 0);
            $gd = (int) ($team->standingGoalDifference ?? ($gf - $ga));

            return [
                'team'       => $team,
                'position'   => (int) ($team->standingPosition ?? 0),
                'stats'      => [
                    'played' => $played,
                    'won' => $won,
                    'drawn' => $drawn,
                    'lost' => $lost,
                    'gf' => $gf,
                    'ga' => $ga,
                    'pts' => $pts,
                ],
                'gd'         => $gd,
                'gdLabel'    => ($gd > 0 ? '+' : '') . $gd,
                'gdClass'    => $gd > 0 ? 'text-emerald-500' : ($gd < 0 ? 'text-red-500' : ''),
                'gdClassRow' => $gd > 0 ? 'text-emerald-500' : ($gd < 0 ? 'text-red-500' : 'text-base-content/40'),
            ];
        })->sortBy(fn($row) => [
            $row['position'] > 0 ? $row['position'] : 9999,
            -$row['stats']['pts'],
            -$row['gd'],
            -$row['stats']['gf'],
            $row['team']->name,
        ])->values();

        return view('livewire.group', compact('teamsWithStats', 'selectedTeam'));
    }
}
