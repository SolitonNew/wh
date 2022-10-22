<?php

namespace App\Console\Commands;

use App\Library\Commands\CoreExecuteClear;
use \Illuminate\Console\Command;

class CoreExecuteClearCommand extends Command
{
    protected $signature = 'coreexecute:clear';
    protected $description = 'coreexecute:clear';

    public function handle(CoreExecuteClear $command)
    {
        $command->execute();
    }
}
