<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use \Carbon\Carbon;
use Lang;
use Log;

class SchedulerModel extends Model
{
    protected $table = 'core_scheduler';
    public $timestamps = false;
    protected $primaryKey = 'ID';
    
    /**
     * Создает новую запись расписания для одноразового выполнения.
     * Это для INTERVAL_TYPE = 4
     * Настройки минимальны.
     * 
     * @param type $comm
     * @param type $action
     * @param type $datetime
     * @param type $variableID
     */
    static public function appendFastRecord($comm, $action, $datetime, $variableID) {
        $item = new SchedulerModel();
        $item->COMM = $comm;
        $item->ACTION = $action;
        $item->ACTION_DATETIME = $datetime;
        $item->INTERVAL_TIME_OF_DAY = '';
        $item->INTERVAL_DAY_OF_TYPE = '';
        $item->INTERVAL_TYPE = 4;
        $item->TEMP_VARIABLE_ID = $variableID;
        $item->ENABLE = 1;
        $item->save();
    }
    
    
    /**
     * Специальные ключевые слова для переменных временных меток
     * @var type 
     */
    private $_KEYS = [
        'SUNRISE',  // Восход солнца
        'SUNSET'    // Закат солнца
    ];
    
    /**
     * 
     * @return type
     */
    public function makeDateTime() {
        $action_datetime = Carbon::parse($this->ACTION_DATETIME);
        $now = now()->startOfDay();
        
        // Разбираемся с интервалами дат
        $dates = [];
        switch ($this->INTERVAL_TYPE) {
            case 0: // Каждый день
                $dates[] = $now;
                $dates[] = $now->copy()->addDay();
                break;
            case 1: // Каждую неделю
                // Получаем дату понедельника этой недели
                $dw = $now->copy()->addDay(-$now->dayOfWeek);
                // Получаем дату понедельника следующей недели
                $dw_next = $dw->copy()->addWeek();
                $week_days = Lang::get('admin/schedule.week_days');
                foreach(explode(',', $this->INTERVAL_DAY_OD_TYPE) as $w) {
                    try {
                        $i = array_search(mb_strtolower(trim($w)), $week_days);
                        $dates[] = $dw->copy()->addDay($i);
                        $dates[] = $dw_next->copy()->addDay($i);                        
                    } catch (\Exception $ex) {

                    }
                }
                break;
            case 2: // Каждый месяц
                // Получаем дату первого дня месяца
                $dw = $now->copy()->addDay(-$now->day + 1);
                // Получаем дату первого дня следующего месяца
                $dw_next = $dw->copy()->addMonth();
                foreach(explode(',', $this->INTERVAL_DAY_OF_TYPE) as $n) {
                    try {
                        $i = trim($n) - 1;
                        $dates[] = $dw->copy()->addDay($i);
                        $dates[] = $dw_next->copy()->addDay($i);                        
                    } catch (\Exception $ex) {

                    }
                }
                break;
            case 3: // Каждый год
            case 4: // Одноразово
                foreach(explode(',', $this->INTERVAL_DAY_OF_TYPE) as $day) {
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
        
        
        // Разбираемся с интервалами времени и собираем дат и время в одно 
        // число для сортировки
        $dt = [];
        foreach($dates as $dat) {
            foreach($this->_makeTime($dat) as $tim) {
                $dt[] = $tim->timestamp;
            }
        }
        
        // Сортируем дату и время
        sort($dt);

        // Проверяем какая дата из расписания ближайшая для выполнения
        foreach($dt as $d) {
            $curr = Carbon::createFromTimestamp($d);
            if ($curr->gt($action_datetime)) {
                return $curr;
            }
        }
        
        return null;
    }
    
    /**
     * Парсит строку с временными метками и вовзращает конвертированые метки в секундах
     * @return type
     */
    private function _makeTime(Carbon $date) {
        $times = [];
        $time_of_day = mb_strtoupper($this->INTERVAL_TIME_OF_DAY);
        foreach(explode(',', $time_of_day) as $time_val) {
            $time_type = '';
            $time_str = trim($time_val);
            // Проверяем ВОСХОД/ЗАКАТ
            foreach($this->_KEYS as $key) {
                if (strpos($key, $time_str) !== false) {
                    $time_type = $key;
                    break;
                }
            }
            
            if ($time_type == '') { // Это просто время
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
            } else { // Это восход/закат
                try {
                    $latitude = config('app.location_latitude');
                    $longitude = config('app.location_longitude');
                    $zenith = 90.8333333333333;
                    $times[] = $this->getSunTime($date, $latitude, $longitude, $zenith, $time_type);
                } catch (\Exception $ex) {
                    Log::error($ex->getMessage());
                }
            }
        }
        
        return $times;
    }
    
    
    /**
     * Вычисляет время восхода или заката на указанную дату с параметрами локации
     * 
     * @param Carbon $date
     * @param float $latitude
     * @param float $longitude
     * @param float $zenith
     * @param string $sunTime
     * @return type
     */
    public function getSunTime(Carbon $date, float $latitude, float $longitude, float $zenith, string $sunTime) {
        // 1. first calculate the day of the year
        $N = $date->copy()->dayOfYear();
    
        // 2. convert the longitude to hour value and calculate an approximate time

        $lngHour = $longitude / 15;
    
        if ($sunTime == 'SUNRISE') {
            $t = $N + ((6 - $lngHour) / 24);
        } else {
            $t = $N + ((18 - $lngHour) / 24);
        }

        // 3. calculate the Sun's mean anomaly

        $M = (0.9856 * $t) - 3.289;

        //4. calculate the Sun's true longitude
    
        $L = $M + (1.916 * sin(deg2rad($M))) + (0.020 * sin(deg2rad(2 * $M))) + 282.634;
        
        // NOTE: L potentially needs to be adjusted into the range [0,360) by adding/subtracting 360
        $L = $this->_adjust($L, 360);
 
        // 5a. calculate the Sun's right ascension
    
        $RA = rad2deg(atan(0.91764 * tan(deg2rad($L))));
        
        // NOTE: RA potentially needs to be adjusted into the range [0,360) by adding/subtracting 360
        $RA = $this->_adjust($RA, 360);
    
        // 5b. right ascension value needs to be in the same quadrant as L
    
        $Lquadrant = floor($L / 90) * 90;
        $RAquadrant = floor($RA / 90) * 90;
        $RA = $RA + ($Lquadrant - $RAquadrant);

        // 5c. right ascension value needs to be converted into hours

        $RA = $RA / 15;
    
        // 6. calculate the Sun's declination

        $sinDec = 0.39782 * sin(deg2rad($L));
        $cosDec = cos(asin($sinDec));

        // 7a. calculate the Sun's local hour angle

        $HCos = (cos(deg2rad($zenith)) - ($sinDec * sin(deg2rad($latitude)))) / ($cosDec * cos(deg2rad($latitude)));
        if (($HCos > 1) || ($HCos < -1)) {
            return null;
        }

        // 7b. finish calculating H and convert into hours

        if ($sunTime == 'SUNRISE') {
            $H = 360 - rad2deg(acos($HCos));
        } else {
            $H = rad2deg(acos($HCos));
        }
        
        $H = $H / 15;
        
        // 8. calculate local mean time of rising/setting
        $LocalT = $H + $RA - (0.06571 * $t) - 6.622;

        // 9. adjust back to UTC
        $UT = $LocalT - $lngHour;
        
        # NOTE: UT potentially needs to be adjusted into the range [0,24) by adding/subtracting 24
        $st = $this->_adjust($UT, 24);
        
        return Carbon::create($date->year, $date->month, $date->day, 0, 0, 0, 'UTC')->addSecond($st * 3600);
    }
    
    /**
     * 
     * @param type $value
     * @param type $bounds
     * @return type
     */
    private function _adjust($value, $bounds) {
        while ($value >= $bounds) {
            $value = $value - $bounds;
        }
        while ($value < 0) {
            $value = $value + $bounds;
        }
        
        return $value;
    }

    
}
