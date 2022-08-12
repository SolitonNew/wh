<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

return [
    /**
     * List of processes running in the background
     */
    'list' => [
        'extapi-daemon',
        'din-daemon',
        'orangepi-daemon',
        'schedule-daemon',
        'command-daemon',
        'observer-daemon',
        'camcorder-daemon',
        'eventtransmitter-daemon',
        'websockets:serve',
    ],
];