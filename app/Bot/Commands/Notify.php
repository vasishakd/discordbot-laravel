<?php


namespace App\Bot\Commands;


use App\Bot\Contracts\Command;
use App\Bot\Strategy\ChannelChecker\ChannelCheckerFactory;
use App\Channel;
use App\DiscordChannel;
use App\Events\ChannelCreated;
use App\Service;

class Notify extends Command
{
    public function execute()
    {
        if ($this->isMod() && $this->args) {
            $channelKey = $this->args[0];
            $serviceTitle = $this->args[1] ?? Service::SERVICES['goodgame'];

            $service = Service::whereTitle($serviceTitle)->first();

            if (!$service) {
                $this->message->channel->send('No such service');
                return;
            }

            $discordChannelId = $this->message->channel->getId();
            $discordChannel = DiscordChannel::where('channel_id', $discordChannelId)->first();
            if (!$discordChannel) {
                $discordChannel = DiscordChannel::create([
                    'channel_id' => $discordChannelId,
                ]);
            }

            $channelExists = Channel::where('key', $channelKey)->whereHas('service', function($query) use ($serviceTitle) {
                $query->whereTitle($serviceTitle);
            })->whereHas('discordChannels', function($query) use ($discordChannel) {
                $query->where('id', $discordChannel->id);
            })->exists();

            if ($channelExists) {
                $this->message->channel->send('Channel ' . $channelKey . ' already following');
                return;
            }

            $channelCheckerFactory = new ChannelCheckerFactory;
            try {
                $channelChecker = $channelCheckerFactory->getChannelChecker($serviceTitle);
                $exists = $channelChecker->exists($channelKey);
            } catch (\Exception $e) {
                echo $e->getMessage();
                return;
            }

            if ($exists) {
                $channel = Channel::firstOrCreate([
                    'key' => $channelKey,
                    'service_id' => $service->id,
                ]);
                $channel->discordChannels()->attach($discordChannel, ['service_id' => $service->id]);

                event(new ChannelCreated($channel));

                $response = $channelKey . ' now notifying';
            } else {
                $response = $channelKey . ' doesnt exist';
            }

            $this->message->channel->send($response);

        }
    }
}