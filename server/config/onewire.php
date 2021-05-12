<?php

return [
    'types' =>[
        0x28 => [
            'description' => 'DS18B20',
            'channels' => ['TEMP'],
            'consuming' => 1,
        ],
        0xF0 => [
            'description' => 'Two buttons switch',
            'channels' => ['LEFT', 'RIGHT'],
            'consuming' => 100,
        ],
        0xF1 => [
            'description' => 'Venting',
            'channels' => ['F1', 'F2', 'F3', 'F4'],
            'consuming' => 100,
        ],
        0xF2 => [
            'description' => 'Pin converter',
            'channels' => ['P1', 'P2', 'P3', 'P4'],
            'consuming' => 100,
        ],
        0xF3 => [
            'description' => 'Humidity sensor',
            'channels' => ['H', 'T'],
            'consuming' => 100,
        ],
        0xF4 => [
            'description' => 'Gas sensor',
            'channels' => ['CO'],
            'consuming' => 0,
        ],
        0xF5 => [
            'description' => 'Currency sensor',
            'channels' => ['AMP'],
            'consuming' => 0,
        ],
        0xF6 => [
            'description' => 'Relay',
            'channels' => ['R1', 'R2', 'R3', 'R4'],
            'consuming' => 100,
        ],
    ],
];