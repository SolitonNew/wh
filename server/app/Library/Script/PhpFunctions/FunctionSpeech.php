<?php

namespace App\Library\Script\PhpFunctions;

use App\Models\ScriptString;
use App\Library\Speech;

trait FunctionSpeech
{
    /**
     * @param int $phraseID
     * @param int ...$args
     * @return void
     */
    public function function_speech(int $phraseID, int ...$args): void
    {
        $string = ScriptString::find($phraseID);

        if ($string) {
            $phrase = vsprintf($string->data, $args);

            if ($this->fake) {
                $this->printLine('>>> '.$phrase);
            } else {
                (new Speech())->turn($phrase);
            }
        }
    }
}
