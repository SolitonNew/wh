<?php

namespace App\Services\Admin;

use App\Models\Hub;

class HostsService 
{
    /**
     * 
     * @param int $hubID
     * @return type
     */
    public function getHostType(int $hubID)
    {
        return Hub::findOrCreate($hubID)->typ;
    }
}
