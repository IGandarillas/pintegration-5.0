<?php namespace pintegration\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use pintegration\Console\Commands\SyncAllPrestashopClients;
use pintegration\Console\Commands\SyncPrestashopClients;
use pintegration\Console\Commands\SyncPrestashopProducts;
use pintegration\Http\Requests;
use pintegration\Http\Controllers\Controller;
use Illuminate\Http\Request;
use pintegration\User;

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
		$user = User::find(\Auth::id());
		//$user->now_sync = true;
		//$user->update();
		//if($user->now_sync) {
			//Usar queue closure a modo callback en lugar de incluir operaciones en el handle
			//Queue::push(new SyncPrestashopProducts());
			//Queue::push(new SyncAllPrestashopClients());

		//return view('home.createOrUpdate'
		return view('synchronization.synchronization',compact('user'));
			/*return redirect('/home')->with([
				'OK' => 'Sincronizando...'
			]);*/
		//}
	}

	public function syncallproducts()
	{
		$user = User::find(\Auth::id());
		Queue::push(new SyncPrestashopProducts());
		return redirect('/sync')->with([
			'OK' => 'Sincronizando...'
		]);
	}
	public function syncallclients()
	{
		$user = User::find(\Auth::id());
		Queue::push(new SyncAllPrestashopClients());
		return redirect('/sync')->with([
			'OK' => 'Sincronizando...'
		]);
	}
	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
		$user = User::find(\Auth::id());
		$config = $user->configuration;
		$config->freq_products = $request->get('freq_products');
		$config->freq_clients = $request->get('freq_clients');
		$config->update();
		return view('synchronization.synchronization',compact('user'));

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
	public function update(Request $request)
	{
		$user = User::find(\Auth::id());
		$config = $user->configuration;
		$config->freq_products = $request->get('freq_products');
		$config->freq_clients = $request->get('freq_clients');
		$config->update();
		return view('synchronization.synchronization',compact('user'));

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
