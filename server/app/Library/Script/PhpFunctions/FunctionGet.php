<?php

namespace App\Library\Script\PhpFunctions;

use App\Models\Device;

trait FunctionGet
{
    /**
     * @param string $name
     * @return float
     * @throws \Exception
     */
    public function function_get(string $name): float
    {
        $variable = Device::whereName($name)->first();

        if ($variable) {
            return $variable->value;
        } else {
            throw new \Exception("Variable '$name' not found");
        }
    }
}
