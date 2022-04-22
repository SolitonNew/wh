<?php

namespace App\Library\SoftHostDrivers;

class DemoTwo extends SoftHostDriverBase
{
    public $name = 'demo_two';
    public $channels = [
        'C4',
        'C5',
        'C6',
    ];
    public $properties = [
        'api_key' => 'large',
        'input' => 'small',
    ];
}
