<?php

namespace pintegration\Commands;


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
        Log::info('new client');
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
        Log::info('existsClient');
        $dealProductsData = $this->getDealProductsData($this->dealId);
        $client           = $this->updateClient();
        $address          = $client->direccion;

        if($this->isAddress())
            if($this->isCompleteAddress())
                $this->addAddress($client);

        if( isset($client) ) {
            $tools = new Tools( $this->user_id );
            $tools->editClient( $client );

            if(!isset($address, $address->id_address_prestashop) || $address->id_address_prestashop == 0)
                $tools->addAddress($client);
            else
                $tools->editAddress($client);

            $tools->addCart($client,$dealProductsData);

        }
    }

    /**
     * Add new client to db;
     * @return static
     */
    protected function createClient()
    {
        Log::info('createClient');
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

        Log::info('addAddress');

        $address = Direccion::firstOrNew( array( 'client_id' => $client->id ) );

        $address->address1 = $this->clientData['data'][$this->user->address_field];
        $address->country  = $this->clientData['data'][$this->user->address_field.'_country'];
        $address->postcode = $this->clientData['data'][$this->user->address_field.'_postal_code'];
        $address->city     = $this->clientData['data'][$this->user->address_field.'_locality'];

        $address->save();
    }

    protected function updateClient()
    {
        Log::info('updateClient');

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
        $state = $this->clientData['data'][$this->user->address_field.'_admin_area_level_1'];
        if( $state != NULL ){
            try {
                $idState = State::whereName($state)->first()->id_prestashop;
                if (isset($idState) && $idState != NULL)
                    return $idState;
            }catch(\PDOException $e){
                return 0;
            }
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
