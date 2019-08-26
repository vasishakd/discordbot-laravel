<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DiscordChannel extends Model
{
    protected $fillable = [
        'channel_id',
    ];

    public function channels()
    {
        return $this->belongsToMany('App\Channel');
    }
}
