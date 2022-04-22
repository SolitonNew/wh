<?php

namespace App\Library\SoftHostDrivers;

use \Carbon\Carbon;
use App\Models\Device;
use App\Models\Property;
use Log;

class OpenWeather extends SoftHostDriverBase
{
    const URL = 'https://api.openweathermap.org/data/2.5';
    const PRESSURE_K = 1.357; // 1.333
    
    public $name = 'openweather';
    public $channels = [
        'TEMP', // Air temperature in degrees celsius
        'P',    // Air pressure in hPa
        'CC',   // Total cloud coverage in percent
        'G',    // Wind gust in meters per second
        'H',    // Relative humidity in percent
        'V',    // Horizontal visibility in km
        'WD',   // Direction of wind at 10m above sea level. 0° indicates wind coming from north
        'WS',   // Speed of wind at 10m above sea level in meters per second.
    ];
    public $properties = [
        'api_id' => 'large',
    ];
    
    protected $requestCronExpression = '0 * * * *'; // '0 0,4,8,12,16,20 * * *';
    
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
            if (!$date || Carbon::parse($date)->diffInHours(Carbon::now()) > 1) {
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
        $apiID = $this->getDataValue('api_id');
        if (!$apiID) {
            throw new \Exception('Bad api id value');
        }
        
        $client = new \GuzzleHttp\Client([
            'http_errors' => false,
        ]);
        
        $location = Property::getLocation();
        
        $options = [
            'query' => [
                'lat' => $location->latitude,
                'lon' => $location->longitude,
                'appid' => $apiID,
                'units' => 'metric',
            ],
        ];
        
        $result = $client->get(self::URL.'/forecast', $options);
        
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
     * @param type $item
     * @return type
     */
    private function _load_channels($item)
    {
        return [
            'TEMP' => $item->main->temp,
            'P' => round($item->main->pressure / self::PRESSURE_K),
            'CC' => $item->clouds->all,
            'G' => $item->wind->gust,
            'H' => $item->main->humidity,
            'V' => $item->visibility / 1000,
            'WD' => $item->wind->deg,
            'WS' => $item->wind->speed,
        ];
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
        
        $json = json_decode($data);
        foreach ($json->list as $item) {
            $time = Carbon::createFromTimestamp($item->dt, 'UTC')->startOfHour();
            if ($time->gte($utcNow)) {
                $values = $this->_load_channels($item);
                break;
            }
        }
        
        $resultLog = [];
        
        $devices = $this->getAssociatedDevices();
        foreach ($devices as $device) {
            if (isset($values[$device->channel])) {
                $value = $values[$device->channel];
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
        $now = \Carbon\Carbon::now('UTC')->startOfHour()->addHours($afterHours);
        
        $data = json_decode($this->getLastStorageData());
        
        foreach ($data->list as $item) {
            $time = \Carbon\Carbon::createFromTimestamp($item->dt, 'UTC');
            $time->startOfHour();
            if ($time->gte($now)) {
                $values = $this->_load_channels($item);
                return $values[$channel];
            }
        }
        
        return 0;
    }
}
