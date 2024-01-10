<?php

/**************************
Project Name	: White Label
Created on		: 18 Aug, 2023
Last Modified 	: 04 Sep, 2023
Description		: Page contains common REST settings
 ***************************/

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/* this function used validate APP ID */
if (!function_exists('app_validation')) {
	function app_validation($unquieID)
	{
		$CI = &get_instance();
		$company = $CI->Mydb->get_record('*', 'company', array('company_unquie_id' => addslashes($unquieID), 'company_status' => 'A'));
		if (empty($company)) {
			echo json_encode(array('status' => 'error', 'message' => get_label('app_invalid')));
			exit;
		} else {
			$company_setting_array = $CI->Mydb->get_record("setting_value", "company_settings", array('company_id' => $company['company_id']));
			if (!empty($company_setting_array) && !empty($company_setting_array['setting_value'])) {
				$clientSetting = json_decode($company_setting_array['setting_value'], true);
				$company = array_merge($company, $clientSetting);
			}
			return $company;
		}
	}
}

if (!function_exists('client_validation')) {
	function client_validation($company_id)
	{
		$CI = &get_instance();

		$company = $CI->Mydb->get_record('*', 'company', array('company_id' => addslashes($company_id), 'company_status' => 'A'));
		if (empty($company)) {

			echo json_encode(array('status' => 'error', 'message' => get_label('app_invalid')));
			exit;
		} else {
			$company_setting_array = $CI->Mydb->get_record("setting_value", "company_settings", array('company_id' => $company['company_id']));
			if (!empty($company_setting_array) && !empty($company_setting_array['setting_value'])) {
				$clientSetting = json_decode($company_setting_array['setting_value'], true);
				$company = array_merge($company, $clientSetting);
			}
			return $company;
		}
	}
}

if (!function_exists('get_reservation_local_ordeno')) {
	function get_reservation_local_ordeno($app_id = null, $order_source)
	{
		$CI = &get_instance();
		$loc_parent = date("ymd");
		$loc_query = "SELECT reservation_local_order_id FROM pos_reservation WHERE reservation_app_id = " . $CI->db->escape($app_id) . " AND reservation_local_order_id like   '%$loc_parent%'  ORDER BY  	reservation_id DESC LIMIT 1";

		$loc_result = $CI->Mydb->custom_query_single($loc_query);
		if (!empty($loc_result)) {
			$old_lno = substr($loc_result['reservation_local_order_id'], -4) + 1;
		} else {
			$old_lno = 1001;
		}

		if ($order_source == "CallCenter") {
			$string = "C";
		} elseif ($order_source == "Mobile") {
			$string = "M";
		} elseif ($order_source == "Reservation") {
			$string = "R";
		} else {
			$string = "E";
		}

		$loc_order_no = $loc_parent . "." . $string . $old_lno;
		return $loc_order_no;
	}
}
/* this function used to  200 response */
if (!function_exists('success_response')) {
	function success_response()
	{
		return 200;
	}
}

/* this function used to  success response */
if (!function_exists('notfound_response')) {
	function notfound_response()
	{
		return 200;
	}
}

/* this function used to  success response */
if (!function_exists('something_wrong')) {
	function something_wrong()
	{
		return 200;
	}
}

/* this function used to chnage timezone */
if (!function_exists('change_time_zone')) {
	function change_time_zone($time_zone = null)
	{
		$time_zone = ($time_zone == "") ? "Asia/Singapore" : $time_zone;
		date_default_timezone_set($time_zone);
	}
}

/* this function used to get delivery global id */
if (!function_exists('get_delivery_id')) {
	function get_delivery_id($time_zone = null)
	{
		return '634E6FA8-8DAF-4046-A494-FFC1FCF8BD11';
	}
}


