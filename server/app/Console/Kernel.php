<?php

namespace App\Console;

use App\Console\Commands\CoreEventsClearCommand;
use App\Console\Commands\CoreExecuteClearCommand;
use App\Console\Commands\DaemonsObserveCommand;
use App\Console\Commands\DaemonRunCommand;
use App\Console\Commands\HistoryClearCommand;
use App\Console\Commands\WebLogsClearCommand;
use App\Library\Commands\CoreEventsClear;
use App\Library\Commands\CoreExecuteClear;
use App\Library\Commands\HistoryClear;
use App\Library\Commands\WebLogsClear;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        DaemonRunCommand::class,
        DaemonsObserveCommand::class,
        WebLogsClearCommand::class,
        CoreEventsClearCommand::class,
        CoreExecuteClearCommand::class,
        HistoryClearCommand::class,
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
        $schedule->command(DaemonsObserveCommand::class)
            ->everyMinute()
            ->runInBackground();

        // Reading "web_logs_mem"
        $schedule->call(function (WebLogsClear $command) {
            $command->execute();
        })->everyMinute();

        // Clearing "core_events_mem"
        $schedule->call(function (CoreEventsClear $command) {
            $command->execute();
        })->everyFiveMinutes();

        // Clearing "core_execute"
        $schedule->call(function (CoreExecuteClear $command) {
            $command->execute();;
        })->dailyAt('4:00');

        // Clear Deleted Devices
        $schedule->call(function (HistoryClear $command) {
            $command->execute();
        })->dailyAt('4:10');
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
