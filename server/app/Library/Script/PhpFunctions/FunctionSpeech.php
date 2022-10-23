<?php

namespace App\Library\Script\PhpFunctions;

use App\Models\ScriptString;
use App\Library\Speech;

trait FunctionSpeech
{
    /**
     * @param int $targetID
     * @param int $phraseID
     * @param int ...$args
     * @return void
     */
    public function function_speech(int $targetID, int $phraseID, float ...$args): void
    {
        $target = ScriptString::getStringById($targetID);
        $phrase = ScriptString::getStringById($phraseID);

        if ($phrase) {
            $phraseText = vsprintf($phrase, $args);

            if ($this->fake) {
                $this->printLine('>>> for '.$target.' speech phrase: '.$phraseText);
            } else {
                (new Speech())->turn($phrase);
            }
        }
    }
}
