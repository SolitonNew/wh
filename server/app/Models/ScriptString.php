<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScriptString extends Model
{
    protected $table = 'core_script_strings';
    public $timestamps = false;
    
    /**
     * 
     * @param type $data
     * @return type
     */
    static public function setData($data)
    {
        //$data = trim($data);
        
        $item = self::whereData($data)->first();
        
        if (!$item) {
            $item = new ScriptString();
            $item->data = $data;
            $item->save();
        }
        
        return $item->id;
    }
}
