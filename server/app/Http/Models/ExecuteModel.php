<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class ExecuteModel extends Model
{
    protected $table = 'core_execute';
    public $timestamps = false;
    protected $primaryKey = 'ID';
    
    /**
     * 
     * @param string $command
     */
    static public function command(string $command) {
        $item = new ExecuteModel();
        $item->COMMAND = $command;
        $item->save();
    }
}
