<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use \App\Http\Controllers\Admin\DemonsController;
use Lang;
use DB;

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

        $this->printLine('');
        $this->printLine('');
        $this->printLine('');
        $this->printLine(Lang::get('admin/demons.observer-demon-title'));
        
        
        $clear_web_log_i = 0;
        
        while(1) {
            // Прочистка веблогов
            $clear_web_log_i++;
            if ($clear_web_log_i > 10 * 60) {
                foreach(DemonsController::$demons as $demon) {
                    $rows = DB::select('select ID
                                          from web_logs 
                                         where DEMON = "'.$demon.'"
                                        order by ID desc
                                        limit '.config("app.admin_demons_log_lines_count"));
                    if (count($rows)) {
                        $id = $rows[count($rows) - 1]->ID;
                        DB::delete('delete from web_logs where DEMON = "'.$demon.'" and ID < '.$id);
                    }
                }
                $clear_web_log_i = 0;
            }

            usleep(100000);
        }
    }
}
