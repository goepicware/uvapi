<?php

/**************************
Project Name	: White Label
Created on		: 07 Sep, 2023
Last Modified 	: 07 Sep, 2023
Description		: Orders Report details

 ***************************/
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Report extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p>', '</p>');
		$this->load->helper('loyalty');
		$this->table = "orders";
		$this->order_status = "order_status";
		$this->order_outlet = "order_outlet";
		$this->outlet_management = "outlet_management";
		$this->orders_customer_details = "orders_customer_details";
		$this->order_methods = "order_methods";
		$this->order_items = "order_items";
		$this->component_set = "order_menu_set_components";
		$this->date_history = "order_date_history";
		$this->load->library('common');
		$this->label = get_label('rest_order');
		$this->load->library('Authorization_Token');
		$this->primary_key = 'order_primary_id';
		$this->company_id = 'order_company_id';
	}

	public function list_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$logID = decode_value($this->input->get('logID'));
				$userDetails = getUserDetails($logID);
				if (!empty($userDetails)) {
					$select_array = array(
						'order_primary_id',
						'order_location_id',
						'order_tat_time',
						'order_created_on',
						'order_id',
						'order_company_id',
						'order_company_unique_id',
						'order_delivary_type',
						'order_outlet_id',
						'order_date',
						'order_sub_total',
						'order_total_amount',
						'order_special_discount_amount',
						'order_discount_amount',
						'order_delivery_charge',
						'order_additional_delivery',
						'order_subcharge_amount',
						'order_tax_charge',
						'order_tax_calculate_amount',
						'order_tax_calculate_amount_inclusive',
						'order_service_charge_amount',
						'order_status',
						'order_availability_id',
						'order_availability_name',
						'order_local_no',
						'order_cancel_remark',
						'order_remarks',
						'order_payment_mode',
						'order_payment_getway_type',
						'order_advanced_date',
						'order_is_advanced',
						'order_source',
						'order_table_number',
						'order_status',
						'order_payment_retrieved',
						'order_pickup_time_slot_from',
						'order_pickup_time_slot_to',
						'order_is_timeslot'
					);
					$limit = $offset = '';
					$like = array();
					$get_limit = $this->input->get('limit');
					$post_offset = (int) $this->input->get('offset');
					$from_date = $this->input->get('from_date');
					$to_date = $this->input->get('to_date');
					$order_number = $this->input->get('order_number');
					$order_availability = $this->input->get('order_availability');
					$order_status = $this->input->get('order_status');
					$order_outlet = $this->input->get('order_outlet');

					if ((int) $get_limit != 0) {
						$limit = (int) $get_limit;
					}
					$post_offset = ($post_offset > 1) ? $post_offset - 1 : 0;
					$offset = ($post_offset > 0 ? $post_offset * $limit : 0);

					$company_id = decode_value($this->input->get('company_id'));
					$where = array("$this->primary_key !=" => '', $this->company_id => $company_id);
					if (!empty($order_number)) {
						$where = array_merge($where, array('order_local_no' => $order_number));
					}
					if (!empty($order_availability)) {
						$where = array_merge($where, array('order_availability_id' => $order_availability));
					}
					if (!empty($order_status)) {
						$where = array_merge($where, array('order_status' => $order_status));
					}
					if (!empty($order_outlet)) {
						$where = array_merge($where, array('outlet_id' => $order_outlet));
					}


					if (!empty($from_date) && !empty($to_date)) {
						$where = array_merge($where, array('order_date>=' => $from_date, 'order_date<=' => $to_date));
					} else if (!empty($from_date)) {
						$where = array_merge($where, array('order_date' => $from_date));
					}

					if (!empty($userDetails)) {
						if ($userDetails['company_user_type'] == 'SubAdmin') {
							$where = array_merge($where, array("outlet_id" => $userDetails['company_user_permission_outlet']));
						}
					}

					$orderByArr = array('order_primary_id' => 'DESC');
					if ($this->get('orderBy') == 'status') {
						$orderByArr = array('pos_order_status.status_order' => 'ASC', 'order_primary_id' => 'DESC');
					}

					if (!empty($sort_order_date)) {
						$orderByArr = array('order_date' => $sort_order_date);
					}

					$j = 0;
					$join[$j]['select'] = "GROUP_CONCAT(outlet_id) AS outlet_id";
					$join[$j]['table'] = $this->order_outlet;
					$join[$j]['condition'] = "order_primary_id = outlet_order_primary_id";
					$join[$j]['type'] = "INNER";
					$j++;

					$join[$j]['select'] = "pos_order_status.status_name";
					$join[$j]['table'] = $this->order_status;
					$join[$j]['condition'] = "order_status = pos_order_status.status_id";
					$join[$j]['type'] = "LEFT";
					$j++;

					$join[$j]['select'] = "order_customer_id,CONCAT_WS(' ',order_customer_fname,order_customer_lname) AS customer_name,order_customer_email,order_customer_mobile_no,
		order_customer_unit_no1,order_customer_unit_no2,order_customer_address_line1,order_customer_address_line2,order_customer_city,
		order_customer_state,order_customer_country,order_customer_postal_code,order_customer_created_on";
					$join[$j]['table'] = $this->orders_customer_details;
					$join[$j]['condition'] = "order_customer_order_primary_id = order_primary_id";
					$join[$j]['type'] = "LEFT";
					$j++;

					$join[$j]['select'] = "order_method_name";
					$join[$j]['table'] = "pos_order_methods";
					$join[$j]['condition'] = "order_payment_mode = order_method_id";
					$join[$j]['type'] = "LEFT";


					$total_records = $this->Mydb->get_num_join_rows($this->primary_key, $this->table, $where, '', '', '', $like, array('order_primary_id'), $join);


					$totalPages = (!empty($limit)) ? ceil($total_records / $limit) : 0;

					$result = $this->Mydb->get_all_records($select_array, $this->table, $where, $limit, $offset, $orderByArr, $like, array('order_primary_id'), $join);

					if (!empty($result)) {
						$outlet = $this->Mydb->get_all_records('outlet_id, outlet_name', $this->outlet_management, array('outlet_company_id' => $company_id));
						$outlet_id = array_column($outlet, 'outlet_id');
						$outlet_name = array_column($outlet, 'outlet_name');
						$clientOutlet = array_combine($outlet_id, $outlet_name);

						$return_array = array('status' => "ok", 'message' => 'success', 'result' => $result, 'totalRecords' => $total_records, 'totalPages' => $totalPages, 'outlet' => $clientOutlet);
						$this->set_response($return_array, success_response());
					} else {
						$this->set_response(array('status' => 'error', 'message' => get_label('no_records_found')), something_wrong());
					}
				} else {
					$this->set_response(array(
						'status' => 'error',
						'message' => get_label('invalid_user'),
						'form_error' => ''
					), something_wrong()); /* error message */
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
	public function export_get()
	{


		$select_array = array(
			'order_primary_id',
			'order_location_id',
			'order_tat_time',
			'order_created_on',
			'order_id',
			'order_company_id',
			'order_company_unique_id',
			'order_delivary_type',
			'order_outlet_id',
			'order_date',
			'order_sub_total',
			'order_total_amount',
			'order_special_discount_amount',
			'order_discount_amount',
			'order_delivery_charge',
			'order_additional_delivery',
			'order_subcharge_amount',
			'order_tax_charge',
			'order_tax_calculate_amount',
			'order_tax_calculate_amount_inclusive',
			'order_service_charge_amount',
			'order_status',
			'order_availability_id',
			'order_availability_name',
			'order_local_no',
			'order_cancel_remark',
			'order_remarks',
			'order_payment_mode',
			'order_payment_getway_type',
			'order_advanced_date',
			'order_is_advanced',
			'order_source',
			'order_table_number',
			'order_status',
			'order_payment_retrieved',
			'order_pickup_time_slot_from',
			'order_pickup_time_slot_to',
			'order_is_timeslot'
		);
		$like = array();
		$get_limit = $this->input->get('limit');
		$post_offset = (int) $this->input->get('offset');
		$from_date = $this->input->get('from_date');
		$to_date = $this->input->get('to_date');
		$order_number = $this->input->get('order_number');
		$order_availability = $this->input->get('order_availability');
		$order_status = $this->input->get('order_status');
		$order_outlet = $this->input->get('order_outlet');


		$company_id = decode_value($this->input->get('company_id'));
		$where = array("$this->primary_key !=" => '', $this->company_id => $company_id);
		if (!empty($order_number)) {
			$where = array_merge($where, array('order_local_no' => $order_number));
		}
		if (!empty($order_availability)) {
			$where = array_merge($where, array('order_availability_id' => $order_availability));
		}
		if (!empty($order_status)) {
			$where = array_merge($where, array('order_status' => $order_status));
		}
		if (!empty($order_outlet)) {
			$where = array_merge($where, array('outlet_id' => $order_outlet));
		}


		if (!empty($from_date) && !empty($to_date)) {
			$where = array_merge($where, array('order_date>=' => $from_date, 'order_date<=' => $to_date));
		} else if (!empty($from_date)) {
			$where = array_merge($where, array('order_date' => $from_date));
		}

		$orderByArr = array('order_primary_id' => 'DESC');
		if ($this->get('orderBy') == 'status') {
			$orderByArr = array('pos_order_status.status_order' => 'ASC', 'order_primary_id' => 'DESC');
		}

		if (!empty($sort_order_date)) {
			$orderByArr = array('order_date' => $sort_order_date);
		}

		$j = 0;
		$join[$j]['select'] = "GROUP_CONCAT(outlet_id) AS outlet_id";
		$join[$j]['table'] = $this->order_outlet;
		$join[$j]['condition'] = "order_primary_id = outlet_order_primary_id";
		$join[$j]['type'] = "INNER";
		$j++;

		$join[$j]['select'] = "pos_order_status.status_name";
		$join[$j]['table'] = $this->order_status;
		$join[$j]['condition'] = "order_status = pos_order_status.status_id";
		$join[$j]['type'] = "LEFT";
		$j++;

		$join[$j]['select'] = "order_customer_id,CONCAT_WS(' ',order_customer_fname,order_customer_lname) AS customer_name,order_customer_email,order_customer_mobile_no,
		order_customer_unit_no1,order_customer_unit_no2,order_customer_address_line1,order_customer_address_line2,order_customer_city,
		order_customer_state,order_customer_country,order_customer_postal_code,order_customer_created_on";
		$join[$j]['table'] = $this->orders_customer_details;
		$join[$j]['condition'] = "order_customer_order_primary_id = order_primary_id";
		$join[$j]['type'] = "LEFT";
		$j++;

		$join[$j]['select'] = "order_method_name";
		$join[$j]['table'] = "pos_order_methods";
		$join[$j]['condition'] = "order_payment_mode = order_method_id";
		$join[$j]['type'] = "LEFT";

		$result = $this->Mydb->get_all_records($select_array, $this->table, $where, $limit, $offset, $orderByArr, $like, array('order_primary_id'), $join);
		$outlet = $this->Mydb->get_all_records('outlet_id, outlet_name', $this->outlet_management, array('outlet_company_id' => $company_id));
		$outletList = array();
		if (!empty($outlet)) {
			$outlet_id = array_column($outlet, 'outlet_id');
			$outlet_name = array_column($outlet, 'outlet_name');
			$outletList = array_combine($outlet_id, $outlet_name);
		}
		if (!empty($result)) {
			$exportarray =  array();
			$this->load->helper('export');
			$reportTitle = array(
				'Order Number',
				'Delivery Date',
				'Placed On',
				'Outlet Name',
				'Customer Name',
				'Order Type',
				'Status',
				'Sub Total',
				'Delivery Chareg',
				'Additional Delivery',
				'Tax Percentage',
				'Tax Type',
				'Tax Amount',
				'Service Charges',
				'Discount',
				'Special Discount',
				'Grand Amount',
				'Payment Mode',
				'Rider',
			);
			$exportarray[]  = $reportTitle;
			foreach ($result as $val) {
				$orderTime = date('d-m-Y', strtotime($val['order_date'])) . " ";
				if (!empty($val['order_pickup_time_slot_from']) && !empty($val['order_pickup_time_slot_to'])) {
					$orderTime .= date('h:i A', strtotime($val['order_pickup_time_slot_from'])) . ' - ' . date('h:i A', strtotime($val['order_pickup_time_slot_to']));
				} else {
					$orderTime .= date('h:i A', strtotime($val['order_date']));
				}
				$displyOutelt = "";
				if (!empty($val['outlet_id'])) {
					$splitOutelt = explode(',', $val['outlet_id']);
					foreach ($splitOutelt as $value) {
						$displyOutelt .= (!empty($displyOutelt)) ? ',' : '';
						$displyOutelt .= (!empty($outletList[$value])) ? $outletList[$value] : '';
					}
				}

				$exportarray[] = array(
					$val['order_local_no'],
					$orderTime,
					$val['order_created_on'],
					$displyOutelt,
					$val['customer_name'],
					$val['order_availability_name'],
					$val['status_name'],
					$val['order_sub_total'],
					$val['order_delivery_charge'],
					$val['order_additional_delivery'],
					$val['order_tax_charge'],
					($val['order_tax_calculate_amount_inclusive'] > 0) ? 'Inclusive' : 'Exclusive',
					$val['order_tax_calculate_amount'],
					$val['order_service_charge_amount'],
					$val['order_discount_amount'],
					$val['order_special_discount_amount'],
					$val['order_total_amount'],
					$val['order_method_name'],
					'N/A',
				);
			}

			array_to_xls($exportarray, 'reports-' . date("m-d-Y-h-i-A") . '.xls');
		} else {
			$this->set_response(array('status' => 'error', 'message' => get_label('no_records_found')), something_wrong());
		}
	}
} /* end of files */
