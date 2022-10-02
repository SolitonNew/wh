<?php

$router->get('/login', ['as' => 'login', 'uses' => 'LoginController@showLogin']);
$router->post('/login', ['as' => 'loginPost', 'uses' => 'LoginController@postLogin']);
$router->get('/loginpage', ['as' => 'loginpage', 'uses' => 'LoginController@loginPage']);
$router->get('/logout', ['as' => 'logout', 'uses' => 'LoginController@logout']);

$router->group(['middleware' => 'auth.admin'], function ($router) {
    /* Index controller ----------------------------------------------------- */
    $router->get('/', ['as' => 'admin', 'uses' => 'IndexController@index']);
    $router->get('/variable-changes/{lastID}', ['as' => 'admin.variable-changes', 'uses' => 'IndexController@variableChanges']);


    /* Planning rooms  ------------------------------------------------------ */
    $router->get('/plan[/{id}]', ['as' => 'admin.plan', 'uses' => 'PlanController@index']);
    $router->get('/plan-edit/{id}[/{p_id}]', ['as' => 'admin.plan-edit', 'uses' => 'PlanController@editShow']);
    $router->post('/plan-edit/{id}[/{p_id}]', ['as' => 'admin.plan-edit', 'uses' => 'PlanController@editPost']);
    $router->delete('/plan-delete/{id}', ['as' => 'admin.plan-delete', 'uses' => 'PlanController@delete']);
    $router->get('/plan-clone/{id}/{direction}', ['as' => 'admin.plan-clone', 'uses' => 'PlanController@planClone']);
    $router->get('/plan-move-childs/{id}', ['as' => 'admin.plan-move-childs', 'uses' => 'PlanController@moveChildsShow']);
    $router->post('/plan-move-childs/{id}', ['as' => 'admin.plan-move-childs', 'uses' => 'PlanController@moveChildsPost']);
    $router->get('/plan-order/{id}', ['as' => 'admin.plan-order', 'uses' => 'PlanController@orderShow']);
    $router->post('/plan-order/{id}', ['as' => 'admin.plan-order', 'uses' => 'PlanController@orderPost']);
    $router->post('/plan-move/{id}/{newX}/{newY}', ['as' => 'admin.plan-move', 'uses' => 'PlanController@move']);
    $router->post('/plan-size/{id}/{newW}/{newH}', ['as' => 'admin.plan-size', 'uses' => 'PlanController@size']);
    $router->get('/plan-import', ['as' => 'admin.plan-import', 'uses' => 'PlanController@planImportShow']);
    $router->post('/plan-import', ['as' => 'admin.plan-import', 'uses' => 'PlanController@planImportPost']);
    $router->get('/plan-export', ['as' => 'admin.plan-export', 'uses' => 'PlanController@planExport']);
    $router->get('/plan-link-device/{planID}[/{deviceID}]', ['as' => 'admin.plan-link-device', 'uses' => 'PlanController@linkDeviceShow']);
    $router->post('/plan-link-device/{planID}[/{deviceID}]', ['as' => 'admin.plan-link-device', 'uses' => 'PlanController@linkDevicePost']);
    $router->delete('/plan-unlink-device/{deviceID}', ['as' => 'admin.plan-unlink-device', 'uses' => 'PlanController@unlinkDevice']);
    $router->get('/plan-port-edit/{planID}[/{portIndex}]', ['as' => 'admin.plan-port-edit', 'uses' => 'PlanController@portEditShow']);
    $router->post('/plan-port-edit/{planID}[/{portIndex}]', ['as' => 'admin.plan-port-edit', 'uses' => 'PlanController@portEditPost']);
    $router->delete('/plan-port-delete/{planID}/{portIndex}', ['as' => 'admin.plan-port-delete', 'uses' => 'PlanController@portDelete']);


    /* Configuration  ------------------------------------------------------- */
    /* Hubs management routes */
    $router->get('/hubs[/{hubID}]', ['as' => 'admin.hubs', 'uses' => 'HubsController@index']);
    $router->get('/hub-edit/{id}', ['as' => 'admin.hub-edit', 'uses' => 'HubsController@editShow']);
    $router->post('/hub-edit/{id}', ['as' => 'admin.hub-edit', 'uses' => 'HubsController@editPost']);
    $router->delete('/hub-delete/{id}', ['as' => 'admin.hub-delete', 'uses' => 'HubsController@delete']);
    $router->get('/hub-network-scan/{id}', ['as' => 'admin.hub-network-scan', 'uses' => 'HubsController@hubNetworkScan']);
    $router->get('/hubs-firmware', ['as' => 'admin.firmware', 'uses' => 'HubsController@firmware']);
    $router->get('/hubs-firmware-start', ['as' => 'admin.firmware-start', 'uses' => 'HubsController@firmwareStart']);
    $router->get('/hubs-firmware-status', ['as' => 'admin.firmware-status', 'uses' => 'HubsController@firmwareStatus']);
    $router->get('/hubs-reset', ['as' => 'admin.hubs-reset', 'uses' => 'HubsController@hubsReset']);
    $router->get('/hubs-add-devices-for-all-hosts/{hubID}', ['as' => 'admin.hubs-add-devices-for-all-hosts', 'uses' => 'HubsController@addDevicesForAllHosts']);

    /* Hosts management routes */
    $router->get('/hubs/{hubID}/hosts', ['as' => 'admin.hub-hosts', 'uses' => 'Hubs\HostsController@index']);
    // ExtApi hosts
    $router->get('/hub-extapihost-edit/{hubID}/{id}', ['as' => 'admin.hub-extapihost-edit', 'uses' => 'Hubs\HostsController@editExtApiShow']);
    $router->post('/hub-extapihost-edit/{hubID}/{id}', ['as' => 'admin.hub-extapihost-edit', 'uses' => 'Hubs\HostsController@editExtApiPost']);
    $router->delete('/hub-extapihost-delete/{hubID}/{id}', ['as' => 'admin.hub-extapihost-delete', 'uses' => 'Hubs\HostsController@deleteExtApi']);
    // Camcorder hosts
    $router->get('/hub-camcorderhost-edit/{hubID}/{id}', ['as' => 'admin.hub-camcorderhost-edit', 'uses' => 'Hubs\HostsController@editCamcorderShow']);
    $router->post('/hub-camcorderhost-edit/{hubID}/{id}', ['as' => 'admin.hub-camcorderhost-edit', 'uses' => 'Hubs\HostsController@editCamcorderPost']);
    $router->delete('/hub-camcorderhost-delete/{hubID}/{id}', ['as' => 'admin.hub-camcorderhost-delete', 'uses' => 'Hubs\HostsController@deleteCamcorder']);
    // Orange Pi hosts
    $router->get('/hub-orangehost-edit/{hubID}/{id}', ['as' => 'admin.hub-orangehost-edit', 'uses' => 'Hubs\HostsController@editOrangeShow']);
    $router->post('/hub-orangehost-edit/{hubID}/{id}', ['as' => 'admin.hub-orangehost-edit', 'uses' => 'Hubs\HostsController@editOrangePost']);
    $router->delete('/hub-orangehost-delete/{hubID}/{id}', ['as' => 'admin.hub-orangehost-delete', 'uses' => 'Hubs\HostsController@deleteOrange']);
    // Din hosts
    $router->get('/hub-dinhost-edit/{hubID}/{id}', ['as' => 'admin.hub-dinhost-edit', 'uses' => 'Hubs\HostsController@editDinShow']);
    $router->post('/hub-dinhost-edit/{hubID}/{id}', ['as' => 'admin.hub-dinhost-edit', 'uses' => 'Hubs\HostsController@editDinPost']);
    $router->delete('/hub-dinhost-delete/{hubID}/{id}', ['as' => 'admin.hub-dinhost-delete', 'uses' => 'Hubs\HostsController@deleteDin']);
    // Pyhome hosts
    $router->get('/hub-pyhomehost-edit/{hubID}/{id}', ['as' => 'admin.hub-pyhomehost-edit', 'uses' => 'Hubs\HostsController@editPyhomeShow']);
    $router->post('/hub-pyhomehost-edit/{hubID}/{id}', ['as' => 'admin.hub-pyhomehost-edit', 'uses' => 'Hubs\HostsController@editPyhomePost']);
    $router->delete('/hub-pyhomehost-delete/{hubID}/{id}', ['as' => 'admin.hub-pyhomehost-delete', 'uses' => 'Hubs\HostsController@deletePyhome']);
    // Zigbee One hosts
    $router->get('/hub-zigbeehost-edit/{hubID}/{id}', ['as' => 'admin.hub-zigbeehost-edit', 'uses' => 'Hubs\HostsController@editZigbeeShow']);
    $router->post('/hub-zigbeehost-edit/{hubID}/{id}', ['as' => 'admin.hub-zigbeehost-edit', 'uses' => 'Hubs\HostsController@editZigbeePost']);
    $router->delete('/hub-zigbeehost-delete/{hubID}/{id}', ['as' => 'admin.hub-zigbeehost-delete', 'uses' => 'Hubs\HostsController@deleteZigbee']);

    /* Devices management routes */
    $router->get('/hubs/{hubID}/devices[/{groupID}]', ['as' => 'admin.hub-devices', 'uses' => 'Hubs\DevicesController@index']);
    $router->get('/hub-device-edit/{hubID}/{id}', ['as' => 'admin.hub-device-edit', 'uses' => 'Hubs\DevicesController@editShow']);
    $router->post('/hub-device-edit/{hubID}/{id}', ['as' => 'admin.hub-device-edit', 'uses' => 'Hubs\DevicesController@editPost']);
    $router->delete('/hub-device-delete/{id}', ['as' => 'admin.hub-device-delete', 'uses' => 'Hubs\DevicesController@delete']);
    $router->get('/hub-device-host-list/{hubID}', ['as' => 'admin.hub-device-host-list', 'uses' => 'Hubs\DevicesController@hostList']);
    $router->get('/hub-device-host-channel-list/{typ}[/{hostID}]', ['as' => 'admin.hub-device-host-channel-list', 'uses' => 'Hubs\DevicesController@hostChannelList']);


    /* Scripts management routes  ------------------------------------------- */
    $router->get('/scripts[/{id}]', ['as' => 'admin.scripts', 'uses' => 'ScriptsController@index']);
    $router->get('/script-edit/{id}', ['as' => 'admin.script-edit', 'uses' => 'ScriptsController@editShow']);
    $router->post('/script-edit/{id}', ['as' => 'admin.script-edit', 'uses' => 'ScriptsController@editPost']);
    $router->delete('/script-delete/{id}', ['as' => 'admin.script-delete', 'uses' => 'ScriptsController@delete']);
    $router->get('/script-template', ['as' => 'admin.script-template', 'uses' => 'ScriptsController@scriptTemplate']);
    $router->get('/script-events/{id}', ['as' => 'admin.script-events', 'uses' => 'ScriptsController@attacheEventsShow']);
    $router->post('/script-events/{id}', ['as' => 'admin.script-events', 'uses' => 'ScriptsController@attacheEventsPost']);
    $router->post('/script-save/{id}', ['as' => 'admin.script-save', 'uses' => 'ScriptsController@saveScript']);
    $router->post('/script-test', ['as' => 'admin.script-test', 'uses' => 'ScriptsController@scriptTest']);


    /* User management routes  ---------------------------------------------- */
    $router->get('/users', ['as' => 'admin.users', 'uses' => 'UsersController@index']);
    $router->get('/user-edit/{id}', ['as' => 'admin.user-edit', 'uses' => 'UsersController@editShow']);
    $router->post('/user-edit/{id}', ['as' => 'admin.user-edit', 'uses' => 'UsersController@editPost']);
    $router->delete('/user-delete/{id}', ['as' => 'admin.user-delete', 'uses' => 'UsersController@delete']);


    /* Schedule management routes  ------------------------------------------ */
    $router->get('/schedule', ['as' => 'admin.schedule', 'uses' => 'ScheduleController@index']);
    $router->get('/schedule-edit/{id}', ['as' => 'admin.schedule-edit', 'uses' => 'ScheduleController@editShow']);
    $router->post('/schedule-edit/{id}', ['as' => 'admin.schedule-edit', 'uses' => 'ScheduleController@editPost']);
    $router->delete('/schedule-delete/{id}', ['as' => 'admin.schedule-delete', 'uses' => 'ScheduleController@delete']);


    /* System jurnal  ------------------------------------------------------- */
    /* Jurnal management routes */
    $router->get('/jurnal', ['as' => 'admin.jurnal', 'uses' => 'JurnalController@index']);

    /* Devices history management routes */
    $router->addRoute(['GET', 'POST'], '/jurnal/history[/{id}]', ['as' => 'admin.jurnal-history', 'uses' => 'Jurnal\HistoryController@index']);
    $router->get('/jurnal/history-value-view/{id}', ['as' => 'admin.jurnal-history-value-view', 'uses' => 'Jurnal\HistoryController@valueView']);
    $router->delete('/jurnal/history-value-delete/{id}', ['as' => 'admin.jurnal-history-value-delete', 'uses' => 'Jurnal\HistoryController@valueDelete']);
    $router->delete('/jurnal/history-delete-all-visible/{id}', ['as' => 'admin.jurnal-history-delete-all-visible', 'uses' => 'Jurnal\HistoryController@deleteAllVisibleValues']);

    /* Processes management routes */
    $router->get('/jurnal/daemons[/{id}]', ['as' => 'admin.jurnal-daemons', 'uses' => 'Jurnal\DaemonsController@index']);
    $router->get('/jurnal/daemon-data/{id}[/{lastID}]', ['as' => 'admin.jurnal-daemon-data', 'uses' => 'Jurnal\DaemonsController@data']);
    $router->get('/jurnal/daemon-start/{id}', ['as' => 'admin.jurnal-daemon-start', 'uses' => 'Jurnal\DaemonsController@daemonStart']);
    $router->get('/jurnal/daemon-stop/{id}', ['as' => 'admin.jurnal-daemon-stop', 'uses' => 'Jurnal\DaemonsController@daemonStop']);
    $router->get('/jurnal/daemon-restart/{id}', ['as' => 'admin.jurnal-daemon-restart', 'uses' => 'Jurnal\DaemonsController@daemonRestart']);
    $router->get('/jurnal/daemon-start-all', ['as' => 'admin.jurnal-daemon-start-all', 'uses' => 'Jurnal\DaemonsController@daemonStartAll']);
    $router->get('/jurnal/daemon-stop-all', ['as' => 'admin.jurnal-daemon-stop-all', 'uses' => 'Jurnal\DaemonsController@daemonStopAll']);
    $router->get('/jurnal/daemons-state', ['as' => 'admin.jurnal-daemons-state', 'uses' => 'Jurnal\DaemonsController@daemonsState']);

    /* Forecast routes */
    $router->get('/jurnal/forecast', ['as' => 'admin.jurnal-forecast', 'uses' => 'Jurnal\ForecastController@index']);
    $router->delete('/jurnal/forecast-clear', ['as' => 'admin.jurnal-forecast-clear', 'uses' => 'Jurnal\ForecastController@clearStorageData']);

    /* Power management routes */
    $router->get('/jurnal/power', ['as' => 'admin.jurnal-power', 'uses' => 'Jurnal\PowerController@index']);


    /* Settings  --------------------------------------------------- */
    $router->get('/settings', ['as' => 'admin.settings', 'uses' => 'SettingsController@index']);
    $router->post('/settings-set-max-level/{value}', ['as' => 'admin.settings-set-max-level', 'uses' => 'SettingsController@setMaxLevel']);
    $router->post('/settings-set-timezone', ['as' => 'admin.settings-set-timezone', 'uses' => 'SettingsController@setTimezone']);
    $router->post('/settings-set-location', ['as' => 'admin.settings-set-location', 'uses' => 'SettingsController@setLocation']);
    $router->post('/settings-set-din-settings', ['as' => 'admin.settings-set-din-settings', 'uses' => 'SettingsController@setDinSettings']);
    $router->post('/settings-set-pyhome-settings', ['as' => 'admin.settings-set-pyhome-settings', 'uses' => 'SettingsController@setPyhomeSettings']);
    $router->post('/settings-set-forecast', ['as' => 'admin.settings-set-forecast', 'uses' => 'SettingsController@setForecast']);

    /* Test  ------------------------------------------------- */
    $router->get('/test', 'TestController@test');
});
