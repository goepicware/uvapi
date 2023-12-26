<?php

/**************************
 Project Name	: White Label
Created on		: 13  Sep, 2016
Last Modified 	: 14 Sep, 2016
Description		:  this file contains loyalty reward points for admin and client panel..
 ***************************/

/* Change the loyality points status - yes-after order completed */
if (!function_exists('loyality_change_status')) {

	function loyality_change_status($order_primary_id, $lh_status = 'yes')
	{

		$CI = &get_instance();

		$loyalty_table = 'loyality_points';

		if ($order_primary_id != '') {

			$where = array('lh_ref_id' => $order_primary_id, 'lh_from' => 'order');

			if ($CI->Mydb->update($loyalty_table, $where, array('lh_expiry_flag' => $lh_status))) {

				loyality_earn_points_notify($order_primary_id);
			}
		}

		return true;
	}
}

/* this function used to update ingredient quantity */
if (!function_exists('update_ingredient_quantity_inorder')) {
	function update_ingredient_quantity_inorder($companyid_txt, $companyapp_idtxt, $ingr_id, $ingr_outlet_id, $type, $conversion_qty, $operator)
	{

		$CI = &get_instance();
		$update_array = array();
		$new_ingredient_balance = '';
		if (!empty($ingr_id) && !empty($conversion_qty)) {

			$where_array = array('ingredientavl_company_id' => $companyid_txt, 'ingredientavl_app_id' => $companyapp_idtxt, 'avl_ingredient_primary_id' => $ingr_id, 'ingredientavl_outlet_id' => $ingr_outlet_id); /* check compnay and app id... */
			$records = $CI->Mydb->get_all_records('avl_ingredient_primary_id,ingredientavl_by_stock,ingredientavl_by_order,ingredientavl_by_wastage,ingredientavl_balance', 'pos_ingredients_availability', $where_array, '', '', array('avl_ingredient_primary_id' => "ASC"), '', '', '');
			if (!empty($records)) {

				$ingredient_by_stock = $new_ingredient_by_stock = ($records[0]['ingredientavl_by_stock'] != '') ? $records[0]['ingredientavl_by_stock'] : 0;
				$ingredient_by_order = $new_ingredient_by_order = ($records[0]['ingredientavl_by_order'] != '') ? $records[0]['ingredientavl_by_order'] : 0;
				$ingredient_by_wastage = $new_ingredient_by_wastage = ($records[0]['ingredientavl_by_wastage'] != '') ? $records[0]['ingredientavl_by_wastage'] : 0;

				if ($type == 'stock' && ($operator == 'plus' || $operator == 'minus')) {
					$new_ingredient_by_stock = ($operator == 'plus') ? (floatval($ingredient_by_stock) + floatval($conversion_qty)) : (floatval($ingredient_by_stock) - floatval($conversion_qty));
					$new_ingredient_balance = floatval($new_ingredient_by_stock) - (floatval($ingredient_by_order) + floatval($ingredient_by_wastage));
				} else if ($type == 'order' && ($operator == 'plus' || $operator == 'minus')) {
					$new_ingredient_by_order = ($operator == 'plus') ? (floatval($ingredient_by_order) + floatval($conversion_qty)) : (floatval($ingredient_by_order) - floatval($conversion_qty));
					$new_ingredient_balance = floatval($ingredient_by_stock) - (floatval($new_ingredient_by_order) + floatval($ingredient_by_wastage));
				} else if ($type == 'wastage' && ($operator == 'plus' || $operator == 'minus')) {
					$new_ingredient_by_wastage = ($operator == 'plus') ? (floatval($ingredient_by_wastage) + floatval($conversion_qty)) : (floatval($ingredient_by_wastage) - floatval($conversion_qty));
					$new_ingredient_balance = floatval($ingredient_by_stock) - (floatval($ingredient_by_order) + floatval($new_ingredient_by_wastage));
				}

				$update_array = array(
					'ingredientavl_by_stock' => $new_ingredient_by_stock,
					'ingredientavl_by_order' => $new_ingredient_by_order,
					'ingredientavl_by_wastage' => $new_ingredient_by_wastage,
					'ingredientavl_balance' => $new_ingredient_balance,
					'ingredientavl_updated_on' => current_date(),
					'ingredientavl_updated_ip' => get_ip()
				);

				$CI->Mydb->update('pos_ingredients_availability', array('avl_ingredient_primary_id' => $ingr_id, 'ingredientavl_outlet_id' => $ingr_outlet_id), $update_array);
			}
		}
		return  $new_ingredient_balance;
	}
}


