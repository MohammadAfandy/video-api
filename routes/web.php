
<?php

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

$router->get('/', function () use ($router) {
    return "OK";
});

// $router->group(['prefix' => 'api', 'as' => 'api'], function () use ($router) {

	// Auth Group
	$router->group(['prefix' => 'auth', 'as' => 'auth'], function () use ($router) {
		// Login
		$router->post('login', [
			'as' => 'login',
			'uses' => 'AuthController@login',
		]);
	});

	$router->group(['middleware' => 'jwt.auth'], function () use ($router) {

		// User Group
		$router->group(['prefix' => 'user', 'as' => 'user'], function () use ($router) {
			$router->get('/', [
				'as' => 'index',
				'uses' => 'UserController@index',
			]);

			$router->get('/{id:[0-9]+}', [
				'as' => 'show',
				'uses' => 'UserController@show',
			]);
		});

		// Video Group
		$router->group(['prefix' => 'video', 'as' => 'video'], function () use ($router) {
			$router->get('/', [
				'as' => 'index',
				'uses' => 'VideoController@index',
			]);
			$router->get('/{id:[0-9]+}', [
				'as' => 'show',
				'uses' => 'VideoController@show',
			]);
		});
	});
// });
