<?php

namespace App\Enums;

use App\Models\Game;
use Carbon\Carbon;

enum GameStatus: string
{
    case Future  = 'future';
    case Ongoing = 'ongoing';
    case Ended   = 'ended';

    public static function fromGame(Game $game): self
    {
        if ($game->scoreHome !== null && $game->scoreAway !== null) {
            return self::Ended;
        }

        if (Carbon::parse($game->startDate)->isPast()) {
            return self::Ongoing;
        }

        return self::Future;
    }

    public function label(): string
    {
        return match ($this) {
            self::Future  => 'Upcoming',
            self::Ongoing => 'Live',
            self::Ended   => 'Ended',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Future  => 'badge-info',
            self::Ongoing => 'badge-warning animate-pulse',
            self::Ended   => 'badge-neutral',
        };
    }

    public function textClass(): string
    {
        return match ($this) {
            self::Future  => 'text-sky-500',
            self::Ongoing => 'text-amber-500',
            self::Ended   => 'text-base-content/40',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Future  => 'o-clock',
            self::Ongoing => 'o-signal',
            self::Ended   => 'o-check-badge',
        };
    }
}
