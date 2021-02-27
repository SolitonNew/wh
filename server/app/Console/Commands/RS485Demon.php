<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use \Carbon\Carbon;
use Lang;

class RS485Demon extends Command
{
    use PrintToDB;
    
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
        DB::select('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
        
        $lastProcessedID = -1;

        $this->printLine('');
        $this->printLine('');
        $this->printLine('');
        $this->printLine(Lang::get('admin/demons.rs485-demon-title'));
        
        $controllers = \App\Http\Models\ControllersModel::where('ID', '<', 100)
                            ->orderBy('NAME', 'asc')
                            ->get();
        
        while(1) {
            foreach($controllers as $controller) {
                $s = '['.now()->format('H:i:s').'] '.$controller->NAME;
                if (random_int(0, 10) > 7) {
                    $s .= ' VARIABLES';
                }
                $this->printLine($s);
                usleep(100000);
            }
        }
    }
}
