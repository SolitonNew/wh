<?php

namespace App\Services\Api;

use Illuminate\Support\Facades\DB;

class EventsService 
{
    /**
     * 
     * @return type
     */
    public function getLastID()
    {
        $res = DB::select("select max(id) m_id from core_events_mem");
        
        return $res[0]->m_id ?? 0;
    }
    
    /**
     * 
     * @param int $lastID
     * @return type
     */
    public function getEvents(int $lastID)
    {
        $res = DB::select("select c.id, c.device_id, c.value, UNIX_TIMESTAMP(c.created_at) created_at ".
                          "  from core_events_mem c ".
                          " where c.id > $lastID ".
                          " order by c.id");
        
        return $res;
    }
}
