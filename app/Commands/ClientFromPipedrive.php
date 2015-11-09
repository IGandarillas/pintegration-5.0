<?php namespace pintegration\Commands;

use pintegration\State;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use GuzzleHttp;
use Illuminate\Support\Facades\Log;
use pintegration\Client;
use pintegration\User;
use Pipedrive\Pipedrive;
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


    public function __construct($request,$user_id)
    {
        $this->request = $request;
        $this->user_id=$user_id;
        $this->user = User::find($user_id);
        $this->clientId = $request['current']['id'];

    }
    public function handle()
    {
        $this->clientData['data'] = $this->request['current'];
        $client = Client::where( array('id_client_pipedrive' => $this->clientId) )->first();

        if(  $client != null && $client->id_client_prestashop != null )
            $this->existsClient();
    }

    protected function existsClient()
    {
        $client           = $this->updateClient();

        if($this->isAddress())
            if($this->isCompleteAddress())
                $this->addAddress($client);
        $address          = $client->direccion;
        if( isset($client) ) {
            $tools = new Tools( $this->user_id );
            if ( isset($client->id_client_prestashop) || $client->id_client_prestashop != 0 )
                $tools->editClient( $client );
            else
                return;

            if(isset($address, $address->id_address_prestashop))
                if($address->id_address_prestashop != 0)
                    $tools->editAddress($client);
                else
                    $tools->addAddress($client);

        }
    }
    protected function getNameFirstWord($name){
        $nameSplit = explode(" ", $name);
        $firstname = implode(" ",array_slice($nameSplit,0,1));
        $lastname  = implode(" ",array_slice($nameSplit,1));
        return array($firstname,$lastname);
    }
    protected function checkClientName($client){
        $name = $this->clientData['data']['name'];

            $pd = new Pipedrive();
            $composedName = $pd->searchName($name);
            if($composedName!=0){
                $client->firstname = $composedName[0];
                $client->lastname  = $composedName[1];
                Log::info('FirstName = '.$composedName[0].' LastName = '.$composedName[1]);
            }else{
                $composedName = $this->getNameFirstWord($name);
                $client->firstname = $composedName[0];
                $client->lastname  = $composedName[1];
                Log::info('Posible fallo en nombre FirstName = '.$composedName[0].' LastName = '.$composedName[1]);
            }
    }

    protected function updateClient()
    {
        $client = Client::whereIdClientPipedrive(array('id_client_pipedrive' => $this->clientId))->first();

        $this->checkClientName($client);
        $client->email     = $this->clientData['data']['email'][0]['value'];

        $client->update();

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

        if( isset($idState->id) )
            if ($idState->id != 0)
                $address->id_state = $idState->id;

        $address->save();
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
            $specialState = $this->checkSpecialState($state);
            if($specialState !== false) {
                return $this->getStateFromDb($specialState);
            }else
                return $this->getStateFromDb($state);
        }
        return 0;
    }

    protected function checkSpecialState($state){
        $specialStates = array(
            utf8_encode('A Coruña') => utf8_encode('La Coruña'),
            utf8_encode('Castelló') => utf8_encode('Castellón'),
            utf8_encode('Nafarroa') => utf8_encode('Navarra'),
            utf8_encode('Alacant')  => utf8_encode('Alicante'),
            utf8_encode('Balears')  => utf8_encode('Baleares'),
            utf8_encode('Bizkaia')  => utf8_encode('Vizcaya'),
            utf8_encode('Gipuzkoa') => utf8_encode('Guipuzcoa'),
            utf8_encode('València') => utf8_encode('Valencia'),
        );

        if(isset($state) ){
            foreach($specialStates as $key => $value){
                $match1 = strpos($key, $state);
                $match2 = strpos($state, $key);
                $match3 = strpos($value, $state);
                $match4 = strpos($state, $value);
                if($match1 !== false || $match2 !== false || $match3 !== false || $match4 !== false ){
                    return $key;
                }
            }
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


}