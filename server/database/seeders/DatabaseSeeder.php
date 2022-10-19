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
        // Create default admin record
        $item = new \App\Models\User();
        $item->login = 'wh';
        $item->password = app('hash')->make('wh');
        $item->access = 2;
        $item->save();

        // Create default terminal record
        $item = new \App\Models\User();
        $item->login = 'terminal';
        $item->password = app('hash')->make('terminal');
        $item->access = 1;
        $item->save();
    }
}
