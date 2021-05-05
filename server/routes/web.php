<?php

Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('/login', 'Auth\LoginController@login')->name('loginPost');
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');

Route::group(['middleware'=>'role:terminal'], function () {
    /* Отображение сгруппированых данных по комнатам  ----------------------- */
    Route::get('/', 'Terminal\RoomsController@index')->name('home');
    Route::get('/room/{roomID}', 'Terminal\RoomController@index')->name('terminal.room');
    Route::get('/device/{deviceID}', 'Terminal\DeviceController@index')->name('terminal.device');

    
    /* Page "Favorites"  ---------------------------------------------------- */
    /* Index  */
    Route::get('/checked', 'Terminal\CheckedController@index')->name('terminal.checked');
    
    /* The page for selecting devices to display on the "Favorites" page */
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
    Route::get('/device-changes/{lastID}', 'Terminal\DeviceController@changes')->name('terminal.device-changes');
    
    
    /* Установка значения переменной  --------------------------------------- */
    Route::post('/device-set/{deviceID}/{value}', 'Terminal\DeviceController@set')->name('terminal.device-set');
});

Route::group(['prefix' => 'admin', 'middleware'=>'role:admin'], function () {
    /* Индексный контроллер  ------------------------------------------------ */
    Route::get('/', 'Admin\IndexController@index')->name('admin');
    Route::get('/variable-changes/{lastID}', 'Admin\IndexController@variableChanges')->name('admin.variable-changes');

    
    /* Конфигурация  -------------------------------------------------------- */
    /* Управление хабами */
    Route::get('/hubs/{hubID?}', 'Admin\HubsController@index')->name('admin.hubs');
    Route::match(['get', 'post'], '/hub-edit/{id}', 'Admin\HubsController@edit')->name('admin.hub-edit');
    Route::delete('/hub-delete/{id}', 'Admin\HubsController@delete')->name('admin.hub-delete');
    Route::get('/hubs-scan', 'Admin\HubsController@hubsScan')->name('admin.hubs-scan');
    Route::get('/hubs-firmware', 'Admin\HubsController@firmware')->name('admin.firmware');
    Route::get('/hubs-firmware-start', 'Admin\HubsController@firmwareStart')->name('admin.firmware-start');
    Route::get('/hubs-firmware-status', 'Admin\HubsController@firmwareStatus')->name('admin.firmware-status');
    Route::get('/hubs-reset', 'Admin\HubsController@hubsReset')->name('admin.hubs-reset');
    
    /* Управление устройствами */
    Route::get('/hubs/{hubID}/devices/{groupID?}', 'Admin\Hubs\DevicesController@index')->name('admin.hub-devices');
    Route::match(['get', 'post'], '/hub-device-edit/{hubID}/{id}', 'Admin\Hubs\DevicesController@edit')->name('admin.hub-device-edit');
    Route::delete('/hub-device-delete/{id}', 'Admin\Hubs\DevicesController@delete')->name('admin.hub-device-delete');
    Route::get('/hub-device-host-list/{hubID}', 'Admin\Hubs\DevicesController@hostList')->name('admin.hub-device-host-list');
    Route::get('/hub-device-host-channel-list/{typ}/{hostID?}', 'Admin\Hubs\DevicesController@hostChannelList')->name('admin.hub-device-host-channel-list');
    
    /* Управление хостами */
    Route::get('/hubs/{hubID}/hosts', 'Admin\Hubs\HostsController@index')->name('admin.hub-hosts');
    Route::match(['get', 'post'], '/hub-host-edit/{hubID}/{id}', 'Admin\Hubs\HostsController@edit')->name('admin.hub-host-edit');
    Route::delete('/hub-host-delete/{id}', 'Admin\Hubs\HostsController@delete')->name('admin.hub-host-delete');
    
    
    /* Раздел "Планирование" помещений  ------------------------------------- */
    Route::get('/plan/{id?}', 'Admin\PlanController@index')->name('admin.plan');
    Route::match(['get', 'post'], '/plan-edit/{id}/{p_id?}', 'Admin\PlanController@edit')->name('admin.plan-edit');
    Route::delete('/plan-delete/{id}', 'Admin\PlanController@delete')->name('admin.plan-delete');
    Route::get('/plan-clone/{id}/{direction}', 'Admin\PlanController@planClone')->name('admin.plan-clone');
    Route::match(['get', 'post'], '/plan-move-childs/{id}', 'Admin\PlanController@moveChilds')->name('admin.plan-move-childs');
    Route::match(['get', 'post'], '/plan-order/{id}', 'Admin\PlanController@order')->name('admin.plan-order');
    Route::post('/plan-move/{id}/{newX}/{newY}', 'Admin\PlanController@move')->name('admin.plan-move');
    Route::post('/plan-size/{id}/{newW}/{newH}', 'Admin\PlanController@size')->name('admin.plan-size');
    Route::match(['get', 'post'], '/plan-import', 'Admin\PlanController@planImport')->name('admin.plan-import');
    Route::get('/plan-export', 'Admin\PlanController@planExport')->name('admin.plan-export');
    Route::match(['get', 'post'], '/plan-link-device/{planID}/{deviceID?}', 'Admin\PlanController@linkDevice')->name('admin.plan-link-device');
    Route::delete('/plan-unlink-device/{deviceID}', 'Admin\PlanController@unlinkDevice')->name('admin.plan-unlink-device');
    Route::match(['get', 'post'], '/plan-port-edit/{planID}/{portIndex?}', 'Admin\PlanController@portEdit')->name('admin.plan-port-edit');
    Route::delete('/plan-port-delete/{planID}/{portIndex}', 'Admin\PlanController@portDelete')->name('admin.plan-port-delete');
       
    
    /* Управление скриптами (сценариями) системы  --------------------------- */
    Route::get('/scripts/{scriptID?}', 'Admin\ScriptsController@index')->name('admin.scripts');
    Route::match(['get', 'post'], '/script-edit/{id}', 'Admin\ScriptsController@edit')->name('admin.script-edit');
    Route::delete('/script-delete/{id}', 'Admin\ScriptsController@delete')->name('admin.script-delete');
    Route::match(['get', 'post'], '/script-events/{id}', 'Admin\ScriptsController@attacheEvents')->name('admin.script-events');
    Route::post('/script-save/{id}', 'Admin\ScriptsController@saveScript')->name('admin.script-save');
    Route::post('/script-test', 'Admin\ScriptsController@scriptTest')->name('admin.script-test');
    
    
    /* Управление учетными записями пользователей  -------------------------- */
    Route::get('/users', 'Admin\UsersController@index')->name('admin.users');
    Route::match(['get', 'post'], '/user-edit/{id}', 'Admin\UsersController@edit')->name('admin.user-edit');
    Route::delete('/user-delete/{id}', 'Admin\UsersController@delete')->name('admin.user-delete');
    
    
    /* Настройка событий системы по расписанию  ----------------------------- */
    Route::get('/schedule', 'Admin\ScheduleController@index')->name('admin.schedule');
    Route::match(['get', 'post'], '/schedule-edit/{id}', 'Admin\ScheduleController@edit')->name('admin.schedule-edit');
    Route::delete('/schedule-delete/{id}', 'Admin\ScheduleController@delete')->name('admin.schedule-delete');
    
    
    /* Управление камерами видеонаблюдения  --------------------------------- */
    Route::get('/cams', 'Admin\CamsController@index')->name('admin.cams');
    //Route::match(['get', 'post'], '/cam-edit/{id}', 'Admin\CamsController@edit')->name('admin.cam-edit');
    Route::get('/cam-edit/{id}', 'Admin\CamsController@editShow')->name('admin.cam-edit');
    Route::post('/cam-edit/{id}', 'Admin\CamsController@editPost')->name('admin.cam-edit');
    Route::delete('/cam-delete/{id}', 'Admin\CamsController@delete')->name('admin.cam-delete');
    
    
    /* Журнал системы  ------------------------------------------------------ */
    /* Управление разделом "Журнал" */
    Route::get('/jurnal', 'Admin\JurnalController@index')->name('admin.jurnal');
    
    /* История устройств */
    Route::match(['get', 'post'], '/jurnal/history/{id?}', 'Admin\Jurnal\HistoryController@index')->name('admin.jurnal-history');
    Route::get('/jurnal/history-value-view/{id}', 'Admin\Jurnal\HistoryController@valueView')->name('admin.jurnal-history-value-view');
    Route::delete('/jurnal/history-value-delete/{id}', 'Admin\Jurnal\HistoryController@valueDelete')->name('admin.jurnal-history-value-delete');
    Route::delete('/jurnal/history-delete-all-visible/{id}', 'Admin\Jurnal\HistoryController@deleteAllVisibleValues')->name('admin.jurnal-history-delete-all-visible');
    
    /* Фоновые процессы */
    Route::get('/jurnal/demons/{id?}', 'Admin\Jurnal\DemonsController@index')->name('admin.jurnal-demons');
    Route::get('/jurnal/demon-data/{id}/{lastID?}', 'Admin\Jurnal\DemonsController@data')->name('admin.jurnal-demon-data');
    Route::get('/jurnal/demon-start/{id}', 'Admin\Jurnal\DemonsController@demonStart')->name('admin.jurnal-demon-start');
    Route::get('/jurnal/demon-stop/{id}', 'Admin\Jurnal\DemonsController@demonStop')->name('admin.jurnal-demon-stop');
    Route::get('/jurnal/demon-restart/{id}', 'Admin\Jurnal\DemonsController@demonRestart')->name('admin.jurnal-demon-restart');
    
    /* Статистика потребления */    
    Route::get('/jurnal/power', 'Admin\Jurnal\PowerController@index')->name('admin.jurnal-power');
    
    
    /* Terminal settings  --------------------------------------------------- */
    Route::get('/terminal', 'Admin\TerminalController@index')->name('admin.terminal');
    Route::post('/terminal-set-max-level/{value}', 'Admin\TerminalController@setMaxLevel')->name('admin.terminal-set-max-level');
});
