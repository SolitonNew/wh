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
     * Создает новую запись расписания для одноразового выполнения.
     * Это для INTERVAL_TYPE = 4
     * Настройки минимальны.
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
    public function makeDateTime() 
    {
        $action_datetime = Carbon::parse($this->action_datetime);
        $now = now()->startOfDay();
        
        // Разбираемся с интервалами дат
        $dates = [];
        switch ($this->interval_type) {
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
                foreach(explode(',', $this->interval_day_of_type) as $w) {
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
                foreach(explode(',', $this->interval_day_of_type) as $n) {
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
    private function _makeTime(Carbon $date) 
    {
        $times = [];
        $time_of_day = mb_strtoupper($this->interval_time_of_day);
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
                    $times[] = \App\Library\SunTime::get($date, $latitude, $longitude, $zenith, $time_type);
                } catch (\Exception $ex) {
                    Log::error($ex->getMessage());
                }
            }
        }
        
        return $times;
    }    
}
