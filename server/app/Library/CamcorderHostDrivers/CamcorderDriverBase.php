<?php

namespace App\Library\CamcorderHostDrivers;

use App\Models\Device;
use \Cron\CronExpression;
use Illuminate\Support\Facades\Lang;

class CamcorderDriverBase
{
    /**
     *
     * @var type 
     */
    public $name = '';
    
    /**
     *
     * @var type 
     */
    public $channels = [];
    
    /**
     *
     * @var type 
     */
    public $properties = [];   // Key => small|large
    
    /**
     *
     * @var type 
     */
    protected $data;
    
    /**
     *
     * @var type 
     */
    protected $key;
    
    /**
     *
     * @var type 
     */
    protected $thumbnailCronExpression = '* * * * *';
    
    public function __get($name) {
        switch ($name) {
            case 'title':
            case 'description':
                return Lang::get('admin/camcorderhosts/'.$this->name.'.'.$name);
        }
    }
    
    /**
     * 
     * @param type $key
     */
    public function assignKey($key)
    {
        $this->key = $key;
    }
    
    /**
     * 
     * @param type $data
     */
    public function assignData($data)
    {
        $this->data = json_decode($data);
    }
    
    /**
     * 
     * @return type
     */
    public function propertiesWithTitles()
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
     * 
     * @param type $key
     * @return type
     */
    protected function getDataValue($key)
    {
        return isset($this->data->$key) ? $this->data->$key : '';
    }
    
    /**
     * 
     * @return type
     */
    public function getAssociatedDevices()
    {
        return Device::whereTyp('camcorder')
            ->whereHostId($this->key)
            ->get();
    }
    
    /**
     * 
     * @return boolean
     */
    public function canThumbnailRequest()
    {
        return CronExpression::factory($this->thumbnailCronExpression)->isDue();
    }
    
    /**
     * 
     * @return string
     */
    public function thumbnailRequest() {
        return '';
    }    
}
