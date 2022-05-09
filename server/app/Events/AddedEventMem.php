<?php

namespace App\Events;

use App\Models\EventMem;

class AddedEventMem extends Event
{
    public $eventMem = null;
    
    public function __construct(EventMem $eventMem)
    {
        $this->eventMem = $eventMem;
    }
}
