<?php

namespace pintegration\Console\Commands;

use Illuminate\Console\Command;
use pintegration\User;
use pintegration\Item;
use GuzzleHttp;
use PSWebS\PrestaShopWebservice;
use Faker;
class PdCreateProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'command:pdcreateproducts';

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
        $faker = Faker\Factory::create();
        for ($i = 0 ; $i <= 1000000; $i++) {
                $item = new Item();
                $item->name = $faker->name;
                $item->user_id = '1';
                $item->save();
                try {
                    // https://api.pipedrive.com/v1/
                    $res = $client->post('https://api.pipedrive.com/v1/products?api_token=e9748c75a8b8a2179354dd2226665332c04c71ea', [
                        'body' => [
                            'name' => $item->name,
                            'active_flag' => '1',
                            'visible_to' => '3',
                            'owner_id' => '830118',
                            'prices' => '200'
                        ]
                    ]);
                }catch(GuzzleHttp\Exception\ClientException $e){
                    echo $e->getMessage();
                    $item->delete();
                }catch(GuzzleHttp\Exception\RequestException $e){
                    echo $e->getMessage();
                    $item->delete();

                }catch(GuzzleHttp\Exception\AdapterException $e){
                    echo $e->getMessage();
                    $item->delete();

                }

                if( $res->getStatusCode() == 201 && $res!=null ){
                    $jsonResponse = json_decode($res->getBody()->getContents(),true);
                    $item->id_item_pipedrive = $jsonResponse['data']['id'];
                    $item->update();
                }

        }
    }

}
