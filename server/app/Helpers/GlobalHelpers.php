<?php

if (!function_exists('parse_datetime')) {
    function parse_datetime($datetime)
    {
        return \Carbon\Carbon::parse($datetime, 'UTC')->setTimezone(App\Models\Property::getTimezone());
    }
}

if (!function_exists('eq_floats')) {
    function eq_floats(float $a, float $b, int $size = 3)
    {   
        switch ($size) {
            case 0:
                return round($a) === round($b);
            case 1:
                return round($a * 10) === round($b * 10);
            case 2:
                return round($a * 100) === round($b * 100);
            case 3:
                return round($a * 1000) === round($b * 1000);
            default:
                return round($a * 10000) === round($b * 10000);
        }
    }
}

if (!function_exists('active_segment')) {
    function active_segment(int $segment, string $page)
    {
        $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri_segments = explode('/', $uri_path);
        
        if (isset($uri_segments[$segment]) && $uri_segments[$segment] == $page) {
            return 'active';
        }
        return '';
    }
}

if (!function_exists('now')) {
    function now($tz = 'UTC')
    {
        return \Carbon\Carbon::now($tz);
    }
}