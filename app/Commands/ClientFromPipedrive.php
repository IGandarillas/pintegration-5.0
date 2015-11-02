<?php

namespace pintegration\Commands;

use pintegration\State;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use GuzzleHttp;
use Illuminate\Support\Facades\Log;
use pintegration\Client;
use pintegration\User;
use Symfony\Component\Finder\Shell\Command;
use Tools\Tools;
use pintegration\Direccion;
use Faker;

/**
 * Get provided data from pipedrive and store it.
 * Class ClientFromPipedrive
 * @package pintegration\Commands
 */
class ClientFromPipedrive extends Command implements SelfHandling, ShouldBeQueued
{
    use InteractsWithQueue, SerializesModels;

    protected $request;
    protected $user_id;
    protected $user;
    protected $clientId;
    protected $clientData;
    protected $dealId;


    public function __construct($request,$user_id)
    {
        $this->request = $request;
        $this->user_id=$user_id;
        $this->user = User::find($user_id);
        $this->clientId = $request['current']['person_id'];
        $this->dealId = $this->request['current']['id'];

    }
    public function handle()
    {
        $this->clientData = $this->getClientData($this->clientId);

        $client = Client::where( array('id_client_pipedrive' => $this->clientId) )->first();

        if(  $client != null && $client->id_client_prestashop != null )
            $this->existsClient();
        else
            $this->newClient();
    }

    protected function newClient()
    {
        $client = $this->createClient();

        if($this->isAddress())
            if($this->isCompleteAddress())
                $this->addAddress($client);

        $dealProductsData = $this->getDealProductsData($this->dealId);

        $tools = new Tools($this->user_id);
        $tools->addClient($client);
        $tools->addAddress($client);
        $tools->addCart($client,$dealProductsData);

    }
    protected function existsClient()
    {
        $dealProductsData = $this->getDealProductsData($this->dealId);
        $client           = $this->updateClient();

        if($this->isAddress())
            if($this->isCompleteAddress())
                $this->addAddress($client);
        $address          = $client->direccion;
        if( isset($client) ) {
            $tools = new Tools( $this->user_id );
            if(isset($client->id_client_prestashop) || $client->id_client_prestashop != 0)
                $tools->editClient( $client );
            else
                $tools->addClient($client);

            if(isset($address, $address->id_address_prestashop))
                if($address->id_address_prestashop != 0)
                    $tools->editAddress($client);
                else
                    $tools->addAddress($client);


            $tools->addCart($client,$dealProductsData);

        }
    }

    /**
     * Add new client to db;
     * @return static
     */
    protected function createClient()
    {
        $faker = Faker\Factory::create();

        $clientIdPipedrive= array(
            'id_client_pipedrive' => $this->clientId,
            'user_id'             => $this->user_id
        );

        $client = Client::firstOrNew($clientIdPipedrive);

        $client->firstname = $this->clientData['data']['first_name'];
        $client->lastname  = $this->clientData['data']['last_name'];
        $client->email     = $this->clientData['data']['email'][0]['value'];
        $client->password  = $faker->password(6,10);
        $client->id_client_pipedrive = $this->clientId;

        $client->save();

        return $client;

    }

    protected function addAddress($client)
    {
        $address = Direccion::firstOrNew( array( 'client_id' => $client->id ) );

        $address->address1 = $this->clientData['data'][$this->user->address_field];
        $address->country  = $this->clientData['data'][$this->user->address_field.'_country'];
        $address->postcode = $this->clientData['data'][$this->user->address_field.'_postal_code'];
        $address->city     = $this->clientData['data'][$this->user->address_field.'_locality'];
        if(isset($this->clientData['data']['phone']))
            $address->phone_mobile = $this->clientData['data']['phone'][0]['value'];
        $idState = $this->getState();
        if( isest($idState) && $idState->id !== 0)
            $address->id_state = $idState->id;
        $address->save();
    }


    protected function updateClient()
    {
        $client = Client::whereIdClientPipedrive(array('id_client_pipedrive' => $this->clientId))->first();

        $client->firstname = $this->clientData['data']['first_name'];
        $client->lastname  = $this->clientData['data']['last_name'];
        $client->email     = $this->clientData['data']['email'][0]['value'];

        $client->update();

        return $client;

    }

    protected function getClientData($id)
    {
        $url = 'https://api.pipedrive.com/v1/persons/'.$id.'?api_token='.$this->user->pipedrive_api;
        return $this->getData($url);
    }

    protected function getDealData($id)
    {
        $url = 'https://api.pipedrive.com/v1/deals/'.$id.'/products?start=0&api_token='.$this->user->pipedrive_api;
        return $this->getData($url);
    }

    protected function getDealProductsData($id)
    {
        $url = 'https://api.pipedrive.com/v1/deals/'.$id.'/products?start=0&include_product_data=0&api_token='.$this->user->pipedrive_api;
        return $this->getData($url);
    }

    protected function isAddress()
    {
        return ($this->clientData['data'][$this->user->address_field.'_formatted_address'] != NULL);
    }

    protected function isCompleteAddress()
    {
        return ( //Null fields mean malformed address.
            $this->clientData['data'][$this->user->address_field.'_country'] != NULL &&
            $this->clientData['data'][$this->user->address_field.'_postal_code'] != NULL &&
            $this->clientData['data'][$this->user->address_field.'_locality'] != NULL
        );
    }
    protected function getState()
    {
        if(isset($this->clientData['data'][$this->user->address_field.'_admin_area_level_2'])){
            $state = $this->clientData['data'][$this->user->address_field.'_admin_area_level_2'];
            if($specialState = $this->checkSpecialState($state) !== false) {
                return $this->getStateFromDb($specialState);
            }else
                return $this->getStateFromDb($state);
        }
        return 0;
    }

    protected function checkSpecialState($state){
        $specialStates = array(
            'A Coruña' => 'La Coruña',
            'Castelló' => 'Castellón',
            'Nafarroa' => 'Navarra',
            'Alacant' => 'Alicante',
            'Balears' => 'Baleares',
            'Bizkaia' => 'Vizcaya',
            'Gipuzkoa' => 'Guipuzcoa',
            'València' => 'Valencia'
        );
        foreach($specialStates as $key => $value){
            $match1 = strpos($key, $state);
            $match2 = strpos($state, $key);
            $match3 = strpos($value, $state);
            $match4 = strpos($state, $value);
            if($match1 !== false || $match2 !== false || $match3 !== false || $match4 !== false )
                return $key;
        }
        return false;
    }
    protected function getStateFromDb($state){


        foreach( State::all() as $stateDB ) { //Perform in model with sql sentence.
            $match1 = strpos($state, $stateDB->name);
            $match2 = strpos($stateDB->name, $state);
            if( $match1 !== false || $match2 !== false)
                return $stateDB;
        }
        return 0;

    }
    protected function getData($url)
    {
        $guzzleClient = new GuzzleHttp\Client();
        try {
            $response = $guzzleClient->get($url);
            if(  $response!=null && $response->getStatusCode() == 200 )
                return json_decode($response->getBody(),true);

        }catch(GuzzleHttp\Exception\ClientException $e){
            error_log($e->getMessage());
        }

    }
}
