<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Demons\Commands;

/**
 * Description of PlayCommand
 *
 * @author soliton
 */
class Play extends CommandBase {

    public function execute(string $command, &$output) {
        $output = 'LINES';
        return false;
    }
    
}
