<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Demons;

use DB;
use Lang;

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
        $this->printLine('');
        $this->printLine(Lang::get('admin/demons.rs485-demon-title'));
        
        $controllers = \App\Http\Models\ControllersModel::where('id', '<', 100)
                            ->orderBy('name', 'asc')
                            ->get();
        
        if (count($controllers) == 0) return;
        
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
                $contr = $controller->name;
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