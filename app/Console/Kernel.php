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
		'pintegration\Console\Commands\Inspire',
		'pintegration\Console\Commands\SyncPrestashopClients',
		'pintegration\Console\Commands\SyncPrestashopProducts',
		'pintegration\Console\Commands\SyncPrestashopAddresses',
		'pintegration\Console\Commands\PsCreateOneClient',
		'pintegration\Console\Commands\PdCreateProducts',
		'pintegration\Console\Commands\PdDeleteProducts',
		'pintegration\Console\Commands\Prueba'

	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		$schedule->command('command:syncpsproducts')
			->cron('* * * * *');
		$schedule->command('command:syncpsclients')
			->cron('* * * * *');
		$schedule->command('command:syncpsaddresses')
			->cron('* * * * *');
	}

}
