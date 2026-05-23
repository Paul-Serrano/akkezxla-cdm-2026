<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    public $timestamps = false;

    protected $table = 'player';

    protected $fillable = [
        'apiId',
        'name',
        'dateOfBirth',
        'role',
        'teamId',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class, 'teamId');
    }
}
