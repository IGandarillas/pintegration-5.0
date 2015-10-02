<?php

namespace pintegration\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use pintegration\Http\Requests;
use pintegration\Handlers\ProductsCsvSeed;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use pintegration\Fileentry;
use Illuminate\Support\Facades\Redirect;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Auth;
use pintegration\Commands\ProductsCsvSeedJob;

class FileEntryController extends Controller
{
    use DispatchesCommands;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function getProducts(){
        return view('products.importProducts');
    }

    public function postProducts(Request $request) {

        if ($request->hasFile('csvproducts')) {
                $file = $request->file('csvproducts');
                $extension = $file->getClientOriginalExtension();
                Storage::disk('local')->put($file->getFilename() . '.' . $extension, File::get($file));

                $entry = new Fileentry();
                $entry->mime = $file->getClientMimeType();
                $entry->size = $file->getSize();
                $entry->original_filename = $file->getClientOriginalName();
                $entry->filename = $file->getFilename() . '.' . $extension;
                $entry->save();

                $path = storage_path().'/app/'.$file->getFilename().'.'.$extension;
                $file->move( storage_path(),$file->getFilename());
                error_log($file->getPathname().$file->getFilename().' '.storage_path().' '.$file->getRealPath());


            error_log('Lanzar job');
                $task = new ProductsCsvSeedJob($path);
            Queue::later(Carbon::now()->addSeconds(10), $task);
            error_log('Seeder c');
                return redirect('/products');

            }else{
            error_log('AASDF');
                return redirect('/products');
            }

    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //
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
        //
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
