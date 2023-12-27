<?php

/**************************
Project Name	: White Label
Created on		: 29 Aug, 2023
Last Modified 	: 05 Sep, 2023
Description		: Orders details

 ***************************/
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Orders extends REST_Controller
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
		$this->promotion_history = "promotion_history";
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
						'order_total_amount',
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
					$limit = $offset = $like = '';
					$get_limit = $this->input->get('limit');
					$post_offset = (int) $this->input->get('offset');
					$from_date = $this->input->get('from_date');
					$to_date = $this->input->get('to_date');
					$order_type = $this->input->get('order_type');
					$searchoption = $this->input->get('searchoption');
					$searchkeyword = $this->input->get('searchkeyword');
					$orderstatus = $this->input->get('orderstatus');
					$dateoption = $this->input->get('dateoption');
					$dateoption = (!empty($dateoption)) ? $dateoption : '1';
					$start_date = $this->input->get('start_date');
					$end_date = $this->input->get('end_date');
					if ((int) $get_limit != 0) {
						$limit = (int) $get_limit;
					}
					$post_offset = ($post_offset > 1) ? $post_offset - 1 : 0;
					$offset = ($post_offset > 0 ? $post_offset * $limit : 0);

					$company_id = decode_value($this->input->get('company_id'));
					$where = array("$this->primary_key !=" => '', $this->company_id => $company_id);

					if (!empty($dateoption) && (!empty($start_date) || !empty($end_date))) {
						if (!empty($start_date) && !empty($end_date)) {
							$where = array_merge($where, array('order_date>=' => $start_date, 'order_date<=' => $end_date));
						} else if (!empty($start_date)) {
							$where = array_merge($where, array('order_date' => $start_date));
						} else if (!empty($end_date)) {
							$where = array_merge($where, array('order_date' => $end_date));
						}
					} else {
						if (!empty($order_type)) {
							if ($order_type === 'c') {
								$where = array_merge($where, array('order_date>=' => date('Y-m-d') . ' 00:00:00', 'order_date<=' => date('Y-m-d') . ' 23:59:59'));
							} else if ($order_type === 'a') {
								$where = array_merge($where, array('order_date>=' => date('Y-m-d', strtotime(date('Y-m-d') . ' +1 day')) . ' 00:00:00'));
							}
						} else {
							if (!empty($from_date) && !empty($to_date)) {
								$where = array_merge($where, array('order_date>=' => $from_date, 'order_date<=' => $to_date));
							} else if (!empty($from_date)) {
								$where = array_merge($where, array('order_date' => $from_date));
							} else {
								$where = array_merge($where, array('order_date' => date('Y-m-d')));
							}
						}
					}
					if (!empty($searchoption) && !empty($searchkeyword)) {
						if ($searchoption == 'order_number') {
							$where = array_merge($where, array('order_local_no' => $searchkeyword));
						} else if ($searchoption == 'customer_name') {
							$where = array_merge($where, array("(order_customer_fname LIKE '%" . $searchkeyword . "%' OR order_customer_lname LIKE '%" . $searchkeyword . "%')" => NULL));
						} else if ($searchoption == 'customer_email') {
							$where = array_merge($where, array("order_customer_email LIKE '%" . $searchkeyword . "%'" => NULL));
						} else if ($searchoption == 'customer_phone') {
							$where = array_merge($where, array("order_customer_mobile_no" => $searchkeyword));
						}
					}
					if (!empty($orderstatus)) {
						$where = array_merge($where, array("order_status" => $orderstatus));
					}
					$orderByArr = array('(pos_orders.order_status = "4")' => '', 'order_primary_id' => 'DESC');
					if ($this->get('orderBy') == 'status') {
						$orderByArr = array('pos_order_status.status_order' => 'ASC', 'order_primary_id' => 'DESC');
					}
					if (!empty($userDetails)) {
						if ($userDetails['company_user_type'] == 'SubAdmin') {
							$where = array_merge($where, array("outlet_id" => $userDetails['company_user_permission_outlet']));
						}
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

	public function getactivity_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$logID = decode_value($this->input->get('logID'));
				$userDetails = getUserDetails($logID);
				if (!empty($userDetails)) {
					$company_id = decode_value($this->input->get('company_id'));
					$where = array($this->company_id => $company_id, 'order_status' => 1, 'order_date>=' => date('Y-m-d') . ' 00:00:00', 'order_date<=' => date('Y-m-d') . ' 23:59:59');
					$join = array();
					if (!empty($userDetails)) {
						if ($userDetails['company_user_type'] == 'SubAdmin') {
							$where = array_merge($where, array("outlet_id" => $userDetails['company_user_permission_outlet']));
							$groupBy = array('outlet_id');
							$j = 0;
							$join[$j]['select'] = "";
							$join[$j]['table'] = $this->order_outlet;
							$join[$j]['condition'] = "order_primary_id = outlet_order_primary_id";
							$join[$j]['type'] = "INNER";
							$j++;
						}
					}

					$todayOrder = $this->Mydb->get_all_records('COUNT(order_primary_id) AS totalOrder', $this->table, $where, '', '', '', '', $groupBy, $join);
					$featureDate = date('Y-m-d', strtotime('+1 day', time()));

					$Fwhere = array($this->company_id => $company_id, 'order_status' => 1, 'order_date>=' => $featureDate . ' 00:00:00');
					if (!empty($userDetails)) {
						if ($userDetails['company_user_type'] == 'SubAdmin') {
							$Fwhere = array_merge($Fwhere, array("outlet_id" => $userDetails['company_user_permission_outlet']));
							$groupBy = array('outlet_id');
						}
					}

					$featureOrder = $this->Mydb->get_all_records('COUNT(order_primary_id) AS totalOrder', $this->table, $Fwhere, '', '', '', '', $groupBy, $join);

					$return_array = array('status' => "ok", 'message' => 'success', 'todayOrders' => (!empty($todayOrder)) ? $todayOrder[0]['totalOrder'] : 0, 'featureOrder' => (!empty($featureOrder)) ? $featureOrder[0]['totalOrder'] : 0);
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



	public function details_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$id = decode_value($this->input->get('detail_id'));
				$company_id = decode_value($this->input->get('company_id'));
				$where = array($this->primary_key => $id, $this->company_id => $company_id);

				$join = array();
				$j = 0;
				$join[$j]['select'] = "pos_order_status.status_name";
				$join[$j]['table'] = $this->order_status;
				$join[$j]['condition'] = "order_status = pos_order_status.status_id";
				$join[$j]['type'] = "LEFT";
				$j++;

				$join[$j]['select'] = "order_method_name";
				$join[$j]['table'] = "pos_order_methods";
				$join[$j]['condition'] = "order_payment_mode = order_method_id";
				$join[$j]['type'] = "LEFT";

				$result = $this->Mydb->get_all_records('pos_o.*', $this->table . ' AS pos_o', $where, null, null, null, null, null, $join);
				if (!empty($result)) {
					$result = $result[0];
					$outlet = $this->Mydb->get_all_records('outlet_id, outlet_name', $this->outlet_management, array('outlet_company_id' => $company_id));
					$outlet_id = array_column($outlet, 'outlet_id');
					$outlet_name = array_column($outlet, 'outlet_name');
					$clientOutlet = array_combine($outlet_id, $outlet_name);

					$customer = $this->Mydb->get_record("CONCAT_WS(' ',order_customer_fname,order_customer_lname) AS customer_name,order_customer_email, order_customer_mobile_no, order_customer_unit_no1, order_customer_unit_no2, order_customer_address_line1, order_customer_address_line2, order_customer_city, order_customer_state,order_customer_country, order_customer_postal_code, order_customer_billing_unit_no1, order_customer_billing_unit_no2, order_customer_billing_address_line1, order_customer_billing_address_line2, order_customer_billing_postal_code, order_customer_remarks AS addressRemarks", $this->orders_customer_details, array('order_customer_order_primary_id' => $result['order_primary_id']));
					$result['customer'] = $customer;

					$join = array();
					$j = 0;
					$join[$j]['select'] = "status_name";
					$join[$j]['table'] = $this->order_status;
					$join[$j]['condition'] = "outlet_order_status = status_id";
					$join[$j]['type'] = "LEFT";

					$order_outlet = $this->Mydb->get_all_records('outlet_id, outlet_sub_total_amount, outlet_tax, outlet_tax_amount, outlet_grand_total_amount, outlet_discount_applied, outlet_order_status, outlet_order_remarks', $this->order_outlet, array('outlet_order_primary_id' => $result['order_primary_id']), null, null, null, null, null, $join);
					$outletID = array_column($order_outlet, 'outlet_id');
					$orderOutlet = array_combine($outletID, $order_outlet);

					$itemWhere = array('item_order_primary_id' => $result['order_primary_id']);

					$selectItemValues = array(
						"item_outlet_id",
						'item_id',
						'item_product_id',
						'item_voucher_id',
						'item_name',
						'item_sku',
						'item_qty',
						'item_unit_price',
						'item_total_amount',
						'item_specification',
					);
					$orderItem = $this->Mydb->get_all_records($selectItemValues, $this->order_items, $itemWhere);

					$combomenuItem = $this->Mydb->get_all_records('menu_item_id, menu_menu_component_id, menu_menu_component_name, menu_product_name, menu_product_sku, menu_product_qty, menu_product_price, menu_custom_logo, menu_custom_text,menu_menu_component_min_max_appy, menu_kitchen_status, menu_product_extra_qty, menu_product_extra_price', $this->component_set, array('menu_order_primary_id' => $result['order_primary_id']));
					$finalcombomenuItem = [];
					if (!empty($combomenuItem)) {
						foreach ($combomenuItem as $val) {
							$finalcombomenuItem[$val['menu_item_id']][$val['menu_menu_component_id']]['component_id'] = $val['menu_menu_component_id'];
							$finalcombomenuItem[$val['menu_item_id']][$val['menu_menu_component_id']]['component_name'] = $val['menu_menu_component_name'];
							$finalcombomenuItem[$val['menu_item_id']][$val['menu_menu_component_id']]['component_item'][] = $val;
						}
					}


					$finalOrderItem = [];
					if (!empty($orderItem)) {
						foreach ($orderItem as $val) {
							$val['combo_set'] = (!empty($finalcombomenuItem[$val['item_id']])) ? array_values($finalcombomenuItem[$val['item_id']]) : [];
							$finalOrderItem[$val['item_outlet_id']]['outlet_order_details'] =  (!empty($orderOutlet[$val['item_outlet_id']])) ? $orderOutlet[$val['item_outlet_id']] : "";
							$finalOrderItem[$val['item_outlet_id']]['outlet_name'] =  (!empty($clientOutlet[$val['item_outlet_id']])) ? $clientOutlet[$val['item_outlet_id']] : "";
							$finalOrderItem[$val['item_outlet_id']]['outlet_id'] =  $val['item_outlet_id'];
							$finalOrderItem[$val['item_outlet_id']]['outlet_item'][] = $val;
						}
					}
					$result['order_item'] = (!empty($finalOrderItem)) ? array_values($finalOrderItem) : [];
					$result['discount'] = $this->Mydb->get_all_records('promotion_history_promocode AS promoCode, promotion_history_applied_amt AS promoAmount', $this->promotion_history, array('promotion_history_order_primary_id' => $result['order_primary_id']));

					$return_array = array('status' => "ok", 'message' => 'success', 'result' => $result);
					$this->set_response($return_array, success_response());
				} else {
					$this->set_response(array('status' => 'error', 'message' => get_label('no_records_found')), something_wrong());
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

	public function changeOrderStatus_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$company_id = decode_value($this->input->post('company_id'));
				$company_admin_id = decode_value($this->input->post('company_admin_id'));
				$company = client_validation($company_id); /* validate company */
				$unique_id = $company['company_unquie_id'];

				$order_id = decode_value($this->input->post('order_id'));
				$join = array();
				$i = 0;
				$join[$i]['select'] = "status_name";
				$join[$i]['table'] = "pos_order_status";
				$join[$i]['condition'] = "order_status = status_id AND status_enabled='A' ";
				$join[$i]['type'] = "RIGHT";
				$i++;

				$join[$i]['select'] = "pos_outlet_management.outlet_name";
				$join[$i]['table'] = "pos_outlet_management";
				$join[$i]['condition'] = "order_outlet_id = pos_outlet_management.outlet_id";
				$join[$i]['type'] = "LEFT";

				/* order table values */
				$select_values = array(
					'order_primary_id',
					'order_id',
					'order_status',
					'order_availability_id',
					'order_outlet_id',
					'order_local_no',
					'order_tracking_remarks'
				);

				/* get order list from query */
				$order_list = $this->Mydb->get_all_records($select_values, $this->table, array(
					'order_id' => $order_id
				), 1, '', array(
					'order_primary_id' => 'DESC'
				), '', array(
					'order_primary_id'
				), $join, '');
				if (!empty($order_list)) {
					$availability_id = $order_list[0]['order_availability_id'];

					if ($order_list[0]['order_status'] != 5) {

						$order_status = trim($this->input->post('order_status'));
						$remarks = addslashes(trim($this->input->post('order_remarks')));
						$order_tracking_remarks = addslashes(trim($this->input->post('order_tracking_remarks')));

						$order_text = $this->Mydb->get_record('status_name', 'order_status', array(
							'status_id' => $order_status
						));

						$statusList = array(1, 2, 3, 4, 5);
						if (in_array($order_status, $statusList)) {
							$order_primary_id = $order_list[0]['order_primary_id'];

							/*-------------------Order tracking details---------------------*/
							$existing_order_tracking_remarks = $order_list[0]['order_tracking_remarks'];
							if ($existing_order_tracking_remarks == '') {
								$existing_order_tracking_remarks = array();
							} else {
								$existing_order_tracking_remarks = json_decode($existing_order_tracking_remarks, true);
							}

							if ($order_tracking_remarks != '') {

								$existing_order_tracking_remarks[$order_status] = $order_tracking_remarks;
							}

							if (!empty($existing_order_tracking_remarks)) {
								$existing_order_tracking_remarks = json_encode($existing_order_tracking_remarks);
							} else {
								$existing_order_tracking_remarks = null;
							}

							/*-------------------Order tracking end---------------------*/

							if ($order_status == 5) {

								/*------------ revit quantity to product stock and add log -----------*/
								if (!empty($company['enable_stock']) && $company['enable_stock'] == "1") {
									$join = array();
									$join[0]['select'] = "item_id,item_product_id,item_qty,item_unit_price,item_total_amount";
									$join[0]['table'] = "order_items";
									$join[0]['condition'] = "item_order_primary_id = order_primary_id";
									$join[0]['type'] = "INNER";
									$products = $this->Mydb->get_all_records('order_primary_id,order_outlet_id,order_company_unique_id', 'orders', array('order_primary_id' => $order_primary_id), '', '', '', '', '', $join);

									if (!empty($products)) {
										foreach ($products as $pro_items) {
											$order_itm_qty = ($pro_items['item_qty'] != '') ? $pro_items['item_qty'] : 0;
											$item_product_id = $pro_items['item_product_id'];
											$pro_order_id = $pro_items['order_primary_id'];

											rest_product_stock_log($company, $item_product_id, $order_itm_qty, $pro_order_id, '', 'C');
										}
									}
								}

								/*------------------------ end product stock ------------------------*/
								$this->Mydb->update($this->table, array(
									'order_id' => $order_id
								), array(
									'order_status' => $order_status,
									'order_remarks' => $remarks,
									'order_cancel_remark' => $remarks,
									'order_tracking_remarks' => $existing_order_tracking_remarks,
									'order_cancel_date' => current_date(),
									'order_cancel_source' => 'Business',
									'order_cancel_by' => $company_admin_id
								));
								$customer_detail = $this->Mydb->get_record(array(
									'order_customer_fname',
									'order_customer_id',
									'order_customer_lname',
									'order_customer_email'
								), 'pos_orders_customer_details', array(
									'order_customer_order_id' => $order_id
								));

								/* revert points option */
								$this->load->helper("promotionrevert");
								removeRewardPoints($order_primary_id, $customer_detail['order_customer_id']);


								//Comment
								/* $first_name = (!empty($customer_detail['order_customer_fname'])) ? $customer_detail['order_customer_fname'] : "";
								$last_name = (!empty($customer_detail['order_customer_lname'])) ? $customer_detail['order_customer_lname'] : "";
								$customer_name = $first_name . " " . $last_name;

								$company_name = $this->session->userdata('camp_company_name');

								$company_array = $this->Mydb->get_record(array(
									'company_id',
									'client_name',
									'client_site_url'
								), 'pos_clients', array(
									'client_name' => $company_name
								));

								//echo $this->db->last_query(); exit;

								$base_url = ($company_array['client_site_url'] != '') ? trim($company_array['client_site_url'], '/') : base_url();

								$emai_logo = $base_url . "/media/email-logo/email-logo.jpg";

								$this->load->library('myemail');
								$check_arr = array(
									'[NAME]',
									'[ORDER_Id]',
									'[REMARKS]',
									'[OUTLETNAME]',
									'[LOCAL_ORDER_NO]',
									'[LOGOURL]'
								);
								$replace_arr = array(
									ucfirst(stripslashes($customer_name)),
									$order_id,
									stripslashes($remarks),
									$order_list[0]['outlet_name'],
									$order_list[0]['order_local_no'],
									$emai_logo
								);

								$this->myemail->send_client_mail($customer_detail['order_customer_email'], get_emailtemplate($unique_id, 'order_cancel_template'), $check_arr, $replace_arr, $company_id, $unique_id);

								$this->myemail->send_client_mail('', get_emailtemplate($unique_id, 'order_cancel_template'), $check_arr, $replace_arr, $company_id, $unique_id); */



								/*  if order canceled revert loyalty points  and promotion */
							} elseif ($order_status == 2 && $availability_id == DELIVERY_ID) {

								$rider_id = post_value('rider_id');
								if (!empty($rider_id)) {
									/* remove previous rider entry */
									$this->Mydb->delete('pos_rider_assigned_orders', array(
										'assigned_order_company_id' => $company_id,
										'assigned_order_company_unquie_id' => $unique_id,
										'assigned_order_order_primary_id' => $order_list[0]['order_primary_id'],
										'assigned_order_order_id' => $order_list[0]['order_id']
									));

									/* Insert new rider entry */
									$this->Mydb->insert('pos_rider_assigned_orders', array(
										'assigned_order_company_id' => $company_id,
										'assigned_order_company_unquie_id' => $unique_id,
										'assigned_order_order_primary_id' => $order_list[0]['order_primary_id'],
										'assigned_order_order_id' => $order_list[0]['order_id'],
										'assigned_order_outlet_id' => $order_list[0]['order_outlet_id'],
										'assigned_order_rider_id' => $rider_id,
										'assigned_order_created_on' => current_date(),
										'assigned_order_created_by' => $company_admin_id,
										'assigned_order_created_ip' => get_ip()
									));

									$this->Mydb->update($this->table, array(
										'order_id' => $order_id
									), array(
										'order_status' => $order_status,
										'order_tracking_remarks' => $existing_order_tracking_remarks,
										'order_updated_on' => current_date(),
										'order_updated_by' => $company_admin_id,
										'order_updated_ip' => get_ip()
									));
								} else {
									$response['msg'] = get_label('rider_required');
									$response['status'] = 'error';
									echo json_encode($response);
									exit();
								}
							} else {

								/* reward points*/
								if ($order_status == '4') {

									loyality_change_status($order_primary_id);
									$this->AddRewardPoints($order_list[0]['order_primary_id']);

									/* Membership upadeted Georges */
									$voucher_auto_assign = $this->Mydb->get_record('setting_key,setting_value', 'company_settings', array(
										'company_id' => $company_id, 'setting_key' => 'enable_voucher_auto_assign', 'setting_value' => '1'
									));

									if (empty($voucher_auto_assign)) {
										$this->voucher_order_email($order_list[0]['order_primary_id'], $order_list[0]['order_id'], $company);  //sending voucher details email id
									}
								}
								$this->Mydb->update($this->table, array(
									'order_id' => $order_id
								), array(
									'order_status' => $order_status,
									'order_tracking_remarks' => $existing_order_tracking_remarks,
									'order_updated_on' => current_date(),
									'order_updated_by' => $company_admin_id,
									'order_updated_ip' => get_ip()
								));
							}

							/* insert status log track */
							$log_id = $this->insert_status_log($order_list[0]['order_primary_id'], $order_id, $order_status, $unique_id, $company_id);
							/* find previous staus and update end date */
							$this->update_status_log($log_id, $order_list[0]['order_status'], $order_list[0]['order_primary_id'], $unique_id);
							/* reset pending order count */


							/*Change status name*/
							$ordertext = '';

							if ($availability_id == PICKUP_ID) {
								if ($order_status == 2) { /*if delivering meand change status as Ready to pickup*/
									$ordertext = 'Ready to pickup';
								}
							} else if ($availability_id == DINEIN_ID && $order_status == 2) {
								$ordertext = 'Ready to Eat';
							} else {
								$ordertext = $order_text['status_name'];
							}

							/*Push notify when complete the order*/
							if ($order_status != '1') {
								/****************Push notification ande activities*****************/

								$order_no =  $order_list[0]['order_local_no'];
								$order_primary_id =  $order_list[0]['order_primary_id'];

								$cust_select_array = array('order_customer_id', 'CONCAT(order_customer_fname, "", order_customer_lname) AS customer_name', 'order_customer_email', 'order_customer_mobile_no');

								$customer_res = $this->Mydb->get_record($cust_select_array, 'pos_orders_customer_details', array('order_customer_order_primary_id' => $order_primary_id));
								//Comment
								/* $push_info_arr = array();
								$push_from = 'order_status';

								$push_info_arr['order_status'] = $order_status;
								$push_info_arr['order_no'] = $order_no;
								$push_info_arr['order_primary_id'] = $order_primary_id;
								$push_info_arr['order_text'] = $ordertext;
								$push_info_arr['unique_id'] = $unique_id;

								push_activities($customer_res['order_customer_id'], $push_from, $push_info_arr); */

								/****************Push notification ande activities*****************/

								/***Email Notification for status***/
								//Comment
								/* if ($availability_id == DELIVERY_ID) {
									$deliveryStatusArr = array('1' => 'Order received', '3' => 'Order accepted', '2' => 'Food on the way', '4' => 'Delivered', '5' => 'Canceled');
									$ordertext = $deliveryStatusArr[$order_status];
								} else if ($availability_id == PICKUP_ID) {
									$takewayStatusArr = array('1' => 'Order received', '3' => 'Order accepted', '2' => 'Ready for pickup', '4' => 'Completed', '5' => 'Canceled');
									$ordertext = $takewayStatusArr[$order_status];
								}

								$get_email_id = get_emailtemplate($unique_id, 'order_status_changed_email');

								if ($get_email_id != '') {

									$this->load->library('myemail');
									$check_arr = array('[NAME]', '[ORDER_NO]', '[ORDER_STATUS]');
									$replace_arr = array(ucfirst(stripslashes($customer_res['customer_name'])), $order_no, $ordertext);
									
									$this->myemail->send_client_mail($customer_res['order_customer_email'], $get_email_id, $check_arr, $replace_arr, $company_id, $unique_id);
								} */
							}

							$order_primary_id =  $order_list[0]['order_primary_id'];
							$email_template_id = get_emailtemplate($unique_id, 'orderstatus');
							if (!empty($email_template_id)) {
								$cust_select_array = array('order_customer_id', 'CONCAT(order_customer_fname, "", order_customer_lname) AS customer_name', 'order_customer_email', 'order_customer_mobile_no');

								$customer_res = $this->Mydb->get_record($cust_select_array, 'pos_orders_customer_details', array('order_customer_order_primary_id' => $order_primary_id));
								$check_arr = array('[NAME]', '[ORDER_NO]', '[ORDER_STATUS]');
								$replace_arr = array(ucfirst(stripslashes($customer_res['customer_name'])), $order_list[0]['order_local_no'], $ordertext);

								$this->load->library('myemail');

								$this->myemail->send_client_mail($company, 'praba9717@gmail.com', $email_template_id, $check_arr,  $replace_arr);
							}

							$response['status'] = 'success';
							$response['order_status'] = $order_status;
							$response['order_status_name'] = $ordertext;
							$response['msg'] = sprintf($this->lang->line('success_message_order_status'), $this->label);
							echo json_encode($response);
							exit();
						} else {

							$response['msg'] = sprintf($this->lang->line('something_wrong'), $this->label);
							$response['status'] = 'error';
							echo json_encode($response);
							exit();
						}
					} else { /*if cancelled already try to change other status or again cancel from Other tab*/

						$response['msg'] = sprintf($this->lang->line('something_wrong'), $this->label);
						$response['status'] = 'error';
						echo json_encode($response);
						exit();
					}
				} else {
					$response['msg'] = $this->lang->line('invalid_order_id');
					$response['status'] = 'error';
					echo json_encode($response);
					exit();
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

	public function updateorderdate_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$company_id = decode_value($this->input->post('company_id'));
				$company = client_validation($company_id); /* validate company */
				$unique_id = $company['company_unquie_id'];

				$order_id_form = decode_value(post_value('order_id'));
				$order_detail = $this->Mydb->get_record(array(
					'order_primary_id',
					'order_id',
					'order_outlet_id',
					'order_date',
				), 'pos_orders', array(
					'order_id' => $order_id_form,
					'order_company_id' => $company_id,
					'order_company_unique_id' => $unique_id
				));
				if (!empty($order_detail)) {

					$new_order_date = date("Y-m-d H:i:s", strtotime(post_value('pickup_timing')));
					$old_slot_from = (post_value('old_slot_from')) ? date("H:i:s", strtotime(post_value('old_slot_from'))) : '';
					$slot_from = (post_value('slot_from')) ? date("H:i:s", strtotime(post_value('slot_from'))) : '';
					$old_slot_to = (post_value('old_slot_to')) ? date("H:i:s", strtotime(post_value('old_slot_to'))) : '';
					$slot_to = (post_value('slot_to')) ? date("H:i:s", strtotime(post_value('slot_to'))) : '';

					/* Insert new date entry */
					$this->Mydb->insert($this->date_history, array(
						'ohd_order_id' => $order_id_form,
						'ohd_order_company_unique_id' => $unique_id,
						'ohd_from_date' => $order_detail['order_date'],
						'ohd_to_date' => $new_order_date,
						'ohd_old_from_slot' => $old_slot_from,
						'ohd_new_from_slot' => $slot_from,
						'ohd_old_to_slot' => $old_slot_to,
						'ohd_new_to_slot' => $slot_to,
						'ohd_remark' => stripslashes(post_value('change_date_remark')),
						'ohd_created_on' => current_date(),
					));


					$this->Mydb->update($this->table, array(
						'order_id' => $order_id_form
					), array(
						'order_date' => $new_order_date,
						'order_pickup_time_slot_from' => $slot_from,
						'order_pickup_time_slot_to' => $slot_to,
						'order_date_change_remarks' => stripslashes(post_value('change_date_remark')),
					));
					$history = $this->loadOrderHistory($order_id_form, $unique_id);
					$result['status'] = 'ok';
					$result['msg'] = 'Order Date & Time Updated Success';
					$result['history'] = $history;
				} else {
					$result['status'] = 'error';
					$result['msg'] = get_label('something_wrong');
				}
				echo json_encode($result);
				exit();
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

	public function datehistory_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$company_id = decode_value($this->input->get('company_id'));
				$company = client_validation($company_id); /* validate company */
				$unique_id = $company['company_unquie_id'];

				$order_id = decode_value($this->input->get('order_id'));
				$history = $this->loadOrderHistory($order_id, $unique_id);
				$return_array = array('status' => "ok", 'message' => 'success', 'orderdatehistory' => $history);
				$this->set_response($return_array, success_response());
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

	public function loadstatus_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$statusList = $this->Mydb->get_all_records('status_id AS value, status_name AS label', $this->order_status, array('status_enabled' => 'A'));
				$return_array = array('status' => "ok", 'message' => 'success', 'result' => $statusList);
				$this->set_response($return_array, success_response());
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
	private function loadOrderHistory($orderID, $unique_id)
	{
		$history = $this->Mydb->get_all_records('ohd_from_date, ohd_to_date, ohd_old_from_slot, ohd_new_from_slot, ohd_old_to_slot, ohd_new_to_slot, ohd_remark', $this->date_history, array('ohd_order_company_unique_id' => $unique_id, 'ohd_order_id' => $orderID));
		return $history;
	}
	/* this function used  add reward points to cutomer */
	private function AddRewardPoints($order_primary_id)
	{
		$this->load->helper('promotion');
		$order_primary_id = 	$order_primary_id;

		/* join tables - Status table */
		$join[0]['select'] = "item_order_primary_id";
		$join[0]['table'] = "order_items";
		$join[0]['condition'] = "item_order_primary_id = order_primary_id";
		$join[0]['type'] = "LEFT";

		$join[1]['select'] = "product_reward_point";
		$join[1]['table'] = "products";
		$join[1]['condition'] = "product_id = item_product_id";
		$join[1]['type'] = "LEFT";

		$join[2]['select'] = "order_customer_id,order_company_unique_id,order_id";
		$join[2]['table'] = "orders_customer_details";
		$join[2]['condition'] = "order_customer_order_primary_id = order_primary_id";
		$join[2]['type'] = "INNER";

		$products = $this->Mydb->get_all_records(
			'SUM(item_qty*product_reward_point) as reward_points',
			'orders',
			array('order_primary_id' => $order_primary_id),
			'',
			'',
			'',
			'',
			'',
			$join
		);
		if (!empty($products)) {
			$redeem_point = $products[0]['reward_points'];
			$unique_id       = $products[0]['order_company_unique_id'];
			$customer_id  = $products[0]['order_customer_id'];
			$order_primary_id = $products[0]['item_order_primary_id'];
			$order_id  = $products[0]['order_id'];
			insert_redeem_point($redeem_point, $unique_id, $customer_id, $order_primary_id, $order_id);
		}
	}
	public function voucher_order_email($order_primary_id, $order_id, $company)
	{
		if (!empty($order_primary_id) && !empty($order_id)) {
			//get customer details
			$customer_detail = $this->Mydb->get_record(array('order_customer_fname', 'order_customer_id', 'order_customer_lname', 'order_customer_email'), 'pos_orders_customer_details', array('order_customer_order_id' => $order_id));

			$select_values = array('pos_order_item_voucher.*');
			$where = array('is_used' => '0', 'item_order_primary_id' => $order_primary_id, "DATE_FORMAT(expiry_date,'%Y-%m-%d') >= " => date("Y-m-d"));
			$join = array();
			$join[0]['select'] = "item_name,item_qty";
			$join[0]['table'] = "order_items";
			$join[0]['condition'] = "order_items.item_id = order_item_voucher.order_item_id";
			$join[0]['type'] = "LEFT";

			$voucher_gift_res = $this->Mydb->get_all_records($select_values, 'order_item_voucher', $where,  '', '', '', '', '', $join, '');


			$sign_in_url = $$company['client_site_url'] . '#/sign-in';
			if (!empty($voucher_gift_res)) {
				foreach ($voucher_gift_res as $voucher_gifts) {
					if ($voucher_gifts['order_item_voucher_email'] != "") {
						$this->load->library('myemail');

						$check_arr = array('[NAME]', '[VOUCHERDETAILS]', '[SIGNUP_LINK]', '[PROCEDURE]', '[GIFTERDETAILS]');
						$voucher_details = '<p><b>Voucher Name: </b>' . $voucher_gifts['item_name'] . '</p>';
						$voucher_details .= '<p><b>Quantity: </b>' . $voucher_gifts['item_qty'] . '</p>';
						if ($voucher_gifts['customer_email'] != $voucher_gifts['order_item_voucher_email']) {
							$voucher_details .= '<p><b>Message: </b>' . $voucher_gifts['order_item_voucher_message'] . '</p>';
						}
						$voucher_details .= '<p><b>Expiry Date: </b>' . get_date_formart($voucher_gifts['expiry_date'], 'F d, Y') . '</p>';
						/*for cash voucher*/
						if ($voucher_gifts['voucher_points'] != 0 && $voucher_gifts['voucher_points'] != "") {
							$voucher_details .= '<p><b>Type: </b>Cash Voucher</p>';
							$voucher_details .= '<p><b>Points: </b>' . $voucher_gifts['voucher_points'] . '</p>';

							$procedure = '<table cellpadding="0" cellspacing="0">
							<tr> 
							<td height="20" align="left" valign="bottom"> 
							<table width="250" border="1" align="center" cellpadding="0" cellspacing="0" style="border-collapse:collapse"> <tbody><tr> <td height="20" align="center" bgcolor="#098612"> <font face="Zurich BT, Zurich Cn BT, Arial" color="#ffffff" style="font-size:15px"><strong>How to redeem the voucher</strong></font> </td> </tr> </tbody>
							</table> 
							</td> 
							</tr>
							<tr> <td align="center" valign="top"> 
							<table width="400" border="1" cellspacing="0" cellpadding="0" style="border-collapse:collapse"> <tbody><tr> <td height="20" align="left" style="padding:10px"> <table width="380" border="0" align="center" cellpadding="0" cellspacing="0"> <tbody><tr> <td width="20" height="20" align="center"> <font face="Zurich BT, Zurich Cn BT, Arial" color="#000000" style="font-size:15px">•</font> </td> <td width="360" height="20" align="left"> <font face="Zurich BT, Zurich Cn BT, Arial" color="#000000" style="font-size:15px">Signup for an account in <strong style="color:#003366">' . $sign_in_url . '</strong></font> </td> </tr>
							<tr> <td height="20" align="center"> <font face="Zurich BT, Zurich Cn BT, Arial" color="#000000" style="font-size:15px">•</font> </td> <td height="20" align="left"> <font face="Zurich BT, Zurich Cn BT, Arial" color="#000000" style="font-size:15px">Click on <strong style="color:#003366">My e-Vouchers</strong> under My Account</font> </td> </tr> <tr> <td height="20" align="center"> <font face="Zurich BT, Zurich Cn BT, Arial" color="#000000" style="font-size:15px">•</font> </td> <td height="20" align="left"> <font face="Zurich BT, Zurich
							Cn BT, Arial" color="#000000" style="font-size:15px">View the conditions for using the voucher</font> </td> </tr> <tr> <td height="20" align="center"> <font face="Zurich BT, Zurich Cn BT, Arial" color="#000000" style="font-size:15px">•</font> </td> <td height="20" align="left"> <font face="Zurich BT, Zurich Cn BT, Arial" color="#000000" style="font-size:15px">Click Redeem the voucher to add points to your wallet (Rewards)</font> </td> </tr> <tr> <td height="20" align="center"> <font face="Zurich BT, Zurich Cn BT, Arial" color="#000000" style="font-size:15px">•</font> </td> <td height="20" align="left"> <font face="Zurich BT, Zurich Cn BT, Arial" color="#000000" style="font-size:15px">Redeem the points in the checkout page</font> </td> </tr> </tbody></table> </td> </tr> </tbody></table> </td> </tr>
							</table>';
						} else {
							$voucher_details .= '<p><b>Type: </b>Food Voucher</p>';
							$procedure = '<table cellpadding="0" cellspacing="0">
							<tr> 
							<td height="20" align="left" valign="bottom"> 
							<table width="250" border="1" align="center" cellpadding="0" cellspacing="0" style="border-collapse:collapse"> <tbody><tr> <td height="20" align="center" bgcolor="#098612"> <font face="Zurich BT, Zurich Cn BT, Arial" color="#ffffff" style="font-size:15px"><strong>How to redeem the voucher</strong></font> </td> </tr> </tbody>
							</table> 
							</td> 
							</tr>
							<tr> <td align="center" valign="top"> 
							<table width="400" border="1" cellspacing="0" cellpadding="0" style="border-collapse:collapse"> <tbody><tr> <td height="20" align="left" style="padding:10px"> <table width="380" border="0" align="center" cellpadding="0" cellspacing="0"> <tbody><tr> <td width="20" height="20" align="center"> <font face="Zurich BT, Zurich Cn BT, Arial" color="#000000" style="font-size:15px">•</font> </td> <td width="360" height="20" align="left"> <font face="Zurich BT, Zurich Cn BT, Arial" color="#000000" style="font-size:15px">Signup for an account in<strong style="color:#003366"> ' . $sign_in_url . '</strong></font> </td> </tr>
							<tr> <td height="20" align="center"> <font face="Zurich BT, Zurich Cn BT, Arial" color="#000000" style="font-size:15px">•</font> </td> <td height="20" align="left"> <font face="Zurich BT, Zurich Cn BT, Arial" color="#000000" style="font-size:15px">Click on <strong style="color:#003366">My e-Vouchers</strong> under My Account</font> </td> </tr> <tr> <td height="20" align="center"> <font face="Zurich BT, Zurich Cn BT, Arial" color="#000000" style="font-size:15px">•</font> </td> <td height="20" align="left"> <font face="Zurich BT, Zurich
							Cn BT, Arial" color="#000000" style="font-size:15px">View the conditions for using the voucher</font> </td> </tr> <tr> <td height="20" align="center"> <font face="Zurich BT, Zurich Cn BT, Arial" color="#000000" style="font-size:15px">•</font> </td> <td height="20" align="left"> <font face="Zurich BT, Zurich Cn BT, Arial" color="#000000" style="font-size:15px">Click Redeem the voucher to add products to cart</font> </td> </tr> <tr> <td height="20" align="center"> <font face="Zurich BT, Zurich Cn BT, Arial" color="#000000" style="font-size:15px">•</font> </td> <td height="20" align="left"> <font face="Zurich BT, Zurich Cn BT, Arial" color="#000000" style="font-size:15px">Checkout</font> </td> </tr> </tbody></table> </td> </tr> </tbody></table> </td> </tr>
							</table>';
						}
						$gifter_details = '';
						if ($customer_detail['order_customer_email'] != $voucher_gifts['order_item_voucher_email']) {
							$gifter_details = '<p style="font-size: 15px; color: #000000; font-family: Helvetica, Arial, sans-serif;">
							<b>Gift from: </b>' . $customer_detail['order_customer_fname'] . ' ' . $customer_detail['order_customer_lname'] . '</p>';
							$gifter_details .= '<p style="font-size: 15px; color: #000000; font-family: Helvetica, Arial, sans-serif;">
							<b> Email: </b>' . $customer_detail['order_customer_email'] . '</p>';
						}
						$replace_arr = array(ucfirst(stripslashes($voucher_gifts['voucher_gift_name'])), $voucher_details, $sign_in_url, $procedure, $gifter_details);

						$this->myemail->send_client_mail($voucher_gifts['order_item_voucher_email'], get_emailtemplate($company['company_unquie_id'], 'order_voucher_gift'), $check_arr, $replace_arr, $company['company_id'], $company['company_unquie_id']);

						/*update used status*/
						$update_vou_used = array('is_used' => '1', 'voucher_start_date' => current_date());
						if ($voucher_gifts['customer_id'] == "") {
							$update_vou_used['customer_id'] = $customer_detail['order_customer_id'];
						}
						$this->db->where('order_item_voucher_id', $voucher_gifts['order_item_voucher_id']);
						$this->db->update('order_item_voucher', $update_vou_used);
					}
				}
			}
		}
	}
	private function insert_status_log($order_primary_id, $order_id, $order_sts, $unique_id, $company_id)
	{
		return $this->Mydb->insert('productivity_status_log', array(
			'psl_company_unquie_id' => $unique_id,
			'psl_company_id' => $company_id,
			'psl_order_primary_id' => $order_primary_id,
			'psl_order_id' => $order_id,
			'psl_status_id' => $order_sts,
			'psl_start_date' => current_date(),
			'psl_user_ip' => get_ip()
		));
	}
	private function update_status_log($log_id, $order_sts, $order_primary_id, $unique_id)
	{
		$find_pre = $this->Mydb->get_record('psl_logid,psl_start_date', 'productivity_status_log', array(
			'psl_logid !=' => $log_id,
			'psl_company_unquie_id' => $unique_id,
			'psl_status_id' => $order_sts,
			'psl_order_primary_id' => $order_primary_id
		), array(
			'psl_logid' => 'DESC'
		));
		if (!empty($find_pre)) {
			$to_time = strtotime(current_date());
			$from_time = strtotime($find_pre['psl_start_date']);
			$diff_time = round(abs($to_time - $from_time) / 60, 2);

			$this->Mydb->update('productivity_status_log', array(
				'psl_logid' => $find_pre['psl_logid']
			), array(
				'psl_end_date' => current_date(),
				'psl_time_diff' => $diff_time
			));
		}
	}
} /* end of files */
