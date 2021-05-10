<?php

namespace App\Console\Commands;

use \Illuminate\Console\Command;

class CommandDemon extends Command
{    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command-demon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Eavesdrops on in-system commands';
    
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
        $demon = new \App\Library\Demons\CommandDemon($this->signature);
        $demon->execute();
    }
}
