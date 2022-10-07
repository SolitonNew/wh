<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use App\Library\DaemonManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ExtApiDaemon::class,
        Commands\DinDaemon::class,
        Commands\PyhomeDaemon::class,
        Commands\ScheduleDaemon::class,
        Commands\CommandDaemon::class,
        Commands\ObserverDaemon::class,
        Commands\OrangePiDaemon::class,
        Commands\CamcorderDaemon::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        // Checking background processes.
        // If process stopped - to start.
        $schedule->call(function (DaemonManager $daemonManager) {
            foreach (\App\Models\Property::runningDaemons() as $daemon) {
                if (count($daemonManager->findDaemonPID($daemon)) == 0) {
                    try {
                        Log::channel('daemons')->info("An attempt to launch the '$daemon' demon...");
                        $daemonManager->start($daemon);
                        Log::channel('daemons')->info("Daemon '$daemon' has been launched");
                    } catch (\Exception $ex) {
                        Log::error($ex->getMessage());
                    }
                }
            }
        })->everyMinute();

        // Reading "web_logs_mem"
        $schedule->call(function (DaemonManager $daemonManager) {
            foreach ($daemonManager->daemons() as $daemon) {
                $rows = DB::select('select id
                                      from web_logs_mem
                                     where daemon = "'.$daemon.'"
                                    order by id desc
                                    limit '.config("settings.admin_daemons_log_lines_count"));
                if (count($rows)) {
                    $id = $rows[count($rows) - 1]->id;
                    DB::delete('delete from web_logs_mem where daemon = "'.$daemon.'" and id < '.$id);
                }
            }
        })->everyMinute();

        // Clearing "core_events_mem"
        $schedule->call(function () {
            $maxID = DB::select('select max(m.id) mId from core_events_mem m')[0]->mId;
            if ($maxID) {
                $maxID -= config('settings.admin_log_lines_count');
                DB::delete('delete from core_events_mem m where m.id < '.$maxID);
            }
        })->everyFiveMinutes();

        // Clearing "core_execute"
        $schedule->call(function () {
            DB::delete('delete from core_execute
                         where id < (select a.maxID
                                       from (select (IFNULL(MAX(id), 0) - 100) maxID
                                               from core_execute) a)');
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
