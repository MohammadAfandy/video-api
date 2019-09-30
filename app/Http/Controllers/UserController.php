<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Hash;

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

	public function login(Request $request)
	{
		if (!empty($request->username) && !empty($request->password)) {
			if ($user = User::where('username', $request->username)->first()) {
				if (Hash::check($request->password, $user->password)) {
					return app('api.helper')->Success(
						"Login Success",
						['token' => $this->generateToken($user)]
					);
				}
				return app('api.helper')->failed("Wrong Username or Password", [], 401);
			}
			return app('api.helper')->failed("Username Not Found", [], 401);
		}
		return app('api.helper')->failed("Wrong Parameter", []);
	}

	private function generateToken(User $user)
	{
		$iat = time();			// issued at (now)
		$nbf = $iat + 5;		// not before (+10 s)
		$exp = $nbf + 60 * 60 * 24;	// expired nbf + 1d
		
		$payload = [
			'iss' => env('APP_NAME'),
			'sub' => $user->id_user,
			'iat' => $iat,
			'nbf' => $nbf,
			'exp' => $exp,
			'data' => [
				'username' => $user->username,
				'nama' => $user->nama,
				'email' => $user->email,
				'level' => $user->level,
			]
		];

		return JWT::encode($payload, env('JWT_SECRET'));
	}
}
