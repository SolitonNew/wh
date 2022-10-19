<?php

namespace App\Listeners;

use App\Events\AddedEventMem;
use App\Models\EventMem;
use App\Models\Property;

class AddedEventMemListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param AddedEventMem $event
     * @return void
     */
    public function handle(AddedEventMem $event): void
    {
        $eventMem = $event->eventMem;

        switch ($eventMem->typ) {
            case EventMem::PLAN_LIST_CHANGE:
                break;
            case EventMem::HUB_LIST_CHANGE:
                $this->checkDaemonState($eventMem->getData()->typ);
                break;
            case EventMem::HOST_LIST_CHANGE:
                break;
            case EventMem::DEVICE_LIST_CHANGE:
                break;
            case EventMem::SCRIPT_LIST_CHANGE:
                break;
        }
    }

    /**
     * @param string $typ
     * @return void
     * @throws \Exception
     */
    private function checkDaemonState(string $typ): void
    {
        $cross = [
            'extapi' => 'extapi-daemon',
            'orangepi' => 'orangepi-daemon',
            'din' => 'din-daemon',
            'pyhome' => 'pyhome-daemon',
            'camcorder' => 'camcorder-daemon',
            'zigbeeone' => 'zigbeeone-daemon',
        ];

        if (isset($cross[$typ])) {
            $manager = new \App\Library\DaemonManager();
            if (!$manager->isStarted($cross[$typ])) {
                $daemonClass = $manager->getDaemonClass($cross[$typ]);
                $daemonClass::setWorkingState(true);
                $manager->start($cross[$typ]);
            }
        }
    }
}
