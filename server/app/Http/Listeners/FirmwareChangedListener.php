<?php

namespace App\Http\Listeners;

use App\Http\Events\FirmwareChangedEvent;
use App\Models\PropertysModel;

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
        $n = PropertysModel::getFirmwareChanges();
        PropertysModel::setFirmwareChanges($n + 1);
    }
}
