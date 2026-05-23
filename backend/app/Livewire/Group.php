<?php

namespace App\Livewire;

use App\Models\Game;
use App\Models\Standing;
use App\Models\Team;
use App\Services\StandingCalculatorService;
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
            ->orderBy('rank')
            ->get();

        $selectedTeam = null;

        if ($this->selectedTeamId !== null) {
            $selectedTeam = $teams->firstWhere('id', $this->selectedTeamId);

            if (! $selectedTeam) {
                $this->selectedTeamId = null;
            }
        }

        $teamIds = $teams->pluck('id');

        // All played games involving these teams
        $games = Game::whereNotNull('scoreHome')
            ->whereNotNull('scoreAway')
            ->where(function ($q) use ($teamIds) {
                $q->whereIn('homeTeamId', $teamIds)
                  ->orWhereIn('awayTeamId', $teamIds);
            })
            ->get();

        // Compute stats per team via shared service
        $stats = StandingCalculatorService::computeStats($teams, $games);

        // Sort by pts desc, then goal diff desc, then gf desc
        $teamsWithStats = $teams->map(function ($team) use ($stats) {
            $teamStats = $stats[$team->id];
            $gd = $teamStats['gf'] - $teamStats['ga'];

            return [
                'team'       => $team,
                'stats'      => $teamStats,
                'gd'         => $gd,
                'gdLabel'    => ($gd > 0 ? '+' : '') . $gd,
                'gdClass'    => $gd > 0 ? 'text-emerald-500' : ($gd < 0 ? 'text-red-500' : ''),
                'gdClassRow' => $gd > 0 ? 'text-emerald-500' : ($gd < 0 ? 'text-red-500' : 'text-base-content/40'),
            ];
        })->sortByDesc(fn($row) => [
            $row['stats']['pts'],
            $row['stats']['gf'] - $row['stats']['ga'],
            $row['stats']['gf'],
        ])->values();

        return view('livewire.group', compact('teamsWithStats', 'selectedTeam'));
    }
}
