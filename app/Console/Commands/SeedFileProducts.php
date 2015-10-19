<?php namespace pintegration\Console\Commands;
use Illuminate\Console\Command;

use Illuminate\Contracts\Bus\SelfHandling;
use Faker;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
class SeedFileProducts extends Command implements SelfHandling {


	protected $name = 'command:seed';
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
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle()
	{

		//File::put(storage_path()."\\products.sql",'vac');
		//Storage::disk('local')->put('products' . '.' . 'sql','');
		//Storage::append('products.sql', 'Appended Text');
		$this->references();

/*

		$path = storage_path()."\\app\\testfile.txt";
		$myfile = fopen($path, "w");


		fwrite($myfile, $txt);
		fclose($myfile);*/
	}
	public function lang(){
		$faker = Faker\Factory::create();
		$price = $faker->randomFloat(3, 1, 550);
		$path = 'C:\Users\yukorff\devpsintegration\storage\lang2.sql';
		$count = 200001;
		for($i=200001;$i<=300000;$i++) {
			if($i%1000==0)
				echo $i.'-';
			if( $i == 200001){
				$txt = "REPLACE INTO `ps_product_lang` (`id_product`, `id_shop`, `id_lang`, `description`, `description_short`, `link_rewrite`, `meta_description`, `meta_keywords`, `meta_title`, `name`, `available_now`, `available_later`) VALUES
";
				file_put_contents($path, $txt, FILE_APPEND);
			}
			if($i%170==0) {
				$name = str_replace("'","",$faker->name());
				$word = $faker->word();
				$txt = "(".$i.", 1, 1, '$name', '$name', '".strtolower($word)."', '', '', '', '".$name."', '', ''),\n";
				$txt .= "(".$i.", 1, 2, '', '', '".strtolower($word)."', '', '', '', '".$name."', '', '');\n";
				$txt .= "REPLACE INTO `ps_product_lang` (`id_product`, `id_shop`, `id_lang`, `description`, `description_short`, `link_rewrite`, `meta_description`, `meta_keywords`, `meta_title`, `name`, `available_now`, `available_later`) VALUES
";

				file_put_contents($path, $txt, FILE_APPEND);
			}else{
				$name = str_replace("'","",$faker->name());
				$word = $faker->word();
				$txt = "(".$i.", 1, 1, '$name', '$name', '".strtolower($word)."', '', '', '', '".$name."', '', ''),\n";
				$txt .= "(".$i.", 1, 2, '', '', '".strtolower($word)."', '', '', '', '".$name."', '', ''),\n";
				file_put_contents($path, $txt, FILE_APPEND);
			}
		}
	}
	public function shop(){

		$faker = Faker\Factory::create();
		$price = $faker->randomFloat(3, 1, 550);
		$path = 'C:\Users\yukorff\devpsintegration\storage\shop.sql';
		$count = 0;
		for($i=194991;$i<=300000;$i++) {
			if($i%1000==0)
				echo $i.'-';
			if( $i == 115999){
				$txt = "INSERT INTO `ps_product_shop` (`id_product`, `id_shop`, `id_category_default`, `id_tax_rules_group`, `on_sale`, `online_only`, `ecotax`, `minimal_quantity`, `price`, `wholesale_price`, `unity`, `unit_price_ratio`, `additional_shipping_cost`, `customizable`, `uploadable_files`, `text_fields`, `active`, `redirect_type`, `id_product_redirected`, `available_for_order`, `available_date`, `condition`, `show_price`, `indexed`, `visibility`, `cache_default_attribute`, `advanced_stock_management`, `date_add`, `date_upd`, `pack_stock_type`) VALUES
";
				file_put_contents($path, $txt, FILE_APPEND);
			}
			if($i%170==0) {
				$price = $faker->randomFloat(3, 1, 550);
				$txt = "(".$i.", 1, 2, 1, 0, 0, '0.000000', 1, '".$price."', '0.000000', '', '0.000000', '0.00', 0, 0, 0, 1, '404', 0, 1, '0000-00-00','new', 1, 0, 'both', 1, 0, '2015-10-15 21:55:06', '2015-10-15 22:38:44', 3);\n";
				$txt .= "INSERT INTO `ps_product_shop` (`id_product`, `id_shop`, `id_category_default`, `id_tax_rules_group`, `on_sale`, `online_only`, `ecotax`, `minimal_quantity`, `price`, `wholesale_price`, `unity`, `unit_price_ratio`, `additional_shipping_cost`, `customizable`, `uploadable_files`, `text_fields`, `active`, `redirect_type`, `id_product_redirected`, `available_for_order`, `available_date`, `condition`, `show_price`, `indexed`, `visibility`, `cache_default_attribute`, `advanced_stock_management`, `date_add`, `date_upd`, `pack_stock_type`) VALUES
";

				file_put_contents($path, $txt, FILE_APPEND);
			}else{
				$price = $faker->randomFloat(3, 1, 550);
				$txt = "(".$i.", 1, 2, 1, 0, 0, '0.000000', 1, '".$price."', '0.000000', '', '0.000000', '0.00', 0, 0, 0, 1, '404', 0, 1, '0000-00-00','new', 1, 0, 'both', 1, 0, '2015-10-15 21:55:06', '2015-10-15 22:38:44', 3),\n";
				file_put_contents($path, $txt, FILE_APPEND);
			}
		}
	}

