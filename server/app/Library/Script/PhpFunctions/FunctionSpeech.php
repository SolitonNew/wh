<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Script\PhpFunctions;

use App\Models\ScriptString;
use App\Library\Speech;

trait FunctionSpeech 
{
    /**
     * 
     * @param type $phraseID
     * @param type $args
     */
    public function function_speech($phraseID, ...$args)
    {          
        $string = ScriptString::find($phraseID);
        
        if ($string) {
            $phrase = vsprintf($string->data, $args);
            
            if ($this->_fake) {
                $this->printLine('>>> '.$phrase);
            } else {
                (new Speech())->turn($phrase);
            }
        }
    }
}