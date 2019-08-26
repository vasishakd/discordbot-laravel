<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        foreach (\App\Service::SERVICES as $service) {
            factory('App\Service')->create([
                'title' => $service,
            ]);
        }
    }
}
