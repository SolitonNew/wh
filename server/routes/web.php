<?php

Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('/login', 'Auth\LoginController@login')->name('loginPost');
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');

Route::group(['middleware'=>'role:terminal'], function () {
    /* Отображение сгруппированых данных по комнатам  ----------------------- */
    Route::get('/', 'Terminal\RoomsController@index')->name('home');
    Route::get('/room/{roomID}', 'Terminal\RoomController@index')->name('terminal.room');
    Route::get('/variable/{variableID}', 'Terminal\VariableController@index')->name('terminal.variable');

    
    /* Страница "Избранное"  ------------------------------------------------ */
    Route::get('/checked', 'Terminal\CheckedController@index')->name('terminal.checked');
    
    
    /* Страница выбора переменных для отображения на страницах "Избранное"  - */
    Route::get('/checked/edit/add/{selKey?}', 'Terminal\CheckedController@editAdd')->name('terminal.checked-edit-add');
    Route::get('/checked/edit/add-add/{id}', 'Terminal\CheckedController@editAdd_ADD')->name('terminal.checked-edit-add-add');
    Route::get('/checked/edit/add-del/{id}', 'Terminal\CheckedController@editAdd_DEL')->name('terminal.checked-edit-add-del');

    
    /* Страница настройки порядка отображения переменных на странице "Избранное" */
    Route::get('/checked/edit/order', 'Terminal\CheckedController@editOrder')->name('terminal.checked-edit-order');
    Route::get('/checked/edit/order-up/{id}', 'Terminal\CheckedController@editOrder_UP')->name('terminal.checked-edit-order-up');
    Route::get('/checked/edit/order-down/{id}', 'Terminal\CheckedController@editOrder_DOWN')->name('terminal.checked-edit-order-down');

    
    /* Страница настройки цвета переменных по текстовой маске  -------------- */
    Route::get('/checked/edit/color', 'Terminal\CheckedController@editColor')->name('terminal.checked-edit-color');
    Route::post('/checked/edit/color-action/{action}', 'Terminal\CheckedController@editColor_ACTION')->name('terminal.checked-edit-color-action');

    
    /* Запрос изменений переменных  ----------------------------------------- */
    Route::get('/variable-changes/{lastID}', 'Terminal\VariableController@variableChanges')->name('terminal.variable-changes');
    
    
    /* Установка значения переменной  --------------------------------------- */
    Route::post('/variable-set/{varID}/{varValue}', 'Terminal\VariableController@variableSet')->name('terminal.variable-set');
});

