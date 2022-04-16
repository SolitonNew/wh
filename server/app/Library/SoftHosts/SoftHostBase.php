<?php

namespace App\Library\SoftHosts;

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
    protected $cron = '';
    
    /**
     *
     * @var type 
     */
    private $_lastExecuteTime = false;
    
    public function __get($name) {
        switch ($name) {
            case 'title':
            case 'description':
                return Lang::get('admin/softhosts/'.$this->name.'.'.$name);
        }
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
     * @return boolean
     */
    public function canExecute()
    {
        return false;
    }
    
    /**
     * 
     * @return string
     */
    public function execute() {
        return '';
    }
}
