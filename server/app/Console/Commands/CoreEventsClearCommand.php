<?php

namespace App\Console\Commands;

use App\Library\Commands\CoreEventsClear;
use \Illuminate\Console\Command;

class CoreEventsClearCommand extends Command
{
    protected $signature = 'coreevents:clear';
    protected $description = 'coreevents:clear';

    public function handle(CoreEventsClear $command)
    {
        $command->execute();
    }
}