Route::group(['prefix' => 'admin', 'middleware'=>'role:admin'], function () {
    /* Индексный контроллер  ------------------------------------------------ */
    Route::get('/', 'Admin\IndexController@index')->name('admin');
    Route::get('/variable-changes/{lastID}', 'Admin\IndexController@variableChanges')->name('variable-changes');

    
    /* Конфигурация  -------------------------------------------------------- */
    /* Управление хабами */
    Route::get('/hubs/{hubID?}', 'Admin\HubsController@index')->name('admin.hubs');
    Route::match(['get', 'post'], '/hub-edit/{id}', 'Admin\HubsController@edit')->name('admin.hub-edit');
    Route::delete('/hub-delete/{id}', 'Admin\HubsController@delete')->name('admin.hub-delete');
    
    /* Управление устройствами */
    Route::get('/hubs/{hubID}/devices', 'Admin\Hubs\DevicesController@index')->name('admin.hub-devices');
    Route::match(['get'], '/hub-device-edit/{id}', 'Admin\Hubs\DevicesController@edit')->name('admin.hub-device-edit');
    Route::delete('/hub-device-delete/{id}', 'Admin\Hubs\DeviceController@delete')->name('admin.hub-device-delete');
    
    /* Управление хостами */
    Route::get('/hubs/{hubID}/hosts', 'Admin\Hubs\HostsController@index')->name('admin.hub-hosts');
    Route::match(['get', 'post'], '/hub-host-edit/{id}', 'Admin\Hubs\HostsController@edit')->name('admin.hub-host-edit');
    Route::delete('/hub-host-delete/{id}', 'Admin\Hubs\HostsController@delete')->name('admin.hub-host-delete');
    
    
    /* Раздел "Планирование" помещений  ------------------------------------- */
    Route::get('/plan/{id?}', 'Admin\PlanController@index')->name('plan');
    Route::match(['get', 'post'], '/plan-edit/{id}/{p_id?}', 'Admin\PlanController@edit')->name('plan-edit');
    Route::get('/plan-delete/{id}', 'Admin\PlanController@delete')->name('plan-delete');
    Route::match(['get', 'post'], '/plan-move-childs/{id}', 'Admin\PlanController@moveChilds')->name('plan-move-childs');
    Route::match(['get', 'post'], '/plan-order/{id}', 'Admin\PlanController@order')->name('plan-order');
    
    
    /* Раздел управления переменными системы  ------------------------------- */
    Route::get('/variables/{partID?}', 'Admin\VariablesController@index')->name('variables');
    Route::get('/variables-ow-list/{controller?}', 'Admin\VariablesController@owList')->name('variables-ow-list');
    Route::get('/variables-channel-list/{rom}/{ow_id?}', 'Admin\VariablesController@channelList')->name('variables-channel-list');
    Route::match(['get', 'post'], '/variable-edit/{id}', 'Admin\VariablesController@edit')->name('variable-edit');
    Route::get('/variable-delete/{id}', 'Admin\VariablesController@delete')->name('variable-delete');
    
    
    /* Управление скриптами (сценариями) системы  --------------------------- */
    Route::get('/scripts/{scriptID?}', 'Admin\ScriptsController@index')->name('scripts');
    Route::match(['get', 'post'], '/script-edit/{id}', 'Admin\ScriptsController@edit')->name('script-edit');
    Route::get('/script-delete/{id}', 'Admin\ScriptsController@delete')->name('script-delete');
    Route::match(['get', 'post'], '/script-events/{id}', 'Admin\ScriptsController@attacheEvents')->name('script-events');
    Route::post('/script-save/{id}', 'Admin\ScriptsController@saveScript')->name('script-save');
    Route::post('/script-test', 'Admin\ScriptsController@scriptTest')->name('script-test');
    
    
    /* Управление учетными записями пользователей  -------------------------- */
    Route::get('/users', 'Admin\UsersController@index')->name('users');
    Route::match(['get', 'post'], '/user-edit/{id}', 'Admin\UsersController@edit')->name('user-edit');
    Route::get('/user-delete/{id}', 'Admin\UsersController@delete')->name('user-delete');
    
    
    /* Управление конфигурацией системы  ------------------------------------ */
    Route::get('/configuration/{id?}', 'Admin\ConfigurationController@index')->name('configuration');
    Route::match(['get', 'post'], '/configuration-edit/{id}', 'Admin\ConfigurationController@edit')->name('configuration-edit');
    Route::get('/configuration-delete/{id}', 'Admin\ConfigurationController@delete')->name('configuration-delete');
    Route::get('/configuration-ow-info/{id}', 'Admin\ConfigurationController@owInfo')->name('configuration-ow-info');
    Route::get('/configuration-ow-delete/{id}', 'Admin\ConfigurationController@owDelete')->name('configuration-ow-delete');
    Route::get('/configuration-gen-vars', 'Admin\ConfigurationController@generateVarsForFreeDevs')->name('configuration-gen-vars');
    Route::get('/configuration-firmware/{id?}', 'Admin\ConfigurationController@configurationFirmware')->name('configuration-firmware');
    Route::get('/configuration-firmware-start', 'Admin\ConfigurationController@configurationFirmwareStart')->name('configuration-firmware-start');
    Route::get('/configuration-firmware-status', 'Admin\ConfigurationController@configurationFirmwareStatus')->name('configuration-firmware-status');
    Route::get('/configuration-reset', 'Admin\ConfigurationController@resetControllers')->name('configuration-reset');
    Route::get('/configuration-ow-scan', 'Admin\ConfigurationController@runOwScan')->name('configuration-ow-scan');
    
    
    /* Настройка событий системы по расписанию  ----------------------------- */
    Route::get('/schedule', 'Admin\ScheduleController@index')->name('schedule');
    Route::match(['get', 'post'], '/schedule-edit/{id}', 'Admin\ScheduleController@edit')->name('schedule-edit');
    Route::get('/schedule-delete/{id}', 'Admin\ScheduleController@delete')->name('schedule-delete');
    
    
    /* Управление камерами видеонаблюдения  --------------------------------- */
    Route::get('/cams', 'Admin\CamsController@index')->name('cams');
    Route::match(['get', 'post'], '/cam-edit/{id}', 'Admin\CamsController@edit')->name('cam-edit');
    Route::get('/cam-delete/{id}', 'Admin\CamsController@delete')->name('cam-delete');
    
    
    /* Управление фоновыми процессами  -------------------------------------- */
    Route::get('/demons/{id?}', 'Admin\DemonsController@index')->name('demons');
    Route::get('/demon-data/{id}/{lastID?}', 'Admin\DemonsController@data')->name('demon-data');
    Route::get('/demon-start/{id}', 'Admin\DemonsController@demonStart')->name('demon-start');
    Route::get('/demon-stop/{id}', 'Admin\DemonsController@demonStop')->name('demon-stop');
    Route::get('/demon-restart/{id}', 'Admin\DemonsController@demonRestart')->name('demon-restart');
    
    
    /* Визуализация статистики системы  ------------------------------------- */
    Route::match(['get', 'post'], '/statistics/table/{id?}', 'Admin\Statistics\TableController@index')->name('statistics-table');
    Route::get('/statistics/table-value-view/{id}', 'Admin\Statistics\TableController@valueView')->name('statistics-table-value-view');
    Route::get('/statistics/table-value-delete/{id}', 'Admin\Statistics\TableController@valueDelete')->name('statistics-table-value-delete');
    Route::get('/statistics/table-delete-all-visible/{id}', 'Admin\Statistics\TableController@deleteAllVisibleValues')->name('statistics-table-delete-all-visible');
    
    Route::get('/statistics/chart', 'Admin\Statistics\ChartController@index')->name('statistics-chart');
    Route::get('/statistics/power', 'Admin\Statistics\PowerController@index')->name('statistics-power');
    
    
    
    Route::get('/test', 'Admin\TestController@index')->name('test');
});
