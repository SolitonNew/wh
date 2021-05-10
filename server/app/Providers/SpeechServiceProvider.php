<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Library\Speech;

class SpeechServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('speech', function () {
            return new Speech();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
