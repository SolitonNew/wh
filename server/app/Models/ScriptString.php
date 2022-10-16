<?php

namespace App\Models;

use App\Library\Script\IScriptStringStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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

    /**
     * @param string $string
     * @return int
     */
    public static function getIdByString(string $string): int
    {
        $item = self::whereData($string)->first();
        if (!$item) {
            $item = new ScriptString();
            $item->data = $string;
            $item->save();
        }
        return $item->id;
    }

    /**
     * @param int $id
     * @return string
     */
    public static function getStringById(int $id): string
    {
        $item = self::find($id);
        return $item ? $item->data : '';
    }
}
