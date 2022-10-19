<?php

namespace App\Library\ExtApiHostDrivers;

use \Carbon\Carbon;
use App\Models\Device;
use App\Models\Property;

class OpenWeather extends ExtApiHostDriverBase
{
    private const URL = 'https://api.openweathermap.org/data/2.5';
    private const PRESSURE_K = 1.357; // 1.333

    /**
     * @var string
     */
    public string $name = 'openweather';

    /**
     * @var array|string[]
     */
    public array $channels = [
        'TEMP', // Air temperature in degrees celsius
        'P',    // Air pressure in hPa
        'CC',   // Total cloud coverage in percent
        'G',    // Wind gust in meters per second
        'H',    // Relative humidity in percent
        'V',    // Horizontal visibility in km
        'WD',   // Direction of wind at 10m above sea level. 0° indicates wind coming from north
        'WS',   // Speed of wind at 10m above sea level in meters per second.
        'MP',   //
    ];

    /**
     * @var array|string[]
     */
    public array $properties = [
        'api_id' => 'large',
        'test' => 'small',
    ];

    /**
     * @var string
     */
    protected string $requestCronExpression = '0 * * * *'; // '0 0,4,8,12,16,20 * * *';

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
            if (!$date || Carbon::parse($date)->diffInHours(Carbon::now()) > 1) {
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
            'TEMP' => $item->main->temp,
            'P' => round($item->main->pressure / self::PRESSURE_K),
            'CC' => $item->clouds->all,
            'G' => $item->wind->gust,
            'H' => $item->main->humidity,
            'V' => $item->visibility / 1000,
            'WD' => $item->wind->deg,
            'WS' => $item->wind->speed,
            'MP' => isset($item->rain) ? round($item->rain->{"3h"} / 3, 2) : 0,
        ];
    }

    /**
     * @return string
     */
    public function update(): string
    {
        $data = $this->getLastStorageData();

        if ($data == null) return '';

        $utcNow = Carbon::now('UTC')->startOfHour();

        $values = [];

        $json = json_decode($data);
        foreach ($json->list as $item) {
            $time = Carbon::createFromTimestamp($item->dt, 'UTC')->startOfHour();
            if ($time->gte($utcNow)) {
                $values = $this->load_channels($item);
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

        foreach ($data->list as $item) {
            $time = \Carbon\Carbon::createFromTimestamp($item->dt, 'UTC');
            $time->startOfHour();
            if ($time->gte($now)) {
                $values = $this->load_channels($item);
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
        foreach ($storage->list as $item) {
            $time = \Carbon\Carbon::createFromTimestamp($item->dt, 'UTC')->startOfHour()->timestamp;
            $result[$time] = (object)$this->load_channels($item);
        }
        return $result;
    }
}
