<?php

/**************************
Project Name	: POS
Created on		: 19  Jan, 2021
Last Modified 	: 19  Jan, 2021
Description		:  this file contains Vouchers Helper Functions
***************************/
if (! function_exists ( 'find_voucher_products' )) {
	function find_voucher_products($cart_unique_id = null, $customer_id = null, $app_id = null, $cart_voucher_order_item_id = null) {
		$CI = & get_instance ();

		if(!empty($cart_unique_id)){

			if(!empty($cart_voucher_order_item_id)){

			$voucher_find = $CI->Mydb->get_record("*", "cart_items", array("cart_item_customer_id" => $customer_id, "cart_item_cart_id" => $cart_unique_id, "cart_item_product_type" => "5", "cart_item_product_voucher" => "f", "cart_item_voucher_product_type" => "2", "cart_voucher_order_item_id" => $cart_voucher_order_item_id));
		     }else{

		     	$voucher_find = $CI->Mydb->get_record("*", "cart_items", array("cart_item_customer_id" => $customer_id, "cart_item_cart_id" => $cart_unique_id, "cart_item_product_type" => "5", "cart_item_product_voucher" => "f", "cart_item_voucher_product_type" => "2"));
		     }

			if (! empty ( $voucher_find )) {
				return $voucher_find;				
			} else {				
				return '';
			}
		}
	}
}

if (! function_exists ( 'update_cart_details_items' )) {
	function update_cart_details_items($cart_id, $time = null, $product_id = null) {
		$CI = & get_instance ();
		/* join tables - Outlet table */
		$join [0] ['select'] = "cart_delivery_charge";
		$join [0] ['table'] = "cart_details";
		$join [0] ['condition'] = "cart_item_cart_id = cart_id ";
		$join [0] ['type'] = "INNER";		
		$result = $CI->Mydb->get_all_records ( ' SUM(cart_item_qty) as total_items, SUM(cart_item_total_price) as total_amount ', 'cart_items', array ('cart_item_cart_id' => $cart_id ), 1, '', '', '', '', $join );		
		if (! empty ( $result )) {			
			$timer_array = array ();
			if ($time != "") {
				$timer_array = array (
					'cart_timer' => $time 
				);
			}
			$cart_details_found = $CI->Mydb->get_record("*", "cart_details", array("cart_id" => $cart_id));
			$voucher_found = find_voucher_products($cart_id,$cart_details_found['cart_customer_id'], $cart_details_found['cart_app_id']);
			if(empty($voucher_found) || $voucher_found['cart_voucher_order_item_id']=='0'){
				$total_items = $result [0] ['total_items'];
				$sub_total = $result [0] ['total_amount'];
				$grand_total = $sub_total + $result [0] ['cart_delivery_charge'];
				$CI->Mydb->update ( 'pos_cart_details', array (
					'cart_id' => $cart_id 
				), array_merge ( array (					
					'cart_total_items' => $total_items,
					'cart_sub_total' => $sub_total,
					'cart_grand_total' => $grand_total,
					'cart_updated_on' => current_date () 
				), $timer_array ) );

			}else{
				$get_products = $CI->Mydb->get_record("product_voucher_food_option", "products", array("product_id"=>$product_id));
				if(empty($get_products['product_voucher_food_option'])){
					$get_discount_amount = $CI->Mydb->get_record("*", "products", array("product_id"=>$voucher_found['cart_item_product_id']));
					if($voucher_found['cart_item_product_type'] == "5" && $voucher_found['cart_item_product_voucher'] == "f" && $voucher_found['cart_item_voucher_product_type'] == "2" && $voucher_found['cart_item_voucher_discount_type'] =="2"){
						after_voucher_cart_insert_product_update($cart_id, $time, $get_discount_amount['product_voucher_food_discount_val'], $get_discount_amount['product_primary_id'], $voucher_found['cart_item_voucher_discount_type'], $voucher_found['cart_item_product_id'], $cart_details_found['cart_customer_id']);
					}else if($voucher_found['cart_item_product_type'] == "5" && $voucher_found['cart_item_product_voucher'] == "f" && $voucher_found['cart_item_voucher_product_type'] == "2" && $voucher_found['cart_item_voucher_discount_type'] =="1"){
						after_voucher_cart_insert_product_update($cart_id, $time, $get_discount_amount['product_voucher_food_discount_val'], $get_discount_amount['product_primary_id'], $voucher_found['cart_item_voucher_discount_type'], $voucher_found['cart_item_product_id'], $cart_details_found['cart_customer_id']);
					}
				}else {
					update_cart_details_voucher( $cart_id, $time, $product_id);
				}
			}
		}
	}
}


if (! function_exists ( 'update_product_price_after_voucher' )) {
	function update_product_price_after_voucher($product_id=null, $customer_id=null, $app_id=null,$cart_id=null){
		$CI = & get_instance ();
		$app_id = $app_id;
		$customer_id = $customer_id;		
		$find_cart_item_product = $CI->Mydb->get_record("*", "cart_items", array("cart_item_product_id" => $product_id, "cart_item_customer_id" => $customer_id, 'cart_item_cart_id'=> $cart_id));
		$cart_voucher= $CI->Mydb->get_record("product_primary_id, product_voucher_food_discount_val" , "products", array("product_id" => $product_id));
		if($find_cart_item_product['cart_item_product_type'] == "5" && $find_cart_item_product['cart_item_voucher_discount_type'] == "2" && $find_cart_item_product['cart_item_voucher_discount_type'] == "2" ){
			$whereFindRec =  "cart_item_cart_id='".$cart_id."' AND cart_item_customer_id='".$customer_id."' AND cart_item_product_type!='5' AND cart_item_voucher_id='".$product_id."'";
			$find_record = $CI->Mydb->get_all_records ( '*', 'cart_items', $whereFindRec);			
			if(!empty($find_record)){
				$applied_count = 0;
				$balance_qty = $find_cart_item_product['cart_item_qty'];
				$i = 0;
				foreach ($find_record as $val) {
					$find_product_is_voucher_any_product = $CI->Mydb->get_record("*", "product_voucher_food_discount", array("food_discount_food_product_id" => $cart_voucher['product_primary_id']));
					if(!empty($find_product_is_voucher_any_product)){
						$find_product_is_voucher = $CI->Mydb->get_record("*", "product_voucher_food_discount", array("food_discount_product_id" => $val['cart_item_product_id'], "food_discount_food_product_id" => $cart_voucher['product_primary_id']));
						if($find_product_is_voucher['food_discount_product_id'] == $val['cart_item_product_id']){
							$variable_count = $balance_qty - $applied_count;
							$product_unit_price = $val['cart_item_unit_price'];
							$discount_amount_product = $product_unit_price * $cart_voucher['product_voucher_food_discount_val'] / 100;
							if($variable_count>0){
								if($variable_count == $val['cart_item_qty']){
									$discount_total_price = $discount_amount_product * $variable_count;
									$applied_count += $val['cart_item_qty'];
								}else if($variable_count > $val['cart_item_qty']){
									$discount_total_price = $discount_amount_product * $val['cart_item_qty'];
									$identify_quantiy = $balance_qty - $val['cart_item_qty'];
									$applied_count += $val['cart_item_qty'];
								}else if($variable_count < $val['cart_item_qty']){
									$discount_total_price = $discount_amount_product * $variable_count;	
									$applied_count += $balance_qty;
								}
								$discount_val_total_price = $val['cart_item_unit_price'] * $val['cart_item_qty'] - $discount_total_price;

								$CI->Mydb->update ( 'cart_items', array (
									'cart_item_customer_id ' => $customer_id,
									'cart_item_product_id' => $val['cart_item_product_id'], 
									'cart_item_id' => $val['cart_item_id']
								), array (
									'cart_item_total_price' => $discount_val_total_price,
									'cart_item_voucher_id' => $find_cart_item_product['cart_item_product_id'], 
								) );
							}else{
								$unit_price = $val['cart_item_unit_price'];
								$total_price = $unit_price * $val['cart_item_qty'];
								/*Discount calculation - refer insert_cart_items*/
								$item_promotion_discount = null;
								if((float)$val['cart_item_promotion_discount'] > 0) {
									$item_promotion_discount = ($val['cart_item_actual_unit_price']-$unit_price)* $val['cart_item_qty'];

								}
								$updated = $CI->Mydb->update ( 'cart_items', array (
									'cart_item_customer_id ' => $customer_id,
									'cart_item_product_id' => $val['cart_item_product_id'],  
									'cart_item_id' => $val['cart_item_id']
								), array (
									'cart_item_total_price' => $total_price,
									'cart_item_voucher_id' => '',
									'cart_item_promotion_discount'=>$item_promotion_discount
								) );
							}
						}
					}else{
						$unit_price = $val['cart_item_unit_price'];
						$total_price = $unit_price * $val['cart_item_qty'];
						/*Discount calculation - refer insert_cart_items*/
						$item_promotion_discount = null;
						if((float)$val['cart_item_promotion_discount'] > 0) {
							$item_promotion_discount = ($val['cart_item_actual_unit_price']-$unit_price)* $val['cart_item_qty'];
						}
						$updated = $CI->Mydb->update ( 'cart_items', array (
							'cart_item_customer_id ' => $customer_id,
							'cart_item_product_id' => $val['cart_item_product_id'],  
							'cart_item_id' => $val['cart_item_id']
						), array (
							'cart_item_total_price' => $total_price,
							'cart_item_voucher_id' => '',
							'cart_item_promotion_discount'=>$item_promotion_discount
						) );
					}
				}
			}
		}
	}
}

if (! function_exists ( 'update_voucher_amount_cart' )) {
	function update_voucher_amount_cart($customer_id = null, $app_id = null, $voucher_type = null,$discount_val = null, $discount_type=null, $discount_type_method=null, $time=null, $cart_id=null, $cart_item_id=null, $product_id=null){
		$CI = & get_instance ();
		if($discount_type == "1"){
			$update_type = 'update';
			update_product_total_price_voucher_fixed($product_id, $customer_id, $app_id, $cart_id, $discount_type, $discount_val, $update_type);
		}else if($discount_type == "2"){
			update_product_price_after_voucher($product_id, $customer_id, $app_id,$cart_id);

			/* join tables - Outlet table */
			$join [0] ['select'] = "cart_delivery_charge";
			$join [0] ['table'] = "cart_details";
			$join [0] ['condition'] = "cart_item_cart_id = cart_id ";
			$join [0] ['type'] = "INNER";

			$result = $CI->Mydb->get_all_records ( ' SUM(cart_item_qty) as total_items, SUM(cart_item_total_price) as total_amount,SUM(cart_item_promotion_discount) as special_discount ', 'cart_items', array (
				'cart_item_cart_id' => $cart_id,  
			), 1, '', '', '', '', $join );

			$timer_array = array ();
			if ($time != "") {
				$timer_array = array (
					'cart_timer' => $time 
				);
			}

			$total_items = $result [0] ['total_items'];
			$sub_total = $result [0] ['total_amount'];
			$grand_total = $sub_total + $result [0] ['cart_delivery_charge'];
			$special_discount = $result [0] ['special_discount'];
			$discount_type_array = array ();
			if ($discount_type != '' && $special_discount > 0) {
				$discount_type_array = array ('cart_special_discount_type' => $discount_type);
			} else if ($special_discount <= 0) {
				$discount_type_array = array ('cart_special_discount_type' => null);
			}
			if($grand_total > 0){
				$grand_total = $grand_total;
			}else{
				$grand_total = '0.00';
			}

			$CI->Mydb->update ( 'cart_details', array (
				'cart_id' => $cart_id, 
			), array_merge ( array (
				'cart_total_items' => $total_items,
				'cart_sub_total' => $sub_total,
				'cart_grand_total' => $grand_total,
				'cart_special_discount' => $special_discount,
				'cart_updated_on' => current_date () 
			), $timer_array ,$discount_type_array) );
		}
	}
}