/* this function used to display quantity unit */
if (!function_exists('get_display_quantityunit_buss')) {
	function get_display_quantityunit_buss($group_id, $unit_id, $qty)
	{

		$CI = &get_instance();
		if (empty($group_id) && !empty($unit_id)) {
			$where_array = array('unit_company_id' => get_busines_company_id(), 'unit_app_id' => get_busines_company_app_id(), 'unit_id' => $unit_id); /* check compnay and app id... */
			$records = $CI->Mydb->get_all_records('unit_id,unit_name', 'unit_management', $where_array, '', '', array('unit_id' => "ASC"), '', '', '');
			if (!empty($records)) {
				$group_id = $records[0]['unit_group_id'];
			}
		}

		$QtyText = '-';
		$UnitText = '';
		$return_txt = '';
		if (!empty($group_id)) {
			$qty = ($qty != '') ? floatval($qty) : 0;
			$qty_optr = ($qty < 0) ? 'yes' : '';
			if ($qty_optr == 'yes') {
				$qty = abs($qty);
			}

			if ($group_id == 1) {
				$qty_kg = ($qty / 1000000);
				$qty_gm = ($qty / 1000);
				if (floor($qty_kg) > 0) {
					$QtyText = $qty_kg;
					$UnitText = " kg";
				} else if (floor($qty_gm) > 0) {
					$QtyText = $qty_gm;
					$UnitText = " g";
				} else if ($qty > 0) {
					$QtyText = $qty;
					$UnitText = " mg";
				} else {
					//$QtyText = $qty." kg";
				}
			} else if ($group_id == 2) {
				$qty_lt = ($qty / 1000);
				if (round($qty_lt) > 0) {
					$QtyText = $qty_lt;
					$UnitText = " L";
				} else if ($qty > 0) {
					$QtyText = $qty;
					$UnitText = " ml";
				} else {
					//$QtyText = $qty." L";
				}
			} else if ($group_id == 3) {
				$qty_km = ($qty / 1000000);
				$qty_m = ($qty / 1000);
				$qty_cm = ($qty / 10);
				if (round($qty_km) > 0) {
					$QtyText = $qty_km;
					$UnitText = " km";
				} else if (round($qty_m) > 0) {
					$QtyText = $qty_m;
					$UnitText = " m";
				} else if (round($qty_cm) > 0) {
					$QtyText = $qty_cm;
					$UnitText = " cm";
				} else if ($qty > 0) {
					$QtyText = $qty;
					$UnitText = " mm";
				} else {
					//$QtyText = $qty." km";
				}
			}

			$return_txt = ($qty_optr == 'yes' && $QtyText != '-') ? -Abs($QtyText) . $UnitText : $QtyText . $UnitText;
		}



		return  $return_txt;
	}
}


/* this is used to Inventory enable or not */
if (!function_exists('inventory_status_inbusiness')) {
	function inventory_status_inbusiness()
	{
		$CI = &get_instance();
		$status = "";
		$where = array('client_app_id' => get_busines_company_app_id());
		$outlet_result = $CI->Mydb->get_record('client_enable_inventory', 'pos_clients', $where);
		if (!empty($outlet_result)) {
			$status = $outlet_result['client_enable_inventory'];
		}
		return $status;
	}
}

/* this is used to Inventory two enable or not */
if (!function_exists('catering_version_two_enable')) {
	function catering_version_two_enable()
	{
		$CI = &get_instance();
		$status = "";
		$where = array('client_app_id' => get_busines_company_app_id());
		$outlet_result = $CI->Mydb->get_record('client_catering_product_upgrade', 'pos_clients', $where);
		if (!empty($outlet_result)) {
			$status = $outlet_result['client_catering_product_upgrade'];
		}
		return $status;
	}
}


