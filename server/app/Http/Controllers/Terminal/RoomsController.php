<?php

namespace App\Http\Controllers\Terminal;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;

class RoomsController extends Controller
{   
    /**
     *
     * @var type 
     */
    private $_groups = [];
    
    /**
     *
     * @var type 
     */
    private $_variables = [];
    
    /**
     * 
     * @return type
     */
    public function index() {       
        $this->_groups = \App\Http\Models\PlanPartsModel::orderBy('order_num', 'asc')
                            ->orderBy('name', 'asc')
                            ->get();
        
        $this->_variables = \App\Http\Models\VariablesModel::get();
        $data = [];
        $this->_makeItems(null, 0, $data);
        
        $columnCount = count($data);
        if ($columnCount > 5) {
            $columnCount = 3;
        }
        
        return view('terminal.rooms', [
            'data' => $data,
            'columnCount' => $columnCount,
        ]);
    }
    
    /**
     * 
     * @param type $parentID
     * @param type $level
     */
    private function _makeItems($parentID, $level, &$data) {
        $switches_2 = [
            ' НОЧНИК', 
            ' СТОЛОВАЯ'
        ];
        
        for ($i = 0; $i < count($this->_groups); $i++) {
            $row = $this->_groups[$i];
            if ($row->parent_id == $parentID) {
                switch ($level) {
                    case 0:
                        break;
                    case 1:
                        $data[] = (object)[
                            'title' => mb_strtoupper($row->name),  
                            'rooms' => [],
                        ];
                        break;
                    case 2:
                        $room = $data[count($data) - 1];
                        $titleUpper = mb_strtoupper($row->name);
                        $vars = $this->_findVariable($row->id, $titleUpper);
                        
                        $temperature_id = -1;
                        $temperature_val = 0;
                        
                        $switch_1_id = -1;
                        $switch_1_val = 0;
                        
                        $switch_2_id = -1;
                        $switch_2_val = 0;
                        
                        foreach ($vars as $v) {
                            switch ($v['app_control']) {
                                case 4:                       
                                    if (mb_strtoupper($v['comm']) == $titleUpper) {
                                        $temperature_id = $v['id'];
                                        $temperature_val = $v['value'];
                                    }
                                    break;
                                case 1:
                                    if (mb_strtoupper($v['comm']) == $titleUpper) {
                                        $switch_1_id = $v['id'];
                                        $switch_1_val = $v['value'];
                                    } else {
                                        for ($n = 0; $n < count($switches_2); $n++) {
                                            if (mb_strtoupper($v['comm']) == $titleUpper.$switches_2[$n]) {
                                                $switch_2_id = $v['id'];
                                                $switch_2_val = $v['value'];
                                                break;
                                            }
                                        }
                                    }
                                    break;
                            }
                        }
                        
                        $room->rooms[] = (object)[
                            'id' => $row->id,
                            'title' => mb_strtoupper($row->name),
                            'titleCrop' => str_replace($room->title, '', $titleUpper),
                            'temperature_id' => $temperature_id,
                            'temperature_val' => $temperature_val,
                            'switch_1_id' => $switch_1_id,
                            'switch_1_val' => $switch_1_val,
                            'switch_2_id' => $switch_2_id,
                            'switch_2_val' => $switch_2_val,
                        ];
                        
                        break;
                }                
                $this->_makeItems($row->id, $level + 1, $data);
            }            
        }        
    }
    
    /**
     * 
     * @param type $roomID
     * @param type $roomNameUpper
     * @return type
     */
    private function _findVariable($roomID, $roomNameUpper) {
        $res = [];
        for ($i = 0; $i < count($this->_variables); $i++) {
            $var = $this->_variables[$i];
            if ($var->group_id == $roomID) {
                if (mb_strtoupper(mb_substr($var->comm, 0, mb_strlen($roomNameUpper))) == $roomNameUpper) {
                    $res[] = $var;
                }
            }
        }
        return $res;
    }
}
