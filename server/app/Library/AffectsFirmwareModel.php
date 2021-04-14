<?php

namespace App\Library;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Illuminate\Database\Eloquent\Model;

class AffectsFirmwareModel extends Model
{
    /**
     *
     * @var type 
     */
    protected $_affectFirmwareFields = [];
    
    /**
     * 
     * @param array $options
     */
    public function finishSave(array $options = []) 
    {
        $firmwareChanged = count($this->_affectFirmwareFields) ? $this->isDirty($this->_affectFirmwareFields) : true;
        parent::finishSave($options);
        if ($firmwareChanged && !in_array('withoutevents', $options)) {
            event(new \App\Http\Events\FirmwareChangedEvent());
        }
    }
        
    /**
     * 
     */
    public function delete() 
    {
        $return = parent::delete();
        event(new \App\Http\Events\FirmwareChangedEvent());
        return $return;
    }
}