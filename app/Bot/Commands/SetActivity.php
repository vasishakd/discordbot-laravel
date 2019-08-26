<?php

namespace App\Bot\Commands;

use App\Bot\Contracts\Command;

class SetActivity extends Command
{
    public function execute()
    {
        if ($this->isMod() && $this->args) {
            $activity = implode(' ', $this->args);
            $this->client->user->setActivity($activity);

            setEnvironmentValue([
                'DISCORD_ACTIVITY' => $activity,
            ]);
        }
    }
}