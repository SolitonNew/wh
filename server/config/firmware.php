<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

return [
    'mmcu' => env('MMCU', 'atmega8a'),
    'spm_pagesize' => env('SPM_PAGESIZE', 128),
    
    'rs485_port' => env('RS485_PORT', '/dev/ttyUSB0'),
    'rs485_baud' => env('RS485_BAUD', 9600),
    
    'channels' => [
        'atmega8a' => [
            'R1', 
            'R2', 
            'R3', 
            'R4',
        ],
        'atmega328a' => [
            'R1', 
            'R2', 
            'R3', 
            'R4',
        ],
        'atmega16a' => [
            'R1', 
            'R2', 
            'R3', 
            'R4',
            'R5', 
            'R6', 
            'R7', 
            'R8',
            'R9', 
            'R10', 
            'R11', 
            'R12',
            'R13', 
            'R14', 
            'R15', 
            'R16',
        ],
    ],
];