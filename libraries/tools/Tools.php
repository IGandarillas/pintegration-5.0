<?php


/**
 * Created by PhpStorm.
 * User: Yunkorff
 * Date: 24/09/2015
 * Time: 22:47
 */

namespace Tools;
use GuzzleHttp\Client;
use pintegration\Item;
use pintegration\User;
use PSWebS\PrestaShopWebservice;

use Auth;

class Tools
{
    protected $user_id=null;

    public function __construct($user_id){
        $this->user_id=$user_id;
    }
    public function addClient($client){

        try
        {   //Get Blank schema
            $connectClient = $this->initConnection();
            $xml = $connectClient->get(array('url' => 'http://osteox.esy.es/prestashop/api/customers?schema=blank'));
            $resources = $xml->children()->children();
        }
        catch (PrestaShopWebserviceException $e)
        { // Here we are dealing with errors
            error_log($e->getMessage());
        }
        if($client->firstname == null){
            $resources->firstname = $client->lastname;
            $resources->lastname = $client->lastname;
        }elseif( $client->lastname == null ){
            $resources->firstname = $client->firstname;
            $resources->lastname = $client->firstname;
        }else {
            $resources->firstname = $client->firstname;
            $resources->lastname = $client->lastname;
        }

        $resources->passwd = $client->password;
        $resources->email = $client->email;
        error_log('client se pasa? : ' .$resources->email);
        try {
            $opt = array('resource' => 'customers');
            $opt['postXml'] = $xml->asXML();
            $connectClient = $this->initConnection();
            $xml = $connectClient->add($opt);
            $client->id_client_prestashop = $xml->children()->children()->id;//Process response.
            $client->update();
        }
        catch (PrestaShopWebserviceException $ex)
        { // Here we are dealing with errors
            echo $ex->getMessage();
        }
    }

    public function addAddress($client){
        try
        {   //Get Blank schema
            $connectClient = $this->initConnection();
            $xml = $connectClient->get(array('url' => 'http://osteox.esy.es/prestashop/api/addresses?schema=blank'));
            $resources = $xml->children()->children();
        }
        catch (PrestaShopWebserviceException $e)
        { // Here we are dealing with errors
            error_log($e->getMessage());
        }
        $direccion = $client->direccion;
        $resources->id_customer = $client->id_client_prestashop;
        $resources->firstname = $client->firstname;
        $resources->lastname = $client->lastname;
        $resources->address1 = $direccion->address1;
        $resources->city = $direccion->city;
        $resources->id_country = '6';
        $resources->postcode = $direccion->postcode;
        $resources->alias = 'Alias';
        try {
            $opt = array('resource' => 'addresses');
            $opt['postXml'] = $xml->asXML();
            $xml = $connectClient->add($opt);
            $direccion->id_address_prestashop = $xml->children()->children()->id;//Process response.
            $direccion->update();
        }
        catch (PrestaShopWebserviceException $ex) {
         // Here we are dealing with errors
            echo $ex->getMessage();
        }
    }

    public function addOrder($client,$order){

        try
        {   //Get Blank schema
            $connectClient = $this->initConnection();
            $xml = $connectClient->get(array('url' => 'http://osteox.esy.es/prestashop/api/orders?schema=blank'));
            $resources = $xml->children()->children();
        }
        catch (PrestaShopWebserviceException $e)
        { // Here we are dealing with errors
            error_log($e->getMessage());
        }
        $direccion = $client->direccion;
        $resources->id_customer = $client->id_client_prestashop;
        $resources->id_address_delivery = $direccion->id_address_prestashop;

        $item = Item::whereIdItemPipedrive($order['data'][0]['product_id'])->first();
        $resources->associations->order_rows[0]->product_id = $item->id;



        try {
            $opt = array('resource' => 'orders');
            $opt['postXml'] = $xml->asXML();
            $xml = $connectClient->add($opt);
            //$direccion->id_address_prestashop = $xml->children()->children()->id;//Process response.
            //$direccion->update();
        }
        catch (PrestaShopWebserviceException $ex) {
            // Here we are dealing with errors
            echo $ex->getMessage();
        }
    }
    public function editClient($client){

        try
        {   //Get Blank schema
            $connectClient = $this->initConnection();
            $xml = $connectClient->get(array('url' => 'http://osteox.esy.es/prestashop/api/customers?schema=blank'));
            $resources = $xml->children()->children();
        }
        catch (PrestaShopWebserviceException $e)
        { // Here we are dealing with errors
            error_log($e->getMessage());
        }
        if($client->firstname == null){
            $resources->firstname = $client->lastname;
            $resources->lastname = $client->lastname;
        }elseif( $client->lastname == null ){
            $resources->firstname = $client->firstname;
            $resources->lastname = $client->firstname;
        }else {
            $resources->firstname = $client->firstname;
            $resources->lastname = $client->lastname;
        }
        $resources->id = $client->id_client_prestashop;
        $resources->passwd = $client->password;
        $resources->email = $client->email;
        error_log('client se pasa? : ' .$resources->email);
        try {
            $opt = array(
                'resource' => 'customers',
                'id' => $client->id
            );
            $opt['putXml'] = $xml->asXML();
            $connectClient = $this->initConnection();
            $xml = $connectClient->edit($opt);
            //$client->id_client_prestashop = $xml->children()->children()->id;//Process response.
            //$client->update();
        }
        catch (PrestaShopWebserviceException $ex)
        { // Here we are dealing with errors
            echo $ex->getMessage();
        }
    }


    public function editAddress($client){
        try
        {   //Get Blank schema
            $connectClient = $this->initConnection();
            $xml = $connectClient->get(array('url' => 'http://osteox.esy.es/prestashop/api/addresses?schema=blank'));
            $resources = $xml->children()->children();
        }
        catch (PrestaShopWebserviceException $e)
        { // Here we are dealing with errors
            error_log($e->getMessage());
        }
        $direccion = $client->direccion;
        $resources->id = $direccion->id_address_prestashop;
        $resources->id_customer = $client->id_client_prestashop;
        $resources->firstname = $client->firstname;
        $resources->lastname = $client->lastname;
        $resources->address1 = $direccion->address1;
        $resources->city = $direccion->city;
        $resources->id_country = '6';
        $resources->postcode = $direccion->postcode;
        $resources->alias = 'Alias';
        try {
            $opt = array(
                'resource' => 'addresses',
                'id' => $direccion->id
            );
            $opt['putXml'] = $xml->asXML();
            $xml = $connectClient->edit($opt);
            //$direccion->id_address_prestashop = $xml->children()->children()->id;//Process response.
            //$direccion->update();
        }
        catch (PrestaShopWebserviceException $ex) {
            // Here we are dealing with errors
            echo $ex->getMessage();
        }
    }


    protected function initConnection(){
        $user = User::find($this->user_id);
        return new PrestaShopWebservice($user->prestashop_url, $user->prestashop_api, true);
    }
}

