<?php

namespace pintegration\Console\Commands;

use Illuminate\Console\Command;
use pintegration\User;
use pintegration\Item;
use GuzzleHttp;
use PSWebS\PrestaShopWebservice;
use Faker;
class PdDeleteProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'command:pddeleteproducts';

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
        $this->syncWithPipedrive();
    }
    public function syncWithPipedrive(){
        $client = new GuzzleHttp\Client();
        $res=null;
        for ($i =83  ; $i <= 87; $i++) {
;
            try {
                // https://api.pipedrive.com/v1/
                $res = $client->delete('https://api.pipedrive.com/v1/products/'.$i.'?api_token=20054b8b08c4f0fd0f97a8c796143a50d41778e3');
            }catch(GuzzleHttp\Exception\ClientException $e){
                echo $e->getMessage();

            }catch(GuzzleHttp\Exception\RequestException $e){
                echo $e->getMessage();


            }catch(GuzzleHttp\Exception\AdapterException $e){
                echo $e->getMessage();


            }

        }
    }

}
