<?php

namespace App\Library\SoftHosts;

class Demo extends SoftHostBase
{
    public $name = 'demo';
    public $channels = [
        'C1',
        'C2',
        'C3',
    ];
    public $properties = [
        'api_key' => 'large',
        'ext_params' => 'large',
        'input' => 'small',
    ];
}
