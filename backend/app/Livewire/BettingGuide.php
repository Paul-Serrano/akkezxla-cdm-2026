<?php

namespace App\Livewire;

use App\Enums\ConfigKey;
use App\Models\Config;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class BettingGuide extends Component
{
    public int $pointsSuperWin;
    public int $pointsWin;

    public function mount(): void
    {
        $this->pointsSuperWin = Config::get(ConfigKey::PointsSuperWin);
        $this->pointsWin = Config::get(ConfigKey::PointsWin);
    }

    public function render()
    {
        return view('betting-guide');
    }
}
