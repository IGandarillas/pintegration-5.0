<?php

namespace pintegration\Console\Commands;

use Illuminate\Console\Command;
use PSWebS\PrestaShopWebservice;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp;
class PsCreateOneClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'command:pscreateoneclient';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

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



}
