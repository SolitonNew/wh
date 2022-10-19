<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;

class CoreExecuteClear extends \Illuminate\Console\Command
{
    protected $signature = 'coreexecute:clear';
    protected $description = 'coreexecute:clear';

    public function handle()
    {
        DB::delete('delete from core_execute
                         where id < (select a.maxID
                                       from (select (IFNULL(MAX(id), 0) - 100) maxID
                                               from core_execute) a)');
    }
}
