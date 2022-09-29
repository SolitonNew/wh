<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class WebLogMem extends Model
{
    protected $table = 'web_logs_mem';
    public $timestamps = false;

    /**
     * @param string $daemonID
     * @param int $lastID
     * @return Collection
     */
    public static function getDaemonDataFromID(string $daemonID, int $lastID): Collection
    {
        $data = self::whereDaemon($daemonID)
                    ->where('id', '>', $lastID)
                    ->orderby('id', 'desc')
                    ->limit(config("settings.admin_daemons_log_lines_count"))
                    ->get();

        foreach ($data as &$row) {
            $str = $row->data;
            $str = str_replace('[', '<span class="datetime">[', $str);
            $str = str_replace(']', ']</span>', $str);

            $row->data = $str;
        }

        return $data;
    }
}
