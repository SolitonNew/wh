<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use \App\Library\DemonManager;
use DB;
use Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\RS485Demon::class,
        Commands\ScheduleDemon::class,
        Commands\CommandDemon::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Проверяем работоспособность фоновых процессов. Если кто-то пропал - запускаем
        $schedule->call(function (DemonManager $demonManager) {
            foreach(\App\Http\Models\PropertysModel::runningDemons() as $demon) {
                if (count($demonManager->findDemonPID($demon)) == 0) {
                    $demonManager->start($demon);
                }
            }
        })->everyMinute();
        
        // Прочистка "web_logs"
        $schedule->call(function (DemonManager $demonManager) {
            foreach($demonManager->demons() as $demon) {
                $rows = DB::select('select id
                                      from web_logs 
                                     where demon = "'.$demon.'"
                                    order by id desc
                                    limit '.config("app.admin_demons_log_lines_count"));
                if (count($rows)) {
                    $id = $rows[count($rows) - 1]->id;
                    DB::delete('delete from web_logs where demon = "'.$demon.'" and id < '.$id);
                }
            }
        })->everyMinute();
        
        // Прочистка "core_variable_changes_mem"
        $schedule->call(function () {
            DB::delete('delete from core_variable_changes_mem
                         where change_date < CURRENT_TIMESTAMP - interval 1 day');
        })->hourly();
        
        // Прочистка "core_execute"
        $schedule->call(function () {
            DB::delete('delete from core_execute
                         where id < (select a.maxID 
                                       from (select (IFNULL(MAX(id), 0) - 100) maxID 
                                               from core_execute) a)');
        })->dailyAt('4:00');
        
        // Прочистка "app_control_sess"
        $schedule->call(function () {
            /*DB::delete('delete from app_control_sess
                         where id < (select a.maxID 
                                       from (select (IFNULL(MAX(id), 0) - 100) maxID 
                                               from app_control_sess) a)'); */
        })->dailyAt('4:00');
        
        // Прочистка "app_control_queue"
        $schedule->call(function () {
            /*DB::delete('delete from app_control_queue
                         where id < (select a.maxID 
                                       from (select (IFNULL(MAX(id), 0) - 100) maxID 
                                               from app_control_queue) a)'); */
        })->dailyAt('4:00');
        
        // Прочистка "app_control_exe_queue"
        $schedule->call(function () {
            /*DB::delete('delete from app_control_exe_queue
                         where id < (select a.maxID 
                                       from (select (IFNULL(MAX(id), 0) - 100) maxID 
                                               from app_control_exe_queue) a)'); */
        })->dailyAt('4:00');
        
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
