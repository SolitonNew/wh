<?php

namespace App\Library\Script;

use App\Models\ScriptString;
use Illuminate\Support\Facades\Log;

class ScriptStringManager
{
    /**
     * @var array
     */
    private array $specialList = [];

    /**
     * @param array $specialList   [string] => [key|null]
     */
    public function __construct(array $specialList = [])
    {
        $this->specialList = $specialList;
    }

    /**
     * @param string $string
     * @return int|bool
     */
    public function getKeyByString(string $string): int|bool
    {
        if (isset($this->specialList[$string])) {
            return $this->specialList[$string];
        } else {
            return ScriptString::getIdByString($string);
        }
    }
}