/* this function used to get delivery global id */
if (!function_exists('get_pickup_id')) {
	function get_pickup_id()
	{
		return '718B1A92-5EBB-4F25-B24D-3067606F67F0';
	}
}
/* this function used to get availability name */
if (!function_exists('get_availability_name')) {
	function get_availability_name($availability_id = null)
	{
		$CI = &get_instance();
		$result = $CI->Mydb->get_record('av_name', 'availability', array('av_id' => $availability_id));
		return (!empty($result)) ? join($result) : "";
	}
}
/* this function used to get product name */
if (!function_exists('get_product_tag')) {
	function get_product_tag($product_tag_id = null)
	{
		$CI = &get_instance();
		//$result = $CI->Mydb->get_record('pro_tag_name','product_sub_tags',array('pro_tag_id' => $product_tag_id));
		// return (!empty($result))? join($result) : "";

		$join[0]['select'] = "";
		$join[0]['table'] = "product_sub_tags";
		$join[0]['condition'] = "pro_sub_tag_id = pro_tag_id";
		$join[0]['type'] = "inner";
		$where = array(
			'pro_tag_app_id' => $product_tag_id
		);
		$select_array = array('pro_tag_name ');
		$record = $CI->Mydb->get_all_records($select_array, 'product_tags', $where, '', '', '', '', '', $join);
		//echo $CI->db->last_query();
		//exit;
		return $record;
	}
}


if (!function_exists('get_local_ordeno')) {
	function get_local_ordeno($app_id = null, $order_source)
	{
		$CI = &get_instance();
		$loc_parent = date("ymd");
		$loc_query = "SELECT order_local_no FROM pos_orders WHERE order_company_unique_id = " . $CI->db->escape($app_id) . "  ORDER BY order_primary_id DESC LIMIT 1";

		$loc_result = $CI->Mydb->custom_query_single($loc_query);
		if (!empty($loc_result)) {
			$old_lno = substr($loc_result['order_local_no'], -4) + 1;
		} else {
			$old_lno = 1001;
		}

		if ($order_source == "CallCenter") {
			$string = "C";
		} elseif ($order_source == "Mobile") {
			$string = "M";
		} else {
			$string = "E";
		}

		$loc_order_no = $loc_parent . "." . $string . $old_lno;
		return $loc_order_no;
	}
}
if (!function_exists('show_price_client')) {
	function show_price_client($price, $company_currency)
	{
		return $company_currency . " " . number_format($price, 3);
	}
}


