<?php

namespace App\Library\Script;

use App\Models\ScriptString;

class TranslateDB extends Translate
{
    /**
     * 
     * @param type $parts
     * @param type $strings
     */
    protected function _prepareStrings(&$parts, &$strings)
    {
        for ($i = 0; $i < count($parts); $i++) {
            if (!is_object($parts[$i]) && isset($strings[$parts[$i]])) {
                $data = substr($parts[$i], 1, strlen($parts[$i]) - 2);
                $parts[$i] = ScriptString::setData($data);
            }
        }
    }
}
