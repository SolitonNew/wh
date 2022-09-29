<?php

namespace App\Library\Script\PhpFunctions;

trait FunctionFloor
{
    /**
     * @param float $value
     * @return int
     */
    public function function_floor(float $value): int
    {
        return (int)floor($value);
    }
}
