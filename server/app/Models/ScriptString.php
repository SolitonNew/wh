<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScriptString extends Model
{
    protected $table = 'core_script_strings';
    public $timestamps = false;

    /**
     * @param string $data
     * @return int
     */
    public static function setData(string $data): int
    {
        $item = self::whereData($data)->first();

        if (!$item) {
            $item = new ScriptString();
            $item->data = $data;
            $item->save();
        }

        return $item->id;
    }
}
