<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $table = 'core_propertys';
    public $timestamps = false;
    
    /**
     * 
     * @return type
     */
    static public function getWebColors() 
    {
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
    static public function getWebChecks() 
    {
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
    static public function runningDaemons() 
    {
        $item = self::whereName('RUNNING_DAEMONS')->first();
        if ($item && $item->value) {
            return explode(';', $item->value);
        } else {
            return [];
        }
    }
    
    /**
     * 
     * @param type $daemon
     */
    static public function setAsRunningDaemon($daemon) 
    {
        $a = self::runningDaemons();
        if (!in_array($daemon, $a)) {
            $a[] = $daemon;
            $item = self::whereName('RUNNING_DAEMONS')->first();
            if (!$item) {
                $item = new Property();
                $item->name = 'RUNNING_DAEMONS';
                $item->comm = '';
            }
            $item->value = implode(';', $a);
            $item->save();
        }
    }
    
    /**
     * 
     * @param type $daemon
     */
    static public function setAsStoppedDaemon($daemon) 
    {
        $a = self::runningDaemons();
        if (in_array($daemon, $a)) {
            array_splice($a, array_search($daemon, $a));
            $item = self::whereName('RUNNING_DAEMONS')->first();
            if (!$item) {
                $item = new Property();
                $item->name = 'RUNNING_DAEMONS';
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
    static public function getPlanMaxLevel() 
    {
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
    static public function setPlanMaxLevel($maxLevel) 
    {
        $item = self::whereName('PLAN_MAX_LEVEL')->first();
        if ($item) {
            $item->value = $maxLevel;
        } else {
            $item = new Property();
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
    static public function getDinCommand($clear = false) 
    {
        $item = self::whereName('DIN_COMMAND')->first();
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
    static public function setDinCommand($command) 
    {
        $item = self::whereName('DIN_COMMAND')->first();
        $item->value = $command;
        $item->save();
    }
    
    /**
     * 
     * @return string
     */
    static public function getDinCommandInfo() 
    {
        $item = self::whereName('DIN_COMMAND_INFO')->first();
        if ($item) {
            return $item->value;
        }
        return '';
    }
    
    /**
     * 
     * @param type $text
     * @param type $new
     */
    static public function setDinCommandInfo($text, $first = false) 
    {
        $item = self::whereName('DIN_COMMAND_INFO')->first();
        if (!$item) {
            $item = new Property();
            $item->name = 'DIN_COMMAND_INFO';
            $item->comm = '';
        }
        if ($first) {
            $item->value = $text;
        } else {
            $item->value .= $text;
        }
        $item->save();
    }
    
    /**
     * The cache for getFirmwareChanges.
     */
    static protected $_firmwareChanges = false;
    
    /**
     * Returns the number of changes to the DB (what affects the firmware)
     * siens the last update.
     * 
     * @return int
     */
    static public function getFirmwareChanges() 
    {
        if (self::$_firmwareChanges === false) {
            $item = self::whereName('WIRMWARE_CHANGES')->first();
            if ($item) {
                self::$_firmwareChanges = $item->value ?? 0;
            } else {
                self::$_firmwareChanges = 0;
            }
        }
        return self::$_firmwareChanges;
    }
    
    /**
     * Sets the number of changes to the DB (what affects the firmware).
     * 
     * @param int $count
     */
    static public function setFirmwareChanges(int $count) 
    {
        $item = self::whereName('WIRMWARE_CHANGES')->first();
        if (!$item) {
            $item = new Property();
            $item->name = 'WIRMWARE_CHANGES';
            $item->comm = '';
        }
        $item->value = $count;
        $item->save();
        
        self::$_firmwareChanges = $count;
    }
    
}