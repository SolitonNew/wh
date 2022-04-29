<?php

namespace App\Services\Terminal;

use App\Models\Room;
use App\Models\Device;
use App\Models\Property;
use Lang;

class RoomsService 
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
    public function roomsData()
    {
        $this->_groups = Room::orderBy('order_num', 'asc')
                            ->orderBy('name', 'asc')
                            ->get();
        
        $this->_variables = Device::get();
        
        // Проставим кархдому устройству comm в виде названия комнаты, 
        // если небыло указано ранее
        foreach($this->_variables as $var) {
            if (!$var->comm) {
                foreach($this->_groups as $g) {
                    if ($g->id == $var->room_id) {
                        $var->comm = $g->name;
                        break;
                    }
                }
            }
        }
        
        
        $data = [];
        
        switch (Property::getPlanMaxLevel()) {
            case 1:
                $data[] = (object)[
                    'title' => '',
                ];
                $this->_makeItems(null, 2, $data);
                break;
            case 2:
                $this->_makeItems(null, 1, $data);
                break;
            default:
                $this->_makeItems(null, 0, $data);
        }
        
        return $data;
    }
    
    /**
     * This method to creates data to display a list of rooms.
     * 
     * @param type $parentID
     * @param type $level
     */
    private function _makeItems($parentID, $level, &$data) 
    {
        $switches_2 = [];
        foreach (Lang::get('terminal.switches_second') as $s) {
            $switches_2[] = ' '.$s;
        }
        
        for ($i = 0; $i < count($this->_groups); $i++) {
            $row = $this->_groups[$i];
            if ($row->parent_id == $parentID) {
                switch ($level) {
                    case 0:
                        break;
                    case 1:
                        $parentTitle = '';
                        foreach($this->_groups as $p_g) {
                            if ($p_g->id == $row->parent_id) {
                                $parentTitle = mb_strtoupper($p_g->name);
                                break;
                            }
                        }
                        
                        $itemTitle = mb_strtoupper($row->name);
                        if ($parentTitle != '' && $parentTitle != $itemTitle) {
                            $itemTitle = $parentTitle.' '.$itemTitle;
                        }
                        
                        $data[] = (object)[
                            'title' => $itemTitle,  
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
     * This method of finding a device in the device storage by id and room name.
     * 
     * @param type $roomID
     * @param type $roomNameUpper
     * @return type
     */
    private function _findVariable($roomID, $roomNameUpper) 
    {
        $res = [];
        for ($i = 0; $i < count($this->_variables); $i++) {
            $var = $this->_variables[$i];
            if ($var->room_id == $roomID) {
                if (mb_strtoupper(mb_substr($var->comm, 0, mb_strlen($roomNameUpper))) == $roomNameUpper) {
                    $res[] = $var;
                }
            }
        }
        return $res;
    }
    
    public function roomsColumnCount(&$data)
    {
        $columnCount = count($data);
        if ($columnCount > 5) {
            $columnCount = 3;
        }
        
        return $columnCount;
    }
}
