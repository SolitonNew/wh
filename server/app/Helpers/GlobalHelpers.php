<?php

if (!function_exists('parse_datetime')) {
    function parse_datetime($datetime)
    {
        return \Carbon\Carbon::parse($datetime, 'UTC')->setTimezone(config('app.timezone'));
    }
}

if (!function_exists('eq_floats')) {
    function eq_floats(float $a, float $b)
    {   
        return round($a * 1000) === round($b * 1000);
    }
}