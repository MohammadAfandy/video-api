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
					return app('api.helper')->success(
						"Login Success",
						[
							'token' => $this->generateToken($user),
						]
					);
				}
				return app('api.helper')->failed("Wrong Username or Password", [], 401);
			}
			return app('api.helper')->failed("Username Not Found", [], 401);
		} catch(\Illuminate\Validation\ValidationException $e) {
			return app('api.helper')->failed("Validation Failed", $e->errors(), 422);
		} catch(\Exception $e) {
			return app('api.helper')->failed("Server Error");
		}
	}

	public function info(Request $request)
	{
		return app('api.helper')->success("Success", ['info' => $request->credentials->data]);
	}

	public function register(Request $request)
	{
		try {
			$request->merge(['role' => 'public']);
			$this->validateUser($request);
			$request->merge(['password' => Hash::make($request->password)]);
			$user = User::create($request->all());
			return app('api.helper')->success("Register Success");
		} catch(\Illuminate\Validation\ValidationException $e) {
			return app('api.helper')->failed("Validation Failed", $e->errors(), 422);
		} catch(\Exception $e) {
			return app('api.helper')->failed("Server Error");
		}
	}

	private function generateToken(User $user)
	{
		$iat = time();
		$nbf = $iat;
		$exp = $nbf + 60 * 60;
		
		$payload = [
			'iss' => env('APP_NAME'),
			'sub' => $user->id,
			'iat' => $iat,
			'nbf' => $nbf,
			'exp' => $exp,
			'data' => [
				'id_user' => $user->id,
				'username' => $user->username,
				'name' => $user->name,
				'role' => $user->role,
			]
		];

		return JWT::encode($payload, env('JWT_SECRET'));
	}

	private function validateUser($request)
	{
		$this->validate($request, [
			'username' => 'required|max:50|unique:user,username',
			'name' => 'required|max:200',
			'email' => 'required|max:200|email|unique:user,email',
			'role' => 'in:admin,public',
			'password' => 'required|min:6|same:password_confirmation',
			'password_confirmation' => 'required|min:6'
		]);
	}
}
