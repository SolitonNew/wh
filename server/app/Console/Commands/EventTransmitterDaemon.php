<?php

namespace App\Console\Commands;

use \Illuminate\Console\Command;

class EventTransmitterDaemon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eventtransmitter-daemon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daemon serving the event transmitter';

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
        $demon = new \App\Library\Daemons\EventTransmitterDaemon($this->signature);
        $demon->execute();
    }
}
