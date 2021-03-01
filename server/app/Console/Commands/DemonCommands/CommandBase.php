<?php

namespace App\Console\Commands\DemonCommands;


abstract class CommandBase {
        
    /**
     * 
     * @param string $command
     */
    public function execute(string $command, &$output) {
        $output = 'LINES';
        return false;
    }
}