/*check image exists or not check this writing a file */
if (!function_exists('check_image_exists')) {
	function check_image_exists($path, $image, $imageType)
	{
		$CI = &get_instance();
		$filename = $path . $image . $imageType;
		if (file_exists($filename)) {
			$image = random_string('alnum', 30);
			return check_image_exists($path, $image, $imageType);
		} else {
			return $image . $imageType;
		}
	}
}
/*check image exists or not check this writing a file */
if (!function_exists('update_addtioncarttotal')) {
	function update_addtioncarttotal($cart_id, $priceupdate)
	{
		$newsubtotal = $newgrandtotal = $cart_delivery_charge = $cart_sub_total = $cartupdate = "";
		$update_array = array();
		$CI = &get_instance();
		$result = $CI->Mydb->get_record('cart_sub_total,cart_delivery_charge', 'cart_details', array('cart_id' => $cart_id));
		$cart_delivery_charge = $result['cart_delivery_charge'];
		$cart_sub_total = $result['cart_sub_total'];
		$newsubtotal = $cart_sub_total + $priceupdate;
		$newgrandtotal = $newsubtotal + $cart_delivery_charge;
		$update_array = array('cart_sub_total' => $newsubtotal, 'cart_grand_total' => $newgrandtotal);
		$cartupdate = $CI->Mydb->update('cart_details', array('cart_id' => $cart_id), $update_array);
		return $cartupdate;
	}
}
if (!function_exists('time_availability')) {
	function time_availability($availability_time = array())
	{
		if (!empty($availability_time)) {


			if (($availability_time->monday_available == 1) && ($availability_time->monday_start_time != "") && ($availability_time->monday_end_time) && (date('D') == 'Mon')) {
				if (!(date("H:i:s", strtotime($availability_time->monday_start_time)) < date("H:i:s")  && date("H:i:s", strtotime($availability_time->monday_end_time)) > date("H:i:s"))) {
					return 1;
				}
			}
			if (($availability_time->tuesday_available == 1) && ($availability_time->tuesday_start_time != "") && ($availability_time->tuesday_end_time != "") && (date('D') == 'Tue')) {
				if (!(date("H:i:s", strtotime($availability_time->tuesday_start_time)) < date("H:i:s")  && date("H:i:s", strtotime($availability_time->tuesday_end_time)) > date("H:i:s"))) {
					return 1;
				}
			}

			if (($availability_time->wednesday_available == 1) && ($availability_time->wednesday_start_time != "") && ($availability_time->wednesday_end_time) && (date('D') == 'Wed')) {
				if (!(date("H:i:s", strtotime($availability_time->wednesday_start_time)) < date("H:i:s")  && date("H:i:s", strtotime($availability_time->wednesday_end_time)) > date("H:i:s"))) {
					return 1;
				}
			}
			if (($availability_time->thursday_available == 1) && ($availability_time->thursday_start_time != "") && ($availability_time->thursday_end_time) && (date('D') == 'Thu')) {
				if (!(date("H:i:s", strtotime($availability_time->thursday_start_time)) < date("H:i:s")  && date("H:i:s", strtotime($availability_time->thursday_end_time)) > date("H:i:s"))) {
					return 1;
				}
			}
			if (($availability_time->friday_available == 1) && ($availability_time->friday_start_time != "") && ($availability_time->friday_end_time) && (date('D') == 'Fri')) {

				if (!(date("H:i:s", strtotime($availability_time->friday_start_time)) < date("H:i:s")  && date("H:i:s", strtotime($availability_time->friday_end_time)) > date("H:i:s"))) {
					return 1;
				}
			}
			if (($availability_time->saturday_available == 1) && ($availability_time->saturday_start_time != "") && ($availability_time->saturday_end_time) && (date('D') == 'Sat')) {
				if (!(date("H:i:s", strtotime($availability_time->saturday_start_time)) < date("H:i:s")  && date("H:i:s", strtotime($availability_time->saturday_end_time)) > date("H:i:s"))) {
					return 1;
				}
			}
			if (($availability_time->sunday_available == 1) && ($availability_time->sunday_start_time != "") && ($availability_time->sunday_end_time) && (date('D') == 'Sun')) {
				if (!(date("H:i:s", strtotime($availability_time->sunday_start_time)) < date("H:i:s")  && date("H:i:s", strtotime($availability_time->sunday_end_time)) > date("H:i:s"))) {
					return 1;
				}
			}
		}
		return 0;
	}
}
/* splecial_days_availability */

if (!function_exists('splecial_days_availability')) {
	function splecial_days_availability($product_spcial_days = array())
	{
		if (!empty($product_spcial_days)) {
			if (($product_spcial_days->product_from_special_day != "") && ($product_spcial_days->product_to_special_day != "")) {
				$get_current_date = strtotime(date('Y-m-d'));
				$from_date = strtotime($product_spcial_days->product_from_special_day);
				$to_date = strtotime($product_spcial_days->product_to_special_day);
				if (($get_current_date >= $from_date) && ($get_current_date <= $to_date)) {
					return 1;
				}
			}
		}
		return 0;
	}
}

/* splecial_days_availability New 22-Nov-2019 */
if (!function_exists('timeavailability_check')) {
	function timeavailability_check($availability_time = array())
	{
		$productavailable = 'yes';
		if (!empty($availability_time)) {
			$currentDay  = date('l');
			$currentDay  = strtolower($currentDay);
			$availableKy = $currentDay . '_available';
			$startTimeKy = $currentDay . '_start_time';
			$endTimeKy 	 = $currentDay . '_end_time';

			if (($availability_time[$availableKy] == 1) && ($availability_time[$startTimeKy] != "") && ($availability_time[$endTimeKy] != "")) {

				if (!(date("H:i:s", strtotime($availability_time[$startTimeKy])) < date("H:i:s")  && date("H:i:s", strtotime($availability_time[$endTimeKy])) > date("H:i:s"))) {

					$productavailable = 'no';
				}
			}
		}
		return $productavailable;
	}
}
/* splecial_days_availability New */


