<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Standing extends Model
{
    public $timestamps = false;

    protected $table = 'standing';

    protected $fillable = [
        'apiId',
        'name',
    ];

    public function teams()
    {
        return $this->hasMany(Team::class, 'standingId');
    }
}
