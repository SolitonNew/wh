<?php

namespace App\Http\Models;

use \App\Library\AffectsFirmwareModel;
use Lang;
use Log;
use DB;

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
     * Makes all the necessary attributes to create a device label.
     * 
     * @param type $app_control
     * @return object
     */
    static public function decodeAppControl($app_control) 
    {
        $control = '';
        $typ = -1; // 1-label; 2-switch; 3-track;
        $resolution = '';
        $varMin = 0;
        $varMax = 10;
        $varStep = 1;    
        switch ($app_control) {
            case 1: // Light
                $control = Lang::get('admin/hubs.log_app_control.1');
                $typ = 2;
                break;
            case 3: // Socket
                $control = '';
                $typ = 2;
                break;
            case 4: // Termometr
                $control = Lang::get('admin/hubs.log_app_control.4');
                $typ = 1;
                $resolution = '°C';
                break;
            case 5: // Termostat
                $control = Lang::get('admin/hubs.log_app_control.5');
                $typ = 3;
                $resolution = '°C';
                $varMin = 15;
                $varMax = 30;
                $varStep = 1;
                break;
            case 7: // Fan
                $control = Lang::get('admin/hubs.log_app_control.7');
                $typ = 3;
                $resolution = '%';
                $varMin = 0;
                $varMax = 100;
                $varStep = 10;
                break;
            case 10: // Humidity sensor
                $control = Lang::get('admin/hubs.log_app_control.10');
                $typ = 1;
                $resolution = '%';
                break;
            case 11: // Gas sensor
                $control = Lang::get('admin/hubs.log_app_control.11');
                $typ = 1;
                $resolution = 'ppm';
                break;
            case 13: // Atm. pressure
                $control = '';
                $typ = 1;
                $resolution = 'mm';
                break;
            case 14: // Current sensor
                $control = Lang::get('admin/hubs.log_app_control.14');
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
     * 
     * @param type $groupName
     * @param type $variableName
     * @param type $appControlLabel
     * @return string
     */
    static public function groupVariableName($groupName, $variableName, $appControlLabel) 
    {
        $resLabel = '';
        if ($appControlLabel != '') {
            $resLabel = $appControlLabel.' ';
        }    
        return $resLabel.mb_strtoupper(str_replace($groupName, '', $variableName));
    }
    
    /**
     * Sets the device value using a stored procedure.
     * 
     * @param int $deviceID
     * @param float $value
     */
    static public function setValue(int $deviceID, float $value)
    {
        try {
            DB::select("CALL CORE_SET_VARIABLE($deviceID, $value, -1)");
        } catch (\Exception $e) {
            Log::error($e);
        }
    }
}
