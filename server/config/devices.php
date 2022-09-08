<?php

return [
    'app_controls' => [   // typ: 1-chart, 2-switch, 3-tracking
        0 => [
            'typ' => -1,
            'title' => '-//-',
            'log' => '-//-',
            'values' => [],
            'unit' => '',
            'min' => 0,
            'max' => 100,
            'step' => 1,
            'visible' => false,
        ],
        1 => [
            'typ' => 2,
            'title' => 'Light',
            'log' => 'LIGHT',
            'values' => [0 => 'OFF', 1 => 'ON'],
            'unit' => '',
            'min' => 0,
            'max' => 1,
            'step' => 1,
            'visible' => true,
        ],
        2 => [
            'typ' => 2,
            'title' => 'Switch',
            'log' => 'SWITCH',
            'values' => [0 => 'UNPRESSED', 1 => 'PRESSET'],
            'unit' => '',
            'min' => 0,
            'max' => 1,
            'step' => 1,
            'visible' => false,
        ],
        3 => [
            'typ' => 2,
            'title' => 'Socket',
            'log' => 'SOCKET',
            'values' => [0 => 'OFF', 1 => 'ON'],
            'unit' => '',
            'min' => 0,
            'max' => 1,
            'step' => 1,
            'visible' => true,
        ],
        4 => [
            'typ' => 1,
            'title' => 'Termometer',
            'log' => 'TEMP.',
            'values' => [],
            'unit' => '°C',
            'min' => -45,
            'max' => 125,
            'step' => 1,
            'visible' => true,
        ],
        5 => [
            'typ' => 3,
            'title' => 'Termostat',
            'log' => 'TSTAT.',
            'values' => [],
            'unit' => '°C',
            'min' => 15,
            'max' => 30,
            'step' => 1,
            'visible' => true,
        ],
        6 => [
            'typ' => 2,
            'title' => 'Camcorder',
            'log' => 'CAM',
            'values' => [0 => 'REC. OFF', 1 => 'REC. ON'],
            'unit' => '',
            'min' => 0,
            'max' => 1,
            'step' => 1,
            'visible' => true,
        ],
        7 => [
            'typ' => 3,
            'title' => 'Venting',
            'log' => 'VENT.',
            'values' => [],
            'unit' => '%',
            'min' => 0,
            'max' => 100,
            'step' => 10,
            'visible' => true,
        ],
        8 => [
            'typ' => 2,
            'title' => 'Motion',
            'log' => 'MOTION',
            'values' => [0 => 'NORMAL', 1 => 'MOTION'],
            'unit' => '',
            'min' => 0,
            'max' => 1,
            'step' => 1,
            'visible' => false,
        ],
        9 => [
            'typ' => 2,
            'title' => 'Leakage',
            'log' => 'LEAKAGE',
            'values' => [0 => 'NORMAL', 1 => 'ALARM'],
            'unit' => '',
            'min' => 0,
            'max' => 1,
            'step' => 1,
            'visible' => false,
        ],
        10 => [
            'typ' => 1,
            'title' => 'Humidity',
            'log' => 'HUMID.',
            'values' => [],
            'unit' => '%',
            'min' => 0,
            'max' => 100,
            'step' => 1,
            'visible' => true,
        ],
        11 => [
            'typ' => 1,
            'title' => 'Gas sensor',
            'log' => 'GAS',
            'values' => [],
            'unit' => 'ppm',
            'min' => 0,
            'max' => 10000,
            'step' => 1,
            'visible' => true,
        ],
        12 => [
            'typ' => 2,
            'title' => 'Door sensor',
            'log' => 'DOOR',
            'values' => [0 => 'OPEN', 1 => 'CLOSE'],
            'unit' => '',
            'min' => 0,
            'max' => 1,
            'step' => 1,
            'visible' => false,
        ],
        13 => [
            'typ' => 1,
            'title' => 'Atmosphere pressure',
            'log' => 'ATM.',
            'values' => [],
            'unit' => 'mm',
            'min' => 500,
            'max' => 800,
            'step' => 1,
            'visible' => true,
        ],
        14 => [
            'typ' => 1,
            'title' => 'Current',
            'log' => 'CURR.',
            'values' => [],
            'unit' => 'A',
            'min' => 0,
            'max' => 1000,
            'step' => 1,
            'visible' => true,
        ],
        15 => [
            'typ' => 1,
            'title' => 'Speed',
            'log' => 'SPEED',
            'values' => [],
            'unit' => 'm/s',
            'min' => 0,
            'max' => 50,
            'step' => 1,
            'visible' => true,
        ],
        16 => [
            'typ' => 1,
            'title' => 'Direction',
            'log' => 'DIRE.',
            'values' => [],
            'unit' => '°',
            'min' => 0,
            'max' => 359,
            'step' => 1,
            'visible' => true,
        ],
        17 => [
            'typ' => 1,
            'title' => 'Distance',
            'log' => 'DIST',
            'values' => [],
            'unit' => 'km',
            'min' => 0,
            'max' => 20,
            'step' => 1,
            'visible' => true,
        ],
        18 => [
            'typ' => 1,
            'title' => 'Percents',
            'log' => 'PERC.',
            'values' => [],
            'unit' => '%',
            'min' => 0,
            'max' => 100,
            'step' => 1,
            'visible' => true,
        ],
        19 => [
            'typ' => 1,
            'title' => 'Height',
            'log' => 'HIGH.',
            'values' => [],
            'unit' => 'mm',
            'min' => 0,
            'max' => 100,
            'step' => 1,
            'visible' => true,
        ],
        20 => [
            'typ' => 2,
            'title' => 'Heater',
            'log' => 'HEATER',
            'values' => [0 => 'OFF', 1 => 'ON'],
            'unit' => '',
            'min' => 0,
            'max' => 1,
            'step' => 1,
            'visible' => true,
        ],
    ],
];