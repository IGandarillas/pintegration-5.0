<?php

namespace pintegration\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use pintegration\Direccion;
use pintegration\State;
use pintegration\User;
use pintegration\Client;
use PSWebS\PrestaShopWebservice;
use GuzzleHttp;
class SyncAllPrestashopAddresses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'command:syncallpsaddresses';

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
                            $this->getAddresses($user);
                            $user->last_addresses_sync = $date;
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
                    $this->getAddresses($user);
                    $user->last_addresses_sync = $date;
                    $user->update();
                }

                break;
            case (self::ALL_AUTH_USER):

                if(isset( $this->values['user_id'])){
                    $user = User::find($this->values['user_id']);

                    if(isset($user->prestashop_url,$user->prestashop_api,$user->pipedrive_api)) {
                        $date = Carbon::now();
                        $this->getAddresses($user);
                        $user->last_addresses_sync = $date;
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
                            $this->getAddresses($user);
                            $user->last_addresses_sync = $date;
                            $user->update();
                        }
                    }
                }
                break;
            default:
        }
    }
    public function getAddresses($user){
        $totalCount = 0;
        $chunk = 1000;
        $start=0;
        $exit = false;
        $addresses = array();

        while(!$exit) {
            $webService = new PrestaShopWebservice($user->prestashop_url, $user->prestashop_api, false);
            // Here we set the option array for the Webservice : we want customers resources
            $opt['resource'] = 'addresses';
            $opt['display'] = '[id,id_customer,address1,postcode,city,id_country,id_state,phone_mobile]';
            $opt['limit'] = $start . ',' . $chunk;
            $opt['output_format'] = 'JSON';
            if( $this->flag == self::SINCE_DATE_AUTH_USER || $this->flag == self::SINCE_DATE_EVERY_USER) {
                if (isset($this->values['datetime'])) {
                    $opt['filter[date_upd]'] = '>[' . $this->values['datetime'] . ']';
                }
            }

        try{
            $json = $webService->get($opt);

        }catch (PrestaShopWebserviceException $e){
            echo $e->getTrace();
        }

            $resources = json_decode($json,true);
            if(isset($resources['addresses'])) {
                $itemsCount = count($resources['addresses']);
                if ($itemsCount < $chunk)
                    $exit = true;

                foreach ($resources['addresses'] as $resource) {
                    $totalCount++;
                    $client = Client::whereIdClientPrestashop($resource['id_customer'])->first();

                    if (isset($client) && $client->id_client_pipedrive != 0) {
                        $clientIdPrestashop = array(
                            'client_id' => $client->id
                        );
                        $address = Direccion::firstOrNew($clientIdPrestashop);
                        $address->id_address_prestashop = $resource['id'];
                        $address->address1 = $resource['address1'];
                        $address->postcode = $resource['postcode'];
                        $address->city = $resource['city'];
                        $address->country = $resource['id_country'];
                        if(isset($resource['phone_mobile']))
                            $address->phone_mobile = $resource['phone_mobile'];
//No se utiliza
//                        if(isset($resource['id_state']) && $resource['id_state'] !=0)
//                                $address->id_state = State::whereIdState($resource['id_state'])->first()->id;

                        $address->save();

                        array_push($addresses, $address);
                        if ($totalCount % 100 == 0) {
                            Log::info($totalCount." Prestashop -> Pipedrive: Address: " . $address->address1);
                            if ($start != 0)
                                sleep(10);
                            $this->addAddressesToPipedrive($user, $addresses);
                            $addresses = array();
                        } else if ($exit && $resources['addresses'][$itemsCount - 1]['id'] == $resource['id']) {
                            Log::info($totalCount." Prestashop -> Pipedrive: Address: " . $address->address1);
                            if ($start != 0)
                                sleep(10);
                            $this->addAddressesToPipedrive($user, $addresses);
                            $addresses = array();
                        }
                    }
                }
            }else{
                $exit = true;
            }

            $start += $chunk;

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
                }catch(GuzzleHttp\Exception\ClientException $e){
                    echo $e->getMessage();
                }
            }
        }
    }
    public function addAddressesToPipedrive($user,$items){
        $options = array();
        $res=null;
        foreach($items as $item) {
            $client = Client::find($item->client_id);
            if(isset($client)) {
                $option['data'] = $this->fillAddressPipedrive($item, $client, $user);
                $option['id'] = $item->id;
                $option['url'] = 'https://api.pipedrive.com/v1/persons/' . $client->id_client_pipedrive . '?api_token=' . $user->pipedrive_api;
                $option['verb'] = 'PUT';
                array_push($options, $option);
            }
        }
        $this->multipleConnections($options,$items);
    }
    public function multipleConnections($options,$items){

        $multi = curl_multi_init();
        $channels = array();
        // Loop through the URLs, create curl-handles
        // and attach the handles to our multi-request

        foreach ($options as $item) {
           // dd($item);
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
            curl_multi_remove_handle($multi, $channel);
        }
        // Close the multi-handle and return our results
        curl_multi_close($multi);
        // dd($channels);
        return $channels;
    }
    public function fillAddressPipedrive($address,$client,$user){
        $name = $client->firstname.' '.$client->lastname;
        $addressData=  array(
            'name'        => htmlspecialchars($name,ENT_NOQUOTES),
            'active_flag' => '1',
            'first_name'  => htmlspecialchars($client->firstname,ENT_NOQUOTES),
            'last_name'   => htmlspecialchars($client->lastname,ENT_NOQUOTES),
            'email'       => htmlspecialchars($client->email,ENT_NOQUOTES),
            'visible_to'  => '3',
            'phone'       => htmlspecialchars($address->phone_mobile),
            $user->address_field => htmlspecialchars($address->address1,ENT_NOQUOTES),
            $user->address_field.'_postal_code' => htmlspecialchars($address->postcode,ENT_NOQUOTES),
            $user->address_field.'_locality' => htmlspecialchars($address->city,ENT_NOQUOTES),
        );

        return $addressData;

    }
}
