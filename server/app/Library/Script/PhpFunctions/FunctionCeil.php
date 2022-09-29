<?php

namespace App\Library\Script\PhpFunctions;

trait FunctionCeil
{
    /**
     * @param float $value
     * @return int
     */
    public function function_ceil(float $value): int
    {
        return (int)ceil($value);
    }
}
