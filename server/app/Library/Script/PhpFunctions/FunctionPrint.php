<?php

namespace App\Library\Script\PhpFunctions;

trait FunctionPrint
{
    /**
     * @param string $text
     * @return void
     */
    public function function_print(string $text): void
    {
        $this->printLine('>>> '.$text);
    }
}
