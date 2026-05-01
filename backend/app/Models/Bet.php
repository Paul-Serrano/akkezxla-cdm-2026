<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    public $timestamps = false;

    protected $table = 'bet';

    protected $fillable = [
        'bet',
        'scoreHome',
        'scoreAway',
        'gameId',
        'userId',
        'playerId',
        'apiId',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class, 'gameId');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'userId');
    }
}
