<?php

namespace App\Library\SoftHosts;

use Lang;

class SoftHostBase 
{
    public $name = '';
    public $channels = [];
    public $properties = [];   // Key => small|large
    protected $data;
    
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
     */
    public function execute() {
        return '';
    }
}
