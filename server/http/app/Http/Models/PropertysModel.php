<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class PropertysModel extends Model
{
    protected $table = 'core_propertys';
    public $timestamps = false;
    
    /**
     * 
     * @return type
     */
    static public function getWebColors() {
        $row = self::whereName('WEB_COLOR')->first();
        if ($row) {
            return json_decode($row->VALUE, true);
        } else {
            return [];
        }
    }
    
    /**
     * 
     * @return type
     */
    static public function getWebChecks() {
        $row = self::whereName('WEB_CHECKED')->first();
        if ($row) {
            return $row->VALUE;
        } else {
            return [];
        }
    }
}
