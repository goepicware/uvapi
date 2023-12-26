<?php

/**************************
Project Name	: White Label
Created on		: 28 Aug, 2023
Last Modified 	: 28 Aug, 2023
Description		: Promotion details

 ***************************/
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Promotion extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p>', '</p>');
		$this->table = "promotion";
		$this->promotion_availability = "promotion_availability";
		$this->promotion_categories = "promotion_categories";
		$this->promotion_customer = "promotion_customer";
		$this->outlet_management = "outlet_management";
		$this->product_categories = "product_categories";
		$this->load->library('common');
		$this->label = get_label('rest_promotion');
		$this->load->library('Authorization_Token');
		$this->primary_key = 'promotion_id';
		$this->company_id = 'promotion_company_id';
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
					$select_array = array('promotion_id', "promotion_name", 'promotion_start_date', 'promotion_end_date', 'promotion_status', 'promotion_created_on');
					$limit = $offset = '';
					$like = array();
					$get_limit = $this->input->get('limit');
					$post_offset = (int) $this->input->get('offset');
					if ((int) $get_limit != 0) {
						$limit = (int) $get_limit;
					}
					$post_offset = ($post_offset > 1) ? $post_offset - 1 : 0;
					$offset = ($post_offset > 0 ? $post_offset * $limit : 0);

					$company_id = decode_value($this->input->get('company_id'));
					$storeID = $this->input->get('storeID');
					$name = $this->input->get('name');
					$status = $this->input->get('status');
					$where = array("$this->primary_key !=" => '', $this->company_id => $company_id);
					if (!empty($status)) {
						$where = array_merge($where, array('promotion_status' => $status));
					}
					if (!empty($storeID) && $storeID != 'alloutlet') {
						$where = array_merge($where, array('promotion_outlet_id' => $storeID));
					}
					if (!empty($userDetails)) {
						if ($userDetails['company_user_type'] == 'SubAdmin') {
							$where = array_merge($where, array("promotion_outlet_id" => $userDetails['company_user_permission_outlet']));
						}
					}
					if (!empty($name)) {
						$like = array("promotion_name" => $name);
					}
					$order_by = array($this->primary_key => 'DESC');

					$join = array();
					$i = 0;
					$join[$i]['select'] = "outlet_name";
					$join[$i]['table'] = "outlet_management";
					$join[$i]['condition'] = "promotion_outlet_id = outlet_id";
					$join[$i]['type'] = "LEFT";
					$i++;

					$total_records = $this->Mydb->get_num_join_rows($this->primary_key, $this->table, $where, null, null, null, $like, null, $join);

					$totalPages = (!empty($limit)) ? ceil($total_records / $limit) : 0;

					$result = $this->Mydb->get_all_records($select_array, $this->table, $where, $limit, $offset,  $order_by, $like, array($this->primary_key), $join);
					if (!empty($result)) {
						$return_array = array('status' => "ok", 'message' => 'success', 'result' => $result, 'totalRecords' => $total_records, 'totalPages' => $totalPages);
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

	public function dropdownlist_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$company_id = decode_value($this->input->get('company_id'));
				$where = array('promotion_company_id' => $company_id, 'promotion_start_date<=' => date('Y-m-d H:i:s'), 'promotion_end_date>=' => date('Y-m-d H:i:s'), 'promotion_status' => 'A');
				$result =  $this->Mydb->get_all_records('promotion_id AS value, promotion_name AS label', $this->table, $where);
				if (!empty($result)) {
					$return_array = array('status' => "ok", 'message' => 'success', 'result' => $result);
					$this->set_response($return_array, success_response());
				} else {
					$this->set_response(array('status' => 'error', 'message' => get_label('no_records_found')), something_wrong());
				}
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => get_label('token_verify_faild'),
				), something_wrong()); /* error message */
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => get_label('token_faild'),
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
				$result = $this->Mydb->get_record('*', $this->table, $where);
				if (!empty($result)) {

					$join = array();
					$join[0]['select'] = "av_name AS label";
					$join[0]['table'] = "availability";
					$join[0]['condition'] = "promo_availability_id = av_id";
					$join[0]['type'] = "INNER";
					$outlet_availability = $this->Mydb->get_all_records('promo_availability_id AS value', $this->promotion_availability, array('promo_availability_promocode_primary_id' => $result[$this->primary_key]), null, null, null, null, null, $join);
					$result['promo_availability'] = $outlet_availability;

					$outlet = array();
					if (!empty($result['promotion_outlet_id'])) {
						$outlet = $this->Mydb->get_all_records('outlet_id AS value, outlet_name AS label', $this->outlet_management, array('outlet_id' => $result['promotion_outlet_id']));
					}
					$result['promo_outlet'] = $outlet;


					$join = array();
					$join[0]['select'] = "pro_cate_name AS label";
					$join[0]['table'] = "product_categories";
					$join[0]['condition'] = "promo_category_primary_id = pro_cate_primary_id";
					$join[0]['type'] = "INNER";
					$outlet_availability = $this->Mydb->get_all_records('promo_category_primary_id AS value', $this->promotion_categories, array('promo_promotion_primary_id' => $result[$this->primary_key]), null, null, null, null, null, $join);
					$result['promo_category'] = $outlet_availability;

					$join = array();
					$join[0]['select'] = "CONCAT(customer_first_name, ' ', customer_last_name) AS label";
					$join[0]['table'] = "customers";
					$join[0]['condition'] = "pro_customer_id = customer_id";
					$join[0]['type'] = "INNER";
					$outlet_availability = $this->Mydb->get_all_records('pro_customer_id AS value', $this->promotion_customer, array('pro_promotion_id' => $result[$this->primary_key]), null, null, null, null, null, $join);
					$result['promo_customer'] = $outlet_availability;


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

	public function add_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$this->form_validation->set_rules('promotion_name', 'lang:promotion_name', 'required|callback_promotionname_exists');
				$this->form_validation->set_rules('start_date', 'lang:promotion_start_date', 'required');
				$this->form_validation->set_rules('end_date', 'lang:promotion_end_date', 'required');
				$this->form_validation->set_rules('promotion_coupon_type', 'lang:promotion_coupon_type', 'required');
				$this->form_validation->set_rules('promotion_type', 'lang:promotion_type', 'required');
				$this->form_validation->set_rules('status', 'lang:status', 'required');
				if ($this->form_validation->run() == TRUE) {
					$this->addedit();
					$this->set_response(array(
						'status' => 'success',
						'message' => sprintf(get_label('success_message_add'), $this->label),
						'form_error' => '',
					), success_response()); /* success message */
				} else {
					$this->set_response(array(
						'status' => 'error',
						'message' => get_label('rest_form_error'),
						'form_error' => validation_errors()
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

	public function update_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$this->form_validation->set_rules('promotion_name', 'lang:promotion_name', 'required|callback_promotionname_exists');
				$this->form_validation->set_rules('start_date', 'lang:promotion_start_date', 'required');
				$this->form_validation->set_rules('end_date', 'lang:promotion_end_date', 'required');
				$this->form_validation->set_rules('promotion_coupon_type', 'lang:promotion_coupon_type', 'required');
				$this->form_validation->set_rules('promotion_type', 'lang:promotion_type', 'required');
				if ($this->form_validation->run() == TRUE) {
					$edit_id = $this->input->post('edit_id');
					$this->addedit($edit_id);
					$this->set_response(array(
						'status' => 'success',
						'message' => sprintf(get_label('success_message_edit'), $this->label),
						'form_error' => '',
					), success_response()); /* success message */
				} else {
					$this->set_response(array(
						'status' => 'error',
						'message' => get_label('rest_form_error'),
						'form_error' => validation_errors()
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

	public function delete_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$company_id = decode_value($this->input->post('company_id'));
				$delete_id = decode_value($this->input->post('delete_id'));
				if (!empty($company_id)) {
					$where = array(
						$this->primary_key => trim($delete_id)
					);
					$result = $this->Mydb->get_record($this->primary_key, $this->table, $where);
					if (!empty($result)) {
						$this->Mydb->delete($this->table, array($this->primary_key => $result[$this->primary_key]));

						$this->Mydb->delete($this->promotion_availability, array(
							'promo_availability_company_id' => $company_id,
							'promo_availability_promocode_primary_id' => $result[$this->primary_key]
						));
						$this->Mydb->delete($this->promotion_categories, array('promo_promotion_primary_id' => $result[$this->primary_key]));
						$this->Mydb->delete($this->promotion_customer, array('pro_promotion_id' => $result[$this->primary_key]));

						$return_array = array('status' => "ok", 'message' => sprintf(get_label('success_message_delete'), $this->label));
						$this->set_response($return_array, success_response());
					} else {
						$this->set_response(array('status' => 'error', 'message' => get_label('no_records_found'), 'form_error' => ''), something_wrong());
					}
				} else {
					$this->set_response(array('status' => 'error', 'message' => sprintf(get_label('field_required'), 'Company ID'), 'form_error' => ''), something_wrong());
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

	public function addedit($edit_id = null)
	{

		if (!empty($edit_id)) {
			$edit_id = decode_value($edit_id);
		}
		$action = post_value('action');
		$data = array(
			'promotion_name' 		=> post_value('promotion_name'),
			'promotion_outlet_id'	=> post_value('outlets'),
			'promotion_start_date' 	=> post_value('start_date'),
			'promotion_end_date' 	=> post_value('end_date'),
			'promotion_qty' 		=> post_value('promotion_qty'),
			'promotion_amount' 		=> post_value('promotion_amount'),
			'promotion_coupon_type' => post_value('promotion_coupon_type'),
			'promotion_delivery_charge_discount' => post_value('promotion_delivery_charge_discount'),
			'promotion_type' 		=> post_value('promotion_type'),
			'promotion_percentage' 	=> post_value('promotion_percentage'),
			'promotion_max_amt' 	=> post_value('promotion_max_amt'),
			'promotion_no_use' 		=> (post_value('promotion_coupon_type') == 1) ? post_value('promotion_no_use') : 1,
			'promotion_title' 		=> post_value('promotion_title'),
			'promotion_desc' 		=> post_value('promotion_desc', 'false'),
			'promotion_long_desc' 	=> post_value('promotion_long_desc', 'false'),
			'promotion_thumbnail_image' => post_value('thumbnail'),
			'promotion_banner_image' => post_value('banner'),
			'promotion_cata_flag' 	=> post_value('promotion_cata_flag'),
			'promotion_status' 		=> ($this->input->post('status') == "A" ? 'A' : 'I'),
		);
		$company_id = decode_value($this->input->post('company_id'));
		$company_admin_id = decode_value($this->input->post('company_admin_id'));
		$getCompanyDetails = getCompanyUniqueID($company_id);
		if ($action == 'add') {

			$data = array_merge(
				$data,
				array(
					'promotion_company_id' => $company_id,
					'promotion_company_unique_id' => (!empty($getCompanyDetails)) ? $getCompanyDetails : '',
					'promotion_created_on'	=> current_date(),
					'promotion_created_by' 	=> $company_admin_id,
					'promotion_created_ip' 	=> get_ip()
				)
			);

			$edit_id = $this->Mydb->insert($this->table, $data);
		} else {

			$data = array_merge(
				$data,
				array(
					'promotion_updated_on'	=> current_date(),
					'promotion_updated_by' 	=> $company_admin_id,
					'promotion_updated_ip' 	=> get_ip()
				)
			);
			$this->Mydb->update($this->table, array($this->primary_key => $edit_id), $data);
		}
		if (!empty($edit_id)) {
			$promo_avilablity = (post_value('assign_availability') != "") ? explode(',', post_value('assign_availability')) : [];
			$this->insert_avalablity($action, $promo_avilablity, $edit_id, $getCompanyDetails, $company_id, $company_admin_id);
			$promo_categories =  (post_value('promotion_category') != "") ? explode(',', post_value('promotion_category')) : [];
			$this->insert_promo_categories($action, $promo_categories, $edit_id);
			$promo_customer =  (post_value('promo_customer') != "") ? explode(',', post_value('promo_customer')) : [];
			$this->insert_promo_customer($action, $promo_customer, $edit_id);
		}
	}
	private function insert_avalablity($action, $promo_avilablity, $promoID, $getCompanyDetails, $company_id, $loginID)
	{
		if ($action == "edit") {
			$this->Mydb->delete($this->promotion_availability, array(
				'promo_availability_company_id' => $company_id,
				'promo_availability_company_app_id' => $getCompanyDetails,
				'promo_availability_promocode_primary_id' => $promoID
			));
		}
		if (!empty($promo_avilablity)) {
			foreach ($promo_avilablity as $avail) {
				$insert_array = array(
					'promo_availability_id' => $avail,
					'promo_availability_promocode_primary_id' => $promoID,
					'promo_availability_company_id' => $company_id,
					'promo_availability_company_app_id' => $getCompanyDetails,
					'promo_availability_updated_on' => current_date(),
					'promo_availability_updated_by' => $loginID,
					'promo_availability_updated_ip' => get_ip(),
				);
				$this->Mydb->insert($this->promotion_availability, $insert_array);
			}
		}
	}
	private function insert_promo_categories($action, $promo_categories, $promoID)
	{
		if ($action == "edit") {
			$this->Mydb->delete($this->promotion_categories, array('promo_promotion_primary_id' => $promoID));
		}

		if (!empty($promo_categories)) {
			foreach ($promo_categories as $category) {
				$insert_array = array(
					'promo_category_primary_id' => $category,
					'promo_promotion_primary_id' => $promoID
				);
				$this->Mydb->insert($this->promotion_categories, $insert_array);
			}
		}
	}

	private function insert_promo_customer($action, $promo_customer, $promoID)
	{
		if ($action == "edit") {
			$this->Mydb->delete($this->promotion_customer, array('pro_promotion_id' => $promoID));
		}
		if (!empty($promo_customer)) {
			$p = 1;
			foreach ($promo_customer as $cust_id) {
				$this->Mydb->insert(
					$this->promotion_customer,
					array(
						'pro_promotion_id' => $promoID,
						'pro_customer_id' => $cust_id,
						'pro_incre_id' => $p
					)
				);
				$p++;
			}
		}
	}

	/* this method used check discount name alredy exists or not */
	public function promotionname_exists()
	{
		$promotion_name = $this->input->post('promotion_name');
		$edit_id = $this->input->post('edit_id');
		$company_id = decode_value($this->input->post('company_id'));
		$where = array(
			'promotion_name' => trim($promotion_name),
			'promotion_company_id' => $company_id
		);
		if (!empty($edit_id)) {
			$edit_id = decode_value($edit_id);
			$where = array_merge($where, array(
				"promotion_id !=" => $edit_id,
			));
		}
		$result = $this->Mydb->get_record($this->primary_key, $this->table, $where);
		if (!empty($result)) {
			$this->form_validation->set_message('promotionname_exists', get_label('promotionname_exists'));
			return false;
		} else {
			return true;
		}
	}


	public function action_post()
	{
		$ids = ($this->input->post('multiaction') == 'Yes' ? $this->input->post('id') : decode_value($this->input->post('changeId')));
		$postaction = $this->input->post('postaction');
		$company_id = decode_value($this->input->post('company_id'));
		$company_admin_id = decode_value($this->input->post('company_admin_id'));
		$get_company_details = $this->Mydb->get_record('company_unquie_id', 'company', array('company_id' => $company_id));
		$company_app_id =  $get_company_details['company_unquie_id'];
		$response = array(
			'status' => 'error',
			'msg' => get_label('something_wrong'),
			'action' => '',
			'form_error' => '',
			'multiaction' => $this->input->post('multiaction')
		);

		/* Delete */
		$wherearray = array('menu_company_id' => $company_id, 'menu_unquie_id' => $company_app_id);
		if ($postaction == 'Delete' && !empty($ids)) {
			$this->audit_action($ids, $postaction);
			// $this->Mydb->delete_where_in($this->table,'client_id',$ids,'');
			if (is_array($ids)) {
				$this->Mydb->delete_where_in($this->table, $this->primary_key, $ids, $wherearray);
				$response['msg'] = sprintf(get_label('success_message_delete'), $this->module_label);
			} else {
				$this->Mydb->delete($this->table, array($this->primary_key => $ids, 'menu_company_id' => $company_id, 'menu_unquie_id' => $company_app_id));
				$response['msg'] = sprintf(get_label('success_message_delete'), $this->module_label);
			}
			$response['status'] = 'success';
			$response['action'] = $postaction;
		}

		$where_array = array('menu_company_id' => $company_id, 'menu_unquie_id' => $company_app_id);
		/* Activation */
		if ($postaction == 'Activate' && !empty($ids)) {
			$update_values = array(
				"menu_status" => 'A',
				"menu_updated_on" => current_date(),
				'menu_updated_by' => $company_admin_id,
				'menu_updated_ip' => get_ip()
			);

			if (is_array($ids)) {
				$this->Mydb->update_where_in($this->table, $this->primary_key, $ids, $update_values, $where_array);
				$response['msg'] = sprintf(get_label('success_message_activate'), $this->module_labels);
			} else {

				$this->Mydb->update_where_in($this->table, $this->primary_key, array(
					$ids
				), $update_values, $where_array);
				$response['msg'] = sprintf(get_label('success_message_activate'), $this->module_label);
			}
			/* track outlet status */
			$this->track_outlet_status($ids, 1);
			$this->audit_action($ids, $postaction);
			$response['status'] = 'success';
			$response['action'] = $postaction;
		}

		/* Deactivation */
		if ($postaction == 'Deactivate' && !empty($ids)) {
			$update_values = array(
				"menu_status" => 'I',
				"menu_updated_on" => current_date(),
				'menu_updated_by' => $company_admin_id,
				'menu_updated_ip' => get_ip()
			);

			if (is_array($ids)) {
				$this->Mydb->update_where_in($this->table, $this->primary_key, $ids, $update_values, $where_array);
				$response['msg'] = sprintf(get_label('success_message_deactivate'), $this->module_labels);
			} else {
				$this->Mydb->update_where_in($this->table, $this->primary_key, array(
					$ids
				), $update_values, $where_array);
				$response['msg'] = sprintf(get_label('success_message_deactivate'), $this->module_label);
			}
			$response['status'] = 'success';
			$response['action'] = $postaction;
		}

		$this->set_response($response, success_response()); /* success message */
	}
} /* end of files */
