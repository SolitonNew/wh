<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RS485Demon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rs485-demon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обслуживает взаимодействие сервера и щитовых контроллеров';

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
