<?php

/**************************
Project Name	: White Label
Created on		: 18 Aug, 2023
Last Modified 	: 18 Aug, 2023
Description		: Banner

 ***************************/
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Banner extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p>', '</p>');
		$this->table = "banners";
		$this->load->library('common');
		$this->label = "Banner";
		$this->load->library('Authorization_Token');
		$this->primary_key = 'banner_id';
		$this->company_id = 'banner_company_id';
	}

	public function list_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$select_array = array('banner_id ', 'banner_name', 'banner_status', 'banner_sequence', 'banner_image');
				$limit = $offset = $like = $next = $previous = '';
				$get_limit = $this->input->get('limit');
				$post_offset = (int) $this->input->get('offset');
				if ((int) $get_limit != 0) {
					$limit = (int) $get_limit;
				}
				$post_offset = ($post_offset > 1) ? $post_offset - 1 : 0;
				$offset = ($post_offset > 0 ? $post_offset * $limit : 0);

				$company_id = decode_value($this->input->get('company_id'));
				$company_admin_id = decode_value($this->input->get('company_admin_id'));
				$where = array("$this->primary_key !=" => '', $this->company_id => $company_id);

				$order_by = array($this->primary_key => 'DESC');

				$total_records = $this->Mydb->get_num_join_rows($this->primary_key, $this->table, $where, null, null, null, $like);

				$totalPages = (!empty($limit)) ? ceil($total_records / $limit) : 0;

				$result = $this->Mydb->get_all_records($select_array, $this->table, $where, $limit, $offset,  array(
					$this->primary_key => 'DESC'
				), '', array($this->primary_key));
				if (!empty($result)) {
					$return_array = array('status' => "ok", 'message' => 'success', 'result' => $result, 'totalRecords' => $total_records, 'totalPages' => $totalPages);
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
				$this->form_validation->set_rules('banner_name', 'lang:banner_name', 'required');
				$this->form_validation->set_rules('status', 'lang:status', 'required');
				$this->form_validation->set_rules('banner_image', 'lang:banner_image', 'required');
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
						'message' => validation_errors(),
						'form_error' => '',
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
				$this->form_validation->set_rules('banner_name', 'lang:banner_name', 'required');
				$this->form_validation->set_rules('status', 'lang:status', 'required');
				$this->form_validation->set_rules('banner_image', 'lang:banner_image', 'required');
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
				$company_admin_id = decode_value($this->input->post('company_admin_id'));
				if (!empty($company_id)) {
					$getCompanyDetails = getCompanyUniqueID($company_id);
					$where = array(
						$this->primary_key => trim($delete_id)
					);
					$result = $this->Mydb->get_record($this->primary_key . ', banner_name', $this->table, $where);
					if (!empty($result)) {
						$this->Mydb->delete($this->table, array($this->primary_key => $result[$this->primary_key]));

						createAuditLog("Banner", stripslashes($result['banner_name']), "Delete", $company_admin_id, 'Web', '', $company_id, $getCompanyDetails);

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

		$banner_avilablity = $this->input->post('product_avilablity');

		$data = array(
			'banner_name' 		=> post_value('banner_name'),
			'banner_image'		=> post_value('banner_image'),
			'banner_description' => post_value('banner_description'),
			'banner_link' 		=> post_value('banner_link'),
			'banner_status' 	=> (post_value('status') == "A" ? 'A' : 'I'),
		);

		$company_id = decode_value($this->input->post('company_id'));
		$company_admin_id = decode_value($this->input->post('company_admin_id'));
		$getCompanyDetails = getCompanyUniqueID($company_id);

		$company_array = array('banner_company_id' => $company_id, 'banner_unquie_id' => $getCompanyDetails);
		$banner_sequence = ((int)$this->input->post('banner_sequence') == 0) ?  get_sequence('banner_sequence', $this->table, $company_array) : $this->input->post('banner_sequence');

		if ($action == 'add') {
			$data = array_merge(
				$data,
				array(
					'banner_sequence' 	=> $banner_sequence,
					'banner_company_id'	=> $company_id,
					'banner_unquie_id' 	=> (!empty($getCompanyDetails)) ? $getCompanyDetails : '',
					'banner_created_on'	=> current_date(),
					'banner_created_by'	=> $company_admin_id,
					'banner_created_ip'	=> get_ip()
				)
			);
			$this->Mydb->insert($this->table, $data);
			createAuditLog("Banner", stripslashes(post_value('banner_name')), "Add", $company_admin_id, 'Web', '', $company_id, $getCompanyDetails);

			/* if (!empty($banner_avilablity)) {
				$this->insert_avalablity('add', $banner_avilablity, $insert_id, $company_id, $company_admin_id, $getCompanyDetails);
			} */
		} else {
			$data = array_merge(
				$data,
				array(
					'banner_sequence' 	=> $banner_sequence,
					'banner_updated_on'	=> current_date(),
					'banner_updated_by'	=> $company_admin_id,
					'banner_updated_ip'	=> get_ip()
				)
			);
			$this->Mydb->update($this->table, array($this->primary_key => $edit_id), $data);
			createAuditLog("Banner", stripslashes(post_value('banner_name')), "Update", $company_admin_id, 'Web', '', $company_id, $getCompanyDetails);

			/* $this->insert_avalablity('update', $banner_avilablity, $edit_id, $company_id, $company_admin_id, $getCompanyDetails); */
		}
	}


	/* this method used to insetr availablity */
	private function insert_avalablity($action, $banner_avilablity, $insert_id, $company_id, $company_admin_id, $getCompanyDetails)
	{
		$banner_id = $insert_id;
		if ($action == "update") {
			$this->Mydb->delete(
				'banner_assigned_availability',
				array(
					'banner_availability_company_id' => $company_id,
					'banner_availability_company_unquie_id' => $getCompanyDetails,
					'banner_availability_banner_id' => $banner_id
				)
			);
		}
		if (!empty($banner_avilablity)) {

			$banner_avilablity_new = explode(",", $banner_avilablity);

			foreach ($banner_avilablity_new as $avail) {
				$insert_array = array(
					'banner_availability_id' => $avail,
					'banner_availability_banner_id' => $banner_id,
					'banner_availability_company_id' => $company_id,
					'banner_availability_company_unquie_id' => $getCompanyDetails,
					'banner_availability_updated_on' => current_date(),
					'banner_availability_updated_by' => $company_admin_id,
					'banner_availability_updated_ip' => get_ip()
				);

				$insert_id = $this->Mydb->insert('banner_assigned_availability', $insert_array);
			}
			// true return
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
		$wherearray = array('banner_company_id' => $company_id, 'banner_unquie_id' => $company_app_id);

		if ($postaction == 'Delete' && !empty($ids)) {
			$this->audit_action($ids, $postaction);
			// $this->Mydb->delete_where_in($this->table,'client_id',$ids,'');
			if (is_array($ids)) {
				$this->Mydb->delete_where_in($this->table, $this->primary_key, $ids, $wherearray);
				$response['msg'] = sprintf(get_label('success_message_delete'), $this->module_label);
			} else {
				$this->Mydb->delete($this->table, array($this->primary_key => $ids, 'banner_company_id' => $company_id, 'email_unbanner_unquie_idquie_id' => $company_app_id));
				$response['msg'] = sprintf(get_label('success_message_delete'), $this->module_label);
			}
			$response['status'] = 'success';
			$response['action'] = $postaction;
		}

		$where_array = array('banner_company_id' => $company_id, 'banner_unquie_id' => $company_app_id);

		/* Activation */
		if ($postaction == 'Activate' && !empty($ids)) {
			$update_values = array(
				"banner_status" => 'A',
				"banner_updated_on" => current_date(),
				'banner_updated_by' => $company_admin_id,
				'banner_updated_ip' => get_ip()
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
				"banner_status" => 'I',
				"banner_updated_on" => current_date(),
				'banner_updated_by' => $company_admin_id,
				'banner_updated_ip' => get_ip()
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
