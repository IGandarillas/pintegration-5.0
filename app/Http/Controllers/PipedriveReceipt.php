<?php

namespace pintegration\Http\Controllers;

use Illuminate\Support\Facades\Request;
use pintegration\Client;
use pintegration\Http\Requests;
use Auth;
use GuzzleHttp;
use pintegration\Commands\InsertClientFromPipedrive;

class PipedriveReceipt extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
//
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


    public function handlePipedriveReceipt(\Illuminate\Http\Request $request)
    {

        error_log("Añadir a cola");
        //$req = $request->all();

        $req=$request;
       // dd( );


        error_log('Status: '.$req['current']['status']);
        //$this->dispatchFrom('App\Jobs\SyncPipedriveDeals',$request);

        if($req['current']['status'] == 'won' && $req['previous']['status'] != 'won'){

            $clientIdPipedrive= array(
                'id_client_pipedrive' => $req['current']['person_id']
            );

            if(  Client::whereIdClientPipedrive($clientIdPipedrive)->first() != null  ){
                //Get

                return;
            }else{

                error_log("job");
                $this->dispatch(new InsertClientFromPipedrive($req));

            }
            //
        }

    }

}
