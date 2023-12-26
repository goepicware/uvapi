<?php

/**************************
Project Name	: White Label
Created on		: 29 Aug, 2023
Last Modified 	: 05 Sep, 2023
Description		: Dashboard details

 ***************************/
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Dashboard extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p>', '</p>');
		$this->load->helper('loyalty');
		$this->table = "orders";
		$this->customers = "customers";
		$this->cart_details = "cart_details";
		$this->order_status = "order_status";
		$this->order_outlet = "order_outlet";
		$this->outlet_management = "outlet_management";
		$this->orders_customer_details = "orders_customer_details";
		$this->order_methods = "order_methods";
		$this->order_items = "order_items";
		$this->component_set = "order_menu_set_components";
		$this->date_history = "order_date_history";
		$this->promotion_history = "promotion_history";
		$this->load->library('common');
		$this->label = get_label('rest_order');
		$this->load->library('Authorization_Token');
		$this->primary_key = 'order_primary_id';
		$this->company_id = 'order_company_id';
	}

	public function index_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$result = array();
				$logID = decode_value($this->input->get('logID'));
				$userDetails = getUserDetails($logID);
				if (!empty($userDetails)) {
					$filterType = $this->get('filterType');
					$initialData = $this->get('initialData');

					$company_id = decode_value($this->input->get('company_id'));

					$company = client_validation($company_id); /* validate company */
					$unique_id = $company['company_unquie_id'];

					$where = array("$this->primary_key !=" => '', $this->company_id => $company_id, 'order_status!=' => 5);
					$start = $endDate = "";
					if ($filterType == '7days') {
						$start = date('Y-m-d', strtotime('-6 day', time()));
					} else if ($filterType == '1month') {
						$start = date('Y-m-d', strtotime('-1 month', time()));
					} else if ($filterType == '3month') {
						$start = date('Y-m-d', strtotime('-3 month', time()));
					} else if ($filterType == '6month') {
						$start = date('Y-m-d', strtotime('-6 month', time()));
					} else if ($filterType == '1year') {
						$start = date('Y-m-d', strtotime('-12 month', time()));
					} else if ($filterType == 'custom') {
						$start = $this->get('start_date');
						$endDate = $this->get('end_date');
					}

					if ($initialData == true) {

						$totalOrderAmount = $this->Mydb->get_all_records('SUM(order_total_amount) AS totalAmount', $this->table, $where);
						$result['totalAmount'] = (!empty($totalOrderAmount) && !empty($totalOrderAmount[0]['totalAmount'])) ? $totalOrderAmount[0]['totalAmount'] : 0;

						$todayWhere = array_merge($where, array('order_date>=' => date('Y-m-d') . ' 00:00:00', 'order_date<=' => date('Y-m-d') . ' 23:59:59'));
						$todaytotalOrderAmount = $this->Mydb->get_all_records('SUM(order_total_amount) AS totalAmount', $this->table, $todayWhere);
						$result['todaytotalAmount'] = (!empty($todaytotalOrderAmount) && !empty($todaytotalOrderAmount[0]['totalAmount'])) ? $todaytotalOrderAmount[0]['totalAmount'] : 0;

						$totalCustomer = $this->Mydb->get_all_records('COUNT(customer_id) AS total', $this->customers, array('customer_company_id' => $company_id, 'customer_status' => 'A'));
						$result['totalCustomer'] = (!empty($totalCustomer) && !empty($totalCustomer[0]['total'])) ? $totalCustomer[0]['total'] : 0;

						$totalCart = $this->Mydb->get_all_records('COUNT(cart_id) AS total', $this->cart_details, array('cart_company_unquie_id' => $unique_id));
						$result['totalCart'] = (!empty($totalCart) && !empty($totalCart[0]['total'])) ? $totalCart[0]['total'] : 0;
					}



					$endDate = date('Y-m-d');
					$where = array_merge($where, array('order_date>=' => $start . ' 00:00:00', 'order_date<=' => $endDate . ' 23:59:59'));

					$groupBy = "orderDate";
					if ($filterType == '3month') {
						$groupBy = "YEAR(order_date), MONTH(order_date)";
					}

					$orderdata = $this->Mydb->get_all_records('DATE(order_date) AS orderDate, SUM(order_total_amount) AS totalAmount', $this->table, $where, '', '', '', '', $groupBy);
					$order_data = array();

					$result['xaxis'] = array();
					if (!empty($orderdata)) {

						if (!empty($start)) {
							$dates = $this->getAllDates($start, $endDate, $filterType);
							$finalResult = [];
							if ($filterType == '3month' || $filterType == '6month' || $filterType == '1year') {
								$finalItem = [];
								foreach ($orderdata as $key => $val) {
									$finalItem[date('M', strtotime($val['orderDate']))]['totalAmount'] = $val['totalAmount'];
								}
								$order_data = $finalItem;
							} else {
								$order_data = array_combine(array_column($orderdata, 'orderDate'), $orderdata);
							}
							foreach ($dates as $key => $val) {
								if (!empty($order_data[$val])) {
									$finalResult[$key] = $order_data[$val]['totalAmount'];
								} else {
									$finalResult[$key] = 0;
								}
							}

							$result['xaxis'] = $dates;
							$result['datas'] = $finalResult;
						}
					}

					$join = array();
					$i = 0;
					$join[$i]['select'] = "item_name";
					$join[$i]['table'] = $this->order_items;
					$join[$i]['condition'] = "order_primary_id = item_order_primary_id";
					$join[$i]['type'] = "RIGHT";
					$i++;

					$orderItemdata = $this->Mydb->get_all_records('SUM(item_total_amount) AS totalAmount', $this->table, $where, 10, 0, '', '', 'item_product_id', $join);
					$result['itemxaxis'] = array();
					$result['itemdata'] = array();
					if (!empty($orderItemdata)) {
						$result['itemxaxis'] = array_column($orderItemdata, 'item_name');
						$result['itemdata'] = array_column($orderItemdata, 'totalAmount');
					}

					$return_array = array('status' => "ok", 'message' => 'success', 'result' => $result);
					$this->set_response($return_array, success_response());
				}
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => get_label('token_verify_faild'),
					'form_error' => ''
				), something_wrong()); /* error message */
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => get_label('token_faild'),
				'form_error' => ''
			), something_wrong()); /* error message */
		}
	}

	private function getAllDates($startingDate, $endingDate, $filterType)
	{
		$datesArray = [];
		if ($filterType == '3month' || $filterType == '6month' || $filterType == '1year') {
			$end = "";
			if ($filterType == '3month') {
				$end = 3;
			} else if ($filterType == '6month') {
				$end = 6;
			} else if ($filterType == '1year') {
				$end = 12;
			}
			for ($i = 1; $i <= $end; $i++) {
				$datesArray[] = date('M', strtotime('+' . $i . ' month', strtotime($startingDate)));
			}
		} else {
			$startingDate = strtotime($startingDate);
			$endingDate = strtotime($endingDate);

			for ($currentDate = $startingDate; $currentDate <= $endingDate; $currentDate += (86400)) {
				$date = date('Y-m-d', $currentDate);
				$datesArray[] = $date;
			}
		}

		return $datesArray;
	}
} /* end of files */
