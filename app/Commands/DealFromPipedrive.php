<?php

namespace pintegration\Commands;

    use pintegration\State;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Contracts\Bus\SelfHandling;
    use Illuminate\Contracts\Queue\ShouldBeQueued;
    use GuzzleHttp;
    use pintegration\Client;
    use Illuminate\Support\Facades\Log;
    use Pipedrive\Pipedrive;
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
class DealFromPipedrive extends Command implements SelfHandling, ShouldBeQueued
{
    use InteractsWithQueue, SerializesModels;

    protected $request;
    protected $user_id;
    protected $user;
    protected $clientId;
    protected $clientData;
    protected $dealId;
    protected $flag;
    const ADD_CART = 0; //Sync all products for all users.
    const NOT_ADD_CART = 1; //Sync all products for all users.

    public function __construct($request,$user_id,$flag = self::ADD_CART)
    {
        $this->request = $request;
        $this->user_id=$user_id;
        $this->user = User::find($user_id);
        $this->clientId = $request['current']['person_id'];
        $this->dealId = $this->request['current']['id'];
        $this->flag=$flag;

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

        $this->checkClientName($client);
        $client->email     = $this->clientData['data']['email'][0]['value'];
        $client->password  = $faker->password(6,10);
        $client->id_client_pipedrive = $this->clientId;

        $client->save();

        return $client;

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


    protected function updateClient()
    {
        $client = Client::whereIdClientPipedrive(array('id_client_pipedrive' => $this->clientId))->first();

        $this->checkClientName($client);
        $client->email     = $this->clientData['data']['email'][0]['value'];

        $client->update();

        return $client;

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

        foreach($specialStates as $key => $value){
            $match1 = strpos($key, $state);
            $match2 = strpos($state, $key);
            $match3 = strpos($value, $state);
            $match4 = strpos($state, $value);
            if($match1 !== false || $match2 !== false || $match3 !== false || $match4 !== false ){
                return $key;
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
}
