<?php

return [
    'menu' => 'Процессы',
    'title' => '',
    
    'demon_run' => 'Запустить текущий процесс',
    'demon_stop' => 'Остановить текущий процесс',
    'demon_reload' => 'Перезапустить тукущий процесс',
    'demon_btn_top' => 'Вверх',
    
    'demon_run_confirm' => 'Запустить текущий процесс?',
    'demon_stop_confirm' => 'Остановить текущий процесс?',
    'demon_reload_confirm' => 'Перезапустить тукущий процесс?',
    
    'rs485-demon' => 'Связь с щитовыми контроллерами',
    'rs485-demon-title' => '-- МОДУЛЬ ВЗАИМОДЕЙСТВИЯ ПО ШИНЕ RS485',
    
    'schedule-demon' => 'Контроль расписания',
    'schedule-demon-title' => '-- МОДУЛЬ ОБРАБОТКИ РАСПИСАНИЯ',
    'schedule-demon-disabled' => '[НЕ ВЫПОЛНЯТЬ!!!]',
    'schedule-demon-line' => '[:datetime] Произошло событие ":comm" и запрошена команда ":action"',
    
    'command-demon' => 'Командный процессор',
    'command-demon-title' => '-- КОМАНДНЫЙ ПРОЦЕССОР',
    'command-demon-line' => '[:datetime] Выполнена команда ":command"',
    'command-demon-hours' => [
        1 => 'Один час ночи',
        2 => 'Два час*а ночи',
        3 => 'Три час*а ночи',
        4 => 'Четыре час*а ночи',
        5 => 'Пять часов утр*а',
        6 => 'Шесть часов утр*а',
        7 => 'Семь часов утр*а',
        8 => 'Восемь часов утр*а',
        9 => 'Девять часов утр*а',
        10 => 'Десять часов утр*а',
        11 => 'Одинадцать часов утр*а',
        12 => 'Двенадцать часов дня',
        13 => 'Один час дня',
        14 => 'Два час*а дня',
        15 => 'Три час*а дня',
        16 => 'Четыре час*а дня',
        17 => 'Пять часов в*ечера',
        18 => 'Шесть часов в*ечера',
        19 => 'Семь часов в*ечера',
        20 => 'Восемь часов в*ечера',
        21 => 'Девять часов в*ечера',
        22 => 'Десять часов в*ечера',
        23 => 'Одинадцать часов ночи',
        24 => 'Двенадцать часов ночи',
    ],
    'command-demon-minutes-2' => [
        0 => '',
        1 => 'одна',
        2 => 'две',
        3 => 'три',
        4 => 'четыре',
        5 => 'пять',
        6 => 'шесть',
        7 => 'семь',
        8 => 'восемь',
        9 => 'девять',
    ],
    'command-demon-minutes-1' => [
        0 => '',
        1 => '',
        2 => 'двадцать',
        3 => 'тридцать',
        4 => 'сорок',
        5 => 'пятдесят',
        6 => 'шестьдесят',
    ],
    'command-demon-minutes' => [
        0 => 'минут',
        1 => 'минута',
        2 => 'минуты',
        3 => 'минуты',
        4 => 'минуты',
        5 => 'минут',
        6 => 'минут',
        7 => 'минут',
        8 => 'минут',
        9 => 'минут',
    ],
    'command-demon-temps' => [
        0 => 'градусов',
        1 => 'градус',
        2 => 'градуса', 
        3 => 'градуса',
        4 => 'градуса',
        5 => 'градусов',
        6 => 'градусов',
        7 => 'градусов',
        8 => 'градусов',
        9 => 'градусов',
    ],
    'command-demon-info-temp' =>  'Температура на улице :temp',
    'command-demon-info-temp-znak' => [
        0 => 'мороза',
        1 => 'тепла',
    ],    
    
];