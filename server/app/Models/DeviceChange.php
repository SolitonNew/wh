<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceChange extends Model
{
    protected $table = 'core_device_changes';
    public $timestamps = false;

    /**
     * 
     * @param int $id
     */
    static public function deleteById(int $id)
    {
        try {
            $item = DeviceChange::find($id);
            if (!$item) abort(404);
            
            $item->delete();
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
    
    /**
     * 
     * @return type
     */
    static public function getCount()
    {
        $count = self::count();
        
        $sizes = ['', 'k', 'M', 'G'];
        
        $s = 0;
        for ($i = 0; $i < 4; $i++) {
            if ($count > 1000) {
                $count /= 1000;
                $s++;
            } else {
                break;
            }
        }
        
        return $count.$sizes[$s];
    }
}
