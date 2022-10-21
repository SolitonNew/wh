<?php

namespace App\Console\Commands;

use \Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HistoryClear extends Command
{
    protected $signature = 'history:clear';
    protected $description = 'history:clear';

    public function handle()
    {
        $sql = 'select dc.id '.
               '  from core_device_changes dc '.
               ' where not exists(select * '.
               '                    from core_devices d '.
               '                   where d.id = dc.device_id) '.
               ' limit 1000';

        while (true) {
            $ids = [];
            foreach (DB::select($sql) as $row) {
                $ids[] = $row->id;
            }
            if (count($ids) == 0) break;
            DB::delete('delete from core_device_changes where id in ('.implode(',', $ids).')');
            sleep(1);
        }
    }
}