if (! function_exists ( 'after_voucher_cart_details_update' )) {
	function after_voucher_cart_details_update($cart_id, $time = null, $discount_val = null) {
		$CI = & get_instance ();
		/* join tables - Outlet table */
		$join [0] ['select'] = "cart_delivery_charge";
		$join [0] ['table'] = "cart_details";
		$join [0] ['condition'] = "cart_item_cart_id = cart_id ";
		$join [0] ['type'] = "INNER";
		
		$result = $CI->Mydb->get_all_records ( ' SUM(cart_item_qty) as total_items, SUM(cart_item_total_price) as total_amount ', 'cart_items', array (
			'cart_item_cart_id' => $cart_id 
		), 1, '', '', '', '', $join );
		if (! empty ( $result )) {			
			$timer_array = array ();
			if ($time != "") {
				$timer_array = array (
					'cart_timer' => $time 
				);
			}
			$total_items = $result [0] ['total_items'];
			$sub_total = $result [0] ['total_amount'] - $discount_val;
			if($sub_total < 0){
				$sub_total = "0.00";
				$grand_total = "0.00";
			}else{
				$grand_total = $sub_total + $result [0] ['cart_delivery_charge'];
			}		

			$CI->Mydb->update ( 'pos_cart_details', array (
				'cart_id' => $cart_id 
			), array_merge ( array (

				'cart_total_items' => $total_items,
				'cart_sub_total' => $sub_total,
				'cart_grand_total' => $grand_total,
				'cart_updated_on' => current_date () 
			), $timer_array ) );

		}
	}
}


if (! function_exists ( 'after_voucher_cart_insert_product_update' )) {
	function after_voucher_cart_insert_product_update($cart_id=null, $time=null, $discount_amount=null, $voucher_product_primary_id=null, $type=null, $voucher_product_id=null, $customer_id=null){
		$CI = & get_instance ();
		if($type == "2"){
			$find_record = $CI->Mydb->get_all_records ( '*', 'cart_items', array (
				'cart_item_cart_id' => $cart_id,
				'cart_item_product_type!=' => '5'
			));
			$voucher_qty = $CI->Mydb->get_record("*", "cart_items", array("cart_item_product_id" => $voucher_product_id, "cart_item_customer_id" => $customer_id, 'cart_item_cart_id' => $cart_id));
			$check_voucher_food_product = $CI->Mydb->get_record("product_primary_id,product_voucher_food_discount_val" , "products", array("product_id" => $voucher_qty['cart_item_product_id']));
			$applied_count = 0;
			$balance_qty = $voucher_qty['cart_item_qty'];
			foreach ($find_record as  $val) {
				$find_product_is_voucher = $CI->Mydb->get_record("*", "product_voucher_food_discount", array("food_discount_product_id" => $val['cart_item_product_id'], "food_discount_food_product_id" => $voucher_product_primary_id));
				if(!empty($find_product_is_voucher)){
					if($find_product_is_voucher['food_discount_product_id'] == $val['cart_item_product_id']){
						$variable_count = $balance_qty - $applied_count;
						$product_price = $val['cart_item_unit_price'];
						$voucher_discount_amount = $val['cart_item_unit_price'] * $discount_amount / 100;
						if($variable_count > 0){
							if($variable_count == $val['cart_item_qty']){
								$discount_total_price = $voucher_discount_amount * $variable_count;
								$applied_count += $val['cart_item_qty'];
							}else if($variable_count > $val['cart_item_qty']){
								$discount_total_price = $voucher_discount_amount * $val['cart_item_qty'];
								$identify_quantiy = $balance_qty - $val['cart_item_qty'];
								$applied_count += $val['cart_item_qty'];
							}else if($variable_count < $val['cart_item_qty']){
								$discount_total_price = $voucher_discount_amount * $variable_count;
								$applied_count += $balance_qty;
							}

							if($variable_count < $val['cart_item_qty']){
								$new_variable_count = $variable_count;
							}else{
								$new_variable_count = $val['cart_item_qty'];
							}

							if($val['cart_item_unit_price'] > $cart_voucher['product_voucher_food_discount_val']){
								if($discount_total_price > 0){
									$discount_val_total_price = $val['cart_item_unit_price'] * $val['cart_item_qty'] - $discount_total_price;
								}else{
									$discount_val_total_price = "0.00";
								}
							}else{
								if($discount_total_price > 0){
									$calculate_qty = $val['cart_item_qty'] - $new_variable_count;
									$discount_val_total_price = $val['cart_item_unit_price'] * $calculate_qty;	

								}else{
									$discount_val_total_price = "0.00";
								}
							}

							$CI->Mydb->update ( 'cart_items', array (
								'cart_item_product_id' => $val['cart_item_product_id'],
								'cart_item_id' => $val['cart_item_id']
							), array (
								'cart_item_total_price' => $discount_val_total_price,
								'cart_item_voucher_id' => $voucher_qty['cart_item_product_id'],

							) );

						}else{
							$product_sub_total = $val['cart_item_unit_price'] * $val['cart_item_qty'];
							$CI->Mydb->update ( 'cart_items', array (
								'cart_item_product_id' => $val['cart_item_product_id'],
								'cart_item_id' => $val['cart_item_id']
							), array (
								'cart_item_total_price' => $product_sub_total,
								'cart_item_voucher_id' => ""
							) );
						}
					}

				}else{
					$join [0] ['select'] = "cart_delivery_charge,cart_voucher_discount_amount";
					$join [0] ['table'] = "cart_details";
					$join [0] ['condition'] = "cart_item_cart_id = cart_id ";
					$join [0] ['type'] = "INNER";
					$result = $CI->Mydb->get_all_records ( ' SUM(cart_item_qty) as total_items, SUM(cart_item_total_price) as total_amount,SUM(cart_item_promotion_discount) as special_discount ', 'cart_items', array (
						'cart_item_cart_id' => $cart_id,  
					), 1, '', '', '', '', $join );

					$timer_array = array ();
					if ($time != "") {
						$timer_array = array (
							'cart_timer' => $time 
						);
					}

					$total_items = $result [0] ['total_items'];
					$sub_total = $result [0] ['total_amount'];
					$old_voucher_discount_amount = $result [0] ['cart_voucher_discount_amount'];
					$special_discount = $result [0] ['special_discount'];
					$discount_type_array = array ();
					if ($discount_type != '' && $special_discount > 0) {
						$discount_type_array = array ('cart_special_discount_type' => $discount_type);
					} else if ($special_discount <= 0) {
						$discount_type_array = array ('cart_special_discount_type' => null);
					} else {}


					$current_amount = $sub_total - $old_voucher_discount_amount;

					if($current_amount > 0){
						$current_amount = $current_amount;
					}else{
						$current_amount = '0.00';
					}

					$discount_calcualte_amount = $current_amount * $check_voucher_food_product['product_voucher_food_discount_val'] / 100;
					$multiply_of_discount_amount = $discount_calcualte_amount * $voucher_qty['cart_item_qty'];

					if($multiply_of_discount_amount > $sub_total){
						$multiply_of_discount_amount = $sub_total;
					}else{
						$multiply_of_discount_amount = $multiply_of_discount_amount;
					}

					if($discount_calcualte_amount > 0){
						$discount_calcualte_amount = $multiply_of_discount_amount + $old_voucher_discount_amount;
						$grand_total = $multiply_of_discount_amount + $result [0] ['cart_delivery_charge'];
					}else{
						$grand_total = '0.00' + $result [0] ['cart_delivery_charge'];
					}

					$CI->Mydb->update ( 'cart_details', array (
						'cart_id' => $cart_id, 
					), array_merge ( array (
						'cart_voucher_discount_amount' => $multiply_of_discount_amount, 
						'cart_total_items' => $total_items,
						'cart_sub_total' => $sub_total,
						'cart_grand_total' => $grand_total,
						'cart_special_discount' => $special_discount,
						'cart_updated_on' => current_date () 
					), $timer_array ,$discount_type_array) );
				}
			}

			/* join tables - Outlet table */
			$join [0] ['select'] = "cart_delivery_charge,cart_voucher_discount_amount ";
			$join [0] ['table'] = "cart_details";
			$join [0] ['condition'] = "cart_item_cart_id = cart_id ";
			$join [0] ['type'] = "INNER";

			$result = $CI->Mydb->get_all_records ( ' SUM(cart_item_qty) as total_items, SUM(cart_item_total_price) as total_amount ', 'cart_items', array (
				'cart_item_cart_id' => $cart_id 
			), 1, '', '', '', '', $join );

			if (! empty ( $result )) {

				$timer_array = array ();
				if ($time != "") {
					$timer_array = array (
						'cart_timer' => $time 
					);
				}

				$total_items = $result [0] ['total_items'];
				$sub_total = $result [0] ['total_amount'];
				$grand_total = $sub_total + $result [0] ['cart_delivery_charge'];

				$CI->Mydb->update ( 'pos_cart_details', array (
					'cart_id' => $cart_id 
				), array_merge ( array (
					
					'cart_total_items' => $total_items,
					'cart_sub_total' => $sub_total,
					'cart_grand_total' => $grand_total,
					'cart_updated_on' => current_date () 
				), $timer_array ) );
			}

		}else if($type == "1"){

			update_product_total_price_voucher_fixed($voucher_product_id, $customer_id, $app_id, $cart_id, $type, $discount_amount);

		}
	}
}

