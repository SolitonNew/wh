<?php

Route::get('/zzz', function () {
    dd(opcache_get_status());
});
        

Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('/login', 'Auth\LoginController@login')->name('loginPost');
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');

Route::group(['middleware'=>'auth'], function () {
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
});

Route::group(['prefix' => 'admin', 'middleware'=>'auth'], function () {
    Route::get('/', function () {
        return redirect(route('variables'));
    });
    
    Route::get('/rooms/{partID?}', 'Admin\RoomsController@index')->name('parts');
    
    Route::get('/variables/{partID?}', 'Admin\VariablesController@index')->name('variables');
    Route::get('/variables-ow-list/{controller}', 'Admin\VariablesController@owList')->name('variables-ow-list');
    Route::get('/variables-channel-list/{rom}/{ow_id?}', 'Admin\VariablesController@channelList')->name('variables-channel-list');
    Route::match(['get', 'post'], '/variable-edit/{id}', 'Admin\VariablesController@edit')->name('variable-edit');
    Route::get('/variable-delete/{id}', 'Admin\VariablesController@delete')->name('variable-delete');
    
    Route::get('/variable-changes/{lastID}', function ($lastID) {
        \App\Http\Models\VariableChangesModel::setLastVariableID($lastID);
        return view('admin.log');
    })->name('variable-changes');
    
    Route::get('/scripts', 'Admin\ScriptsController@index')->name('scripts');
    
    Route::get('/statistics', 'Admin\StatisticsController@index')->name('statistics');
    
    Route::get('/users', 'Admin\UsersController@index')->name('users');
    Route::match(['get', 'post'], '/user-edit/{id}', 'Admin\UsersController@edit')->name('user-edit');
    Route::get('/user-delete/{id}', 'Admin\USersController@delete')->name('user-delete');
    
    Route::post('/users', 'Admin\UsersController@append')->name('users');
    
    Route::get('/ow-manager', 'Admin\OwManagerController@index')->name('ow-manager');
    
    Route::get('/schedule', 'Admin\ScheduleController@index')->name('schedule');
});
