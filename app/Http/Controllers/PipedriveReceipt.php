<?php

namespace pintegration\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use pintegration\Http\Requests;
use GuzzleHttp;
use pintegration\Commands\ClientFromPipedrive;

class PipedriveReceipt extends Controller
{

    public function handlePipedriveReceipt(\Illuminate\Http\Request $request)
    {
        $req = $request->all();

        if($req['current']['status'] == 'won' && $req['previous']['status'] != 'won'){
                $task= new ClientFromPipedrive($req, Auth::user()->id);
                Queue::push( $task);
        }
    }

}
