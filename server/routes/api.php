<?php

$router->get('/start', 'StartController@getData');
$router->post('/login', 'AuthController@login');

$router->group(['middleware' => 'auth.terminal'], function ($router) {
    $router->get('/rooms', 'RoomsController@getData');
    $router->get('/cams', 'CamsController@getData');
    $router->get('/cam-poster/{id}', ['as' => 'cam-posters', 'uses' => 'CamsController@getPoster']);
    
    $router->get('/room/{roomID}', 'RoomController@getData');
    
    $router->get('/device/{deviceID}', 'DeviceController@getData');
    
    $router->get('/events/{lastID}', 'EventsController@getData');
    $router->post('/set-device-value/{deviceID}', 'DeviceController@setData');
    
    $router->get('/favorites', 'FavoritesController@getData');
    $router->get('/favorites-device-list', 'SettingsController@getFavoritesDeviceList');
    $router->post('/favorites-device-add/{deviceID}', 'SettingsController@addDeviceToFavorites');
    $router->post('/favorites-device-del/{deviceID}', 'SettingsController@delDeviceFromFavorites');
    $router->get('/favorites-order-list', 'SettingsController@getFavoritesOrderList');
    $router->post('/favorites-order-set', 'SettingsController@setFavoritesOrders');
    $router->get('/device-color-list', 'SettingsController@getDeviceColors');
    $router->post('/set-device-color/{index}', 'SettingsController@setDeviceColor');
    $router->delete('/del-device-color/{index}', 'SettingsController@delDeviceColor');
});
