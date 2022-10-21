<?php

namespace App\Library\ExtApiHostDrivers;

use App\Models\ExtApiHostStorage;
use App\Models\Device;
use \Cron\CronExpression;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Lang;

class ExtApiHostDriverBase
{
    /**
     * @var string
     */
    public string $name = '';

    /**
     * @var array
     */
    public array $channels = [];

    /**
     * @var array
     */
    public array $properties = [];   // Key => small|large

    /**
     * @var mixed
     */
    protected mixed $data;

    /**
     * @var string|null
     */
    protected string|null $key;

    /**
     * @var string
     */
    protected string $requestCronExpression = '* * * * *';

    /**
     * @var string
     */
    protected string $updateCronExpression = '* * * * *';

    public function __get($name) {
        switch ($name) {
            case 'title':
            case 'description':
                return Lang::get('admin/extapihosts/'.$this->name.'.'.$name);
        }
    }

    /**
     * @param string|null $key
     * @return void
     */
    public function assignKey(string|null $key): void
    {
        $this->key = $key;
    }

    /**
     * @param mixed $data
     * @return void
     */
    public function assignData(mixed $data): void
    {
        $this->data = json_decode($data ?? '');
    }

    /**
     * @return array
     */
    public function propertiesWithTitles(): array
    {
        $result = [];
        foreach ($this->properties as $key => $val) {
            $result[$key] = (object)[
                'title' => Lang::get('admin/extapihosts/'.$this->name.'.'.$key),
                'size' => $val,
            ];
        }

        return $result;
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getDataValue(string $key): string
    {
        return isset($this->data->$key) ? $this->data->$key : '';
    }

    /**
     * @param string $data
     * @return void
     * @throws \Exception
     */
    protected function putStorageData(string $data): void
    {
        if (!json_decode($data)) {
            throw new \Exception('Bad request content.');
        }

        ExtApiHostStorage::create([
            'extapi_host_id' => $this->key,
            'data' => $data,
        ]);
    }

    /**
     * @return string|null
     */
    protected function getLastStorageData(): string|null
    {
        $row = ExtApiHostStorage::where('extapi_host_id', $this->key)
            ->orderBy('created_at', 'desc')
            ->first();

        return $row ? $row->data : null;
    }

    /**
     *
     * @return type
     */
    public function getLastStorageDatetime()
    {
        return ExtApiHostStorage::where('extapi_host_id', $this->key)->max('created_at');
    }

    /**
     * @return Collection
     */
    public function getAssociatedDevices(): Collection
    {
        return Device::whereTyp('extapi')
            ->whereHostId($this->key)
            ->get();
    }

    /**
     * @return bool
     */
    public function canRequest(): bool
    {
        return CronExpression::factory($this->requestCronExpression)->isDue();
    }

    /**
     * @return string
     */
    public function request(): string
    {
        return '';
    }

    /**
     * @return bool
     */
    public function canUpdate(): bool
    {
        return CronExpression::factory($this->updateCronExpression)->isDue();
    }

    /**
     * @return string
     */
    public function update(): string
    {
        return '';
    }

    /**
     * @param string $channel
     * @param int $afterHours
     * @return float
     */
    public function getForecastValue(string $channel, int $afterHours): float
    {
        return 0;
    }

    /**
     * @return array
     */
    public function getLastForecastData(): array
    {
        return [];
    }
}
