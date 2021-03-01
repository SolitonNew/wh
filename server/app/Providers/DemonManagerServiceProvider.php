<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use \App\Classes\DemonManager;

class DemonManagerServiceProvider extends ServiceProvider
{
    
    protected $defer = true;
    
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(DemonManager::class, function () {
            return new DemonManager();
        });
    }
}
