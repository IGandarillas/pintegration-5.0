<?php namespace pintegration\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
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
		'pintegration\Console\Commands\checkConsistency',
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
		$cron_options = array(
			'0' => '0',
			'1' => '*/1 * * * * *',
			'2' => '*/5 * * * * *',
			'3' => '0 * * * * *',
			'4' => '0 0 * * * *',
			'5' => '0 0 * * 0 *',
			'6' => '0 0 1 * * *'
		);

		$users = User::all();
		foreach($users as $user) {
			$freq_products = $user->configuration->freq_products;
			$freq_clients = $user->configuration->freq_clients;
			//Change this, not optimum.
			if($freq_products != 0)
			$schedule->call(function () use($user) {
				$task = new SyncPrestashopProducts(3,array('datetime'=>$user->last_products_sync, 'user_id'=>$user->id));
				$task->handle();
			})->cron($cron_options[$freq_products])->name('syncproducts')->withoutOverlapping();
			if($freq_clients != 0)
			$schedule->call(function () use($user) {
				$task = new SyncAllPrestashopClients(3,array('datetime'=>$user->last_clients_sync,'user_id'=>$user->id));
				$task->handle();
			})->cron($cron_options[$freq_clients])->name('syncclients')->withoutOverlapping();
			$schedule->call(function () use($user) {
				$task = new SyncPrestashopProducts(4,array('user_id'=>$user->id));
				$task->handle();
			})->hourly()->name('checkconsistency')->withoutOverlapping();
		}
		//$schedule->command('command:syncpsproducts')
		//	->cron('* * * * *');
		//$schedule->command('command:syncallpsclients')
		//	->cron('* * * * *');
		//$schedule->command('command:syncpsaddresses')
		///	->cron('* * * * *');
	}

}