/* this is used to Product rating enable or not */
if (!function_exists('product_rating_status')) {
	function product_rating_status()
	{
		$CI = &get_instance();
		$status = "";
		$where = array('client_app_id' => get_busines_company_app_id());
		$rating_result = $CI->Mydb->get_record('client_product_rating_option_enable', 'pos_clients', $where);
		if (!empty($rating_result)) {
			$status = $rating_result['client_product_rating_option_enable'];
		}
		return $status;
	}
}

/* Loyalty Reward points */
if (!function_exists('loyalty_reward_points')) {

	function loyalty_reward_points($app_id, $customer_id, $order_primary_id, $order_no, $cartamount)
	{

		$CI = &get_instance();

		$insert_id = '';

		/* get Client details.. */
		$client_res = $CI->Mydb->get_record(array('client_loyality_enable', 'client_currency', 'client_reward_point', 'client_loyalty_reward_percentage', 'client_loyalty_expiry_on'), 'pos_clients', array('client_app_id' => $app_id));
		if (!empty($client_res)) {

			$insert_loyalty_arr = array();
			$reward_points = 0;

			$default_reward_amount = floatval(get_reward_amount());

			$client_reward_point = floatval($client_res['client_reward_point']); /*reward point per dollar($)*/

			$reason = sprintf(get_label('loyalty_reward_reason'), $order_no);

			$from_mode = 'order';
			$ref_id = $order_primary_id;
			$expiry_flag = 'no'; /*order status complete change yes*/

			if ($client_res['client_loyality_enable'] == '1' && $client_reward_point > 0) {

				$client_currency = $client_res['client_currency'];

				$loyalty_reward_percentage = floatval($client_res['client_loyalty_reward_percentage']);
				$loyalty_expiry_on = (int)$client_res['client_loyalty_expiry_on'];

				$date = new DateTime();
				$date->add(new DateInterval('P' . $loyalty_expiry_on . 'D'));
				$expiry_on = $date->format('Y-m-d H:i:s');

				if ($loyalty_expiry_on >= 0) { /*Loyalty expiry on*/

					if ($loyalty_reward_percentage > 0 && $loyalty_expiry_on >= 0) { /*Loyalty percenatge*/

						$reward_amount = (floatval($cartamount) * $loyalty_reward_percentage) / 100;

						$reward_amount_per_point = $client_reward_point / $default_reward_amount; /*Reward amount per points*/

						$reward_points = round(($reward_amount * $reward_amount_per_point), 2);
					} else { /*Loyalty cash valuse from settings*/

						$where = array('order_primary_id' => $order_primary_id);

						/* join tables - Status table */
						$join[0]['select'] = "item_order_primary_id";
						$join[0]['table'] = "order_items";
						$join[0]['condition'] = "item_order_primary_id = order_primary_id";
						$join[0]['type'] = "LEFT";

						$join[1]['select'] = "product_reward_point";
						$join[1]['table'] = "products";
						$join[1]['condition'] = "product_id = item_product_id";
						$join[1]['type'] = "LEFT";

						$reward_res = $CI->Mydb->get_all_records('SUM(item_qty*product_reward_point) as reward_points', 'orders', $where, '', '', '', '', '', $join);

						if (!empty($reward_res[0]['reward_points']))
							$reward_points = $reward_res[0]['reward_points'];
					}
				}

				if (floatval($reward_points) != 0) {

					$insert_loyalty_arr = array(
						'lh_customer_id' => $customer_id,
						'lh_credit_points' => $reward_points,
						'lh_currency_symbol' => $client_res['client_currency'],
						'lh_reward_per_point' => $client_res['client_reward_point'],
						'lh_reward_per_amount' => get_reward_amount(),
						'lh_from' => $from_mode,
						'lh_ref_id' => $ref_id,
						'lh_reason' => $reason,
						'lh_expiry_flag' => $expiry_flag,
						'lh_expiry_on' => $expiry_on,
						'lh_created_on' => current_date(),
						'lh_created_by' => $customer_id,
						'lh_created_ip' => get_ip()
					);

					/*Inert into reward points*/
					$insert_id = $CI->Mydb->insert('loyality_points', $insert_loyalty_arr);
				}
			} /*loyality*/
		} /*Client exist*/

		return $insert_id;
	}
}

