<?php

namespace App\Console\Commands;

use App\Bot\Strategy\ChannelChecker\ChannelCheckerFactory;
use App\Channel;
use App\DiscordChannel;
use App\Service;
use CharlotteDunois\Yasmin\Client;
use CharlotteDunois\Yasmin\Models\MessageEmbed;
use Illuminate\Console\Command;
use React\EventLoop\Factory;

class RunBotCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run bot';

    protected $cookieJar;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    public function handle()
    {
        $loop = Factory::create();
        $client = new Client(array(), $loop);

        $client->on('error', function ($error) {
            echo $error . PHP_EOL;
        });

        $client->on('ready', function () use ($client, $loop) {
            $client->user->setActivity(config('discord.activity'));
            echo 'Logged in as ' . $client->user->tag . ' created on ' . $client->user->createdAt->format('d.m.Y H:i:s') . PHP_EOL;

            $services = Service::SERVICES;
            $checkerFactory = new ChannelCheckerFactory;

            foreach ($services as $service) {
                $checkers[] = $checkerFactory->getChannelChecker($service);
            }

            $loop->addPeriodicTimer(10, function() use ($client, $checkers) {
                foreach ($checkers as $checker) {
                    $checker->online($client);
                }
            });

        });

        $client->on('message', function ($message) use ($client) {
            if ($message->author->bot || $message->content[0] !== config('discord.prefix')) {
                return;
            }

            $args = explode(' ', substr($message->content, 1));
            $commands = config('discord.commands');
            $sendedCommand = array_shift($args);

            if (!isset($commands[$sendedCommand])) {
                return;
            }

            try {
                $command = new $commands[$sendedCommand]($client, $message, $args);
                $command->execute();
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        });

        $client->login(config('discord.token'))->done();

        $loop->run();
    }

    public function checkGG($client)
    {
        $guzzle = new \GuzzleHttp\Client([
            'http_errors' => 'false',
            'base_uri' => 'http://api2.goodgame.ru/v2/'
        ]);

        $channels = Channel::all();

        foreach ($channels as $channel) {
            try {
                $service = Service::whereTitle(Service::SERVICES['goodgame'])->first();
                $discordChannels = $channel->discordChannels()->wherePivot('service_id', $service->id)->get();

                if ($discordChannels) {
                    $response = $guzzle->get('streams/' . $channel->title, [
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
                                $discordChannelClient->send('@everyone', ['embed' => $embed])
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

    public function checkWASD($client)
    {

    }
}
