<?php

namespace App\Enums;

enum BetStatus: string
{
    case Placed    = 'placed';
    case NotPlaced = 'not_placed';

    public function label(): string
    {
        return match ($this) {
            self::Placed    => 'Bet placed',
            self::NotPlaced => 'No bet',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Placed    => 'badge-success',
            self::NotPlaced => 'badge-ghost',
        };
    }

    public function textClass(): string
    {
        return match ($this) {
            self::Placed    => 'text-emerald-500',
            self::NotPlaced => 'text-base-content/40',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Placed    => 'o-bookmark-solid',
            self::NotPlaced => 'o-bookmark',
        };
    }
}