if (! function_exists ( 'update_product_total_price_voucher_fixed' )) {

	function update_product_total_price_voucher_fixed($product_id=null, $customer_id=null, $app_id=null, $cart_id=null, $discount_type=null, $discount_val=null, $cart_item_id = null){

		$CI = & get_instance ();

		$app_id = $app_id;
		$customer_id = $customer_id;

		if(!empty($cart_item_id)){
		$find_cart_item_product = $CI->Mydb->get_record("cart_item_product_type,cart_item_voucher_discount_type,cart_item_qty,cart_item_product_id, cart_item_voucher_product_type, ", "cart_items", array("cart_item_product_id" => $product_id, "cart_item_customer_id" => $customer_id, 'cart_item_cart_id' => $cart_id, "cart_item_id" => $cart_item_id));
	    }else{

	    $find_cart_item_product = $CI->Mydb->get_record("cart_item_product_type,cart_item_voucher_discount_type,cart_item_qty,cart_item_product_id, cart_item_voucher_product_type, ", "cart_items", array("cart_item_product_id" => $product_id, "cart_item_customer_id" => $customer_id, 'cart_item_cart_id' => $cart_id));	
	    }

		$cart_voucher = $CI->Mydb->get_record("product_primary_id, product_voucher_food_discount_val" , "products", array("product_id" => $product_id));
	
		if($find_cart_item_product['cart_item_product_type'] == "5" && $find_cart_item_product['cart_item_voucher_product_type'] == "2" && $find_cart_item_product['cart_item_voucher_discount_type'] == "1" ){

			$find_record = $CI->Mydb->get_all_records ( '*', 'cart_items', array (
				'cart_item_cart_id' => $cart_id,
				'cart_item_customer_id'=>$customer_id,
				'cart_item_product_type!=' => '5',
			));

			$applied_count = 0;
			$balance_qty = $find_cart_item_product['cart_item_qty'];
			$product_selected_voucher = 0;

			$find_product_is_voucher_any_product = $CI->Mydb->get_record("*", "product_voucher_food_discount", array("food_discount_food_product_id" => $cart_voucher['product_primary_id']));
	
			if(!empty($find_product_is_voucher_any_product)){

				foreach ($find_record as  $val) {

					$find_product_is_voucher = $CI->Mydb->get_record("*", "product_voucher_food_discount", array("food_discount_product_id" => $val['cart_item_product_id'], "food_discount_food_product_id" => $cart_voucher['product_primary_id']));

					if(empty($val['cart_item_voucher_id'])){

						if($find_product_is_voucher['food_discount_product_id'] == $val['cart_item_product_id']){	
							$variable_count = $balance_qty - $applied_count;
							$product_unit_price = $val['cart_item_unit_price'];

							if($variable_count > 0){
								if($variable_count == $val['cart_item_qty']){
									$discount_total_price = $cart_voucher['product_voucher_food_discount_val'] * $variable_count;
									$applied_count += $val['cart_item_qty'];
								}else if($variable_count > $val['cart_item_qty']){
									$discount_total_price = $cart_voucher['product_voucher_food_discount_val'] * $val['cart_item_qty'];
									$identify_quantiy = $balance_qty - $val['cart_item_qty'];
									$applied_count += $val['cart_item_qty'];
								}else if($variable_count < $val['cart_item_qty']){
									$discount_total_price = $cart_voucher['product_voucher_food_discount_val'] * $variable_count;
									$applied_count += $balance_qty;
								}

								if($variable_count < $val['cart_item_qty']){
									$new_variable_count = $variable_count;
								}else{
									$new_variable_count = $val['cart_item_qty'];
								}

								if($val['cart_item_unit_price'] > $cart_voucher['product_voucher_food_discount_val']){
									if($discount_total_price > 0){
										$discount_val_total_price = $val['cart_item_unit_price'] * $val['cart_item_qty'] - $discount_total_price;
									}else{
										$discount_val_total_price = "0.00";
									}
								}else{
									if($discount_total_price > 0){
										$calculate_qty = $val['cart_item_qty'] - $new_variable_count;
										$discount_val_total_price = $val['cart_item_unit_price'] * $calculate_qty;	
									}else{
										$discount_val_total_price = "0.00";
									}
								}

								if($discount_val_total_price > 0){
									$discount_val_total_price = $discount_val_total_price;
								}else{
									$discount_val_total_price = "0.00";
								}

								$CI->Mydb->update ( 'cart_items', array (
									'cart_item_customer_id ' => $customer_id,
									'cart_item_product_id' => $val['cart_item_product_id'], 
									'cart_item_id' => $val['cart_item_id']
								), array (
									'cart_item_total_price' => $discount_val_total_price,
									'cart_item_voucher_id' => $find_cart_item_product['cart_item_product_id'], 
								) );


								$join [0] ['select'] = "cart_delivery_charge, cart_voucher_discount_amount";
								$join [0] ['table'] = "cart_details";
								$join [0] ['condition'] = "cart_item_cart_id = cart_id ";
								$join [0] ['type'] = "INNER";

								$result = $CI->Mydb->get_all_records ( ' SUM(cart_item_qty) as total_items, SUM(cart_item_total_price) as total_amount ', 'cart_items', array (
									'cart_item_cart_id' => $cart_id 
								), 1, '', '', '', '', $join );

								$update_sub_total = $result [0] ['total_amount'];
								$existing_cart_discount_amount = $result[0]['cart_voucher_discount_amount'];
								$update_grand_total = $update_sub_total + $result [0] ['cart_delivery_charge'];
								if($update_grand_total < 0 ){
									$update_sub_total = "0.00";
									$update_grand_total = "0.00";
								}else{
									$update_sub_total = $result [0] ['total_amount'];
									$update_grand_total = $update_sub_total + $result [0] ['cart_delivery_charge'];
								}
								if($existing_cart_discount_amount > 0){
									$update_grand_total = $update_grand_total - $existing_cart_discount_amount;
									if($update_grand_total > 0){
										$update_grand_total = $update_grand_total;		  		
									}else{

										$update_grand_total = '0.00';
									}

									$CI->Mydb->update ( 'cart_details', array (
										'cart_customer_id' => $customer_id,
										'cart_id' => $cart_id 
									), array (
										'cart_sub_total' => $update_sub_total,
										'cart_grand_total' => $update_grand_total,
									) );

								}else{
									$CI->Mydb->update ( 'cart_details', array (
										'cart_customer_id' => $customer_id,
										'cart_id' => $cart_id 
									), array (
										'cart_sub_total' => $update_sub_total,
										'cart_grand_total' => $update_grand_total,
										'cart_voucher_discount_amount' => ''

									) );
								}

							}else{

								$product_total_price = $val['cart_item_unit_price'] * $val['cart_item_qty'];
								$CI->Mydb->update ( 'cart_items', array (
									'cart_item_customer_id ' => $customer_id,
									'cart_item_product_id' => $val['cart_item_product_id'], 
									'cart_item_id' => $val['cart_item_id']
								), array (
									'cart_item_total_price' => $product_total_price,
									'cart_item_voucher_id' =>  '', 
								) );

								$join [0] ['select'] = "cart_delivery_charge, cart_voucher_discount_amount";
								$join [0] ['table'] = "cart_details";
								$join [0] ['condition'] = "cart_item_cart_id = cart_id ";
								$join [0] ['type'] = "INNER";

								$result = $CI->Mydb->get_all_records ( ' SUM(cart_item_qty) as total_items, SUM(cart_item_total_price) as total_amount ', 'cart_items', array (
									'cart_item_cart_id' => $cart_id 
								), 1, '', '', '', '', $join );

								$existing_cart_discount_amount = $result[0]['cart_voucher_discount_amount'];
								$update_sub_total = $result [0] ['total_amount'];
								$update_grand_total = $update_sub_total + $result [0] ['cart_delivery_charge'];
								if($update_grand_total < 0 ){
									$update_sub_total = "0.00";
									$update_grand_total = "0.00";
								}else{
									$update_sub_total = $result [0] ['total_amount'];
									$update_grand_total = $update_sub_total + $result [0] ['cart_delivery_charge'];
								}

								if($existing_cart_discount_amount > 0){
									$update_grand_total = $update_grand_total - $existing_cart_discount_amount;
									if($update_grand_total > 0){
										$update_grand_total = $update_grand_total;		  		
									}else{

										$update_grand_total = '0.00';
									}

									$CI->Mydb->update ( 'cart_details', array (
										'cart_customer_id' => $customer_id,
										'cart_id' => $cart_id 
									), array (
										'cart_sub_total' => $update_sub_total,
										'cart_grand_total' => $update_grand_total,
									) );

								}else{
									$CI->Mydb->update ( 'cart_details', array (
										'cart_customer_id' => $customer_id,
										'cart_item_cart_id' => $cart_id 
									), array (
										'cart_sub_total' => $update_sub_total,
										'cart_grand_total' => $update_grand_total,
										'cart_voucher_discount_amount' => ''

									) );
								}
							}

						}
					}
				}

			}else{

				$join [0] ['select'] = "cart_delivery_charge,cart_voucher_discount_amount";
				$join [0] ['table'] = "cart_details";
				$join [0] ['condition'] = "cart_item_cart_id = cart_id";
				$join [0] ['type'] = "INNER";

				$result = $CI->Mydb->get_all_records ( ' SUM(cart_item_qty) as total_items, SUM(cart_item_total_price) as total_amount ', 'cart_items', array (
					'cart_item_cart_id' => $cart_id
				), 1, '', '', '', '', $join );
				echo "<pre>";
				print_r($result);
				exit;
				$existing_discount_amount = $result[0]['cart_voucher_discount_amount'];
				if($find_cart_item_product['cart_item_qty'] > 1){
					$discount_total_amount = $discount_val * $find_cart_item_product['cart_item_qty'];
					$new_discount_amount =  $discount_total_amount;
				}else{
					$discount_total_amount = $discount_val * $find_cart_item_product['cart_item_qty'];
					$new_discount_amount =   $discount_total_amount;
				}

				$update_sub_total = $result [0] ['total_amount'] - $new_discount_amount;
				$update_grand_total = $update_sub_total + $result [0] ['cart_delivery_charge'];

				if($update_sub_total < 0 ){
					$update_sub_total = $result [0] ['total_amount'];
					$update_grand_total = "0.00";
				}else{
					$update_sub_total = $result [0] ['total_amount'];
					$update_grand_total = $update_sub_total + $result [0] ['cart_delivery_charge'] - $new_discount_amount;
				}

				$CI->Mydb->update ( 'cart_details', array (
					'cart_customer_id' => $customer_id,
					'cart_id' => $cart_id 
				), array (
					'cart_voucher_discount_amount' => $new_discount_amount,
					'cart_sub_total' => $update_sub_total,
					'cart_grand_total' => $update_grand_total
				) );
			}
		}
	}

}


if (! function_exists ( 'update_voucher_amount' )) {

	function update_voucher_amount($app_id = null,$customer_id = null,$voucher_type = null,$discount_val = null, $discount_type=null, $product_id=null, $cart_id=null, $cart_item_id = null){

		$CI = & get_instance ();
		$app_id = $app_id;
		$customer_id = $customer_id;

		if($discount_type == "1"){

			update_product_total_price_voucher_fixed($product_id, $customer_id, $app_id, $cart_id, $discount_type, $discount_val, $cart_item_id);

			$any_cart_item_voucher_discount_type = $CI->Mydb->get_all_records("*", 'cart_items', array("cart_item_voucher_product_type" => '2', 'cart_item_voucher_discount_type' => '2', 'cart_item_customer_id' => $customer_id, 'cart_item_cart_id' => $cart_id));

			foreach ($any_cart_item_voucher_discount_type as  $value) {

				$get_product_discount_details = $CI->Mydb->get_record("product_primary_id,product_voucher_food_discount_val", "products", array("product_id" =>$value['cart_item_product_id']));

				$get_product_discount_voucher_all = $CI->Mydb->get_record("*", "product_voucher_food_discount", array("food_discount_food_product_id" => $get_product_discount_details['product_primary_id']));
				if(empty($get_product_discount_voucher_all)){
					$all_product_voucher_details = $value['cart_item_product_id'];
					$all_product_voucher_details_qty = $value['cart_item_qty'];
				}
			}


			if(!empty($all_product_voucher_details)){
				$get_product_discount_details = $CI->Mydb->get_record("product_voucher_food_discount_val", "products", array("product_id" =>$all_product_voucher_details));
				$join [0] ['select'] = "cart_delivery_charge,cart_voucher_discount_amount";
				$join [0] ['table'] = "cart_details";
				$join [0] ['condition'] = "cart_item_cart_id = cart_id ";
				$join [0] ['type'] = "INNER";

				$result = $CI->Mydb->get_all_records ( ' SUM(cart_item_qty) as total_items, SUM(cart_item_total_price) as total_amount ', 'cart_items', array (
					'cart_item_cart_id' => $cart_id
				), 1, '', '', '', '', $join );

				$total_items = $result [0] ['total_items'];
				$sub_total = $result [0] ['total_amount'];

				$calcualte_discount_amount = $sub_total * $get_product_discount_details['product_voucher_food_discount_val'] / 100;

				if($all_product_voucher_details_qty > 1){
					$multiply_of_discount_amount = $calcualte_discount_amount * $all_product_voucher_details_qty;
				}else{
					$discount_amount = $sub_total;
					$calcualte_discount_amount = $discount_amount * $get_product_discount_details['product_voucher_food_discount_val'] / 100;
					$multiply_of_discount_amount = $calcualte_discount_amount;
				}

				if($calcualte_discount_amount > 0){
					$calcualte_discount_amount  = $sub_total - $multiply_of_discount_amount + $result [0] ['cart_delivery_charge'];
					$existing_cart_discount_amount = $multiply_of_discount_amount;
				}else{
					$calcualte_discount_amount  = "0.00"  + $result [0] ['cart_delivery_charge'];
					$existing_cart_discount_amount = $multiply_of_discount_amount;
				}

				if($calcualte_discount_amount > 0){
					$calcualte_discount_amount = $calcualte_discount_amount;
				}else{
					$calcualte_discount_amount = '0.00';
				}
				$CI->Mydb->update ( 'cart_details', array (
					'cart_id' => $cart_id 
				),array (
					'cart_voucher_discount_amount' => $existing_cart_discount_amount,
					'cart_total_items' => $total_items,
					'cart_sub_total' => $sub_total,
					'cart_grand_total' => $calcualte_discount_amount,
					'cart_updated_on' => current_date () 
				));
			}
		}else if($discount_type == "2"){

			update_product_total_price_voucher($product_id, $customer_id, $app_id,$cart_id, $cart_item_id);
 
			$find_cart_item_product = $CI->Mydb->get_record("*", "cart_items", array("cart_item_product_id" => $product_id, "cart_item_customer_id" => $customer_id, 'cart_item_cart_id' => $cart_id));

			$cart_voucher= $CI->Mydb->get_record("product_primary_id,product_voucher_food_discount_val" , "products", array("product_id" => $product_id));

			if($find_cart_item_product['cart_item_product_type'] == "5" && $find_cart_item_product['cart_item_voucher_discount_type'] == "2" && $find_cart_item_product['cart_item_voucher_discount_type'] == "2" ){

				$find_all_voucher_all_discount  = $CI->Mydb->get_record("*", "product_voucher_food_discount", array("food_discount_food_product_id" => $cart_voucher['product_primary_id']));


				if(!empty($find_all_voucher_all_discount)){

					$join [0] ['select'] = "cart_delivery_charge,cart_voucher_discount_amount";
					$join [0] ['table'] = "cart_details";
					$join [0] ['condition'] = "cart_item_cart_id = cart_id ";
					$join [0] ['type'] = "INNER";

					$result = $CI->Mydb->get_all_records ( ' SUM(cart_item_qty) as total_items, SUM(cart_item_total_price) as total_amount ', 'cart_items', array (
						'cart_item_cart_id' => $cart_id
					), 1, '', '', '', '', $join );

					$any_cart_item_voucher_discount_type = $CI->Mydb->get_all_records("*", 'cart_items', array("cart_item_voucher_product_type" => '2', 'cart_item_voucher_discount_type' => '2', 'cart_item_customer_id' => $customer_id, 'cart_item_cart_id' => $cart_id));

					foreach ($any_cart_item_voucher_discount_type as  $value) {
						
						$get_product_discount_details = $CI->Mydb->get_record("product_primary_id,product_voucher_food_discount_val", "products", array("product_id" =>$value['cart_item_product_id']));

						$get_product_discount_voucher_all = $CI->Mydb->get_record("*", "product_voucher_food_discount", array("food_discount_food_product_id" => $get_product_discount_details['product_primary_id']));

						if(empty($get_product_discount_voucher_all)){

							$all_product_voucher_details = $value['cart_item_product_id'];

							$all_product_voucher_details_qty = $value['cart_item_qty'];
						}
					}

					if(!empty($all_product_voucher_details)){

						$get_product_discount_details = $CI->Mydb->get_record("product_voucher_food_discount_val", "products", array("product_id" =>$all_product_voucher_details));

						$sub_total = $result [0] ['total_amount'];

						$calcualte_discount_amount = $sub_total * $get_product_discount_details['product_voucher_food_discount_val'] / 100;

						if($all_product_voucher_details_qty > 1){
							$multiply_of_discount_amount = $calcualte_discount_amount * $all_product_voucher_details_qty;
						}else{
							$discount_amount = $sub_total;
							$calcualte_discount_amount = $discount_amount * $get_product_discount_details['product_voucher_food_discount_val'] / 100;
							$multiply_of_discount_amount = $calcualte_discount_amount;
						}

						if($calcualte_discount_amount > 0){
							$calcualte_discount_amount  = $sub_total - $multiply_of_discount_amount + $result [0] ['cart_delivery_charge'];
							$existing_cart_discount_amount = $multiply_of_discount_amount;
						}else{
							$calcualte_discount_amount  = "0.00"  + $result [0] ['cart_delivery_charge'];
							$existing_cart_discount_amount = $multiply_of_discount_amount;
						}

						if($calcualte_discount_amount > 0){
							$calcualte_discount_amount = $calcualte_discount_amount;
						}else{
							$calcualte_discount_amount = '0.00';
						}


						$CI->Mydb->update ( 'cart_details', array (
							'cart_id' => $cart_id 
						),array (
							'cart_voucher_discount_amount' => $existing_cart_discount_amount,
							'cart_sub_total' => $sub_total,
							'cart_grand_total' => $calcualte_discount_amount
						));

					}else{

						$discount_amount = $cart_voucher['product_voucher_food_discount_val'] * $find_cart_item_product['cart_item_qty'];

						$discount_amount_product = $result [0] ['total_amount'];

						$update_sub_total = $result [0] ['total_amount'];
						$cart_voucher_discount_amount = $result[0]['cart_voucher_discount_amount'];
						$update_grand_total = $update_sub_total + $result [0] ['cart_delivery_charge'];

						if($update_grand_total < 0 ){
							$update_sub_total = "0.00";
							$update_grand_total = "0.00";
						}else{
							$update_sub_total = $result [0] ['total_amount'];
							$update_grand_total = $update_sub_total + $result [0] ['cart_delivery_charge'];
						}

						if($cart_voucher_discount_amount > 0){
							$update_grand_total = $update_sub_total + $result [0] ['cart_delivery_charge'] - $cart_voucher_discount_amount;
						}else{
							$update_grand_total = $update_sub_total + $result [0] ['cart_delivery_charge'];
						}

						if($update_grand_total > 0){
							$update_grand_total  = $update_grand_total;
						}else{

							$update_grand_total  = "0.00";
						}

						$CI->Mydb->update ( 'cart_details', array (
							'cart_customer_id' => $customer_id,
							'cart_id' => $cart_id
						), array (
							'cart_sub_total' => $update_sub_total,
							'cart_grand_total' => $update_grand_total
						) );		
					}

				}else{

					$join [0] ['select'] = "cart_delivery_charge,cart_voucher_discount_amount";
					$join [0] ['table'] = "cart_details";
					$join [0] ['condition'] = "cart_item_cart_id = cart_id ";
					$join [0] ['type'] = "INNER";

					$result = $CI->Mydb->get_all_records ( ' SUM(cart_item_qty) as total_items, SUM(cart_item_total_price) as total_amount ', 'cart_items', array (
						'cart_item_cart_id' => $cart_id
					), 1, '', '', '', '', $join );

					$update_sub_total = $result [0] ['total_amount'];
					$update_grand_total = $update_sub_total + $result [0] ['cart_delivery_charge'];

					if($update_sub_total < 0 ){
						$update_sub_total = "0.00";
						$update_grand_total = "0.00";
					}else{
						$update_sub_total = $result [0] ['total_amount'];
						$update_grand_total = $update_sub_total + $result [0] ['cart_delivery_charge'];
					}

					$cart_voucher_discount_amount = $result[0]['cart_voucher_discount_amount'];
					if($cart_voucher_discount_amount > 0){
						$update_grand_total = $update_sub_total + $result [0] ['cart_delivery_charge'] - $cart_voucher_discount_amount;
					}else{
						$update_grand_total = $update_sub_total + $result [0] ['cart_delivery_charge'];
					}

					if($update_grand_total > 0){
						$update_grand_total  = $update_grand_total;
					}else{

						$update_grand_total  = "0.00";
					}

					$CI->Mydb->update ( 'cart_details', array (
						'cart_id' => $cart_id,
						'cart_customer_id' => $customer_id,
					), array (
						'cart_sub_total' => $update_sub_total,
						'cart_grand_total' => $update_grand_total
					) );
				}
			}
		}
	}
}

