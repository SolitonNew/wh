<?php

namespace App\Library\Script\PhpFunctions;

trait FunctionRound
{
    /**
     * @param float $value
     * @return int
     */
    public function function_round(float $value): int
    {
        return (int)round($value);
    }
}
