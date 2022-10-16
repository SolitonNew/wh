<?php

namespace App\Library\Script\Translators;

/**
 *
 * @author soliton
 */
interface ITranslator
{
    /**
     * @param object $data
     * @return string
     */
    public function translate(object $data): string;
}
