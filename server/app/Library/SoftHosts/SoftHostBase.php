<?php

namespace App\Library\SoftHosts;

use Log;

class SoftHostBase 
{
    public $name = '';
    public $description = '';
    public $channels = [];
    public $properties = [];   // Key => small|large
    
    public function execute() {
        Log::info('SOFTWARE HOST EXECUTE');
    }
}
