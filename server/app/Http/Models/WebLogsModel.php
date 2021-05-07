<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class WebLogsModel extends Model
{
    protected $table = 'web_logs';
    public $timestamps = false;
    
    /**
     * 
     * @param string $demonID
     * @param int $lastID
     * @return type
     */
    static public function getDemonDataFromID(string $demonID, int $lastID)
    {
        $data = \App\Http\Models\WebLogsModel::whereDemon($demonID)
                    ->where('id', '>', $lastID)
                    ->orderby('id', 'desc')
                    ->limit(config("app.admin_demons_log_lines_count"))
                    ->get();

        foreach($data as &$row) {
            $str = $row->data;            
            $str = str_replace('[', '<span class="datetime">[', $str);
            $str = str_replace(']', ']</span>', $str);
            
            $row->data = $str;
        }
        
        return $data;
    }
}
