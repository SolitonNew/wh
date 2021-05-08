<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class WebQueueMemModel extends Model
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
        $item = new WebQueueMemModel();
        $item->action = $action;
        $item->data = $data;
        $item->save();
    }
}
