<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebQueueMem extends Model
{
    protected $table = 'web_queue_mem';
    public $timestamps = false;
    
    /**
     * 
     * @param type $action
     * @param type $data
     */
    static public function appendRecord($action, $data) 
    {
        $item = new WebQueueMem();
        $item->action = $action;
        $item->data = $data;
        $item->save();
    }
    
    static private $_lastQueueID = -1;
    
    static public function lastQueueID() 
    {
        if (self::$_lastQueueID == -1) {
            $maxID = WebQueueMem::max('id');
            if ($maxID > 0) {
                self::$_lastQueueID = $maxID;
            }
        }
        return self::$_lastQueueID;
    }
    
    static public function setLastQueueID($id) 
    {
        self::$_lastQueueID = $id;
    }
    
    static public function getLastQueueList(int $lastID)
    {
        return WebQueueMem::where('id', '>', $lastID)
                    ->orderBy('id', 'asc')
                    ->get();
    }
}
