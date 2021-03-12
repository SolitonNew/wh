<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Artisan;
use App\Library\DemonManager;

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
                'id' => $dem,
                'stat' => $stat,
            ];
            if ($dem == $id) {
                $currStat = $stat;
            }
        }

        return view('admin.demons.demons', [
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
                    ->where('id', '>', $lastID)
                    ->orderby('id', 'desc')
                    ->limit(config("app.admin_demons_log_lines_count"))
                    ->get();

        foreach($data as &$row) {
            $str = $row->data;            
            $str = str_replace('[', '<span class="datetime">[', $str);
            $str = str_replace(']', ']</span>', $str);
            
            $row->data = $str;
        }

        return view('admin.demons.demon-log', [
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
            \App\Http\Models\PropertysModel::setAsRunningDemon($id);
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
            \App\Http\Models\PropertysModel::setAsStoppedDemon($id);
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
            \App\Http\Models\PropertysModel::setAsRunningDemon($id);
            $demonManager->restart($id);
            return 'OK';
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }    
}
