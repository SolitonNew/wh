<?php

namespace App\Console;

use App\Console\Commands\CoreEventsClear;
use App\Console\Commands\CoreExecuteClear;
use App\Console\Commands\DaemonObserve;
use App\Console\Commands\DaemonRun;
use App\Console\Commands\HistoryClear;
use App\Console\Commands\WebLogsClear;
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
        DaemonRun::class,
        DaemonObserve::class,
        WebLogsClear::class,
        CoreEventsClear::class,
        CoreExecuteClear::class,
        HistoryClear::class,
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
        $schedule->command(DaemonObserve::class)->everyMinute();

        // Reading "web_logs_mem"
        $schedule->command(WebLogsClear::class)->everyMinute();

        // Clearing "core_events_mem"
        $schedule->command(CoreEventsClear::class)->everyFiveMinutes();

        // Clearing "core_execute"
        $schedule->command(CoreExecuteClear::class)->dailyAt('4:00');

        // Clear Deleted Devices
        $schedule->command(HistoryClear::class)->dailyAt('4:10');
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
