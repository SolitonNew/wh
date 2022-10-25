<?php

namespace App\Library\Daemons;

use App\Models\Schedule;
use App\Models\Execute;
use \Carbon\Carbon;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;

class ScheduleDaemon extends BaseDaemon
{
    public const SIGNATURE = 'schedule-daemon';

    public const PROPERTY_NAME = 'SCHEDULE';

    /**
     * @return bool
     */
    public static function canRun(): bool
    {
        return true;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        DB::select('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
        DB::update('update core_schedule set action_datetime = null');

        $this->printInitPrompt(Lang::get('admin/daemons/schedule-daemon.description'));

        // Showing current tasks
        foreach (Schedule::orderBy('comm', 'asc')->get() as $row) {
            $row->action_datetime = $row->makeDateTime();
            $row->save();
            $time = '--//--';
            if ($row->action_datetime) {
                $time = parse_datetime($row->action_datetime)->format('Y-m-d H:i:s');
            }
            $this->printLine("[$time] $row->comm       ".($row->enable ? '' : Lang::get('admin/daemons/schedule-daemon.disabled')));
        }

        while (1) {
            foreach (Schedule::orderBy('comm', 'asc')->get() as $row) {
                $next_time = null;
                if (!$row->action_datetime) {
                    $next_time = $row->makeDateTime();
                } elseif (Carbon::parse($row->action_datetime)->lte(now())) {
                    $next_time = $row->makeDateTime();
                    if ($row->enable) {
                        // Runing
                        Execute::command($row->action);
                        $this->printLine(Lang::get('admin/daemons/schedule-daemon.line', [
                            'datetime' => parse_datetime($row->action_datetime),
                            'comm' => $row->comm,
                            'action' => str_replace("\n", ' ', $row->action),
                        ]));
                    }

                    if ($row->interval_type == 4) { // Это одноразовая задача
                        $row->delete();
                        $next_time = null;
                    }
                }

                if ($next_time) {
                    $row->action_datetime = $next_time;
                    $row->save();
                }
            }
            
            sleep(1);
        }
    }
}
