<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Demons;

use DB;
use Lang;
use Log;

/**
 * Description of CommandDemon
 *
 * @author soliton
 */
class RS485Demon extends BaseDemon {
    
    /**
     * 
     */
    public function execute() {
        DB::select('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
        
        $lastProcessedID = -1;

        $this->printLine('');
        $this->printLine('');
        $this->printLine(str_repeat('-', 100));
        $this->printLine(Lang::get('admin/demons.rs485-demon-title'));
        $this->printLine('--    PORT: '.config('firmware.rs485_port')); 
        $this->printLine('--    BAUD: '.config('firmware.rs485_baud')); 
        $this->printLine(str_repeat('-', 100));
        $this->printLine('');
        
        $controllers = \App\Http\Models\ControllersModel::where('id', '<', 100)
                            ->orderBy('name', 'asc')
                            ->get();
        
        if (count($controllers) == 0) return;
        
        try {            
            exec('stty -F '.config('firmware.rs485_port').' '.config('firmware.rs485_baud').' cs8 cstopb');
            $port = fopen(config('firmware.rs485_port'), 'r+b');
            while (1) {
                foreach($controllers as $controller) {
                    if ($controller->is_server) continue;
                    $contr = $controller->name;
                    
                    $vars_out_str = [];
                    $vars_in_str = [];
                    
                    try {
                        fwrite($port, 0x30);
                        
                        $stat = 'OK';
                        $s = "[".now()."] SYNC. '$contr': $stat\n";
                        $s .= "   >>   [".implode(', ', $vars_out_str)."]\n";
                        $s .= "   <<   [".implode(', ', $vars_in_str)."]\n";
                    } catch (\Exception $ex) {
                        $s = "[".now()."] SYNC. '$contr': ERROR\n";
                        $s .= $ex->getMessage();
                    }
                                    
                    $this->printLine($s); 
                    
                    usleep(100000);
                }
            }
        } catch (\Exception $ex) {
            $s = "[".now()."] ERROR\n";
            $s .= $ex->getMessage();
            $this->printLine($s); 
        }
        fclose($port);
    }
}