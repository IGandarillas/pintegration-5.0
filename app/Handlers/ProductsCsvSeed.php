<?php
/**
 * Created by PhpStorm.
 * User: yukorff
 * Date: 28/09/2015
 * Time: 12:31
 */
namespace pintegration\Handlers;

use Flynsarmy\CsvSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class ProductsCsvSeed extends CsvSeeder\CsvSeeder {

    public function __construct($filePath,$user_id)
    {
        $this->table = 'items';
        $this->filename = $filePath;
        $this->csv_delimiter = ',';
        $this->mapping = [
            0 => 'id_item_prestashop',
            1 => 'code'
        ];
        $this->offset_rows = 1;
        $this->customFields['user_id']=$user_id;

        error_log($this->customFields['user_id']);


    }

    public function run()
    {

        // Recommended when importing larger CSVs
        DB::disableQueryLog();

        // Uncomment the below to wipe the table clean before populating
        DB::table($this->table)->truncate();

        parent::run();
    }
}