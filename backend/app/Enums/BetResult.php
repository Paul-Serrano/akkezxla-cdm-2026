<?php

namespace App\Enums;

use App\Models\Game;

enum BetResult: string
{
    case SuperWin = 'super_win';
    case Win      = 'win';
    case Lose     = 'lose';
    case Pending  = 'pending';

    public static function compute(?int $betHome, ?int $betAway, Game $game): self
    {
        if ($betHome === null || $betAway === null) {
            return self::Pending;
        }

        if ($game->scoreHome === null || $game->scoreAway === null) {
            return self::Pending;
        }

        $gHome = (int) $game->scoreHome;
        $gAway = (int) $game->scoreAway;

        if ($betHome === $gHome && $betAway === $gAway) {
            return self::SuperWin;
        }

        // Compare outcome: home win / draw / away win
        $betOutcome  = $betHome <=> $betAway;
        $gameOutcome = $gHome <=> $gAway;

        return $betOutcome === $gameOutcome ? self::Win : self::Lose;
    }

    public function label(): string
    {
        return match ($this) {
            self::SuperWin => 'Super Win!',
            self::Win      => 'Win',
            self::Lose     => 'Lose',
            self::Pending  => 'Pending',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::SuperWin => 'badge-warning text-amber-900 border-amber-400',
            self::Win      => 'badge-success',
            self::Lose     => 'badge-error',
            self::Pending  => 'badge-ghost',
        };
    }

    /** Tailwind text color for standalone use (icons, labels outside badges) */
    public function textClass(): string
    {
        return match ($this) {
            self::SuperWin => 'text-amber-500',
            self::Win      => 'text-emerald-500',
            self::Lose     => 'text-red-500',
            self::Pending  => 'text-base-content/40',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::SuperWin => 'o-star',
            self::Win      => 'o-check-circle',
            self::Lose     => 'o-x-circle',
            self::Pending  => 'o-clock',
        };
    }
}
