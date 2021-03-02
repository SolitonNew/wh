<?php

namespace App\Console\Commands;

use \Illuminate\Console\Command;

class ScheduleDemon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule-demon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Демон обслуживающий подсистему расписания';

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
        $demon = new \App\Library\Demons\ScheduleDemon($this->signature);
        $demon->execute();
    }
}