if (! function_exists ( 'update_product_total_price' )) {

	function update_product_total_price($product_id=false, $customer_id=false, $app_id=false, $cart_item_id=false, $qty=false){

		$CI = & get_instance ();
		$app_id = $app_id;
		$customer_id = $customer_id;

		$updated = $CI->Mydb->update ( 'cart_items', array (
			'cart_item_id' => $cart_item_id,
			'cart_item_product_id' => $product_id,
			'cart_item_customer_id' => $customer_id
		), array (
			'cart_item_qty' => $qty
		) );



		$product_id = $product_id;

		$get_product_details = $CI->Mydb->get_record("cart_item_voucher_id, cart_item_cart_id", "cart_items", array("cart_item_product_id" => $product_id, "cart_item_id" => $cart_item_id, "cart_item_customer_id" => $customer_id));

		$find_cart_item_product = $CI->Mydb->get_record("*", "cart_items", array("cart_item_product_id" => $product_id, "cart_item_customer_id" => $customer_id, 'cart_item_cart_id' => $get_product_details['cart_item_cart_id']));
		$order_voucher_id = $find_cart_item_product['cart_voucher_order_item_id'];


		if(!empty($order_voucher_id)){

			$select_free_product = $CI->Mydb->get_record("cart_voucher_order_item_id", "cart_items", array("cart_item_product_id" => $product_id, "cart_item_id" => $cart_item_id, "cart_item_customer_id" => $customer_id, 'cart_voucher_order_item_id' => $order_voucher_id, "cart_item_voucher_product_type" => "1"));


			if(!empty($select_free_product)){
				$select_vocher_table = $CI->Mydb->get_record("order_item_voucher_free_product_balance_qty", "order_item_voucher", array("order_item_id" => $order_voucher_id));
				if($qty > $select_vocher_table['order_item_voucher_free_product_balance_qty']){
					$free_product_qty = $select_vocher_table['order_item_voucher_free_product_balance_qty'];
				}else{
					$free_product_qty = $qty;
				}
				$updated = $CI->Mydb->update ( 'cart_items', array (
					'cart_voucher_order_item_id' => $order_voucher_id,
					'cart_item_unit_price' => '0.00',
					'cart_item_voucher_product_free' => '1'
				), array (
					'cart_item_qty' => $free_product_qty
				) );
			}
		}

		if($find_cart_item_product['cart_item_product_type'] == "5" && $find_cart_item_product['cart_item_voucher_product_type'] == "2" && $find_cart_item_product['cart_item_voucher_discount_type'] == "2" ){

		// $find_all_cart_products = $CI->Mydb->get_record("*", "cart_items", array("cart_item_cart_id" => $find_cart_item_product['cart_item_cart_id'], "cart_item_voucher_discount_type" => "2", "cart_item_product_type" => "5", "cart_item_voucher_product_type" => "2"));


			$check_voucher_food_product = $CI->Mydb->get_record("product_primary_id,product_voucher_food_discount_val" , "products", array("product_id" => $find_cart_item_product['cart_item_product_id']));

			$find_record_total_price = $CI->Mydb->get_all_records ( '*', 'cart_items', array (
				'cart_item_cart_id' => $find_cart_item_product['cart_item_cart_id'],
				'cart_item_customer_id'=>$customer_id,
				'cart_item_product_type !=' => '5',
				'cart_item_voucher_id'=>$find_cart_item_product['cart_item_product_id']
			));

			echo $CI->db->last_query();
			exit;


			$applied_count = 0;
			$balance_qty = $find_cart_item_product['cart_item_qty'];

			foreach ($find_record_total_price as  $val) {

				$check_product_food_voucher_any_product = $CI->Mydb->get_record("*", "product_voucher_food_discount", array("food_discount_food_product_id" => $check_voucher_food_product['product_primary_id']));

				if(!empty($check_product_food_voucher_any_product)){

					$check_product_food_voucher_discount = $CI->Mydb->get_record("*", "product_voucher_food_discount", array("food_discount_product_id" => $val['cart_item_product_id'], "food_discount_food_product_id" => $check_voucher_food_product['product_primary_id']));

					if($check_product_food_voucher_discount['food_discount_product_id'] == $val['cart_item_product_id']){
						$variable_count = $balance_qty - $applied_count;
						$product_unit_price = $val['cart_item_unit_price'];
						$discount_amount_product = $product_unit_price * $check_voucher_food_product['product_voucher_food_discount_val'] / 100;

						if($variable_count>0){
							if($variable_count == $val['cart_item_qty']){
								$discount_total_price = $discount_amount_product * $variable_count;
								$applied_count += $val['cart_item_qty'];
							}else if($variable_count > $val['cart_item_qty']){
								$discount_total_price = $discount_amount_product * $val['cart_item_qty'];
								$identify_quantiy = $balance_qty - $val['cart_item_qty'];
								$applied_count += $val['cart_item_qty'];
							}else if($variable_count < $val['cart_item_qty']){
								$discount_total_price = $discount_amount_product * $variable_count;
								$applied_count += $balance_qty;
							}else {}

							$discount_val_total_price = $val['cart_item_unit_price'] * $val['cart_item_qty'] - $discount_total_price;
							if($discount_val_total_price > 0){
								$discount_val_total_price = $discount_val_total_price;
							}else{
								$discount_val_total_price = "0.00";
							}

							$CI->Mydb->update ( 'cart_items', array (
								'cart_item_customer_id ' => $customer_id,
								'cart_item_product_id' => $val['cart_item_product_id'], 
								'cart_item_id' => $val['cart_item_id']
							), array (
								'cart_item_total_price' => $discount_val_total_price,
								'cart_item_voucher_id' => $find_cart_item_product['cart_item_product_id'], 
							) );

						}else{

							$unit_price = $val['cart_item_unit_price'];
							$total_price = $unit_price * $val['cart_item_qty'];
							/*Discount calculation - refer insert_cart_items*/
							$item_promotion_discount = null;
							if((float)$val['cart_item_promotion_discount'] > 0) {
								$item_promotion_discount = ($val['cart_item_actual_unit_price']-$unit_price)* $val['cart_item_qty'];

							}
							$updated = $CI->Mydb->update ( 'cart_items', array (
								'cart_item_customer_id ' => $customer_id,
								'cart_item_product_id' => $val['cart_item_product_id'], 
								'cart_item_id' => $val['cart_item_id'] 
							), array (
								'cart_item_total_price' => $total_price,
								'cart_item_promotion_discount'=>$item_promotion_discount
							) );
						}

					}else{
						$unit_price = $val['cart_item_unit_price'];
						$total_price = $unit_price * $val['cart_item_qty'];
						/*Discount calculation - refer insert_cart_items*/
						$item_promotion_discount = null;
						if((float)$val['cart_item_promotion_discount'] > 0) {
							$item_promotion_discount = ($val['cart_item_actual_unit_price']-$unit_price)* $val['cart_item_qty'];

						}
						$updated = $CI->Mydb->update ( 'cart_items', array (
							'cart_item_customer_id ' => $customer_id,
							'cart_item_product_id' => $val['cart_item_product_id'],
							'cart_item_id' => $val['cart_item_id']  
						), array (
							'cart_item_total_price' => $total_price,
							'cart_item_promotion_discount'=>$item_promotion_discount
						) );
					}

				}else{

					$join [0] ['select'] = "cart_delivery_charge,cart_voucher_discount_amount";
					$join [0] ['table'] = "cart_details";
					$join [0] ['condition'] = "cart_item_cart_id = cart_id ";
					$join [0] ['type'] = "INNER";

					$result = $CI->Mydb->get_all_records ( ' SUM(cart_item_qty) as total_items, SUM(cart_item_total_price) as total_amount,SUM(cart_item_promotion_discount) as special_discount ', 'cart_items', array (
						'cart_item_cart_id' => $cart_id,  
					), 1, '', '', '', '', $join );

					$timer_array = array ();
					if ($time != "") {
						$timer_array = array (
							'cart_timer' => $time 
						);
					}

					$total_items = $result [0] ['total_items'];
					$sub_total = $result [0] ['total_amount'];
					$old_voucher_discount_amount = $result [0] ['cart_voucher_discount_amount'];
			//	$grand_total = $sub_total + $result [0] ['cart_delivery_charge'];
					$special_discount = $result [0] ['special_discount'];
					$discount_type_array = array ();
					if ($discount_type != '' && $special_discount > 0) {
						$discount_type_array = array ('cart_special_discount_type' => $discount_type);
					} else if ($special_discount <= 0) {
						$discount_type_array = array ('cart_special_discount_type' => null);
					} else {}

					$current_amount = $sub_total - $old_voucher_discount_amount;

					if($current_amount > 0){
						$current_amount = $current_amount;
					}else{
						$current_amount = '0.00';
					}

					$discount_calcualte_amount = $current_amount * $check_voucher_food_product['product_voucher_food_discount_val'] / 100;
					$multiply_of_discount_amount = $discount_calcualte_amount * $find_cart_item_product['cart_item_qty'];

					if($multiply_of_discount_amount > $sub_total){
						$multiply_of_discount_amount = $sub_total;
					}else{
						$multiply_of_discount_amount = $multiply_of_discount_amount;
					}

					if($discount_calcualte_amount > 0){
						$discount_calcualte_amount = $multiply_of_discount_amount + $old_voucher_discount_amount;
						$grand_total = $multiply_of_discount_amount + $result [0] ['cart_delivery_charge'];
					}else{
						$grand_total = '0.00' + $result [0] ['cart_delivery_charge'];
					}

					$CI->Mydb->update ( 'cart_details', array (
						'cart_id' => $cart_id, 
					), array_merge ( array (
						'cart_voucher_discount_amount' => $multiply_of_discount_amount, 
						'cart_total_items' => $total_items,
						'cart_sub_total' => $sub_total,
						'cart_grand_total' => $grand_total,
						'cart_special_discount' => $special_discount,
						'cart_updated_on' => current_date () 
					), $timer_array ,$discount_type_array) ); 
				}
			}

		}else if($find_cart_item_product['cart_item_product_type'] == "5" && $find_cart_item_product['cart_item_voucher_product_type'] == "2" && $find_cart_item_product['cart_item_voucher_discount_type'] == "1" ){

			$get_products = $CI->Mydb->get_record("product_voucher_food_discount_val,product_voucher_food_discount_type,product_voucher_food_option, product_id", "products", array("product_id"=>$find_cart_item_product['cart_item_product_id']));

			update_product_total_price_voucher_fixed_update($find_cart_item_product['cart_item_product_id'], $customer_id, $app_id, $find_cart_item_product['cart_item_cart_id'], $get_products['product_voucher_food_discount_type'], $get_products['product_voucher_food_discount_val']);
		}
	}
}



