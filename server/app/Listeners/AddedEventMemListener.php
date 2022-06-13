<?php

namespace App\Listeners;

use App\Events\AddedEventMem;
use App\Models\EventMem;

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
     * @param  \App\Events\ExampleEvent  $event
     * @return void
     */
    public function handle(AddedEventMem $event)
    {
        $eventMem = $event->eventMem;
        
        switch ($eventMem->typ) {
            case EventMem::PLAN_LIST_CHANGE:
                break;
            case EventMem::HUB_LIST_CHANGE:
                $this->_checkDaemonState($eventMem->getData()->typ);
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
     * 
     * @param type $typ
     */
    private function _checkDaemonState($typ)
    {
        $cross = [
            'extapi' => 'extapi-daemon',
            'orangepi' => 'orangepi-daemon',
            'din' => 'din-daemon',
            'camcorder' => 'camcorder-daemon',
        ];
        
        if (isset($cross[$typ])) {
            $manager = new \App\Library\DaemonManager();
            if (!$manager->isStarted($cross[$typ])) {
                $manager->start($cross[$typ]);
            }
        }
    }
}
