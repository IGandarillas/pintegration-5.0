<?php

namespace pintegration\Commands;


use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use GuzzleHttp;
use pintegration\Client;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Finder\Shell\Command;
use Tools\Tools;
use pintegration\Direccion;
class InsertClientFromPipedrive extends Command implements SelfHandling, ShouldBeQueued
{
    use InteractsWithQueue, SerializesModels;

    protected $request;
    protected $user_id;

    public function __construct($request,$user_id)    {
        $this->request = $request;
        $this->user_id=$user_id;
    }
    public function handle(){
        error_log("hbdle");
        $newClientId = $this->request['current']['person_id'];//Id Pipedrive
        error_log($newClientId);
        $client = $this->createNewClient($newClientId);
        error_log('User IDd '.$this->user_id);
        $tools = new Tools($this->user_id);
        $tools->addClient($client);
        $tools->addAddress($client);
        error_log("FIN");

    }
    protected function createNewClient($newClientId){
        $clientData = $this->getClientData($newClientId);
        error_log($clientData['data']['first_name']);
        error_log($clientData['data']['last_name']);
        error_log($clientData['data']['email'][0]['value']);
        if($this->isAddress($clientData)) {
            $newClient = new Client();
            $newClient->firstname = $clientData['data']['first_name'];
            $newClient->lastname = $clientData['data']['last_name'];
            $newClient->email = $clientData['data']['email'][0]['value'];
            $newClient->password = 'needAutoGenPass';
            $newClient->id_client_pipedrive = $newClientId;
            $newClient->user_id = '1';
            $newClient->save();
            error_log('cliente creado');
            $address = new Direccion();
            $address->client_id = $newClient->id;//?
            $address->address1 = $clientData['data']['57cda8344ed4defb3ad99df35e755b8cfc64c248'];
            $address->country = $clientData['data']['57cda8344ed4defb3ad99df35e755b8cfc64c248_country'];
            $address->postcode = $clientData['data']['57cda8344ed4defb3ad99df35e755b8cfc64c248_postal_code'];
            $address->city = $clientData['data']['57cda8344ed4defb3ad99df35e755b8cfc64c248_locality'];
            error_log($address->city);
            error_log($address->client_id);
            $address->save();

            return $newClient;
        }
    }

    protected function getClientData($id)
    {
        $url = 'https://api.pipedrive.com/v1/persons/'.$id.'?api_token=e9748c75a8b8a2179354dd2226665332c04c71ea';
        return $this->getData($url);
    }
    protected function getDealData($id){
        $url = 'https://api.pipedrive.com/v1/deals/'.$id.'/products?start=0&api_token=e9748c75a8b8a2179354dd2226665332c04c71ea';
        return $this->getData($url);
    }

    protected function isAddress($clientData){
        return ($clientData['data']['57cda8344ed4defb3ad99df35e755b8cfc64c248_formatted_address'] != NULL);
    }

    protected function getData($url){
        error_log($url);
        $guzzleClient = new GuzzleHttp\Client();
        try {
            $response = $guzzleClient->get($url);
            error_log($response);
            if(  $response!=null && $response->getStatusCode() == 200 ){
                return json_decode($response->getBody(),true);
            }
        }catch(GuzzleHttp\Exception\ClientException $e){
            error_log($e->getMessage());
        }
    }
}
