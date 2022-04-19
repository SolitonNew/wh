<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\SoftHosts;

class SoftHostsManager 
{
    private $_providers = [];
    
    public function __construct() 
    {
        foreach (config('softhosts.providers') as $provider) {
            $this->_providers[] = new $provider();
        }
    }
    
    /**
     * 
     * @return type
     */
    public function providers()
    {
        return $this->_providers;
    }
    
    /**
     * 
     * @param type $name
     * @return type
     */
    public function providerByName($name)
    {
        foreach ($this->_providers as $provider) {
            if ($provider->name == $name) {
                return $provider;
            }
        }
        
        return null;
    }
}