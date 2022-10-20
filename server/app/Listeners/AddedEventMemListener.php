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
        foreach (config('daemons.list') as $daemonClass) {
            if ($daemonClass::SIGNATURE == $typ.'-daemon') {
                $manager = new \App\Library\DaemonManager();
                if (!$manager->isStarted($daemonClass::SIGNATURE)) {
                    $daemonClass::setWorkingState(true);
                    $manager->start($daemonClass::SIGNATURE);
                }
            }
        }
    }
}
