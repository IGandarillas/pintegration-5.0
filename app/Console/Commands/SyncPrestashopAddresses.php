<?php

namespace pintegration\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use pintegration\Direccion;
use pintegration\User;
use pintegration\Client;
use PSWebS\PrestaShopWebservice;
use GuzzleHttp;
class SyncPrestashopAddresses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'command:syncpsaddresses';

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

                    $this->getAddresses($user);
                    $user->last_addresses_sync = $date;
                    $user->update();
                }

                //$bar->advance();
            }
            //$bar->finish();
        }
    }
    public function getAddresses($user){
        try{

            $webService = new PrestaShopWebservice($user->prestashop_url, $user->prestashop_api, false);
            // Here we set the option array for the Webservice : we want customers resources
            $opt['resource'] = 'addresses';
            $opt['display'] = '[id,id_customer,address1,postcode,city]';
            $opt['filter[date_upd]'] = '>['.$user->last_clients_sync.']';
            $opt['limit'] = '50';
            // Call
            $xml = $webService->get($opt);

            // Here we get the elements from children of customers markup "customer"
            $resources = $xml->addresses->children();
        }catch (PrestaShopWebserviceException $e){
            // Here we are dealing with errors
            $trace = $e->getTrace();
            if ($trace[0]['args'][0] == 404) echo 'Bad ID';
            else if ($trace[0]['args'][0] == 401) echo 'Bad auth key';
            else echo 'Other error';
        }

        if (isset($resources)){
            $addresses = array();
            $chunk = 0;
            foreach ($resources as $resource)
            {
                try {
                    $client = Client::whereIdClientPrestashop($resource->id_customer)->first();
                    if(isset($client) && $client->id_client_pipedrive != 0) {
                        $clientIdPrestashop = array(
                            'client_id' => $client->id
                    );

                        $address = Direccion::firstOrNew($clientIdPrestashop);
                        $address->id_address_prestashop = $resource->id;
                        $address->address1 = $resource->address1;
                        $address->postcode = $resource->postcode;
                        $address->city = $resource->city;
                        $address->country = $resource->country;
                        $address->save();
                        array_push($addresses, $address);
                        $chunk++;
                        if($chunk >= 50) {
                            $this->syncWithPipedrive($user, $addresses);
                            $addresses = array();
                        }
                    }
                } catch ( QueryException $e) {
                    var_dump($e->errorInfo);
                }

            }

        }
    }
    public function syncWithPipedrive($user,$addresses){
        $guzzleClient = new GuzzleHttp\Client();

        foreach ($addresses as $address) {
            $client = Client::find($address->client_id);
            if(isset($client)){
                try {

                    $res = $guzzleClient->put('https://api.pipedrive.com/v1/persons/'.$client->id_client_pipedrive.'?api_token='.$user->pipedrive_api, [
                        'body' => [
                            $user->address_field => htmlspecialchars($address->address1,ENT_NOQUOTES)
                        ]
                    ]);
                    dd($res);
                }catch(GuzzleHttp\Exception\ClientException $e){
                    dd('Actualizar '.$e->getMessage());
                    // echo $e->getMessage();
                }


            }/*else{
                //Get pipedrive Key and update

                try {
                    // https://api.pipedrive.com/v1/

                    $res = $guzzleClient->post('https://api.pipedrive.com/v1/persons?api_token='.$user->pipedrive_api, [
                        'body' => [
                            $user->address_field => htmlspecialchars($address->address1,ENT_NOQUOTES),
                            'owner_id' => '830118'
                        ]
                    ]);

                }catch(GuzzleHttp\Exception\ClientException $e){
                    echo $e->getMessage();
                }

            }*/

        }
    }
}
