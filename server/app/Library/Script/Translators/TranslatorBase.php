<?php

namespace App\Library\Script\Translators;

use App\Library\Script\ScriptStringManager;

/**
 *
 * @author soliton
 */
abstract class TranslatorBase
{
    protected ScriptStringManager|null $stringManager = null;

    public function __construct(ScriptStringManager $stringManager = null)
    {
        $this->stringManager = $stringManager;
    }

    /**
     * @param object $data
     * @return string
     */
    public function translate(object $data): string
    {

    }
}
