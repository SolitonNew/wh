<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use \Carbon\Carbon;
use Lang;
use Log;

class ScheduleModel extends Model
{
    protected $table = 'core_schedule';
    public $timestamps = false;
    
    /**
     * Creates a new record of the schedule for one-time execution.
     * This is for INTERVAL_TYPE = 4
     * The settings are minimal.
     * 
     * @param type $comm
     * @param type $action
     * @param type $datetime
     * @param type $variableID
     */
    static public function appendFastRecord($comm, $action, $datetime, $variableID) 
    {
        $item = new SchedulerModel();
        $item->comm = $comm;
        $item->action = $action;
        $item->action_datetime = $datetime;
        $item->interval_time_of_day = '';
        $item->interval_day_of_type = '';
        $item->interval_type = 4;
        $item->temp_variable_id = $variableID;
        $item->enable = 1;
        $item->save();
    }
    
    
    /**
     * The special keywords for variable labels of time.
     * 
     * @var string
     */
    private $_KEYS = [
        'SUNRISE',  // Восход солнца
        'SUNSET'    // Закат солнца
    ];
    
    /**
     * This method creates a time label using the propertys of the entry.
     * 
     * @return type
     */
    public function makeDateTime() 
    {
        $action_datetime = Carbon::parse($this->action_datetime);
        $now = now()->startOfDay();
        
        // Processing time intervals
        $dates = [];
        switch ($this->interval_type) {
            case 0: // Каждый день
                $dates[] = $now;
                $dates[] = $now->copy()->addDay();
                break;
            case 1: // Every week
                // Retrieve date of this week's monday
                $dw = $now->copy()->addDay(-$now->dayOfWeek);
                // Retrieve date of next week's monday
                $dw_next = $dw->copy()->addWeek();
                $week_days = Lang::get('admin/schedule.week_days');
                foreach(explode(',', $this->interval_day_of_type) as $w) {
                    try {
                        $i = array_search(mb_strtolower(trim($w)), $week_days);
                        $dates[] = $dw->copy()->addDay($i);
                        $dates[] = $dw_next->copy()->addDay($i);                        
                    } catch (\Exception $ex) {

                    }
                }
                break;
            case 2: // Every month
                // Retrieve date of the first day of this month
                $dw = $now->copy()->addDay(-$now->day + 1);
                // Retrieve date of the first day of next month
                $dw_next = $dw->copy()->addMonth();
                foreach(explode(',', $this->interval_day_of_type) as $n) {
                    try {
                        $i = trim($n) - 1;
                        $dates[] = $dw->copy()->addDay($i);
                        $dates[] = $dw_next->copy()->addDay($i);                        
                    } catch (\Exception $ex) {

                    }
                }
                break;
            case 3: // Every year
            case 4: // one time
                foreach(explode(',', $this->interval_day_of_type) as $day) {
                    $d = explode('-', $day);
                    if (count($d) > 1) {
                        try {
                            $dw = Carbon::create($now->year, $d[1], $d[0], 0, 0, 0);
                            $dw_next = $dw->copy()->addYear();
                            $dates[] = $dw;
                            $dates[] = $dw_next;                            
                        } catch (\Exception $ex) {

                        }
                    }
                }
                break;
        }
        
        
        // Combining date and time into one number for sorting
        $dt = [];
        foreach($dates as $dat) {
            foreach($this->_makeTime($dat) as $tim) {
                $dt[] = $tim->timestamp;
            }
        }
        
        // Sorting date and time
        sort($dt);

        // We check which date from the schedule is the closest to be fulfilled
        foreach($dt as $d) {
            $curr = Carbon::createFromTimestamp($d);
            if ($curr->gt($action_datetime)) {
                return $curr;
            }
        }
        
        return null;
    }
    
    /**
     * Parses a string with timestamps and returns converted timestamps in seconds
     * 
     * @param Carbon $date
     * @return type
     */
    private function _makeTime(Carbon $date) 
    {
        $times = [];
        $time_of_day = mb_strtoupper($this->interval_time_of_day);
        foreach(explode(',', $time_of_day) as $time_val) {
            $time_type = '';
            $time_str = trim($time_val);
            // Check sunrise/sunset
            foreach($this->_KEYS as $key) {
                if (strpos($key, $time_str) !== false) {
                    $time_type = $key;
                    break;
                }
            }
            
            if ($time_type == '') { // Is it time
                try {
                    $t = explode(':', $time_str);
                    $h = 0;
                    if (count($t) > 0) {
                        $h = $t[0] * 3600;
                    }
                    $m = 0;
                    if (count($t) > 1) {
                        $m = $t[1] * 60;
                    }
                    $s = 0;
                    if (count($t) > 2) {
                        $s = $t[2];
                    }
                    $times[] = $date->copy()->addSecond($h + $m + $s);
                } catch (\Exception $ex) {
                    Log::error($ex->getMessage());
                }
            } else { // Is it sunrise or sunset
                try {
                    $latitude = config('app.location_latitude');
                    $longitude = config('app.location_longitude');
                    $zenith = 90.8333333333333;
                    $times[] = \App\Library\SunTime::get($date, $latitude, $longitude, $zenith, $time_type);
                } catch (\Exception $ex) {
                    Log::error($ex->getMessage());
                }
            }
        }
        
        return $times;
    }    
}
