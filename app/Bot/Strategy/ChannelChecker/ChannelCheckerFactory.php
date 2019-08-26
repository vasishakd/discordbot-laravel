<?php


namespace App\Bot\Strategy\ChannelChecker;


use App\Service;

class ChannelCheckerFactory
{
    /**
     * @param string $title
     * @return ChannelChecker
     * @throws \Exception
     */
    public function getChannelChecker(string $title): ChannelChecker
    {
        $services = Service::SERVICES;
        switch ($title) {
            case $services['goodgame']:
                return new GGChecker;
            case $services['wasd']:
                return new WASDChecker;
            default:
                throw new \Exception('Unknown Class');
        }
    }
}