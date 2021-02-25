<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Artisan;

class DemonsController extends Controller
{
    private $_demons = [
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
            $id = $this->_demons[0];
            return redirect(route('demons', $id));
        }

        if (!in_array($id, $this->_demons)) return 'ERROR';

        return view('admin.demons', [
            'id' => $id,
            'demons' => $this->_demons,
        ]);
    }

    /**
     *
     * @param string $id
     * @param int $lastID
     * @return string
     */
    public function data(string $id, int $lastID = -1) {
        if (!in_array($id, $this->_demons)) return 'ERROR';

        $data = \App\Http\Models\WebLogsModel::whereDemon($id)
                    ->where('ID', '>', $lastID)
                    ->orderBy('ID', 'desc')
                    ->limit(100)
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
        if (!in_array($id, $this->_demons)) return 'ERROR';

        try {
            exec('php '.base_path().'/artisan '.$id);
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }

    public function demonStop(string $id) {
        if (!in_array($id, $this->_demons)) return 'ERROR';



        return 'OK';
    }

    public function demonRestart(string $id) {
        if (!in_array($id, $this->_demons)) return 'ERROR';

        return 'OK';
    }
}