if (! function_exists ( 'update_product_total_price_voucher' )) {
   
   function update_product_total_price_voucher($product_id=null, $customer_id=null, $app_id=null, $cart_id=null,$cart_item_id = null){

   	    $CI = & get_instance ();

		$app_id = $app_id;
		$customer_id = $customer_id;

		if(!empty($cart_item_id)){
        $find_cart_item_product = $CI->Mydb->get_record("*", "cart_items", array("cart_item_product_id" => $product_id, "cart_item_customer_id" => $customer_id, 'cart_item_cart_id' => $cart_id, "cart_item_id" => $cart_item_id));
		}else{
		$find_cart_item_product = $CI->Mydb->get_record("*", "cart_items", array("cart_item_product_id" => $product_id, "cart_item_customer_id" => $customer_id, 'cart_item_cart_id' => $cart_id));
	    }

		$cart_voucher= $CI->Mydb->get_record("product_primary_id, product_voucher_food_discount_val" , "products", array("product_id" => $product_id));

		if($find_cart_item_product['cart_item_product_type'] == "5" && $find_cart_item_product['cart_item_voucher_discount_type'] == "2" && $find_cart_item_product['cart_item_voucher_discount_type'] == "2" ){

		  $find_product_is_voucher_any_product = $CI->Mydb->get_record("*", "product_voucher_food_discount", array("food_discount_food_product_id" => $cart_voucher['product_primary_id']));

			$vouchers_all = 0;

			if(!empty($find_product_is_voucher_any_product)){

			$whereFindRec =  "cart_item_cart_id='".$cart_id."' AND cart_item_customer_id='".$customer_id."' AND (cart_item_product_type !='5' OR cart_item_product_type IS NULL) AND (cart_item_voucher_id='' OR cart_item_voucher_id IS NULL) AND cart_item_voucher_product_free = '0'";

			$find_record = $CI->Mydb->get_all_records ( '*', 'cart_items', $whereFindRec);


			if(!empty($find_record)){

			$applied_count = 0;
			$balance_qty = $find_cart_item_product['cart_item_qty'];
			
			foreach ($find_record as $val) {

			 $find_product_is_voucher = $CI->Mydb->get_record("*", "product_voucher_food_discount", array("food_discount_product_id" => $val['cart_item_product_id'], "food_discount_food_product_id" => $cart_voucher['product_primary_id']));

				if($find_product_is_voucher['food_discount_product_id'] == $val['cart_item_product_id']){
					$variable_count = $balance_qty - $applied_count;
					$product_unit_price = $val['cart_item_unit_price'];
					$discount_amount_product = $product_unit_price * $cart_voucher['product_voucher_food_discount_val'] / 100;
					if($variable_count>0){
						if($variable_count == $val['cart_item_qty']){
							$discount_total_price = $discount_amount_product * $variable_count;
							$applied_count += $val['cart_item_qty'];
						}else if($variable_count > $val['cart_item_qty']){
							$discount_total_price = $discount_amount_product * $val['cart_item_qty'];
							$identify_quantiy = $balance_qty - $val['cart_item_qty'];
							$applied_count += $val['cart_item_qty'];
						}else if($variable_count < $val['cart_item_qty']){
							$discount_total_price = $discount_amount_product * $variable_count;	
							$applied_count += $balance_qty;
						}else {}
						$discount_val_total_price = $val['cart_item_unit_price'] * $val['cart_item_qty'] - $discount_total_price;

						$CI->Mydb->update ( 'cart_items', array (
							'cart_item_customer_id ' => $customer_id,
							'cart_item_product_id' => $val['cart_item_product_id'], 
							'cart_item_id' => $val['cart_item_id']
						), array (
							'cart_item_total_price' => $discount_val_total_price,
							'cart_item_voucher_id' => $find_cart_item_product['cart_item_product_id'], 
						) );
					}else{
						$unit_price = $val['cart_item_unit_price'];
						$total_price = $unit_price * $val['cart_item_qty'];
						/*Discount calculation - refer insert_cart_items*/
						$item_promotion_discount = null;
						if((float)$val['cart_item_promotion_discount'] > 0) {
							$item_promotion_discount = ($val['cart_item_actual_unit_price']-$unit_price)* $val['cart_item_qty'];
						
						}
						$updated = $CI->Mydb->update ( 'cart_items', array (
								'cart_item_customer_id ' => $customer_id,
								'cart_item_product_id' => $val['cart_item_product_id'],  
								'cart_item_id' => $val['cart_item_id']
						), array (
								'cart_item_total_price' => $total_price,
								'cart_item_voucher_id' => '',
								'cart_item_promotion_discount'=>$item_promotion_discount
						) );
				   }

				  }else{
					$unit_price = $val['cart_item_unit_price'];
					$total_price = $unit_price * $val['cart_item_qty'];
					/*Discount calculation - refer insert_cart_items*/
					$item_promotion_discount = null;
					if((float)$val['cart_item_promotion_discount'] > 0) {
						$item_promotion_discount = ($val['cart_item_actual_unit_price']-$unit_price)* $val['cart_item_qty'];
					
					}
					$updated = $CI->Mydb->update ( 'cart_items', array (
							'cart_item_customer_id ' => $customer_id,
							'cart_item_product_id' => $val['cart_item_product_id'],  
							'cart_item_id' => $val['cart_item_id']
					), array (
							'cart_item_total_price' => $total_price,
							'cart_item_voucher_id' => '',
							'cart_item_promotion_discount'=>$item_promotion_discount
					) );
			    }

		     }	

		   }else{
			$CI->Mydb->delete ( 'cart_items', array (
					'cart_item_id' => $find_cart_item_product['cart_item_id'],
					'cart_item_product_id' => $product_id,
			) );

		  	$CI->response ( array (
				'status' => 'error',
				'message' => "This Voucher Not Applicable Your Cart Item.",
				'form_error' => validation_errors ()
			), something_wrong () );
		  }

		  }else{
				$vouchers_all++;
		  }

		  if(!empty($vouchers_all)){

			$join [0] ['select'] = "cart_delivery_charge,cart_voucher_discount_amount";
			$join [0] ['table'] = "cart_details";
			$join [0] ['condition'] = "cart_item_cart_id = cart_id ";
			$join [0] ['type'] = "INNER";

			$result = $CI->Mydb->get_all_records ( ' SUM(cart_item_qty) as total_items, SUM(cart_item_total_price) as total_amount,SUM(cart_item_promotion_discount) as special_discount ', 'cart_items', array (
				'cart_item_cart_id' => $cart_id,  
			), 1, '', '', '', '', $join );

			$timer_array = array ();
			if ($time != "") {
				$timer_array = array (
					'cart_timer' => $time 
				);
			}

			$total_items = $result [0] ['total_items'];
			$sub_total = $result [0] ['total_amount'];
			$old_voucher_discount_amount = $result [0] ['cart_voucher_discount_amount'];
			$special_discount = $result [0] ['special_discount'];
			$discount_type_array = array ();
			if ($discount_type != '' && $special_discount > 0) {
				$discount_type_array = array ('cart_special_discount_type' => $discount_type);
			} else if ($special_discount <= 0) {
				$discount_type_array = array ('cart_special_discount_type' => null);
			} else {}

			$current_amount = $sub_total - $old_voucher_discount_amount;

			if($current_amount > 0){
				$current_amount = $current_amount;
			}else{
				$current_amount = '0.00';
			}

			$discount_calcualte_amount = $current_amount * $cart_voucher['product_voucher_food_discount_val'] / 100;

		    if($discount_calcualte_amount > 0){
		    $multiply_of_discount_amount = $discount_calcualte_amount * $find_cart_item_product['cart_item_qty'];
		    }else{
		    $multiply_of_discount_amount = $discount_calcualte_amount;
		    }

		    if($multiply_of_discount_amount > $sub_total){
		    	$multiply_of_discount_amount = $sub_total;
		    }else{
		    	$multiply_of_discount_amount = $multiply_of_discount_amount;
		    }

			if($discount_calcualte_amount > 0){
			$discount_calcualte_amount =  $old_voucher_discount_amount + $multiply_of_discount_amount;
	     	$grand_total = $discount_calcualte_amount + $result [0] ['cart_delivery_charge'];
			}else{
			$grand_total = '0.00' + $result [0] ['cart_delivery_charge'];
			$discount_calcualte_amount = $old_voucher_discount_amount + $multiply_of_discount_amount;
			}

			if($grand_total == $discount_calcualte_amount){
				$grand_total = '0.00';
			}else{
				$grand_total = $grand_total;
			}

			$CI->Mydb->update ( 'cart_details', array (
				'cart_id' => $cart_id, 
			), array_merge ( array (
				'cart_voucher_discount_amount' => $discount_calcualte_amount, 
				'cart_total_items' => $total_items,
				'cart_sub_total' => $sub_total,
				'cart_grand_total' => $grand_total,
				'cart_special_discount' => $special_discount,
				'cart_updated_on' => current_date () 
			), $timer_array ,$discount_type_array) );
		}
    }
  }
}


