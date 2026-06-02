<?php

namespace App\Enums;

enum PlayerPosition: string
{
    case Goalkeeper = 'goalkeeper';
    case Defense = 'defense';
    case Midfield = 'midfield';
    case Offence = 'offence';

    public static function fromRole(?string $role): self
    {
        $value = strtolower(trim((string) $role));

        if ($value === '' || str_contains($value, 'goalkeeper') || $value === 'gk') {
            return self::Goalkeeper;
        }

        if (
            str_contains($value, 'back') ||
            str_contains($value, 'centre-back') ||
            str_contains($value, 'center-back') ||
            str_contains($value, 'defender') ||
            str_contains($value, 'defence') ||
            str_contains($value, 'defense') ||
            $value === 'rb' ||
            $value === 'rwb' ||
            $value === 'cb' ||
            $value === 'lb' ||
            $value === 'lwb'
        ) {
            return self::Defense;
        }

        if (
            str_contains($value, 'midfield') ||
            str_contains($value, 'midfielder') ||
            $value === 'dm' ||
            $value === 'cdm' ||
            $value === 'cm' ||
            $value === 'cam'
        ) {
            return self::Midfield;
        }

        if (
            str_contains($value, 'winger') ||
            str_contains($value, 'forward') ||
            str_contains($value, 'striker') ||
            str_contains($value, 'attacker') ||
            str_contains($value, 'offence') ||
            str_contains($value, 'offense')
        ) {
            return self::Offence;
        }

        return self::Midfield;
    }

    public function rank(): int
    {
        return match ($this) {
            self::Goalkeeper => 0,
            self::Defense => 1,
            self::Midfield => 2,
            self::Offence => 3,
        };
    }
}
