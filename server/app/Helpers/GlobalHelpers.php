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