<?php

namespace App\Livewire;

use App\Models\Standing;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Standings extends Component
{
    public function render()
    {
        $standings = Standing::whereNotNull('name')
            ->where('name', '!=', 'FIFA World Cup')
            ->orderBy('name')
            ->get();

        return view('livewire.standings', compact('standings'));
    }
}
