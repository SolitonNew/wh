<?php

namespace App\Console\Commands;

use \Illuminate\Console\Command;

class MediaDaemon extends Command
{    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media-daemon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Media observer';
    
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
        $demon = new \App\Library\Daemons\MediaDaemon($this->signature);
        $demon->execute();
    }
}
