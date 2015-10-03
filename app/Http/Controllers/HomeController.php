<?php

namespace pintegration\Http\Controllers;

use Illuminate\Http\Request;
use pintegration\Http\Requests;
use pintegration\Http\Controllers\Controller;
use pintegration\User;


class HomeController extends Controller
{

    public function __construct(){
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){        
  
        $user = User::find(\Auth::id());

        return view('home.createOrUpdate',compact('user'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(){
        $user = User::find(\Auth::id());
        return view('home.createOrUpdate',compact('user')); 
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        $user = User::find(\Auth::id());
        $user->prestashop_api = $request->get('prestashop_api');
        $user->pipedrive_api = $request->get('pipedrive_api');
        $user->prestashop_url = $request->get('prestashop_url');
        $user->address_field = $request->get('address_field');
        $user->save();
        return view('home.createOrUpdate',compact('user'));
    }


    public function postProducts(Request $request){
        if ($request->hasFile('csvproducts')) {
            if ($request->file('csvproducts')->isValid()) {
                $destinationPath = 'uploads'; // upload path
                //$extension = $request->file('image')->getClientOriginalExtension(); // getting image extension
                //$fileName = rand(11111,99999).'.'.$extension; // renameing image
                $request->file('image')->move($destinationPath, 'adf'); // uploading file to given path
                // sending back with message
                Session::flash('success', 'Fichero subido correctamente.');
                return Redirect::to('products');
            }
            else {
                // sending back with error message.
                Session::flash('error', 'Fichero no valido.');
                return Redirect::to('products');
            }
        }else{
            return redirect('/products')->withErrors([
                'failed' => 'Fichero no válido'
            ]);
        }

    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {   
        dd('3');
        $user = User::find(\Auth::id());
        return view('home.createOrUpdate',compact('user'));  
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
  
        $user = User::find(\Auth::id());
        $user->prestashop_api = $request->get('prestashop_api');
        $user->pipedrive_api = $request->get('pipedrive_api');
        $user->prestashop_url = $request->get('prestashop_url');
        $user->update();
        return redirect('home')->with('message', 'Campos actualizados!');
       
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }



}
