<?php

namespace pintegration\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use GuzzleHttp;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use pintegration\Services\CheckDbConsistency;
use pintegration\User;
use pintegration\Item;
use PSWebS\PrestaShopWebservice;
use PSWebS\PrestaShopWebserviceException;


class SyncPrestashopProducts extends Command implements SelfHandling,ShouldBeQueued
{
    use InteractsWithQueue,SerializesModels;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'command:syncpsproducts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    const ALL_EVERY_USER = 0; //Sync all products for all users.
    const ALL_AUTH_USER = 1;//Sync all products for auth user.
    const SINCE_DATE_EVERY_USER = 2; //Sync a set of products for all user.
    const SINCE_DATE_AUTH_USER = 3; //Sync a set of products for auth user.
    const RELOAD_ITEMS = 4;
    public $flag;
    public $values;
    public function __construct($flag = self::ALL_EVERY_USER,$values = null)
    {

        $this->flag=$flag;
        $this->values = $values;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        switch($this->flag){
            case (self::SINCE_DATE_EVERY_USER):
                if(User::count()>0){
                    $users = User::all();
                    foreach ($users as $user) {
                        if(isset($user->prestashop_url,$user->prestashop_api,$user->pipedrive_api)) {
                            $date = Carbon::now();
                            $this->getAllProducts($user);
                            $user->last_products_sync = $date;
                            $user->update();
                        }
                    }
                }
            break;
            case (self::SINCE_DATE_AUTH_USER):
                //dd($this->flag.' bien '.$this->value);

                    $user = User::find($this->values['user_id']);

                        if(isset($user->prestashop_url,$user->prestashop_api,$user->pipedrive_api)) {
                            $date = Carbon::now();
                            $this->getAllProducts($user);
                            $user->last_products_sync = $date;
                            $user->update();
                        }

                break;
            case (self::ALL_AUTH_USER):

                if(isset( $this->values['user_id'])){
                    $user = User::find($this->values['user_id']);

                    if(isset($user->prestashop_url,$user->prestashop_api,$user->pipedrive_api)) {
                        $date = Carbon::now();
                        $this->getAllProducts($user);
                        $user->last_products_sync = $date;
                        $user->update();
                    }
                }
                break;
            case (self::ALL_EVERY_USER ):
                if(User::count()>0){
                    $users = User::all();
                    foreach ($users as $user) {
                        if(isset($user->prestashop_url,$user->prestashop_api,$user->pipedrive_api)) {
                            $date = Carbon::now();
                            $this->getAllProducts($user);
                            $user->last_products_sync = $date;
                            $user->update();
                        }
                    }
                }
            break;
            case (self::RELOAD_ITEMS ):
                if(User::count()>0){
                    if(isset( $this->values['user_id'])) {
                        $user = User::find($this->values['user_id']);
                        $dbConsistency = new CheckDbConsistency();
                        $products = $dbConsistency->products($user->id);
                        //Log::info($products);
                        $tries=0;
                        while ($tries++ < 3){
                            if($products != 0) {
                                $this->values['items'] = $products;
                                if (isset($user->prestashop_url, $user->prestashop_api, $user->pipedrive_api)) {
                                    $this->getAllProducts($user);
                                }
                            }
                            $products = $dbConsistency->products($user->id);
                        }

                    }
                }
                break;
            default:

        }

    }

