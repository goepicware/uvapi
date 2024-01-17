<?php

/**************************
 Project Name	: White Label
Created on		: 01 Aug, 2023
Last Modified 	: 14 Aug, 2023
Description		: Brands details
 ***************************/
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

class Brands extends REST_Controller
{
	function __construct()
	{
		parent::__construct();

		$this->load->library('form_validation');
		$this->load->library('Authorization_Token');
		$this->form_validation->set_error_delimiters('<p>', '</p>');
		$this->table = "brands";
		$this->primary_key = 'brand_id';
		$this->company_id = 'brand_company_id';
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

				$select_array = array('brand_id', 'brand_name', 'status');

				$limit = $offset = $like = '';
				$get_limit = $this->input->get('limit');
				$post_offset = (int) $this->input->get('offset');
				if ((int) $get_limit != 0) {
					$limit = (int) $get_limit;
				}
				$post_offset = ($post_offset > 1) ? $post_offset - 1 : 0;
				$offset = ($post_offset > 0 ? $post_offset * $limit : 0);

				$where = array(
					$this->company_id => $company_id,
				);
				if (!empty($status)) {
					$where = array_merge($where, array('status' => $status));
				}
				if (!empty($name)) {
					$like = array("brand_name" => $name);
				}

				$totalPages = 0;
				$total_records = $this->Mydb->get_num_rows($this->primary_key, $this->table, $where, null, null, null, $like);
				if (!empty($limit)) {
					$totalPages = ceil($total_records / $limit);
				}
				$result = $this->Mydb->get_all_records($select_array, $this->table, $where, $limit, $offset,  array(
					$this->primary_key => 'DESC'
				), $like, array($this->primary_key));


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
				$company_admin_id = decode_value($this->input->get('company_admin_id'));
				$get_company_details = $this->Mydb->get_record('company_unquie_id', 'company', array('company_id' => $company_id));
				$select_array = array(
					'brand_id AS value',
					'brand_name AS label',
				);

				$where = array(
					'status' => 'A',
					$this->company_id => $company_id,

				);

				$result = $this->Mydb->get_all_records($select_array, $this->table, $where, '', '',  array(
					'brand_id' => 'DESC'
				), '', array('brand_id'));


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
				$company_admin_id = decode_value($this->input->get('company_admin_id'));
				$get_company_details = $this->Mydb->get_record('company_unquie_id', 'company', array('company_id' => $company_id));
				$id = decode_value($this->input->get('detail_id'));
				$where = array('brand_id' => $id, $this->company_id => $company_id);
				$result = $this->Mydb->get_record('*', $this->table, $where);
				if (!empty($result)) {

					$site_location_list = $this->db->query("SELECT sl_location_id AS value, sl_name as label FROM `pos_site_location` WHERE `sl_location_id` IN (" . $result["brand_site_location_id"] . ")");
					$site_location_list_result = $site_location_list->result_array();
					$result['site_location_list'] = $site_location_list_result;
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
					$this->form_validation->set_rules('shop_type', 'lang:shop_type', 'required');
					$this->form_validation->set_rules('brand_name', 'lang:brand_name', 'required|callback_brandexists');
					if ($this->form_validation->run() == TRUE) {
						$brand_id  = '';
						$this->brandaddedit($brand_id);
						$this->set_response(array(
							'status' => 'success',
							'message' => "Brand has been addded successfully",
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
				$this->form_validation->set_rules('shop_type', 'lang:shop_type', 'required');
				$this->form_validation->set_rules('brand_name', 'lang:brand_name', 'required|callback_brandexists');
				if ($this->form_validation->run() == TRUE) {
					$brand_id  = decode_value($this->input->post('edit_id'));
					$this->brandaddedit($brand_id);
					$this->set_response(array(
						'status' => 'success',
						'message' => "Brand has been updated successfully",
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
				$brand_id  = decode_value($this->input->post('delete_id'));
				$company_admin_id = decode_value($this->input->post('company_admin_id'));
				if (!empty($company_id)) {
					$get_company_details = $this->Mydb->get_record('company_unquie_id', 'company', array('company_id' => $company_id));

					$where = array(
						'brand_id' => trim($brand_id),
						$this->company_id => $company_id,
					);
					$result = $this->Mydb->get_record('brand_id, brand_name', $this->table, $where);
					if (!empty($result)) {
						$this->Mydb->delete($this->table, array('brand_id' => $result['brand_id']));
						createAuditLog("Brand", stripslashes($result['brand_name']), "Delete", $company_admin_id, 'Web', '', $company_id, $get_company_details['company_unquie_id']);
						$return_array = array('status' => "ok", 'message' => 'Brand deleted successfully.',);
						$this->set_response($return_array, success_response());
					} else {
						$this->set_response(array('status' => 'error', 'message' => 'No Record Found.', 'form_error' => ''), something_wrong());
					}
				} else {
					$this->set_response(array('status' => 'error', 'message' => 'Brand Id field is required', 'form_error' => ''), something_wrong());
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

	public function brandaddedit($edit_id = null)
	{

		$action = post_value('action');

		$company_id = decode_value($this->input->post('company_id'));
		$company_admin_id = decode_value($this->input->post('company_admin_id'));
		$get_company_details = $this->Mydb->get_record('company_unquie_id', 'company', array('company_id' => $company_id));

		$Itemarray = array(
			'shop_type' => post_value('shop_type'),
			'brand_name' => post_value('brand_name'),
			'brand_site_location_id' => post_value('brand_site_location_id'),
			'brand_image' => post_value('brand_image'),
			'brand_active_image' => post_value('brand_active_image'),
			'brand_description' => post_value('brand_description'),
			'brand_sequence' => post_value('brand_sequence'),
			'brand_bg_class' => post_value('brand_bg_class'),
			'brand_website' => post_value('brand_website'),
			'brand_fb' => post_value('brand_fb'),
			'brand_twitter' => post_value('brand_twitter'),
			'brand_instagram' => post_value('brand_instagram'),
			'brand_open_time' => post_value('brand_open_time'),
			'brand_tags' => post_value('brand_tags'),
			'status' => post_value('status'),

		);
		// print'<pre>';print_r($Itemarray);
		if ($action == 'add') {

			$brand_slug = make_slug(stripslashes(post_value('brand_name')), $this->table, 'brand_slug', '');

			$Itemarray = array_merge($Itemarray,  array(
				$this->company_id => $company_id,
				'brand_unquie_id' => $get_company_details['company_unquie_id'],
				'brand_slug' => $brand_slug,

			));
			$this->Mydb->insert($this->table, $Itemarray);
			createAuditLog("Brand", stripslashes(post_value('brand_name')), "Add", $company_admin_id, 'Web', '', $company_id, $get_company_details['company_unquie_id']);
		} else if ($action == 'edit') {
			$brand_slug = make_slug(stripslashes(post_value('brand_name')), $this->table, 'brand_slug', '');
			$Itemarray = array_merge($Itemarray,  array(
				'brand_slug' => $brand_slug,
			));
			$this->Mydb->update($this->table, array($this->primary_key => $edit_id), $Itemarray);
			createAuditLog("Brand", stripslashes(post_value('brand_name')), "Update", $company_admin_id, 'Web', '', $company_id, $get_company_details['company_unquie_id']);
		}
	}

	public function brandexists()
	{
		$brand_name = $this->input->post('brand_name');
		$edit_id = $this->input->post('edit_id');
		$company_id = decode_value($this->input->post('company_id'));
		$where = array(
			'brand_name' => trim($brand_name),
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
			$this->form_validation->set_message('brandexists', sprintf(get_label('alredy_exist'), get_label('brand_name')));
			return false;
		} else {
			return true;
		}
	}
} /* end of files */
