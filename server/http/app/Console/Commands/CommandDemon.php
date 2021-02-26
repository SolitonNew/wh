<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CommandDemon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command-demon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Выпоняет внутрисистемные комманды';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $i = 0;
        while(1) {
            echo "$i\n";
            $i++;
            usleep(500000);
        }
    }
}
