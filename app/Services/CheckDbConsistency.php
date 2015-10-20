<?php namespace pintegration\Services;
use pintegration\Item;
use Tools\Tools;
/**
 * Created by PhpStorm.
 * User: yukorff
 * Date: 20/10/2015
 * Time: 23:19
 */
class CheckDbConsistency{
    public function __construct($jsonReq)
    {
        $tools = new Tools();
    }
    public function products($date){
        $products = Item::all();
        foreach($products as $product){

        }
    }
}