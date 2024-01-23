<?php

/**************************
 Project Name	: White Label
Created on		: 01 Aug, 2023
Last Modified 	: 14 Aug, 2023
Description		: Site Location details
 ***************************/
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Sitelocation extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->load->library('Authorization_Token');
		$this->form_validation->set_error_delimiters('<p>', '</p>');
		$this->table = "site_location";
		$this->primary_key = 'sl_location_id';
		$this->company_id = 'sl_company_id';
		$this->load->library('common');
	}

	public function list_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$company_id = decode_value($this->input->get('company_id'));
				$name = $this->input->get('name');
				$status = $this->input->get('status');
				$select_array = array(
					'sl_location_id',
					'sl_name',
					'sl_slug',
					'sl_location_name',
					'sl_pickup_postal_code',
					'sl_pickup_unit_number1',
					'sl_pickup_unit_number2',
					'sl_pickup_address_line1',
					'sl_status',
				);

				$limit = $offset = $like = '';
				$get_limit = $this->input->get('limit');
				$post_offset = (int) $this->input->get('offset');
				if ((int) $get_limit != 0) {
					$limit = (int) $get_limit;
				}
				$post_offset = ($post_offset > 1) ? $post_offset - 1 : 0;
				$offset = ($post_offset > 0 ? $post_offset * $limit : 0);

				$where = array(
					'sl_company_id' => $company_id,
				);
				if (!empty($status)) {
					$where = array_merge($where, array('sl_status' => $status));
				}
				if (!empty($name)) {
					$like = array("sl_name" => $name);
				}

				$totalPages = 0;
				$total_records = $this->Mydb->get_num_rows($this->primary_key, $this->table, $where, null, null, null, $like);

				if (!empty($limit)) {
					$totalPages = ceil($total_records / $limit);
				}
				$result = $this->Mydb->get_all_records($select_array, $this->table, $where, $limit, $offset,  array(
					$this->primary_key => 'DESC'
				),  $like, array($this->primary_key));

				if (!empty($result)) {
					$return_array = array('status' => "ok", 'message' => 'success', 'result' => $result, 'totalRecords' => $total_records, 'totalPages' => $totalPages);
					$this->set_response($return_array, success_response());
				} else {
					$this->set_response(array('status' => 'error', 'message' => 'No Record Found.'), something_wrong());
				}
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => 'Authentication token verification failed',
				), something_wrong()); /* error message */
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => 'Authentication failed',
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
				$select_array = array(
					'sl_location_id AS value',
					'sl_name AS label',
				);
				$where = array(
					'sl_company_id' => $company_id,
				);

				$result = $this->Mydb->get_all_records($select_array, $this->table, $where, '', '',  array(
					'sl_location_id' => 'DESC'
				), '', array('sl_location_id'));


				if (!empty($result)) {
					$return_array = array('status' => "ok", 'message' => 'success', 'result' => $result);
					$this->set_response($return_array, success_response());
				} else {
					$this->set_response(array('status' => 'error', 'message' => 'No Record Found.'), something_wrong());
				}
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => 'Authentication token verification failed',
				), something_wrong()); /* error message */
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => 'Authentication failed',
			), something_wrong()); /* error message */
		}
	}

	public function details_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$company_id = decode_value($this->input->get('company_id'));
				$id = decode_value($this->input->get('detail_id'));
				$where = array('sl_location_id' => $id, 'sl_company_id' => $company_id);
				$result = $this->Mydb->get_record('*', $this->table, $where);
				if (!empty($result)) {
					$return_array = array('status' => "ok", 'message' => 'success', 'result' => $result);
					$this->set_response($return_array, success_response());
				} else {
					$this->set_response(array('status' => 'error', 'message' => 'No Record Found.'), something_wrong());
				}
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => 'Authentication token verification failed',
					'form_error' => ''
				), something_wrong()); /* error message */
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => 'Authentication failed',
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
				if ($this->input->post('action') == "add") {
					$this->form_validation->set_rules('sl_name', 'lang:sl_name', 'required|callback_sitelocationexists');
					$this->form_validation->set_rules('sl_pickup_postal_code', 'lang:sl_pickup_postal_code', 'required');
					$this->form_validation->set_rules('sl_pickup_address_line1', 'lang:sl_pickup_address_line1', 'required');

					if ($this->form_validation->run() == TRUE) {
						$sl_location_id  = '';
						$this->sitelocationaddedit($sl_location_id);
						$this->set_response(array(
							'status' => 'success',
							'message' => "Site Location has been addded successfully",
							'form_error' => '',
						), success_response()); /* success message */
					} else {
						$this->set_response(array(
							'status' => 'error',
							'message' => get_label('rest_form_error'),
							'form_error' => validation_errors()
						), something_wrong()); /* error message */
					}
				}
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => 'Authentication token verification failed',
					'form_error' => ''
				), something_wrong()); /* error message */
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => 'Authentication failed',
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
				$error_tag = $this->post('error_element');
				$this->form_validation->set_rules('sl_name', 'lang:sl_name', 'required|callback_sitelocationexists');
				$this->form_validation->set_rules('sl_pickup_postal_code', 'lang:sl_pickup_postal_code', 'required');
				$this->form_validation->set_rules('sl_pickup_address_line1', 'lang:sl_pickup_address_line1', 'required');
				if ($this->form_validation->run() == TRUE) {
					$sl_location_id  = decode_value($this->input->post('edit_id'));
					$this->sitelocationaddedit($sl_location_id);
					$this->set_response(array(
						'status' => 'success',
						'message' => "Site Location has been updated successfully",
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
					'message' => 'Authentication token verification failed',
					'form_error' => ''
				), something_wrong()); /* error message */
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => 'Authentication failed',
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
				$sl_location_id  = decode_value($this->input->post('delete_id'));
				$company_admin_id = decode_value($this->input->post('company_admin_id'));
				if (!empty($company_id)) {
					$get_company_details = $this->Mydb->get_record('company_unquie_id', 'company', array('company_id' => $company_id));
					$user_arr = array();
					$where = array(
						'sl_location_id' => trim($sl_location_id)
					);
					$result = $this->Mydb->get_record('sl_location_id, sl_name', $this->table, $where);
					if (!empty($result)) {
						$this->Mydb->delete($this->table, array('sl_location_id' => $result['sl_location_id'], 'sl_company_id' => $company_id));

						createAuditLog("Site Location", stripslashes($result['sl_name']), "Delete", $company_admin_id, 'Web', '', $company_id, $get_company_details['company_unquie_id']);

						$return_array = array('status' => "ok", 'message' => 'Site Location deleted successfully.',);
						$this->set_response($return_array, success_response());
					} else {
						$this->set_response(array('status' => 'error', 'message' => 'No Record Found.', 'form_error' => ''), something_wrong());
					}
				} else {
					$this->set_response(array('status' => 'error', 'message' => 'Site Location Id field is required', 'form_error' => ''), something_wrong());
				}
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => 'Authentication token verification failed',
					'form_error' => ''
				), something_wrong()); /* error message */
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => 'Authentication failed',
				'form_error' => ''
			), something_wrong()); /* error message */
		}
	}

	public function sitelocationaddedit($sl_location_id = null)
	{

		$action = post_value('action');

		$company_id = decode_value($this->input->post('company_id'));
		$company_admin_id = decode_value($this->input->post('company_admin_id'));
		$get_company_details = $this->Mydb->get_record('company_unquie_id', 'company', array('company_id' => $company_id));

		$Itemarray = array(
			'sl_name' 					=> post_value('sl_name'),
			'sl_pickup_postal_code' 	=> post_value('sl_pickup_postal_code'),
			'sl_pickup_unit_number1' 	=> post_value('sl_pickup_unit_number1'),
			'sl_pickup_unit_number2' 	=> post_value('sl_pickup_unit_number2'),
			'sl_pickup_address_line1' 	=> post_value('sl_pickup_address_line1'),
			'sl_pickup_province'		=> post_value('sl_pickup_province'),
			'sl_pickup_city'			=> post_value('sl_pickup_city'),
			'sl_pickup_district'		=> post_value('sl_pickup_district'),
			'sl_pickup_village'			=> post_value('sl_pickup_village'),
			'sl_pickup_country'			=> post_value('sl_pickup_country'),
			'sl_latitude' 				=> post_value('sl_latitude'),
			'sl_longitude' 				=> post_value('sl_longitude'),
			'sl_location_name'          => post_value('sl_location_name'),
			'sl_status' 				=> (post_value('status') == "A" ? 'A' : 'I'),
			'sl_image' 					=> post_value('sl_image')
		);

		if ($action == 'add') {
			$Itemarray = array_merge($Itemarray,  array(
				'sl_company_id' => $company_id,
				'sl_unquie_id' => $get_company_details['company_unquie_id'],
				'sl_created_by'	=> $company_admin_id,

			));
			$sl_slug = make_slug(stripslashes(post_value('sl_name')), $this->table, 'sl_slug', '');
			$Itemarray = array_merge($Itemarray, array('sl_slug' => $sl_slug));
			$this->Mydb->insert($this->table, $Itemarray);
			createAuditLog("Site Location", stripslashes(post_value('sl_name')), "Add", $company_admin_id, 'Web', '', $company_id, $get_company_details['company_unquie_id']);
		} else if ($action == 'edit') {

			$sl_slug = make_slug(stripslashes(post_value('sl_name')), $this->table, 'sl_slug', '');

			$Itemarray = array_merge($Itemarray,  array(
				'sl_company_id' => $company_id,
				'sl_unquie_id' => $get_company_details['company_unquie_id'],
				'sl_created_by'	=> $company_admin_id,
				'sl_slug' => $sl_slug,

			));

			$this->Mydb->update($this->table, array($this->primary_key => $sl_location_id), $Itemarray);
			createAuditLog("Site Location", stripslashes(post_value('sl_name')), "Update", $company_admin_id, 'Web', '', $company_id, $get_company_details['company_unquie_id']);
		}
	}
	public function sitelocationexists()
	{
		$sl_name = $this->input->post('sl_name');
		$edit_id = $this->input->post('edit_id');
		$company_id = decode_value($this->input->post('company_id'));
		$where = array(
			'sl_name' => trim($sl_name),
			$this->company_id => $company_id,
		);
		if (!empty($edit_id)) {
			$edit_id = decode_value($edit_id);
			$where = array_merge($where, array(
				$this->primary_key . " !=" => $edit_id,
			));
		}
		$result = $this->Mydb->get_record($this->primary_key, $this->table, $where);
		if (!empty($result)) {
			$this->form_validation->set_message('sitelocationexists', get_label('sl_name_exist'));
			return false;
		} else {
			return true;
		}
	}
} /* end of files */
