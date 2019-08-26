<?php

return [
    'token' => env('DISCORD_BOT_TOKEN'),

    'prefix' => env('DISCORD_PREFIX', '!'),

    'activity' => env('DISCORD_ACTIVITY', '!@#$%^&*()_+'),

    'commands' => [
        'setactivity' => App\Bot\Commands\SetActivity::class,
        'notify' => App\Bot\Commands\Notify::class,
    ]
];