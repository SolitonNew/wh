<?php

namespace App\Library\Commands;

use App\Library\DaemonManager;
use Illuminate\Support\Facades\DB;

class WebLogsClear
{
    public function execute()
    {
        $daemonManager = new DaemonManager();
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
    }
}
