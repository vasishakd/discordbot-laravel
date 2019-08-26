<?php

namespace App\Bot\Strategy\ChannelChecker;

use App\Channel;
use App\Service;
use CharlotteDunois\Yasmin\Models\MessageEmbed;
use CharlotteDunois\Yasmin\Client as YasminClient;
use GuzzleHttp\Client;

class GGChecker implements ChannelChecker
{
    private $guzzle;

    public function __construct()
    {
        $this->guzzle = new Client([
            'http_errors' => false,
            'base_uri' => 'http://api2.goodgame.ru/v2/'
        ]);
    }

    /**
     * @param string $channel
     * @return bool
     */
    public function exists(string $channel): bool
    {
        $response = $this->guzzle->get('streams/' . $channel, [
            'headers' => [
                'Accept' => 'application/json',
            ]
        ]);

        if ($response->getStatusCode() == 404) {
            return false;
        }

        return true;
    }

    /**
     * @param \CharlotteDunois\Yasmin\Client $client
     * @throws \Throwable
     */
    public function online(YasminClient $client)
    {
        $channels = Channel::whereHas('service', function ($query) {
            $query->whereTitle(Service::SERVICES['goodgame']);
        })->get();

        foreach ($channels as $channel) {
            try {
                $service = Service::whereTitle(Service::SERVICES['goodgame'])->first();
                $discordChannels = $channel->discordChannels()->wherePivot('service_id', $service->id)->get();

                if ($discordChannels->isNotEmpty()) {
                    $response = $this->guzzle->get('streams/' . $channel->key, [
                        'headers' => [
                            'Accept' => 'application/json',
                        ]
                    ]);

                    if ($response->getStatusCode() == 200) {
                        $body = json_decode($response->getBody());

                        if ($body->status === 'Live' && !$channel->is_live) {
                            $channel->is_live = true;
                            $channel->save();

                            $embed = new MessageEmbed();

                            $url = $body->url;

                            $embed->setTitle('Stream online')
                                ->setColor('#233056')
                                ->setURL($url)
                                ->setAuthor($body->key, '', $url)
                                ->setDescription('Hey @everyone ' . $body->key . ' is now live! ' . $url)
                                ->setImage('http:' . $body->channel->thumb)
                                ->setTimestamp();

                            foreach ($discordChannels as $discordChannel) {
                                $discordChannelClient = $client->channels->get($discordChannel->channel_id);
                                $discordChannelClient->send('@everyone', ['embed' => $embed, 'disableEveryone' => false])
                                    ->done(null, function ($error) {
                                        echo $error . PHP_EOL;
                                    });
                            }
                        }

                        if ($body->status === 'Dead' && $channel->is_live) {
                            $channel->is_live = false;
                            $channel->save();
                        }
                    }
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
    }
}