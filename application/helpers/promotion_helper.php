<?php

/**************************
 Project Name	: White Label
Created on		: 17  may, 2016
Last Modified 	: 13  Sep, 2016
Description		:  this file contains common setting for admin and client panel..
 ***************************/

/* validate voucher code */
if (!function_exists('validate_voucher_code')) {
	function validate_voucher_code($voucher_name, $app_id, $customer_id, $cartamount)
	{
		$CI = &get_instance();
		$voucher_table = "voucher";
		$app_id = $app_id;
		$current_date = date('Y-m-d');

		/* get voucher details.. */
		/*$voucher = $CI->Mydb->get_record ( 'voucher_id,voucher_name,voucher_start_date,voucher_end_date,voucher_amount,voucher_status', $voucher_table, array (
				'voucher_app_id' => $app_id,
				'voucher_status' => 'A',
				'voucher_name' => trim ( $voucher_name ) 
		) );*/

		$voucher = $CI->Mydb->get_record('voucher_id,voucher_name,voucher_code,voucher_start_date,voucher_end_date,voucher_amount,voucher_status', $voucher_table, array(
			'voucher_app_id' => $app_id,
			'voucher_status' => 'A',
			'voucher_code' => trim($voucher_name)
		));

		$details = array();
		if (!empty($voucher)) {
			$db_from_date = date("Y-m-d", strtotime($voucher['voucher_start_date']));
			$db_to_date = date("Y-m-d", strtotime($voucher['voucher_end_date']));
			/* validate date */
			if ((($db_from_date == "-0001-11-30" || $db_from_date == "0000-00-00" || $db_from_date == "1970-01-01" || $db_from_date <= $current_date) && ($db_to_date == "-0001-11-30" || $db_to_date == "0000-00-00" || $db_to_date == "1970-01-01" || $db_to_date >= $current_date))) {

				/* check voucher_customer entry details */
				$voucher_customer_details = $CI->Mydb->get_record('vc_voucher_amount,vc_voucher_spended_amount,vc_voucher_balance_amount', 'voucher_customer', array('vc_voucher_id' => $voucher['voucher_id'], 'vc_customer_id' => $customer_id));
				if (!empty($voucher_customer_details)) {
					if ($voucher_customer_details['vc_voucher_balance_amount'] > 0) {
						if ($voucher_customer_details['vc_voucher_balance_amount'] >= $cartamount) {
							$voucher_amount = $cartamount;
						} else {
							$voucher_amount = $voucher_customer_details['vc_voucher_balance_amount'];
						}
						$status = "success";
						$message = get_label('voucher_success');
						$details = array(
							"voucher_id"	=> $voucher['voucher_id'],
							"voucher_name"	=> $voucher['voucher_name'],
							"voucher_amount" => $voucher_amount,
						);
					} else {
						$status = "error";
						$message = get_label('voucher_amount_expire');
						$clear_voucher = 'Yes';
					}
				} else {

					if ($voucher['voucher_amount'] >= $cartamount) {
						$voucher_amount = $cartamount;
					} else {
						$voucher_amount = $voucher['voucher_amount'];
					}

					$status = "success";
					$message = get_label('voucher_success');
					$details = array(
						"voucher_id"	=> $voucher['voucher_id'],
						"voucher_name"	=> $voucher['voucher_name'],
						"voucher_code"	=> $voucher['voucher_code'],
						"voucher_amount" => $voucher_amount,
					);
				}
			} else {
				$status = "error";
				$message = get_label('voucher_date_expire');
				$clear_voucher = 'Yes';
				/* voucher date expired.. */
			}
		} else {
			$status = "error";
			$message = get_label('voucher_invalid');
			/* invalid voucher details */
		}
		if ($status == 'error') {
			echo json_encode(array('status' => $status, 'message' => $message, 'clear_offer' => $clear_voucher, 'result_set' => $details));
			exit;
		} else {
			return $details;
		}
	}
}
/* insert loyality points entry to customer table */
if (!function_exists('insert_redeem_point')) {
	function insert_redeem_point($redeem_point, $app_id, $customer_id, $order_primary_id, $order_id)
	{

		$CI = &get_instance();
		$customer_table = "customers";
		$current_date = date('Y-m-d H:i:s');

		/* get voucher details.. */
		$customer = $CI->Mydb->get_record('customer_reward_point', $customer_table, array(
			'customer_unquie_app_id' => $app_id,
			'customer_status' => 'A',
			'customer_id' => trim($customer_id)
		));


		if (!empty($customer)) {
			$customer_prev_remaining = $customer['customer_reward_point'] + $redeem_point;

			$customer_points_arr = array(
				'customer_reward_point' => $customer_prev_remaining,
				'customer_updated_on' => $current_date,
				'customer_updated_by' => $customer_id,
				'customer_updated_ip' => get_ip(),
			);
			//print_r($customer_points_arr);
			$CI->Mydb->update('customers', array('customer_id' => $customer_id), $customer_points_arr);

			$loyality_userpoints_arr = array(
				'lup_order_id' => $order_id,
				'lup_customer_id' => $customer_id,
				'lup_customer_points' => $redeem_point,
				'lup_created_on' => $current_date,
				'lup_created_by' => $customer_id,
				'lup_created_ip' => get_ip(),
			);
			//print_r($loyality_userpoints_arr);
			//exit;
			$CI->Mydb->insert('loyality_userpoints', $loyality_userpoints_arr);
		}
	}
}


