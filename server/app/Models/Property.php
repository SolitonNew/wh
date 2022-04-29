<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $table = 'core_properties';
    public $timestamps = false;
    
    const VERSION = '2.2.3 alpha';
    
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
            $item = self::whereName('FIRMWARE_CHANGES')->first();
            if ($item) {
                self::$_firmwareChanges = $item->value ?: 0;
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
        $item = self::whereName('FIRMWARE_CHANGES')->first();
        if (!$item) {
            $item = new Property();
            $item->name = 'FIRMWARE_CHANGES';
            $item->comm = '';
        }
        $item->value = $count;
        $item->save();
        
        self::$_firmwareChanges = $count;
    }
    
    /**
     * 
     * @return type
     */
    static public function getTotalDaemons()
    {
        $manager = new \App\Library\DaemonManager();
        return count($manager->daemons());
    }
    
    /**
     * 
     * @return int
     */
    static public function getRunedDaemons()
    {
        $service = new \App\Services\Admin\DaemonsService();
        
        $count = 0;
        foreach ($service->daemonsList() as $daemon) {
            if ($daemon->stat) {
                $count++;
            }
        }
        
        return $count;
    }
    
    static private $_timezone = false;
    
    /**
     * 
     * @return type
     */
    static public function getTimezone()
    {
        if (self::$_timezone === false) {
            $item = self::whereName('TIMEZONE')->first();
            if ($item) {
                self::$_timezone = $item->value ?: 'UTC';
            } else {
                self::$_timezone = 'UTC';
            }
        }
        
        return self::$_timezone;
    }
    
    /**
     * 
     * @param type $timezone
     */
    static public function setTimezone($timezone)
    {
        $item = self::whereName('TIMEZONE')->first();
        if (!$item) {
            $item = new Property();
            $item->name = 'TIMEZONE';
            $item->comm = '';
        }
        
        $item->value = $timezone;
        $item->save();
        
        self::$_timezone = $timezone;
    }
    
    static private $_location = false;
    
    /**
     * 
     * @return type
     */
    static public function getLocation()
    {
        if (self::$_location === false) {
            $item = self::whereName('LOCATION')->first();
            if ($item && $item->value) {
                self::$_location = json_decode($item->value);
            } else {
                self::$_location = (object)[
                    'latitude' => 0,
                    'longitude' => 0,
                ];
            }
        }
        
        return self::$_location;
    }
    
    /**
     * 
     * @param type $latitude
     * @param type $longitude
     */
    static public function setLocation($latitude, $longitude)
    {
        $item = self::whereName('LOCATION')->first();
        if (!$item) {
            $item = new Property();
            $item->name = 'LOCATION';
            $item->comm = '';
        }
        
        self::$_location = (object)[
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];
        
        $item->value = json_encode(self::$_location);
        $item->save();
    }
    
    static private $_din_settings = false;
    
    /**
     * 
     * @return type
     */
    static public function getDinSettings()
    {
        if (self::$_din_settings === false) {
            $item = self::whereName('DIN_SETTINGS')->first();
            if ($item && $item->value) {
                self::$_din_settings = json_decode($item->value);
            } else {
                self::$_din_settings = (object)[
                    'port' => '/dev/ttyUSB0',
                    'mmcu' => 'atmega16a',
                ];
            }
        }
        
        return self::$_din_settings;
    }
    
    /**
     * 
     * @param type $port
     * @param type $mmcu
     */
    static public function setDinSettings($port, $mmcu)
    {
        $item = self::whereName('DIN_SETTINGS')->first();
        if (!$item) {
            $item = new Property();
            $item->name = 'DIN_SETTINGS';
            $item->comm = '';
        }
        
        self::$_din_settings = (object)[
            'port' => $port,
            'mmcu' => $mmcu,
        ];
        
        $item->value = json_encode(self::$_din_settings);
        $item->save();
    }
    
    static private $_forecast_settings = false;
    
    /**
     * 
     * @return type
     */
    static public function getForecastSettings()
    {
        if (self::$_forecast_settings === false) {
            $item = self::whereName('FORECAST_SETTINGS')->first();
            if ($item && $item->value) {
                self::$_forecast_settings = json_decode($item->value);
            } else {
                self::$_forecast_settings = (object)[
                    'TEMP' => '',
                    'P' => '',
                    'CC' => '',
                    'G' => '',
                    'H' => '',
                    'V' => '',
                    'WD' => '',
                    'WS' => '',
                    'MP' => '',
                ];
            }
        }
        
        return self::$_forecast_settings;
    }
    
    /**
     * 
     * @param type $temp
     * @param type $p
     * @param type $cc
     * @param type $g
     * @param type $h
     * @param type $v
     * @param type $wd
     * @param type $ws
     * @param type $mp
     */
    static public function setForecastSettings($temp, $p, $cc, $g, $h, $v, $wd, $ws, $mp)
    {
        $item = self::whereName('FORECAST_SETTINGS')->first();
        if (!$item) {
            $item = new Property();
            $item->name = 'FORECAST_SETTINGS';
            $item->comm = '';
        }
        
        self::$_forecast_settings = (object)[
            'TEMP' => $temp,
            'P' => $p,
            'CC' => $cc,
            'G' => $g,
            'H' => $h,
            'V' => $v,
            'WD' => $wd,
            'WS' => $ws,
            'MP' => $mp,
        ];
        
        $item->value = json_encode(self::$_forecast_settings);
        $item->save();
    }
    
    
}
