<?php

namespace App\Enums;

enum PlayerPosition: string
{
    case Goalkeeper = 'goalkeeper';
    case RightBack = 'right_back';
    case CentreBack = 'centre_back';
    case LeftBack = 'left_back';
    case DefensiveMidfield = 'defensive_midfield';
    case OffensiveMidfield = 'offensive_midfield';
    case Attacker = 'attacker';
    case Other = 'other';

    public static function fromRole(?string $role): self
    {
        $value = strtolower(trim((string) $role));

        if ($value === '' || str_contains($value, 'goalkeeper') || $value === 'gk') {
            return self::Goalkeeper;
        }

        if (str_contains($value, 'right-back') || str_contains($value, 'right back') || $value === 'rb' || $value === 'rwb') {
            return self::RightBack;
        }

        if (str_contains($value, 'centre-back') || str_contains($value, 'center-back') || str_contains($value, 'centre back') || str_contains($value, 'center back') || $value === 'cb') {
            return self::CentreBack;
        }

        if (str_contains($value, 'left-back') || str_contains($value, 'left back') || $value === 'lb' || $value === 'lwb') {
            return self::LeftBack;
        }

        if (str_contains($value, 'defensive midfield') || str_contains($value, 'defensive midfielder') || str_contains($value, 'holding midfielder') || $value === 'dm' || $value === 'cdm') {
            return self::DefensiveMidfield;
        }

        if (str_contains($value, 'attacking midfield') || str_contains($value, 'attacking midfielder') || str_contains($value, 'offensive midfield') || str_contains($value, 'offensive midfielder') || str_contains($value, 'central midfield') || str_contains($value, 'midfield') || $value === 'cm' || $value === 'cam') {
            return self::OffensiveMidfield;
        }

        if (str_contains($value, 'winger') || str_contains($value, 'forward') || str_contains($value, 'striker') || str_contains($value, 'attacker')) {
            return self::Attacker;
        }

        return self::Other;
    }

    public function rank(): int
    {
        return match ($this) {
            self::Goalkeeper => 0,
            self::RightBack => 1,
            self::CentreBack => 2,
            self::LeftBack => 3,
            self::DefensiveMidfield => 4,
            self::OffensiveMidfield => 5,
            self::Attacker => 6,
            self::Other => 7,
        };
    }
}
