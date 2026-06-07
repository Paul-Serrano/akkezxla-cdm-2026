<?php

namespace App\Models;

use App\Enums\WinamaxBetStatus;
use Illuminate\Database\Eloquent\Model;

class WinamaxBet extends Model
{
    protected $table = 'winamax_bet';

    protected $fillable = [
        'matchdayPage',
        'totalOdds',
        'amountBet',
        'earning',
        'status',
        'userId',
    ];

    protected function casts(): array
    {
        return [
            'status' => WinamaxBetStatus::class,
            'totalOdds' => 'decimal:2',
            'amountBet' => 'decimal:2',
            'earning' => 'decimal:2',
        ];
    }

    public function locksGameBets(): bool
    {
        return $this->status?->locksGameBets() ?? false;
    }

    public function games()
    {
        return $this->belongsToMany(Game::class, 'winamax_bet_game', 'winamaxBetId', 'gameId')
            ->orderBy('startDate');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }
}
