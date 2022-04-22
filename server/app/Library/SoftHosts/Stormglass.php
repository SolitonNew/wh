<?php

namespace App\Library\SoftHosts;

use \Carbon\Carbon;
use App\Models\Device;
use App\Models\Property;

class Stormglass extends SoftHostBase
{
    const URL = 'https://api.stormglass.io/v2';
    const PRESSURE_K = 1.357; // 1.333
    
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
     * Override this method for request after reboot.
     * 
     * @return boolean
     */
    public function canRequest()
    {
        $result = parent::canRequest();
        
        if (!$result) {
            $date = $this->getLastStorageDatetime();
            if (!$date || Carbon::parse($date)->diffInHours(Carbon::now()) > 4) {
                $result = true;
            }
        }
        
        return $result;
    }
    
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
        
        $params = [
            'airTemperature',
            'pressure',
            'cloudCover',
            'gust',
            'humidity',
            'visibility',
            'windDirection',
            'windSpeed',
        ];
        
        $location = Property::getLocation();
        
        $options = [
            'headers' => [
                'Authorization' => $apiKey,
            ],
            'form_params' => [
                'lat' => $location->latitude,
                'lng' => $location->longitude,
                'params' => implode(',', $params),
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
        
        $values = [];
        
        $putValue = function(&$dest, &$source, $channel, $key) {
            if (isset($source->$key) && isset($source->$key->sg)) {
                $dest[$channel] = $source->$key->sg;
            }
        };
        
        $json = json_decode($data);
        foreach ($json->hours as $hour) {
            $hourTime = Carbon::parse($hour->time, 'UTC')->startOfHour();
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
        
        $resultLog = [];
        
        $devices = $this->getAssociatedDevices();
        foreach ($devices as $device) {
            if (isset($values[$device->channel])) {
                switch ($device->channel) {
                    case 'P':
                        $value = round($values[$device->channel] / self::PRESSURE_K);
                        break;
                    default:
                        $value = $values[$device->channel];
                }
                
                if (!eq_floats($device->value, $value)) {
                    Device::setValue($device->id, $value);
                    $resultLog[] = $value; 
                }
            }
        }
        
        if (count($resultLog)) {
            return '   <<   ['.implode(', ', $resultLog).']';
        } else {
            return '';
        }
    }
    
    /**
     * 
     * @param type $channel
     * @param type $afterHours
     * @return int
     */
    public function getForecastValue($channel, $afterHours)
    {
        $cannels = [
            'TEMP' => 'airTemperature',
            'P' => 'pressure',
            'CC' => 'cloudCover',
            'G' => 'gust',
            'H' => 'humidity',
            'V' => 'visibility',
            'WD' => 'windDirection',
            'WS' => 'windSpeed',
        ];
        
        $attr = $cannels[$channel];
        
        $now = \Carbon\Carbon::now('UTC')->startOfHour()->addHours($afterHours);
        
        $data = json_decode($this->getLastStorageData());
        
        foreach ($data->hours as $hour) {
            $time = \Carbon\Carbon::parse($hour->time, 'UTC');
            $time->startOfHour();
            if ($time == $now) {
                switch ($attr) {
                    case 'pressure':
                        return round($hour->$attr->sg / self::PRESSURE_K);
                    default:
                        return $hour->$attr->sg;
                }
            }
        }
        
        return 0;
    }
}
