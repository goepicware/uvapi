<?php

/**************************
Project Name	: White Label
Created on		: 28 Aug, 2023
Last Modified 	: 28 Aug, 2023
Description		: Product Group Templates

 ***************************/
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Productgroup extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p>', '</p>');
		$this->table = "product_groups";
		$this->product_groups_details = "product_groups_details";
		$this->load->library('common');
		$this->label = get_label('pro_group_label');
		$this->load->library('Authorization_Token');
		$this->primary_key = 'pro_group_primary_id';
		$this->company_id = 'pro_group_company_id';
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
					$select_array = array('pro_group_primary_id', 'pro_group_name', 'pro_group_status', 'pro_group_status', 'pro_group_created_on');
					$limit = $offset = $like =  '';
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
					if (!empty($storeID)) {
						$where = array_merge($where, array('pro_group_outlet_id' => $storeID));
					}
					if (!empty($status)) {
						$where = array_merge($where, array('pro_group_status' => $status));
					}
					if (!empty($name)) {
						$like = array("pro_group_name" => $name);
					}
					if (!empty($userDetails)) {
						if ($userDetails['company_user_type'] == 'SubAdmin') {
							$where = array_merge($where, array("pro_group_outlet_id" => $userDetails['company_user_permission_outlet']));
						}
					}

					$order_by = array($this->primary_key => 'DESC');

					$join = array();


					$i = 0;
					$join[$i]['select'] = "outlet_name";
					$join[$i]['table'] = "outlet_management";
					$join[$i]['condition'] = "pro_group_outlet_id = outlet_id";
					if (!empty($storeID)) {
						$join[$i]['type'] = "INNER";
					} else {
						$join[$i]['type'] = "LEFT";
					}

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
				$storeID = $this->input->get('storeID');
				$select_array = array(
					$this->primary_key . ' AS value',
					'pro_group_name AS label',
				);
				$where = array(
					$this->company_id => $company_id,
					'pro_group_status' => 'A'
				);
				if (!empty($storeID)) {
					$where = array_merge($where, array('pro_group_outlet_id' => $storeID));
				}

				$result = $this->Mydb->get_all_records($select_array, $this->table, $where);
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

					$outlet = $this->Mydb->get_record('outlet_id AS value, outlet_name AS label', "outlet_management", array('outlet_id' => $result['pro_group_outlet_id']));
					$result['group_outlet'] = $outlet;
					$group_products = $this->Mydb->get_all_records('group_detail_product_id', 'product_groups_details', array('group_detail_group_id' => $id));
					$result['group_products'] = (!empty($group_products)) ? array_column($group_products, 'group_detail_product_id') : [];

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
				/* callback_group_exists */
				$this->form_validation->set_rules('group_name', 'lang:pro_group_name', 'required');
				/* callback_check_include_prodcuts */
				$this->form_validation->set_rules('outlet_id', 'lang:rest_outlet_id', 'required');
				$this->form_validation->set_rules('product_id', 'lang:included_products', 'required');
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
				/* callback_group_exists */
				$this->form_validation->set_rules('group_name', 'lang:pro_group_name', 'required');
				/* callback_check_include_prodcuts */
				$this->form_validation->set_rules('outlet_id', 'lang:rest_outlet_id', 'required');
				$this->form_validation->set_rules('product_id', 'lang:included_products', 'required');

				$this->form_validation->set_rules('status', 'lang:status', 'required');
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
						$this->Mydb->delete($this->product_groups_details, array('group_detail_group_id' => $result[$this->primary_key]));

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
			'pro_group_name' 		=> post_value('group_name'),
			'pro_group_outlet_id' 	=> post_value('outlet_id'),
			'pro_group_status' 		=> (post_value('status') == "A" ? 'A' : 'I'),
			'pro_group_discount' 	=> (post_value('discount') == "A" ? 'A' : 'I'),
		);
		$company_id = decode_value($this->input->post('company_id'));
		$company_admin_id = decode_value($this->input->post('company_admin_id'));
		$getCompanyDetails = getCompanyUniqueID($company_id);
		if ($action == 'add') {
			$company_array = array($this->company_id => $company_id, 'pro_group_unquie_id' => $getCompanyDetails);
			$pro_group_id = get_guid($this->table, 'pro_group_id', $company_array);

			$data = array_merge(
				$data,
				array(
					'pro_group_id' 			=> $pro_group_id,
					'pro_group_company_id' 	=> $company_id,
					'pro_group_unquie_id' 	=> (!empty($getCompanyDetails)) ? $getCompanyDetails : '',
					'pro_group_created_on' 	=> current_date(),
					'pro_group_created_by' 	=> $company_admin_id,
					'pro_group_created_ip' 	=> get_ip()
				)
			);
			$edit_id = $this->Mydb->insert($this->table, $data);
		} else {
			$data = array_merge(
				$data,
				array(
					'pro_group_updated_on' => current_date(),
					'pro_group_updated_by' => $company_admin_id,
					'pro_group_updated_ip' => get_ip()
				)
			);
			$this->Mydb->update($this->table, array($this->primary_key => $edit_id), $data);
		}
		if (!empty($edit_id)) {
			$product_ids = post_value('product_id');
			$this->insert_group_details($edit_id, $product_ids, $action);
		}
	}

	private function insert_group_details($editID, $product_ids, $action)
	{
		/* if update delete old records.. */
		if ($action == "edit") {
			$this->Mydb->delete('product_groups_details', array('group_detail_group_id' => $editID));
		}
		$product_ids = explode(',', $product_ids);
		$products = $this->Mydb->get_all_records_where_in('product_category_id,product_subcategory_id,product_id', 'products', 'product_id', $product_ids, array('product_sequence' => "ASC"));
		if (!empty($products)) {
			foreach ($products as $key) {
				$grp_details = array(
					'group_detail_group_id' => $editID,
					'group_detail_category_id' => $key['product_category_id'],
					'group_detail_subcategory_id' =>  $key['product_subcategory_id'],
					'group_detail_product_id' => $key['product_id']
				);
				$this->Mydb->insert('product_groups_details', $grp_details);
			}
		}
	}

	/* this method used check group name or alredy exists or not */
	public function group_exists()
	{
		$name = $this->input->post('group_name');
		$edit_id = $this->input->post('edit_id');

		$where = array(
			'pro_group_name' => trim($name)
		);
		if (!empty($edit_id)) {
			$where = array_merge($where, array(
				$this->primary_key . " !=" => $edit_id
			));
		}
		$where = array_merge(array($this->company_id => $company_id), $where);
		$result = $this->Mydb->get_record($this->primary_key, $this->table, $where);

		if (!empty($result)) {
			$this->form_validation->set_message('group_exists', get_label('group_exist'));
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
		$wherearray = array('email_company_id' => $company_id, 'email_unquie_id' => $company_app_id);
		if ($postaction == 'Delete' && !empty($ids)) {
			$this->audit_action($ids, $postaction);
			// $this->Mydb->delete_where_in($this->table,'client_id',$ids,'');
			if (is_array($ids)) {
				$this->Mydb->delete_where_in($this->table, $this->primary_key, $ids, $wherearray);
				$response['msg'] = sprintf(get_label('success_message_delete'), $this->module_label);
			} else {
				$this->Mydb->delete($this->table, array($this->primary_key => $ids, 'email_company_id' => $company_id, 'email_unquie_id' => $company_app_id));
				$response['msg'] = sprintf(get_label('success_message_delete'), $this->module_label);
			}
			$response['status'] = 'success';
			$response['action'] = $postaction;
		}


		$this->set_response($response, success_response()); /* success message */
	}
} /* end of files */
