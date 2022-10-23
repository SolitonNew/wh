<?php

namespace App\Library\Script\PhpFunctions;

use App\Models\ScriptString;

trait FunctionPrint
{
    /**
     * @param string $text
     * @return void
     */
    public function function_print(int $textID, float ...$args): void
    {
        $text = ScriptString::getStringById($textID);
        $textText = vsprintf($text, $args);
        $this->printLine('>>> '.$textText);
    }
}
