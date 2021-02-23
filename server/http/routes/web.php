<?php

Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('/login', 'Auth\LoginController@login')->name('loginPost');
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');

Route::group(['middleware'=>'role:rerminal'], function () {
    /* Отображение сгруппированых данных по комнатам */
    Route::get('/', 'Terminal\RoomsController@index')->name('home');
    Route::get('/room/{roomID}', 'Terminal\RoomController@index')->name('room');
    Route::get('/variable/{variableID}', 'Terminal\VariableController@index')->name('variable');

    /* Страница "Избранное" */
    Route::get('/checked', 'Terminal\CheckedController@index')->name('checked');
    
    /* Страница выбора переменных для отображения на страницах "Избранное" */
    Route::get('/checked/edit/add/{selKey?}', 'Terminal\CheckedController@editAdd')->name('checked-edit-add');
    Route::get('/checked/edit/add-add/{id}', 'Terminal\CheckedController@editAdd_ADD')->name('checked-edit-add-add');
    Route::get('/checked/edit/add-del/{id}', 'Terminal\CheckedController@editAdd_DEL')->name('checked-edit-add-del');

    /* Страница настройки порядка отображения переменных на странице "Избранное" */
    Route::get('/checked/edit/order', 'Terminal\CheckedController@editOrder')->name('checked-edit-order');
    Route::get('/checked/edit/order-up/{id}', 'Terminal\CheckedController@editOrder_UP')->name('checked-edit-order-up');
    Route::get('/checked/edit/order-down/{id}', 'Terminal\CheckedController@editOrder_DOWN')->name('checked-edit-order-down');

    /* Страница настройки цвета переменных по текстовой маске */
    Route::get('/checked/edit/color', 'Terminal\CheckedController@editColor')->name('checked-edit-color');
    Route::post('/checked/edit/color-action/{action}', 'Terminal\CheckedController@editColor_ACTION')->name('checked-edit-color-action');

    /* Запрос изменений переменных */
    Route::get('/variable-changes/{lastID}', 'Terminal\VariableController@variableChanges')->name('variable-changes');
    
    /* Установка значения переменной */
    Route::post('/variable-set/{varID}/{varValue}', 'Terminal\VariableController@variableSet')->name('variable-set');
});

Route::group(['prefix' => 'admin', 'middleware'=>'role:admin'], function () {
    /* Индексный контроллер */
    Route::get('/', 'Admin\IndexController@index');
    
    /* Раздел "Планирование" помещений */
    Route::get('/plan/{id?}', 'Admin\PlanController@index')->name('plan');
    Route::match(['get', 'post'], '/plan-edit/{id}/{p_id?}', 'Admin\PlanController@edit')->name('plan-edit');
    Route::get('/plan-delete/{id}', 'Admin\PlanController@delete')->name('plan-delete');
    Route::match(['get', 'post'], '/plan-move-childs/{id}', 'Admin\PlanController@moveChilds')->name('plan-move-childs');
    Route::match(['get', 'post'], '/plan-order/{id}', 'Admin\PlanController@order')->name('plan-order');
    
    /* Раздел управления переменными системы */
    Route::get('/variables/{partID?}', 'Admin\VariablesController@index')->name('variables');
    Route::get('/variables-ow-list/{controller}', 'Admin\VariablesController@owList')->name('variables-ow-list');
    Route::get('/variables-channel-list/{rom}/{ow_id?}', 'Admin\VariablesController@channelList')->name('variables-channel-list');
    Route::match(['get', 'post'], '/variable-edit/{id}', 'Admin\VariablesController@edit')->name('variable-edit');
    Route::get('/variable-delete/{id}', 'Admin\VariablesController@delete')->name('variable-delete');
    
    /* Запрос изменений переменных */
    Route::get('/variable-changes/{lastID}', 'Admin\VariablesController@variableChanges')->name('variable-changes');
    
    /* Управление скриптами (сценариями) системы */
    Route::get('/scripts/{scriptID?}', 'Admin\ScriptsController@index')->name('scripts');
    Route::match(['get', 'post'], '/script-edit/{id}', 'Admin\ScriptsController@edit')->name('script-edit');
    Route::get('/script-delete/{id}', 'Admin\ScriptsController@delete')->name('script-delete');
    Route::match(['get', 'post'], '/script-events/{id}', 'Admin\ScriptsController@attacheEvents')->name('script-events');
    Route::post('/script-save/{id}', 'Admin\ScriptsController@saveScript')->name('script-save');
    
    /* Управление учетными записями пользователей */
    Route::get('/users', 'Admin\UsersController@index')->name('users');
    Route::match(['get', 'post'], '/user-edit/{id}', 'Admin\UsersController@edit')->name('user-edit');
    Route::get('/user-delete/{id}', 'Admin\USersController@delete')->name('user-delete');
    
    /* Управление сетью OW устройств коньроллеров */
    Route::get('/ow-manager/{controllerID?}', 'Admin\OwManagerController@index')->name('ow-manager');
    Route::get('/ow-manager-info/{id}', 'Admin\OwManagerController@info')->name('ow-manager-info');
    Route::get('/ow-manager-delete/{id}', 'Admin\OwManagerController@delete')->name('ow-manager-delete');
    Route::get('/ow-manager-gen-vars', 'Admin\OwManagerController@generateVarsForFreeDevs')->name('ow-manager-gen-vars');
    
    /* Настройка событий системы по расписанию */
    Route::get('/schedule', 'Admin\ScheduleController@index')->name('schedule');
    Route::match(['get', 'post'], '/schedule-edit/{id}', 'Admin\ScheduleController@edit')->name('schedule-edit');
    Route::get('/schedule-delete/{id}', 'Admin\ScheduleController@delete')->name('schedule-delete');
    
    /* Управление камерами видеонаблюдения */
    Route::get('/cams', 'Admin\CamsController@index')->name('cams');
    Route::match(['get', 'post'], '/cam-edit/{id}', 'Admin\CamsController@edit')->name('cam-edit');
    Route::get('/cam-delete/{id}', 'Admin\CamsController@delete')->name('cam-delete');
    
    /* Управление фоновыми процессами */
    Route::get('/demons/{id?}', 'Admin\DemonsController@index')->name('demons');
    
    /* Визуализация статистики системы */
    Route::get('/statistics', 'Admin\StatisticsController@index')->name('statistics');
});
