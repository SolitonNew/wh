<?php

/* Displaying grouped data by room  ------------------------------------- */
Route::get('/', 'RoomsController@index')->name('home');
Route::get('/room/{roomID}', 'RoomController@index')->name('terminal.room');
Route::get('/device/{deviceID}', 'DeviceController@index')->name('terminal.device');


/* Page "Favorites"  ---------------------------------------------------- */
/* Index  */
Route::get('/checked', 'CheckedController@index')->name('terminal.checked');

/* The page for selecting devices to display on the "Favorites" page */
Route::get('/checked/edit/add/{selKey?}', 'CheckedController@editAdd')->name('terminal.checked-edit-add');
Route::get('/checked/edit/add-add/{id}', 'CheckedController@editAdd_ADD')->name('terminal.checked-edit-add-add');
Route::get('/checked/edit/add-del/{id}', 'CheckedController@editAdd_DEL')->name('terminal.checked-edit-add-del');


/* The page for configuring the order of displaying variables on the Favorites page */
Route::get('/checked/edit/order', 'CheckedController@editOrder')->name('terminal.checked-edit-order');
Route::get('/checked/edit/order-up/{id}', 'CheckedController@editOrder_UP')->name('terminal.checked-edit-order-up');
Route::get('/checked/edit/order-down/{id}', 'CheckedController@editOrder_DOWN')->name('terminal.checked-edit-order-down');


/* Page for setting the color of variables by text mask  ---------------- */
Route::get('/checked/edit/color', 'CheckedController@editColor')->name('terminal.checked-edit-color');
Route::post('/checked/edit/color-action/{action}', 'CheckedController@editColor_ACTION')->name('terminal.checked-edit-color-action');


/* Requesting variable changes  ----------------------------------------- */
Route::get('/device-changes/{lastID}', 'DeviceController@changes')->name('terminal.device-changes');


/* Requesting queue changes  -------------------------------------------- */
Route::get('/quque-changes/{lasID}', 'QueueController@changes')->name('terminal.queue-changes');
Route::get('/queue-speech-source/{id}', 'QueueController@speechSource')->name('terminal.queue-speech-source');


/* Setting device value  ------------------------------------------------ */
Route::post('/device-set/{deviceID}/{value}', 'DeviceController@set')->name('terminal.device-set');