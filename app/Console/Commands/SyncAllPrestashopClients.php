<?php
namespace pintegration\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use pintegration\User;
use pintegration\Client;
use PSWebS\PrestaShopWebservice;
use GuzzleHttp;

class SyncAllPrestashopClients extends Command implements SelfHandling, ShouldBeQueued {

	use InteractsWithQueue, SerializesModels;

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $name = 'command:syncallpsclients';

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
							$this->getAllClients($user);
							$user->last_clients_sync = $date;
							$user->update();
						}
					}
				}
				break;
			case (self::SINCE_DATE_AUTH_USER):
				$user = User::find($this->values['user_id']);
				if(isset($user->prestashop_url,$user->prestashop_api,$user->pipedrive_api)) {
					$date = Carbon::now();
					$this->getAllClients($user);
					$user->last_clients_sync = $date;
					$user->update();
				}

				break;
			case (self::ALL_AUTH_USER):

				if(isset( $this->values['user_id'])){
					$user = User::find($this->values['user_id']);

					if(isset($user->prestashop_url,$user->prestashop_api,$user->pipedrive_api)) {
						$date = Carbon::now();
						$this->getAllClients($user);
						$user->last_clients_sync = $date;
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
							$this->getAllClients($user);
							$user->last_clients_sync = $date;
							$user->update();
						}
					}
				}
				break;
			default:
		}
			//$states = new SyncPrestashopStates();
			//$states->handle();
			$addresses = new SyncAllPrestashopAddresses($this->flag,$this->values);
			$addresses->handle();

	}
	public function getAllClients($user){
		$totalCount = 0;
		$chunk = 1000;
		$start=0;
		$exit = false;
		$items = array();
		while(!$exit) {


				$webService = new PrestaShopWebservice($user->prestashop_url, $user->prestashop_api, false);
				// Here we set the option array for the Webservice : we want customers resources
				$opt['resource'] = 'customers';
				$opt['display'] = '[id,firstname,lastname,email,passwd,secure_key]';
				$opt['limit'] = $start . ',' . $chunk;
				$opt['output_format'] = 'JSON';
				if( $this->flag == self::SINCE_DATE_AUTH_USER || $this->flag == self::SINCE_DATE_EVERY_USER) {
					if (isset($this->values['datetime'])) {
						$opt['filter[date_upd]'] = '>[' . $this->values['datetime'] . ']';
					}
				}
			try {
				$json = $webService->get($opt);
				// Here we get the elements from children of customers markup "customer"

			} catch (PrestaShopWebserviceException $e) {
				// Here we are dealing with errors
				echo $e->getTrace();
			}
			$json = json_decode($json,true);
			if(isset($json['customers'])) {
				$itemsCount = count($json['customers']);
				if ($itemsCount < $chunk)
					$exit = true;

				foreach ($json['customers'] as $customer) {
					$totalCount++;
					$clientIdPrestashop = array(
						'id_client_prestashop' => $customer['id'],
						'user_id' => $user->id
					);

					$item = Client::firstOrNew($clientIdPrestashop);
					$item->id_client_prestashop = $customer['id'];
					$item->firstname  = $customer['firstname'];
					$item->lastname   = $customer['lastname'];
					$item->password   = $customer['passwd'];
					$item->secure_key = $customer['secure_key'];
					$item->email 	  = $customer['email'];
					$item->save();
					array_push($items, $item);


					if ($totalCount % 100 == 0) {
						Log::info("Total:" . $exit . " " . $totalCount . " Last client name: " . $item->firstname . ' ' . $item->lastname);
						if ($start != 0)
							sleep(10);
						$this->addClientsToPipedrive($user, $items);
						$items = array();
					} else if ($exit && $json['customers'][$itemsCount - 1]['id'] == $customer['id']) {
						Log::info("Total:" . $exit . " " . $totalCount . " Last client name: " . $item->firstname . ' ' . $item->lastname);
						if ($start != 0)
							sleep(10);

						$this->addClientsToPipedrive($user, $items);
						$items = array();
					}

				}
			}else{
				$exit = true;

			}

			$start += $chunk;
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
							'email' => $client->email,
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
							'email' => $client->email,
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
	public function addClientsToPipedrive($user,$items){
		$options = array();
		$res=null;
		foreach($items as $item) {
			$option['data']  = $this->fillClientsPipedrive($item);
			$option['id'] = $item->id;
			if ($item->id_client_pipedrive != NULL) {

				$option['url'] = 'https://api.pipedrive.com/v1/persons/' . $item->id_client_pipedrive . '?api_token=' . $user->pipedrive_api;
				$option['verb'] = 'PUT';
				array_push($options, $option);
			}else {
				$option['url'] = 'https://api.pipedrive.com/v1/persons?api_token=' . $user->pipedrive_api;
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

			foreach($items as $item){
				if($item->id==$id)
					$item->id_client_pipedrive = $response['data']['id'];
				$item->save();
			}
			curl_multi_remove_handle($multi, $channel);
		}
		// Close the multi-handle and return our results
		curl_multi_close($multi);
		// dd($channels);
		return $channels;
	}
	public function fillClientsPipedrive($client){
		return  array(
			'name' => utf8_encode($client->firstname." ".$client->lastname),
			'active_flag' => '1',
			'email' => $client->email,
			'visible_to' => '3',
		);

	}
}