/* this function get counts each module (Orders,promotion,reward points,notifications) */
if (!function_exists('get_activity_counts_withoutuniqcode')) {
	function get_activity_counts_withoutuniqcode($app_id, $customer_id, $act_arr = array())
	{
		$CI = &get_instance();

		$retun_arr = array();

		if (in_array('promotion', $act_arr)) {

			$current_date = current_date();

			$querytxt  = "SELECT `promotion_id`, `promotion_name`, `promotion_long_desc` as `promo_desc`, `promotion_desc`, `promotion_image`, `promotion_start_date`, `promotion_end_date`, `promotion_no_use`,";
			$querytxt .= " (select count(*) from pos_promotion_history WHERE promotion_history_promotion_id=promain.promotion_id AND promotion_history_customer_id='" . $customer_id . "') as prom_history,";
			$querytxt .= " (select count(*) from pos_promotion_customer as cust WHERE cust.`pro_promotion_id` = promain.`promotion_id`) as cust_promo_all,";
			$querytxt .= " (select count(*) from pos_promotion_customer as cust WHERE cust.`pro_promotion_id` = promain.`promotion_id` AND cust.`pro_customer_id`='" . $customer_id . "') as cust_promo_intl";
			$querytxt .= " FROM `pos_promotion` as promain";
			$querytxt .= " WHERE (`promotion_end_date` >= '" . $current_date . "'  AND `promotion_start_date` <= '" . $current_date . "' )";
			$querytxt .= " AND `promotion_app_id` = '" . $app_id . "' AND promotion_cata_flag = 'Yes'";
			$querytxt .= " HAVING ((`prom_history` < `promotion_no_use`) AND (cust_promo_all = 0 OR cust_promo_intl > 0))";

			$resultList = $CI->db->query($querytxt)->result_array();

			$retun_arr['promotion'] = count($resultList);
		}
		if (in_array('order', $act_arr) || in_array('order_all', $act_arr)) {

			$join = array();

			$where = array(
				'order_company_app_id' => $app_id,
				'(order_status!=4 AND order_status!=5)' => null,
				'(order_availability_id !="' . RESERVATION_ID . '")' => null
			);

			$join[0]['select'] = "order_customer_id";
			$join[0]['table'] = "pos_orders_customer_details";
			$join[0]['condition'] = "order_customer_order_primary_id = order_primary_id AND order_customer_id = " . $customer_id;
			$join[0]['type'] = "INNER ";

			$retun_arr['order'] = $CI->Mydb->get_num_join_rows('order_id', 'orders', $where, '', '', '', '', '', $join);

			//echo $CI->db->last_query();

			if (in_array('order_all', $act_arr)) {

				$retun_arr['reservation_order'] = 0;
				$retun_arr['catering_order'] = 0;
			}

			$retun_arr['order_all'] = $retun_arr['order'] + $retun_arr['reservation_order'] + $retun_arr['catering_order'];
		}

		return $retun_arr;
	}
}

