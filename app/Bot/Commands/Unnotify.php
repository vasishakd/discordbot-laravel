<?php


namespace App\Bot\Commands;


use App\Bot\Contracts\Command;

class Unnotify extends Command
{

    public function execute()
    {
        if ($this->isMod() && $this->args) {

        }
    }
}