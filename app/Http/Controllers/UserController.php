<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends BaseController
{
	public function index(Request $request)
	{
		return app('api.helper')->Success("Success", User::all());
	}

	public function show($id)
	{

		return app('api.helper')->Success("");
	}
}