if (! function_exists ( 'find_discounted_product' )) {

	function find_discounted_product($product_id, $customer_id, $app_id, $cart_item_id, $qty, $cart_food_vouch_id){

		$CI = & get_instance ();

		$app_id = $app_id;
		$customer_id = $customer_id;

		if(empty($cart_food_vouch_id)){

			$find_cart_item_product = $CI->Mydb->get_record("cart_item_cart_id", "cart_items", array("cart_item_product_id" => $product_id, "cart_item_id" => $cart_item_id));

			$cart_product = $CI->Mydb->get_record("product_primary_id,product_voucher_food_discount_val,product_voucher_food_discount_type" , "products", array("product_id" => $product_id));

			$find_all_cart_products_discount_percentage = $CI->Mydb->get_record("*", "cart_items", array("cart_item_cart_id" => $find_cart_item_product['cart_item_cart_id'], "cart_item_voucher_discount_type" => "2", "cart_item_product_type" => "5", "cart_item_voucher_product_type" => "2"));

			$select_percentage = array("cart_item_cart_id" => $find_cart_item_product['cart_item_cart_id'], "cart_item_product_type" =>"5", "cart_item_voucher_product_type" => "2");

			$select_fixed = array("cart_item_cart_id" => $find_cart_item_product['cart_item_cart_id'], "cart_item_product_type" =>"1", "cart_item_voucher_product_type" => "2");

			$CountPercentageDiscount = $CI->Mydb->get_num_join_rows('cart_item_id', 'cart_items', $select_percentage);

			$CountPercentageFixed = $CI->Mydb->get_num_join_rows('cart_item_id', 'cart_items', $select_fixed);

			$find_all_cart_products_discount_fixed = $CI->Mydb->get_record("*", "cart_items", array("cart_item_cart_id" => $find_cart_item_product['cart_item_cart_id'], "cart_item_voucher_discount_type" => "1", "cart_item_product_type" => "5", "cart_item_voucher_product_type" => "2"));

			if(!empty($find_all_cart_products_discount_percentage)){

				$check_voucher_food_product = $CI->Mydb->get_record("product_primary_id" , "products", array("product_id" => $find_all_cart_products_discount_percentage['cart_item_product_id']));

				$check_product_food_voucher_discount = $CI->Mydb->get_record("*", "product_voucher_food_discount", array("food_discount_product_id" => $product_id, "food_discount_food_product_id" => $check_voucher_food_product['product_primary_id']));

			}else{
				$check_voucher_food_product = $CI->Mydb->get_record("product_primary_id" , "products", array("product_id" => $find_all_cart_products_discount_fixed['cart_item_product_id']));
				$check_product_food_voucher_discount = $CI->Mydb->get_record("*", "product_voucher_food_discount", array("food_discount_product_id" => $product_id, "food_discount_food_product_id" => $check_voucher_food_product['product_primary_id']));
			}

			if($CountPercentageDiscount == 1 || $CountPercentageFixed == 1){
				if(!empty($find_all_cart_products_discount_percentage)){
					return $find_all_cart_products_discount_percentage;
				}else{
					return $find_all_cart_products_discount_fixed;
				}
			}else{

			}

		}else{

			$item_details = $CI->Mydb->get_record ( array (
				'cart_item_qty',
				'cart_item_total_price',
				'cart_item_id',
				'cart_item_unit_price',
				'cart_item_product_id'
			), 'cart_items', array (
				'cart_item_id' => $cart_item_id 
			) );


			if (! empty ( $item_details )) {
				$new_qty = $qty;
				$new_total_amount = $new_qty * $item_details ['cart_item_unit_price'];
				$CI->Mydb->update ( 'cart_items', array (
					'cart_item_id' => $cart_item_id 
				), array (
					'cart_item_qty' => $new_qty,
					'cart_item_total_price' => $new_total_amount 
				) );
			}

			$find_cart_item_product = $CI->Mydb->get_record("*", "cart_items", array("cart_item_product_id" => $product_id, "cart_item_id" => $cart_item_id, "cart_item_customer_id" => $customer_id));

			$cart_product = $CI->Mydb->get_record("product_primary_id,product_voucher_food_discount_val,product_voucher_food_discount_type" , "products", array("product_id" => $product_id));


			if($find_cart_item_product['cart_item_product_type'] == "5" && $find_cart_item_product['cart_item_voucher_product_type'] == "2" && $find_cart_item_product['cart_item_voucher_discount_type'] == "2" ){

				$find_record = $CI->Mydb->get_all_records ( '*', 'cart_items', array (
					'cart_item_cart_id' => $find_cart_item_product['cart_item_cart_id'],
					'cart_item_customer_id'=>$customer_id,
					'cart_item_product_id!=' => '5',
					//'cart_item_voucher_id' => $find_cart_item_product['cart_item_product_id']
				));	

				if(!empty($find_record)){
					$applied_count = 0;
					$balance_qty = $find_cart_item_product['cart_item_qty'];


					$find_product_is_voucher = $CI->Mydb->get_record("*", "product_voucher_food_discount", array("food_discount_food_product_id" => $cart_product['product_primary_id']));

					if(!empty($find_product_is_voucher)){ 
						foreach ($find_record as  $val) {
							$product_voucher_food_discount = $CI->Mydb->get_record("*", "product_voucher_food_discount", array("food_discount_product_id" => $val['cart_item_product_id'], "food_discount_food_product_id" => $cart_product['product_primary_id']));
							if(!empty($product_voucher_food_discount)){
								if($product_voucher_food_discount['food_discount_product_id'] == $val['cart_item_product_id']){
									$variable_count = $balance_qty - $applied_count;
									$product_unit_price = $val['cart_item_unit_price'];
									$discount_amount_product = $product_unit_price * $cart_product['product_voucher_food_discount_val'] / 100;
									if($variable_count>0){
										if($variable_count == $val['cart_item_qty']){
											$discount_total_price = $discount_amount_product * $variable_count;
											$applied_count += $val['cart_item_qty'];
										}else if($variable_count > $val['cart_item_qty']){
											$discount_total_price = $discount_amount_product * $val['cart_item_qty'];
											$identify_quantiy = $balance_qty - $val['cart_item_qty'];
											$applied_count += $val['cart_item_qty'];
										}else if($variable_count < $val['cart_item_qty']){
											$discount_total_price = $discount_amount_product * $variable_count;	
											$applied_count += $balance_qty;
										}else {}

										$discount_val_total_price = $val['cart_item_unit_price'] * $val['cart_item_qty'] - $discount_total_price;

										$CI->Mydb->update ( 'cart_items', array (
											'cart_item_customer_id ' => $customer_id,
											'cart_item_product_id' => $val['cart_item_product_id'], 
											'cart_item_id' => $val['cart_item_id']

										), array (
											'cart_item_total_price' => $discount_val_total_price,
											'cart_item_voucher_id' => $find_cart_item_product['cart_item_product_id'], 
										) );

									}else{
										$unit_price = $val['cart_item_unit_price'];
										$total_price = $unit_price * $val['cart_item_qty'];
										/*Discount calculation - refer insert_cart_items*/
										$item_promotion_discount = null;
										if((float)$val['cart_item_promotion_discount'] > 0) {
											$item_promotion_discount = ($val['cart_item_actual_unit_price']-$unit_price)* $val['cart_item_qty'];
										}

										$updated = $CI->Mydb->update ( 'cart_items', array (
											'cart_item_customer_id ' => $customer_id,
											'cart_item_product_id' => '',  
											'cart_item_id' => $val['cart_item_id']
										), array (
											'cart_item_total_price' => $total_price,
											'cart_item_promotion_discount'=>$item_promotion_discount
										) );
									}
								}
							}
						}
					} else{

						$join [0] ['select'] = "cart_delivery_charge, cart_voucher_discount_amount";
						$join [0] ['table'] = "cart_details";
						$join [0] ['condition'] = "cart_item_cart_id = cart_id ";
						$join [0] ['type'] = "INNER";

						$result = $CI->Mydb->get_all_records ( ' SUM(cart_item_qty) as total_items, SUM(cart_item_total_price) as total_amount,SUM(cart_item_promotion_discount) as special_discount ', 'cart_items', array (
							'cart_item_cart_id' => $find_cart_item_product['cart_item_cart_id'] 
						), 1, '', '', '', '', $join );


						$total_items = $result [0] ['total_items'];
						$sub_total = $result [0] ['total_amount'];
						$existing_cart_discount_amount = $result [0] ['cart_voucher_discount_amount']; 
						$discount_amount = $sub_total - $result [0] ['cart_voucher_discount_amount'];

						$calcualte_discount_amount = $sub_total * $cart_product['product_voucher_food_discount_val'] / 100;

						if($find_cart_item_product['cart_item_qty'] > 1){
							$multiply_of_discount_amount = $calcualte_discount_amount * $find_cart_item_product['cart_item_qty'];
						}else{
							$discount_amount = $sub_total;
							$calcualte_discount_amount = $discount_amount * $cart_product['product_voucher_food_discount_val'] / 100;
							$multiply_of_discount_amount = $calcualte_discount_amount;
						}
						if($calcualte_discount_amount > 0){
							$calcualte_discount_amount  = $sub_total - $multiply_of_discount_amount + $result [0] ['cart_delivery_charge'];
							$existing_cart_discount_amount = $multiply_of_discount_amount;
						}else{
							$calcualte_discount_amount  = "0.00"  + $result [0] ['cart_delivery_charge'];
							$existing_cart_discount_amount = $multiply_of_discount_amount;
						}

						$CI->Mydb->update ( 'pos_cart_details', array (
							'cart_id' => $find_cart_item_product['cart_item_cart_id'] 
						),array (
							'cart_voucher_discount_amount' => $existing_cart_discount_amount,
							'cart_total_items' => $total_items,
							'cart_sub_total' => $sub_total,
							'cart_grand_total' => $calcualte_discount_amount,
							'cart_updated_on' => current_date () 
						));
					}

				}

			}else if($find_cart_item_product['cart_item_product_type'] == "5" && $find_cart_item_product['cart_item_voucher_product_type'] == "2" && $find_cart_item_product['cart_item_voucher_discount_type'] == "1" ){

				if($cart_product['product_voucher_food_discount_type'] == "1"){

					$find_product_is_voucher_any_product = $CI->Mydb->get_record("*", "product_voucher_food_discount", array("food_discount_food_product_id" => $cart_product['product_primary_id']));

					if(empty($find_product_is_voucher_any_product)){ 

						$get_cart_items_details = $CI->Mydb->get_record( 'cart_sub_total, cart_grand_total, cart_voucher_discount_amount', 'cart_details', array (
							'cart_app_id' => $app_id,
							'cart_customer_id ' => $customer_id
						) );

						$join [0] ['select'] = "cart_delivery_charge";
						$join [0] ['table'] = "cart_details";
						$join [0] ['condition'] = "cart_item_cart_id = cart_id ";
						$join [0] ['type'] = "INNER";

						$result = $CI->Mydb->get_all_records ( ' SUM(cart_item_qty) as total_items, SUM(cart_item_total_price) as total_amount,SUM(cart_item_promotion_discount) as special_discount ', 'cart_items', array (
							'cart_item_cart_id' => $find_cart_item_product['cart_item_cart_id'] 
						), 1, '', '', '', '', $join );

						$discount_val_amount = $cart_product['product_voucher_food_discount_val'] * $find_cart_item_product['cart_item_qty'];

						$total_items = $result [0] ['total_items'];
						$sub_total = $result [0] ['total_amount'] - $discount_val_amount;

						if($sub_total < 0){
							$sub_total = "0.00";
							$grand_total = "0.00";
						}else{
							$grand_total = $sub_total + $result [0] ['cart_delivery_charge'];
						}

						$special_discount = $result [0] ['special_discount'];

						$CI->Mydb->update ( 'pos_cart_details', array (
							'cart_id' => $find_cart_item_product['cart_item_cart_id'] 
						),  array (
							'cart_voucher_discount_amount' => $discount_val_amount, 
							'cart_total_items' => $total_items,
							'cart_sub_total' => $sub_total,
							'cart_grand_total' => $grand_total,
							'cart_special_discount' => $special_discount,
							'cart_updated_on' => current_date () 
						));
					}else{
						update_product_total_price_voucher_fixed_update($product_id, $customer_id, $app_id, $find_cart_item_product['cart_item_cart_id'],$cart_product['product_voucher_food_discount_type'], $cart_product['product_voucher_food_discount_val']);
					}

				}
			}
		}
	}
}



