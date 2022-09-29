<?php

namespace App\Library\Script\PhpFunctions;

trait FunctionAbs
{
    /**
     * @param int $value
     * @return int
     */
    public function function_abs_i(int $value): int
    {
        return (int)abs($value);
    }

    /**
     * @param float $value
     * @return float
     */
    public function function_abs_f(float $value): float
    {
        return abs($value);
    }
}
