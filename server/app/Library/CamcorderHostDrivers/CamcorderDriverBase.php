<?php

namespace App\Library\CamcorderHostDrivers;

use App\Models\Device;
use \Cron\CronExpression;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Lang;

class CamcorderDriverBase
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
     * @var object|null
     */
    protected object|null $data;

    /**
     * @var string|null
     */
    protected string|null $key;

    /**
     * @var string|null
     */
    protected string|null $caption;

    /**
     * @var string
     */
    protected string $thumbnailCronExpression = '* * * * *';

    public function __get($name) {
        switch ($name) {
            case 'title':
            case 'description':
                return Lang::get('admin/camcorderhosts/'.$this->name.'.'.$name);
            case 'caption':
                return $this->caption;
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
     * @param string|null $caption
     * @return void
     */
    public function assignCaption(string|null $caption): void
    {
        $this->caption = $caption;
    }

    /**
     * @param string|null $data
     * @return void
     */
    public function assignData(string|null $data): void
    {
        $this->data = json_decode($data);
    }

    /**
     *
     * @return array
     */
    public function propertiesWithTitles(): array
    {
        $result = [];
        foreach ($this->properties as $key => $val) {
            $result[$key] = (object)[
                'title' => Lang::get('admin/camcorderhosts/'.$this->name.'.'.$key),
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
     * @return Collection
     */
    public function getAssociatedDevices(): Collection
    {
        return Device::whereTyp('camcorder')
            ->whereHostId($this->key)
            ->get();
    }

    /**
     * @return bool
     */
    public function canThumbnailRequest(): bool
    {
        return CronExpression::factory($this->thumbnailCronExpression)->isDue();
    }

    /**
     * @return string
     */
    public function requestThumbnail(): string
    {
        return '';
    }

    /**
     * @return bool
     */
    public function checkRecording(): bool
    {
        return false;
    }

    /**
     * @param int $key
     * @return string
     */
    public function startRecording(int $key): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function stopRecording(): string
    {
        return '';
    }
}
