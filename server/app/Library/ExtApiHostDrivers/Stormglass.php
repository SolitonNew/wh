<?php

namespace App\Library\ExtApiHostDrivers;

use \Carbon\Carbon;
use App\Models\Device;
use App\Models\Property;

class Stormglass extends ExtApiHostDriverBase
{
    private const URL = 'https://api.stormglass.io/v2';
    private const PRESSURE_K = 1.357; // 1.333

    /**
     * @var string
     */
    public string $name = 'stormglass';

    /**
     * @var array|string[]
     */
    public array $channels = [
        'TEMP', // airTemperature - Air temperature in degrees celsius
        'P',    // pressure - Air pressure in hPa
        'CC',   // cloudCover - Total cloud coverage in percent
        'G',    // gust - Wind gust in meters per second
        'H',    // humidity - Relative humidity in percent
        'V',    // visibility - Horizontal visibility in km
        'WD',   // windDirection - Direction of wind at 10m above sea level. 0° indicates wind coming from north
        'WS',   // windSpeed - Speed of wind at 10m above sea level in meters per second.
        'MP',   // precipitation - Mean precipitation in kg/m²/h = mm/h
    ];

    /**
     * @var array|string[]
     */
    public array $properties = [
        'api_key' => 'large',
    ];

    /**
     * @var string
     */
    protected string $requestCronExpression = '0 0,4,8,12,16,20 * * *';

    /**
     * @var string
     */
    protected string $updateCronExpression = '* * * * *';

    /**
     * Override this method for request after reboot.
     *
     * @return bool
     */
    public function canRequest(): bool
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
     * @return string
     * @throws \Exception
     */
    public function request(): string
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
            'precipitation',
        ];

        $location = Property::getLocation();

        $options = [
            'headers' => [
                'Authorization' => $apiKey,
            ],
            'query' => [
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
                throw new \Exception($ex->getMessage());
            }
        } else {
            throw new \Exception('REQUEST STATUS: '.$result->getStatusCode().' MESSAGE: '.$result->getBody());
        }

        return '';
    }

    /**
     * @param object $item
     * @return array
     */
    private function load_channels(object $item): array
    {
        return [
            'TEMP' => $item->airTemperature->sg,
            'P' => round($item->pressure->sg / self::PRESSURE_K),
            'CC' => $item->cloudCover->sg,
            'G' => $item->gust->sg,
            'H' => $item->humidity->sg,
            'V' => $item->visibility->sg,
            'WD' => $item->windDirection->sg,
            'WS' => $item->windSpeed->sg,
            'MP' => $item->precipitation->sg,
        ];
    }

    /**
     * @return string
     */
    public function update(): string
    {
        $data = $this->getLastStorageData();

        if ($data == null) return '';
        $json = json_decode($data);
        if ($json == null) return '';

        $utcNow = Carbon::now('UTC')->startOfHour();

        $values = [];

        foreach ($json->hours as $hour) {
            $hourTime = Carbon::parse($hour->time, 'UTC')->startOfHour();
            if ($hourTime->eq($utcNow)) {
                $values = $this->load_channels($hour);
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
     * @param string $channel
     * @param int $afterHours
     * @return float
     */
    public function getForecastValue(string $channel, int $afterHours): float
    {
        $now = \Carbon\Carbon::now('UTC')->startOfHour()->addHours($afterHours);

        $data = json_decode($this->getLastStorageData());

        foreach ($data->hours as $hour) {
            $time = \Carbon\Carbon::parse($hour->time, 'UTC');
            $time->startOfHour();
            if ($time == $now) {
                $values = $this->load_channels($hour);
                return $values[$channel];
            }
        }

        return 0;
    }

    /**
     * @return array
     */
    public function getLastForecastData(): array
    {
        $storage = json_decode($this->getLastStorageData());

        if (!$storage) return [];

        $result = [];
        foreach ($storage->hours as $hour) {
            $time = \Carbon\Carbon::parse($hour->time, 'UTC')->startOfHour()->timestamp;
            $result[$time] = (object)$this->load_channels($hour);
        }
        return $result;
    }
}
