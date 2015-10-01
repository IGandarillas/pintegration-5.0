<?php

namespace pintegration\Http\Controllers;

use Illuminate\Support\Facades\Request;
use pintegration\Client;
use pintegration\Http\Requests;
use Auth;
use GuzzleHttp;
use pintegration\Jobs\InsertClientFromPipedrive;

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
           $json = '{"v": 1, "matches_filters": { "current": [ 2 ] }, "meta": { "v": 1, "action": "updated", "object": "deal", "id": 71, "company_id": 598923, "user_id": 830118, "host": "igandarillas.pipedrive.com", "timestamp": 1443045589, "permitted_user_ids": [ 830118 ], "trans_pending": false, "is_bulk_update": false, "matches_filters": { "current": [ 2 ] } }, "retry": 0, "current": { "id": 71, "user_id": 830118, "person_id": 65, "org_id": null, "stage_id": 1, "title": "Johnny negocio", "value": 123, "currency": "EUR", "add_time": "2015-09-22 20:49:06", "update_time": "2015-09-23 21:59:49", "stage_change_time": null, "active": false, "deleted": false, "status": "won", "next_activity_date": null, "next_activity_time": null, "next_activity_id": null, "last_activity_id": null, "last_activity_date": null, "lost_reason": null, "visible_to": "3", "close_time": "2015-09-23 21:59:49", "pipeline_id": 1, "won_time": "2015-09-23 21:59:49", "lost_time": null, "products_count": null, "files_count": null, "notes_count": 0, "followers_count": 1, "email_messages_count": null, "activities_count": null, "done_activities_count": null, "undone_activities_count": null, "reference_activities_count": null, "participants_count": 1, "expected_close_date": null, "stage_order_nr": 1, "person_name": "Johnny", "org_name": null, "next_activity_subject": null, "next_activity_type": null, "next_activity_duration": null, "next_activity_note": null, "formatted_value": "123 €", "rotten_time": null, "weighted_value": 123, "formatted_weighted_value": "123 €", "owner_name": "IGandarillas", "cc_email": "igandarillas+deal71@pipedrivemail.com", "org_hidden": false, "person_hidden": false }, "previous": {
        "id": 71,
        "user_id": 830118,
        "person_id": 65,
        "org_id": null,
        "stage_id": 1,
        "title": "Johnny negocio",
        "value": 123,
        "currency": "EUR",
        "add_time": "2015-09-22 20:49:06",
        "update_time": "2015-09-23 21:59:48",
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
        "products_count": null,
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
        "person_name": "Johnny",
        "org_name": null,
        "next_activity_subject": null,
        "next_activity_type": null,
        "next_activity_duration": null,
        "next_activity_note": null,
        "formatted_value": "123 €",
        "weighted_value": 123,
        "formatted_weighted_value": "123 €",
        "rotten_time": null,
        "owner_name": "IGandarillas",
        "cc_email": "igandarillas+deal71@pipedrivemail.com",
        "org_hidden": false,
        "person_hidden": false
      },
      "event": "updated.deal"
    }';
        error_log("Añadir a cola");
        //$req = $request->all();
        $json = iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($json));

       // dd( );
        $req = GuzzleHttp\json_decode($json,true);

        error_log('Status: '.$req['current']['status']);
        //$this->dispatchFrom('App\Jobs\SyncPipedriveDeals',$request);

        if($req['current']['status'] == 'won' && $req['previous']['status'] != 'won'){

            $clientIdPipedrive= array(
                'id_client_pipedrive' => $req['current']['person_id']
            );

            if(  Client::whereIdClientPipedrive($clientIdPipedrive)->first() != null  ){
                //Get
                dd('encontrad');
                return;
            }else{

                error_log("job");
                $this->dispatch(new InsertClientFromPipedrive($req));

            }
            //
        }

    }

}
