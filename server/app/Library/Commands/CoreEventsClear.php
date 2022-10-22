<?php

namespace App\Library\Commands;

use Illuminate\Support\Facades\DB;

class CoreEventsClear
{
    public function execute()
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
