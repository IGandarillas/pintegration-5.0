<?php

namespace pintegration\Http\Controllers;

use Carbon\Carbon;

use Illuminate\Support\Facades\Auth;
use pintegration\User;
use Illuminate\Support\Facades\Queue;
use pintegration\Client;
use pintegration\Commands\UpdateClientFromPipedrive;
use pintegration\Http\Requests;

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
        ///
    }


    public function handlePipedriveReceipt(\Illuminate\Http\Request $request)
    {

        error_log("Request");
        $req = $request->all();

        //error_log(env(QUEUE_DRIVER));
        //$this->dispatchFrom('App\Jobs\SyncPipedriveDeals',$request);

        if($req['current']['status'] == 'won' && $req['previous']['status'] != 'won'){

            $clientIdPipedrive= array(
                'id_client_pipedrive' => $req['current']['person_id']
            );

            if(  Client::whereIdClientPipedrive($clientIdPipedrive)->first() != null  ){
                $task= new UpdateClientFromPipedrive($req, Auth::user()->id);
                error_log("Job Update");
                Queue::later(Carbon::now()->addSeconds(1), $task);
            }else{
                $task= new InsertClientFromPipedrive($req, Auth::user()->id);
                error_log("job");
                Queue::later(Carbon::now()->addSeconds(1), $task);

            }
            //
        }

    }

}
