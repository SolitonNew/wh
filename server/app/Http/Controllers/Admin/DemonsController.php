<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Artisan;

class DemonsController extends Controller
{
    static public $demons = [
        'rs485-demon',
        'schedule-demon',
        'command-demon',
        'observer-demon',
    ];

    /**
     *
     * @param string $id
     * @return type
     */
    public function index(string $id = null) {
        if (!$id) {
            $id = self::$demons[0];
            return redirect(route('demons', $id));
        }

        if (!in_array($id, self::$demons)) return 'ERROR';

        $currStat = 0;
        $demons = [];
        foreach(self::$demons as $dem) {
            $stat = count($this->_getDemonPID($dem)) ? 1 : 0;
            $demons[] = (object)[
                'ID' => $dem,
                'STAT' => $stat,
            ];
            if ($dem == $id) {
                $currStat = $stat;
            }
        }

        return view('admin.demons', [
            'id' => $id,
            'stat' => $currStat,
            'demons' => $demons,
        ]);
    }

    /**
     *
     * @param string $id
     * @param int $lastID
     * @return string
     */
    public function data(string $id, int $lastID = -1) {
        if (!in_array($id, self::$demons)) return 'ERROR';

        $data = \App\Http\Models\WebLogsModel::whereDemon($id)
                    ->where('ID', '>', $lastID)
                    ->orderBy('ID', 'desc')
                    ->limit(config("app.admin_demons_log_lines_count"))
                    ->get();

        foreach($data as &$row) {
            $row->DATA = str_replace('[', '<span class="datetime">[', $row->DATA);
            $row->DATA = str_replace(']', ']</span>', $row->DATA);
        }

        return view('admin.demon-log', [
            'data' => $data,
        ]);
    }

    public function demonStart(string $id) {
        if (!in_array($id, self::$demons)) return 'ERROR';

        try {
            exec('php '.base_path().'/artisan '.$id.'>/dev/null &');
            sleep(1);
            return 'OK';
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function demonStop(string $id) {
        if (!in_array($id, self::$demons)) return 'ERROR';

        try {
            foreach($this->_getDemonPID($id) as $pid) {
                exec('kill -9 '.$pid);
            }
            sleep(1);
            return 'OK';
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function demonRestart(string $id) {
        if (!in_array($id, self::$demons)) return 'ERROR';

        return 'OK';
    }

    private function _getDemonPID(string $id) {
        $pids = [];
        exec("ps ax | grep $id | grep -v grep | grep -v 'sh -c '", $outs);
        foreach($outs as $out) {
            $a = explode(' ', trim($out));
            if (count($a)) {
                $pids[] = $a[0];
            }
        }
        return $pids;
    }
}
