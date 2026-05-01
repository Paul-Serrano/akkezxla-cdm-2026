<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    public $timestamps = false;

    protected $table = 'team';

    protected $fillable = [
        'apiId',
        'name',
        'shortName',
        'founded',
        'crest',
        'rank',
        'standingId',
    ];

    public function standing()
    {
        return $this->belongsTo(Standing::class, 'standingId');
    }
}
