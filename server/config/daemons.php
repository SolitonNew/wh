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
        \App\Library\Daemons\ExtApiDaemon::class,
        \App\Library\Daemons\DinDaemon::class,
        \App\Library\Daemons\PyhomeDaemon::class,
        \App\Library\Daemons\ZigbeeoneDaemon::class,
        \App\Library\Daemons\ServerDaemon::class,
        \App\Library\Daemons\ScheduleDaemon::class,
        \App\Library\Daemons\CommandDaemon::class,
        \App\Library\Daemons\ObserverDaemon::class,
        \App\Library\Daemons\CamcorderDaemon::class,
    ],
];
