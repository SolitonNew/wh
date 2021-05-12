<?php

namespace App\Models;

use \App\Library\AffectsFirmwareModel;

class OwDev extends AffectsFirmwareModel
{    
    protected $table = 'core_ow_devs';
    public $timestamps = false;
    
    protected $_affectFirmwareFields = [
        'id',
    ];
    
    /**
     * 
     * @return type
     */
    public function hub()
    {
        return $this->belongsTo(Hub::class, 'hub_id');
    }
    
    /**
     * 
     * @return type
     */
    public function devices()
    {
        return $this->hasMany(Device::class, 'ow_id')
                    ->whereTyp('ow')
                    ->orderBy('name', 'asc');
    }
    
    /**
     *
     * @var type 
     */
    public $type = null;
    
    /**
     * 
     * @return type
     */
    public function type()
    {
        if ($this->type === null) {
            $types = config('onewire.types');
            $type = isset($types[$this->rom_1]) ? $types[$this->rom_1] : [];

            if (!isset($type['description'])) {
                $type['description'] = '';
            }

            if (!isset($type['channels'])) {
                $type['channels'] = [];
            }

            if (!isset($type['consuming'])) {
                $type['consuming'] = 0;
            }

            $this->type = (object)$type;
        }
        
        return $this->type;
    }
    
    /**
     * 
     * @return type
     */
    public function channelsOfType()
    {
        if ($this->type()) {
            return $this->type()->channels;
        }
        
        return [];
    }
    
    /**
     * 
     * @return type
     */
    public function romAsString()
    {
        return sprintf("x%'02X x%'02X x%'02X x%'02X x%'02X x%'02X x%'02X", 
            $this->rom_1, 
            $this->rom_2, 
            $this->rom_3, 
            $this->rom_4, 
            $this->rom_5, 
            $this->rom_6, 
            $this->rom_7
        );
    }
}
