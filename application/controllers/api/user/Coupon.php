<?php

/**************************
Project Name	: White Label
Created on		: 08 Nov, 2023
Last Modified 	: 08 Nov, 2023
Description		: Customer Coupon List & Apply Coupon Templates
 ***************************/
error_reporting(-1);

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

class Coupon extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library(array('form_validation'));
		$this->load->helper('promotion_level_one');
		$this->form_validation->set_error_delimiters('<p>', '</p>');
		$this->customerAddress = "customer_secondary_addresses";
	}
	public function listPromo_get()
	{
		$unquieid = $this->get('unquieid');
		$company = app_validation($unquieid);
		$customerID = decode_value($this->get('customerID'));

		$current_date = current_date();

		$querytxt  = "SELECT outlet_name, promotion_id AS promoID, promotion_name as promo_code, promotion_title, promotion_image AS image, promotion_thumbnail_image AS thumbnail, promotion_banner_image AS banner ,promotion_start_date AS startDate, promotion_end_date AS endDate, promotion_desc AS promotion_short_desc, promotion_long_desc as promo_desc, promotion_type, promotion_percentage, promotion_no_use, promotion_max_amt AS maxAmount, ";
		$querytxt .= " (select count(*) from pos_promotion_history WHERE promotion_history_promotion_id=promain.promotion_id AND promotion_history_customer_id='" . $customerID . "') as prom_history,";
		$querytxt .= " (select count(*) from pos_promotion_customer as cust WHERE cust.`pro_promotion_id` = promain.`promotion_id`) as cust_promo_all,";
		$querytxt .= " (select count(*) from pos_promotion_customer as cust WHERE cust.`pro_promotion_id` = promain.`promotion_id` AND cust.`pro_customer_id`='" . $customerID . "') as cust_promo_intl";
		$querytxt .= " FROM `pos_promotion` as promain LEFT JOIN `pos_outlet_management` ON promotion_outlet_id=outlet_id";
		$querytxt .= " WHERE (`promotion_end_date` >= '" . $current_date . "'  AND `promotion_start_date` <= '" . $current_date . "' )";
		$querytxt .= " AND `promotion_company_unique_id` = '" . $unquieid . "'  AND promotion_cata_flag = 'Yes' AND `promotion_status` = 'A'";
		$querytxt .= " HAVING ((`prom_history` < `promotion_no_use`) AND (cust_promo_all = 0 OR cust_promo_intl > 0))";
		$res_list = $this->db->query($querytxt)->result_array();

		if (!empty($res_list)) {
			$output_arr['available_promo'] = array();
			$my_promo = array();
			foreach ($res_list as $key => $res) {
				$datetime1 = new DateTime(date("Y-m-d"));
				$datetime2 = new DateTime($res['endDate']);
				$interval = $datetime1->diff($datetime2);
				$days = $interval->format('%a');
				if ($days > 1) {
					$days = $days . ' days left';
				} elseif ($days == 1) {
					$days = $days . ' day left';
				} else {
					$days = 'Expire today';
				}
				$my_promo[$key] = $res;
				$my_promo[$key]['expiryOn'] = (!empty($res['endDate']) && $res['endDate'] != "0000-00-00 00:00:00") ? date('d M Y', strtotime($res['endDate'])) : '';
				$my_promo[$key]['promo_days_left'] = $days;
				unset($my_promo[$key]['cust_promo_all']);
				unset($my_promo[$key]['cust_promo_intl']);
				$my_promo[$key]['promo_desc_showtext'] = stripslashes($res['promo_desc']);
			}

			$output_arr['available_promo'] =  array_merge(array(), $my_promo);
			$return_array = array('status' => "ok", 'result' =>  $output_arr);
			$this->set_response($return_array, success_response());
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => sprintf(get_label('admin_no_records_found'), 'Address'),
				'form_error' => validation_errors()
			), something_wrong()); /* error message */
		}
	}
	public function applyPromo_post()
	{
		$unquieid = post_value('unquieid');
		app_validation($unquieid);
		$this->form_validation->set_rules('discountcode', 'lang:rest_promo_code', 'required');
		$this->form_validation->set_rules('customerID', 'lang:rest_customer_id', 'required');
		$this->form_validation->set_rules('subtotal', 'lang:rest_sub_total', 'required');
		$this->form_validation->set_rules('itemQuantity', 'lang:rest_cart_quantity', 'required');
		$this->form_validation->set_rules('availabilityID', 'lang:rest_availabel_status', 'required');
		if ($this->form_validation->run() == TRUE) {
			$discountcode = explode(',', post_value('discountcode'));
			$customerID = decode_value(post_value('customerID'));
			$subtotal = post_value('subtotal');
			$itemQuantity = post_value('itemQuantity');
			$categoryID = post_value('categoryID');
			$availabilityID = post_value('availabilityID');
			$status = "ok";
			$message = "";
			$resultPromo = [];
			foreach ($discountcode as $key => $val) {
				$applyPromo = validate_promotion_code($val, $unquieid, $customerID, $subtotal, $itemQuantity, $categoryID, $availabilityID);
				$resultPromo[$key] = $applyPromo;
				if (empty($applyPromo['promotion_id'])) {
					$status = "warning";
					$resultPromo[$key]['promotion_code'] = $val;
					$message .= $val . ' -' . $applyPromo['message'];
				}
			}

			$return_array = array('status' => $status, 'message' => $message,  'discountDetails' => $resultPromo);
			$this->set_response($return_array, success_response());
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => get_label('rest_form_error'),
				'form_error' => validation_errors()
			), something_wrong()); /* error message */
		}
	}
}
