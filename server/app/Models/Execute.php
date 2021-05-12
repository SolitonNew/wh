<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Execute extends Model
{
    protected $table = 'core_execute';
    public $timestamps = false;
    
    /**
     * 
     * @param string $command
     */
    static public function command(string $command) 
    {
        $item = new Execute();
        $item->command = $command;
        $item->save();
    }
}
