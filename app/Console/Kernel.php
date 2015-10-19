<?php namespace pintegration\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		'pintegration\Console\Commands\SyncPrestashopClients',
		'pintegration\Console\Commands\SyncPrestashopProducts',
		'pintegration\Console\Commands\SyncPrestashopAddresses',
		'pintegration\Console\Commands\PsCreateOneClient',
		'pintegration\Console\Commands\PdCreateProducts',
		'pintegration\Console\Commands\PdDeleteProducts',
		'pintegration\Console\Commands\Prueba',
		'pintegration\Console\Commands\SeedPrestashopCustomers',
		'pintegration\Console\Commands\SeedPrestashopProducts',
		'pintegration\Console\Commands\SyncAllPrestashopClients',
		'pintegration\Console\Commands\SyncAllPrestashopAddresses',
		'pintegration\Console\Commands\SyncPrestashopStates',
		'pintegration\Console\Commands\SeedFileProducts',

	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		//$schedule->command('command:syncpsproducts')
		//	->cron('* * * * *');
		//$schedule->command('command:syncpsclients')
		//	->cron('* * * * *');
		//$schedule->command('command:syncpsaddresses')
		///	->cron('* * * * *');
	}

}
