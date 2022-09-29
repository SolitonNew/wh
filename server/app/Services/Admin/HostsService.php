<?php

namespace App\Services\Admin;

use App\Models\Hub;

class HostsService
{
    /**
     * @param int $hubID
     * @return string
     */
    public function getHostType(int $hubID): string
    {
        return Hub::findOrCreate($hubID)->typ;
    }
}
