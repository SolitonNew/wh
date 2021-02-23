<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use \Illuminate\Support\Facades\DB;
use Lang;
use \Carbon\Carbon;

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
        DB::select('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
        DB::update('update core_scheduler set ACTION_DATETIME = null');
        
        echo Lang::get('admin\schedule.demon_tasks_title')."\n";
        foreach(\App\Http\Models\SchedulerModel::orderBy('COMM', 'asc')->get() as $row) {
            $row->ACTION_DATETIME = $row->makeDateTime();
            $row->save();
            $time = '--//--';
            if ($row->ACTION_DATETIME) {
                $time = Carbon::parse($row->ACTION_DATETIME)->format('Y-m-d H:i:s');
            }
            echo "[$time] $row->COMM       ".($row->ENABLE ? '' : Lang::get('admin\schedule.demon_task_disabled'))."\n";
        }
        echo "---------------------------------\n";
        
        while(1) {
            foreach(\App\Http\Models\SchedulerModel::orderBy('COMM', 'asc')->get() as $row) {
                $next_time = null;
                if (!$row->ACTION_DATETIME) {
                    $next_time = $row->makeDateTime();
                } elseif (Carbon::parse($row->ACTION_DATETIME)->lte(now())) {
                    $next_time = $row->makeDateTime();
                    if ($row->ENABLE) {
                        // Выполняем
                        \App\Http\Models\ExecuteModel::command($row->ACTION);
                        echo Lang::get('admin\schedule.demon_info_line', [
                            'datetime' => $next_time,
                            'comm' => $row->COMM,
                            'action' => $row->ACTION
                        ])."\n";        
                    }
                    
                    if ($row->INTERVAL_TYPE == 4) { // Это одноразовая задача
                        $row->delete();
                        $next_time = null;
                    }
                }
                
                if ($next_time) {
                    $row->ACTION_DATETIME = $next_time;
                    $row->save();
                }
            }
            sleep(1);
        }
    }    
}
