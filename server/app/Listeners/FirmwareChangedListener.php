<?php

namespace App\Listeners;

use App\Events\FirmwareChangedEvent;
use App\Models\Property;
use App\Services\Admin\Autotest;

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
    public function handle(FirmwareChangedEvent $event): void
    {
        $n = Property::getFirmwareChanges();
        Property::setFirmwareChanges($n + 1);
        
        $autotest = new Autotest();
        $autotest->runForAllScripts();
        $autotest->runForAllSchedules();
    }
}
