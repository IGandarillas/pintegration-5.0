<?php namespace pintegration\Services;
use Illuminate\Support\Facades\Log;
use pintegration\Item;
use Tools\Tools;
/**
 * Created by PhpStorm.
 * User: yukorff
 * Date: 20/10/2015
 * Time: 23:19
 */
class CheckDbConsistency{

    public function __construct()
    {

    }
    public function products(){

    }
    public function deleteProducts($date){
        $deleteProducts = array();

        foreach(Item::all() as $product)
            if($product->updated_at < $date)
                array_push($deleteProducts,$product);

        if(count($deleteProducts)>0) {
            Log::info('Se van a eliminar ' . count($deleteProducts) . ' productos');
            return $deleteProducts;
        }else
            return 0;
    }
}