/* validate loyality points */
if (!function_exists('validate_loyality_code')) {

	function validate_loyality_code($redeempoint, $app_id, $customer_id, $cartamount, $client_redeempoint)
	{

		$CI = &get_instance();
		$customer_table = "customers";
		$app_id = $app_id;
		$current_date = date('Y-m-d');
		$clear_loyality = 'No';

		/* get voucher details.. */
		$customer = $CI->Mydb->get_record('customer_reward_point', $customer_table, array(
			'customer_unquie_app_id' => $app_id,
			'customer_status' => 'A',
			'customer_id' => trim($customer_id)
		));

		$details = array();
		if (!empty($customer)) {

			if ($customer['customer_reward_point'] >= $redeempoint) {
				$customer_prev_remaining = $customer['customer_reward_point'] - $redeempoint;

				$point_amount = $redeempoint / $client_redeempoint;
				if ($point_amount > $cartamount) {
					$point_amount = $cartamount;
				}
				$points_used = $point_amount * $client_redeempoint;

				$remaining_point = floor($redeempoint) - floor($points_used);
				$details = array(
					'points_used' => $points_used,
					'points_amount' => $point_amount,
					'customer_balance_point' => $customer_prev_remaining + $remaining_point,
				);
				$status = "success";
				$message = get_label('redeem_applied');
			} else {
				$status = "error";
				$message = get_label('invalid_redeem_point');
				$clear_loyality = 'Yes';
				/* invalid redeem point */
			}
		} else {
			$status = "error";
			$message = get_label('invalid_rest_customer');
			$clear_loyality = 'Yes';
			/* invalid customer id */
		}
		if ($status == 'error') {
			echo json_encode(array('status' => $status, 'message' => $message, 'clear_offer' => $clear_loyality, 'result_set' => $details));
			exit;
		} else {
			return $details;
		}
	}
}

