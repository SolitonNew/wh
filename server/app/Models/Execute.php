<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Execute extends Model
{
    protected $table = 'core_execute';
    public $timestamps = false;

    /**
     * @param string $command
     * @return void
     */
    public static function command(string $command): void
    {
        $item = new Execute();
        $item->command = $command;
        $item->save();
    }
}
