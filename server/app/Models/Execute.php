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

    /**
     * @param string $command
     * @param array $data
     * @return string
     */
    public static function executeRawCommand(string $command, array $data): string
    {
        $pack = (object)[
            'command' => $command,
            'data' => $data,
        ];
        self::command(json_encode($pack));
        return $command.'('.implode(', ', $data).')';
    }
}