/* validate Promotion code */
if (!function_exists('validate_promotion_code')) {
	function validate_promotion_code($promo_code, $app_id, $customer_id, $cartamount, $cart_quantity, $category_id)
	{

		$CI = &get_instance();
		$promotion_table = "promotion";
		$app_id = $app_id;
		$current_date = date('Y-m-d');

		/* get voucher details.. */
		$promotion = $CI->Mydb->get_record('promotion_id,promotion_name,promotion_start_date,promotion_end_date,promotion_qty,promotion_amount,promotion_category,promotion_coupon_type,promotion_delivery_charge_discount,promotion_no_use,promotion_type,promotion_percentage,promotion_max_amt', $promotion_table, array(
			'promotion_company_unique_id' => $app_id,
			'promotion_status' => 'A',
			'promotion_name' => trim($promo_code)
		));

		$details = array();
		$status = '';
		if (!empty($promotion)) {
			$db_from_date = date("Y-m-d", strtotime($promotion['promotion_start_date']));
			$db_to_date = date("Y-m-d", strtotime($promotion['promotion_end_date']));
			/* validate date */
			if ((($db_from_date == "-0001-11-30" || $db_from_date == "0000-00-00" || $db_from_date == "1970-01-01" || $db_from_date <= $current_date) && ($db_to_date == "-0001-11-30" || $db_to_date == "0000-00-00" || $db_to_date == "1970-01-01" || $db_to_date >= $current_date))) {


				$promotion_history_count = $CI->Mydb->get_num_rows('*', 'promotion_history', array('promotion_history_promotion_id' => $promotion['promotion_id'], 'promotion_history_customer_id' => $customer_id));

				if ($promotion['promotion_no_use'] > $promotion_history_count) {
					if ($promotion['promotion_delivery_charge_discount'] == 'Yes') {
						$details = array('promotion_id' => $promotion['promotion_id'], 'promotion_code' => $promotion['promotion_name'], 'promotion_delivery_charge_applied' => 'Yes', 'promotion_amount' => '', 'promotion_category' => '');
					} else {
						$sel_cate_res = $CI->Mydb->get_all_records('promo_category_primary_id', 'promotion_categories', array(
							'promo_promotion_primary_id' => $promotion['promotion_id']
						));
						if (!empty($sel_cate_res)) {
							$products_category = array();
							foreach ($sel_cate_res as $prom_cate_id_ar) {
								$join[0]['select'] = "pro_cate_id";
								$join[0]['table'] = "product_categories";
								$join[0]['condition'] = "products.product_category_id = product_categories.pro_cate_id";
								$join[0]['type'] = "INNER";
								$pro_categories = $CI->Mydb->get_all_records('product_id', 'products', array('product_categories.pro_cate_primary_id' => $prom_cate_id_ar['promo_category_primary_id']), '', '', array('product_sequence' => 'ASC'), '', '', $join);

								if (!empty($pro_categories)) {
									foreach ($pro_categories as $prod_cat) {
										$products_category[] = $prod_cat['product_id'];
									}
								}
							}

							$app_products = explode(';', $category_id);
							$cart_product_price = 0;
							if (!empty($app_products)) {
								$pr_count = 0;

								foreach ($app_products as $split_product) {
									$prods_prices = explode('|', $split_product);
									if (count($prods_prices) == 3) {
										if (in_array($prods_prices[0], $products_category)) {
											$cart_product_price += $prods_prices[1];
											$pr_count += isset($prods_prices[2]) ? $prods_prices[2] : 0;
										}
									}
								}

								$cartamount = $cart_product_price;

								if ($cartamount <= 0) {
									$status = "error";
									$message = get_label('rest_promotion_not_applicable');
									$clear_promotion = 'Yes';
								} else {
									if (($pr_count < $promotion['promotion_qty']) || $cart_product_price < $promotion['promotion_amount']) {
										if ($promotion['promotion_qty'] != '' && $pr_count < $promotion['promotion_qty']) {

											$message = sprintf(get_label('promotion_min_cat_qty_error'), $promotion['promotion_qty']);
										} else if ($promotion['promotion_amount'] != '' &&  $cart_product_price < $promotion['promotion_amount']) {

											$message = sprintf(get_label('promotion_min_cat_amount_error'), $promotion['promotion_amount']);
										} else {
										}


										$status = 'error';
										$clear_promotion = 'Yes';
									} else {
										$status = '';
									}
								}
							} else {
								$status = 'error';
								$message = get_label('invalid_rest_promocode');
								$clear_promotion = 'Yes';
							}
						} else {
							if (($cart_quantity < $promotion['promotion_qty']) || $cartamount < $promotion['promotion_amount']) {
								$status = 'error';
								$message = get_label('rest_promotion_not_applicable');
								$clear_promotion = 'Yes';
							}
						}
						if ($status != 'error') {

							if ($promotion['promotion_type'] == 'percentage') {
								$promotion_amount = (($cartamount * $promotion['promotion_percentage']) / 100);
								if ($promotion_amount > $promotion['promotion_max_amt']) {
									$promotion_amount = $promotion['promotion_max_amt'];
								}
							} else {
								$promotion_amount = $promotion['promotion_max_amt'];
							}


							if ($promotion_amount > $cartamount) {
								$promotion_amount = $cartamount;
							}

							if ($promotion_amount > 0) {

								$details = array('promotion_id' => $promotion['promotion_id'], 'promotion_code' => $promotion['promotion_name'], 'promotion_delivery_charge_applied' => 'No', 'promotion_amount' => $promotion_amount, 'promotion_category' => $promotion['promotion_category']);
							} else {
								$status = 'error';
								$message = get_label('invalid_rest_promocode');
								$clear_promotion = 'Yes';
							}
						}
					}
				} else {

					$status = "error";
					$message = get_label('rest_promotion_already_used');
					$clear_promotion = 'Yes';
				}
			} else {
				$status = "error";
				$message = get_label('promotion_date_expire');
				$clear_promotion = 'Yes';
				/* promocode date expire */
			}
		} else {
			$status = "error";
			$message = get_label('invalid_rest_promocode');
			$clear_promotion = 'Yes';
			/* invalid promocode */
		}
		if ($status == 'error') {
			echo json_encode(array('status' => $status, 'message' => $message, 'clear_offer' => $clear_promotion, 'result_set' => array()));
			exit;
		} else {
			return $details;
		}
	}
}

