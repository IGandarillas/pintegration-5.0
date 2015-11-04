<?php namespace pintegration\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Tools\Tools;
use Pipedrive\Pipedrive;


class checkConsistency extends Command implements SelfHandling, ShouldBeQueued {

	use InteractsWithQueue, SerializesModels;

	protected $name = 'command:checkconsistency';
	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
	//	parent::__construct();
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle()
	{

		$pd = new Pipedrive();
		$name = $pd->searchName('hugo José Acebo Pérez');
		echo "FIN". $name[0],"\n";
		echo $name[1];
	  	//$reload = new SyncPrestashopProducts(4);
		//$reload->handle();
	}

}
