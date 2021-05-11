<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Library\DaemonManager;
use DB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\SoftwareDaemon::class,
        Commands\DinDaemon::class,
        Commands\ScheduleDaemon::class,
        Commands\CommandDaemon::class,
        Commands\ObserverDaemon::class,
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
        $schedule->call(function (DaemonManager $daemonManager) {
            foreach(\App\Models\PropertysModel::runningDaemons() as $daemon) {
                if (count($daemonManager->findDaemonPID($daemon)) == 0) {
                    $daemonManager->start($daemon);
                }
            }
        })->everyMinute();
        
        // Прочистка "web_logs"
        $schedule->call(function (DaemonManager $daemonManager) {
            foreach($daemonManager->daemons() as $daemon) {
                $rows = DB::select('select id
                                      from web_logs 
                                     where daemon = "'.$daemon.'"
                                    order by id desc
                                    limit '.config("app.admin_daemons_log_lines_count"));
                if (count($rows)) {
                    $id = $rows[count($rows) - 1]->id;
                    DB::delete('delete from web_logs where daemon = "'.$daemon.'" and id < '.$id);
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
        
        // Прочистка "web_queue"
        $schedule->call(function () {
            DB::delete('delete from web_queue_mem
                         where id < (select a.maxID 
                                       from (select (IFNULL(MAX(id), 0) - 100) maxID 
                                               from web_queue_mem) a)');
        })->hourly();
        
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
