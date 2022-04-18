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
    
    protected $frequencyCronExpression = '0 */2 * * *';
    
    public function execute()
    {
        return 'RUN';
    }
}
