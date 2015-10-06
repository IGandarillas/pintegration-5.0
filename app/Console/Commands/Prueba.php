<?php namespace pintegration\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\SelfHandling;
use Faker;
class Prueba extends Command {

	protected $name = 'command:prueba';
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
		$faker = Faker\Factory::create();
		$faker=$faker->password = $faker->password(6,10);
		dd($faker);
		$product = [
			'body' => array(
				'name' => 'asdfasdf',
				'active_flag' => '1',
				'visible_to' => '3',
				'owner_id' => '867597',
				'prices' =>
				[
					array(
						'price' => '100',
						'currency' => 'EUR',
						'overhead_cost' => '100',
						'cost' => '100'
					)
				]
			)
		];
		dd(json_encode($product));
	}

}
