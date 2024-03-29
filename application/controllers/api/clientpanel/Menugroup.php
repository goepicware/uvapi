<?php

/**************************
Project Name	: White Label
Created on		: 18 Aug, 2023
Last Modified 	: 18 Aug, 2023
Description		: Menu Group details

 ***************************/
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Menugroup extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p>', '</p>');
		$this->table = "menu";
		$this->load->library('common');
		$this->label = "Menu";
		$this->load->library('Authorization_Token');
		$this->primary_key = 'menu_id';
		$this->company_id = 'menu_company_id';
	}

	public function list_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$select_array = array('menu_id ', 'menu_title', 'menu_status', 'menu_slug', 'menu_created_on');
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

				$where = array("$this->primary_key !=" => '', $this->company_id => $company_id);
				if (!empty($status)) {
					$where = array_merge($where, array('menu_status' => $status));
				}
				if (!empty($name)) {
					$like = array("menu_title" => $name);
				}

				$total_records = $this->Mydb->get_num_rows($this->primary_key, $this->table, $where, null, null, null, $like);

				$totalPages = (!empty($limit)) ? ceil($total_records / $limit) : 0;

				$result = $this->Mydb->get_all_records($select_array, $this->table, $where, $limit, $offset,  array(
					$this->primary_key => 'DESC'
				),  $like, array($this->primary_key));
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

	public function dropdownlist_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$company_id = decode_value($this->input->get('company_id'));
				$select_array = array(
					$this->primary_key . ' AS value',
					'menu_title AS label',
				);
				$where = array(
					$this->company_id => $company_id,
					'menu_status' => 'A'
				);
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
				$this->form_validation->set_rules('menu_title', 'Title', 'required|callback_menutitle_exists');
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
				$this->form_validation->set_rules('menu_title', 'Title', 'required|callback_menutitle_exists');
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
					$get_company_details = $this->Mydb->get_record('company_unquie_id', 'company', array('company_id' => $company_id));
					$where = array(
						$this->primary_key => trim($delete_id)
					);
					$result = $this->Mydb->get_record($this->primary_key . ', menu_title', $this->table, $where);
					if (!empty($result)) {
						$this->Mydb->delete($this->table, array($this->primary_key => $result[$this->primary_key]));

						createAuditLog("Menu Group", stripslashes($result['menu_title']), "Delete", $company_admin_id, 'Web', '', $company_id, $get_company_details['company_unquie_id']);

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
		$title = post_value('menu_title');
		$data = array(
			'menu_title'	=> $title,
			'menu_status' 	=> post_value('status'),
		);
		$company_id = decode_value($this->input->post('company_id'));
		$company_admin_id = decode_value($this->input->post('company_admin_id'));
		$getCompanyDetails = getCompanyUniqueID($company_id);
		if ($action == 'add') {
			$menu_slug = make_slug($title, $this->table, 'menu_slug', array("$this->company_id !=" => $company_id));
			$data = array_merge(
				$data,
				array(
					'menu_company_id' 	=> $company_id,
					'menu_unquie_id' 	=> (!empty($getCompanyDetails)) ? $getCompanyDetails : '',
					'menu_slug'			=> $menu_slug,
					'menu_created_on'	=> current_date(),
					'menu_created_by' 	=> $company_admin_id,
					'menu_created_ip' 	=> get_ip()
				)
			);

			$this->Mydb->insert($this->table, $data);
			createAuditLog("Menu Group", stripslashes(post_value('menu_title')), "Add", $company_admin_id, 'Web', '', $company_id, $getCompanyDetails);
		} else {
			$menu_slug = make_slug($title, $this->table, 'menu_slug', array("$this->company_id !=" => $company_id, "$this->primary_key !=" => $edit_id));
			$data = array_merge(
				$data,
				array(
					'menu_slug'			=> $menu_slug,
					'menu_updated_on'	=> current_date(),
					'menu_updated_by' 	=> $company_admin_id,
					'menu_updated_ip' 	=> get_ip()
				)
			);
			$this->Mydb->update($this->table, array($this->primary_key => $edit_id), $data);
			createAuditLog("Menu Group", stripslashes(post_value('menu_title')), "Updated", $company_admin_id, 'Web', '', $company_id, $getCompanyDetails);
		}
	}

	public function menutitle_exists()
	{
		$menu_title = $this->input->post('menu_title');
		$edit_id = $this->input->post('edit_id');
		$company_id = decode_value($this->input->post('company_id'));
		$where = array(
			'menu_title' => trim($menu_title),
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
			$this->form_validation->set_message('menutitle_exists', sprintf(get_label('alredy_exist'), 'Title'));
			return false;
		} else {
			return true;
		}
	}
} /* end of files */
