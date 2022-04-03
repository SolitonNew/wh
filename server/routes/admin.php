<?php

/* Index controller ----------------------------------------------------- */
Route::get('/', 'IndexController@index')->name('admin');
Route::get('/variable-changes/{lastID}', 'IndexController@variableChanges')->name('admin.variable-changes');


/* Planning rooms  ------------------------------------------------------ */
Route::get('/plan/{id?}', 'PlanController@index')->name('admin.plan');
Route::get('/plan-edit/{id}/{p_id?}', 'PlanController@editShow')->name('admin.plan-edit');
Route::post('/plan-edit/{id}/{p_id?}', 'PlanController@editPost')->name('admin.plan-edit');
Route::delete('/plan-delete/{id}', 'PlanController@delete')->name('admin.plan-delete');
Route::get('/plan-clone/{id}/{direction}', 'PlanController@planClone')->name('admin.plan-clone');
Route::get('/plan-move-childs/{id}', 'PlanController@moveChildsShow')->name('admin.plan-move-childs');
Route::post('/plan-move-childs/{id}', 'PlanController@moveChildsPost')->name('admin.plan-move-childs');
Route::get('/plan-order/{id}', 'PlanController@orderShow')->name('admin.plan-order');
Route::post('/plan-order/{id}', 'PlanController@orderPost')->name('admin.plan-order');
Route::post('/plan-move/{id}/{newX}/{newY}', 'PlanController@move')->name('admin.plan-move');
Route::post('/plan-size/{id}/{newW}/{newH}', 'PlanController@size')->name('admin.plan-size');
Route::get('/plan-import', 'PlanController@planImportShow')->name('admin.plan-import');
Route::post('/plan-import', 'PlanController@planImportPost')->name('admin.plan-import');
Route::get('/plan-export', 'PlanController@planExport')->name('admin.plan-export');
Route::get('/plan-link-device/{planID}/{deviceID?}', 'PlanController@linkDeviceShow')->name('admin.plan-link-device');
Route::post('/plan-link-device/{planID}/{deviceID?}', 'PlanController@linkDevicePost')->name('admin.plan-link-device');
Route::delete('/plan-unlink-device/{deviceID}', 'PlanController@unlinkDevice')->name('admin.plan-unlink-device');
Route::get('/plan-port-edit/{planID}/{portIndex?}', 'PlanController@portEditShow')->name('admin.plan-port-edit');
Route::post('/plan-port-edit/{planID}/{portIndex?}', 'PlanController@portEditPost')->name('admin.plan-port-edit');
Route::delete('/plan-port-delete/{planID}/{portIndex}', 'PlanController@portDelete')->name('admin.plan-port-delete');


/* Configuration  ------------------------------------------------------- */
/* Hubs management routes */
Route::get('/hubs/{hubID?}', 'HubsController@index')->name('admin.hubs');
Route::get('/hub-edit/{id}', 'HubsController@editShow')->name('admin.hub-edit');
Route::post('/hub-edit/{id}', 'HubsController@editPost')->name('admin.hub-edit');
Route::delete('/hub-delete/{id}', 'HubsController@delete')->name('admin.hub-delete');
Route::get('/hubs-scan', 'HubsController@hubsScan')->name('admin.hubs-scan');
Route::get('/hubs-firmware', 'HubsController@firmware')->name('admin.firmware');
Route::get('/hubs-firmware-start', 'HubsController@firmwareStart')->name('admin.firmware-start');
Route::get('/hubs-firmware-status', 'HubsController@firmwareStatus')->name('admin.firmware-status');
Route::get('/hubs-reset', 'HubsController@hubsReset')->name('admin.hubs-reset');

/* Devices management routes */
Route::get('/hubs/{hubID}/devices/{groupID?}', 'Hubs\DevicesController@index')->name('admin.hub-devices');
Route::get('/hub-device-edit/{hubID}/{id}', 'Hubs\DevicesController@editShow')->name('admin.hub-device-edit');
Route::post('/hub-device-edit/{hubID}/{id}', 'Hubs\DevicesController@editPost')->name('admin.hub-device-edit');
Route::delete('/hub-device-delete/{id}', 'Hubs\DevicesController@delete')->name('admin.hub-device-delete');
Route::get('/hub-device-host-list/{hubID}', 'Hubs\DevicesController@hostList')->name('admin.hub-device-host-list');
Route::get('/hub-device-host-channel-list/{typ}/{hostID?}', 'Hubs\DevicesController@hostChannelList')->name('admin.hub-device-host-channel-list');

