<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Demons\Commands;

use Log;
use DB;

/**
 * Description of VariableCommand
 *
 * @author soliton
 */
class Variable extends CommandBase {
    
    public function execute(string $command, &$output) {
        $output = '';
        $command = strtoupper(trim($command));
        
        $keys = [
            0 => 'OFF(',
            1 => 'ON(', 
        ];
        
        foreach($keys as $val => $key) {
            if (strpos($command, $key) === 0) {
                $varName = substr($command, strlen($key), strlen($command) - strlen($key) - 1);
                $varName = str_replace('"', '', $varName);
                $varName = str_replace("'", '', $varName);
                $varName = trim($varName);
                
                $var = \App\Http\Models\VariablesModel::whereName($varName)->first();
                if ($var) {
                    try {
                        DB::select("call CORE_SET_VARIABLE($var->ID, $val, -1)");
                        return true;
                    } catch (\Exception $ex) {
                        Log::error($ex->getMessage());
                    }
                }
            }
        }
        
        return false;
    }
}
