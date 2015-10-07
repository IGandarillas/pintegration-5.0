<?php

namespace pintegration\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use pintegration\Direccion;
use pintegration\User;
use pintegration\Client;
use PSWebS\PrestaShopWebservice;
use GuzzleHttp;
class SyncPrestashopAdresses extends Command
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
            $webService = new PrestaShopWebservice($user->prestashop_url, $user->prestashop_api, true);
            // Here we set the option array for the Webservice : we want customers resources
            $opt['resource'] = 'addresses';
            $opt['display'] = '[id_customer,address1,postcode,city]';
            $opt['filter[date_upd]'] = '>['.$user->last_clients_sync.']';
            // Call
            $xml = $webService->get($opt);

            // Here we get the elements from children of customers markup "customer"
            $resources = $xml->customers->children();
        }catch (PrestaShopWebserviceException $e){
            // Here we are dealing with errors
            $trace = $e->getTrace();
            if ($trace[0]['args'][0] == 404) echo 'Bad ID';
            else if ($trace[0]['args'][0] == 401) echo 'Bad auth key';
            else echo 'Other error';
        }

        if (isset($resources)){
            $addresses = array();
            foreach ($resources as $resource)
            {
                try {
                    $clientIdPrestashop = array(
                        'client_id'              => $resource->id_customer
                    );
                    $address = Direccion::firstOrNew($clientIdPrestashop);
                    $address->id_address_prestashop = $resource->id;
                    $address->address1 = $resource->address1;
                    $address->postcode = $resource->postcode;
                    $address->city = $resource->city;
                    $address->country = $resource->country;
                    $address->save();
                    array_push($addresses,$address);
                } catch ( QueryException $e) {
                    var_dump($e->errorInfo);
                }

            }
            $this->syncWithPipedrive($user,$addresses);
        }
    }
    public function syncWithPipedrive($user,$addresses){
        $guzzleClient = new GuzzleHttp\Client();
        $res=null;
        foreach ($addresses as $address) {
            if($address->id_address_pipedrive != NULL){
                try {

                    $res = $guzzleClient->put('https://api.pipedrive.com/v1/persons/'.$address->client_id.'?api_token='.$user->pipedrive_api, [
                        'body' => [
                            $user->address_field => $address->address1
                        ]
                    ]);
                }catch(GuzzleHttp\Exception\ClientException $e){
                    // echo $e->getMessage();
                }


            }else{
                //Get pipedrive Key and update

                try {
                    // https://api.pipedrive.com/v1/

                    $res = $guzzleClient->post('https://api.pipedrive.com/v1/persons?api_token='.$user->pipedrive_api, [
                        'body' => [
                            $user->address_field => $address->address1,
                        ]
                    ]);

                }catch(GuzzleHttp\Exception\ClientException $e){
                    echo $e->getMessage();
                }

            }

        }
    }
}
