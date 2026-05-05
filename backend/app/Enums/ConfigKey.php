<?php

namespace App\Enums;

enum ConfigKey: string
{
    case PointsSuperWin  = 'points_super_win';
    case PointsWin       = 'points_win';
    case PointsScorer    = 'points_scorer';
    case TotalPlayerBet  = 'total_player_bet';

    public function label(): string
    {
        return match ($this) {
            self::PointsSuperWin => 'Points — Exact score (Super Win)',
            self::PointsWin      => 'Points — Correct result (Win)',
            self::PointsScorer   => 'Points — Correct scorer',
            self::TotalPlayerBet => 'Max scorer bets per user per game',
        };
    }

    public function hint(): string
    {
        return match ($this) {
            self::PointsSuperWin => 'Awarded when the user guesses the exact scoreline',
            self::PointsWin      => 'Awarded when the user guesses the right outcome (win/draw/loss)',
            self::PointsScorer   => 'Awarded per correctly predicted goal scorer',
            self::TotalPlayerBet => 'How many goal scorers a user can bet on',
        };
    }

    public function default(): int
    {
        return match ($this) {
            self::PointsSuperWin => 3,
            self::PointsWin      => 1,
            self::PointsScorer   => 1,
            self::TotalPlayerBet => 3,
        };
    }
}
