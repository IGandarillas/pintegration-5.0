<?php

namespace pintegration\Http\Controllers;

use Carbon\Carbon;

use Illuminate\Support\Facades\Auth;
use pintegration\Commands\Insertpdprueba;
use pintegration\User;
use Illuminate\Support\Facades\Queue;
use pintegration\Client;
use pintegration\Commands\UpdateClientFromPipedrive;
use pintegration\Http\Requests;

use GuzzleHttp;
use pintegration\Commands\InsertClientFromPipedrive;

class PipedriveReceip extends Controller
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
        $request= '{
  "v": 1,
  "matches_filters": {
    "current": [
      3
    ]
  },
  "meta": {
    "v": 1,
    "action": "updated",
    "object": "deal",
    "id": 5,
    "company_id": 634990,
    "user_id": 888118,
    "host": "devpsintegration.pipedrive.com",
    "timestamp": 1444862429,
    "permitted_user_ids": [
      888118
    ],
    "trans_pending": false,
    "is_bulk_update": false,
    "matches_filters": {
      "current": [
        3
      ]
    }
  },
  "retry": 0,
  "current": {
    "id": 5,
    "user_id": 888118,
    "person_id": 947,
    "org_id": null,
    "stage_id": 1,
    "title": "Gilbert Runte negocio",
    "value": 343576,
    "currency": "EUR",
    "add_time": "2015-10-14 22:40:17",
    "update_time": "2015-10-14 22:40:29",
    "stage_change_time": null,
    "active": false,
    "deleted": false,
    "status": "won",
    "next_activity_date": null,
    "next_activity_time": null,
    "next_activity_id": null,
    "last_activity_id": null,
    "last_activity_date": null,
    "lost_reason": "d",
    "visible_to": "3",
    "close_time": "2015-10-14 22:40:28",
    "pipeline_id": 1,
    "won_time": null,
    "lost_time": "2015-10-14 22:40:28",
    "products_count": 1,
    "files_count": null,
    "notes_count": 1,
    "followers_count": 1,
    "email_messages_count": null,
    "activities_count": null,
    "done_activities_count": null,
    "undone_activities_count": null,
    "reference_activities_count": null,
    "participants_count": 1,
    "expected_close_date": null,
    "stage_order_nr": 1,
    "person_name": "Gilbert Runte",
    "org_name": null,
    "next_activity_subject": null,
    "next_activity_type": null,
    "next_activity_duration": null,
    "next_activity_note": null,
    "formatted_value": "343.576 €",
    "weighted_value": 343576,
    "formatted_weighted_value": "343.576 €",
    "rotten_time": null,
    "owner_name": "prueba prueba",
    "cc_email": "devpsintegration+deal5@pipedrivemail.com",
    "org_hidden": false,
    "person_hidden": false
  },
  "previous": {
    "id": 5,
    "user_id": 888118,
    "person_id": 947,
    "org_id": null,
    "stage_id": 1,
    "title": "Gilbert Runte negocio",
    "value": 343576,
    "currency": "EUR",
    "add_time": "2015-10-14 22:40:17",
    "update_time": "2015-10-14 22:40:28",
    "stage_change_time": null,
    "active": false,
    "deleted": false,
    "status": "ddfa",
    "next_activity_date": null,
    "next_activity_time": null,
    "next_activity_id": null,
    "last_activity_id": null,
    "last_activity_date": null,
    "lost_reason": "d",
    "visible_to": "3",
    "close_time": "2015-10-14 22:40:28",
    "pipeline_id": 1,
    "won_time": null,
    "lost_time": "2015-10-14 22:40:28",
    "products_count": 1,
    "files_count": null,
    "notes_count": 0,
    "followers_count": 1,
    "email_messages_count": null,
    "activities_count": null,
    "done_activities_count": null,
    "undone_activities_count": null,
    "reference_activities_count": null,
    "participants_count": 1,
    "expected_close_date": null,
    "stage_order_nr": 1,
    "person_name": "Gilbert Runte",
    "org_name": null,
    "next_activity_subject": null,
    "next_activity_type": null,
    "next_activity_duration": null,
    "next_activity_note": null,
    "formatted_value": "343.576 €",
    "weighted_value": 343576,
    "formatted_weighted_value": "343.576 €",
    "rotten_time": null,
    "owner_name": "prueba prueba",
    "cc_email": "devpsintegration+deal5@pipedrivemail.com",
    "org_hidden": false,
    "person_hidden": false
  },
  "event": "updated.deal"
}';

        $input = iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($request));
        $req = json_decode($input,true);

        //error_log(env(QUEUE_DRIVER));
        //$this->dispatchFrom('App\Jobs\SyncPipedriveDeals',$request);
      error_log("Job ");

        if($req['current']['status'] == 'won' && $req['previous']['status'] != 'won'){

            $clientIdPipedrive= array(
                'id_client_pipedrive' => $req['current']['person_id']
            );

          if(  Client::whereIdClientPipedrive($clientIdPipedrive)->first() != null  ){
            $task= new UpdateClientFromPipedrive($req, Auth::user()->id);
            error_log("Job Update");

            $task->handle();
          }else{
            $task= new InsertClientFromPipedrive($req, Auth::user()->id);
            error_log("Insert");
            $task->handle();

          }



            //
        }

    }

}
