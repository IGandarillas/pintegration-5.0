<?php namespace Tools;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use pintegration\Item;
use pintegration\User;
use PSWebS\PrestaShopWebservice;
use PSWebS\PrestaShopWebserviceException;
use pintegration\State;


class Tools
{
    protected $user_id=null;
    protected $user;
    const PIPEDRIVE = 'Pipedrive';
    const CUSTOMERS = 'customers';
    const CARTS = 'carts';
    const ADDRESSES = 'addresses';
    const CARRITO_CREADO = 'Carrito creado ';
    const CARRITO_ERROR = 'Error carrito ';
    const CLIENTE_CREADO = 'Cliente creado ';
    const CLIENTE_ERROR = 'Error cliente ';
    const CLIENTE_ACTUALIZADO = 'Cliente actualizado ';
    const DIRECCION_CREADA = 'Direccion creada ';
    const DIRECCION_ERROR = 'Error direccion ';
    const DIRECCION_ACTUALIZADA = 'Direccion actualizada ';
    const NO_DEFINIDO = 'Faltan datos por definir ';

    public function __construct($user_id){

        $this->user_id=$user_id;
        $this->user = User::find($user_id);

    }
    public function checkClientName($resources,$client)
    {
        if (!isset($client->firstname) || $client->firstname == null) {
            $resources->firstname = self::PIPEDRIVE;
            $resources->lastname = $client->lastname;
        } elseif (!isset($client->lastname) || $client->lastname == null) {
            $resources->firstname = $client->firstname;
            $resources->lastname = self::PIPEDRIVE;
        }else{
            $resources->firstname = $client->firstname;
            $resources->lastname = $client->lastname;
        }
    }
    public function hasClientRequiredFields($client)
    {
        return isset($client->email,$client->firstname,$client->lastname);
    }

