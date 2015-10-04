<?php

namespace pintegration\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use pintegration\Item;
use pintegration\Client;
use PSWebS\PrestaShopWebservice;
use XmlParser;


class SyncPipedrive extends Command{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'command:syncpipedrive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea/Actualiza productos en Pipedrive';

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {   
       // $this->getProducts();
        $this->getClients();
    }

}