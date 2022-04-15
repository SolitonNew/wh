<?php

namespace App\Models;

use App\Library\AffectsFirmwareModel;
use Illuminate\Http\Request;

class OwHost extends AffectsFirmwareModel
{    
    protected $table = 'core_ow_hosts';
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
        return $this->hasMany(Device::class, 'host_id')
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
    
    /**
     * 
     * @param int $hubID
     * @return type
     */
    static public function listForIndex(int $hubID)
    {
        return OwHost::whereHubId($hubID)
            ->orderBy('rom_1', 'asc')
            ->orderBy('rom_2', 'asc')
            ->orderBy('rom_3', 'asc')
            ->orderBy('rom_4', 'asc')
            ->orderBy('rom_5', 'asc')
            ->orderBy('rom_6', 'asc')
            ->orderBy('rom_7', 'asc')
            ->get();
    }
    
    /**
     * 
     * @param int $hubID
     * @param int $id
     * @return \App\Models\OwHost
     */
    static public function findOrCreate(int $hubID, int $id)
    {
        $item = OwHost::whereHubId($hubID)->whereId($id)->first();
        if (!$item) {
            $item = new OwHost();
            $item->id = $id;
            $item->hubId = $hubID;
        }
        
        return $item;
    }
    
    /**
     * 
     * @param Request $request
     * @param int $hubID
     * @param int $id
     */
    static public function storeFromRequest(Request $request, int $hubID, int $id)
    {
        
    }
    
    /**
     * 
     * @param int $id
     */
    static public function deleteById(int $id)
    {
        try {
            Device::whereTyp('ow')
                    ->whereHostId($id)
                    ->delete();
            $item = OwHost::find($id);
            $item->delete();
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
}
