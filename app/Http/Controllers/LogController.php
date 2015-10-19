<?php

namespace pintegration\Http\Controllers;



use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Rap2hpoutre\LaravelLogViewer\LaravelLogViewer;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;

class LogController extends LogViewerController
{
	public function __construct(){
		$this->middleware('auth');
	}

	public function index()
	{
		if (Input::get('l')) {
			LaravelLogViewer::setFile(base64_decode(Input::get('l')));
		}

		if (Input::get('dl')) {
			return Response::download(storage_path() . '/logs/' . base64_decode(Input::get('dl')));
		} elseif (Input::has('del')) {
			File::delete(storage_path() . '/logs/' . base64_decode(Input::get('del')));
			return Redirect::to(Request::url());
		}

		$logs = LaravelLogViewer::all();

		return View::make('log.log', [
			'logs' => $logs,
			'files' => LaravelLogViewer::getFiles(true),
			'current_file' => LaravelLogViewer::getFileName()
		]);
	}

}



