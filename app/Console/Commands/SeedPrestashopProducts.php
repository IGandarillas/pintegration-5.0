<?php namespace pintegration\Console\Commands;

use Illuminate\Console\Command;
use Faker;
use Maatwebsite\Excel\Facades\Excel;
use Tools\Tools;

class SeedPrestashopProducts extends Command  {

	protected $name = 'command:seedproducts';
	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	protected $description = 'Command description.';


	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public static function handle()
	{
		$faker = Faker\Factory::create();

		$tools = new Tools('1');
		$tools->addProductsFake($faker);

	}

}
