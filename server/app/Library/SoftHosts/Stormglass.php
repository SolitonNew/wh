<?php

namespace App\Library\SoftHosts;

use \Carbon\Carbon;
use App\Models\Device;
use Log;

class Stormglass extends SoftHostBase
{
    const URL = 'https://api.stormglass.io/v2';
    
    public $name = 'stormglass';
    public $channels = [
        'TEMP', // airTemperature - Air temperature in degrees celsius
        'P',    // pressure - Air pressure in hPa
        'CC',   // cloudCover - Total cloud coverage in percent
        'G',    // gust - Wind gust in meters per second
        'H',    // humidity - Relative humidity in percent
        'V',    // visibility - Horizontal visibility in km
        'WD',   // windDirection - Direction of wind at 10m above sea level. 0° indicates wind coming from north
        'WS',   // windSpeed - Speed of wind at 10m above sea level in meters per second.
    ];
    public $properties = [
        'api_key' => 'large',
    ];
    
    protected $requestCronExpression = '0 0,4,8,12,16,20 * * *';
    
    protected $updateCronExpression = '* * * * *';
    
    /**
     * 
     * @return string
     * @throws \Exception
     */
    public function request()
    {
        $apiKey = $this->getDataValue('api_key');
        if (!$apiKey) {
            throw new \Exception('Bad api key value');
        }
        
        $client = new \GuzzleHttp\Client([
            'http_errors' => false,
        ]);
        
        $parans = [
            'airTemperature',
            'pressure',
            'cloudCover',
            'gust',
            'humidity',
            'visibility',
            'windDirection',
            'windSpeed',
        ];
        
        $options = [
            'headers' => [
                'Authorization' => $apiKey,
            ],
            'form_params' => [
                'lat' => config('settings.location_latitude'),
                'lng' => config('settings.location_longitude'),
                'params' => implode(',', $parans),
            ],
        ];
        
        $result = $client->get(self::URL.'/weather/point', $options);
        
        if ($result->getStatusCode() == 200) {
            try {
                $body = $result->getBody();
                $this->putStorageData($body);
            } catch (\Exception $ex) {
                throw new \Exception('REQUEST ERROR: '.$ex->getMessage());
            }
        } else {
            throw new \Exception('REQUEST ERROR: '.$result->getStatusCode().' MESSAGE: '.$result->getBody());
        }
        
        return '';
    }
    
    /**
     * 
     * @return string
     */
    public function update()
    {
        $data = $this->getLastStorageData();
        
        if ($data == null) return '';
        
        $utcNow = Carbon::now('UTC')->startOfHour();
        $utcNext = $utcNow->clone()->addHours(1);
        
        $values = [];
        
        $putValue = function(&$dest, &$source, $channel, $key) {
            if (isset($source->$key) && isset($source->$key->sg)) {
                $dest[$channel] = $source->$key->sg;
            }
        };
        
        $json = json_decode($data);
        foreach ($json->hours as $hour) {
            $hourTime = Carbon::parse($hour->time)->startOfHour();
            if ($hourTime->eq($utcNow)) {
                $putValue($values, $hour, 'TEMP', 'airTemperature');
                $putValue($values, $hour, 'P', 'pressure');
                $putValue($values, $hour, 'CC', 'cloudCover');
                $putValue($values, $hour, 'G', 'gust');
                $putValue($values, $hour, 'H', 'humidity');
                $putValue($values, $hour, 'V', 'visibility');
                $putValue($values, $hour, 'WD', 'windDirection');
                $putValue($values, $hour, 'WS', 'windSpeed');
                break;
            }
        }
        
        $devices = $this->getAssociatedDevices();
        foreach ($devices as $device) {
            if (isset($values[$device->channel])) {
                switch ($device->channel) {
                    case 'P':
                        $value = round($values[$device->channel] / 1.333);
                        break;
                    default:
                        $value = $values[$device->channel];
                }
                
                if ($device->value != $value) {
                    Device::setValue($device->id, $value);
                }
            }
        }
        
        return '';
    }
}
