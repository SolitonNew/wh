<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class PropertysModel extends Model
{
    protected $table = 'core_propertys';
    public $timestamps = false;
    
    /**
     * 
     * @return type
     */
    static public function getWebColors() {
        $item = self::whereName('WEB_COLOR')->first();
        if ($item && $item->value) {
            return json_decode($item->value, true);
        } else {
            return [];
        }
    }
    
    /**
     * 
     * @return type
     */
    static public function getWebChecks() {
        $item = self::whereName('WEB_CHECKED')->first();
        if ($item && $item->value) {
            return $item->value;
        } else {
            return '';
        }
    }
    
    /**
     * 
     * @return type
     */
    static public function runningDemons() {
        $item = self::whereName('RUNNING_DEMONS')->first();
        if ($item && $item->value) {
            return explode(';', $item->value);
        } else {
            return [];
        }
    }
    
    /**
     * 
     * @param type $demon
     */
    static public function setAsRunningDemon($demon) {
        $a = self::runningDemons();
        if (!in_array($demon, $a)) {
            $a[] = $demon;
            $item = self::whereName('RUNNING_DEMONS')->first();
            if (!$item) {
                $item = new PropertysModel();
                $item->name = 'RUNNING_DEMONS';
                $item->comm = '';
            }
            $item->value = implode(';', $a);
            $item->save();
        }
    }
    
    /**
     * 
     * @param type $demon
     */
    static public function setAsStoppedDemon($demon) {
        $a = self::runningDemons();
        if (in_array($demon, $a)) {
            array_splice($a, array_search($demon, $a));
            $item = self::whereName('RUNNING_DEMONS')->first();
            if (!$item) {
                $item = new PropertysModel();
                $item->name = 'RUNNING_DEMONS';
                $item->comm = '';
            }
            $item->value = implode(';', $a);
            $item->save();
        }
    }
    
    /**
     * 
     * @return int
     */
    static public function getPlanMaxLevel() {
        $item = self::whereName('PLAN_MAX_LEVEL')->first();
        if ($item && $item->value) {
            return $item->value;
        } else {
            return 1;
        }
    }
    
    /**
     * 
     * @param type $maxLevel
     */
    static public function setPlanMaxLevel($maxLevel) {
        $item = self::whereName('PLAN_MAX_LEVEL')->first();
        if ($item) {
            $item->value = $maxLevel;
        } else {
            $item = new PropertysModel();
            $item->name = 'PLAN_MAX_LEVEL';
            $item->comm = '';
            $item->value = $maxLevel;
        }
        $item->save();
    }
    
    /**
     * 
     * @return string
     */
    static public function getRs485Command($clear = false) {
        $item = self::whereName('RS485_COMMAND')->first();
        if ($item) {
            $value = $item->value;
            if ($clear) {
                $item->value = '';
                $item->save();
            }
            return $value;
        }
        return '';
    }
    
    /**
     * 
     * @param type $command
     */
    static public function setRs485Command($command) {
        $item = self::whereName('RS485_COMMAND')->first();
        $item->value = $command;
        $item->save();
    }
}
