<?php

namespace App\Console\Commands;

use \Illuminate\Console\Command;

class DinDaemon extends Command
{    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'din-daemon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Serves the interaction of the server and panel controllers';

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
        $demon = new \App\Library\Daemons\DinDaemon($this->signature);
        $demon->execute();
    }
}