/* Total loyality points */
if (!function_exists('get_loyality_points')) {

	function get_loyality_points($customer_id, $order_primary_id = null)
	{

		$CI = &get_instance();

		$loyalty_table = 'loyality_points';

		$where = array(
			'lh_customer_id' => $customer_id,
			'lh_expiry_on >=' => current_date(),
			'lh_expiry_flag' => 'yes'
		);

		if ($order_primary_id != null) {
			$where = array_merge($where, array('lh_from' => 'order', 'lh_ref_id' => $order_primary_id));
		}

		$earned_points = 0;

		/*If already exist or not*/
		$result = $CI->Mydb->get_record('SUM(lh_credit_points-lh_debit_points) as earned_points', $loyalty_table, $where);

		//echo '<pre>';
		//print_r($result);
		//echo $CI->db->last_query();
		//exit;
		if (!empty($result)) {

			$earned_points = (float)$result['earned_points'];
		}

		return $earned_points;
	}
}

/* expiry loyaltypoints */
if (!function_exists('get_monthly_loyality_points')) {

	function get_monthly_loyality_points($customer_id)
	{

		$CI = &get_instance();

		$loyalty_table = 'loyality_points';
		$month = date('Y-m-d H:i:s', strtotime("+30 days"));
		$current_date = current_date();

		$where = "lh_customer_id = $customer_id AND (lh_expiry_on >= '" . $current_date . "' && lh_expiry_on <= '" . $month . "') AND lh_expiry_flag = 'yes'";

		$earned_points = 0;

		/*If already exist or not*/
		$result = $CI->Mydb->get_record('SUM(lh_credit_points-lh_debit_points) as earned_points', $loyalty_table, $where);
		//echo '<pre>';
		//print_r($result);
		//echo $CI->db->last_query();
		//exit;
		if (!empty($result)) {
			$earned_points = $result['earned_points'];
			if (floatval($earned_points) < 0) $earned_points = 0;/*If came duplicate*/
		}

		return $earned_points;
	}
}


/* expiry loyaltypoints - with in expiring x days - return with this string */
if (!function_exists('get_days_expiring_loyality_points')) {

	function get_days_expiring_loyality_points($customer_id, $dsays = 30)
	{

		$CI = &get_instance();

		$loyalty_table = 'loyality_points';
		$month = date('Y-m-d H:i:s', strtotime('+' . $dsays . ' days'));
		$current_date = current_date();

		$where = "lh_customer_id = $customer_id AND (lh_expiry_on >= '" . $current_date . "' && lh_expiry_on <= '" . $month . "') AND lh_expiry_flag = 'yes'";

		$earned_points = 0;
		$earned_points_str = '';

		/*If already exist or not*/
		$result = $CI->Mydb->get_record('SUM(lh_credit_points-lh_debit_points) as earned_points', $loyalty_table, $where);

		if (!empty($result)) {
			$earned_points = $result['earned_points'];
			if (floatval($earned_points) < 0) $earned_points = 0;/*If came duplicate*/
		}

		if ($earned_points > 0) {

			$earned_points_str = $earned_points . ' Dollar(s) Expiring with in ' . $dsays . ' Days';
		}

		return $earned_points_str;
	}
}


/* Revert back loyality points */
if (!function_exists('revert_loyality_points')) {

	function revert_loyality_points($loyalty_history)
	{

		$CI = &get_instance();

		if (!empty($loyalty_history)) {

			$loyalty_table = 'loyality_points';

			foreach ($loyalty_history as $lh_id => $lh_points) {

				$res = $CI->Mydb->get_record(
					'lh_debit_points',
					$loyalty_table,
					array('lh_id' => $lh_id)
				);

				if (!empty($res)) {

					$debit_points = $res['lh_debit_points'] - $lh_points;

					$CI->Mydb->update($loyalty_table, array('lh_id' => $lh_id), array('lh_debit_points' => $debit_points));

					/*echo $lquery = $CI->db->last_query();*/
				}
			}
		}

		return true;
	}
}

