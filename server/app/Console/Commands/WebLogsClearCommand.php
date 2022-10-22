<?php

namespace App\Console\Commands;

use App\Library\Commands\WebLogsClear;
use \Illuminate\Console\Command;

class WebLogsClearCommand extends Command
{
    protected $signature = 'weblogs:clear';
    protected $description = 'weblogs:clear';

    public function handle(WebLogsClear $command)
    {
        $command->execute();
    }
}
