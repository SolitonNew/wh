<?php

namespace App\Http\Controllers\Terminal;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\Lang;
use \Illuminate\Support\Facades\DB;
use \App\Http\Models\PropertysModel;

class CheckedController extends Controller
{
    /**
     * 
     * @return type
     */
    public function index() {
        $web_color = PropertysModel::getWebColors();
        $web_checks = PropertysModel::getWebChecks();

        if ($web_checks) {
            $sql = "select v.* from core_variables v " .
                   " where v.ID in ($web_checks) ";
        } else {
            $sql = "select v.* from core_variables v " .
                   " where v.ID in (0) ";
        }
        
        $rows = [];
        $vars = DB::select($sql);
        foreach (explode(',', $web_checks) as $key) {
            for ($i = 0; $i < count($vars); $i++) {
                $row = $vars[$i];
                if ($row->ID == $key) {
                    $c = \App\Http\Models\VariablesModel::decodeAppControl($row->APP_CONTROL);
                    $itemLabel = mb_strtoupper($row->COMM);
                    $c->title = $c->label.' '.$itemLabel;

                    $rows[] = (object)[
                        'data' => $row, 
                        'control' => $c
                    ];
                    break;
                }
            }
        }        
        
        $charts = [];
        $varSteps = [];
        
        foreach ($rows as $row) {
            $itemLabel = $row->control->title;

            $color = '';
            for ($i = 0; $i < count($web_color); $i++) {
                if (mb_strpos(mb_strtoupper($itemLabel), mb_strtoupper($web_color[$i]['keyword'])) !== false) {
                    $color = $web_color[$i]['color'];
                    if ($color) {
                        $color = "'$color'";
                    }
                    break;
                }
            }

            $varSteps[] = "{id: ".$row->data->ID.", step: ".$row->control->varStep."}";
            
            if ($row->control->typ == 1) {
                $sql = "select UNIX_TIMESTAMP(v.CHANGE_DATE) * 1000 V_DATE, v.VALUE ".
                       "  from core_variable_changes_mem v ".
                       " where v.VARIABLE_ID = ".$row->data->ID.
                       "   and v.VALUE <> 85 ".
                       "   and v.CHANGE_DATE > (select max(zz.CHANGE_DATE) ".
                       "                          from core_variable_changes_mem zz ".
                       "                         where zz.VARIABLE_ID = ".$row->data->ID.") - interval 3 hour ".
                       " order by v.ID ";
                
                $chartData = [];
                foreach(DB::select($sql) as $row) {
                    $x = $row->V_DATE;
                    $y = $row->VALUE;
                    $data[] = "{x: $x, y: $y}";
                }
                
                $charts[] = (object)[
                    'ID' => $row->data->ID,
                    'data' => implode(', ', $chartData),
                    'color' => $color,
                ];
            }
        }
        
        return view('terminal.checked', [
            'rows' => $rows,
            'charts' => $charts,
            'varSteps' => implode(', ', $varSteps),
        ]);
    }
    
    /**
     * 
     * @param type $selKey
     * @return type
     */
    public function editAdd($selKey = 0) {        
        $selKey = (int)$selKey;
        
        $app_controls = [];
        $app_controls_ids = [];
        foreach(Lang::get('terminal.app_control_labels') as $key => $val) {
            if ($val) {
                $app_controls[] = (object)[
                    'key' => $key,
                    'value' => $val,
                ];
                $app_controls_ids[] = $key;
            }
        }
        
        $app_controls_ids = implode(',', $app_controls_ids);
        
        $checks = explode(',',  PropertysModel::getWebChecks());
        
        $where = '';
        if ($selKey > 0) {
            $where = " and v.APP_CONTROL = $selKey ";
        }

        $sql = "select v.* ".
               "  from core_variables v ".
               " where v.APP_CONTROL in ($app_controls_ids) ".
               $where.
               " order by v.COMM";
        
        $data = [];
        
        foreach(DB::select($sql) as $row) {
            $c = \App\Http\Models\VariablesModel::decodeAppControl($row->APP_CONTROL);
            $data[] = (object)[
                'ID' => $row->ID,
                'COMM' => $row->COMM,
                'typLabel' => $c->label,
            ];
        }
        
        return view('terminal.checked-edit-add', [
            'page' => 'add',
            'selKey' => $selKey,
            'appControls' => $app_controls,
            'checks' => $checks,
            'data' => $data,
        ]);
    }
    
    /**
     * 
     * @param type $id
     * @return string
     */
    public function editAdd_ADD($id) {
        $id = (int)$id;
        $p = PropertysModel::getWebChecks();
        if ($p) {
            $a = explode(',', $p);
        } else {
            $a = [];
        }
        
        if (!in_array($id, $a)) {
            $a[] = $id;
            $s = implode(',', $a);
            DB::update("update core_propertys set VALUE = '$s' where NAME = 'WEB_CHECKED'");
            return 'OK';
        }
        
        return 'ERROR';
    }
    