/* insert voucher entry for order saving */
if (!function_exists('insert_voucher')) {
	function insert_voucher($voucher_name, $appid, $customer_id, $voucher_amount, $order_primary_id, $orderid, $cartamount)
	{
		$CI = &get_instance();
		$voucher_table = "voucher";
		$app_id = $appid;
		$current_date = date('Y-m-d H:i:s');

		$voucher = $CI->Mydb->get_record('voucher_id,voucher_name,voucher_code,voucher_start_date,voucher_end_date,voucher_amount,voucher_status', $voucher_table, array(
			'voucher_app_id' => $app_id,
			'voucher_status' => 'A',
			'voucher_code' => trim($voucher_name)
		));
		if (!empty($voucher)) {

			/* checking the voucher customer table , for previous entry checking */
			$voucher_customer_details = $CI->Mydb->get_record('vc_id,vc_voucher_amount,vc_voucher_spended_amount,vc_voucher_balance_amount', 'voucher_customer', array('vc_voucher_id' => $voucher['voucher_id'], 'vc_customer_id' => $customer_id));
			if (!empty($voucher_customer_details)) {


				if ($voucher_customer_details['vc_voucher_balance_amount'] >= $cartamount) {
					$voucher_amount = $cartamount;
				} else {
					$voucher_amount = $voucher_customer_details['vc_voucher_balance_amount'];
				}

				$voucher_balance_amount = $voucher_customer_details['vc_voucher_balance_amount'] - $voucher_amount;
				$voucher_spended_amount = $voucher_customer_details['vc_voucher_spended_amount'] + $voucher_amount;
				$voucher_customer_arr = array(
					'vc_voucher_spended_amount' => $voucher_spended_amount,
					'vc_voucher_balance_amount' => $voucher_balance_amount,
					'vc_updated_on' => $current_date,
					'vc_updated_by' => $customer_id,
					'vc_updated_ip' => get_ip(),
				);
				$CI->Mydb->update('voucher_customer', array('vc_id' => $voucher_customer_details['vc_id']), $voucher_customer_arr);
			} else {

				if ($voucher['voucher_amount'] >= $cartamount) {
					$voucher_amount = $cartamount;
				} else {
					$voucher_amount = $voucher['voucher_amount'];
				}

				$voucher_balance_amount = $voucher['voucher_amount'] - $voucher_amount;
				$voucher_spended_amount = $voucher_amount;
				$voucher_customer_arr = array(
					'vc_voucher_id' => $voucher['voucher_id'],
					'vc_voucher_code' => $voucher['voucher_code'],
					'vc_voucher_amount' => $voucher['voucher_amount'],
					'vc_voucher_spended_amount' => $voucher_spended_amount,
					'vc_voucher_balance_amount' => $voucher_balance_amount,
					'vc_customer_id' => $customer_id,
					'vc_created_on' => $current_date,
					'vc_created_by' => $customer_id,
					'vc_created_ip' => get_ip(),
				);
				$CI->Mydb->insert('voucher_customer', $voucher_customer_arr);
			}

			$voucher_history_arr = array(
				'vh_voucher_id' => $voucher['voucher_id'],
				'vh_voucher_code' => $voucher['voucher_code'],
				'vh_order_primary_id' => $order_primary_id,
				'vh_order_id' => $orderid,
				'vh_voucher_amount' => $voucher_amount,
				'vh_customer_id' => $customer_id,
				'vh_created_on' => $current_date,
				'vh_created_by' => $customer_id,
				'vh_created_ip' => get_ip(),
			);
			$CI->Mydb->insert('voucher_history', $voucher_history_arr);
		}
	}
}

