<?php

namespace App\Http\Controllers\Terminal;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\EventMem;

class EventsController extends Controller
{
    /**
     * 
     * @param int $lastID
     * @return type
     */
    public function getEvents(int $lastID = -1)
    {
        if ($lastID > 0) {
            $res = DB::select("select c.id, c.typ, c.data, c.device_id, c.value, UNIX_TIMESTAMP(c.created_at) * 1000 created_at ".
                              "  from core_events_mem c ".
                              " where c.id > $lastID ".
                              " order by c.id");
            return response()->json($res);
        } else {
            return 'LAST_ID: '.(EventMem::max('id') ?? -1);
        }
    }
}
