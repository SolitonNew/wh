<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Script\PhpFunctions;

use Speech;

trait FunctionSpeech 
{
    /**
     * 
     * @param type $phrase
     */
    public function function_speech($phrase)
    {          
        Speech::turn($phrase);
    }
}