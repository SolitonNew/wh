<?php

namespace App\Library\SoftHosts;

class DemoTwo extends SoftHostBase
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
