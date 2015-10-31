<?php namespace Tools;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use pintegration\Item;
use pintegration\User;
use PSWebS\PrestaShopWebservice;
use PSWebS\PrestaShopWebserviceException;

class Tools
{
    protected $user_id=null;
    protected $user;
    const PIPEDRIVE = 'Pipedrive';
    const CUSTOMERS = 'customers';
    const CARTS = 'carts';
    const ADDRESSES = 'addresses';
    public function __construct($user_id){

        $this->user_id=$user_id;
        $this->user = User::find($user_id);

    }
    public function checkClientName($resources,$client)
    {
        if (!isset($client->firstname) || $client->firstname == null) {
            $resources->firstname = PIPEDRIVE;
            $resources->lastname = $client->lastname;
        } elseif (!isset($client->lastname) || $client->lastname == null) {
            $resources->firstname = $client->firstname;
            $resources->lastname = PIPEDRIVE;
        }else{
            $resources->firstname = $client->firstname;
            $resources->lastname = $client->lastname;
        }
    }
    public function hasClientRequiredFields($client)
    {
        return isset($client->email);
    }

    public function addClient($client){
        if($this->hasClientRequiredFields($client)) {

            $xml = $this->getBlankSchema(CUSTOMERS);
            $resources = $xml->children()->children();

            $this->checkClientName($resources,$client);

            $resources->passwd = $client->password;
            $resources->email = $client->email;
            $resources->active = true;
            $resources->id_default_group = '3';
            $resources->associations->groups->group->id = '3';

            $opt = array('resource' => 'customers');
            $opt['postXml'] = $xml->asXML();
            $connectClient = $this->initConnection();
            try {
                $xml = $connectClient->add($opt);

            } catch (PrestaShopWebserviceException $ex) { // Here we are dealing with errors
                Log::info('Prestashop falló al crear el cliente '.$client->firstname.' '.$client->lastname);
            }

            $response = $xml->children()->children();
            $client->id_client_prestashop = $response->id;
            $client->secure_key           = $response->secure_key;
            $client->password             = $response->passwd;

            $client->update();
        }else{
            //$client->email = 'pipedrive'.$client->firstname.$client->id_client_pipedrive
            Log::error('No hay email para el cliente: '.$client->firstname.' '.$client->lastname);
        }
    }
    public function editClient($client){
        if($this->hasClientRequiredFields($client)) {
                //Instantiate PSWebService.
                $connectClient = $this->initConnection();
                $opt['resource'] = 'customers';
                $opt['id'] = $client->id_client_prestashop;
            try{
                $xml = $connectClient->get($opt);
            } catch (PrestaShopWebserviceException $e) { // Here we are dealing with errors
                Log::error('Algo fue mal en Prestashop: '. $e->getMessage());
            }
            $resources = $xml->children()->children();

            $this->checkClientName($resources,$client);

            $resources->id = $client->id_client_prestashop;
            $resources->email = $client->email;


                $opt = array(
                    'resource' => 'customers',
                    'id' => $client->id_client_prestashop
                );
                $opt['putXml'] = $xml->asXML();
                $connectClient = $this->initConnection();
            try {
                $connectClient->edit($opt);
            } catch (PrestaShopWebserviceException $ex) { // Here we are dealing with errors
                Log::error('Algo fue mal en Prestashop: '. $e->getMessage());
            }
        }
    }


