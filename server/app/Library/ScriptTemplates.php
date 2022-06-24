<?php

return [
    '-- none --' => [
        'name' => '',
        'params' => [],
        'template' => "",
    ],
    'Light Switch' => [
        'name' => 'Light Switch @ROOM@',
        'params' => [
            'LIGHT' => [
                'title' => 'Light',
                'typ' => 'device',
                'event' => false,
                'app_control' => [1, 3],
            ],
            'SWITCH' => [
                'title' => 'Switch',
                'typ' => 'device',
                'event' => true,
                'app_control' => [2],
            ],
        ],
        'template' => "if (get('@SWITCH@')) {\n    toggle('@LIGHT@');\n}",
    ],
    'Termostat' => [
        'name' => 'Termostat @ROOM@',
        'params' => [
            'TERMOMETR' => [
                'title' => 'Termometr',
                'typ' => 'device',
                'event' => true,
                'app_control' => [4],
            ],
            'HEATER' => [
                'title' => 'Heater',
                'typ' => 'device',
                'event' => false,
                'app_control' => [3],
            ],
            'CONTROL' => [
                'title' => 'Control',
                'typ' => 'variable',
                'event' => true,
                'app_control' => [5],
            ],
            'SENSITIVITY' => [
                'title' => 'Sensitivity',
                'typ' => 'const',
            ],
        ],
        'template' => "if (get('@TERMOMETR@') > get('@CONTROL@')) {\n    off('@HEATER@');\n} else\nif(get('@TERMOMETR@') < get('@CONTROL@') - @SENSITIVITY@) {\n    on('@HEATER@');\n}",
    ],
    'WC Venting' => [
        'name' => 'WC Venting @ROOM@',
        'params' => [
            'MOTION' => [
                'title' => 'Motion',
                'typ' => 'device',
                'event' => false,
                'app_control' => [3],
            ],
            'DOOR' => [
                'title' => 'Door',
                'typ' => 'device',
                'event' => false,
                'app_control' => [3],
            ],
            'FAN' => [
                'title' => 'Fan',
                'typ' => 'device',
                'event' => false,
                'app_control' => [3],
            ],
            'LIGHT' => [
                'title' => 'Light',
                'typ' => 'device',
                'event' => false,
                'app_control' => [3],
            ]
        ],
        'template' => "",
    ]
];
