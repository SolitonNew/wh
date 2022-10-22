<?php

namespace App\Library\Commands;

use Illuminate\Support\Facades\DB;

class CoreExecuteClear
{
    public function execute()
    {
        DB::delete('delete from core_execute
                         where id < (select a.maxID
                                       from (select (IFNULL(MAX(id), 0) - 100) maxID
                                               from core_execute) a)');
    }
}
