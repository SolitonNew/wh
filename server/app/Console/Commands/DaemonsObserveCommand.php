<?php

namespace App\Console\Commands;

use App\Library\Commands\DaemonsObserve;
use \Illuminate\Console\Command;

class DaemonsObserveCommand extends Command
{
    protected $signature = 'daemon:observe';
    protected $description = 'daemon:observe';

    public function handle(DaemonsObserve $command)
    {
        $command->execute();
    }
}
