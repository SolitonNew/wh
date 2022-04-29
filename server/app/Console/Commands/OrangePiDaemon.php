<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Console\Commands;

use \Illuminate\Console\Command;

/**
 * Description of OrangePiDaemon
 *
 * @author User
 */
class OrangePiDaemon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orangepi-daemon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processing Orange Pi hubs';

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
        $demon = new \App\Library\Daemons\OrangePiDaemon($this->signature);
        $demon->execute();
    }
}
