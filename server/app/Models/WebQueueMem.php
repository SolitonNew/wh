<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

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
            $res = DB::select('select max(id) max_id from web_queue_mem');
            if (count($res) && ($res[0]->max_id > 0)) {
                self::$_lastQueueID = $res[0]->max_id;
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
