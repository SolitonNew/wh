<?php

namespace App\Http\Models;

use \App\Library\AffectsFirmwareModel;

class VariablesModel extends AffectsFirmwareModel
{    
    protected $table = 'core_variables';
    public $timestamps = false;
    
    protected $_affectFirmwareFields = [
        'controller_id',
        'typ',
        'ow_id',
        'direction',
        'name',
        'channel',
    ];
    
    /**
     * 
     * @param type $app_control
     * @return type
     */
    static public function decodeAppControl($app_control) {
        $control = '';
        $typ = -1; // 1-label; 2-switch; 3-track;
        $resolution = '';
        $varMin = 0;
        $varMax = 10;
        $varStep = 1;    
        switch ($app_control) {
            case 1: // Лампочка
                $control = 'СВЕТ';
                $typ = 2;
                break;
            case 3: // Розетка
                $control = '';
                $typ = 2;
                break;
            case 4: // Термометр
                $control = 'ТЕРМОМЕТР';
                $typ = 1;
                $resolution = '°C';
                break;
            case 5: // Термостат
                $control = 'ТЕРМОСТАТ';
                $typ = 3;
                $resolution = '°C';
                $varMin = 15;
                $varMax = 30;
                $varStep = 1;
                break;
            case 7: //Вентилятор
                $control = 'ВЕНТИЛЯЦИЯ';
                $typ = 3;
                $resolution = '%';
                $varMin = 0;
                $varMax = 100;
                $varStep = 10;
                break;
            case 10: //Гигрометр
                $control = 'ВЛАЖНОСТЬ';
                $typ = 1;
                $resolution = '%';
                break;
            case 11: // Датчик газа
                $control = 'СО';
                $typ = 1;
                $resolution = 'ppm';
                break;
            case 13: // Атм. давление
                $control = '';
                $typ = 1;
                $resolution = 'mm';
                break;
            case 14: // Датчик тока
                $control = 'ТОК';
                $typ = 1;
                $resolution = 'A';
                break;
        }

        return (object)[
            'label' => $control,
            'typ' => $typ,
            'resolution' => $resolution,
            'varMin' => $varMin,
            'varMax' => $varMax,
            'varStep' => $varStep
        ];
    }
    
    /**
     * 
     * @param type $groupName
     * @param type $variableName
     * @param type $appControlLabel
     * @return type
     */
    static public function groupVariableName($groupName, $variableName, $appControlLabel) {
        $resLabel = '';
        if ($appControlLabel != '') {
            $resLabel = $appControlLabel.' ';
        }    
        return $resLabel . mb_strtoupper(str_replace($groupName, '', $variableName));
    }
}
