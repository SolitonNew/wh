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
     * Route for favorites index page.
     * 
     * @return type
     */
    public function index() 
    {
        $web_color = PropertysModel::getWebColors();
        $web_checks = PropertysModel::getWebChecks();

        if ($web_checks) {
            $sql = "select v.*,
                           (select p.name from plan_parts p where p.id = v.group_id) group_name
                      from core_variables v 
                     where v.ID in ($web_checks) ";
        } else {
            $sql = "select v.*,
                           (select p.name from plan_parts p where p.id = v.group_id) group_name
                      from core_variables v
                     where v.ID in (0) ";
        }
        
        $rows = [];
        $vars = DB::select($sql);
        foreach (explode(',', $web_checks) as $key) {
            for ($i = 0; $i < count($vars); $i++) {
                $row = $vars[$i];
                if ($row->id == $key) {
                    $c = \App\Http\Models\VariablesModel::decodeAppControl($row->app_control);
                    if (!$row->comm) {
                        $row->comm = $row->group_name;
                    }
                    $itemLabel = mb_strtoupper($row->comm);
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

            $varSteps[] = "{id: ".$row->data->id.", step: ".$row->control->varStep."}";
            
            if ($row->control->typ == 1) {
                $sql = "select UNIX_TIMESTAMP(v.change_date) * 1000 v_date, v.value ".
                       "  from core_variable_changes_mem v ".
                       " where v.variable_id = ".$row->data->id.
                       "   and v.change_date > (select max(zz.change_date) ".
                       "                          from core_variable_changes_mem zz ".
                       "                         where zz.variable_id = ".$row->data->id.") - interval 3 hour ".
                       " order by v.ID ";
                
                $chartData = [];
                foreach(DB::select($sql) as $v_row) {
                    $x = $v_row->v_date;
                    $y = $v_row->value;
                    $chartData[] = "{x: $x, y: $y}";
                }
                
                $charts[] = (object)[
                    'id' => $row->data->id,
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
     * Route to manage favorites page entries.
     * 
     * @param type $selKey
     * @return type
     */
    public function editAdd($selKey = 0) 
    {        
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
            $where = " and v.app_control = $selKey ";
        }

        $sql = "select v.*,
                       (select p.name from plan_parts p where id = v.group_id) group_name
                 from core_variables v
                where v.app_control in ($app_controls_ids) ".
               $where.
               " order by v.comm";
        
        $data = [];
        
        foreach(DB::select($sql) as $row) {
            $c = \App\Http\Models\VariablesModel::decodeAppControl($row->app_control);
            $data[] = (object)[
                'id' => $row->id,
                'comm' => $row->comm ?? $row->group_name,
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
     * Route to add devices to favorites page.
     * 
     * @param int $id
     * @return string
     */
    public function editAdd_ADD(int $id) 
    {
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
            DB::update("update core_propertys set value = '$s' where name = 'WEB_CHECKED'");
            return 'OK';
        }
        
        return 'ERROR';
    }
    
    /**
     * Route to remove devices from favorites page.
     * 
     * @param int $id
     * @return string
     */
    public function editAdd_DEL(int $id) 
    {
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
            DB::update("update core_propertys set value = '$s' where name = 'WEB_CHECKED'");
            return 'OK';
        }
        
        return 'ERROR';
    }
    
    /**
     * Route for ordering favorites page entries.
     * 
     * @return type
     */
    public function editOrder() 
    {
        $checks = PropertysModel::getWebChecks();
        
        if ($checks) {
            $sql = "select v.*,
                           (select p.name from plan_parts p where id = v.group_id) group_name
                      from core_variables v
                     where v.ID in ($checks) ";
        } else {
            $sql = "select v.*,
                           (select p.name from plan_parts p where id = v.group_id) group_name
                      from core_variables v
                     where v.ID = 0 ";
        }
        
        $q = DB::select($sql);
        
        foreach($q as $row) {
            $row->comm = $row->comm ?? $row->group_name;
        }

        $data = [];

        foreach (explode(',', $checks) as $key) {
            for ($i = 0; $i < count($q); $i++) {
                $row = $q[$i];
                if ($row->id == $key) {
                    $c = \App\Http\Models\VariablesModel::decodeAppControl($row->app_control);
                    $itemLabel = mb_strtoupper($row->comm);
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
     * Route to move up entries of favorites page.
     * 
     * @param int $id
     * @return string
     */
    public function editOrder_UP(int $id) 
    {
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
                DB::update("update core_propertys set value = '$s' where name = 'WEB_CHECKED'");
                return 'OK';
            }
        }
        
        return 'ERROR';
    }
    
    /**
     * Route to move down entries of favorites page
     * 
     * @param type $id
     * @return string
     */
    public function editOrder_DOWN($id) 
    {
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
                DB::update("update core_propertys set value = '$s' where name = 'WEB_CHECKED'");
                return 'OK';
            }
        }
        
        return 'ERROR';
    }

    /**
     * Route to change the displayed color of the device.
     * 
     * @return type
     */
    public function editColor() 
    {
        $data = PropertysModel::getWebColors();
        return view('terminal.checked-edit-color', [
            'page' => 'color',
            'data' => $data,
        ]);
    }
    
    /**
     * Route to action to change the displayed color of the device.
     * 
     * @param Request $request
     * @param type $action
     * @return string
     */
    public function editColor_ACTION(Request $request, $action) 
    {
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
        
        DB::update("update core_propertys set value = ? where name = 'WEB_COLOR'", [json_encode($colors)]);
        return 'OK';
    }
}
