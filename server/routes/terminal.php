<?php

/* Displaying grouped data by room  ------------------------------------- */
$router->get('/', ['as' => 'home', 'uses' => 'RoomsController@index']);
$router->get('/room/{roomID}', ['as' => 'terminal.room', 'uses' => 'RoomController@index']);
$router->get('/device/{deviceID}', ['as' => 'terminal.device', 'uses' => 'DeviceController@index']);


/* Page "Favorites"  ---------------------------------------------------- */
/* Index  */
$router->get('/checked', ['as' => 'terminal.checked', 'uses' => 'CheckedController@index']);

/* The page for selecting devices to display on the "Favorites" page */
$router->get('/checked/edit/add[/{selKey}]', ['as' => 'terminal.checked-edit-add', 'uses' => 'CheckedController@editAdd']);
$router->get('/checked/edit/add-add/{id}', ['as' => 'terminal.checked-edit-add-add', 'uses' => 'CheckedController@editAdd_ADD']);
$router->get('/checked/edit/add-del/{id}', ['as' => 'terminal.checked-edit-add-del', 'uses' => 'CheckedController@editAdd_DEL']);


/* The page for configuring the order of displaying variables on the Favorites page */
$router->get('/checked/edit/order', ['as' => 'terminal.checked-edit-order', 'uses' => 'CheckedController@editOrder']);
$router->get('/checked/edit/order-up/{id}', ['as' => 'terminal.checked-edit-order-up', 'uses' => 'CheckedController@editOrder_UP']);
$router->get('/checked/edit/order-down/{id}', ['as' => 'terminal.checked-edit-order-down', 'uses' => 'CheckedController@editOrder_DOWN']);


/* Page for setting the color of variables by text mask  ---------------- */
$router->get('/checked/edit/color', ['as' => 'terminal.checked-edit-color', 'uses' => 'CheckedController@editColor']);
$router->post('/checked/edit/color-action/{action}', ['as' => 'terminal.checked-edit-color-action', 'uses' => 'CheckedController@editColor_ACTION']);


/* Setting device value  ------------------------------------------------ */
$router->post('/device-set/{deviceID}/{value}', ['as' => 'terminal.device-set', 'uses' => 'DeviceController@set']);


/* Requesting event list  ----------------------------------------------- */
$router->get('/events/{lastID}', ['as' => 'terminal.events', 'uses' => 'EventsController@getEvents']);


/* Requesting media data  ----------------------------------------------- */
$router->get('/media-source/{typ}/{id}', ['as' => 'terminal.media-source', 'uses' => 'QueueController@getData']);


