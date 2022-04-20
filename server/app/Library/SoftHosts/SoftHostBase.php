<?php

namespace App\Library\SoftHosts;

use App\Models\SoftHostStorage;
use App\Models\Device;
use \Cron\CronExpression;
use Lang;

class SoftHostBase 
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
    protected $requestCronExpression = '* * * * *';
    
    /**
     *
     * @var type 
     */
    protected $updateCronExpression = '* * * * *';
    
    public function __get($name) {
        switch ($name) {
            case 'title':
            case 'description':
                return Lang::get('admin/softhosts/'.$this->name.'.'.$name);
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
                'title' => Lang::get('admin/softhosts/'.$this->name.'.'.$key),
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
     * @param type $data
     */
    protected function putStorageData($data)
    {
        SoftHostStorage::create([
            'soft_host_id' => $this->key, 
            'data' => $data,
        ]);
    }
    
    /**
     * 
     * @return type
     */
    protected function getLastStorageData()
    {
        $row = SoftHostStorage::where('soft_host_id', $this->key)
            ->orderBy('created_at', 'desc')
            ->first();
        
        return $row ? $row->data : null;
    }
    
    /**
     * 
     * @return type
     */
    public function getAssociatedDevices()
    {
        return Device::whereHostId($this->key)
            ->get();
    }
    
    /**
     * 
     * @return boolean
     */
    public function canRequest()
    {
        return CronExpression::factory($this->requestCronExpression)->isDue();
    }
    
    /**
     * 
     * @return string
     */
    public function request() {
        return '';
    }
    
    /**
     * 
     * @return type
     */
    public function canUpdate()
    {
        return CronExpression::factory($this->updateCronExpression)->isDue();
    }
    
    /**
     * 
     * @return string
     */
    public function update()
    {
        return '';
    }
}