/* validate loyality points*/
if (!function_exists('validate_loyalty_point')) {

	function validate_loyalty_point($redeempoint, $app_id, $customer_id, $cartamount, $client_redeempoint)
	{

		$CI = &get_instance();

		$redeempoint = floatval($redeempoint);
		$total_redeempoint = get_loyality_points($customer_id);
		//$total_redeempoint = 100;

		$customer_table = 'customers';
		$loyalty_table = 'loyality_points';

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

		$temp_redeempoint = $redeempoint = floatval($redeempoint);
		$total_redeempoint = floatval($total_redeempoint);

		if (!empty($customer)) {

			if ($total_redeempoint >= $redeempoint) {
				$points_used = '';
				$point_amount = 0;
				$points_history = array();

				/*----------------------------------------------------------------*/

				$where = array(
					'lh_customer_id' => $customer_id,
					'lh_expiry_on >=' => current_date(),
					'lh_expiry_flag' => 'yes'
				);

				$orderby = array('lh_created_on' => 'ASC');

				/*Get rreward points list*/
				$res_set = $CI->Mydb->get_all_records('lh_id,lh_reward_per_point,lh_reward_per_amount,lh_debit_points,(lh_credit_points-lh_debit_points) as reward_points', $loyalty_table, $where, '', '', $orderby);

				$temp_points = 0;

				foreach ($res_set as $res_arr) {

					$reward_per_point = floatval($res_arr['lh_reward_per_point']);
					$reward_per_amount = floatval($res_arr['lh_reward_per_amount']);
					$debit_points = floatval($res_arr['lh_debit_points']);

					$actual_reward_points = floatval($res_arr['reward_points']);

					if ($actual_reward_points != 0) {

						/*---------------------------------------------------*/

						if ($actual_reward_points >= $temp_redeempoint) {

							$current_used_points = $temp_redeempoint;
						} else {

							$current_used_points = $actual_reward_points;
						}

						$points_history[$res_arr['lh_id']] = $current_used_points;

						$temp_redeempoint = $temp_redeempoint - $current_used_points;
						$temp_points += $current_used_points;

						$point_amount += round(($current_used_points / $reward_per_point) * $reward_per_amount, 2);

						/*------------------Debit points update---------------*/

						$debit_points = $debit_points + $current_used_points;

						//echo '-----'.$point_amount.'-----redeem--'.$redeempoint.'---temp redeem---'.$temp_redeempoint.'---used---'.$current_used_points.'----earenedpoints----'.$actual_reward_points.'---debit------'.$debit_points."-<br/>--";

						$CI->Mydb->update($loyalty_table, array('lh_id' => $res_arr['lh_id']), array('lh_debit_points' => $debit_points));

						/*------------------*/

						if ($temp_points == $redeempoint) break;
					}
				}

				/*----------------------------------------------------------------*/

				$remain_points = get_loyality_points($customer_id);

				/*----------------------------------------*/

				if ($point_amount > $cartamount) {
					$point_amount = $cartamount;
				}

				$details = array(
					'points_used' => $temp_points,
					'points_amount' => $point_amount,
					'customer_balance_point' => $remain_points,
					'points_history' => $points_history
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

/* validate loyality points*/
if (!function_exists('validate_loyalty_pointv1')) {

	function validate_loyalty_pointv1($redeempoint, $app_id, $customer_id, $cartamount, $client_redeempoint)
	{

		$CI = &get_instance();

		$redeempoint = floatval($redeempoint);
		$total_redeempoint = get_loyality_points($customer_id);

		//$total_redeempoint = 100;

		$customer_table = 'customers';
		$loyalty_table = 'loyality_points';

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

		$temp_redeempoint = $redeempoint = floatval($redeempoint);
		$total_redeempoint = floatval($total_redeempoint);

		if (!empty($customer)) {

			if ($total_redeempoint >= $redeempoint) {
				$points_used = '';
				$point_amount = 0;
				$points_history = array();

				/*----------------------------------------------------------------*/

				$where = array(
					'lh_customer_id' => $customer_id,
					'lh_expiry_on >=' => current_date(),
					'lh_expiry_flag' => 'yes'
				);

				$orderby = array('lh_created_on' => 'ASC');

				/*Get rreward points list*/
				$res_set = $CI->Mydb->get_all_records('lh_id,lh_reward_per_point,lh_reward_per_amount,lh_debit_points,(lh_credit_points-lh_debit_points) as reward_points', $loyalty_table, $where, '', '', $orderby);

				$temp_points = 0;
				$point_amount = 0;


				foreach ($res_set as $res_arr) {

					$reward_per_point = floatval($res_arr['lh_reward_per_point']);
					$reward_per_amount = floatval($res_arr['lh_reward_per_amount']);
					$debit_points = floatval($res_arr['lh_debit_points']);

					$actual_reward_points = floatval($res_arr['reward_points']);

					if ($actual_reward_points != 0) {

						/*---------------------------------------------------*/

						if ($actual_reward_points >= $temp_redeempoint) {

							$current_used_points = $temp_redeempoint;
						} else {

							$current_used_points = $actual_reward_points;
						}

						$temp_redeempoint = $temp_redeempoint - $current_used_points;
						$temp_points += $current_used_points;

						$current_cal_amount = ($current_used_points / $reward_per_point) * $reward_per_amount;
						$point_amount += $current_cal_amount;

						//echo $current_used_points.'--------'.$current_cal_amount.'<br/>';

						if ($temp_points == $redeempoint || $point_amount >= $cartamount) {
							//echo 'point_amount.'.$point_amount.'-cartamount--'.$cartamount .'--<br/>';
							if ($point_amount > $cartamount) {

								$get_exceed_amount = $point_amount - $cartamount;
								//$get_exceed_amount = floor($get_exceed_amount * 100) / 100;

								$get_exceed_points = ($get_exceed_amount * $reward_per_point) / $reward_per_amount;
								//echo 'exceed amount.'.$get_exceed_amount.'-epoints--'.$get_exceed_points .'--<br/>';
								$temp_points -= $current_used_points;

								//echo $temp_points.'--<br/>';

								$temp_points += ($current_used_points - $get_exceed_points);

								//echo $temp_points.'--<br/>';

							}

							break;
						}
					}
				}

				if ($point_amount > $cartamount) {
					$point_amount = (float)$cartamount;
				}

				$ex_plo = explode('.', $point_amount);
				if (isset($ex_plo[1]) && strlen($ex_plo[1]) > 2) {

					$point_amount = floor($point_amount * 100) / 100;
				}

				$details = array(
					'points_used' => $temp_points,
					'points_amount' => $point_amount
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


/* get loyality Amount - without amount(cart amount)*/
if (!function_exists('validate_loyalty_point_without_amount')) {

	function validate_loyalty_point_without_amount($redeempoint, $app_id, $customer_id, $client_redeempoint)
	{

		$CI = &get_instance();

		$redeempoint = floatval($redeempoint);
		$total_redeempoint = get_loyality_points($customer_id);

		$customer_table = 'customers';
		$loyalty_table = 'loyality_points';

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

		$temp_redeempoint = $redeempoint = floatval($redeempoint);
		$total_redeempoint = floatval($total_redeempoint);

		if (!empty($customer)) {

			if ($total_redeempoint >= $redeempoint) {
				$points_used = '';
				$point_amount = 0;
				$points_history = array();

				/*----------------------------------------------------------------*/

				$where = array(
					'lh_customer_id' => $customer_id,
					'lh_expiry_on >=' => current_date(),
					'lh_expiry_flag' => 'yes'
				);

				$orderby = array('lh_created_on' => 'ASC');

				/*Get rreward points list*/
				$res_set = $CI->Mydb->get_all_records('lh_id,lh_reward_per_point,lh_reward_per_amount,lh_debit_points,(lh_credit_points-lh_debit_points) as reward_points', $loyalty_table, $where, '', '', $orderby);

				$temp_points = 0;
				$point_amount = 0;


				foreach ($res_set as $res_arr) {

					$reward_per_point = floatval($res_arr['lh_reward_per_point']);
					$reward_per_amount = floatval($res_arr['lh_reward_per_amount']);
					$debit_points = floatval($res_arr['lh_debit_points']);

					$actual_reward_points = floatval($res_arr['reward_points']);

					if ($actual_reward_points != 0) {

						/*---------------------------------------------------*/

						if ($actual_reward_points >= $temp_redeempoint) {

							$current_used_points = $temp_redeempoint;
						} else {

							$current_used_points = $actual_reward_points;
						}

						$temp_redeempoint = $temp_redeempoint - $current_used_points;
						$temp_points += $current_used_points;

						$point_amount += ($current_used_points / $reward_per_point) * $reward_per_amount;

						if ($temp_points == $redeempoint) break;
					}
				}

				$details = array(
					'points_used' => $temp_points,
					'points_amount' => $point_amount
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

/* validate loyality points*/
if (!function_exists('deduct_loyalty_point')) {

	function deduct_loyalty_point($app_id, $customer_id, $redeempoint, $redeemed_amount)
	{

		$CI = &get_instance();

		$redeempoint = floatval($redeempoint);
		$redeemed_amount = floatval($redeemed_amount);

		$customer_table = 'customers';
		$loyalty_table = 'loyality_points';

		$current_date = date('Y-m-d');

		$point_amount = 0;
		$points_return_arr = array();

		/*----------------------------------------------------------------*/

		$where = array(
			'lh_customer_id' => $customer_id,
			'lh_expiry_on >=' => current_date(),
			'lh_expiry_flag' => 'yes'
		);

		$orderby = array('lh_created_on' => 'ASC');

		/*Get rreward points list*/
		$res_set = $CI->Mydb->get_all_records('lh_id,lh_reward_per_point,lh_reward_per_amount,lh_debit_points,(lh_credit_points-lh_debit_points) as reward_points', $loyalty_table, $where, '', '', $orderby);

		foreach ($res_set as $res_arr) {

			$reward_per_point = floatval($res_arr['lh_reward_per_point']);
			$reward_per_amount = floatval($res_arr['lh_reward_per_amount']);
			$debit_points = floatval($res_arr['lh_debit_points']);

			$remaining_points = floatval($res_arr['reward_points']);

			if ($remaining_points != 0) {

				if ($point_amount >= $redeemed_amount) break;

				$temp_point_amount = $point_amount;

				/*---------------------------------------------------*/
				$point_amount += ($remaining_points / $reward_per_point) * $reward_per_amount;

				if ($point_amount > $redeemed_amount) {

					$bal_amount = $redeemed_amount - $temp_point_amount;

					/*remaing points need to reach the redeemed amount*/
					$need_points = ($bal_amount * $reward_per_point) / $reward_per_amount;
					$remaining_points = $need_points;
				}

				/*------------------Debit points update---------------*/

				$debit_points = $debit_points + $remaining_points;

				if ($CI->Mydb->update($loyalty_table, array('lh_id' => $res_arr['lh_id']), array('lh_debit_points' => $debit_points))) {

					$points_return_arr[$res_arr['lh_id']] = $remaining_points;
				}
				/*------------------------------------------------------*/
			}
		}

		return $points_return_arr;
	}
}

/* push notification for earn points*/
if (!function_exists('loyality_earn_points_notify')) {

	function loyality_earn_points_notify($order_primary_id)
	{

		$CI = &get_instance();

		if ($order_primary_id != '') {

			$order_res = $CI->Mydb->get_record('order_local_no,order_company_unique_id', 'orders', array('order_primary_id' => $order_primary_id));
			if ($order_res) {

				$order_no = $order_res['order_local_no'];
				$app_id = $order_res['order_company_unique_id'];

				$customer_res = $CI->Mydb->get_record('order_customer_id', 'pos_orders_customer_details', array('order_customer_order_primary_id' => $order_primary_id));
				if ($customer_res) {

					$customer_id = $customer_res['order_customer_id'];

					$point_res = $CI->Mydb->get_record('lh_credit_points', 'loyality_points', array('lh_customer_id' => $customer_id, 'lh_from' => 'order', 'lh_ref_id' => $order_primary_id));
					$points = (float)$point_res['lh_credit_points'];

					if ((float)$points != 0) {

						$push_info_arr = array();
						$push_from = 'Earnpoints';

						$push_info_arr['points'] = $points;
						$push_info_arr['order_no'] = $order_no;
						$push_info_arr['app_id'] = $app_id;

						push_activities($customer_id, $push_from, $push_info_arr);
					}
				}
			}
		}

		return true;
	}
}
