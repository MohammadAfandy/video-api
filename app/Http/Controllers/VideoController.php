<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\Video;

class VideoController extends BaseController
{
	public function index(Request $request)
	{
		return app('api.helper')->success("Success", Video::all());
	}

	public function show($id)
	{
		return app('api.helper')->success("Success", Video::where('id', $id)->first());
	}

	public function store(Request $request)
	{
		$video = new Video();
		$video->name = $request->name;
		$video->url = $request->url;
		$video->thumbnail = $request->thumbnail;
		$video->description = $request->description;

		$video->save();

		return app('api.helper')->success("Add Video Success");
	}
}
