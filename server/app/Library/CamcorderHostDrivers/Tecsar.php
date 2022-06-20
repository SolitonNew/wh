<?php

namespace App\Library\CamcorderHostDrivers;

class Tecsar extends CamcorderDriverBase
{
    public $name = 'tecsar';
    public $channels = [
        'REC',    // Recording channel
        'MOTION', // Alarm channel
    ];
    public $properties = [
        'url' => 'large',
    ];
    
    protected $thumbnailCronExpression = '*/5 * * * *';
    
}
