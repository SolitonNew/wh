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
        $item = self::whereName('WEB_COLOR')->first();
        if ($item && $item->value) {
            return json_decode($item->value, true);
        } else {
            return [];
        }
    }
    
    /**
     * 
     * @return type
     */
    static public function getWebChecks() {
        $item = self::whereName('WEB_CHECKED')->first();
        if ($item && $item->value) {
            return $item->value;
        } else {
            return '';
        }
    }
    
    /**
     * 
     * @return type
     */
    static public function runningDemons() {
        $item = self::whereName('RUNNING_DEMONS')->first();
        if ($item && $item->value) {
            return explode(';', $item->value);
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
                $item->name = 'RUNNING_DEMONS';
                $item->comm = '';
            }
            $item->value = implode(';', $a);
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
                $item->name = 'RUNNING_DEMONS';
                $item->comm = '';
            }
            $item->value = implode(';', $a);
            $item->save();
        }
    }
}
