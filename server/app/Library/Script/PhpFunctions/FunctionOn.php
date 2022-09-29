<?php

namespace App\Library\Script\PhpFunctions;

trait FunctionOn
{
    /**
     * @param string $name
     * @param int $time
     * @return void
     * @throws \Exception
     */
    public function function_on(string $name, int $time = 0): void
    {
        $this->function_set($name, 1, $time);
    }
}
