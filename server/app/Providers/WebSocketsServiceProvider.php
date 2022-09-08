<?php

namespace App\Providers;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use BeyondCode\LaravelWebSockets\WebSocketsServiceProvider as ParentServiceProvider;

/**
 * Description of WebSocketsServiceProvider
 *
 * @author User
 */
class WebSocketsServiceProvider extends ParentServiceProvider
{
    protected function registerRoutes()
    {
        return $this;
    }
    
    protected function registerDashboardGate()
    {
        return $this;
    }
}