if (! function_exists ( 'update_product_total_price_voucher_fixed_update' )) {

	function update_product_total_price_voucher_fixed_update($product_id=null, $customer_id=null, $app_id=null, $cart_id=null, $discount_type=null, $discount_val=null, $update_type=null){

		$CI = & get_instance ();

		$app_id = $app_id;
		$customer_id = $customer_id;
		$find_cart_item_product = $CI->Mydb->get_record("cart_item_product_type,cart_item_voucher_discount_type,cart_item_qty,cart_item_product_id, cart_item_voucher_product_type", "cart_items", array("cart_item_product_id" => $product_id, "cart_item_customer_id" => $customer_id, 'cart_item_cart_id' => $cart_id));

		$cart_voucher= $CI->Mydb->get_record("product_primary_id, product_voucher_food_discount_val" , "products", array("product_id" => $product_id));

		if($find_cart_item_product['cart_item_product_type'] == "5" && $find_cart_item_product['cart_item_voucher_product_type'] == "2" && $find_cart_item_product['cart_item_voucher_discount_type'] == "1" ){

			$find_record = $CI->Mydb->get_all_records ( '*', 'cart_items', array (
				'cart_item_cart_id' => $cart_id,
				'cart_item_customer_id'=>$customer_id,
				'cart_item_product_type!=' => '5'
			));

			$applied_count = 0;
			$balance_qty = $find_cart_item_product['cart_item_qty'];
			$product_selected_voucher = 0;

			$find_product_is_voucher_any_product = $CI->Mydb->get_record("*", "product_voucher_food_discount", array("food_discount_food_product_id" => $cart_voucher['product_primary_id']));

			if(!empty($find_product_is_voucher_any_product)){

				foreach ($find_record as  $val) {

					$find_product_is_voucher = $CI->Mydb->get_record("*", "product_voucher_food_discount", array("food_discount_product_id" => $val['cart_item_product_id'], "food_discount_food_product_id" => $cart_voucher['product_primary_id']));


					if($find_product_is_voucher['food_discount_product_id'] == $val['cart_item_product_id']){	
						$variable_count = $balance_qty - $applied_count;
						$product_unit_price = $val['cart_item_unit_price'];

						if($variable_count > 0){
							if($variable_count == $val['cart_item_qty']){
								$discount_total_price = $cart_voucher['product_voucher_food_discount_val'] * $variable_count;
								$applied_count += $val['cart_item_qty'];
							}else if($variable_count > $val['cart_item_qty']){
								$discount_total_price = $cart_voucher['product_voucher_food_discount_val'] * $val['cart_item_qty'];
								$identify_quantiy = $balance_qty - $val['cart_item_qty'];
								$applied_count += $val['cart_item_qty'];
							}else if($variable_count < $val['cart_item_qty']){
								$discount_total_price = $cart_voucher['product_voucher_food_discount_val'] * $variable_count;
								$applied_count += $balance_qty;
							}else {}

							if($variable_count < $val['cart_item_qty']){
								$new_variable_count = $variable_count;
							}else{
								$new_variable_count = $val['cart_item_qty'];
							}

							if($val['cart_item_unit_price'] > $cart_voucher['product_voucher_food_discount_val']){
								if($discount_total_price > 0){
									$discount_val_total_price = $val['cart_item_unit_price'] * $val['cart_item_qty'] - $discount_total_price;
								}else{
									$discount_val_total_price = "0.00";
								}
							}else{
								if($discount_total_price > 0){
									$calculate_qty = $val['cart_item_qty'] - $new_variable_count;
									$discount_val_total_price = $val['cart_item_unit_price'] * $calculate_qty;	
								}else{
									$discount_val_total_price = "0.00";
								}
							}

							if($discount_val_total_price > 0){
								$discount_val_total_price = $discount_val_total_price;
							}else{
								$discount_val_total_price = "0.00";
							}

							$CI->Mydb->update ( 'cart_items', array (
								'cart_item_customer_id ' => $customer_id,
								'cart_item_product_id' => $val['cart_item_product_id'], 
								'cart_item_id' => $val['cart_item_id']
							), array (
								'cart_item_total_price' => $discount_val_total_price,
								'cart_item_voucher_id' => $find_cart_item_product['cart_item_product_id'], 
							) );


							$join [0] ['select'] = "cart_delivery_charge, cart_voucher_discount_amount";
							$join [0] ['table'] = "cart_details";
							$join [0] ['condition'] = "cart_item_cart_id = cart_id ";
							$join [0] ['type'] = "INNER";

							$result = $CI->Mydb->get_all_records ( ' SUM(cart_item_qty) as total_items, SUM(cart_item_total_price) as total_amount ', 'cart_items', array (
								'cart_item_cart_id' => $cart_id 
							), 1, '', '', '', '', $join );

							$update_sub_total = $result [0] ['total_amount'];
							$existing_cart_discount_amount = $result[0]['cart_voucher_discount_amount'];
							$update_grand_total = $update_sub_total + $result [0] ['cart_delivery_charge'];
							if($update_grand_total < 0 ){
								$update_sub_total = "0.00";
								$update_grand_total = "0.00";
							}else{
								$update_sub_total = $result [0] ['total_amount'];
								$update_grand_total = $update_sub_total + $result [0] ['cart_delivery_charge'];
							}
							if($existing_cart_discount_amount > 0){
								$update_grand_total = $update_grand_total - $existing_cart_discount_amount;
								if($update_grand_total > 0){
									$update_grand_total = $update_grand_total;		  		
								}else{

									$update_grand_total = '0.00';
								}

								$CI->Mydb->update ( 'cart_details', array (
									'cart_customer_id' => $customer_id,
									'cart_id' => $cart_id 
								), array (
									'cart_sub_total' => $update_sub_total,
									'cart_grand_total' => $update_grand_total,
								) );

							}else{
								$CI->Mydb->update ( 'cart_details', array (
									'cart_customer_id' => $customer_id,
									'cart_id' => $cart_id 
								), array (
									'cart_sub_total' => $update_sub_total,
									'cart_grand_total' => $update_grand_total,
									'cart_voucher_discount_amount' => ''

								) );
							}

						}else{

							$product_total_price = $val['cart_item_unit_price'] * $val['cart_item_qty'];

							$CI->Mydb->update ( 'cart_items', array (
								'cart_item_customer_id ' => $customer_id,
								'cart_item_product_id' => $val['cart_item_product_id'], 
								'cart_item_id' => $val['cart_item_id']
							), array (
								'cart_item_total_price' => $product_total_price,
								'cart_item_voucher_id' =>  '', 
							) );

							$join [0] ['select'] = "cart_delivery_charge, cart_voucher_discount_amount";
							$join [0] ['table'] = "cart_details";
							$join [0] ['condition'] = "cart_item_cart_id = cart_id ";
							$join [0] ['type'] = "INNER";

							$result = $CI->Mydb->get_all_records ( ' SUM(cart_item_qty) as total_items, SUM(cart_item_total_price) as total_amount ', 'cart_items', array (
								'cart_item_cart_id' => $cart_id 
							), 1, '', '', '', '', $join );

							$existing_cart_discount_amount = $result[0]['cart_voucher_discount_amount'];
							$update_sub_total = $result [0] ['total_amount'];
							$update_grand_total = $update_sub_total + $result [0] ['cart_delivery_charge'];
							if($update_grand_total < 0 ){
								$update_sub_total = "0.00";
								$update_grand_total = "0.00";
							}else{
								$update_sub_total = $result [0] ['total_amount'];
								$update_grand_total = $update_sub_total + $result [0] ['cart_delivery_charge'];
							}

							if($existing_cart_discount_amount > 0){
								$update_grand_total = $update_grand_total - $existing_cart_discount_amount;
								if($update_grand_total > 0){
									$update_grand_total = $update_grand_total;		  		
								}else{

									$update_grand_total = '0.00';
								}

								$CI->Mydb->update ( 'cart_details', array (
									'cart_customer_id' => $customer_id,
									'cart_id' => $cart_id 
								), array (
									'cart_sub_total' => $update_sub_total,
									'cart_grand_total' => $update_grand_total,
								) );

							}else{
								$CI->Mydb->update ( 'cart_details', array (
									'cart_customer_id' => $customer_id,
									'cart_item_cart_id' => $cart_id 
								), array (
									'cart_sub_total' => $update_sub_total,
									'cart_grand_total' => $update_grand_total,
									'cart_voucher_discount_amount' => ''

								) );
							}
						}

					}
		//}
				}

			}else{

				$join [0] ['select'] = "cart_delivery_charge,cart_voucher_discount_amount";
				$join [0] ['table'] = "cart_details";
				$join [0] ['condition'] = "cart_item_cart_id = cart_id";
				$join [0] ['type'] = "INNER";

				$result = $CI->Mydb->get_all_records ( ' SUM(cart_item_qty) as total_items, SUM(cart_item_total_price) as total_amount ', 'cart_items', array (
					'cart_item_cart_id' => $cart_id
				), 1, '', '', '', '', $join );

				$existing_discount_amount = $result[0]['cart_voucher_discount_amount'];
				if($find_cart_item_product['cart_item_qty'] > 1){
					$discount_total_amount = $discount_val * $find_cart_item_product['cart_item_qty'];
					$new_discount_amount =  $discount_total_amount;
				}else{
					$discount_total_amount = $discount_val * $find_cart_item_product['cart_item_qty'];
					$new_discount_amount =   $discount_total_amount;
				}

				$update_sub_total = $result [0] ['total_amount'] - $new_discount_amount;
				$update_grand_total = $update_sub_total + $result [0] ['cart_delivery_charge'];

				if($update_sub_total < 0 ){
					$update_sub_total = "0.00";
					$update_grand_total = "0.00";
				}else{
					$update_sub_total = $result [0] ['total_amount'];
					$update_grand_total = $update_sub_total + $result [0] ['cart_delivery_charge'] - $new_discount_amount;
				}

				$CI->Mydb->update ( 'cart_details', array (
					'cart_customer_id' => $customer_id,
					'cart_id' => $cart_id 
				), array (
					'cart_voucher_discount_amount' => $new_discount_amount,
					'cart_sub_total' => $update_sub_total,
					'cart_grand_total' => $update_grand_total
				) );
			}

		}

	}

}


if (! function_exists ( 'update_product_price_in_single_voucher' )) {

	function update_product_price_in_single_voucher($product_id=false, $customer_id=false, $app_id=false, $cart_item_id=false, $qty=false){

		$CI = & get_instance ();

		$find_cart_item_product = $CI->Mydb->get_record("*", "cart_items", array("cart_item_product_id" => $product_id, "cart_item_customer_id" => $customer_id, 'cart_item_cart_id' => $cart_item_id));

		$order_voucher_id = $find_cart_item_product['cart_voucher_order_item_id'];
		

		if(!empty($order_voucher_id)){
			$select_free_product = $CI->Mydb->get_record("cart_voucher_order_item_id", "cart_items", array("cart_item_product_id" => $product_id, "cart_item_customer_id" => $customer_id, 'cart_voucher_order_item_id' => $order_voucher_id, 'cart_item_cart_id' => $cart_item_id));
			
			
			if(!empty($select_free_product)){
				$select_vocher_table = $CI->Mydb->get_record("order_item_voucher_free_product_balance_qty", "order_item_voucher", array("order_item_id" => $order_voucher_id));
				if($qty > $select_vocher_table['order_item_voucher_free_product_balance_qty']){
					$free_product_qty = $select_vocher_table['order_item_voucher_free_product_balance_qty'];
				}else{
					$free_product_qty = $qty;
				}
				$updated = $CI->Mydb->update ( 'cart_items', array (
					'cart_voucher_order_item_id' => $order_voucher_id,
					'cart_item_unit_price' => '0.00',
					'cart_item_voucher_product_free' => '1'
				), array (
					'cart_item_qty' => $free_product_qty
				) );
				
			}
		}
	}
}

