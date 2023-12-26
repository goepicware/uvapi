<?php

/**************************
Project Name	: White Label
Created on		: 01 Sep, 2023
Last Modified 	: 04 Sep, 2023
Description		: Product Tag details

 ***************************/
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Tags extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p>', '</p>');
		$this->table = "product_tags";
		$this->outlet_management = "outlet_management";
		$this->load->library('common');
		$this->label = get_label('a_users_label');
		$this->load->library('Authorization_Token');
		$this->primary_key = 'pro_tag_primary_id';
		$this->company_id = 'pro_tag_company_id';
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
					$select_array = array('pro_tag_primary_id', 'pro_tag_name', 'pro_tag_status', 'pro_tag_created_on');
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
					$name = $this->input->get('name');
					$status = $this->input->get('status');
					$storeID = $this->input->get('storeID');
					$where = array("$this->primary_key !=" => '', $this->company_id => $company_id);
					if (!empty($storeID)) {
						$where = array_merge($where, array('pro_tag_outlet_id' => $storeID));
					}
					if (!empty($status)) {
						$where = array_merge($where, array('pro_tag_status' => $status));
					}
					if (!empty($name)) {
						$like = array("pro_tag_name" => $name);
					}

					if (!empty($userDetails)) {
						if ($userDetails['company_user_type'] == 'SubAdmin') {
							$where = array_merge($where, array("pro_tag_outlet_id" => $userDetails['company_user_permission_outlet']));
						}
					}

					$order_by = array($this->primary_key => 'DESC');

					$i = 0;
					$join[$i]['select'] = "outlet_name";
					$join[$i]['table'] = $this->outlet_management;
					$join[$i]['condition'] = "pro_tag_outlet_id = outlet_id";
					if (!empty($storeID)) {
						$join[$i]['type'] = "INNER";
					} else {
						$join[$i]['type'] = "LEFT";
					}

					$total_records = $this->Mydb->get_num_join_rows($this->primary_key, $this->table, $where, '', '', '', $like, array($this->primary_key), $join);

					$totalPages = (!empty($limit)) ? ceil($total_records / $limit) : 0;

					$result = $this->Mydb->get_all_records($select_array, $this->table, $where, $limit, $offset, $order_by, $like, array($this->primary_key), $join);
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
					'pro_tag_id AS value',
					"pro_tag_name AS label",
				);
				$where = array(
					$this->company_id => $company_id,
					'pro_tag_status' => 'A'
				);
				if (!empty($storeID)) {
					$where = array_merge($where, array('pro_tag_outlet_id' => $storeID));
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
					$result['outlet'] = $this->Mydb->get_record('outlet_id AS value, outlet_name AS label', $this->outlet_management, array('outlet_id' => $result['pro_tag_outlet_id']));

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
				$this->form_validation->set_rules('tag_name', 'lang:pro_tag_name', 'required|callback_tag_exists');
				$this->form_validation->set_rules('outlet_id', 'lang:rest_outlet_required', 'required');
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
				$this->form_validation->set_rules('tag_name', 'lang:pro_tag_name', 'required|callback_tag_exists');
				$this->form_validation->set_rules('outlet_id', 'lang:rest_outlet_required', 'required');
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
			'pro_tag_name' => post_value('tag_name'),
			'pro_tag_outlet_id' =>  post_value('outlet_id'),
			'pro_tag_image' => post_value('tag_image'),
			'pro_tag_status' => (post_value('status') == "A" ? 'A' : 'I'),
		);

		if ($action == 'add') {
			$company_id = decode_value($this->input->post('company_id'));
			$company_admin_id = decode_value($this->input->post('company_admin_id'));
			$getCompanyDetails = getCompanyUniqueID($company_id);

			$company_array = array('pro_tag_company_id' => $company_id, 'pro_tag_company_unquie_id' => $getCompanyDetails);
			$pro_tag_id = get_guid($this->table, 'pro_tag_id', $company_array);

			$data = array_merge(
				$data,
				array(
					'pro_tag_id' => $pro_tag_id,
					'pro_tag_company_id' => $company_id,
					'pro_tag_company_unquie_id' => (!empty($getCompanyDetails)) ? $getCompanyDetails : '',
					'pro_tag_created_on' => current_date(),
					'pro_tag_created_by' => $company_admin_id,
					'pro_tag_created_ip' => get_ip()
				)
			);

			$this->Mydb->insert($this->table, $data);
		} else {
			$data = array_merge(
				$data,
				array(
					'pro_tag_updated_on' => current_date(),
					'pro_tag_updated_by' => $company_admin_id,
					'pro_tag_updated_ip' => get_ip()
				)
			);
			$this->Mydb->update($this->table, array($this->primary_key => $edit_id), $data);
		}
	}

	/* this method used check tag or alredy exists or not */
	public function tag_exists()
	{
		$name = $this->input->post('tag_name');
		$outlet_id = $this->input->post('outlet_id');

		$edit_id = $this->input->post('edit_id');
		$company_id = decode_value($this->input->post('company_id'));
		$where = array(
			'pro_tag_name' => trim($name),
			'pro_tag_outlet_id' =>  post_value('outlet_id'),
		);
		if (!empty($edit_id)) {
			$edit_id = decode_value($edit_id);
			$where = array_merge($where, array(
				$this->primary_key . " !=" => $edit_id
			));
		}
		$where = array_merge(array('pro_tag_company_id' => $company_id), $where);
		$result = $this->Mydb->get_record($this->primary_key, $this->table, $where);
		if (!empty($result)) {
			$this->form_validation->set_message('tag_exists', get_label('tag_exist'));
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
