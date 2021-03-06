<?php

namespace App\Console\Commands;

use App\Bot\Strategy\ChannelChecker\ChannelCheckerFactory;
use App\Service;
use CharlotteDunois\Yasmin\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
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

        $client->on('disconnect', function ($shard, $code, $reason) use ($client) {
            Log::warning('Disconnected. code: ' . $code . ' Reason: ' . $reason);
            $client->login(config('discord.token'))->done();
        });

        $client->on('error', function ($error) {
            Log::error($error);
        });

        $client->on('ready', function () use ($client, $loop) {
            $client->user->setActivity(config('discord.activity'));
            echo 'Logged in as ' . $client->user->tag . ' created on ' . $client->user->createdAt->format('d.m.Y H:i:s') . PHP_EOL;

            $services = Service::SERVICES;
            $checkerFactory = new ChannelCheckerFactory;

            foreach ($services as $service) {
                $checkers[] = $checkerFactory->getChannelChecker($service);
            }

            $loop->addPeriodicTimer(config('discord.notifications_interval'), function() use ($client, $checkers) {
                foreach ($checkers as $checker) {
                    $checker->online($client);
                }
            });

        });

        $client->on('message', function ($message) use ($client) {
            if ($message->author->bot || !$message->content || $message->content[0] !== config('discord.prefix')) {
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
}
