<?php

namespace pintegration\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use pintegration\Http\Requests;
use GuzzleHttp;
use pintegration\Commands\DealFromPipedrive;
use pintegration\Commands\ClientFromPipedrive;
class PipedriveReceipt extends Controller
{

    public function handlePipedriveReceipt(\Illuminate\Http\Request $request)
    {
        $req = $request->all();
        if(isset($req['current']['status']))
        if($req['current']['status'] == 'won' && $req['previous']['status'] != 'won'){
                $task= new DealFromPipedrive($req, Auth::user()->id);
                Queue::push( $task);
        }

        if(isset( $req['meta']['object'] ) && $req['meta']['object'] == 'person') {
            $currentName       = $req['current']['name'];
            $previousName      = $req['current']['name'];
            $currentFirstName  = $req['current']['first_name'];
            $previousFirstName = $req['previous']['first_name'];
            $currentLastName   = $req['current']['last_name'];
            $previousLastName  = $req['previous']['last_name'];
            $currentEmail      = $req['current']['email'][0]['value'];
            $previousEmail     = $req['previous']['email'][0]['value'];
            $currentAddress    = $req['current'][Auth::user()->address_field];
            $previousAddress   = $req['previous'][Auth::user()->address_field];
            $currentPhone      = $req['current']['phone'][0]['value'];
            $previousPhone     = $req['previous']['phone'][0]['value'];

            if ($currentEmail     != $previousEmail     ||
                $currentAddress   != $previousAddress   ||
                $currentPhone     != $previousPhone     ||
                $currentFirstName != $previousFirstName ||
                $currentLastName  != $previousLastName  ||
                $currentName      != $previousName
            ) {
                $task = new ClientFromPipedrive($req, Auth::user()->id);
                Queue::push($task);
            }
        }
    }

}
