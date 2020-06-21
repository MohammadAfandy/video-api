<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\User;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseController
{
	public function index(Request $request)
	{
		$limit = (is_numeric($request->input('limit')) && $request->input('limit') <= 100) ? $request->input('limit') : 100;
		$sort = explode(':', $request->input('sort'));
		$order = !empty($sort[0]) ? $sort[0] : (new User)->getKeyName();
		$desc = !empty($sort[1]) ? 'DESC' : 'ASC';
		return app('api.helper')->success("Success", User::orderBy($order, $desc)->paginate($limit));
	}

	public function show($id)
	{
		return app('api.helper')->success("");
	}

	public function getProfile(Request $request)
	{
		$profile = User::find($request->credentials->sub);
		return app('api.helper')->success("Success", $profile);
	}

	public function updateProfile(Request $request)
	{
		$profile = User::find($request->credentials->sub);

		if ($profile) {
			try {
				$this->validateUser($request, $profile);

				$profile->name = $request->name;
				$profile->role = $request->role;
				if ($request->password) {
					$profile->password = Hash::make($request->password);
				}
				if ($request->images) {
					$path = 'images/profile/';
					$file_name = $path . $profile->username . "_" . time() . ".png";
					if (!file_exists($path)) {
						mkdir($path, 775, true);
					}
					Image::make($request->images)->save($file_name);
					$profile->images = $file_name;
				}

				$profile->save();
			} catch (\Illuminate\Validation\ValidationException $e) {
				return app('api.helper')->failed("Validation Failed", $e->errors(), 422);
			} catch (\Exception $e) {
				return app('api.helper')->failed("Server Error");
			}

			return app('api.helper')->success("Update Profile Success");
		}
	}

	public function destroy($id)
	{
		$user = User::find($id);

		if ($user && $user->delete()) {
			return app('api.helper')->success("Delete User Success");
		}

		return app('api.helper')->failed("Delete User Failed");
	}

	private function validateUser($request, $profile)
	{
		$this->validate($request, [
			'name' => 'required|min:3|max:200',
			'username' => 'required|min:4|max:50',
			'role' => 'required',
			'password_old' => [
				'sometimes',
				'required',
				function ($attribute, $value, $fail) use ($profile) {
					if (!Hash::check($value, $profile->password)) {
						$fail('Old Password didn\'t match');
					}
				},
			],
			'password' => [
				'sometimes',
				'required',
				'confirmed',
				'min:6',
			],
		]);
	}
}
