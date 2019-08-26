<?php


namespace App\Bot\Strategy\ChannelChecker;


use CharlotteDunois\Yasmin\Client;

interface ChannelChecker
{
    public function exists(string $channel): bool;

    public function online(Client $client);
}