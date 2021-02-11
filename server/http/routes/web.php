<?php

/*Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login')->name('login');
Route::get('logout', 'Auth\LoginController@logout')->name('logout');
 */

Route::get('/', 'Terminal\RoomsController@index')->name('home');
Route::get('/room/{roomID}', 'Terminal\RoomController@index')->name('room');
Route::get('/variable/{variableID}', 'Terminal\VariableController@index')->name('variable');

Route::get('/checked', 'Terminal\CheckedController@index')->name('checked');

Route::get('/checked/edit/add/{selKey?}', 'Terminal\CheckedController@editAdd')->name('checked-edit-add');
Route::get('/checked/edit/add-add/{id}', 'Terminal\CheckedController@editAdd_ADD')->name('checked-edit-add-add');
Route::get('/checked/edit/add-del/{id}', 'Terminal\CheckedController@editAdd_DEL')->name('checked-edit-add-del');

Route::get('/checked/edit/order', 'Terminal\CheckedController@editOrder')->name('checked-edit-order');
Route::get('/checked/edit/order-up/{id}', 'Terminal\CheckedController@editOrder_UP')->name('checked-edit-order-up');
Route::get('/checked/edit/order-down/{id}', 'Terminal\CheckedController@editOrder_DOWN')->name('checked-edit-order-down');

Route::get('/checked/edit/color', 'Terminal\CheckedController@editColor')->name('checked-edit-color');
Route::post('/checked/edit/color-action/{action}', 'Terminal\CheckedController@editColor_ACTION')->name('checked-edit-color-action');

Route::get('/variable-changes/{lastID}', 'Terminal\VariableController@variableChanges')->name('variable-changes');
Route::post('/variable-set/{varID}/{varValue}', 'Terminal\VariableController@variableSet')->name('variable-set');
