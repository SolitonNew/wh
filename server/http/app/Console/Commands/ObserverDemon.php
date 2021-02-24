<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ObserverDemon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'observer-demon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Наблюдает за состоянием системы';

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
     * @return mixed
     */
    public function handle()
    {
        $i = 0;
        while(1) {
            echo "$i\n";
            $i++;
            usleep(500000);
        }
    }
}
