<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class PropertysModel extends Model
{
    protected $table = 'core_propertys';
    public $timestamps = false;
    protected $primaryKey = 'ID';
    
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
    
    /**
     * 
     * @return type
     */
    static public function runningDemons() {
        $item = self::whereName('RUNNING_DEMONS')->first();
        if ($item) {
            return explode(';', $item->VALUE);
        } else {
            return [];
        }
    }
    
    /**
     * 
     * @param type $demon
     */
    static public function setAsRunningDemon($demon) {
        $a = self::runningDemons();
        if (!in_array($demon, $a)) {
            $a[] = $demon;
            $item = self::whereName('RUNNING_DEMONS')->first();
            if (!$item) {
                $item = new PropertysModel();
                $item->NAME = 'RUNNING_DEMONS';
                $item->COMM = '';
            }
            $item->VALUE = implode(';', $a);
            $item->save();
        }
    }
    
    /**
     * 
     * @param type $demon
     */
    static public function setAsStoppedDemon($demon) {
        $a = self::runningDemons();
        if (in_array($demon, $a)) {
            array_splice($a, array_search($demon, $a));
            $item = self::whereName('RUNNING_DEMONS')->first();
            if (!$item) {
                $item = new PropertysModel();
                $item->NAME = 'RUNNING_DEMONS';
                $item->COMM = '';
            }
            $item->VALUE = implode(';', $a);
            $item->save();
        }
    }
}
