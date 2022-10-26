<?php

namespace App\Library\Server;

class System 
{
    public const TEMPERATURE_FILE = '/sys/class/thermal/thermal_zone0/temp';
    public const MEMORY_FILE = '/proc/meminfo';
    
    public function __construct() 
    {
        
    }
    
    /**
     * 
     * @return array
     */
    public function checkSources(): array
    {
        $res = [];
        if (!file_exists(self::TEMPERATURE_FILE)) {
            $res[] = 'Termal Control Is Impossible!';
        }
        
        if (!file_exists(self::MEMORY_FILE)) {
            $res[] = 'Memory Control Is Impossible!';
        }
        
        return $res;
    }
    
    /**
     * @return int
     */
    public function getProcessorTemperature(): int
    {
        if (!file_exists(self::TEMPERATURE_FILE)) return 0;
        
        $val = file_get_contents(self::TEMPERATURE_FILE);
        $temp = preg_replace("/[^0-9]/", "", $val);

        if ($temp > 200) {
            $temp = round($temp / 1000);
        } else {
            $temp = round($temp);
        }
        
        return $temp;
    }
    
    /**
     * @return array
     */
    public function getMemoryStatus(): array
    {
        $total = 0;
        $free = 0;
        
        $info = file_get_contents(self::MEMORY_FILE);
        foreach (explode("\n", $info) as $line) {
            if (str_starts_with($line, 'MemTotal:')) {
                $value = preg_replace("/[^0-9]/", "", $line);
                $total = round($value / 1024);
            } else
            if (str_starts_with($line, 'MemFree:')) {
                $value = preg_replace("/[^0-9]/", "", $line);
                $free = round($value / 1024);
            }
        }
        
        return [$total, $free];
    }
}
