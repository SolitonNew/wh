<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

return [
    'menu' => 'Конфигурация',
    'title' => '',
    
    'main_prompt' => 'Для начала работы вам нужно добавить хаб',
    
    'hub_add' => 'Добавить хаб...',
    'hub_edit' => 'Свойства хаба...',
    'hubs_scan' => 'Просканировать сеть хабов...',
    'hubs_scan_title' => 'Результат сканирования сети хабов',
    'firmware' => 'Обновить хабы...',
    'hubs_reset' => 'Перезагрузить хабы',
    'soft_host_add' => 'Добавить програмный хост...',
    
    'hub_add_title' => 'Добавить новый хаб',
    'hub_edit_title' => 'Свойства хаба',
    'hub_delete_confirm' => 'Удалить текущий хаб?',
    'hub_ID' => 'ID',
    'hub_NAME' => 'Название',
    'hub_TYP' => 'Тип',
    'hub_ROM' => 'ROM',
    'hub_COMM' => 'Примечания',
    
    'firmware_title' => 'Применить конфигурацию хабов',
    'firmware_make_title' => 'Отчет по сборке',
    'firmware_start' => 'Начать обновление',
    'firmware_start_progress' => 'Выполняется обновление',
    'firmware_complete' => 'Обновление успешно выполнено',
    'firmware_notpossible' => 'Обновление невозможно',
    
    'devices' => 'Устройства',
    'device_ID' => 'ID',
    'device_CONTROLLER' => 'Hub',
    'device_TYP' => 'Тип host',
    'device_OW' => 'Host',
    'device_NAME' => 'Идентификатор',
    'device_COMM' => 'Описание',
    'device_GROUP' => 'Комната',
    'device_APP_CONTROL' => 'Тип устройства',
    'device_VALUE' => 'Значение',
    'device_CHANNEL' => 'Host канал',
    
    'device_filter' => 'Фильтр',
    'device_filter_null' => 'Без фильтра',
    'device_group_empty' => 'Не в комнате',
    
    'device_add' => 'Добавить новое устройство...',
    'device_add_title' => 'Новое устройство',
    'device_edit_title' => 'Свойства устройства',
    'device_delete_confirm' => 'Удалить выбранное устройство?',
    
    
    'hosts' => 'Hosts',
    'host_ID' => 'ID',
    'host_CONTROLLER' => 'Контроллер',
    'host_ROM' => 'Номер',
    'host_TYP' => 'Тип',
    'host_CHANNELS' => 'Каналы',
    'host_DEVICES' => 'Устройства',
    
    'host_add' => 'Добавить новый хост...',
    'host_add_title' => 'Новый хост',
    'host_edit_title' => 'Свойства хоста',
    'host_delete_confirm' => 'Удалить выбранный хост?',
    
    
    'app_control' => [
        0 => '-//-',
        1 => 'Свет',
        2 => 'Выключатель',
        3 => 'Розетка',
        4 => 'Термометр',
        5 => 'Термостат',
        6 => 'Камера',
        7 => 'Вентиляция',
        8 => 'Датчик движения',
        9 => 'Датчик затопления',
        10 => 'Гигрометр',
        11 => 'Датчик газа',
        12 => 'Датчик двери',
        13 => 'Атмосферное давление',
        14 => 'Датчик тока',
    ],
    
    'log_app_control' => [
        0 => '-//-',
        1 => 'СВЕТ',
        2 => 'СЕНС.',
        3 => 'РОЗ.',
        4 => 'ТЕМП.',
        5 => 'ТСТАТ',
        6 => 'КАМЕРА',
        7 => 'ВЕНТ.',
        8 => 'ДВИЖ.',
        9 => 'ЗАТОПЛ.',
        10 => 'ВЛАЖН.',
        11 => 'СО',
        12 => 'ДВЕРЬ',
        13 => 'АТМ.',
        14 => 'ТОК',        
    ],
    
    'log_app_control_dim' => [
        -1 => '',
        0 => '',
        1 => [0 => 'ВЫКЛ.', 1 => 'ВКЛ.'],
        2 => [0 => 'ОТЖ.', 1 => 'НАЖ.'],
        3 => [0 => 'ВЫКЛ.', 1 => 'ВКЛ.'],
        4 => '°C',
        5 => '°C',
        6 => 'КАМЕРА',
        7 => '%',
        8 => [0 => 'НОРМ.', 1 => 'ДВИЖ.'],
        9 => [0 => 'НОРМ.', 1 => 'ТРЕВОГА'],
        10 => '%',
        11 => 'ppm',
        12 => [0 => 'ОТКР.', 1 => 'ЗАКР.'],
        13 => 'mm',
        14 => 'A',        
    ],
];