    /**
     * 
     * @param type $id
     * @return string
     */
    public function editAdd_DEL($id) {
        $id = (int)$id;
        $p = PropertysModel::getWebChecks();
        if ($p) {
            $a = explode(',', $p);
        } else {
            $a = [];
        }
        
        $i = array_search($id, $a);
        if ($i > -1) {
            array_splice($a, $i, 1);
            $s = implode(',', $a);
            DB::update("update core_propertys set VALUE = '$s' where NAME = 'WEB_CHECKED'");
            return 'OK';
        }
        
        return 'ERROR';
    }
    
    /**
     * 
     * @return type
     */
    public function editOrder() {
        $checks = PropertysModel::getWebChecks();
        
        if ($checks) {
            $sql = "select v.* ".
                   "  from core_variables v ".
                   " where v.ID in ($checks) ";
        } else {
            $sql = "select v.* ".
                   "  from core_variables v ".
                   " where v.ID = 0 ";
        }
        
        $q = DB::select($sql);

        $data = [];

        foreach (explode(',', $checks) as $key) {
            for ($i = 0; $i < count($q); $i++) {
                $row = $q[$i];
                if ($row->ID == $key) {
                    $c = \App\Http\Models\VariablesModel::decodeAppControl($row->APP_CONTROL);
                    $itemLabel = mb_strtoupper($row->COMM);
                    $c->title = $itemLabel;
                    $data[] = (object)[
                        'data' => $row, 
                        'control' => $c
                    ];
                    break;
                }
            }
        }
        
        
        return view('terminal.checked-edit-order', [
            'page' => 'order',
            'data' => $data,
        ]);
    }
    
    /**
     * 
     * @param type $id
     * @return string
     */
    public function editOrder_UP($id) {
        $id = (int)$id;
        $p = PropertysModel::getWebChecks();
        if ($p) {
            $a = explode(',', $p);
        } else {
            $a = [];
        }
        
        for ($i = 1; $i < count($a); $i++) {
            if ($a[$i] == $id) {
                $t = $a[$i - 1];
                $a[$i - 1] = $a[$i];
                $a[$i] = $t;
                $s = implode(',', $a);
                DB::update("update core_propertys set VALUE = '$s' where NAME = 'WEB_CHECKED'");
                return 'OK';
            }
        }
        
        return 'ERROR';
    }
    
    /**
     * 
     * @param type $id
     * @return string
     */
    public function editOrder_DOWN($id) {
        $id = (int)$id;
        $p = PropertysModel::getWebChecks();
        if ($p) {
            $a = explode(',', $p);
        } else {
            $a = [];
        }
        
        for ($i = 0; $i < count($a) - 1; $i++) {
            if ($a[$i] == $id) {
                $t = $a[$i + 1];
                $a[$i + 1] = $a[$i];
                $a[$i] = $t;
                $s = implode(',', $a);
                DB::update("update core_propertys set VALUE = '$s' where NAME = 'WEB_CHECKED'");
                return 'OK';
            }
        }
        
        return 'ERROR';
    }

    /**
     * 
     * @return type
     */
    public function editColor() {
        $data = PropertysModel::getWebColors();
        return view('terminal.checked-edit-color', [
            'page' => 'color',
            'data' => $data,
        ]);
    }
    
    /**
     * 
     * @param Request $request
     * @param type $action
     * @return string
     */
    public function editColor_ACTION(Request $request, $action) {
        $keyword = $request->post('keyword');
        $color = $request->post('color');
        
        $colors = PropertysModel::getWebColors();
            
        switch ($action) {
            case 'add':
                $colors[] = [
                    'keyword' => $keyword,
                    'color' => $color
                ];
                break;
            case 'set':
                $finded = false;
                for ($i = 0; $i < count($colors); $i++) {
                    if (mb_strtoupper($colors[$i]['keyword']) == mb_strtoupper($keyword)) {
                        $finded = true;
                        $colors[$i]['color'] = $color;
                        break;
                    }
                }
                if (!$finded) {
                    $colors[] = [
                        'keyword' => $keyword,
                        'color' => $color
                    ];
                }
                break;
            case 'del':
                for ($i = 0; $i < count($colors); $i++) {
                    if (mb_strtoupper($colors[$i]['keyword']) == mb_strtoupper($keyword)) {
                        array_splice($colors, $i, 1);
                        break;
                    }
                }
                break;
        }
        
        DB::update("update core_propertys set VALUE = ? where NAME = 'WEB_COLOR'", [json_encode($colors)]);
        return 'OK';
    }
}
