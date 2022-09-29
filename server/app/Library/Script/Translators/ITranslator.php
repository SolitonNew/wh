<?php

namespace App\Library\Script\Translators;

/**
 *
 * @author soliton
 */
interface ITranslator
{
    /**
     * @param object $prepareData
     * @return string
     */
     public function translate(object $prepareData): string;
}
