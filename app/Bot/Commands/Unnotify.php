<?php


namespace App\Bot\Commands;


use App\Bot\Contracts\Command;
use App\Channel;
use App\Service;

class Unnotify extends Command
{

    public function execute()
    {
        if ($this->isMod() && $this->args) {
            if (count($this->args) != 2) {
                $this->message->channel->send('Wrong arguments number');
                return;
            }
            $channelKey = $this->args[0];
            $serviceTitle = $this->args[1];

            $channel = Channel::where('key', $channelKey)->whereHas('service', function($query) use ($serviceTitle) {
                $query->whereTitle($serviceTitle);
            })->first();

            $response = 'Channel ' . $channelKey . ' is not following';

            if ($channel) {
                $service = Service::whereTitle($serviceTitle)->first();
                $discordChannelId = $this->message->channel->getId();

                $discordChannel = $channel->discordChannels()->where('discord_channels.channel_id', $discordChannelId)->wherePivot('service_id', $service->id)->first();

                if ($discordChannel) {
                    $channel->discordChannels()->detach($discordChannel);

                    if ($channel->discordChannels()->count() == 0) {
                        $channel->delete();
                    }

                    $response = 'Channel ' . $channelKey . ' deleted';
                }
            }

            $this->message->channel->send($response);
        }
    }
}