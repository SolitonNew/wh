<?php

namespace App\Library\SoftHosts;

class Demo extends SoftHostBase
{
    public $name = 'Demo';
    public $description = 'Description';
    public $channels = [
        'C1',
        'C2',
        'C3',
    ];
    public $properties = [
        'Api Key' => 'large',
        'Ext Params' => 'large',
        'Input' => 'small',
    ];
}
