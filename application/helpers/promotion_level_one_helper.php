<?php

/**************************
 Project Name	: POS
Created on		: 26  Dec, 2017
Last Modified 	: 12  Dec, 2018
Description		: this file contains promotion functionalities.
 ***************************/

/* validate voucher code */
if (!function_exists('validate_voucher_code')) {

	function validate_voucher_code($voucher_name, $app_id, $customer_id, $cartamount)
	{

		$CI = &get_instance();
		$voucher_table = "voucher";
		$app_id = $app_id;
		$current_date = date('Y-m-d');

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
			'customer_app_id' => $app_id,
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
			'customer_app_id' => $app_id,
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
	function validate_promotion_code($promo_code, $app_id, $customer_id, $cartamount, $cart_quantity, $category_id, $availability_id)
	{
		$CI = &get_instance();
		$current_date = date('Y-m-d H:i:s');
		$promotion_table = "promotion";
		$promo_code = trim($promo_code);
		$promotioncheck = $CI->Mydb->get_record('promotion_id,promotion_title, promotion_name,promotion_outlet_id, promotion_type', $promotion_table, array(
			'promotion_company_unique_id' => $app_id,
			'promotion_status' => 'A',
			'promotion_name' => $promo_code
		));
		if (!empty($promotioncheck)) {
			$promotion_outlet_id = $promotioncheck['promotion_outlet_id'];
			if (!empty($promotion_outlet_id) && $promotioncheck['promotion_type'] == 'percentage') {
				$join1 = array();
				$join1[0]['select'] = "SUM(cart_item_total_price) AS cart_item_total_price";
				$join1[0]['table'] = "cart_items";
				$join1[0]['condition'] = "cart_id = cart_item_cart_id";
				$join1[0]['type'] = "INNER";
				$checkOutlet1 = $CI->Mydb->get_all_records('', 'cart_details', array('cart_customer_id' => $customer_id, 'cart_item_outlet' => $promotion_outlet_id, 'cart_app_id' => $app_id), '', '', '', '', '', $join1);

				if (!empty($checkOutlet1)) {
					$cartamount = $checkOutlet1[0]['cart_item_total_price'];
				}
			}
		}

		/*if promo code exist in my promo*/
		$where_my = array("ref_customer_id" => $customer_id, "LOWER(ref_promo_code)" => strtolower($promo_code));
		$cust_promo_set = $CI->Mydb->get_record('*', 'pos_promotion_refer', $where_my);

		if (!empty($cust_promo_set)) {

			$where_my = array("ref_customer_id" => $customer_id, "DATE_FORMAT(promo_expire_date,'%Y-%m-%d') >=" => date("Y-m-d"), "promo_used" => "No", "LOWER(ref_promo_code)" => strtolower($promo_code));
			$order_by_my = array(
				'promo_expire_date' => 'ASC', 'id' => 'ASC'
			);

			$cust_promo_set = $CI->Mydb->get_record('*', 'pos_promotion_refer', $where_my, $order_by_my);

			if (!empty($cust_promo_set)) {
				$no_of_use = 1;
				$cust_promotion_id = $cust_promo_set['ref_promotion_id'];
				/* Promo code applied.. */
				$promotion = $CI->Mydb->get_record('promotion_id,promotion_name,promotion_outlet_id,promotion_start_date,promotion_end_date,promotion_qty,promotion_amount,promotion_category,promotion_coupon_type,promotion_delivery_charge_discount,promotion_no_use,promotion_type,promotion_percentage,promotion_max_amt,promotion_overall_use,promotion_order_flag, promotion_title', $promotion_table, array(
					'promotion_company_unique_id' => $app_id,
					'promotion_status' => 'A',
					'promotion_id' => $cust_promotion_id
				));
				$details = array();
				$status = '';
				if (!empty($promotion)) {
					/*First orer exist means not able use this promocode*/
					$first_order_flg = 1;
					$promotion_order_flag = $promotion['promotion_order_flag'];
					if ($promotion_order_flag == 'Yes') {
						$first_order_flg = get_isfirst_order($customer_id);
					}
					if ($first_order_flg) {
						/*Checked Based Customer exit or not*/
						if (strtolower(trim($promotion['promotion_delivery_charge_discount'])) == 'Yes') {
							$details = array('promotion_id' => $promotion['promotion_id'], 'promotion_code' => $promotion['promotion_name'], 'promotion_title' => $promotion['promotion_title'], 'promotion_delivery_charge_applied' => 'Yes', 'promotion_amount' => '', 'promotion_category' => '', 'promotion_outlet_id' => $promotion['promotion_outlet_id']);
						} else {
							if ($promotion['promotion_category'] != '') {
								$products_category = array();
								$join[0]['select'] = "pro_cate_id";
								$join[0]['table'] = "product_categories";
								$join[0]['condition'] = "products.product_category_id = product_categories.pro_cate_id";
								$join[0]['type'] = "INNER";
								$pro_categories = $CI->Mydb->get_all_records('product_id', 'products', array('product_categories.pro_cate_primary_id' => $promotion['promotion_category']), '', '', array('product_sequence' => 'ASC'), '', '', $join);
								if (!empty($pro_categories)) {
									foreach ($pro_categories as $prod_cat) {
										$products_category[] = $prod_cat['product_id'];
									}
								}
								$app_products = explode(';', $category_id);
								$cart_product_price = 0;
								if (!empty($app_products)) {
									foreach ($app_products as $split_product) {
										$prods_prices = explode('|', $split_product);
										if (count($prods_prices) == 2) {
											if (in_array($prods_prices[0], $products_category)) {
												$cart_product_price += $prods_prices[1];
											}
										}
									}
									$cartamount = $cart_product_price;
									if ($cartamount <= 0) {
										$status = "error";
										$message = get_label('rest_promotion_not_applicable');
										$clear_promotion = 'Yes';
									} else {
										$status = '';
									}
								} else {
									$status = 'error';
									$message = get_label('invalid_rest_promocode');
									$clear_promotion = 'Yes';
								}
							} else {
								if (($cart_quantity < $promotion['promotion_qty']) || $cartamount < $promotion['promotion_amount']) {
									if ($promotion['promotion_qty'] != '' && $cart_quantity < $promotion['promotion_qty']) {
										$message = sprintf(get_label('promotion_min_qty_error'), $promotion['promotion_qty']);
									} else if ($promotion['promotion_amount'] != '' &&  $cartamount < $promotion['promotion_amount']) {

										$message = sprintf(get_label('promotion_min_amount_error'), $promotion['promotion_amount']);
									}
									$status = 'error';
									$clear_promotion = 'Yes';
								}
							}
						}
						if ($status != 'error') {
							$promotion_id = $promotion['promotion_id'];
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
								/*Convert two digits*/
								$ex_plo = explode('.', $promotion_amount);
								if (isset($ex_plo[1]) && strlen($ex_plo[1]) > 2) {

									$promotion_amount = floor($promotion_amount * 100) / 100;
								}

								$details = array('promotion_id' => $promotion['promotion_id'], 'promotion_code' => $promotion['promotion_name'], 'promotion_delivery_charge_applied' => 'No', 'promotion_amount' => $promotion_amount, 'promotion_category' => $promotion['promotion_category'], 'promotion_title' => $promotion['promotion_title'], 'prmo_type' => 'without_product', 'promotion_outlet_id' => $promotion['promotion_outlet_id'], 'promotion_type' => $promotion['promotion_type'], 'promotion_percentage' => $promotion['promotion_percentage']);
							} else {
								$status = 'error';
								$message = get_label('invalid_rest_promocode');
								$clear_promotion = 'Yes';
							}
						}
					} else {
						$status = "error";
						$message = get_label('promotion_first_order_error');
					}
				} else {

					$status = "error";
					$message = get_label('invalid_rest_promocode');
					$clear_promotion = 'Yes';
					/* invalid promocode */
				}
			} else {
				$status = "error";
				$message = get_label('rest_promotion_already_used');
				$clear_promotion = 'Yes';
			}
		} else {
			/*Normal promocode applied*/
			$set_pro_arr = array();
			if (!in_array($promo_code, $set_pro_arr)) {
				/* Promo code applied.. */
				$promotion = $CI->Mydb->get_record('promotion_id,promotion_outlet_id, promotion_name,promotion_start_date,promotion_end_date,promotion_qty,promotion_amount,promotion_category,promotion_coupon_type,promotion_delivery_charge_discount,promotion_no_use,promotion_type,promotion_percentage,promotion_max_amt,promotion_overall_use,promotion_order_flag, promotion_title', $promotion_table, array(
					'promotion_company_unique_id' => $app_id,
					'promotion_status' => 'A',
					'promotion_name' => $promo_code
				));
				$details = array();
				$status = '';
				if (!empty($promotion)) {
					if ($promotion['promotion_outlet_id'] != 0) {
						$join1 = array();
						$join1[0]['select'] = "cart_item_id";
						$join1[0]['table'] = "cart_items";
						$join1[0]['condition'] = "cart_id = cart_item_cart_id";
						$join1[0]['type'] = "INNER";
						$checkOutlet = $CI->Mydb->get_all_records('', 'cart_details', array('cart_customer_id' => $customer_id, 'cart_item_outlet' => $promotion['promotion_outlet_id'], 'cart_app_id' => $app_id), '', '', '', '', '', $join1);
					} else {
						$checkOutlet = array('Yes');
					}
					if (!empty($checkOutlet)) {
						$db_from_date = date("Y-m-d H:i:s", strtotime($promotion['promotion_start_date']));
						$db_to_date = date("Y-m-d H:i:s", strtotime($promotion['promotion_end_date']));
						$currenttime = date('H:i:s');
						/* validate date */
						if ((($db_from_date == "-0001-11-30 00:00:00" || $db_from_date == "0000-00-00 00:00:00" || $db_from_date == "1970-01-01 " . $currenttime || $db_from_date <= $current_date) && ($db_to_date == "-0001-11-30 00:00:00" || $db_to_date == "0000-00-00 00:00:00" || $db_to_date == "1970-01-01 " . $currenttime || $db_to_date >= $current_date))) {
							/*First orer exist means not able use this promocode*/
							$first_order_flg = 1;
							$promotion_order_flag = $promotion['promotion_order_flag'];
							if ($promotion_order_flag == 'Yes') {
								$first_order_flg = get_isfirst_order($customer_id);
							}
							if ($first_order_flg) {
								$promotion_history_count = $CI->Mydb->get_num_rows('*', 'promotion_history', array('promotion_history_promotion_id' => $promotion['promotion_id']));
								if ($promotion['promotion_overall_use'] == 0 || $promotion['promotion_overall_use'] > (int)$promotion_history_count) {
									$promotion_history_count = $CI->Mydb->get_num_rows('*', 'promotion_history', array('promotion_history_promotion_id' => $promotion['promotion_id'], 'promotion_history_customer_id' => $customer_id));
									if ($promotion['promotion_no_use'] > $promotion_history_count || $promotion_history_count == 0) {
										/*Checked Based Customer exit or not*/
										$pro_customer_exit_fl = 1;
										$cust_pro_result_set = $CI->Mydb->get_all_records('promotion_customer.*', 'promotion_customer', array('pro_promotion_id' => $promotion['promotion_id']));
										if (!empty($cust_pro_result_set)) {
											$pro_customer_exit_arr = array_column($cust_pro_result_set, 'pro_customer_id');
											if (!empty($pro_customer_exit_arr)) {
												if (in_array($customer_id, $pro_customer_exit_arr)) {
													$pro_customer_exit_fl = 1;
												} else {
													$pro_customer_exit_fl = 0;
												}
											}
										}
										/*Checked Based Customer exit or not*/
										if ($pro_customer_exit_fl == 1) {
											/*Checked Based availabilty*/
											$pro_avai_exit_fl = 1;
											$cust_avai_set = $CI->Mydb->get_all_records('promo_availability_id', 'promotion_availability', array('promo_availability_promocode_primary_id' => $promotion['promotion_id']));
											if (!empty($cust_avai_set)) {
												$pro_avai_exit_arr = array_column($cust_avai_set, 'promo_availability_id');
												if (!empty($pro_avai_exit_arr)) {
													if (in_array($availability_id, $pro_avai_exit_arr)) {
														$pro_avai_exit_fl = 1;
													} else {
														$pro_avai_exit_fl = 0;
													}
												}
											}
											if ($pro_avai_exit_fl == 1) {
												if (strtolower(trim($promotion['promotion_delivery_charge_discount'])) == 'yes') {
													$details = array('promotion_id' => $promotion['promotion_id'], 'promotion_code' => $promotion['promotion_name'], 'promotion_delivery_charge_applied' => 'Yes', 'promotion_amount' => '', 'promotion_category' => '', 'promotion_title' => $promotion['promotion_title'], 'promotion_outlet_id' => $promotion['promotion_outlet_id'], 'promotion_type' => $promotion['promotion_type'], 'promotion_percentage' => $promotion['promotion_percentage']);
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
															if ($promotion['promotion_qty'] != '' && $cart_quantity < $promotion['promotion_qty']) {
																$message = sprintf(get_label('promotion_min_qty_error'), $promotion['promotion_qty']);
															} else if ($promotion['promotion_amount'] != '' &&  $cartamount < $promotion['promotion_amount']) {
																$message = sprintf(get_label('promotion_min_amount_error'), $promotion['promotion_amount']);
															}
															$status = 'error';
															$clear_promotion = 'Yes';
														}
													}
													if ($status != 'error') {
														$promotion_id = $promotion['promotion_id'];
														if ($promotion['promotion_type'] == 'percentage') {
															$promotion_amount = (($cartamount * $promotion['promotion_percentage']) / 100);
															if ($promotion_amount > $promotion['promotion_max_amt'] && (float)$promotion['promotion_max_amt'] > 0) {
																$promotion_amount = $promotion['promotion_max_amt'];
															}
														} else {
															$promotion_amount = $promotion['promotion_max_amt'];
														}
														if ($promotion_amount > $cartamount) {
															$promotion_amount = $cartamount;
														}
														if ($promotion_amount > 0) {
															/*Convert two digits*/
															$ex_plo = explode('.', $promotion_amount);
															if (isset($ex_plo[1]) && strlen($ex_plo[1]) > 2) {

																$promotion_amount = floor($promotion_amount * 100) / 100;
															}

															$details = array('promotion_id' => $promotion['promotion_id'], 'promotion_code' => $promotion['promotion_name'], 'promotion_delivery_charge_applied' => 'No', 'promotion_amount' => $promotion_amount, 'promotion_category' => $promotion['promotion_category'], 'promotion_title' => $promotion['promotion_title'], 'prmo_type' => 'without_product', 'promotion_outlet_id' => $promotion['promotion_outlet_id'], 'promotion_type' => $promotion['promotion_type'], 'promotion_percentage' => $promotion['promotion_percentage']);
														} else {
															$status = 'error';
															$message = get_label('invalid_rest_promocode');
															$clear_promotion = 'Yes';
														}
													}
												}
											} else {
												$status = "error";
												$message = get_label('rest_promotion_not_applicable_avai');
												$clear_promotion = 'Yes';
											}
										} else {
											$status = "error";
											$message = get_label('invalid_rest_promocode');
											$clear_promotion = 'Yes';
										}
									} else {
										$status = "error";
										$message = get_label('rest_promotion_already_used');
										$clear_promotion = 'Yes';
									}
								} else {
									$status = "error";
									$message = get_label('rest_promotion_already_used');
									$clear_promotion = 'Yes';
								}
							} else {
								$status = "error";
								$message = get_label('promotion_first_order_error');
								$clear_promotion = 'Yes';
								/* promocode date expire */
							}
						} else {
							$status = "error";
							$message = get_label('promotion_date_expire');
							$clear_promotion = 'Yes';
							/* promocode date expire */
						}
					} else {
						$status = "error";
						$message = 'This promo code not applicable cart products';
						$clear_promotion = 'Yes';
						/* promocode not applicable */
					}
				} else {

					$status = "error";
					$message = get_label('invalid_rest_promocode');
					$clear_promotion = 'Yes';
					/* invalid promocode */
				}
			} else {
				$status = "error";
				$message = get_label('invalid_rest_promocode');
				$clear_promotion = 'Yes';
				/* promocode in settings page  */
			}
		}/*Normal promo code*/

		if ($status == 'error') {
			return array('status' => $status, 'type' => 'promo', 'message' => $message, 'clear_offer' => $clear_promotion, 'result_set' => array());
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
			'customer_app_id' => $app_id,
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
			'promotion_app_id' => $app_id,
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

			$promotion_history_id = $CI->Mydb->insert('promotion_history', $promotion_history_arr);
			if ($promotion_history_id != '') {

				$where_my = array("ref_customer_id" => $customer_id, "DATE_FORMAT(promo_expire_date,'%Y-%m-%d') >=" => date("Y-m-d"), "promo_used" => "No", "LOWER(ref_promo_code)" => strtolower($promo_code));
				$order_by_my = array(
					'promo_expire_date' => 'ASC', 'id' => 'ASC'
				);

				$cust_promo_set = $CI->Mydb->get_record('*', 'pos_promotion_refer', $where_my, $order_by_my);
				if (!empty($cust_promo_set)) {

					/*Insert main refer table*/
					$CI->Mydb->update('pos_promotion_refer', array('id' => $cust_promo_set['id']), array('promo_used' => 'Yes', 'promotion_history_id' => $promotion_history_id));
				}
			}
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
			'customer_app_id' => $app_id,
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

/* Claim Promotion code */
if (!function_exists('claim_promotion_code')) {

	function claim_promotion_code($promo_code, $app_id, $customer_id)
	{

		$CI = &get_instance();

		$company = app_validation($app_id);

		/* Promo name fetching end.. */
		$current_date = date('Y-m-d H:i:s');
		$promotion_table = "promotion";

		$promo_code = trim($promo_code);

		/********************check whether is a promocode or Referral code end customer id**************/
		//$refer_code = 
		/*$cust_result_set = $CI->Mydb->get_record('customer_refer_code,customer_id,customer_status','pos_customers',array('customer_refer_code' => $refer_code));

		if(!empty($cust_result_set)) {

			$status="error";
			$message = get_label('invalid_rest_promocode');
			
			echo json_encode(array('status'=>$status,'type' => 'claim','message'=>$message,'result_set'=>array()));
			exit;

		} else {*/

		/*if promo code exist in my promo*/
		$where_my = array("ref_customer_id" => $customer_id, "LOWER(ref_promo_code)" => strtolower($promo_code));
		$cust_promo_set = $CI->Mydb->get_record('*', 'pos_promotion_refer', $where_my);

		if (!empty($cust_promo_set)) {

			$status = 'error';
			$message = get_label('rest_promotion_already_used');
		} else {

			/*$client_refer_to_promo_code = $company['client_refer_to_promo_code'];
				$client_refer_by_promo_code = $company['client_refer_by_promo_code'];

				// Promo name fetching start..
				$client_refer_to_promo_code_name = '';
				$client_refer_by_promo_code_name = '';
				$promotion_res = $CI->Mydb->get_record ( 'promotion_name,promotion_id', $promotion_table, array (
				'promotion_app_id' => $app_id,'promotion_id' => $client_refer_to_promo_code
				) );
				if(!empty($promotion_res)) {

					$client_refer_to_promo_code_name = stripslashes($promotion_res['promotion_name']);

				}

				$promotion_res = $CI->Mydb->get_record ( 'promotion_name,promotion_id,promotion_end_date,promotion_expire_day', $promotion_table, array (
				'promotion_app_id' => $app_id,'promotion_id' => $client_refer_by_promo_code
				) );
				if(!empty($promotion_res)) {

					$client_refer_by_promo_code_name = stripslashes($promotion_res['promotion_name']);

				} 


				$set_pro_arr = array();

				if($company ['client_promo_code']!='') {
					$set_pro_arr[] = strtoupper($company ['client_promo_code']);
				}
				if($company ['client_refer_to_promo_code']!='') {
					$set_pro_arr[] = strtoupper($client_refer_to_promo_code_name);
				}
				if($company ['client_refer_by_promo_code']!='') {
					$set_pro_arr[] = strtoupper($client_refer_by_promo_code_name);
				}

				if(!in_array($promo_code,$set_pro_arr)) {*/

			/* Promo code applied.. */
			$promotion = $CI->Mydb->get_record('promotion_id,promotion_name,promotion_start_date,promotion_end_date,promotion_qty,promotion_amount,promotion_category,promotion_coupon_type,promotion_delivery_charge_discount,promotion_no_use,promotion_type,promotion_percentage,promotion_max_amt,promotion_overall_use,promotion_order_flag,promotion_expire_day', $promotion_table, array(
				'promotion_app_id' => $app_id,
				'promotion_status' => 'A',
				'promotion_name' => $promo_code
			));

			$details = array();
			$status = '';

			if (!empty($promotion)) {

				$db_from_date = date("Y-m-d H:i:s", strtotime($promotion['promotion_start_date']));
				$promotion_end_date = $db_to_date = date("Y-m-d H:i:s", strtotime($promotion['promotion_end_date']));
				$currenttime = date('H:i:s');

				$promotion_expire_day =  $promotion['promotion_expire_day'];
				$promotion_id = $promotion['promotion_id'];

				$promo_code = $promotion['promotion_name'];

				/* validate date */
				if ((($db_from_date == "-0001-11-30 00:00:00" || $db_from_date == "0000-00-00 00:00:00" || $db_from_date == "1970-01-01 " . $currenttime || $db_from_date <= $current_date) && ($db_to_date == "-0001-11-30 00:00:00" || $db_to_date == "0000-00-00 00:00:00" || $db_to_date == "1970-01-01 " . $currenttime || $db_to_date >= $current_date))) {


					/*First orer exist means not able use this promocode*/
					$first_order_flg = 1;
					$promotion_order_flag = $promotion['promotion_order_flag'];
					if ($promotion_order_flag == 'Yes') {

						$first_order_flg = get_isfirst_order($customer_id);
					}

					if ($first_order_flg) {

						// $promotion_history_count = $CI->Mydb->get_num_rows('*','promotion_history',array('promotion_history_promotion_id'=>$promotion['promotion_id']));

						$promotion_history_count = $CI->Mydb->get_num_join_rows('ref_customer_id', 'pos_promotion_refer', array('ref_promotion_id' => $promotion_id), null, null, null, '', '');

						if ($promotion['promotion_overall_use'] == 0 || $promotion['promotion_overall_use'] > (int)$promotion_history_count) {
							$number_of_use = (int)$promotion['promotion_no_use'];

							if ((int)$promotion['promotion_overall_use'] != 0 && $promotion['promotion_overall_use'] < $number_of_use) {

								$number_of_use = $promotion['promotion_overall_use'];
							}

							$promotion_history_count = $CI->Mydb->get_num_rows('*', 'promotion_history', array('promotion_history_promotion_id' => $promotion['promotion_id'], 'promotion_history_customer_id' => $customer_id));

							$used_count = (int)$promotion_history_count;

							$number_of_use = $number_of_use - $used_count;

							if ($number_of_use > 0) {
								/*Refer campanel promotion controllers*/
								$ref_promo_code = $promo_code;
								$promo_issued_date = current_date();
								$promo_expire_date = $promotion_end_date;

								if ($promotion_expire_day == 0 && $promotion_expire_day != null) {
									$promo_expire_date = current_date();
								} elseif ($promotion_expire_day > 0) {
									$promo_expire_date = date('Y-m-d', strtotime($promo_issued_date . ' +' . $promotion_expire_day . ' days'));;
								} else {
								}

								$refer_balance_use = $number_of_use;

								$ref_id = $promotion_id;

								$ref_customer_id = $customer_id;
								$ref_promotion_id = $promotion_id;

								for ($i = 0; $i < $refer_balance_use; $i++) {

									/*Insert into table*/
									$CI->Mydb->insert('pos_promotion_refer', array(
										'ref_id' => $ref_id,
										'ref_customer_id' => $ref_customer_id,
										'ref_promotion_id' => $ref_promotion_id,
										'ref_promo_code' => $ref_promo_code,
										'promo_issued_date' => $promo_issued_date,
										'promo_expire_date' => $promo_expire_date,
										'promotion_from' => 'CLAIM'
									));
								}
							} else {
								$status = 'error';
								$message = get_label('rest_promotion_already_used');
							}
						} else {
							$status = 'error';
							$message = get_label('rest_promotion_already_used');
						}
					} else {
						$status = 'error';
						$message = get_label('promotion_first_order_error');
					}
				} else {
					$status = 'error';
					$message = get_label('promotion_date_expire');
					/* promocode date expire */
				}
			} else {

				$status = 'error';
				$message = get_label('invalid_rest_promocode');
				$clear_promotion = 'Yes';
				/* invalid promocode */
			}

			/*} else {

					$status = 'error';
					$message = get_label('invalid_rest_promocode');

				}*/
		} /*Already in mypromo*/

		if ($status == 'error') {
			echo json_encode(array('status' => $status, 'type' => 'claim', 'message' => $message, 'result_set' => array()));
			exit;
		} else {

			return $details;
		}
	}/*Function ends*/
}/*Function ends*/

if (!function_exists('get_isfirst_order')) {

	/* this function used to update cart details.. */
	function get_isfirst_order($customer_id)
	{

		$CI = &get_instance();

		$first_order_flg = 1;

		$fo_res = $CI->Mydb->custom_query('SELECT order_primary_id FROM pos_orders_customer_details INNER JOIN pos_orders ON order_primary_id = order_customer_order_primary_id AND order_status!=5 WHERE order_customer_id =' . $customer_id . ' LIMIT 1');
		if (isset($fo_res[0]['order_primary_id']) && $fo_res[0]['order_primary_id'] != '') {
			$first_order_flg = 0;
		}

		return $first_order_flg;
	}
}


if (!function_exists('get_initial_promo')) {

	/* this function used to update cart details.. */
	function get_initial_promo($company, $customer_id, $availability_id)
	{

		$CI = &get_instance();

		$details = array(
			'promo_apply' => 'No',
			'promo_code' => '',
		);

		if ($company['client_first_time_promo_code'] != '') {

			/*if(get_isfirst_order($customer_id)) {*/

			$promotion = $CI->Mydb->get_record('promotion_id,promotion_start_date,promotion_end_date', 'promotion', array(
				'promotion_app_id' => $company['client_app_id'],
				'promotion_status' => 'A',
				'promotion_name' => $company['client_first_time_promo_code']
			));

			if (!empty($promotion)) {

				$db_from_date = date("Y-m-d H:i:s", strtotime($promotion['promotion_start_date']));
				$db_to_date = date("Y-m-d H:i:s", strtotime($promotion['promotion_end_date']));
				$currenttime = date('H:i:s');
				$current_date = date('Y-m-d H:i:s');

				/* validate date */
				if ((($db_from_date == "-0001-11-30 00:00:00" || $db_from_date == "0000-00-00 00:00:00" || $db_from_date == "1970-01-01 " . $currenttime || $db_from_date <= $current_date) && ($db_to_date == "-0001-11-30 00:00:00" || $db_to_date == "0000-00-00 00:00:00" || $db_to_date == "1970-01-01 " . $currenttime || $db_to_date >= $current_date))) {


					/*Checked Based availabilty*/
					$pro_avai_exit_fl = 1;

					$cust_avai_set = $CI->Mydb->get_all_records('promo_availability_id', 'promotion_availability	', array('promo_availability_promocode_primary_id' => $promotion['promotion_id']));
					if (!empty($cust_avai_set)) {

						$pro_avai_exit_arr = array_column($cust_avai_set, 'promo_availability_id');

						if (!empty($pro_avai_exit_arr)) {

							if (in_array($availability_id, $pro_avai_exit_arr)) {
								$pro_avai_exit_fl = 1;
							} else {
								$pro_avai_exit_fl = 0;
							}
						}
					}

					if ($pro_avai_exit_fl == 1) {
						$details = array(
							'promo_apply' => 'Yes',
							'promo_code' => $company['client_first_time_promo_code'],
						);
					}
				}
			}
			/* } First order*/
		}

		return $details;
	}
}
