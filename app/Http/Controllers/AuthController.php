<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseController
{
	public function login(Request $request)
	{
		try {
			$this->validate($request, [
				'username' => 'required',
				'password' => 'required',
			]);
			if ($user = User::where('username', $request->username)->first()) {
				if (Hash::check($request->password, $user->password)) {
					return app('api.helper')->Success(
						"Login Success",
						[
							'token' => $this->generateToken($user)
						]
					);
				}
				return app('api.helper')->failed("Wrong Username or Password", [], 401);
			}
			return app('api.helper')->failed("Username Not Found", [], 401);
		} catch(\Illuminate\Validation\ValidationException $e) {
			return app('api.helper')->failed("Validation Failed", $e->errors(), 422);
		} catch(\Exception $e) {
			return app('api.helper')->failed("Server Error", $e->getMessage());
		}
	}

	public function info(Request $request)
	{
		return app('api.helper')->Success("Success", ['info' => $request->credentials->data]);
	}

	public function register(Request $request)
	{
		dd($request);
	}

	private function generateToken(User $user)
	{
		$iat = time();			// issued at (now)
		$nbf = $iat;		// not before (+10 s)
		$exp = $nbf + 60 * 60;	// expired nbf + 1d
		
		$payload = [
			'iss' => env('APP_NAME'),
			'sub' => $user->id,
			'iat' => $iat,
			'nbf' => $nbf,
			'exp' => $exp,
			'data' => [
				'username' => $user->username,
				'name' => $user->name,
				'role' => $user->role,
			]
		];

		return JWT::encode($payload, env('JWT_SECRET'));
	}
}