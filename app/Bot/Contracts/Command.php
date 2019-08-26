<?php

namespace App\Bot\Contracts;

use CharlotteDunois\Yasmin\Client;
use CharlotteDunois\Yasmin\Models\Message;
use CharlotteDunois\Yasmin\Models\Permissions;

abstract class Command
{
    const NAME = 'setactivity';
    const DESCRIPTION = 'Set bot activity';
    protected $client;
    protected $message;
    protected $args;

    public function __construct(Client $client, Message $message, array $args)
    {
        $this->client = $client;
        $this->message = $message;
        $this->args = $args;
    }

    abstract public function execute();

    protected function isMod()
    {
        return $this->message->member->permissions->has(Permissions::PERMISSIONS['ADMINISTRATOR']);
    }
}