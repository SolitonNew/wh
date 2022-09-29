<?php

namespace App\Library\CamcorderHostDrivers;

class Tecsar extends CamcorderDriverBase
{
    /**
     * @var string
     */
    public string $name = 'tecsar';

    /**
     * @var array|string[]
     */
    public array $channels = [
        'REC',    // Recording channel
        'MOTION', // Alarm channel
    ];

    /**
     * @var array|string[]
     */
    public array $properties = [
        'url' => 'large',
    ];

    /**
     * @var string
     */
    protected string $thumbnailCronExpression = '*/5 * * * *';
}