/* Hosts management routes */
Route::get('/hubs/{hubID}/hosts', 'Hubs\HostsController@index')->name('admin.hub-hosts');
Route::get('/hub-host-edit/{hubID}/{id}', 'Hubs\HostsController@editShow')->name('admin.hub-host-edit');
Route::post('/hub-host-edit/{hubID}/{id}', 'Hubs\HostsController@editPost')->name('admin.hub-host-edit');
Route::delete('/hub-host-delete/{hubID}/{id}', 'Hubs\HostsController@delete')->name('admin.hub-host-delete');


/* Scripts management routes  ------------------------------------------- */
Route::get('/scripts/{id?}', 'ScriptsController@index')->name('admin.scripts');
Route::get('/script-edit/{id}', 'ScriptsController@editShow')->name('admin.script-edit');
Route::post('/script-edit/{id}', 'ScriptsController@editPost')->name('admin.script-edit');
Route::delete('/script-delete/{id}', 'ScriptsController@delete')->name('admin.script-delete');
Route::get('/script-events/{id}', 'ScriptsController@attacheEventsShow')->name('admin.script-events');
Route::post('/script-events/{id}', 'ScriptsController@attacheEventsPost')->name('admin.script-events');
Route::post('/script-save/{id}', 'ScriptsController@saveScript')->name('admin.script-save');
Route::post('/script-test', 'ScriptsController@scriptTest')->name('admin.script-test');


/* User management routes  ---------------------------------------------- */
Route::get('/users', 'UsersController@index')->name('admin.users');
Route::get('/user-edit/{id}', 'UsersController@editShow')->name('admin.user-edit');
Route::post('/user-edit/{id}', 'UsersController@editPost')->name('admin.user-edit');
Route::delete('/user-delete/{id}', 'UsersController@delete')->name('admin.user-delete');


/* Schedule management routes  ------------------------------------------ */
Route::get('/schedule', 'ScheduleController@index')->name('admin.schedule');
Route::get('/schedule-edit/{id}', 'ScheduleController@editShow')->name('admin.schedule-edit');
Route::post('/schedule-edit/{id}', 'ScheduleController@editPost')->name('admin.schedule-edit');
Route::delete('/schedule-delete/{id}', 'ScheduleController@delete')->name('admin.schedule-delete');


/* Videcam management routes  ------------------------------------------- */
Route::get('/cams', 'CamsController@index')->name('admin.cams');
Route::get('/cam-edit/{id}', 'CamsController@editShow')->name('admin.cam-edit');
Route::post('/cam-edit/{id}', 'CamsController@editPost')->name('admin.cam-edit');
Route::delete('/cam-delete/{id}', 'CamsController@delete')->name('admin.cam-delete');


/* System jurnal  ------------------------------------------------------- */
/* Jurnal management routes */
Route::get('/jurnal', 'JurnalController@index')->name('admin.jurnal');

/* Devices history management routes */
Route::match(['get', 'post'], '/jurnal/history/{id?}', 'Jurnal\HistoryController@index')->name('admin.jurnal-history');
Route::get('/jurnal/history-value-view/{id}', 'Jurnal\HistoryController@valueView')->name('admin.jurnal-history-value-view');
Route::delete('/jurnal/history-value-delete/{id}', 'Jurnal\HistoryController@valueDelete')->name('admin.jurnal-history-value-delete');
Route::delete('/jurnal/history-delete-all-visible/{id}', 'Jurnal\HistoryController@deleteAllVisibleValues')->name('admin.jurnal-history-delete-all-visible');

/* Processes management routes */
Route::get('/jurnal/daemons/{id?}', 'Jurnal\DaemonsController@index')->name('admin.jurnal-daemons');
Route::get('/jurnal/daemon-data/{id}/{lastID?}', 'Jurnal\DaemonsController@data')->name('admin.jurnal-daemon-data');
Route::get('/jurnal/daemon-start/{id}', 'Jurnal\DaemonsController@daemonStart')->name('admin.jurnal-daemon-start');
Route::get('/jurnal/daemon-stop/{id}', 'Jurnal\DaemonsController@daemonStop')->name('admin.jurnal-daemon-stop');
Route::get('/jurnal/daemon-restart/{id}', 'Jurnal\DaemonsController@daemonRestart')->name('admin.jurnal-daemon-restart');

/* Power management routes */    
Route::get('/jurnal/power', 'Jurnal\PowerController@index')->name('admin.jurnal-power');


/* Terminal settings  --------------------------------------------------- */
Route::get('/terminal', 'TerminalController@index')->name('admin.terminal');
Route::post('/terminal-set-max-level/{value}', 'TerminalController@setMaxLevel')->name('admin.terminal-set-max-level');
