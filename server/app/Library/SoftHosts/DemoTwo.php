<?php

namespace App\Library\SoftHosts;

class DemoTwo extends SoftHostBase
{
    public $name = 'DemoTwo';
    public $description = 'Description Two';
    public $channels = [
        'C4',
        'C5',
        'C6',
    ];
    public $properties = [
        'Api Key' => 'large',
        'Input' => 'small',
    ];
}
