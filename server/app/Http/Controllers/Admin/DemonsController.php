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
            $str = $row->DATA;
            
            $str = str_replace('[', '<span class="datetime">[', $str);
            $str = str_replace(']', ']</span>', $str);
            
            $i1 = mb_strpos($str, '-- '); 
            $i2 = mb_strpos($str, ' --'); 
            if (($i1 !== false) && ($i2 !== false)) {
                $str = '<div class="demon-log-header">'.mb_substr($str, $i1 + 3, $i1 + $i2 - 3).'</div>';
            }
            
            if (mb_strpos($str, '------') !== false) {
                $str = '<hr class="demon-log-hr">';
            }
            
            $row->DATA = $str;
        }

        return view('admin.demon-log', [
            'data' => $data,
        ]);
    }

    /**
     * 
     * @param string $id
     * @return string
     */
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

    /**
     * 
     * @param string $id
     * @return string
     */
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

    /**
     * 
     * @param string $id
     * @return string
     */
    public function demonRestart(string $id) {
        if (!in_array($id, self::$demons)) return 'ERROR';
        
        try {
            foreach($this->_getDemonPID($id) as $pid) {
                exec('kill -9 '.$pid);
            }
            
            for ($i = 0; $i < 100; $i++) {
                if (count($this->_getDemonPID($id)) == 0) {
                    break;
                }
                usleep(50000);
            }
            
            if (count($this->_getDemonPID($id)) == 0) {
                exec('php '.base_path().'/artisan '.$id.'>/dev/null &');
                sleep(1);
            
                return 'OK';
            }
            return 'ERROR';
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * 
     * @param string $id
     * @return type
     */
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
