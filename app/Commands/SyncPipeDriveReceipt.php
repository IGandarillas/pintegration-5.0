<?php

namespace pintegration\Commands;

use pintegration\Commands\Command;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use pintegration\Client;
use pintegration\Http\Requests;
use Auth;
use Illuminate\Support\Facades\Artisan;
use GuzzleHttp;

class SyncPipeDriveReceipt extends Command implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct($jsonReq)
    {
        $newClientId = $jsonReq['current']['person_id'];//Id Pipedrive
        $client = $this->createNewClient($newClientId);
        error_log("Creacion de argumentos para el comando");
        $arguments = array([
            'deal' => $jsonReq,
            'client' => $client
        ]);
        error_log("Cliente creado con id: ".$client->id);
        Artisan::call('command:pscreateoneclient', $arguments);
    }


    protected function getClientData($id)
    {
        $guzzleClient = new GuzzleHttp\Client();
        try {
            $response = $guzzleClient->get('https://api.pipedrive.com/v1/persons/'.$id.'?api_token='.Auth::user()->pipedrive_api);
            if(  $response!=null && $response->getStatusCode() == 200 ){
                error_log("200OK GET PD");
                return json_decode($response->getBody(),true);
            }
        }catch(GuzzleHttp\Exception\ClientException $e){
            error_log($e->getMessage());
        }

    }
    protected function createNewClient($newClientId){

        $clientData = $this->getClientData($newClientId);
        error_log("Client Data");

        //error_log($clientData['data']['first_name']);
        $newClient = new Client();
        //$ISMA='jesus';
        //file_put_contents("php://stderr",  $ISMA.$clientData['data']['first_name']);
        error_log("Instanciate");
        $newClient->firstname = $clientData['data']['first_name'];
        $newClient->lastname = $clientData['data']['last_name'];
        $newClient->email = $clientData['data']['email'][0]['value'];
        $newClient->password = 'needAutoGenPass';
        $newClient->id_client_pipedrive = $newClientId;
        $newClient->user_id = Auth::user()->id;
        $newClient = $newClient->save();
        error_log("salvar");
        return $newClient;
    }

}
