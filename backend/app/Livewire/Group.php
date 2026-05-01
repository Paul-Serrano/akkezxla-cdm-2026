<?php

namespace App\Livewire;

use App\Models\Game;
use App\Models\Standing;
use App\Models\Team;
use Livewire\Component;

class Group extends Component
{
    public Standing $standing;

    public function mount(Standing $standing): void
    {
        $this->standing = $standing;
    }

    public function render()
    {
        $teams = Team::where('standingId', $this->standing->id)
            ->orderBy('rank')
            ->get();

        $teamIds = $teams->pluck('id');

        // All played games involving these teams
        $games = Game::whereNotNull('scoreHome')
            ->whereNotNull('scoreAway')
            ->where(function ($q) use ($teamIds) {
                $q->whereIn('homeTeamId', $teamIds)
                  ->orWhereIn('awayTeamId', $teamIds);
            })
            ->get();

        // Compute stats per team — plain PHP array so nested mutation works
        $stats = [];
        foreach ($teams as $team) {
            $stats[$team->id] = [
                'played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0,
                'gf'     => 0, 'ga'  => 0, 'pts'   => 0,
            ];
        }

        foreach ($games as $game) {
            $h = $game->homeTeamId;
            $a = $game->awayTeamId;
            $sh = (int) $game->scoreHome;
            $sa = (int) $game->scoreAway;

            if (isset($stats[$h])) {
                $stats[$h]['played']++;
                $stats[$h]['gf'] += $sh;
                $stats[$h]['ga'] += $sa;
                if ($sh > $sa)      { $stats[$h]['won']++;   $stats[$h]['pts'] += 3; }
                elseif ($sh === $sa){ $stats[$h]['drawn']++;  $stats[$h]['pts'] += 1; }
                else                { $stats[$h]['lost']++; }
            }

            if (isset($stats[$a])) {
                $stats[$a]['played']++;
                $stats[$a]['gf'] += $sa;
                $stats[$a]['ga'] += $sh;
                if ($sa > $sh)      { $stats[$a]['won']++;   $stats[$a]['pts'] += 3; }
                elseif ($sh === $sa){ $stats[$a]['drawn']++;  $stats[$a]['pts'] += 1; }
                else                { $stats[$a]['lost']++; }
            }
        }

        // Sort by pts desc, then goal diff desc, then gf desc
        $teamsWithStats = $teams->map(fn($team) => [
            'team'  => $team,
            'stats' => $stats[$team->id],
        ])->sortByDesc(fn($row) => [
            $row['stats']['pts'],
            $row['stats']['gf'] - $row['stats']['ga'],
            $row['stats']['gf'],
        ])->values();

        return view('livewire.group', compact('teamsWithStats'));
    }
}