    public function checkCartParameters($client){
        $address = $client->direccion;
       /* return isset(
            $address->id_address_prestashop
        );*/
        return true;
    }
    public function addCart($client,$order){
        if($this->checkCartParameters($client)) {
            $xml = $this->getBlankSchema(CARTS);
            $resources = $xml->children()->children();

            $direccion = $client->direccion;

            if(isset($direccion->id_address_prestashop, $direccion->id_address_prestashop)) {

                $resources->id_address_delivery = $direccion->id_address_prestashop;
                $resources->id_address_invoice = $direccion->id_address_prestashop;

            }

            $resources->id_currency = '1';
            $resources->id_lang = '1';
            $resources->id_customer = $client->id_client_prestashop;
            $resources->id_carrier = '1';
            $resources->id_show_group = '1';

            if(isset($client->secure_key))
                $resources->secure_key = $client->secure_key;

            //Fetch deal products and save in resources.
            $count = 0;
            if(isset($order['data']))
            foreach ($order['data'] as $product) {
                if(isset($product['product_id'],$product['quantity'])) {
                    $item = Item::whereIdItemPipedrive($product['product_id'])->first();
                    if(isset($item)) {
                        $resources->associations->cart_rows->cart_row[$count]->id_product = $item->id_item_prestashop;
                        if(isset($direccion->id_address_prestashop))
                            $resources->associations->cart_rows->cart_row[$count]->id_address_delivery = $direccion->id_address_prestashop;
                        $resources->associations->cart_rows->cart_row[$count]->quantity = $product['quantity'];
                    }
                }
                $count++;
            }

                $opt = array('resource' => 'carts');
                $opt['postXml'] = $xml->asXML();

                    $connectClient = $this->initConnection();
                    try {
                    $xml = $connectClient->add($opt);
                    } catch (PrestaShopWebserviceException $ex) {
                        Log::error('Algo fue mal en Prestashop: '. $ex->getMessage());
                    }

                    if (isset($xml->children()->children()->id)) {
                        if ($xml->children()->children()->id = !0)
                            Log::info('Carrito creado para el cliente: ' . $client->firstname . ' ' . $client->lastname);
                        else
                            Log::error('Error al crear carrito para el cliente: ' . $client->firstname . ' ' . $client->lastname);
                    }

                return $xml->children()->children()->id;//Process response.

        }else{
            Log::error('Error al crear carrito para el cliente: ' . $client->firstname . ' ' . $client->lastname."\n No se ha definido una dirección para ese cliente.");
        }
    }



    public function checkAddressParameters($client){
        $address = $client->direccion;
        return isset(
            $client->direccion,
            $address->address1,
            $address->city,
            $address->postcode
        );
    }
    public function addAddress($client){
        if($this->checkAddressParameters($client)) {

            $xml = $this->getBlankSchema(ADDRESSES);
            $resources = $xml->children()->children();

            $direccion = $client->direccion;

            $resources->id_customer = $client->id_client_prestashop;
            $resources->firstname = $client->firstname;
            $resources->lastname = $client->lastname;
            $resources->address1 = $direccion->address1;
            $resources->city = $direccion->city;
            $resources->id_country = '6';
            $resources->id_state = '0';
            $resources->postcode = $direccion->postcode;
            $resources->phone = '0';
            $resources->alias = htmlspecialchars('Direccion',ENT_NOQUOTES);

            $opt = array('resource' => 'addresses');
            $opt['postXml'] = $xml->asXML();
            $connectClient = $this->initConnection();

            try {
                $xml = $connectClient->add($opt);
            } catch (PrestaShopWebserviceException $ex) {
                Log::error('Algo fue mal en Prestashop: '. $ex->getMessage());
            }
            //Store response
            $direccion->id_address_prestashop = $xml->children()->children()->id;//Process response.
            $direccion->update();
        }
    }
    public function editAddress($client){

        try {
            $connectClient = $this->initConnection();
            $opt['resource'] = 'addresses';
            $opt['id'] = $client->direccion->id_address_prestashop;
            $xml = $connectClient->get($opt);
            $resources = $xml->children()->children();
        } catch (PrestaShopWebserviceException $e) { // Here we are dealing with errors
            error_log($e->getMessage());
        }
        $direccion = $client->direccion;

        $this->checkClientName($resources,$client);

        $resources->address1 = $direccion->address1;
        $resources->city = $direccion->city;
        $resources->id_country = '6';
        $resources->id_state = '0';
        $resources->phone = '0';
        $resources->postcode = $direccion->postcode;

        $opt = array(
            'resource' => 'addresses',
            'id' => $direccion->id_address_prestashop
        );
        $opt['putXml'] = $xml->asXML();

        try {
            $xml = $connectClient->edit($opt);
        } catch (PrestaShopWebserviceException $ex) {
            Log::error('Algo fue mal en Prestashop: '. $ex->getMessage());
        }
        $direccion->id_address_prestashop = $xml->children()->children()->id;//Process response.
        $direccion->update();

    }
    public function getBlankSchema($type){
        try {   //Get Blank schema
            $connectClient = $this->initConnection();
            return $connectClient->get(array('url' => $this->user->prestashop_url . '/api/'.$type.'?schema=blank'));
        } catch (PrestaShopWebserviceException $e) { // Here we are dealing with errors
            error_log($e->getMessage());
        }
    }
    protected function initConnection(){
        $user = User::find($this->user_id);
        return new PrestaShopWebservice($user->prestashop_url, $user->prestashop_api, false);
    }

}