<?php


namespace App\Bot\Strategy\ChannelChecker;

use App\Channel;
use App\Service;
use CharlotteDunois\Yasmin\Client as YasminClient;
use CharlotteDunois\Yasmin\Models\MessageEmbed;
use GuzzleHttp\Client;

class WASDChecker implements ChannelChecker
{
    private $cookieJar;
    private $guzzle;

    public function __construct()
    {
        $this->guzzle = new Client([
            'http_errors' => false,
            'cookies' => true,
        ]);
    }

    /**
     * @param string $channel
     * @return bool
     */
    public function exists(string $channel): bool
    {
        return true;
    }

    public function online(YasminClient $client)
    {
        if (!$this->cookieJar) {
            $this->guzzle->get('https://wasd.tv/api/auth/anon-token');
            $this->cookieJar = $this->guzzle->getConfig('cookies');
        }

        $channels = Channel::whereHas('service', function ($query) {
            $query->whereTitle(Service::SERVICES['wasd']);
        })->get();

        foreach ($channels as $channel) {
            try {
                $service = Service::whereTitle(Service::SERVICES['wasd'])->first();
                $discordChannels = $channel->discordChannels()->wherePivot('service_id', $service->id)->get();

                if ($discordChannels->isNotEmpty()) {
                    $response = $this->guzzle->get('https://wasd.tv/api/channels/' . $channel->key, [
                        'headers' => [
                            'Accept' => 'application/json',
                        ],
                        'cookies' => $this->cookieJar,
                    ]);

                    if ($response->getStatusCode() == 401) {
                        echo '401';
                        $this->guzzle->get('https://wasd.tv/api/auth/anon-token');
                        $this->cookieJar = $this->guzzle->getConfig('cookies');
                    } else {
                        if ($response->getStatusCode() == 200) {
                            $body = json_decode($response->getBody());

                            if ($body->result->channel_is_live && !$channel->is_live) {
                                $channel->is_live = true;
                                $channel->save();

                                $embed = new MessageEmbed();

                                $url = 'https://wasd.tv/channel/' . $channel->key;

                                $embed->setTitle('Stream online')
                                    ->setColor('#258fe5')
                                    ->setURL($url)
                                    ->setAuthor($url, '', $url)
                                    ->setDescription('Hey @everyone stream is now live! ' . $url)
                                    ->setImage($body->result->channel_image->small)
                                    ->setTimestamp();

                                foreach ($discordChannels as $discordChannel) {
                                    $discordChannelClient = $client->channels->get($discordChannel->channel_id);
                                    $discordChannelClient->send('@everyone', ['embed' => $embed, 'disableEveryone' => false])
                                        ->done(null, function ($error) {
                                            echo $error . PHP_EOL;
                                        });
                                    }
                            }

                            if (!$body->result->channel_is_live && $channel->is_live) {
                                $channel->is_live = false;
                                $channel->save();
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
    }
}