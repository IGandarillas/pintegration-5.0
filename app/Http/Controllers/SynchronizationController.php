<?php namespace pintegration\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use pintegration\Console\Commands\SyncPrestashopAddresses;
use pintegration\Console\Commands\SyncPrestashopClients;
use pintegration\Console\Commands\SyncPrestashopProducts;
use pintegration\Http\Requests;
use pintegration\Http\Controllers\Controller;

use Illuminate\Http\Request;

class SynchronizationController extends Controller {

	public function __construct(){
		$this->middleware('auth');
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$user = Auth::user();
		$user->now_sync = true;
		if($user->now_sync) {
			Queue::push(new SyncPrestashopProducts());
			Queue::push(new SyncPrestashopClients());
			Queue::push(new SyncPrestashopAddresses());
			Queue::push(function () use ($user) {
				$user->now_sync = false;
			});
			return redirect('/home')->with([
				'OK' => 'Sincronizando...'
			]);
		}
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

}
