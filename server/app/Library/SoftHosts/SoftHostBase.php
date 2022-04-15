<?php

namespace App\Library\SoftHosts;

class SoftHostBase 
{
    public $name = '';
    public $description = '';
    public $channels = [];
    public $properties = [];   // Key => small|large
}
