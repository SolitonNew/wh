<?php

namespace App\Library\Script\PhpFunctions;

use App\Models\ScriptString;

trait FunctionPlay
{
    /**
     * @param int $targetID
     * @param int $mediaID
     * @param int ...$args
     * @return void
     */
    public function function_play(int $targetID, int $mediaID, int ...$args): void
    {
        $target = ScriptString::getStringById($targetID);
        $media = ScriptString::getStringById($mediaID);
    }
}
