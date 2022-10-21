<?php

return [
    'default_port' => '/dev/ttyUSB0',
    'default_mmcu' => 'atmega16a',
    'mmcu_list' => [
        'atmega328' => [
            'baud' => 9600,
            'spm_pagesize' => 128,
            'channels' => [
                'R1',
                'R2',
                'R3',
                'R4',
            ],
        ],
        'atmega16a' => [
            'baud' => 9600,
            'spm_pagesize' => 128,
            'channels' => [
                'R1',
                'R2',
                'R3',
                'R4',
            ],
        ],
    ]
];
