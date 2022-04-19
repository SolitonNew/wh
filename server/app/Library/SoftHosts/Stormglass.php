<?php

namespace App\Library\SoftHosts;

class Stormglass extends SoftHostBase
{
    const URL = 'https://api.stormglass.io/v2';
    
    public $name = 'stormglass';
    public $channels = [
        'TEMP', // airTemperature - Air temperature in degrees celsius
        'P',    // pressure - Air pressure in hPa
        'CC',   // cloudCover - Total cloud coverage in percent
        'CD',   // currentDirection - Direction of current. 0° indicates current coming from north
        'CS',   // currentSpeed - Speed of current in meters per second
        'G',    // gust - Wind gust in meters per second
        'H',    // humidity - Relative humidity in percent
        'V',    // visibility - Horizontal visibility in km
        'WD',   // windDirection - Direction of wind at 10m above sea level. 0° indicates wind coming from north
        'WS',   // windSpeed - Speed of wind at 10m above sea level in meters per second.
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
