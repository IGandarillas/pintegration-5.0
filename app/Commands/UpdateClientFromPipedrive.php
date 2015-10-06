<?php namespace pintegration\Commands;

use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use GuzzleHttp;
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
		if($client != null) {
			$tools = new Tools($this->user_id);
			$tools->editClient($client);
			error_log('address');
			$tools->editAddress($client);
			$dealId = $this->request['current']['id'];
			$orderData = $this->getOrderData($dealId);
			$tools->addCart($client,$orderData);
			//$tools->addOrder($client,$orderData);
		}
		error_log("FIN");


	}
	protected function updateClient($clientId){

		$clientData = $this->getClientData($clientId);
		error_log($clientData['data']['first_name']);
		error_log($clientData['data']['last_name']);
		error_log($clientData['data']['email'][0]['value']);
		$updateClient = null;
		error_log('1');
		if($this->isAddress($clientData)) {
			$updateClient = Client::whereIdClientPipedrive($clientId)->first();
			$updateClient->firstname = $clientData['data']['first_name'];
			$updateClient->lastname = $clientData['data']['last_name'];
			$updateClient->email = $clientData['data']['email'][0]['value'];
			if(isset($updateClient->secure_key)){
				$updateClient->secure_key = md5(uniqid(rand(), true));
			}
			$updateClient->id_client_pipedrive = $clientId;
			$updateClient->user_id = '1';
			$updateClient->update();
			error_log('2');

			$direccion= array(
				'client_id' => $updateClient->id,
				'address1' => $clientData['data'][$this->user->address_field],
				'country' => $clientData['data'][$this->user->address_field.'_country'],
				'postcode' => $clientData['data'][$this->user->address_field.'_postal_code'],
				'city' => $clientData['data'][$this->user->address_field.'_locality']
			);
			Direccion::updateOrCreate($direccion);
			error_log('3');
			return $updateClient;
		}
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
		return ($clientData['data'][$this->user->address_field.'_formatted_address'] != NULL);
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
