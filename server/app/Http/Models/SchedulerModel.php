<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use \Carbon\Carbon;
use Lang;

class SchedulerModel extends Model
{
    protected $table = 'core_scheduler';
    public $timestamps = false;
    protected $primaryKey = 'ID';
    
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
        $times = [];
        $dates = [];
        
        // Определяем время
        // Подчищаем возможные лишние символы
        $time_of_day = mb_strtoupper($this->INTERVAL_TIME_OF_DAY);
        foreach($this->_KEYS as $v) {
            if (strpos($v, $time_of_day) !== false) {
                $time_of_day = $v;
                break;
            }
        }
        
        if (in_array($time_of_day, $this->_KEYS)) {
            return null;
        } else {
            foreach(explode(',', $time_of_day) as $v) {
                try {
                    $t = explode(':', $v);
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
                    $times[] = $h + $m + $s;   
                } catch (\Exception $ex) {

                }
            }
        }
        
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
        
        $dt = [];

        // Собираем дату и время расписания в одно
        foreach($times as $tim) {
            foreach($dates as $dat) {
                $dt[] = $dat->copy()->addSeconds($tim)->timestamp;
            }
        }
        
        // Сортируем дату и время
        sort($dt);

        // Проверяем какая дата из расписания готова к выполнению
        foreach($dt as $d) {
            $curr = Carbon::createFromTimestamp($d);
            if ($curr->gt($action_datetime)) {
                return $curr;
            }
        }
        
        return null;
    }
    
}
