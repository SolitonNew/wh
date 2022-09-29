<?php

namespace App\Services\Api;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EventsService
{
    /**
     * @return int
     */
    public function getLastID(): int
    {
        $res = DB::select("select max(id) m_id from core_events_mem");

        return $res[0]->m_id ?? 0;
    }

    /**
     * @param int $lastID
     * @return array
     */
    public function getEvents(int $lastID): array
    {
        $res = DB::select("select c.id, c.device_id, c.value, UNIX_TIMESTAMP(c.created_at) created_at ".
                          "  from core_events_mem c ".
                          " where c.id > $lastID ".
                          " order by c.id");

        return $res;
    }
}
