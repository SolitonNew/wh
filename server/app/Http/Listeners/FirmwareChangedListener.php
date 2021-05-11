<?php

namespace App\Http\Listeners;

use App\Http\Events\FirmwareChangedEvent;
use App\Models\Property;

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
        $n = Property::getFirmwareChanges();
        Property::setFirmwareChanges($n + 1);
    }
}
