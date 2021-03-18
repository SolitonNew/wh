<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

return [
    'mmcu' => env('MMCU', 'atmega8a'),
    
    'rs485_port' => env('RS485_PORT', '/dev/ttyUSB0'),
    'rs485_baud' => env('RS485_BAUD', 9600),
];