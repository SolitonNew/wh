<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Artisan;
use App\Classes\DemonManager;

class DemonsController extends Controller
{
    /**
     *
     * @param string $id
     * @return type
     */
    public function index(DemonManager $demonManager,  string $id = null) {        
        if (!$id) {
            $id = $demonManager->demons()[0];
            return redirect(route('demons', $id));
        }
        
        if (!$demonManager->exists($id)) {
            abort(404);
        }

        $currStat = 0;
        $demons = [];
        foreach($demonManager->demons() as $dem) {
            $stat = $demonManager->isStarted($dem);
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
    public function data(DemonManager $demonManager, string $id, int $lastID = -1) {
        if (!$demonManager->exists($id)) {
            abort(404);
        }

        $data = \App\Http\Models\WebLogsModel::whereDemon($id)
                    ->where('ID', '>', $lastID)
                    ->orderBy('ID', 'desc')
                    ->limit(config("app.admin_demons_log_lines_count"))
                    ->get();

        foreach($data as &$row) {
            $str = $row->DATA;            
            $str = str_replace('[', '<span class="datetime">[', $str);
            $str = str_replace(']', ']</span>', $str);
            
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
    public function demonStart(DemonManager $demonManager, string $id) {
        if (!$demonManager->exists($id)) {
            abort(404);
        }

        try {
            $demonManager->start($id);
            usleep(250000);
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
    public function demonStop(DemonManager $demonManager, string $id) {
        if (!$demonManager->exists($id)) {
            abort(404);
        }

        try {
            $demonManager->stop($id);
            usleep(250000);
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
    public function demonRestart(DemonManager $demonManager, string $id) {
        if (!$demonManager->exists($id)) {
            abort(404);
        }
        
        try {
            $demonManager->restart($id);
            return 'OK';
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }    
}
