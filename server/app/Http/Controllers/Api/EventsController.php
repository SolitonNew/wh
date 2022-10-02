<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\EventsService;

class EventsController extends Controller
{
    /**
     * @var EventsService
     */
    private EventsService $service;

    public function __construct(EventsService $service)
    {
        $this->service = $service;
    }

    /**
     *
     * @param int $lastID
     * @return type
     */
    public function getData(int $lastID)
    {
        if ($lastID > -1) {
            $data = $this->service->getEvents($lastID);
            return response()->json($data);
        } else {
            return response()->json([
                'lastID' => $this->service->getLastID(),
            ]);
        }
    }
}
