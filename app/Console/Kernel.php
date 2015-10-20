<?php namespace pintegration\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use pintegration\Console\Commands\SyncAllPrestashopClients;
use pintegration\Console\Commands\SyncPrestashopProducts;
use pintegration\User;

class Kernel extends ConsoleKernel {

	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		'pintegration\Console\Commands\SyncPrestashopProducts',
		'pintegration\Console\Commands\SyncAllPrestashopClients',
		'pintegration\Console\Commands\SyncAllPrestashopAddresses',
		'pintegration\Console\Commands\SyncPrestashopStates',
		'pintegration\Console\Commands\Seeders\SeedPrestashopCustomers',


	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		$schedule->call(function (){
			Log::info('a');
		})->everyFiveMinutes();

		gi
	}

}
