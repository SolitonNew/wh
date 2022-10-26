<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Http\Request;
use \Carbon\Carbon;
use App\Library\SunTime;
use Illuminate\Support\Facades\Lang;
use Log;

class Schedule extends Model
{
    protected $table = 'core_schedule';
    public $timestamps = false;

    /**
     * @return Collection
     */
    public static function listAll(): Collection
    {
        $data = Schedule::whereTempDeviceId(0)
                    ->orderBy('comm', 'asc')
                    ->get();

        $types = Lang::get('admin/schedule.interval');
        $interval_time = Lang::get('admin/schedule.interval_time');
        $interval_day = Lang::get('admin/schedule.interval_day');

        foreach ($data as &$row) {
            $s = $types[$row->interval_type];
            $s .= ' '.$interval_time.': <b>'.$row->interval_time_of_day.'</b>';

            switch($row->interval_type) {
                case 0: // Every day
                    //
                    break;
                case 1: // Every week
                case 2: // Every month
                case 3: // Every year
                    $s .= ' '.$interval_day.': <b>'.$row->interval_day_of_type.'</b>';
                    break;
            }

            $row->interval_text = $s;
        }

        return $data;
    }

    /**
     * @param int $id
     * @return Schedule
     */
    public static function findOrCreate(int $id): Schedule
    {
        $item = Schedule::find($id);
        if (!$item) {
            $item = new Schedule();
            $item->id = -1;
            $item->interval_type = 0;
            $item->enable = 0;
        }

        return $item;
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|string
     */
    public static function storeFromRequest(Request $request, int $id)
    {
        // Validation  ----------------------
        $rules = [
            'comm' => 'required|string',
            'action' => 'required|string',
            'interval_time_of_day' => 'required|string',
            'interval_day_of_type' => 'string|'.(in_array($request->interval_type, [1, 2, 3]) ? 'required' : 'nullable'),
        ];

        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        // Saving -----------------------
        try {
            $item = Schedule::find($id);
            if (!$item) {
                $item = new Schedule();
                $item->temp_device_id = 0;
            }
            $item->comm = $request->comm;
            $item->action = $request->action;
            $item->action_datetime = null;
            $item->interval_time_of_day = $request->interval_time_of_day;
            $item->interval_day_of_type = $request->interval_day_of_type;
            $item->interval_type = $request->interval_type;
            $item->enable = $request->enable;
            $item->save();
            return 'OK';
        } catch (\Exception $ex) {
            return response()->json([
                'errors' => [$ex->getMessage()],
            ]);
        }
    }

    /**
     * @param int $id
     * @return void
     */
    public static function deleteById(int $id): void
    {
        try {
            $item = Schedule::find($id);
            $item->delete();
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }

    /**
     * Creates a new record of the schedule for one-time execution.
     * This is for INTERVAL_TYPE = 4
     * The settings are minimal.
     *
     * @param string $comm
     * @param string $action
     * @param $datetime
     * @param int|null $variableID
     * @return void
     */
    public static function appendFastRecord(string $comm, string $action, $datetime, int|null $variableID): void
    {        
        Schedule::whereTempDeviceId($variableID)->delete();
        
        $item = new Schedule();
        $item->comm = $comm;
        $item->action = $action;
        $item->action_datetime = $datetime;
        $item->interval_time_of_day = '';
        $item->interval_day_of_type = '';
        $item->interval_type = 4;
        $item->temp_device_id = $variableID;
        $item->enable = 1;
        $item->save();
    }


    /**
     * The special keywords for variable labels of time.
     *
     * @var array|string[]
     */
    private array $_KEYS = [
        'SUNRISE',
        'SUNSET',
    ];

    /**
     * This method creates a time label using the properties of the entry.
     *
     * @return Carbon|null
     */
    public function makeDateTime(): Carbon|null
    {
        $action_datetime = Carbon::parse($this->action_datetime, 'UTC');
        $now = now(Property::getTimezone())->startOfDay();

        // Processing time intervals
        $dates = [];
        switch ($this->interval_type) {
            case 0: // Every day
                $dates[] = $now;
                $dates[] = $now->copy()->addDay();
                break;
            case 1: // Every week
                // Retrieve date of this week's monday
                $dw = $now->copy()->addDay(-$now->dayOfWeek);
                // Retrieve date of next week's monday
                $dw_next = $dw->copy()->addWeek();
                $week_days = Lang::get('admin/schedule.week_days');
                foreach (explode(',', $this->interval_day_of_type) as $w) {
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
                foreach (explode(',', $this->interval_day_of_type) as $n) {
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
                foreach (explode(',', $this->interval_day_of_type) as $day) {
                    $d = explode('-', $day);
                    if (count($d) > 1) {
                        try {
                            $dw = Carbon::create($now->year, $d[1], $d[0], 0, 0, 0, 'UTC');
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
        foreach ($dates as $dat) {
            foreach ($this->makeTime($dat) as $tim) {
                $dt[] = $tim->timestamp;
            }
        }

        // Sorting date and time
        sort($dt);

        // We check which date from the schedule is the closest to be fulfilled
        foreach ($dt as $d) {
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
     * @return array
     */
    private function makeTime(Carbon $date): array
    {
        $times = [];
        $time_of_day = mb_strtoupper($this->interval_time_of_day);
        foreach (explode(',', $time_of_day) as $time_val) {
            $time_type = '';
            $time_str = trim($time_val);
            // Check sunrise/sunset
            foreach ($this->_KEYS as $key) {
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
                    $location = Property::getLocation();
                    $zenith = 90.8333333333333;
                    $times[] = SunTime::get($date, $location->latitude, $location->longitude, $zenith, $time_type);
                } catch (\Exception $ex) {
                    Log::error($ex->getMessage());
                }
            }
        }

        return $times;
    }
}
