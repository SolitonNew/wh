<?php

return [
    'types' => [
        'bmp280' => [
            'address' => [0x76, 0x77],
            'description' => 'Humidity, Pressure and Temperature Sensor',
            'channels' => ['TEMP', 'P'],
            'consuming' => 1,
        ],
    ],
];