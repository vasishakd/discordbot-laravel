<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $fillable = [
        'key', 'service_id'
    ];

    public function service()
    {
        return $this->belongsTo('App\Service');
    }

    public function discordChannels()
    {
        return $this->belongsToMany('App\DiscordChannel');
    }
}
