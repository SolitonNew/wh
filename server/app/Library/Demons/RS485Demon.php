<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Demons;

use DB;
use Lang;
use Log;

/**
 * Description of CommandDemon
 *
 * @author soliton
 */
class RS485Demon extends BaseDemon {
    /**
     *
     * @var type 
     */
    private $_port;
    
    /**
     * 
     */
    public function execute() {
        DB::select('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
        
        $lastProcessedID = -1;

        $this->printLine('');
        $this->printLine('');
        $this->printLine(str_repeat('-', 100));
        $this->printLine(Lang::get('admin/demons.rs485-demon-title'));
        $this->printLine('--    PORT: '.config('firmware.rs485_port')); 
        $this->printLine('--    BAUD: '.config('firmware.rs485_baud')); 
        $this->printLine(str_repeat('-', 100));
        $this->printLine('');
        
        $controllers = \App\Http\Models\ControllersModel::where('id', '<', 100)
                            ->orderBy('name', 'asc')
                            ->get();
        
        if (count($controllers) == 0) return;
        
        try {            
            exec('stty -F '.config('firmware.rs485_port').' '.config('firmware.rs485_baud').' cs8 cstopb');
            $this->_port = fopen(config('firmware.rs485_port'), 'r+b');
            
            while (1) {
                foreach($controllers as $controller) {
                    if ($controller->is_server) continue;
                    $contr = $controller->name;
                    
                    $this->_transmitCMD($controller->id, 2, 100);
                    
                    $this->_transmitCMD($controller->id, 3, 100);
                    
                    $vars_out_str = [];
                    $vars_in_str = [];
                    
                    try {
                        //$this->_transmitCMD(2, 100);
                        
                        $stat = 'OK';
                        $s = "[".now()."] SYNC. '$contr': $stat\n";
                        $s .= "   >>   [".implode(', ', $vars_out_str)."]\n";
                        $s .= "   <<   [".implode(', ', $vars_in_str)."]\n";
                    } catch (\Exception $ex) {
                        $s = "[".now()."] SYNC. '$contr': ERROR\n";
                        $s .= $ex->getMessage();
                    }
                                    
                    $this->printLine($s); 
                    
                    usleep(100000);
                }
            }
            fclose($this->_port);
        } catch (\Exception $ex) {
            $s = "[".now()."] ERROR\n";
            $s .= $ex->getMessage();
            $this->printLine($s); 
        }
    }
    
    /**
     * 
     * @param int $data
     * @return type
     */
    private function _crc_table($data) {
	$crc = 0x0;
	$fb_bit = 0;
	for ($b = 0; $b < 8; $b++) { 
            $fb_bit = ($crc ^ $data) & 0x01;
            if ($fb_bit == 0x01) {
                $crc = $crc ^ 0x18;
            }
            $crc = ($crc >> 1) & 0x7F;
            if ($fb_bit == 0x01) {
                $crc = $crc | 0x80;
            }
            $data >>= 1;
	}
	return $crc;
    }
    
    /**
     * 
     * @param type $controllerId
     * @param type $cmd
     * @param type $tag
     */
    private function _transmitCMD($controllerId, $cmd, $tag) {
        $pack = pack('a*', 'CMD');
        $pack .= pack('C', $controllerId);
        $pack .= pack('C', $cmd);
        $pack .= pack('s', $tag);        
	$crc = 0x0;
	for ($i = 0; $i < strlen($pack); $i++) {
            $crc = $this->_crc_table($crc ^ ord($pack[$i]));
	}   
        $pack .= pack('C', $crc);        
        fwrite($this->_port, $pack);
    }
    
    /**
     * 
     * @param type $id
     * @param type $value
     */
    private function _transmitVAR($controllerId, $id, $value) {
        $pack = pack('a*', 'VAR');
        $pack .= pack('C', $controllerId);
        $pack .= pack('s', $id);
        $pack .= pack('f', $value);
	$crc = 0x0;
	for ($i = 0; $i < strlen($pack); $i++) {
            $crc = $this->_crc_table($crc ^ ord($pack[$i]));
	}   
        $pack .= pack('C', $crc);
        fwrite($this->_port, $pack);
    }
}