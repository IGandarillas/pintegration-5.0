<?php

namespace pintegration\Commands;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\SelfHandling;
use pintegration\Handlers\ProductsCsvSeed;

class ProductsCsvSeedJob extends Command implements SelfHandling
{
    protected $path='hola';
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($path)
    {
        error_log('Seeder c');
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
        $productsCsvSeeder = new ProductsCsvSeed( $this->path );
        //dd($productsCsvSeeder);
        $productsCsvSeeder->run();
    }
}
