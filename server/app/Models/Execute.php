<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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

    /**
     * @param array $data
     * @return string
     */
    public static function executeRawCommand(array $data): string
    {
        $name = array_shift($data);
        $args = '';
        switch ($name) {
            case 'speech':
                $target = ScriptString::getStringById(array_shift($data));
                $phrase = ScriptString::getStringById(array_shift($data));
                $args = "'$target', '$phrase'".(count($data) ? ', ' : '').implode(', ', $data);
                break;
            case 'play':
                $target = ScriptString::getStringById(array_shift($data));
                $media = ScriptString::getStringById(array_shift($data));
                $args = "'$target', '$media'".(count($data) ? ', ' : '').implode(', ', $data);
                break;
            case 'print':
                $text = ScriptString::getStringById(array_shift($data));
                $args = "'$text'".(count($data) ? ', ' : '').implode(', ', $data);
                break;
        }
        $command = $name.'('.$args.');';
        self::command($command);
        return $command;
    }
}
