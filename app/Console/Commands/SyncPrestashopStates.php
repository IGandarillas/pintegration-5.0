<?php namespace pintegration\Console\Commands;

use Illuminate\Database\QueryException;
use Illuminate\Console\Command;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Support\Facades\Log;
use pintegration\State;
use pintegration\User;
use PSWebS\PrestaShopWebservice;

class SyncPrestashopStates extends Command implements SelfHandling, ShouldBeQueued {

	use InteractsWithQueue, SerializesModels;

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $name = 'command:syncpsstates';

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
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle()
	{
		if(User::count()>0){
			$users = User::all();
			foreach ($users as $user) {
				if(isset($user->prestashop_url,$user->prestashop_api,$user->pipedrive_api)) {
					$this->getAllStates($user);
				}
			}
		}

	}
	public function getStates($user){
		try{
			$webService = new PrestaShopWebservice($user->prestashop_url, $user->prestashop_api, true);
			// Here we set the option array for the Webservice : we want customers resources
			$opt['resource'] = 'states';
			$opt['display'] = '[id,name]';
			$opt['filter[id_country]'] = '6';
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

					$state = State::firstOrNew($clientIdPrestashop);
					$state->id_prestashop = $resource->id;
					$state->name = $resource->name;
					$state->save();
				} catch ( QueryException $e) {
					var_dump($e->errorInfo);
				}

			}
		}
	}
	public function getAllStates($user){
		$totalCount = 0;
		$chunk = 1000;
		$start=0;
		$exit = false;
		$items = array();
		while(!$exit) {


			$webService = new PrestaShopWebservice($user->prestashop_url, $user->prestashop_api, false);
			// Here we set the option array for the Webservice : we want customers resources
			$opt['resource'] = 'states';
			$opt['display'] = '[id,name]';
			$opt['filter[id_country]'] = '6';
			$opt['limit'] = $start . ',' . $chunk;
			$opt['output_format'] = 'JSON';
			//$opt['filter[date_upd]'] = '>['.$user->last_clients_sync.']';
			// Call
			try {
				$json = $webService->get($opt);
				// Here we get the elements from children of customers markup "customer"

			} catch (PrestaShopWebserviceException $e) {
				// Here we are dealing with errors
				echo $e->getTrace();
			}
			$json = json_decode($json,true);
			$itemsCount = count($json['states']);
			if( $itemsCount < $chunk )
				$exit=true;

			foreach ($json['states'] as $resource) {
				$totalCount++;
				$clientIdPrestashop = array(
					'id_prestashop' => $resource['id']
				);

				$state = State::firstOrNew($clientIdPrestashop);
				$state->id_prestashop = $resource['id'];
				$state->name = $resource['name'];
				$state->save();
				$exit=true;
				if($totalCount%50==0){
					Log::info("Total: ".$totalCount." State name: ".$state->name);
				}
				else if($exit && $json['states'][$itemsCount-1]['id']==$resource['id']){
					Log::info("Total: ".$totalCount." State name: ".$state->name);
				}
			}
			$start += $chunk;
		}
	}
}
