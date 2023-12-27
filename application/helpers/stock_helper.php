<?php

/**************************
Project Name	: White Label
Created on		: 18 Aug, 2023
Last Modified 	: 04 Sep, 2023
Description		: Page contains Mainrtain Stock REST settings
 ***************************/

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/* this function used validate APP ID */
if (!function_exists('updateStock')) {
	function updateStock($unquieid, $productID, $stock, $type, $stockType)
	{
		$CI = &get_instance();
		$products = $this->Mydb->get_record('product_primary_id, product_company_id,  product_stock', 'products', array('product_company_unique_id' => $unquieid, 'product_id' => $$productID));
		if (!empty($products)) {
			$existStock = $products['product_stock'];
			$updateStock = $existStock;
			if ($type == 'D') {
				$updateStock = $existStock - $stock;
			} else {
				$updateStock = $existStock + $stock;
			}
			$stockItem = array(
				'product_stock_company_id' => $products['product_company_id'],
				'product_stock_company_unquie_id' => $unquieid,
				'product_stock_product_id' => $productID,
				'product_order_primary_id' => $products['product_primary_id'],
				'product_stock_type' => $stockType,
				'product_stock_mode' => 'M',
				'product_stock_value' => $stock,
				'product_stock_created' => current_date(),
				'product_stock_created_ip' => get_ip(),
			);
			$CI->Mydb->insert('product_stock_log', $stockItem);
			$CI->Mydb->update('products', array('product_primary_id' => $products['product_primary_id']), array('product_stock' => $updateStock));
		}
	}
}
