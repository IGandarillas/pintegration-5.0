<?php

namespace pintegration\Console\Commands\Seeders;

use Carbon\Carbon;
use Illuminate\Console\Command;
use pintegration\Direccion;
use pintegration\User;
use pintegration\Client;
use PSWebS\PrestaShopWebservice;
use GuzzleHttp;
use Tools\Tools;
use Faker;
class SeedPrestashopCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'command:seedcustomers';

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
    public function handle()
    {
        $this->getClients('1');
    }
    public function getClients($user_id){
        for($i=0;$i<4872;$i++){
            echo $i."\n";
            $faker = Faker\Factory::create();
            $tools = new Tools($user_id);
            $client = new Client();
            $client->firstname = $faker->firstName;
            $client->lastname = $faker->lastName;
            $client->password = $faker->password();
            $client->email = $faker->safeEmail;
            $client->user_id = $user_id;
            $client->save();
            $tools->addClient($client);
            $direccion = array(
                'client_id' => $client->id,
                'address1' => $faker->address(),
                'country' => 'Spain',
                'postcode' => $faker->postcode,
                'city' => $faker->city,
            );
             Direccion::updateOrCreate($direccion);
            $tools->addAddress($client);
             error_log($i);

        }
    }
}