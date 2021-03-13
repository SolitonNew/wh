<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class ExecuteModel extends Model
{
    protected $table = 'core_execute';
    public $timestamps = false;
    
    /**
     * 
     * @param string $command
     */
    static public function command(string $command) {
        $item = new ExecuteModel();
        $item->command = $command;
        $item->save();
    }
}
