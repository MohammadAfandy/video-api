<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\Video;
use Weidner\Goutte\GoutteFacade;

class VideoController extends BaseController
{
	private $id_user;

	public function __construct(Request $request)
	{
		$this->id_user = $request->credentials->data->id_user;
		$this->is_admin = $request->credentials->data->role == 'admin';
	}

	public function getYoutube(Request $request)
	{
		$data = [];
		$crawler = GoutteFacade::request('GET', $request->input('video_url'));
		$crawler->filter('a')->each(function($node) use (&$data) {
			if (stripos($node->attr('href'), 'watch') !== false) {
				$data[$node->attr('href')] = [
					'name' => $node->text(),
					'url' => 'https://www.youtube.com/embed/' . str_replace('/watch?v=', '', $node->attr('href')),
				];
			}
		});

		$img = [];
		$crawler->filter('img')->each(function($node) use (&$img) {
			if (stripos($node->attr('src'), 'i.ytimg.com/') !== false) {
				$img[] = $node->attr('src');
			}
		});
		$i = 0;
		foreach ($data as $key => &$dat) {
			if (isset($img[$i])) {
				$dat['thumbnail'] = $img[$i];
				$i++;
			} else {
				unset($data[$key]);
			}
		};
		
		foreach ($data as $dat) {
			$request->request->add(['name' => $dat['name']]);
			$request->request->add(['url' => $dat['url']]);
			$request->request->add(['thumbnail' => $dat['thumbnail']]);
			$request->request->add(['description' => 'Terserah']);
			$this->store($request);
		}
	}

	public function index(Request $request)
	{
		$limit = (is_numeric($request->input('limit')) && $request->input('limit') <= 100) ? $request->input('limit') : 100;
		$sort = explode(':', $request->input('sort'));
		$order = !empty($sort[0]) ? $sort[0] : (new Video)->getKeyName();
		$desc = !empty($sort[1]) ? 'DESC' : 'ASC';

		$data = Video::select(['video.*', 'user.username'])
					->join('user', 'video.id_user', '=', 'user.id')
					->orderBy($order, $desc);

		if (!$this->is_admin) {
			$data->where('id_user', $this->id_user);
		}

		return app('api.helper')->success("Success", $data->paginate($limit));
	}

	public function show($id)
	{
		$video = Video::find(['id' => $id]);
		if (!$this->is_admin) {
			$video->where('id_user', $this->id_user);
		}
		$video = $video->first();
		return app('api.helper')->success("Success", $video);
	}

	public function store(Request $request)
	{
		try {
			$request->merge(['id_user' => $this->id_user]);
			$this->validateVideo($request);
			$video = Video::create($request->all());
			return app('api.helper')->success("Add Video Success");
		} catch(\Illuminate\Validation\ValidationException $e) {
			return app('api.helper')->failed("Validation Failed", $e->errors(), 422);
		} catch(\Exception $e) {
			return app('api.helper')->failed("Add Video Failed", $e->getMessage());
		}
	}

	public function update(Request $request, $id)
	{
		$video = Video::find(['id' => $id]);
		if (!$this->is_admin) {
			$video->where('id_user', $this->id_user);
		}
		$video = $video->first();

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
				return app('api.helper')->failed("Update Video Failed", $e->getMessage());
			}
		}
		return app('api.helper')->failed("Video Not Found", [], 404);
	}

	public function destroy($id)
	{
		$video = Video::find(['id' => $id]);
		if (!$this->is_admin) {
			$video->where('id_user', $this->id_user);
		}
		$video = $video->first();

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
			'id_user' => 'required|exists:user,id',
		]);
	}
}
