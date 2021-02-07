<?php

$MAIN_MENUS = [
    'main' => 'КОМНАТЫ',
    'checked' => 'ИЗБРАННОЕ',
    'checked_edit' => 'НАСТРОЙКИ',
    'back' => 'НАЗАД'
];

$CONTOL_LABELS = [
    1 => 'СВЕТ',
    2 => '',
    3 => 'РОЗЕТКА',
    4 => 'ТЕРМОМЕТР',
    5 => 'ТЕРМОСТАТ',
    6 => '',
    7 => 'ВЕНТИЛЯЦИЯ',
    8 => '',
    9 => '',
    10 => 'ВЛАЖНОСТЬ',
    11 => 'СО',
    12 => '',
    13 => 'Атм. давление',
    14 => 'ТОК',
];

$CHART_UPDATE_INTERVAL = 60 * 1000; // Время обновления графиков

function checkHttpPath($file) {
    print_r($_SERVER);
}

function decodeAppControl($app_control) {
    $control = '';
    $typ = -1; // 1-label; 2-switch; 3-track;
    $resolution = '';
    $varMin = 0;
    $varMax = 10;
    $varStep = 1;    
    switch ($app_control) {
        case 1: // Лампочка
            $control = 'СВЕТ';
            $typ = 2;
            break;
        case 3: // Розетка
            $control = '';
            $typ = 2;
            break;
        case 4: // Термометр
            $control = 'ТЕРМОМЕТР';
            $typ = 1;
            $resolution = '°C';
            break;
        case 5: // Термостат
            $control = 'ТЕРМОСТАТ';
            $typ = 3;
            $resolution = '°C';
            $varMin = 15;
            $varMax = 30;
            $varStep = 1;
            break;
        case 7: //Вентилятор
            $control = 'ВЕНТИЛЯЦИЯ';
            $typ = 3;
            $resolution = '%';
            $varMin = 0;
            $varMax = 100;
            $varStep = 10;
            break;
        case 10: //Гигрометр
            $control = 'ВЛАЖНОСТЬ';
            $typ = 1;
            $resolution = '%';
            break;
        case 11: // Датчик газа
            $control = 'СО';
            $typ = 1;
            $resolution = 'ppm';
            break;
        case 13: // Атм. давление
            $control = '';
            $typ = 1;
            $resolution = 'mm';
            break;
        case 14: // Датчик тока
            $control = 'ТОК';
            $typ = 1;
            $resolution = 'A';
            break;
    }
    
    return [
        'label' => $control,
        'typ' => $typ,
        'resolution' => $resolution,
        'varMin' => $varMin,
        'varMax' => $varMax,
        'varStep' => $varStep
    ];
}

function groupVariableName($groupName, $variableName, $appControlLabel) {
    $resLabel = '';
    if ($appControlLabel != '') {
        $resLabel = $appControlLabel.' ';
    }    
    return $resLabel . mb_strtoupper(str_replace($groupName, '', $variableName));
}