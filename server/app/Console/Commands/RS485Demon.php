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
                $vars_out = [now()->timestamp];
                $vars_in = [];
                
                if (random_int(0, 10) > 8) {
                    $vars_out[] = 'VARIABLE OUT';
                }
                
                if (random_int(0, 10) > 8) {
                    $vars_in[] = 'VARIABLE IN';
                }                
                
                $date = now()->format('H:i:s');
                $contr = $controller->NAME;
                $stat = 'OK';
                $vars_out_str = '['.implode(', ', $vars_out).']';
                $vars_in_str = '['.implode(', ', $vars_in).']';
                
                $s = "[$date] SYNC. '$contr': $stat\n";
                $s .= "   >>   $vars_out_str\n";
                $s .= "   <<   $vars_in_str\n";                
                $this->printLine($s);
                
                usleep(100000);
            }
        }
    }
}
