<?php

$router->get('/', ['as' => 'home', 'uses' => 'IndexController@index']);

$router->group(['middleware' => 'auth.terminal'], function () use ($router) {
    $router->post('broadcasting/auth', function (Illuminate\Http\Request $request) {
        return Illuminate\Support\Facades\Broadcast::auth($request);
    });
});