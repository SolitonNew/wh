<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Script\Translators;

/**
 *
 * @author soliton
 */
interface ITranslator 
{
    /**
     * 
     * @param type $source
     */
     public function translate($prepareData);
}
