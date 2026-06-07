<?php

namespace App\Enums;

enum WinamaxBetStatus: string
{
    case Pending = 'pending';
    case Placed = 'placed';
    case Won = 'won';
    case Lost = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Placed => 'Placed',
            self::Won => 'Won',
            self::Lost => 'Lost',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'badge-ghost',
            self::Placed => 'badge-info',
            self::Won => 'badge-success',
            self::Lost => 'badge-error',
        };
    }

    public function locksGameBets(): bool
    {
        return $this !== self::Pending;
    }
}
