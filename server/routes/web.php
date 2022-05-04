<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/


$router->get('/login', ['as' => 'login', 'uses' => 'Auth\LoginController@showLogin']);
$router->post('/login', ['as' => 'loginPost', 'uses' => 'Auth\LoginController@postLogin']);
$router->get('/loginpage', ['as' => 'loginpage', 'uses' => 'Auth\LoginController@loginPage']);
$router->get('/logout', ['as' => 'logout', 'uses' => 'Auth\LoginController@logout']);

$router->get('/', 'IndexController@index');
