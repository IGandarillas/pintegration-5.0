<?php namespace pintegration\Commands;

use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use GuzzleHttp;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\NullHandlerTest;
use pintegration\Client;
use Illuminate\Support\Facades\Artisan;
use pintegration\User;
use Symfony\Component\Finder\Shell\Command;
use Tools\Tools;
use pintegration\Direccion;


class UpdateClientFromPipedrive extends Command implements SelfHandling, ShouldBeQueued{

	use InteractsWithQueue, SerializesModels;

	protected $request;
	protected $user_id;
	protected $user;

	public function __construct($request,$user_id)    {
		$this->request = $request;
		$this->user_id=$user_id;
		$this->user = User::find($user_id);
	}
	public function handle(){

		$clientId = $this->request['current']['person_id'];//Id Pipedrive
		error_log($clientId);
		$client = $this->updateClient($clientId);

		error_log('1');
		if( isset($client) ) {
			$tools = new Tools($this->user_id);
			$tools->editClient($client);
			error_log('address');
			$direccion = $client->direccion;

			if(!isset($direccion, $direccion->id_address_prestashop) || $direccion->id_address_prestashop == 0)
				$tools->addAddress($client);
			else
				$tools->editAddress($client);

			$dealId = $this->request['current']['id'];
			$orderData = $this->getOrderData($dealId);
			$idCart= $tools->addCart($client,$orderData);

			if(isset($idCart) && $idCart != 0 && $idCart != NULL){
				Log::info('Added cart => id: '.$idCart."\n Client => id: ".$client->firstname.' '.$client->lastname);
			}else{
				Log::warning("Not added cart. Client => id: ".$client->firstname.' '.$client->lastname);
			}
			//$tools->addOrder($client,$orderData);
		}
		error_log("FIN");


	}
	protected function createAddress($newClient,$clientData){
		$address = new Direccion();
		$address->client_id = $newClient->id;
		$address->address1 = $clientData['data'][$this->user->address_field];
		$address->country = $clientData['data'][$this->user->address_field.'_country'];
		$address->postcode = $clientData['data'][$this->user->address_field.'_postal_code'];
		$address->city = $clientData['data'][$this->user->address_field.'_locality'];
		error_log($address->city);
		error_log($address->client_id);
		$address->save();
	}
	protected function updateClient($clientId){

		$clientData = $this->getClientData($clientId);
		error_log($clientData['data']['first_name']);
		error_log($clientData['data']['last_name']);
		error_log($clientData['data']['email'][0]['value']);
		error_log('1');
		if($this->isAddress($clientData)) {
			$updateClient = Client::whereIdClientPipedrive($clientId)->first();
			$updateClient->firstname = $clientData['data']['first_name'];
			$updateClient->lastname = $clientData['data']['last_name'];
			$updateClient->email = $clientData['data']['email'][0]['value'];
			$updateClient->update();
			error_log('2');

			if($this->isCompleteAddress($clientData)) {

				$direccion = array(
					'client_id' => $updateClient->id
				);
				$dir = Direccion::firstOrNew($direccion);
				$dir->address1 =  $clientData['data'][$this->user->address_field];
				$dir->country = $clientData['data'][$this->user->address_field . '_country'];
				$dir->postcode = $clientData['data'][$this->user->address_field . '_postal_code'];
				$dir->city = $clientData['data'][$this->user->address_field . '_locality'];
				//$dir->id_state = $this->getState($clientData);
				$dir->save();
			}
			return $updateClient;
		}

		Log::error('No hay direccion: '.$clientData['data']['first_name']);
	}

	protected function getClientData($id)
	{
		$url = 'https://api.pipedrive.com/v1/persons/'.$id.'?api_token='.$this->user->pipedrive_api;

		return $this->getData($url);
	}
	protected function getDealData($id){
		$url = 'https://api.pipedrive.com/v1/deals/'.$id.'/products?start=0&api_token='.$this->user->pipedrive_api;
		error_log($url);
		return $this->getData($url);
	}
	protected function getOrderData($id){
		$url = 'https://api.pipedrive.com/v1/deals/'.$id.'/products?start=0&include_product_data=0&api_token='.$this->user->pipedrive_api;
		return $this->getData($url);
	}

	protected function isAddress($clientData){
		return ($clientData['data'][$this->user->address_field] != NULL);
	}

	//Null fields mean malformed address.
	protected function isCompleteAddress($clientData){
		return (
			 $clientData['data'][$this->user->address_field.'_country'] != NULL &&
			 $clientData['data'][$this->user->address_field.'_postal_code'] != NULL &&
			 $clientData['data'][$this->user->address_field.'_locality'] != NULL
		);
	}
	protected function getState($clientData){
		$state = $clientData['data'][$this->user->address_field.'_admin_area_level_1'];
		if( $state != NULL ){
			$idState = State::whereName($state)->first()->id_prestashop;
			if(isset($idState) && $idState != NULL)
				return $idState;
		}
		return 0;
	}
	protected function getData($url){
		error_log($url);
		$guzzleClient = new GuzzleHttp\Client();
		try {
			$response = $guzzleClient->get($url);
			error_log($response);
			if(  $response!=null && $response->getStatusCode() == 200 ){
				return json_decode($response->getBody(),true);
			}
		}catch(GuzzleHttp\Exception\ClientException $e){
			error_log($e->getMessage());
		}
	}

}