/* insert loyality points entry for order saving */
if (!function_exists('insert_redeem')) {

	function insert_redeem($redeem_point, $app_id, $customer_id, $cart_amount, $redeem_amount, $order_primary_id, $orderid, $points_history)
	{
		$CI = &get_instance();
		$customer_table = "customers";
		$current_date = date('Y-m-d H:i:s');

		/* get voucher details.. */
		$customer = $CI->Mydb->get_record('customer_reward_point', $customer_table, array(
			'customer_unquie_app_id' => $app_id,
			'customer_status' => 'A',
			'customer_id' => trim($customer_id)
		));

		if (!empty($customer)) {

			$customer_prev_remaining = $customer['customer_reward_point'] - $redeem_point;
			$customer_points_arr = array(
				'customer_reward_point' => $customer_prev_remaining,
				'customer_updated_on' => $current_date,
				'customer_updated_by' => $customer_id,
				'customer_updated_ip' => get_ip(),
			);

			$CI->Mydb->update('customers', array('customer_id' => $customer_id), $customer_points_arr);

			$loyality_history_arr = array(
				'lh_redeem_point' => $redeem_point,
				'lh_redeem_amount' => $redeem_amount,
				'lh_order_primaryid' => $order_primary_id,
				'lh_order_id' => $orderid,
				'lh_customer_id' => $customer_id,
				'lh_redeem_history' => $points_history,
				'lh_created_on' => $current_date,
				'lh_created_by' => $customer_id,
				'lh_created_ip' => get_ip(),
			);

			$lh = $CI->Mydb->insert('loyality_history', $loyality_history_arr);
		}
		return $lh;
	}
}

/* insert coupon entry for order saving */
if (!function_exists('insert_coupon')) {
	function insert_coupon($promo_code, $app_id, $customer_id, $cart_amount, $cart_quantity, $category_id, $coupon_amount, $promotion_delivery_charge_applied, $promotion_id, $promotion_catid, $order_primary_id, $orderid)
	{

		$CI = &get_instance();
		$promotion_table = "promotion";
		$app_id = $app_id;
		$current_date = date('Y-m-d');
		$promotion = $CI->Mydb->get_record('promotion_id,promotion_name,promotion_start_date,promotion_end_date,promotion_qty,promotion_amount,promotion_category,promotion_coupon_type,promotion_delivery_charge_discount,promotion_no_use,promotion_type,promotion_percentage,promotion_max_amt', $promotion_table, array(
			'promotion_company_unique_id' => $app_id,
			'promotion_status' => 'A',
			'promotion_name' => trim($promo_code)
		));

		if (!empty($promotion)) {

			$promotion_history_arr = array(
				'promotion_history_app_id' => $app_id,
				'promotion_history_customer_id' => $customer_id,
				'promotion_history_order_primary_id' => $order_primary_id,
				'promotion_history_order_id' => $orderid,
				'promotion_history_promotion_id' => $promotion_id,
				'promotion_history_promocode' => $promo_code,


				'promotion_history_cart_quantity' => $promotion['promotion_qty'],
				'promotion_history_cart_amount' => $promotion['promotion_amount'],
				'promotion_history_category_id' => $promotion_catid,
				'promotion_history_applied_amt' => $coupon_amount,
				'promotion_history_delivery_charge' => $promotion_delivery_charge_applied,

				'promotion_history_created_on' => $current_date,
				'promotion_history_created_by' => $customer_id,
				'promotion_history_created_ip' => get_ip(),
			);
			$CI->Mydb->insert('promotion_history', $promotion_history_arr);
		}
	}
}
/* insert loyality points entry for order saving */
if (!function_exists('get_redeem')) {

	function get_redeem($app_id, $customer_id)
	{
		$CI = &get_instance();
		$customer_point = '';
		$customer_table = "customers";
		/* get voucher details.. */
		$customer = $CI->Mydb->get_record('customer_reward_point', $customer_table, array(
			'customer_unquie_app_id' => $app_id,
			'customer_status' => 'A',
			'customer_id' => trim($customer_id)
		));

		if (!empty($customer)) {
			$customer_point = $customer['customer_reward_point'];
		} else {
			$customer_point = "";
		}
		return $customer_point;
	}
}
