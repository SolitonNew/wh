<?php

namespace App\Library\CamcorderHostDrivers;

class Rtsp extends CamcorderDriverBase
{
    public $name = 'rtsp';
    public $channels = [
        'REC', // Recording channel
    ];
    public $properties = [
        'url' => 'large',
    ];
    
    protected $thumbnailCronExpression = '*/5 * * * *';
    
    public function requestThumbnail() 
    {
        
    }
}
