<?php

namespace App\Library\SoftHosts;

class Stormglass extends SoftHostBase
{
    public $name = 'stormglass';
    public $channels = [
        'C1',
        'C2',
        'C3',
    ];
    public $properties = [
        'api_key' => 'large',
    ];
}
