<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;

class CoreEventsClear extends \Illuminate\Console\Command
{
    protected $signature = 'coreevents:clear';
    protected $description = 'coreevents:clear';

    public function handle()
    {
        $maxID = DB::select('select max(m.id) maxID from core_events_mem m')[0]->maxID ?? 0;
        if ($maxID) {
            $maxID -= config('settings.admin_log_lines_count');
            if ($maxID > 0) {
                DB::delete('delete from core_events_mem where id < '.$maxID);
            }
        }
    }
}
