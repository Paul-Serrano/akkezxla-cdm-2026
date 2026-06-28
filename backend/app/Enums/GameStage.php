<?php

namespace App\Enums;

enum GameStage: string
{
    case GroupStage = 'GROUP_STAGE';
    case Last32 = 'LAST_32';
    case Last16 = 'LAST_16';
    case QuarterFinals = 'QUARTER_FINALS';
    case SemiFinals = 'SEMI_FINALS';
    case Final = 'FINAL';
    case ThirdPlace = 'THIRD_PLACE';

    public static function fromValue(?string $value): ?self
    {
        if ($value === null) {
            return null;
        }

        return self::tryFrom($value);
    }

    public function standardGamesPerPage(): int
    {
        return match ($this) {
            self::GroupStage => 4,
            self::Last32 => 3,
            self::Last16, self::QuarterFinals => 4,
            self::SemiFinals => 2,
            self::Final, self::ThirdPlace => 2,
        };
    }

    public function isCombinedFinalPageStage(): bool
    {
        return $this === self::Final || $this === self::ThirdPlace;
    }
}