/* this function get counts each module (Orders,promotion,reward points,notifications) */
if (!function_exists('get_activity_counts')) {
	function get_activity_counts($app_id, $customer_id, $act_arr = array())
	{
		$CI = &get_instance();

		$retun_arr = array();

		if (in_array('promotionwithoutuqc', $act_arr)) {

			$current_date = current_date();

			$querytxt  = "SELECT `promotion_id`, `promotion_name`, `promotion_long_desc` as `promo_desc`, `promotion_desc`, `promotion_image`, `promotion_start_date`, `promotion_end_date`, `promotion_no_use`,";
			$querytxt .= " (select count(*) from pos_promotion_history WHERE promotion_history_promotion_id=promain.promotion_id AND promotion_history_customer_id='" . $customer_id . "') as prom_history,";
			$querytxt .= " (select count(*) from pos_promotion_customer as cust WHERE cust.`pro_promotion_id` = promain.`promotion_id`) as cust_promo_all,";
			$querytxt .= " (select count(*) from pos_promotion_customer as cust WHERE cust.`pro_promotion_id` = promain.`promotion_id` AND cust.`pro_customer_id`='" . $customer_id . "') as cust_promo_intl";
			$querytxt .= " FROM `pos_promotion` as promain";
			$querytxt .= " WHERE (`promotion_end_date` >= '" . $current_date . "'  AND `promotion_start_date` <= '" . $current_date . "' )";
			$querytxt .= " AND `promotion_app_id` = '" . $app_id . "' AND promotion_cata_flag = 'Yes' AND `promotion_status` = 'A'";
			$querytxt .= " HAVING ((`prom_history` < `promotion_no_use`) AND (cust_promo_all = 0 OR cust_promo_intl > 0))";

			$resultList = $CI->db->query($querytxt)->result_array();

			$retun_arr['promotionwithoutuqc'] = count($resultList);
		}

		if (in_array('order_withcatering', $act_arr)) {

			$join = array();

			$where = array(
				'order_company_app_id' => $app_id,
				'(order_status!=4 AND order_status!=5)' => null,
				'(order_availability_id !="' . RESERVATION_ID . '")' => null
			);

			$join[0]['select'] = "order_customer_id";
			$join[0]['table'] = "pos_orders_customer_details";
			$join[0]['condition'] = "order_customer_order_primary_id = order_primary_id AND order_customer_id = " . $customer_id;
			$join[0]['type'] = "INNER ";

			$retun_arr['order'] = $CI->Mydb->get_num_join_rows('order_id', 'orders', $where, '', '', '', '', '', $join);

			$retun_arr['order_withcatering'] = $retun_arr['order'];
		}

		if (in_array('promotion', $act_arr)) {

			$current_date = current_date();
			$where = array(
				'promotion_cust_id' => $customer_id,
				'promotion_cata_flag' => 'Yes',
				"( promotion_end_date >= '$current_date'  AND promotion_start_date <= '$current_date' ) " => NULL,
			);

			$order_by = array(
				'pos_promotion_customer_code_creation.promotion_id' => 'ASC',
			);


			$select_array = array(
				'promotion_cust_uniqcode as promo_code',
				'promotion_cust_id',
				"(select count(*) from pos_promotion_history WHERE promotion_history_promotion_id=pos_promotion.promotion_id AND promotion_history_customer_id=$customer_id) as prom_history",
			);


			$join[0]['select'] = "promotion.promotion_id,promotion_long_desc as promo_desc,promotion_desc,promotion_image,promotion_start_date,promotion_end_date,promotion_no_use";
			$join[0]['table'] = 'pos_promotion';
			$join[0]['condition'] = "pos_promotion.promotion_id = promotion_customer_code_creation.promotion_id";
			$join[0]['type'] = "LEFT";

			$CI->db->having('prom_history < promotion_no_use');

			$res_list = $CI->Mydb->get_all_records($select_array, 'promotion_customer_code_creation', $where, '', '', $order_by, '', '', $join);

			/*$where = array ("ref_customer_id" => $customer_id,"DATE_FORMAT(promo_expire_date,'%Y-%m-%d') >=" => date ( "Y-m-d" ),"promo_used" => "No");

			$res_list = $CI->Mydb->get_record ( 'COUNT(*)', 'promotion_refer', $where);
			$retun_arr['promotion'] = (int)$res_list['COUNT(*)'];*/

			$retun_arr['promotion'] = count($res_list);
		}
		if (in_array('order', $act_arr) || in_array('order_all', $act_arr)) {

			$join = array();

			$where = array(
				'order_company_app_id' => $app_id,
				'(order_status!=4 AND order_status!=5)' => null,
				'(order_availability_id !="' . RESERVATION_ID . '")' => null
			);

			$join[0]['select'] = "order_customer_id";
			$join[0]['table'] = "pos_orders_customer_details";
			$join[0]['condition'] = "order_customer_order_primary_id = order_primary_id AND order_customer_id = " . $customer_id;
			$join[0]['type'] = "INNER ";

			$retun_arr['order'] = $CI->Mydb->get_num_join_rows('order_id', 'orders', $where, '', '', '', '', '', $join);

			//echo $CI->db->last_query();

			if (in_array('order_all', $act_arr)) {

				$retun_arr['reservation_order'] = 0;
				$retun_arr['catering_order'] = 0;
			}

			$retun_arr['order_all'] = $retun_arr['order'] + $retun_arr['reservation_order'] + $retun_arr['catering_order'];
		}

		if (in_array('overall_orders', $act_arr)) {

			$join = array();

			$where = array(
				'order_company_app_id' => $app_id,
				'(order_status!=4 AND order_status!=5)' => null
			);

			$join[0]['select'] = "order_customer_id";
			$join[0]['table'] = "pos_orders_customer_details";
			$join[0]['condition'] = "order_customer_order_primary_id = order_primary_id AND order_customer_id = " . $customer_id;
			$join[0]['type'] = "INNER ";

			$retun_arr['order'] = $CI->Mydb->get_num_join_rows('order_id', 'orders', $where, '', '', '', '', '', $join);

			$where_res = array(
				'reservation_id !=' => '', 'reservation_customer_id' => $customer_id, 'reservation_app_id' => $app_id, 'reservation_availability_id' => RESERVATION_ID, '(reservation_status != "2" AND reservation_status != "5")' => null
			);
			$retun_arr['reservation_orders'] = $CI->Mydb->get_num_join_rows('reservation_id', 'reservation', $where_res, '', '', '', '', '', array());

			$retun_arr['overall_orders'] = $retun_arr['order'] + $retun_arr['reservation_orders'];
		}

		if (in_array('deliveryTakeaway_orders', $act_arr)) {

			$join = array();

			$where = array(
				'order_company_app_id' => $app_id,
				'(order_status!=4 AND order_status!=5)' => null,
				'(order_availability_id !="' . CATERING_ID . '" AND order_availability_id !="' . RESERVATION_ID . '")' => null
			);

			$join[0]['select'] = "order_customer_id";
			$join[0]['table'] = "pos_orders_customer_details";
			$join[0]['condition'] = "order_customer_order_primary_id = order_primary_id AND order_customer_id = " . $customer_id;
			$join[0]['type'] = "INNER ";

			$deliveryTakeaway = $CI->Mydb->get_num_join_rows('order_id', 'orders', $where, '', '', '', '', '', $join);

			$retun_arr['deliveryTakeaway_orders'] = $deliveryTakeaway;
		}

		if (in_array('bento_orders', $act_arr)) {

			$join = array();

			$where = array(
				'order_company_app_id' => $app_id,
				'(order_status!=4 AND order_status!=5)' => null,
				'(order_availability_id ="' . BENTO_ID . '")' => null
			);

			$join[0]['select'] = "order_customer_id";
			$join[0]['table'] = "pos_orders_customer_details";
			$join[0]['condition'] = "order_customer_order_primary_id = order_primary_id AND order_customer_id = " . $customer_id;
			$join[0]['type'] = "INNER ";

			$bentoOrders = $CI->Mydb->get_num_join_rows('order_id', 'orders', $where, '', '', '', '', '', $join);

			$retun_arr['bento_orders'] = $bentoOrders;
		}

		if (in_array('catering_orders', $act_arr)) {

			$join = array();

			$where = array(
				'order_company_app_id' => $app_id,
				'(order_status!=4 AND order_status!=5)' => null,
				'(order_availability_id ="' . CATERING_ID . '")' => null
			);

			$join[0]['select'] = "order_customer_id";
			$join[0]['table'] = "pos_orders_customer_details";
			$join[0]['condition'] = "order_customer_order_primary_id = order_primary_id AND order_customer_id = " . $customer_id;
			$join[0]['type'] = "INNER ";

			$catering_orders = $CI->Mydb->get_num_join_rows('order_id', 'orders', $where, '', '', '', '', '', $join);

			$retun_arr['catering_orders'] = $catering_orders;
		}

		return $retun_arr;
	}
}


