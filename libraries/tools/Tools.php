<?php


/**
 * Created by PhpStorm.
 * User: Yunkorff
 * Date: 24/09/2015
 * Time: 22:47
 */

namespace Tools;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Queue;
use pintegration\Item;
use pintegration\User;
use PSWebS\PrestaShopWebservice;

use Auth;
use PSWebS\PrestaShopWebserviceException;

class Tools
{
    protected $user_id=null;
    protected $user;
    public function __construct($user_id){
        $this->user_id=$user_id;
        $this->user = User::find($user_id);
    }
    public function addClient($client){

        try
        {   //Get Blank schema
            $connectClient = $this->initConnection();
            $xml = $connectClient->get(array('url' => $this->user->prestashop_url.'/api/customers?schema=blank'));
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
        $resources->active = true;
        $resources->id_default_group = '3';
        $resources->associations->groups->group->id = '3';
        error_log('client se pasa? : ' .$resources->email);
        try {
            $opt = array('resource' => 'customers');
            $opt['postXml'] = $xml->asXML();
            $connectClient = $this->initConnection();
            $xml = $connectClient->add($opt);
            $client->id_client_prestashop = $xml->children()->children()->id;//Process response.
            $client->secure_key = $xml->children()->children()->secure_key;
            $client->password = $xml->children()->children()->passwd;
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
            $xml = $connectClient->get(array('url' => $this->user->prestashop_url.'/api/addresses?schema=blank'));
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
        $resources->id_state = '0';
        $resources->postcode = $direccion->postcode;
        $resources->phone = '645621321';
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
    public function getTotalPrice($order){
        $total = 0;

        foreach ( $order['data'] as $product ){
            $item = Item::whereIdItemPipedrive($product['product_id'])->first();
            $itemWt = $item->price * 1.21;
            $total += $itemWt * $product['quantity'];
        }
        return $total;

    }

    public function addOrder($client,$order){

        try
        {   //Get Blank schema
            $connectClient = $this->initConnection();
            $xml = $connectClient->get(array('url' => $this->user->prestashop_url.'/api/orders?schema=blank'));
            $resources = $xml->children()->children();
        }
        catch (PrestaShopWebserviceException $e)
        { // Here we are dealing with errors
            error_log($e->getMessage());
        }



        $direccion = $client->direccion;
        $price = $this->getTotalPrice($order);
        //dd($price);

        $resources->id_address_delivery = $direccion->id_address_prestashop;
        $resources->id_address_invoice = $direccion->id_address_prestashop;
        $resources->current_state = '10';
        $resources->id_cart = $this->addCart($client,$order);
        $resources->id_currency = '1';
        $resources->id_lang='1';
        $resources->id_customer = $client->id_client_prestashop;
        $resources->id_carrier = '1';
        $resources->module = 'bankwire';
        $resources->secure_key = md5(uniqid(rand(), true));
        $resources->payment = 'Awaiting payment';
        $resources->total_paid_tax_incl = '0';
        $resources->total_paid_tax_excl = '0';
        $resources->total_paid = '0';
        $resources->total_paid_real = '0';
        $resources->total_products = '0';
        $resources->total_products_wt = '0';
        $resources->conversion_rate = '0';
        $resources->valid = false;

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
    public function addCombination($item){
        try
        {   //Get Blank schema
            $connectClient = $this->initConnection();
            $xml = $connectClient->get(array('url' => $this->user->prestashop_url.'/api/combinations?schema=blank'));
            $resources = $xml->children()->children();
        }
        catch (PrestaShopWebserviceException $e)
        { // Here we are dealing with errors
            error_log($e->getMessage());
        }

        $resources->minimal_quantity = '1';
        $resources->id_product = $item->id_item_prestashop;
        //$resources->price = $item->price;
        //$resources->quantity = '1';
        //$resources->reference = $item->code;

        try {
            $opt = array('resource' => 'combinations');
            $opt['postXml'] = $xml->asXML();
            $xml = $connectClient->add($opt);
            return $xml->children()->children()->id;//Process response.

        }
        catch (PrestaShopWebserviceException $ex) {
            // Here we are dealing with errors
            echo $ex->getMessage();
        }
    }

    public function addCart($client,$order){
        try
        {   //Get Blank schema
            $connectClient = $this->initConnection();
            $xml = $connectClient->get(array('url' => $this->user->prestashop_url.'/api/carts?schema=blank'));
            $resources = $xml->children()->children();
        }
        catch (PrestaShopWebserviceException $e)
        { // Here we are dealing with errors
            error_log($e->getMessage());
        }
        error_log('1');
        $direccion = $client->direccion;
        $resources->id_address_delivery = $direccion->id_address_prestashop;
        $resources->id_address_invoice = $direccion->id_address_prestashop;
        $resources->id_currency = '1';
        $resources->id_lang='1';
        $resources->id_customer = $client->id_client_prestashop;
        $resources->id_carrier = '1';
        $resources->id_show_group = '1';
        error_log('2');
        //$resources->secure_key = $client->secure_key;
        $count = 0;
        error_log('3');
        foreach($order['data'] as $product){
            $item = Item::whereIdItemPipedrive($product['product_id'])->first();
            $resources->associations->cart_rows->cart_row[$count]->id_product = $item->id_item_prestashop;
            $resources->associations->cart_rows->cart_row[$count]->id_address_delivery = $direccion->id_address_prestashop;
            $resources->associations->cart_rows->cart_row[$count]->quantity = $product['quantity'];
            $count++;
            error_log('4');
        }


        try {
            $opt = array('resource' => 'carts');
            $opt['postXml'] = $xml->asXML();
            $xml = $connectClient->add($opt);
            error_log('5');
            return $xml->children()->children()->id;//Process response.

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
            $opt['resource'] = 'customers';
            $opt['id'] = $client->id_client_prestashop;
            $xml = $connectClient->get($opt);
            $resources = $xml->children()->children();
        }
        catch (PrestaShopWebserviceException $e)
        { // Here we are dealing with errors
            error_log($e->getMessage());
        }

        $resources->firstname = $client->firstname;
        $resources->lastname = $client->lastname;
        $resources->id = $client->id_client_prestashop;
        $resources->email = $client->email;
        // $resources->active = true;
        //Client group
        //$resources->id_default_group = '3';
        //$resources->associations->groups->group->id = '3';

        try {
            $opt = array(
                'resource' => 'customers',
                'id' => $client->id_client_prestashop
            );
            $opt['putXml'] = $xml->asXML();
            $connectClient = $this->initConnection();
            $xml = $connectClient->edit($opt);
            //Get secure_key

            $client->update();
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
            $opt['resource'] = 'addresses';
            $opt['id'] = $client->direccion->id_address_prestashop;
            $xml = $connectClient->get($opt);
            $resources = $xml->children()->children();
        }
        catch (PrestaShopWebserviceException $e)
        { // Here we are dealing with errors
            error_log($e->getMessage());
        }
        $direccion = $client->direccion;
        $resources->firstname = $client->firstname;
        $resources->lastname = $client->lastname;
        $resources->address1 = $direccion->address1;
        $resources->city = $direccion->city;
        $resources->id_country = '6';
        $resources->id_state = '313';
        $resources->postcode = $direccion->postcode;
        $resources->alias = htmlspecialchars('Direccion',ENT_NOQUOTES);
        try {
            $opt = array(
                'resource' => 'addresses',
                'id' => $direccion->id_address_prestashop
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
        return new PrestaShopWebservice($user->prestashop_url, $user->prestashop_api, false);
    }
    public function addProductsFake($faker){

        try
        {   //Get Blank schema
            $connectClient = $this->initConnection();
            $xml = $connectClient->get(array('url' => $this->user->prestashop_url.'/api/products?schema=blank'));
            $resources = $xml->children()->children();
        }
        catch (PrestaShopWebserviceException $e)
        { // Here we are dealing with errors

            error_log($e->getMessage());
        }

        try {

            $opt = array('resource' => 'products');
            $connectClient = $this->initConnection();
            unset($resources -> id);
            unset($resources -> position_in_category);
            unset($resources -> id_shop_default);
            unset($resources -> date_add);
            unset($resources -> date_upd);
            unset($resources->associations->combinations);
            unset($resources->associations->product_options_values);
            unset($resources->associations->product_features);
            unset($resources->associations->stock_availables->stock_available->id_product_attribute);
            for($i=0; $i <= 100000 ; $i++) {
                //$resources->quantity = $faker->numberBetween(0, 2000);
                if($i%70==0){
                    echo "Reload\n";
                    $opt = array('resource' => 'products');
                    $connectClient = $this->initConnection();
                    unset($resources -> id);
                    unset($resources -> position_in_category);
                    unset($resources -> id_shop_default);
                    unset($resources -> date_add);
                    unset($resources -> date_upd);
                    unset($resources->associations->combinations);
                    unset($resources->associations->product_options_values);
                    unset($resources->associations->product_features);
                    unset($resources->associations->stock_availables->stock_available->id_product_attribute);
                }
                $resources->price =  $faker->randomFloat(3, 1, 550);
                $resources->name->language[0] = $faker->name();
                $resources->active = true;

                $resources->link_rewrite->language[0][0] = $faker->word();

                $name = $faker->name();
                $node = dom_import_simplexml($resources->name->language[0][0]);
                $no = $node->ownerDocument;
                $node->appendChild($no->createCDATASection($name));
                $resources->name->language[0][0] = $name;
                $description = $faker->name();
                $node = dom_import_simplexml($resources->description->language[0][0]);
                $no = $node->ownerDocument;
                $node->appendChild($no->createCDATASection($description));
                $resources -> description -> language[0][0] = $description;
                $node = dom_import_simplexml($resources->description_short->language[0][0]);
                $no = $node->ownerDocument;
                $node->appendChild($no->createCDATASection($description));
                $resources->description_short->language[0][0] = $description; //

                // $resources->associations = '';
                $opt['postXml'] = $xml->asXML();
                //dd($opt);
                try {
                    $connectClient->add($opt);
                }catch(PrestaShopWebserviceException $e) {
                    echo $e->getMessage();
                }

                echo $i."\n";
                //error_log($i);
            }

        }
        catch (PrestaShopWebserviceException $ex)
        { // Here we are dealing with errors
            echo $ex->getMessage();
        }
    }
}

