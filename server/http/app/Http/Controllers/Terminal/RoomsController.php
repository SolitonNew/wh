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
        $this->_groups = \App\Http\Models\PlanPartsModel::orderBy('ORDER_NUM', 'asc')
                            ->orderBy('NAME', 'asc')
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
            if ($row->PARENT_ID == $parentID) {
                switch ($level) {
                    case 0:
                        break;
                    case 1:
                        $data[] = (object)[
                            'title' => mb_strtoupper($row->NAME),  
                            'rooms' => [],
                        ];
                        break;
                    case 2:
                        $room = $data[count($data) - 1];
                        $titleUpper = mb_strtoupper($row->NAME);
                        $vars = $this->_findVariable($row->ID, $titleUpper);
                        
                        $temperature_id = -1;
                        $temperature_val = 0;
                        
                        $switch_1_id = -1;
                        $switch_1_val = 0;
                        
                        $switch_2_id = -1;
                        $switch_2_val = 0;
                        
                        foreach ($vars as $v) {
                            switch ($v['APP_CONTROL']) {
                                case 4:                       
                                    if (mb_strtoupper($v['COMM']) == $titleUpper) {
                                        $temperature_id = $v['ID'];
                                        $temperature_val = $v['VALUE'];
                                    }
                                    break;
                                case 1:
                                    if (mb_strtoupper($v['COMM']) == $titleUpper) {
                                        $switch_1_id = $v['ID'];
                                        $switch_1_val = $v['VALUE'];
                                    } else {
                                        for ($n = 0; $n < count($switches_2); $n++) {
                                            if (mb_strtoupper($v['COMM']) == $titleUpper.$switches_2[$n]) {
                                                $switch_2_id = $v['ID'];
                                                $switch_2_val = $v['VALUE'];
                                                break;
                                            }
                                        }
                                    }
                                    break;
                            }
                        }
                        
                        $room->rooms[] = (object)[
                            'id' => $row->ID,
                            'title' => mb_strtoupper($row->NAME),
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
                $this->_makeItems($row->ID, $level + 1, $data);
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
            if ($var->GROUP_ID == $roomID) {
                if (mb_strtoupper(mb_substr($var->COMM, 0, mb_strlen($roomNameUpper))) == $roomNameUpper) {
                    $res[] = $var;
                }
            }
        }
        return $res;
    }
}
