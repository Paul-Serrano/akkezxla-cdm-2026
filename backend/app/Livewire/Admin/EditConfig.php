<?php

namespace App\Livewire\Admin;

use App\Enums\ConfigKey;
use App\Models\Config;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class EditConfig extends Component
{
    public int $pointsSuperWin;
    public int $pointsWin;
    public int $pointsScorer;
    public int $totalPlayerBet;

    public bool $saved = false;

    public function mount(): void
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $this->pointsSuperWin = Config::get(ConfigKey::PointsSuperWin);
        $this->pointsWin      = Config::get(ConfigKey::PointsWin);
        $this->pointsScorer   = Config::get(ConfigKey::PointsScorer);
        $this->totalPlayerBet = Config::get(ConfigKey::TotalPlayerBet);
    }

    public function save(): void
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $this->validate([
            'pointsSuperWin' => ['required', 'integer', 'min:0', 'max:99'],
            'pointsWin'      => ['required', 'integer', 'min:0', 'max:99'],
            'pointsScorer'   => ['required', 'integer', 'min:0', 'max:99'],
            'totalPlayerBet' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        Config::set(ConfigKey::PointsSuperWin, $this->pointsSuperWin);
        Config::set(ConfigKey::PointsWin,      $this->pointsWin);
        Config::set(ConfigKey::PointsScorer,   $this->pointsScorer);
        Config::set(ConfigKey::TotalPlayerBet, $this->totalPlayerBet);

        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.admin.edit-config', [
            'keys' => ConfigKey::cases(),
        ]);
    }
}