	public function products(){
		$faker = Faker\Factory::create();
		$price = $faker->randomFloat(3, 1, 550);
		$path = 'C:\Users\yukorff\devpsintegration\storage\file.sql';
		$prices=array();
		for($i=115999;$i<=300000;$i++) {
			if($i%1000==0)
				echo $i.'-';
			if( $i == 115999){
				$txt = "REPLACE INTO `ps_product` (`id_product`, `id_supplier`, `id_manufacturer`, `id_category_default`, `id_shop_default`, `id_tax_rules_group`, `on_sale`, `online_only`, `ean13`, `upc`, `ecotax`, `quantity`, `minimal_quantity`, `price`, `wholesale_price`, `unity`, `unit_price_ratio`, `additional_shipping_cost`, `reference`, `supplier_reference`, `location`, `width`, `height`, `depth`, `weight`, `out_of_stock`, `quantity_discount`, `customizable`, `uploadable_files`, `text_fields`, `active`, `redirect_type`, `id_product_redirected`, `available_for_order`, `available_date`, `condition`, `show_price`, `indexed`, `visibility`, `cache_is_pack`, `cache_has_attachments`, `is_virtual`, `cache_default_attribute`, `date_add`, `date_upd`, `advanced_stock_management`, `pack_stock_type`) VALUES
";
				file_put_contents($path, $txt, FILE_APPEND);
			}
			if($i%170==0) {
				$price = $faker->randomFloat(3, 1, 550);
				array_push($prices,$price);
				$txt = "(".$i.", 0, 0, 0, 1, 0, 0, 0, '', '', '0.000000', 0, 0, '" . $price . "', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 1, '', 0, 0, '0000-00-00', 'new', 0, 1, 'both', 0, 0, 0, 0, '2015-10-15 22:17:25', '2015-10-15 22:17:25', 0, 0);\n";
				$txt .= "REPLACE INTO `ps_product` (`id_product`, `id_supplier`, `id_manufacturer`, `id_category_default`, `id_shop_default`, `id_tax_rules_group`, `on_sale`, `online_only`, `ean13`, `upc`, `ecotax`, `quantity`, `minimal_quantity`, `price`, `wholesale_price`, `unity`, `unit_price_ratio`, `additional_shipping_cost`, `reference`, `supplier_reference`, `location`, `width`, `height`, `depth`, `weight`, `out_of_stock`, `quantity_discount`, `customizable`, `uploadable_files`, `text_fields`, `active`, `redirect_type`, `id_product_redirected`, `available_for_order`, `available_date`, `condition`, `show_price`, `indexed`, `visibility`, `cache_is_pack`, `cache_has_attachments`, `is_virtual`, `cache_default_attribute`, `date_add`, `date_upd`, `advanced_stock_management`, `pack_stock_type`) VALUES
";

				file_put_contents($path, $txt, FILE_APPEND);
			}else{
				$price = $faker->randomFloat(3, 1, 550);
				array_push($prices,$price);
				$txt = "(".$i.", 0, 0, 0, 1, 0, 0, 0, '', '', '0.000000', 0, 0, '" . $price . "', '0.000000', '', '0.000000', '0.00', '', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 1, '', 0, 0, '0000-00-00', 'new', 0, 1, 'both', 0, 0, 0, 0, '2015-10-15 22:17:25', '2015-10-15 22:17:25', 0, 0),
";
				file_put_contents($path, $txt, FILE_APPEND);
			}
		}
		return $prices;
	}
	public function references(){


		$faker = Faker\Factory::create();



			$c=0;
		$path = 'C:\Users\yukorff\devpsintegration\storage\references'.$c++.'.sql';
		$file = fopen($path,"w");
		for($i=1;$i<=300001;$i++) {
			if($i%100001==0){
				$price = $faker->randomFloat(3, 1, 550);
				$txt = "(".$i.", 0, 0, 0, 1, 0, 0, 0, '', '', '0.000000', 0, 0, '" . $price . "', '0.000000', '', '0.000000', '0.00', 'demo_".$i."', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 1, '', 0, 0, '0000-00-00', 'new', 0, 1, 'both', 0, 0, 0, 0, '2015-10-15 22:17:25', '2015-10-15 22:17:25', 0, 0);\n";
				fwrite($file, $txt);
				$path = 'C:\Users\yukorff\devpsintegration\storage\references'.$c++.'.sql';
				$file = fopen($path,"w");
			}

			if( $i == 1){
				$txt = "REPLACE INTO `ps_product` (`id_product`, `id_supplier`, `id_manufacturer`, `id_category_default`, `id_shop_default`, `id_tax_rules_group`, `on_sale`, `online_only`, `ean13`, `upc`, `ecotax`, `quantity`, `minimal_quantity`, `price`, `wholesale_price`, `unity`, `unit_price_ratio`, `additional_shipping_cost`, `reference`, `supplier_reference`, `location`, `width`, `height`, `depth`, `weight`, `out_of_stock`, `quantity_discount`, `customizable`, `uploadable_files`, `text_fields`, `active`, `redirect_type`, `id_product_redirected`, `available_for_order`, `available_date`, `condition`, `show_price`, `indexed`, `visibility`, `cache_is_pack`, `cache_has_attachments`, `is_virtual`, `cache_default_attribute`, `date_add`, `date_upd`, `advanced_stock_management`, `pack_stock_type`) VALUES\n
";
				fwrite($file, $txt);
			}
			if($i%1000==0)
				echo $i.'-';
			if($i%170==0) {
				$price = $faker->randomFloat(3, 1, 550);
				$txt = "(".$i.", 0, 0, 0, 1, 0, 0, 0, '', '', '0.000000', 0, 0, '" . $price . "', '0.000000', '', '0.000000', '0.00', 'demo_".$i."', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 1, '', 0, 0, '0000-00-00', 'new', 0, 1, 'both', 0, 0, 0, 0, '2015-10-15 22:17:25', '2015-10-15 22:17:25', 0, 0);\n";

				$txt .= "REPLACE INTO `ps_product` (`id_product`, `id_supplier`, `id_manufacturer`, `id_category_default`, `id_shop_default`, `id_tax_rules_group`, `on_sale`, `online_only`, `ean13`, `upc`, `ecotax`, `quantity`, `minimal_quantity`, `price`, `wholesale_price`, `unity`, `unit_price_ratio`, `additional_shipping_cost`, `reference`, `supplier_reference`, `location`, `width`, `height`, `depth`, `weight`, `out_of_stock`, `quantity_discount`, `customizable`, `uploadable_files`, `text_fields`, `active`, `redirect_type`, `id_product_redirected`, `available_for_order`, `available_date`, `condition`, `show_price`, `indexed`, `visibility`, `cache_is_pack`, `cache_has_attachments`, `is_virtual`, `cache_default_attribute`, `date_add`, `date_upd`, `advanced_stock_management`, `pack_stock_type`) VALUES\n
";

				fwrite($file, $txt);
			}else{
				$price = $faker->randomFloat(3, 1, 550);
				$txt = "(".$i.", 0, 0, 0, 1, 0, 0, 0, '', '', '0.000000', 0, 0, '" . $price . "', '0.000000', '', '0.000000', '0.00', 'demo_".$i."', '', '', '0.000000', '0.000000', '0.000000', '0.000000', 2, 0, 0, 0, 0, 1, '', 0, 0, '0000-00-00', 'new', 0, 1, 'both', 0, 0, 0, 0, '2015-10-15 22:17:25', '2015-10-15 22:17:25', 0, 0),\n";
				fwrite($file, $txt);
			}



		}

	}
}
