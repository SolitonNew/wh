<?php

Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('/login', 'Auth\LoginController@login')->name('loginPost');
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');

Route::get('/', 'IndexController@index');