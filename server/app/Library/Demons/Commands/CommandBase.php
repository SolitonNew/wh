<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Demons\Commands;

/**
 * Description of CommandBase
 *
 * @author soliton
 */
abstract class CommandBase {
    
    /**
     * 
     * @param string $command
     * @param string $output
     * @return boolean
     */
    public function execute(string $command, &$output) {
        $output = '';
        return false;
    }
}