    public function addClient($client){
        if($this->hasClientRequiredFields($client)) {
            $xml = $this->getBlankSchema(self::CUSTOMERS);
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
                $msg = 'Prestashop fallo al crear el cliente '.$client->firstname.' '.$client->lastname;
                Log::error($msg);
                $this->notifySuccess($msg,self::CLIENTE_ERROR);
                return null;
            }

            $response = $xml->children()->children();
            $client->id_client_prestashop = $response->id;
            $client->secure_key           = $response->secure_key;
            $client->password             = $response->passwd;

            $client->update();
                $clientArray = (string)$client;
                $msg = "Cliente creado: \n" . $clientArray;
                Log::info($msg);
                $this->notifySuccess($msg,self::CLIENTE_CREADO);

        }else{
            $msg = 'Cliente no creado. Deben definirse los campos email, nombre y apellidos.';
            Log::error($msg);
            $this->notifySuccess($msg,self::CLIENTE_ERROR);
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
                $msg = "Algo fue mal en Prestashop: \n Prestashop no acepta nombre y apellidos con más de 32 caracteres cada uno.". $e->getMessage();
                Log::error($msg);
                $this->notifySuccess($msg,self::CLIENTE_ERROR);
                return null;
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
                $msg = 'Algo fue mal en Prestashop.';
                Log::error($msg);
                $this->notifySuccess($msg,self::CLIENTE_ERROR);
                return null;
            }
            $clientArray = (string) $client;
            $msg = "Cliente actualizado: \n" . $clientArray;
            Log::info($msg);
            $this->notifySuccess($clientArray,self::CLIENTE_ACTUALIZADO);
        }else{
            //$client->email = 'pipedrive'.$client->firstname.$client->id_client_pipedrive
            $msg = 'Cliente no actualizado. Deben definirse los campos email, nombre y apellidos.';
            Log::error($msg);
            $this->notifySuccess($msg,self::CLIENTE_ERROR.'. '.self::NO_DEFINIDO);
        }
    }


    public function checkCartParameters($client){
        $address = $client->direccion;
         return isset(
             $address->id_address_prestashop
         );
        return true;
    }
    public function checkAddress($direccion){
        return isset($direccion->id_address_prestashop, $direccion->id_address_prestashop);
    }
    public function addCart($client,$order){

            $xml = $this->getBlankSchema(self::CARTS);
            $resources = $xml->children()->children();

            $direccion = $client->direccion;

            if($this->checkAddress($direccion)) {

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
                if ($client->id_client_prestashop != 0)
                $xml = $connectClient->add($opt);
            } catch (PrestaShopWebserviceException $ex) {
                $msg = 'Algo fue mal en Prestashop: '. $ex->getMessage();
                Log::error($msg);
                $this->notifySuccess($msg,self::CARRITO_ERROR);
            }

            if (isset($xml->children()->children()->id)) {
                if ($client->id_client_prestashop == 0)
                    Log::error('Carrito no creado. Deben definirse los campos email, dirección, teléfono, nombre y apellidos.');
                else if($xml->children()->children()->id = !0) {
                    if( !$this->checkAddress($direccion) ){
                        $clientArray = (string) $client;
                        $msg = "Carrito creado sin dirección: \n " .$clientArray;
                        Log::warning($msg);
                        $this->notifySuccess($msg,self::CARRITO_CREADO.'. '.self::NO_DEFINIDO);
                    }else{
                        $clientArray = (string) $client;
                        $msg = "Carrito creado:  \n" .$clientArray;
                        Log::info($msg);
                        $this->notifySuccess($msg,self::CARRITO_CREADO);
                        return $xml->children()->children()->id;//Process response.
                    }

                }else {
                    $clientArray = (string) $client;
                    $msg = "Error al crear carrito: \n". $clientArray;
                    Log::error($msg);
                    $this->notifySuccess($msg,self::CARRITO_ERROR);
                }
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

            $xml = $this->getBlankSchema(self::ADDRESSES);
            $resources = $xml->children()->children();

            $direccion = $client->direccion;

            $resources->id_customer = $client->id_client_prestashop;
            $resources->firstname = $client->firstname;
            $resources->lastname = $client->lastname;
            $resources->address1 = $direccion->address1;
            $resources->city = $direccion->city;
            $resources->id_country = '6';
            $resources->id_state = $this->fillAddressState($direccion);
            $resources->postcode = $direccion->postcode;
            $resources->phone_mobile = $this->fillAddressMobilePhone($direccion);
            $resources->alias = htmlspecialchars('Direccion '.$client->id_client_prestashop,ENT_NOQUOTES);

            $opt = array('resource' => 'addresses');
            $opt['postXml'] = $xml->asXML();
            $connectClient = $this->initConnection();

            try {
                $xml = $connectClient->add($opt);
            } catch (PrestaShopWebserviceException $ex) {
                $msg = "Algo fue mal en Prestashop: \n". $ex->getMessage();
                Log::error($msg);
                $this->notifySuccess($msg,self::DIRECCION_ERROR);
                return;
            }
            //Store response
            $direccion->id_address_prestashop = $xml->children()->children()->id;//Process response.
            $direccion->update();

            $msg = ("Dirección creada: \n" . $client->firstname . " " . $client->lastname);
            Log::info($msg);
            $this->notifySuccess($msg,self::DIRECCION_CREADA);
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
        $resources->id_state = $this->fillAddressState($direccion);

        $resources->phone_mobile = $this->fillAddressMobilePhone($direccion);
        $resources->postcode = $direccion->postcode;

        $opt = array(
            'resource' => 'addresses',
            'id' => $direccion->id_address_prestashop
        );
        $opt['putXml'] = $xml->asXML();

        try {
            $xml = $connectClient->edit($opt);
        } catch (PrestaShopWebserviceException $ex) {
            $msg = 'Algo fue mal en Prestashop: '. $ex->getMessage();
            Log::error($msg);
            $this->notifySuccess($msg,self::DIRECCION_ERROR);
        }
        $direccion->id_address_prestashop = $xml->children()->children()->id;//Process response.
        $direccion->update();
        $clientArray = (string) $client;
        $msg = "Direccion actualizada: \n" .$clientArray;
        Log::info($msg);
        $this->notifySuccess($msg,self::DIRECCION_ACTUALIZADA);

    }
    public function fillAddressState($direccion){
        if(isset($direccion->id_state)){
            $state = State::find($direccion->id_state);
            return $state->id_prestashop;
        } else
            return 0;
    }
    public function fillAddressMobilePhone($direccion){
        if(isset($direccion->phone_mobile))
            return $direccion->phone_mobile;
         else
            return 0;
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
    protected function notifyError($e){
        Mail::raw( $e, function($message) {
            $message->to($this->user->email_log, 'psintegration')->subject('Error');
        });
    }
    protected function notifySuccess($e,$head){
        if($this->isJson($e)){
            $e = json_encode($e, JSON_PRETTY_PRINT);
        }
        Mail::raw( $e, function($message) use ($head){
            $message->to($this->user->email_log, $head)->subject($head);
        });
    }
    function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

}
