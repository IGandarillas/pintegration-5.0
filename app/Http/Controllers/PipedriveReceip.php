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
      2
    ]
  },
  "meta": {
    "v": 1,
    "action": "updated",
    "object": "deal",
    "id": 21,
    "company_id": 623011,
    "user_id": 867597,
    "host": "igandarillas2.pipedrive.com",
    "timestamp": 1444755162,
    "permitted_user_ids": [
      867597
    ],
    "trans_pending": false,
    "is_bulk_update": false,
    "matches_filters": {
      "current": [
        2
      ]
    }
  },
  "retry": 0,
  "current": {
    "id": 21,
    "user_id": 867597,
    "person_id": 904,
    "org_id": null,
    "stage_id": 1,
    "title": "Gwendolyn Baumbach deal",
    "value": 17.51,
    "currency": "EUR",
    "add_time": "2015-10-13 16:28:04",
    "update_time": "2015-10-13 16:52:42",
    "stage_change_time": null,
    "active": false,
    "deleted": false,
    "status": "won",
    "next_activity_date": null,
    "next_activity_time": null,
    "next_activity_id": null,
    "last_activity_id": null,
    "last_activity_date": null,
    "lost_reason": null,
    "visible_to": "3",
    "close_time": "2015-10-13 16:52:42",
    "pipeline_id": 1,
    "won_time": "2015-10-13 16:52:42",
    "lost_time": null,
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
    "person_name": "Gwendolyn Baumbach",
    "org_name": null,
    "next_activity_subject": null,
    "next_activity_type": null,
    "next_activity_duration": null,
    "next_activity_note": null,
    "formatted_value": "17,51 €",
    "rotten_time": null,
    "weighted_value": 17.51,
    "formatted_weighted_value": "17,51 €",
    "owner_name": "Ismael Gandarillas Pérez",
    "cc_email": "igandarillas2+deal21@pipedrivemail.com",
    "org_hidden": false,
    "person_hidden": false
  },
  "previous": {
    "id": 21,
    "user_id": 867597,
    "person_id": 904,
    "org_id": null,
    "stage_id": 1,
    "title": "Gwendolyn Baumbach deal",
    "value": 17.51,
    "currency": "EUR",
    "add_time": "2015-10-13 16:28:04",
    "update_time": "2015-10-13 16:52:41",
    "stage_change_time": null,
    "active": true,
    "deleted": false,
    "status": "open",
    "next_activity_date": null,
    "next_activity_time": null,
    "next_activity_id": null,
    "last_activity_id": null,
    "last_activity_date": null,
    "lost_reason": null,
    "visible_to": "3",
    "close_time": null,
    "pipeline_id": 1,
    "won_time": null,
    "lost_time": null,
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
    "person_name": "Gwendolyn Baumbach",
    "org_name": null,
    "next_activity_subject": null,
    "next_activity_type": null,
    "next_activity_duration": null,
    "next_activity_note": null,
    "formatted_value": "17,51 €",
    "weighted_value": 17.51,
    "formatted_weighted_value": "17,51 €",
    "rotten_time": null,
    "owner_name": "Ismael Gandarillas Pérez",
    "cc_email": "igandarillas2+deal21@pipedrivemail.com",
    "org_hidden": false,
    "person_hidden": false
  },
  "event": "updated.deal"
}';

        $input = iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($request));
        $req = json_decode($input,true);

        //error_log(env(QUEUE_DRIVER));
        //$this->dispatchFrom('App\Jobs\SyncPipedriveDeals',$request);

        if($req['current']['status'] == 'won' && $req['previous']['status'] != 'won'){

            $clientIdPipedrive= array(
                'id_client_pipedrive' => $req['current']['person_id']
            );


                $task= new Insertpdprueba($req, '1');
                $task->handle();


            //
        }

    }

}
