<?php

/**************************
Project Name	: White Label
Created on		: 18 Aug, 2023
Last Modified 	: 18 Aug, 2023
Description		: Page details

 ***************************/
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Pages extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p>', '</p>');
		$this->table = "cmspage";
		$this->load->library('common');
		$this->label = "Pages";
		$this->load->library('Authorization_Token');
		$this->primary_key = 'cmspage_id';
		$this->company_id = 'cmspage_company_id';
	}

	public function list_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$select_array = array('cmspage_id ', 'cmspage_title', 'cmspage_slug', 'cmspage_status', 'cmspage_created_on');
				$limit = $offset =  '';
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
				$where = array("$this->primary_key !=" => '', $this->company_id => $company_id);
				if (!empty($status)) {
					$where = array_merge($where, array('cmspage_status' => $status));
				}
				if (!empty($name)) {
					$like = array("cmspage_title" => $name);
				}

				$total_records =  $this->Mydb->get_num_rows($this->primary_key, $this->table, $where, null, null, null, $like);

				$totalPages = (!empty($limit)) ? ceil($total_records / $limit) : 0;

				$result = $this->Mydb->get_all_records($select_array, $this->table, $where, $limit, $offset,  array(
					$this->primary_key => 'DESC'
				), $like, array($this->primary_key));
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

				$this->form_validation->set_rules('cmspage_title', 'Title', 'required|trim|callback_page_exists');

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
				$this->form_validation->set_rules('cmspage_title', 'Title', 'required|trim|strip_tags|callback_page_exists');
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
		$title = post_value('cmspage_title');
		$cms_description = $this->input->post('cmspage_description', false);
		$data = array(
			'cmspage_title'				=> $title,
			'cmspage_description'		=> $cms_description,
			'cmspage_meta_title' 		=> post_value('cmspage_meta_title'),
			'cmspage_meta_description' 	=> post_value('cmspage_meta_description'),
			'cmspage_meta_keyword' 		=> post_value('cmspage_meta_keyword'),
			'cmspage_status' 			=> post_value('status')
		);
		$banner_image = post_value('banner_image');
		if (!empty($banner_image)) {
			$data = array_merge($data, array('cmspage_banner_image' => $banner_image));
		}

		if ($action == 'add') {
			$company_id = decode_value($this->input->post('company_id'));
			$company_admin_id = decode_value($this->input->post('company_admin_id'));
			$getCompanyDetails = getCompanyUniqueID($company_id);


			$slug = make_slug($title, $this->table, 'cmspage_slug', array($this->company_id => $company_id));
			$data = array_merge(
				$data,
				array(
					'cmspage_slug'		=> $slug,
					'cmspage_company_id' => $company_id,
					'cmspage_company_unquie_id' 	=> (!empty($getCompanyDetails)) ? $getCompanyDetails : '',
					'cmspage_created_on' => current_date(),
					'cmspage_created_by' => $company_admin_id,
					'cmspage_created_ip' => get_ip()
				)
			);

			$this->Mydb->insert($this->table, $data);
		} else {
			$slug = make_slug($title, $this->table, 'cmspage_slug', array("$this->primary_key !=" => $edit_id, $this->company_id => $company_id));
			$data = array_merge(
				$data,
				array(
					'cmspage_slug'		=> $slug,
					'cmspage_updated_on' => current_date(),
					'cmspage_updated_by' => $company_admin_id,
					'cmspage_updated_ip' => get_ip()
				)
			);
			$this->Mydb->update($this->table, array($this->primary_key => $edit_id), $data);
		}
	}

	public function page_exists()
	{
		$cmspage_title = $this->input->post('cmspage_title');
		$edit_id = $this->input->post('edit_id');
		$company_id = decode_value($this->input->post('company_id'));
		$where = array(
			'cmspage_title' => trim($cmspage_title),
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
			$this->form_validation->set_message('page_exists', sprintf(get_label('alredy_exist'), 'Title'));
			return false;
		} else {
			return true;
		}
	}
} /* end of files */
