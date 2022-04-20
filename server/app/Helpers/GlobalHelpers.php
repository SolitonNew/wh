<?php

if (!function_exists('parse_datetime')) {
    function parse_datetime($datetime)
    {
        return \Carbon\Carbon::parse($datetime, 'UTC')->setTimezone(config('app.timezone'));
    }
}
