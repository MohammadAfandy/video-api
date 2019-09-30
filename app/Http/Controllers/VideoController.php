<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\Video;

class VideoController extends BaseController
{
	public function index(Request $request)
	{
		return app('api.helper')->Success("Success", Video::all());
	}

	public function show($id)
	{
		return app('api.helper')->Success("", Video::where('id', $id)->first());
	}
}
