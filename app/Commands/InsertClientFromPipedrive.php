<?php

namespace pintegration\Commands;


use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use GuzzleHttp;
use pintegration\Client;
use pintegration\User;
use Symfony\Component\Finder\Shell\Command;
use Tools\Tools;
use pintegration\Direccion;
use Faker;
class InsertClientFromPipedrive extends Command implements SelfHandling, ShouldBeQueued
{
    use InteractsWithQueue, SerializesModels;

    protected $request;
    protected $user_id;
    protected $user;

    public function __construct($request,$user_id){
        $this->request = $request;
        $this->user_id=$user_id;
        $this->user = User::find($user_id);
    }
    public function handle(){
        $newClientId = $this->request['current']['person_id'];//Id Pipedrive
        error_log($newClientId);
        $client = $this->createNewClient($newClientId);

        $tools = new Tools($this->user_id);
        $tools->addClient($client);
        $tools->addAddress($client);

        $dealId = $this->request['current']['id'];
        $orderData = $this->getOrderData($dealId);
        $tools->addCart($client,$orderData);
        //$tools->addOrder($client,$orderData);
        error_log("FIN");

    }
    protected function createNewClient($newClientId){
        $clientData = $this->getClientData($newClientId);
        error_log($clientData['data']['first_name']);
        error_log($clientData['data']['last_name']);
        error_log($clientData['data']['email'][0]['value']);

        $faker = Faker\Factory::create();

        if($this->isAddress($clientData)) {
            $newClient = new Client();
            $newClient->firstname = $clientData['data']['first_name'];
            $newClient->lastname = $clientData['data']['last_name'];
            $newClient->email = $clientData['data']['email'][0]['value'];
            $newClient->password = $faker->password(6,10);
            $newClient->id_client_pipedrive = $newClientId;
            $newClient->user_id = $this->user_id;
            $newClient->save();
            error_log('cliente creado');
            if($this->isCompleteAddress($clientData)) {
                $this->createAddress($newClient, $clientData);
            }
            return $newClient;
        }
        error_log('Have no address');
    }
    protected function createAddress($newClient,$clientData){
        $address = new Direccion();
        $address->client_id = $newClient->id;
        $address->address1 = $clientData['data'][$this->user->address_field];
        $address->country = $clientData['data'][$this->user->address_field.'_country'];
        $address->postcode = $clientData['data'][$this->user->address_field.'_postal_code'];
        $address->city = $clientData['data'][$this->user->address_field.'_locality'];
        $address->id_state = $this->getState($clientData);
        error_log($address->city);
        error_log($address->client_id);
        $address->save();
    }
    protected function getClientData($id)
    {
        $url = 'https://api.pipedrive.com/v1/persons/'.$id.'?api_token='.$this->user->pipedrive_api;
        error_log($url);
        return $this->getData($url);
    }
    protected function getDealData($id){
        $url = 'https://api.pipedrive.com/v1/deals/'.$id.'/products?start=0&api_token='.$this->user->pipedrive_api;
        return $this->getData($url);
    }
    protected function getOrderData($id){
        $url = 'https://api.pipedrive.com/v1/deals/'.$id.'/products?start=0&include_product_data=0&api_token='.$this->user->pipedrive_api;
        return $this->getData($url);
    }
    protected function isAddress($clientData){
        return ($clientData['data'][$this->user->address_field.'_formatted_address'] != NULL);
    }
    //Null fields mean malformed address.
    protected function isCompleteAddress($clientData){
        return (
            $clientData['data'][$this->user->address_field.'_country'] != NULL &&
            $clientData['data'][$this->user->address_field.'_postal_code'] != NULL &&
            $clientData['data'][$this->user->address_field.'_locality'] != NULL
        );
    }
    protected function getState($clientData){
        $state = $clientData['data'][$this->user->address_field.'_admin_area_level_1'];
        if( $state != NULL ){
            $idState = State::whereName($state)->first()->id_prestashop;
            if(isset($idState) && $idState != NULL)
                return $idState;
        }
        return 0;
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
