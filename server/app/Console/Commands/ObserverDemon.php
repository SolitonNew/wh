<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Lang;

class ObserverDemon extends Command
{
    use PrintToDB;
    
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
        DB::select('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
        
        $lastProcessedID = -1;

        $this->printLine('');
        $this->printLine('');
        $this->printLine('');
        $this->printLine(Lang::get('admin/demons.observer-demon-title'));
        
        while(1) {

            usleep(500000);
        }
    }
}
