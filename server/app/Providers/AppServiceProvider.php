<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::directive('activeMenu', function ($page) {
            return '<?php 
                        echo (Request::segment(2) == '.$page.') ? "active" : "";
                     ?>';
        });
        
        Blade::directive('activeSegment', function ($params) {  // segmend, page
            $a = explode(', ', $params);
            $segment = $a[0];
            $page = trim($a[1]);
            return '<?php 
                        echo (Request::segment('.$segment.') == '.$page.') ? "active" : "";
                     ?>';
        });
    }
}