/* this method used for add maintain stock value */
if (!function_exists('rest_product_stock_log')) {
	function rest_product_stock_log($company, $id = null, $stock = null, $orderId = null, $product_mode = null, $type = null)
	{

		$CI = &get_instance();
		$table = 'products';
		if (empty($type)) {
			$type = 'P';
		}

		if (!empty($id)) {
			$record = $CI->Mydb->get_record('product_stock', $table, array(
				'product_id' => $id,
				'product_company_id' => $company['company_id'],
				'product_company_unique_id' => $company['company_unquie_id']
			));
			if (!empty($type)) {
				$stock_val = $record['product_stock'] + $stock;
				$type = 'C';
			} else {
				$stock_val = $record['product_stock'] - $stock;
				$type = 'S';
			}

			$CI->Mydb->insert('product_stock_log', array(
				'product_stock_company_id' => $company['company_id'],
				'product_stock_company_unquie_id' => $company['company_unquie_id'],
				'product_stock_product_id' => $id,
				'product_order_primary_id' => $orderId,
				'product_stock_type' => $type,
				'product_stock_mode' => $product_mode,
				'product_stock_value' => (int)$stock,
				'product_stock_created' => current_date(),
				'product_stock_created_ip' => get_ip(),
			));

			/* Update the quantity */
			$CI->Mydb->update($table, array(
				'product_id' => $id
			), array('product_stock' => $stock_val));
		}
	}
}

