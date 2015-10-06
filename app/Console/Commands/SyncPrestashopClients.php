<?php

namespace pintegration\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use pintegration\User;
use pintegration\Client;
use PSWebS\PrestaShopWebservice;
use GuzzleHttp;
class SyncPrestashopClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'command:syncpsclients';

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
                    $this->getClients($user);
                    $user->last_clients_sync = $date;
                    $user->update();
                }

                //$bar->advance();
            }
            //$bar->finish();
        }
    }
    public function getClients($user){
        try{
            $webService = new PrestaShopWebservice($user->prestashop_url, $user->prestashop_api, true);
            // Here we set the option array for the Webservice : we want customers resources
            $opt['resource'] = 'customers';
            $opt['display'] = '[id,firstname,lastname,email,passwd]';
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
            foreach ($resources as $resource)
            {
                try {
                    $clientIdPrestashop = array(
                        'id_client_prestashop' => $resource->id,
                        'user_id'              => $user->id
                    );

                    $client = Client::firstOrNew($clientIdPrestashop);
                    $client->id_client_prestashop = $resource->id;
                    $client->firstname = $resource->firstname;
                    $client->lastname = $resource->lastname;
                    $client->email = $resource->email;
                    $client->password = $resource->passwd;
                    $client->secure_key = $resource->secure_key;
                    $client->save();
                 } catch ( QueryException $e) {
                    var_dump($e->errorInfo);
                }

            }
            $this->syncWithPipedrive($user);
        }
    }
    public function syncWithPipedrive($user){
        $clientsList = $user->clients;
        $guzzleClient = new GuzzleHttp\Client();
        $res=null;
        foreach ($clientsList as $client) {
            if($client->id_client_pipedrive != NULL){
                try {

                    $res = $guzzleClient->put('https://api.pipedrive.com/v1/persons/'.$client->id_client_pipedrive.'?api_token='.$user->pipedrive_api, [
                        'body' => [
                            'name' => $client->firstname.' '.$client->lastname,
                        ]
                    ]);
                }catch(GuzzleHttp\Exception\ClientException $e){
                    // echo $e->getMessage();
                }

                if( $res!=null && $res->getStatusCode() == 200  ){
                    //Logic
                }

            }else{
                //Get pipedrive Key and update

                try {
                    // https://api.pipedrive.com/v1/

                    $res = $guzzleClient->post('https://api.pipedrive.com/v1/persons?api_token='.$user->pipedrive_api, [
                        'body' => [
                            'name' => $client->firstname.' '.$client->lastname,
                            'owner_id' => '830118',
                            'visible_to' => '3',
                        ]
                    ]);

                }catch(GuzzleHttp\Exception\ClientException $e){
                    echo $e->getMessage();
                }

                if(  $res!=null && $res->getStatusCode() == 201 ){
                    $jsonResponse = json_decode($res->getBody()->getContents(),true);
                    $client->id_client_pipedrive = $jsonResponse['data']['id'];
                    $client->save();
                }
            }

        }
    }
}
