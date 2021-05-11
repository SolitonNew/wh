<?php

namespace App\Http\Services\Terminal;

use \Illuminate\Http\Request;
use App\Models\PropertysModel;
use App\Models\VariablesModel;
use Lang;
use DB;

class CheckedService 
{
    /**
     * 
     * @return type
     */
    public function getDevicesListInChecks()
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
        
        $rows = DB::select($sql);
        
        foreach($rows as $row) {
            $row->comm = $row->comm ?? $row->group_name;
        }
        
        return $rows;
    }
    
    /**
     * 
     * @return type
     */
    public function webChecks()
    {   
        $rows = [];
        $vars = $this->getDevicesListInChecks();
        foreach ($this->getCheckedIDs() as $key) {
            for ($i = 0; $i < count($vars); $i++) {
                $row = $vars[$i];
                if ($row->id == $key) {
                    $c = VariablesModel::decodeAppControl($row->app_control);
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
        
        return $rows;
    }
    
    /**
     * 
     * @param type $rows
     * @return type
     */
    public function getChartsFor(&$rows)
    {
        $web_color = PropertysModel::getWebColors();
        
        $charts = [];
        
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
            
            if ($row->control->typ == 1) {
                $sql = "select UNIX_TIMESTAMP(v.change_date) * 1000 v_date, v.value
                          from core_variable_changes_mem v 
                         where v.variable_id = ".$row->data->id."
                           and v.change_date > (select max(zz.change_date) 
                                                  from core_variable_changes_mem zz 
                                                 where zz.variable_id = ".$row->data->id.") - interval 3 hour
                         order by v.ID ";
                
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
        
        return $charts;
    }
    
    /**
     * 
     * @param type $rows
     * @return type
     */
    public function getVarStepsFor(&$rows)
    {
        $varSteps = [];
        
        foreach ($rows as $row) {
            $varSteps[] = "{id: ".$row->data->id.", step: ".$row->control->varStep."}";
        }
        
        return implode(',', $varSteps);
    }
    
    /**
     * 
     * @return type
     */
    public function getOrderList()
    {
        $vars = $this->getDevicesListInChecks();
        
        $checks = PropertysModel::getWebChecks();
        
        $data = [];
        foreach (explode(',', $checks) as $key) {
            for ($i = 0; $i < count($vars); $i++) {
                $row = $vars[$i];
                if ($row->id == $key) {
                    $c = VariablesModel::decodeAppControl($row->app_control);
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
        return $data;
    }
    
    /**
     * 
     * @param int $id
     * @return type
     */
    public function orderUp(int $id)
    {
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
                return ;
            }
        }
        
        abort(422);
    }
    
    /**
     * 
     * @param int $id
     * @return type
     */
    public function orderDown(int $id)
    {
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
                return ;
            }
        }
        
        abort(422);
    }
    
    /**
     * 
     * @return type
     */
    public function getWebColors()
    {
        return PropertysModel::getWebColors();
    }
    
    /**
     * 
     * @param Request $request
     * @param type $action
     */
    public function setWebColorsFromRequest(Request $request, $action)
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
    }
    
    public function getAppControls()
    {
        $app_controls = [];
        foreach(Lang::get('terminal.app_control_labels') as $key => $val) {
            if ($val) {
                $app_controls[] = (object)[
                    'key' => $key,
                    'value' => $val,
                ];
            }
        }
        
        return $app_controls;
    }
    
    public function getAppControlsIDs()
    {
        $ids = [];
        foreach($this->getAppControls() as $row) {
            $ids[] = $row->key;
        }
        
        return implode(',', $ids);
    }
    
    public function getCheckedForEdit(int $selKey = 0)
    {
        $where = '';
        if ($selKey > 0) {
            $where = " and v.app_control = $selKey ";
        }
        
        $app_controls_ids = $this->getAppControlsIDs();

        $sql = "select v.*,
                       (select p.name from plan_parts p where id = v.group_id) group_name
                 from core_variables v
                where v.app_control in ($app_controls_ids)
               $where
                order by v.comm";
        
        $data = [];
        
        foreach(DB::select($sql) as $row) {
            $c = VariablesModel::decodeAppControl($row->app_control);
            $data[] = (object)[
                'id' => $row->id,
                'comm' => $row->comm ?? $row->group_name,
                'typLabel' => $c->label,
            ];
        }
        
        return $data;
    }
    
    public function getCheckedIDs()
    {
        $p = PropertysModel::getWebChecks();
        if ($p) {
            $a = explode(',', $p);
        } else {
            $a = [];
        }
        
        return $a;
    }
    
    public function addToChecked(int $id)
    {
        $a = $this->getCheckedIDs();
        
        if (!in_array($id, $a)) {
            $a[] = $id;
            $s = implode(',', $a);
            DB::update("update core_propertys set value = '$s' where name = 'WEB_CHECKED'");
            return ;
        }
        
        abort(422);
    }
    
    public function delFromChecked(int $id)
    {
        $a = $this->getCheckedIDs();
        
        $i = array_search($id, $a);
        if ($i > -1) {
            array_splice($a, $i, 1);
            $s = implode(',', $a);
            DB::update("update core_propertys set value = '$s' where name = 'WEB_CHECKED'");
            return '';
        }
        
        abort(422);
    }
}
