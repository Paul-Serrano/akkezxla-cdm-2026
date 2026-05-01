<?php

namespace App\Livewire;

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

        return view('livewire.group', compact('teams'));
    }
}
