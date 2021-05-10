<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Console\Commands;

use \Illuminate\Console\Command;

/**
 * Description of ObserverDaemon
 *
 * @author soliton
 */
class ObserverDaemon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'observer-daemon';

    /**
     * The console command description.
     * 
     * @var string
     */
    protected $description = 'Observer of changes in the system';

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
        $demon = new \App\Library\Daemons\ObserverDaemon($this->signature);
        $demon->execute();
    }
}
