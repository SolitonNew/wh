<?php

namespace App\Library\Script\PhpFunctions;

trait FunctionOff
{
    /**
     * @param string $name
     * @param int $time
     * @return void
     * @throws \Exception
     */
    public function function_off(string $name, int $time = 0): void
    {
        $this->function_set($name, 0, $time);
    }
}
