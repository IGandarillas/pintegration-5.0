<?php

namespace pintegration\Commands;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\SelfHandling;
use pintegration\Handlers\ProductsCsvSeed;

class ProductsCsvSeedJob extends Command implements SelfHandling
{
    protected $path='';
    protected $user_id='';
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($path,$user_id)
    {
        $this->path = $path;


    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        error_log('Seeder handle');
        $productsCsvSeeder = new ProductsCsvSeed( $this->path, $this->user_id );
        //dd($productsCsvSeeder);
        $productsCsvSeeder->run();
    }
}