/* this function get amount */
if (!function_exists('get_membership_spent')) {
	function get_membership_spent($app_id, $customer_id)
	{
		$CI = &get_instance();

		$spending_amt = 0;

		$join = array();

		$where = array(
			'order_customer_id' => $customer_id,
			'order_company_app_id' => $app_id,
			'order_status' => 4
		);

		$join[0]['select'] = 'order_customer_id';
		$join[0]['table'] = 'pos_orders_customer_details';
		$join[0]['condition'] = 'order_customer_order_primary_id = order_primary_id';
		$join[0]['type'] = 'INNER';

		$join[1]['select'] = 'customer_id,customer_membership_type,customer_nick_name,customer_first_name,customer_last_name';
		$join[1]['table'] = 'pos_customers';
		$join[1]['condition'] = 'customer_id = order_customer_id AND customer_membership_type != \'Kakis\'';

		$join[1]['type'] = 'INNER';

		/*refer rest helper - get_membership_spent()*/
		$result_set = $CI->Mydb->get_all_records('SUM(order_sub_total-order_discount_amount) as spending_amt', 'orders', $where, '', '', '', '', '', $join);

		//echo $CI->db->last_query();

		if (isset($result_set[0])) {
			$spending_amt = (float)$result_set[0]['spending_amt'];
		}

		return $spending_amt;
	}
}

/* this function get customer membership */
if (!function_exists('get_membership_type')) {
	function get_membership_type($customer_id)
	{
		$CI = &get_instance();

		$customer_membership_type = '';

		$res = $CI->Mydb->get_record('customer_membership_type,customer_id', 'pos_customers', array('customer_id' => $customer_id));

		if ($res) {
			$customer_membership_type = $res['customer_membership_type'];
		}

		return $customer_membership_type;
	}
}

/* this function get rewars earn availability*/
if (!function_exists('get_rewards_availability')) {
	function get_rewards_availability($app_id, $avilablity_id)
	{
		$CI = &get_instance();

		$rt_val = 1;

		if (georges_app_id == $app_id) {

			$avi_arr = array(DELIVERY_ID, PICKUP_ID, DINEIN_ID);

			/*if not exist*/
			if (!in_array($avilablity_id, $avi_arr)) {
				$rt_val = 0;
			}
		}

		return $rt_val;
	}
}

/* this function get cart discount availability */
if (!function_exists('get_cart_discount_availability')) {
	function get_cart_discount_availability($app_id, $avilablity_id)
	{
		$CI = &get_instance();

		$rt_val = 1;

		if ($app_id == georges_app_id) {

			$avi_arr = array(DINEIN_ID);

			/*if not exist*/
			if (!in_array($avilablity_id, $avi_arr)) {
				$rt_val = 0;
			}
		} else if ($app_id == nelsonbar_app_id) {

			$avi_arr = array(DINEIN_ID);

			/*if not exist*/
			if (!in_array($avilablity_id, $avi_arr)) {
				$rt_val = 0;
			}
		} else if ($app_id == muthus_app_id) {

			$avi_arr = array(DINEIN_ID, DELIVERY_ID, PICKUP_ID);

			/*if not exist*/
			if (!in_array($avilablity_id, $avi_arr)) {
				$rt_val = 0;
			}
		} else {
		}

		return $rt_val;
	}
}

