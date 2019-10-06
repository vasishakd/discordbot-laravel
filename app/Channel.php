<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $fillable = [
        'key', 'service_id'
    ];

    protected $visible = [
        'id', 'key', 'status', 'service_name', 'links',
    ];

    protected $appends = [
        'status', 'service_name', 'links',
    ];

    public function getStatusAttribute()
    {
        return $this->is_live ? 'Online' : 'Offline';
    }

    public function getServiceNameAttribute()
    {
        return $this->service->title;
    }

    public function getLinksAttribute()
    {
        return [
            'delete' => route('channel.delete', $this),
        ];
    }

    public function service()
    {
        return $this->belongsTo('App\Service');
    }

    public function discordChannels()
    {
        return $this->belongsToMany('App\DiscordChannel');
    }
}
