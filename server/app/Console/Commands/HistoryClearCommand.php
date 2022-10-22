<?php

namespace App\Console\Commands;

use App\Library\Commands\HistoryClear;
use \Illuminate\Console\Command;

class HistoryClearCommand extends Command
{
    protected $signature = 'history:clear';
    protected $description = 'history:clear';

    public function handle(HistoryClear $command)
    {
        $command->execute();
    }
}
