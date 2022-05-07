<?php

return [
    'channels' => [   // CHANNEL => GPIO
        //'PA0' => 0,
        //'PA1' => 1,
        //'PA2' => 2,
        //'PA3' => 3,
        'PA6' => 6,
        'PA7' => 7,
        'PA8' => 8,
        'PA9' => 9,
        'PA10' => 10,
        //'PA11' => 11, // i2C  TWI0
        //'PA12' => 12, // i2C  TWI0
        'PA13' => 13,
        'PA14' => 14,
        'PA18' => 18, // i2C  TWI1
        'PA19' => 19, // i2C  TWI1
        'PA20' => 20,
        'PA21' => 21,
        'PC4' => 68,
        'PC7' => 71,
        'PD14' => 110,
        'PG6' => 198,
        'PG7' => 199,
        'PG8' => 200,
        'PG9' => 201,
        'PROC_TEMP' => -1,
    ],
    'drivers' => [
        'bmp280' => [
            'cron' => '* * * * *',
            'class' => App\Library\OrangePi\I2c\Bmp280::class,
        ],
    ]
];