    public function getProducts($user){
        try{

            $webService = new PrestaShopWebservice($user->prestashop_url, $user->prestashop_api, true);
            // Here we set the option array for the Webservice : we want customers resources
            $opt['resource'] = 'products';
            $opt['display'] = '[name,id,price,reference]';
            $opt['filter[date_upd]'] = '>['.$user->last_products_sync.']';
            // Call
            $xml = $webService->get($opt);

            // Here we get the elements from children of customers markup "customer"
            $resources = $xml->products->children();

        }catch (PrestaShopWebserviceException $e){
            // Here we are dealing with errors
            $trace = $e->getTrace();
            if ($trace[0]['args'][0] == 404) echo 'Bad ID';
            else if ($trace[0]['args'][0] == 401) echo 'Bad auth key';
            else echo 'Other error';
        }

        if (isset($resources)){
            $items = array();
            $chunk = 0;
            foreach ($resources as $resource)
            {
                try {
                    $itemIdPrestashop = array(
                        'id_item_prestashop' => $resource->id,
                        'user_id'       => $user->id
                    );
                    $item = Item::firstOrNew($itemIdPrestashop);
                    $item->name = $resource->name->language[0];
                    error_log($resource->reference.' - '.$resource->price);
                    if($resource->reference != '') {

                        $item->code = $resource->reference;
                        $item->price = $resource->price;
                    }
                    $item->save();
                    array_push($items, $item);
                    $chunk++;
                    if($chunk >= 50) {
                        $this->syncWithPipedrive($user, $items);
                        $addresses = array();
                    }
                } catch ( QueryException $e) {
                    var_dump($e->errorInfo);
                }

            }
        }
    }
    public function syncWithPipedrive($user,$items){

        $client = new GuzzleHttp\Client();
        $res=null;
        foreach ($items as $item) {

            if($item->id_item_pipedrive != NULL){
                try {

                    $product = [
                        'body' => array(
                            'name' => $item->name,
                            'active_flag' => '1',
                            'visible_to' => '3',
                            'owner_id' => '867597',
                            'code' => $item->code,
                            'prices' => [
                                array(
                                    'price' => $item->price,
                                    'currency' => 'EUR',
                                )
                            ]
                        )
                    ];
                    $res = $client->put('https://api.pipedrive.com/v1/products/'.$item->id_item_pipedrive.'?api_token='.$user->pipedrive_api, $product);

                }catch(GuzzleHttp\Exception\ClientException $e){
                    echo $e->getMessage();
                }

                if( $res!=null && $res->getStatusCode() == 200  ){
                    //Logic
                }

            }else{
                //Get pipedrive Key and update
                try {
                   // https://api.pipedrive.com/v1/
                    $product = [
                        'body' => array(
                            'name' => $item->name,
                            'active_flag' => '1',
                            'visible_to' => '3',
                            'owner_id' => '867597',
                            'code' => $item->code,
                            'prices' => [
                                array(
                                    'price' => $item->price,
                                    'currency' => 'EUR',

                                )
                            ]
                        )
                    ];
                    $res = $client->post('https://api.pipedrive.com/v1/products?api_token='.$user->pipedrive_api,$product);

                }catch(GuzzleHttp\Exception\ClientException $e){
                    echo $e->getMessage();
                }

                if( $res->getStatusCode() == 201 && $res!=null ){
                    $jsonResponse = json_decode($res->getBody()->getContents(),true);
                    $item->id_item_pipedrive = $jsonResponse['data']['id'];
                    $item->save();
                }
            }

        }
    }
    public function getAllProducts($user){

        $totalCount = 0;
        $chunk = 1000;
        $start=0;
        $exit = false;
        $items = array();
        while(!$exit){
            $webService = new PrestaShopWebservice($user->prestashop_url, $user->prestashop_api, false);
            $opt['resource'] = 'products';
            $opt['display'] = '[id,reference,price,name]';
            $opt['limit'] = $start.','.$chunk;
            $opt['output_format'] = 'JSON';
           // dd($this->flag . ' bien ' . $this->value);

            if( $this->flag == self::SINCE_DATE_AUTH_USER || $this->flag == self::SINCE_DATE_EVERY_USER) {
                if (isset($this->values['datetime'])) {
                    $opt['filter[date_upd]'] = '>[' . $this->values['datetime'] . ']';
                }
            }
            if( $this->flag == self::RELOAD_ITEMS ){
                if (isset($this->values['items'])) {
                    $str = "";
                    foreach($this->values['items'] as $item)
                        $str .= $item.'|';
                    //Log::info($str);
                    $opt['filter[id]'] = '[' . $str . ']';
                }
            }
            try {
                $json = $webService->get($opt);
            }catch(PrestaShopWebserviceException $e){
                echo $e->getMessage();
            }

            $json = json_decode($json,true);

            if(isset($json['products'])) {
                if (count($json['products']) < $chunk)
                    $exit = true;
                $count = 0;
                foreach ($json['products'] as $product) {
                    $totalCount++;
                    $itemIdPrestashop = array(
                        'id_item_prestashop' => $product['id'],
                        'user_id' => $user->id
                    );
                    $item = Item::firstOrNew($itemIdPrestashop);
                    $item->name = $product['name'][0]['value'];
                    $item->code = str_replace(' ', '_', strtolower($product['reference']));
                    $item->price = $product['price'];
                    $item->save();
                    array_push($items, $item);
                    $itemsCount = count($json['products']);
                    if ($totalCount % 100 == 0) {
                        Log::info("Total: ". $totalCount . " Product => reference: " . $item->code . "\n name: " . $item->name);
                        if ($start != 0)
                            sleep(10);
                        $this->addProductToPipedrive($user, $items);
                        $items = array();
                    } else if ($exit && $json['products'][$itemsCount - 1]['id'] == $product['id']) {
                        if( $this->flag != self::RELOAD_ITEMS ) {
                            Log::info("Total: " . $totalCount . " Product => reference: " . $item->code);
                        }
                        if ($start != 0)
                            sleep(10);
                        $this->addProductToPipedrive($user, $items);
                        $items = array();
                    }else{
                        $this->addProductToPipedrive($user, $items);
                    }
                }
            }else{
                $exit = true;
            }

            $start += $chunk;
        }

    }
    public function addProductToPipedrive($user,$items){
        $options = array();
        $res=null;
        foreach($items as $item) {
            $option['data']  = $this->fillProductPipedrive($item,$user);
            $option['id'] = $item->id;
            if ($item->id_item_pipedrive != NULL) {
                $option['url'] = 'https://api.pipedrive.com/v1/products/' . $item->id_item_pipedrive . '?api_token=' . $user->pipedrive_api;
                $option['verb'] = 'PUT';
                array_push($options, $option);
            }else {
                $option['url'] = 'https://api.pipedrive.com/v1/products?api_token=' . $user->pipedrive_api;
                $option['verb'] = 'POST';
                array_push($options,$option);
            }
        }
         $this->multipleConnections($options,$items);
    }
    //http://tech.vg.no/2013/07/23/php-perform-requests-in-parallel/
    public function multipleConnections($options,$items){

        $multi = curl_multi_init();
        $channels = array();
        // Loop through the URLs, create curl-handles
        // and attach the handles to our multi-request

        foreach ($options as $item) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $item['url']);
            $data = json_encode($item['data']);
            if($item['verb'] == 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            }else if($item['verb'] == 'PUT') {
                //curl_setopt($ch, CURLOPT_PUT, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            }

            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data))
            );

            curl_multi_add_handle($multi, $ch);
            $channels[$item['id']] = $ch;
        }
        // While we're still active, execute curl
        $active = null;
        do {
            $mrc = curl_multi_exec($multi, $running);
            curl_multi_select($multi);
        } while ($running > 0);
        while ($active && $mrc == CURLM_OK) {
            // Wait for activity on any curl-connection
            if (curl_multi_select($multi) == -1) {
                continue;
            }

            // Continue to exec until curl is ready to
            // give us more data
            do {
                $mrc = curl_multi_exec($multi, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }

// Loop through the channels and retrieve the received
// content, then remove the handle from the multi-handle
        foreach ($channels as $id => $channel) {
            $response = curl_multi_getcontent($channel);
            $response = json_decode($response,true);
            if(isset( $response['data']['id'], $response['data']))
            foreach($items as $item){
                if($item->id==$id)
                    $item->id_item_pipedrive = $response['data']['id'];
                $item->save();
            }
            curl_multi_remove_handle($multi, $channel);
        }
        // Close the multi-handle and return our results
        curl_multi_close($multi);
        // dd($channels);
        return $channels;
    }
    public function fillProductPipedrive($item,$user){
        return  array(
                'name' => $item->code.' '.$item->name,
                'code' => $item->code,
                'active_flag' => '1',
                'visible_to' => '3',
                'prices' => [
                    array(
                        'price' => $item->price,
                        'currency' => 'EUR',
                    )
                ]
        );

    }
}