/* this function get cart discount availability */
if (!function_exists('get_discount_details')) {
	function get_discount_details($product_id, $group_id = '')
	{
		$CI = &get_instance();

		//$result['product_discount_details'] = $CI->Mydb->get_all_records('*','discounts',array('discount_product_id'=>$product_id),'','',array('discount_product_count' => 'ASC'));

		$join[0]['select'] = "";
		$join[0]['table'] = "discount_groups";
		$join[0]['condition'] = " dis_group_primary_id =  discount_detail_group_id ";
		$join[0]['type'] = "INNER";

		$join[1]['select'] = "";
		$join[1]['table'] = "pos_discount_groups_assigned";
		$join[1]['condition'] = " discount_assign_group_id =  dis_group_primary_id ";
		$join[1]['type'] = "INNER";

		$current_date = current_date();
		$where = array(
			'dis_group_status' => 'A',
			"(dis_group_start_date <= '$current_date' AND dis_group_end_date >= '$current_date')" => NULL,
		);

		$group_by = '';
		if ($group_id != '') {
			$where = array_merge($where, array('discount_detail_group_id' => $group_id));
			$group_by = 'discount_detail_product_id';
		} else {
			$where = array_merge($where, array('discount_detail_product_id' => $product_id));
		}

		$result = $CI->Mydb->get_all_records('*', 'discount_groups_details', $where, '', '', array('dis_group_discount_type' => 'DESC', 'discount_assign_discount_quantity' => 'DESC'), '', $group_by, $join);


		return $result;
	}
}

if (!function_exists('distance_calculation')) {
	function distance_calculation($lat1, $lon1, $lat2, $lon2, $unit)
	{
		$theta = $lon1 - $lon2;
		$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
		$dist = acos($dist);
		$dist = rad2deg($dist);
		$miles = $dist * 60 * 1.1515;
		$unit = strtoupper($unit);
		if ($unit == "K") {
			return ($miles * 1.609344);
		} else if ($unit == "N") {
			return ($miles * 0.8684);
		} else {
			return $miles;
		}
	}
}


if (!function_exists('getClientSettings')) {
	function getClientSettings($company_id)
	{
		$CI = &get_instance();
		$result = $CI->Mydb->get_all_records('setting_key, setting_value', 'company_settings', array('company_id' => $company_id));
		$settings = array();
		if (!empty($result)) {
			$setting_key = array_column($result, 'setting_key');
			$setting_value = array_column($result, 'setting_value');
			$settings = array_combine($setting_key, $setting_value);
		}

		return $settings;
	}
}

if (!function_exists('getUserDetails')) {
	function getUserDetails($userID)
	{
		$CI = &get_instance();
		$result = $CI->Mydb->get_record('company_user_permission_outlet, company_user_type', 'company_user', array('company_user_id' => $userID, 'company_user_status' => 'A'));
		return $result;
	}
}



if (!function_exists('loadCurlPost')) {
	function loadCurlPost($url, $headers = null, $data)
	{
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		if (!empty($headers)) {
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		}
		if (!empty($data)) {
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}

		//for debug only!
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		$resp = curl_exec($curl);
		curl_close($curl);
		if (!empty($resp)) {
			return json_decode($resp);
		}
	}
}

if (!function_exists('loadCurlget')) {
	function loadCurlget($url, $headers, $withoutdecode = '')
	{

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		//for debug only!
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		$resp = curl_exec($curl);

		curl_close($curl);
		if (!empty($resp)) {
			if (!empty($withoutdecode)) {
				return $resp;
			} else {
				return json_decode($resp);
			}
		}
	}
}


if (!function_exists('loadCurlPatch')) {
	function loadCurlPatch($url, $headers = null, $data)
	{

		$curl = curl_init();
		$curlData = array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'PATCH',
			CURLOPT_HTTPHEADER => $headers,
		);
		if (!empty($data)) {
			$curlData[CURLOPT_POSTFIELDS] = $data;
		}

		curl_setopt_array($curl, $curlData);

		$response = curl_exec($curl);

		curl_close($curl);
		if (!empty($response)) {
			return json_decode($response);
		}
	}
}

if (!function_exists('showPriceWithoutSymbol')) {
	function showPriceWithoutSymbol($price)
	{
		return number_format($price, 3);
	}
}
