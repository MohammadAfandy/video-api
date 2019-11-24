<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\Video;

class VideoController extends BaseController
{
	public function index(Request $request)
	{
		$limit = (is_numeric($request->input('limit')) && $request->input('limit') < 100) ? $request->input('limit') : 100;
		return app('api.helper')->success("Success", Video::paginate($limit));
	}

	public function show($id)
	{
		return app('api.helper')->success("Success", Video::where('id', $id)->first());
	}

	public function store(Request $request)
	{
		$video = new Video();

		try {
			$this->validateVideo($request);
			$video->name = $request->name;
			$video->url = $request->url;
			$video->thumbnail = $request->thumbnail;
			$video->description = $request->description;

			$video->save();
			return app('api.helper')->success("Update Video Success");
		} catch(\Illuminate\Validation\ValidationException $e) {
			return app('api.helper')->failed("Validation Failed", $e->errors(), 422);
		} catch(\Exception $e) {
			return app('api.helper')->failed("Server Error", $e->getMessage());
		}

		return app('api.helper')->success("Add Video Success");
	}

	public function update(Request $request, $id)
	{
		$video = Video::find($id);

		if ($video) {
			try {
				$this->validateVideo($request);
				$video->name = $request->name;
				$video->url = $request->url;
				$video->thumbnail = $request->thumbnail;
				$video->description = $request->description;

				$video->save();
				return app('api.helper')->success("Update Video Success");
			} catch(\Illuminate\Validation\ValidationException $e) {
				return app('api.helper')->failed("Validation Failed", $e->errors(), 422);
			} catch(\Exception $e) {
				return app('api.helper')->failed("Server Error", $e->getMessage());
			}
		}

		return app('api.helper')->failed("Update Video Failed");
	}

	public function destroy($id)
	{
		$video = Video::find($id);

		if ($video) {
			$video->delete();
			return app('api.helper')->success("Delete Video Success");
		}

		return app('api.helper')->failed("Delete Video Failed");
	}

	private function validateVideo($request)
	{
		$this->validate($request, [
			'name' => 'required|max:200',
			'url' => 'required|max:500',
			'description' => 'required|max:500',
		]);
	}
}
