<?php

namespace pintegration\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use GuzzleHttp;
use pintegration\User;
use pintegration\Item;
use PSWebS\PrestaShopWebservice;
use PSWebS\PrestaShopWebserviceException;


class SyncPrestashopProducts extends Command
{
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
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if(User::count()>0){
            $users = User::all();

            foreach ($users as $user) {
                if(isset($user->prestashop_url,$user->prestashop_api,$user->pipedrive_api)) {
                    $date = Carbon::now()->addHours(2);
                    $this->getLargeProducts($user);
                    $user->last_products_sync = $date;
                    $user->update();
                }
            }
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
    public function getLargeProducts($user){
        $totalCount=0;
        $webClient =  new GuzzleHttp\Client();;
        $chunk = 10;
        $start=0;
        $exit = false;
        while(!$exit){
            $totalCount++;
            $webService = new PrestaShopWebservice($user->prestashop_url, $user->prestashop_api, false);
            $opt['resource'] = 'products';
            $opt['display'] = '[id,name,price]';
            $opt['limit'] = $start.','.$chunk;
            $opt['output_format'] = 'JSON';
            try {
                $json = $webService->get($opt);
            }catch(PrestaShopWebserviceException $e){
                echo $e->getMessage();
            }
            $json = json_decode($json,true);
            if( count($json['products']) < $chunk )
                $exit=true;
            $count=0;
            foreach($json['products'] as $product){
                echo 'Count: '.$totalCount.' -> '.$count++."\n";
                $itemIdPrestashop = array(
                    'id_item_prestashop' => $product['id'],
                    'user_id'       => $user->id
                );
                $item = Item::firstOrNew($itemIdPrestashop);
                $item->code = str_replace(' ','_',strtolower($product['name'][0]['value']).$product['id']);
                $item->price = $product['price'];
                $item->save();
                $this->addProductToPipedrive($user,$item,$webClient);
            }
            $start += $chunk;
        }

    }
    public function addProductToPipedrive($user,$item,$client){

        $res=null;
            if($item->id_item_pipedrive != NULL)
                try {
                    $product = $this->fillProductPipedrive($item);
                    $client->put('https://api.pipedrive.com/v1/products/'.$item->id_item_pipedrive.'?api_token='.$user->pipedrive_api, $product);
                }catch(GuzzleHttp\Exception\ClientException $e){
                    echo $e->getMessage();
                }
            else{
                try {
                    $product = $this->fillProductPipedrive($item);
                    $res = $client->post('https://api.pipedrive.com/v1/products?api_token='.$user->pipedrive_api,$product);
                }catch(GuzzleHttp\Exception\ClientException $e){
                    echo $e->getMessage();
                }
                if( $res!=null && $res->getStatusCode() == 201 ){
                    $jsonResponse = json_decode($res->getBody()->getContents(),true);
                    $item->id_item_pipedrive = $jsonResponse['data']['id'];
                    $item->save();
                }
            }
    }
    public function multipleConnections(){
        $client = new GuzzleHttp\Client('http://graph.facebook.com');

        try {
            $responses = $client->send(array(
                $client->get('/' . urlencode('http://tech.vg.no')),
                $client->get('/' . urlencode('http://www.vg.no')),
            ));

            foreach ($responses as $response) {
                echo $response->getBody();
            }
        } catch (MultiTransferException $e) {
            echo 'The following exceptions were encountered:' . PHP_EOL;
            foreach ($e as $exception) {
                echo $exception->getMessage() . PHP_EOL;
            }

        }
    }
    public function fillProductPipedrive($item){
        return  [
            'form_params' => array(
                'name' => $item->code,
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

    }
}
