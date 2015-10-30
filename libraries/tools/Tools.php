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

    public function __construct($user_id){

        $this->user_id=$user_id;
        $this->user = User::find($user_id);
    }
    public function hasClientRequiredFields($client){
        return isset($client->email);
    }
    public function addClient($client){
        if($this->hasClientRequiredFields($client)) {
            try {   //Get Blank schema
                $connectClient = $this->initConnection();
                $xml = $connectClient->get(array('url' => $this->user->prestashop_url . '/api/customers?schema=blank'));
                $resources = $xml->children()->children();
            } catch (PrestaShopWebserviceException $e) { // Here we are dealing with errors
                error_log($e->getMessage());
            }
            if (!isset($client->firstname) || $client->firstname == null) {
                $resources->firstname = $client->lastname;
                $resources->lastname = $client->lastname;
            } elseif (!isset($client->lastname) || $client->lastname == null) {
                $resources->firstname = $client->firstname;
                $resources->lastname = $client->firstname;
            }

            $resources->passwd = $client->password;
            $resources->email = $client->email;
            $resources->active = true;
            $resources->id_default_group = '3';
            $resources->associations->groups->group->id = '3';
            try {
                $opt = array('resource' => 'customers');
                $opt['postXml'] = $xml->asXML();
                $connectClient = $this->initConnection();
                $xml = $connectClient->add($opt);
                $client->id_client_prestashop = $xml->children()->children()->id;//Process response.
                $client->secure_key = $xml->children()->children()->secure_key;
                $client->password = $xml->children()->children()->passwd;
                $client->update();
            } catch (PrestaShopWebserviceException $ex) { // Here we are dealing with errors
                echo $ex->getMessage();
            }
        }else{

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
            try {   //Get Blank schema
                $connectClient = $this->initConnection();
                $xml = $connectClient->get(array('url' => $this->user->prestashop_url . '/api/addresses?schema=blank'));
                $resources = $xml->children()->children();
            } catch (PrestaShopWebserviceException $e) { // Here we are dealing with errors
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
            $resources->phone = '0';
            $resources->alias = 'Address';
            try {
                $opt = array('resource' => 'addresses');
                $opt['postXml'] = $xml->asXML();
                $xml = $connectClient->add($opt);
                $direccion->id_address_prestashop = $xml->children()->children()->id;//Process response.
                $direccion->update();
            } catch (PrestaShopWebserviceException $ex) {
                // Here we are dealing with errors
                echo $ex->getMessage();
            }
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
    public function checkCartParameters($client){
        $address = $client->direccion;
        return isset(
            $client->direccion,
            $address->address1,
            $address->city,
            $address->postcode,
            $address->id_address_prestashop,
            $address->id_address_prestashop
        );
    }
    public function addCart($client,$order){
        if(isset($order['data'][0]['product_id'])) {
            try {   //Get Blank schema
                $connectClient = $this->initConnection();
                $xml = $connectClient->get(array('url' => $this->user->prestashop_url . '/api/carts?schema=blank'));
                $resources = $xml->children()->children();
            } catch (PrestaShopWebserviceException $e) { // Here we are dealing with errors
                error_log($e->getMessage());
            }
            error_log('1');
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

            $count = 0;
            if(isset($order['data']))
            foreach ($order['data'] as $product) {
                if(isset($product['product_id'],$product['quantity'])) {
                    $item = Item::whereIdItemPipedrive($product['product_id'])->first();
                    if(isset($item)) {
                        $resources->associations->cart_rows->cart_row[$count]->id_product = $item->id_item_prestashop;
                        $resources->associations->cart_rows->cart_row[$count]->id_address_delivery = $direccion->id_address_prestashop;
                        $resources->associations->cart_rows->cart_row[$count]->quantity = $product['quantity'];
                    }
                }
                $count++;
            }
            try {
                $opt = array('resource' => 'carts');
                $opt['postXml'] = $xml->asXML();
                $xml = $connectClient->add($opt);
                if(isset($xml->children()->children()->id)) {
                    if ($xml->children()->children()->id = !0)
                        Log::info('Carrito creado para el cliente: ' . $client->firstname . ' ' . $client->lastname);
                    else
                        Log::info('Error al crear carrito para el cliente: ' . $client->firstname . ' ' . $client->lastname);
                }else{
                    Log::info('Error al crear carrito para el cliente: ' . $client->firstname . ' ' . $client->lastname);
                }
                return $xml->children()->children()->id;//Process response.

            } catch (PrestaShopWebserviceException $ex) {
                // Here we are dealing with errors
                echo $ex->getMessage();
            }
        }else{

        }
    }

    public function getClient($idClient){
            try
            {   //Get Blank schema
                $connectClient = $this->initConnection();
                $opt['resource'] = 'customers';
                $opt['id'] = $idClient;
                $opt['display'] = '[id,reference,price,name]';
                $opt['output_format'] = 'JSON';
                $xml = $connectClient->get($opt);
                $resources = $xml->children()->children();
                return $resources;
            }
            catch (PrestaShopWebserviceException $e)
            { // Here we are dealing with errors
                error_log($e->getMessage());
            }

    }
    public function getProduct($idProduct){
        try
        {   //Get Blank schema
            $connectClient = $this->initConnection();
            $opt['resource'] = 'customers';
            $opt['id'] = $idProduct;
            $xml = $connectClient->get($opt);
            $resources = $xml->children()->children();
            return $resources;
        }
        catch (PrestaShopWebserviceException $e)
        { // Here we are dealing with errors
            error_log($e->getMessage());
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

            $client->update();
        }
        catch (PrestaShopWebserviceException $ex)
        { // Here we are dealing with errors
            echo $ex->getMessage();
        }
    }

    public function editAddress($client){
        try
        {
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
        $resources->id_state = '0';
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

            $opts = array();
            $opt = array('resource' => 'products');
            $inicio = Carbon::now();
            $anterior = 0;
                for ($i = 0; $i < 10000; $i++) {
                    //$resources->quantity = $faker->numberBetween(0, 2000);

                        unset($resources->id);
                        unset($resources->position_in_category);
                        unset($resources->id_shop_default);
                        unset($resources->date_add);
                        unset($resources->date_upd);
                        unset($resources->associations->combinations);
                        unset($resources->associations->product_options_values);
                        unset($resources->associations->product_features);
                        unset($resources->associations->stock_availables->stock_available->id_product_attribute);
                    $price = $faker->randomFloat(3, 1, 550);
                    $resources->price = $price;
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
                    $resources->description->language[0][0] = $description;
                    $node = dom_import_simplexml($resources->description_short->language[0][0]);
                    $no = $node->ownerDocument;
                    $node->appendChild($no->createCDATASection($description));
                    $resources->description_short->language[0][0] = $description; //
                    $opt['postXml'] = $xml->asXML();
                    //array_push($opts,$opt);

                    if($i%10==0) {
                        $connectClient = $this->initConnection();
                        $sleep_time = 10;
                        if($i!=0) {
                            echo "\nTiempo de espera: " . $sleep_time . " segundos \n";
                         //   sleep($sleep_time);
                        }
                        $current = Carbon::now();
                        echo "\nStart at: ".$inicio.' - Last 100 items init reqs hour: '.$anterior.' - current hour is: '. Carbon::now()."\n" ;
                        $anterior = $current;
                    }
                    echo  $i ."\n";

                            try {

                            Queue::push(function() use($connectClient,$opt) {
                                $connectClient->add($opt);
                            });

                            } catch (PrestaShopWebserviceException $e) {
                                //echo $e->getMessage();
                            }
                }
        }
        catch (PrestaShopWebserviceException $ex)
        { // Here we are dealing with errors
            echo $ex->getMessage();
        }
    }
}

