<?php namespace pintegration\Console\Commands;

use pintegration\Commands\Command;

use Illuminate\Contracts\Bus\SelfHandling;

class Prueba extends Command implements SelfHandling {

	protected $name = 'prueba';
	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle()
	{
		$product = [
			'body' => array(
				'name' => 'asdfasdf',
				'active_flag' => '1',
				'visible_to' => '3',
				'owner_id' => '867597',
				'prices' => array(
					'price' => '100',
					'currency' => 'EUR',
					'overhead_cost' => '100',
					'cost' => '100'
				)
			)
		];
		dd($product);
	}

}