if (! function_exists ( 'update_cart_details_voucher' )) {

	/* this function used to update cart details.. */
	function update_cart_details_voucher($cart_id, $time = null, $product_id = null, $cart_voucher_order_item_id = null) {

		$CI = & get_instance ();
		/* join tables - Outlet table */
		$join [0] ['select'] = "cart_delivery_charge,cart_voucher_discount_amount";
		$join [0] ['table'] = "cart_details";
		$join [0] ['condition'] = "cart_item_cart_id = cart_id ";
		$join [0] ['type'] = "INNER";
		
		$result = $CI->Mydb->get_all_records ( ' SUM(cart_item_qty) as total_items, SUM(cart_item_total_price) as total_amount ', 'cart_items', array (
			'cart_item_cart_id' => $cart_id 
		), 1, '', '', '', '', $join );
		
		if (! empty ( $result )) {
			
			$timer_array = array ();
			if ($time != "") {
				$timer_array = array (
					'cart_timer' => $time 
				);
			}
			$cart_details_found = $CI->Mydb->get_record("*", "cart_details", array("cart_id" => $cart_id));


			if(!empty($product_id)){
				$get_voucher_details = $CI->Mydb->get_record("cart_item_voucher_id", 'cart_items', array("cart_item_product_id" => $product_id, "cart_item_cart_id" =>$cart_id, 'cart_voucher_order_item_id' => $cart_voucher_order_item_id));
				if(!empty($get_voucher_details['cart_item_voucher_id'])){
					$voucher_found = $CI->Mydb->get_record("cart_item_product_voucher,cart_item_product_type,cart_item_voucher_discount_type,cart_item_voucher_product_type,cart_item_id", 'cart_items', array("cart_item_product_id" => $get_voucher_details['cart_item_voucher_id']));
				}else{
					$voucher_found = $CI->Mydb->get_record("cart_item_product_voucher,cart_item_product_type,cart_item_voucher_discount_type,cart_item_voucher_product_type,cart_item_id", 'cart_items', array("cart_item_product_id" => $product_id, "cart_item_cart_id" =>$cart_id, 'cart_voucher_order_item_id' => $cart_voucher_order_item_id));
				}
			}else{
				$voucher_found = find_voucher_products($cart_id,$cart_details_found['cart_customer_id'], $cart_details_found['cart_app_id'], $cart_voucher_order_item_id);
			}

	

			if(empty($voucher_found['cart_item_voucher_discount_type'])){

				if($result [0]['cart_voucher_discount_amount'] > 0){

					$total_items = $result [0] ['total_items'];
					$sub_total = $result [0] ['total_amount'];
					$cart_voucher_discount_amount = $result [0]['cart_voucher_discount_amount'];
					$grand_total = $sub_total + $result [0] ['cart_delivery_charge'] - $cart_voucher_discount_amount;
					if($grand_total > 0){
						$grand_total = $grand_total;
					}else{
						$grand_total = '0.00';
					}

					$CI->Mydb->update ( 'pos_cart_details', array (
						'cart_id' => $cart_id 
					), array_merge ( array (
						'cart_total_items' => $total_items,
						'cart_sub_total' => $sub_total,
						'cart_grand_total' => $grand_total,
						'cart_updated_on' => current_date () 
					), $timer_array ) );	

				}else{

					$total_items = $result [0] ['total_items'];
					$sub_total = $result [0] ['total_amount'];
					$grand_total = $sub_total + $result [0] ['cart_delivery_charge'];

					$CI->Mydb->update ( 'pos_cart_details', array (
						'cart_id' => $cart_id 
					), array_merge ( array (
						'cart_total_items' => $total_items,
						'cart_sub_total' => $sub_total,
						'cart_grand_total' => $grand_total,
						'cart_updated_on' => current_date () 
					), $timer_array ) );

				}

			}else{

				if(!empty($product_id)){
					if(!empty($get_voucher_details['cart_item_voucher_id'])){
						$get_products = $CI->Mydb->get_record("product_voucher_food_discount_val,product_voucher_food_discount_type,product_voucher_food_option, product_id", "products", array("product_id"=>$get_voucher_details['cart_item_voucher_id']));
					}else{
						$get_products = $CI->Mydb->get_record("product_voucher_food_discount_val,product_voucher_food_discount_type,product_voucher_food_option, product_id", "products", array("product_id"=>$product_id));
					}
				}else{
					$get_products = $CI->Mydb->get_record("product_voucher_food_discount_val,product_voucher_food_discount_type,product_voucher_food_option, product_id", "products", array("product_id"=>$voucher_found['cart_item_product_id']));
				}
	
				if($get_products['product_voucher_food_option'] == 2){
					update_voucher_amount($cart_details_found['cart_app_id'], $cart_details_found['cart_customer_id'],$get_products['product_voucher_food_option'], $get_products['product_voucher_food_discount_val'], $get_products['product_voucher_food_discount_type'], $product_id, $cart_id, $voucher_found['cart_item_id']);

				}else if(empty($get_products['product_voucher_food_option'])){
					$get_discount_amount = $CI->Mydb->get_record("product_voucher_food_discount_val", "products", array("product_id"=>$voucher_found['cart_item_product_id']));
					after_voucher_cart_details_update($cart_id, $time, $get_discount_amount['product_voucher_food_discount_val']);

				}
			}
		}
	}
}


if (! function_exists ( 'find_all_products_delete' )) {

	function find_all_products_delete($cart_item_id=null, $customer_id=null, $app_id=null) {

		$CI = & get_instance ();

		$find_cart_item_product = $CI->Mydb->get_record("*", "cart_items", array("cart_item_id" => $cart_item_id));

		$voucher_product_details = $CI->Mydb->get_record("product_voucher_food_option,product_voucher_food_discount_val,product_primary_id", "products", array("product_id" => $find_cart_item_product['cart_item_product_id']));


		if($find_cart_item_product['cart_item_product_type'] == "5" && $find_cart_item_product['cart_item_voucher_product_type'] == "2" && $find_cart_item_product['cart_item_voucher_discount_type'] == "2" ){

			$cart_details_id = $find_cart_item_product["cart_item_cart_id"];

			$find_voucher_type = $CI->Mydb->get_record("*", "product_voucher_food_discount", array("food_discount_food_product_id" => $voucher_product_details['product_primary_id']));


			if(empty($find_voucher_type)){

				$find_record = $CI->Mydb->get_all_records ( 'cart_item_product_id, cart_item_unit_price, cart_item_total_price, cart_item_qty,cart_item_id,cart_item_voucher_id', 'cart_items', array (
					'cart_item_cart_id' => $cart_details_id,
					'cart_item_customer_id'=>$customer_id,
					'cart_item_product_type!=' => '5'
				));


				$join [0] ['select'] = "cart_delivery_charge,cart_voucher_discount_amount";
				$join [0] ['table'] = "cart_details";
				$join [0] ['condition'] = "cart_item_cart_id = cart_id ";
				$join [0] ['type'] = "INNER";

				$result = $CI->Mydb->get_all_records ( ' SUM(cart_item_qty) as total_items, SUM(cart_item_total_price) as total_amount,SUM(cart_item_promotion_discount) as special_discount ', 'cart_items', array (
					'cart_item_cart_id' => $find_cart_item_product['cart_item_cart_id'],  
				), 1, '', '', '', '', $join );

				$get_cart_details = $CI->Mydb->get_record("*", 'cart_details', array('cart_id' => $find_cart_item_product['cart_item_cart_id']));

				$CI->Mydb->update ( 'cart_details', array (
					'cart_id' => $find_cart_item_product['cart_item_cart_id']
				), array (
					'cart_voucher_discount_amount' => '0.00',
				));

			}else{

				$find_record = $CI->Mydb->get_all_records ( 'cart_item_product_id, cart_item_unit_price, cart_item_total_price, cart_item_qty,cart_item_id,cart_item_voucher_id', 'cart_items', array (
					'cart_item_cart_id' => $cart_details_id,
					'cart_item_customer_id'=>$customer_id,
					'cart_item_product_type!=' => '5',
					'cart_item_voucher_id' => $find_cart_item_product['cart_item_product_id']

				));
				$cart_voucher= $CI->Mydb->get_record("product_primary_id" , "products", array("product_id" => $find_cart_item_product['cart_item_product_id']));

				foreach ($find_record as $val) {

					$find_product_is_voucher = $CI->Mydb->get_record("*", "product_voucher_food_discount", array("food_discount_product_id" => $val['cart_item_product_id'], "food_discount_food_product_id" => $cart_voucher['product_primary_id']));

					if(!empty($find_product_is_voucher)){
						if($val['cart_item_voucher_id'] == $find_cart_item_product['cart_item_product_id']){

							$product_total_price = $val['cart_item_unit_price'] * $val['cart_item_qty'];

							if($val['cart_item_total_price'] !== $product_total_price){

								$CI->Mydb->update ( 'cart_items', array (
									'cart_item_customer_id ' => $customer_id,
									'cart_item_product_id' => $val['cart_item_product_id'],
									'cart_item_id' => $val['cart_item_id']

								), array (
									'cart_item_total_price' => $product_total_price,
									'cart_item_voucher_id' => '',
								) );
							}
						}
					}else{

						$product_total_price = $val['cart_item_unit_price'] * $val['cart_item_qty'];
						$CI->Mydb->update ( 'cart_items', array (
							'cart_item_customer_id ' => $customer_id,
							'cart_item_product_id' => $val['cart_item_product_id'],
							'cart_item_id' => $val['cart_item_id']
						), array (
							'cart_item_total_price' => $product_total_price,
							'cart_item_voucher_id' => ''
						) );
					}
				}
			}

		}else if($find_cart_item_product['cart_item_product_type'] == "5" && $find_cart_item_product['cart_item_voucher_product_type'] == "2" && $find_cart_item_product['cart_item_voucher_discount_type'] == "1" ){

			$find_voucher_type = $CI->Mydb->get_record("*", "product_voucher_food_discount", array("food_discount_food_product_id" => $voucher_product_details['product_primary_id']));

			if(empty($find_voucher_type)){

				$find_record = $CI->Mydb->get_all_records ( '*', 'cart_items', array (
					'cart_item_cart_id' => $find_cart_item_product['cart_item_cart_id'],
					'cart_item_customer_id'=>$customer_id,
					'cart_item_product_type!=' => '5',

				));

				$product_details_voucher_amount = $CI->Mydb->get_record("product_primary_id, product_voucher_food_discount_val" , "products", array("product_id" => $find_cart_item_product['cart_item_product_id']));

				$discount_amount_value_voucher = $product_details_voucher_amount['product_voucher_food_discount_val'] * $find_cart_item_product['cart_item_qty'];

				$join [0] ['select'] = "cart_delivery_charge, cart_voucher_discount_amount";
				$join [0] ['table'] = "cart_details";
				$join [0] ['condition'] = "cart_item_cart_id = cart_id ";
				$join [0] ['type'] = "INNER";

				$result = $CI->Mydb->get_all_records ( ' SUM(cart_item_qty) as total_items, SUM(cart_item_total_price) as total_amount ', 'cart_items', array (
					'cart_item_cart_id' => $find_cart_item_product['cart_item_cart_id'] 
				), 1, '', '', '', '', $join );


				$existing_discount_amount = $result[0]['cart_voucher_discount_amount'];

				$insert_discount_amount = $existing_discount_amount - $discount_amount_value_voucher;

				if($insert_discount_amount > 0){

					$insert_discount_amount = $insert_discount_amount;
				}else{
					$insert_discount_amount = '0.00';
				}

				$get_cart_details = $CI->Mydb->get_record("*", 'cart_details', array('cart_id' => $find_cart_item_product['cart_item_cart_id']));
				$CI->Mydb->update ( 'cart_details', array (
					'cart_id' => $find_cart_item_product['cart_item_cart_id'] 
				),  array (
					'cart_voucher_discount_amount' => $insert_discount_amount
				));

			}else{

				$find_record = $CI->Mydb->get_all_records ( '*', 'cart_items', array (
					'cart_item_cart_id' => $find_cart_item_product['cart_item_cart_id'],
					'cart_item_customer_id'=>$customer_id,
					'cart_item_product_type!=' => '5',
					'cart_item_voucher_id' => $find_cart_item_product['cart_item_product_id']

				));

			$cart_voucher= $CI->Mydb->get_record("product_primary_id" , "products", array("product_id" => $find_cart_item_product['cart_item_product_id']));

			foreach ($find_record as  $val) {

				$find_product_is_voucher = $CI->Mydb->get_record("*", "product_voucher_food_discount", array("food_discount_product_id" => $val['cart_item_product_id'], "food_discount_food_product_id" => $cart_voucher['product_primary_id']));

				if(!empty($find_product_is_voucher)){

					if($val['cart_item_voucher_id'] == $find_cart_item_product['cart_item_product_id']){

						$product_total_price = $val['cart_item_unit_price'] * $val['cart_item_qty'];

						if($val['cart_item_total_price'] !== $product_total_price){

							$CI->Mydb->update ( 'cart_items', array (
								'cart_item_customer_id ' => $customer_id,
								'cart_item_product_id' => $val['cart_item_product_id'],
								'cart_item_id' => $val['cart_item_id']
							), array (
								'cart_item_total_price' => $product_total_price, 
								'cart_item_voucher_id' => "", 
							) );
						}
					}

				}else{

					$product_total_price = $val['cart_item_unit_price'] * $val['cart_item_qty'];
					if($val['cart_item_total_price'] !== $product_total_price){

						$CI->Mydb->update ( 'cart_items', array (
							'cart_item_customer_id ' => $customer_id,
							'cart_item_product_id' => $val['cart_item_product_id'],
							'cart_item_id' => $val['cart_item_id']
						), array (
							'cart_item_total_price' => $product_total_price
						) );

					}

				}

			}
		  }
		}

	}
}