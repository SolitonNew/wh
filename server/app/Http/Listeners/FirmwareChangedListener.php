<?php

namespace App\Http\Listeners;

use App\Http\Events\FirmwareChangedEvent;

class FirmwareChangedListener
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
     * @param  FirmwareChangedEvent  $event
     * @return void
     */
    public function handle(FirmwareChangedEvent $event)
    {
        $n = \App\Http\Models\PropertysModel::getFirmwareChanges();
        \App\Http\Models\PropertysModel::setFirmwareChanges($n + 1);
    }
}
