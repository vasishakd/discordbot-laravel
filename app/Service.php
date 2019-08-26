<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    const SERVICES = [
        'goodgame' => 'gg',
        'wasd' => 'wasd',
    ];

    public function channels()
    {
        return $this->hasMany('App\Channel');
    }
}
