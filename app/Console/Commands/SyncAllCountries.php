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

class SyncAllPrestashopCountries extends Command implements SelfHandling, ShouldBeQueued {

    use InteractsWithQueue, SerializesModels;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'command:syncpsallcountries';

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
                    $this->getAllCountries($user);
                }
            }
        }

    }
    public function getAllCountries($user){
        $totalCount = 0;
        $chunk = 1000;
        $start=0;
        $exit = false;
        $items = array();
        while(!$exit) {


            $webService = new PrestaShopWebservice($user->prestashop_url, $user->prestashop_api, false);
            // Here we set the option array for the Webservice : we want customers resources
            $opt['resource'] = 'countries';
            $opt['display'] = '[id,name]';
            $opt['limit'] = $start . ',' . $chunk;
            $opt['output_format'] = 'JSON';
            try {
                $json = $webService->get($opt);
                // Here we get the elements from children of customers markup "customer"

            } catch (PrestaShopWebserviceException $e) {
                // Here we are dealing with errors
                echo $e->getTrace();
            }
            $json = json_decode($json,true);
            $itemsCount = count($json['countries']);
            if( $itemsCount < $chunk )
                $exit=true;

            foreach ($json['countries'] as $resource) {
                $totalCount++;
                $clientIdPrestashop = array(
                    'id_prestashop' => $resource['id']
                );

                $country = Country::firstOrNew($clientIdPrestashop);
                $country->id_prestashop = $resource['id'];
                $country->name = $resource['name'];
                $country->save();
                $exit=true;
                if($totalCount%50==0){
                    Log::info("Total: ".$totalCount." Country name: ".$country->name);
                }
                else if($exit && $json['countries'][$itemsCount-1]['id']==$resource['id']){
                    Log::info("Total: ".$totalCount." Country name: ".$country->name);
                }
            }
            $start += $chunk;
        }
    }
}
