<?php


/**
 * Created by PhpStorm.
 * User: Yunkorff
 * Date: 24/09/2015
 * Time: 22:47
 */

namespace Tools;
use GuzzleHttp\Client;
use PSWebS\PrestaShopWebservice;
use Auth;

class Tools
{
    protected $user=null;

    public function __construct($user){
        $this->user=$user;
    }
    public function addClient($client){
        error_log('client se pasa? : ' .$client->id);

        try
        {   //Get Blank schema
            $connectClient = $this->initConnection($this->user);
            $xml = $connectClient->get(array('url' => 'http://osteox.esy.es/prestashop/api/customers?schema=blank'));
            $resources = $xml->children()->children();
        }
        catch (PrestaShopWebserviceException $e)
        { // Here we are dealing with errors
            error_log($e->getMessage());
        }

        $resources->firstname = $client->firstname;
        $resources->lastname = $client->lastname;
        $resources->passwd = $client->password;
        $resources->email = $client->email;
        try {
            $opt = array('resource' => 'customers');
            $opt['postXml'] = $xml->asXML();

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
        $user = Auth::user();
        try
        {   //Get Blank schema
            $connectClient = $this->initConnection($user);
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

    public function addOrder($client){
        $user = Auth::user();
        try
        {   //Get Blank schema
            $connectClient = $this->initConnection($user);
            $xml = $connectClient->get(array('url' => 'http://osteox.esy.es/prestashop/api/orders?schema=blank'));
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
        $resources->alias = $client->firstname.' '.$client->lastname;

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


    protected function initConnection($user){
        return new PrestaShopWebservice($user->prestashop_url, $user->prestashop_api, true);
    }
}