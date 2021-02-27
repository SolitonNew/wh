<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Console\Commands\DemonCommands;

/**
 * Description of VariableCommand
 *
 * @author soliton
 */
class VariableCommand extends CommandBase {
    
    public function execute(string $command, &$output) {
        $output = 'LINES';
        return false;
    }
}
