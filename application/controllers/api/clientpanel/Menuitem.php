<?php

/**************************
Project Name	: White Label
Created on		: 18 Aug, 2023
Last Modified 	: 18 Aug, 2023
Description		: Menu Item details

 ***************************/
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Menuitem extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p>', '</p>');
		$this->table = "navigation";
		$this->menu_table = "menu";
		$this->load->library('common');
		$this->label = "Menu Item";
		$this->load->library('Authorization_Token');
		$this->primary_key = 'nav_id';
		$this->company_id = 'nav_company_id';
	}

	public function list_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$select_array = array('nav_id ', 'nav_title', 'nav_status', 'nav_position', 'nav_parent_title', 'menu_created_on');
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
				$menu_group = $this->input->get('menu_group');
				$where = array("$this->primary_key !=" => '', $this->company_id => $company_id);
				if (!empty($status)) {
					$where = array_merge($where, array('nav_status' => $status));
				}
				if (!empty($menu_group)) {
					$where = array_merge($where, array('menu_id' => $menu_group));
				}
				if (!empty($name)) {
					$like = array("nav_title" => $name);
				}

				$join = array();
				$join[0]['select'] = "menu_title";
				$join[0]['table'] = $this->menu_table;
				$join[0]['condition'] = "menu_id = nav_group";
				$join[0]['type'] = "INNER";

				$total_records = $this->Mydb->get_num_join_rows($this->primary_key, $this->table, $where, null, null, null, $like, null, $join);

				$totalPages = (!empty($limit)) ? ceil($total_records / $limit) : 0;

				$result = $this->Mydb->get_all_records($select_array, $this->table, $where, $limit, $offset,  array(
					$this->primary_key => 'DESC'
				), $like, array($this->primary_key), $join);
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
					'nav_title AS label',
				);
				$where = array(
					$this->company_id => $company_id,
					'nav_status' => 'A'
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

					$navGroup = $this->Mydb->get_record('menu_id AS value, menu_title As label', $this->menu_table, array('menu_id' => $result['nav_group']));

					$arentMenu = $this->Mydb->get_record('nav_company_id AS value, nav_title As label', $this->table, array($this->primary_key => $result['nav_parent_title']));

					$result = array_merge($result, array('menu_group' => $navGroup, 'nav_parent_menu' => $arentMenu));

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

				$this->form_validation->set_rules('nav_title', 'Title', 'required|callback_menu_exists');
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
				$this->form_validation->set_rules('nav_title', 'Title', 'required|callback_menu_exists');
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
					$result = $this->Mydb->get_record($this->primary_key . ', nav_title', $this->table, $where);
					if (!empty($result)) {
						$this->Mydb->delete($this->table, array($this->primary_key => $result[$this->primary_key]));

						createAuditLog("Menu Item", stripslashes($result['nav_title']), "Delete", $company_admin_id, 'Web', '', $company_id, $getCompanyDetails);

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
		$title = post_value('nav_title');
		$data = array(
			'nav_group'			=> post_value('nav_group'),
			'nav_title'			=> $title,
			'nav_parent_title'	=> post_value('nav_parent_title'),
			'nav_type'			=> post_value('menu_type'),
			'nav_pages'			=> post_value('custom_link'),
			'nav_icon'			=> post_value('nav_icon'),
			'nav_link_type'		=> post_value('nav_link_type'),
			'nav_position'		=> post_value('nav_position'),
			'nav_status' 		=> post_value('status'),
		);
		$nav_icon = post_value('nav_icon');
		if (!empty($nav_icon)) {
			$data = array_merge($data, array('nav_icon' => $nav_icon));
		}

		$company_id = decode_value($this->input->post('company_id'));
		$company_admin_id = decode_value($this->input->post('company_admin_id'));
		$getCompanyDetails = getCompanyUniqueID($company_id);
		if ($action == 'add') {

			$menu_slug = make_slug($title, $this->table, 'nav_title_slug', array($this->company_id => $company_id));
			$data = array_merge(
				$data,
				array(
					'nav_title_slug' => $menu_slug,
					'nav_company_id' => $company_id,
					'nav_app_id' 	=> (!empty($getCompanyDetails)) ? $getCompanyDetails : '',
					'nav_created_on' => current_date(),
					'nav_created_by' => $company_admin_id,
					'nav_created_ip' => get_ip()
				)
			);

			$this->Mydb->insert($this->table, $data);

			createAuditLog("Menu Item", stripslashes(post_value('nav_title')), "Add", $company_admin_id, 'Web', '', $company_id, $getCompanyDetails);
		} else {
			$menu_slug = make_slug($title, $this->table, 'nav_title_slug', array("$this->primary_key !=" => $edit_id, $this->company_id => $company_id));
			$data = array_merge(
				$data,
				array(
					'nav_title_slug' => $menu_slug,
					'nav_updated_on' => current_date(),
					'nav_updated_by' => $company_admin_id,
					'nav_updated_ip' => get_ip()
				)
			);
			$this->Mydb->update($this->table, array($this->primary_key => $edit_id), $data);
			createAuditLog("Menu Item", stripslashes(post_value('nav_title')), "Update", $company_admin_id, 'Web', '', $company_id, $getCompanyDetails);
		}
	}
	public function menu_exists()
	{
		$nav_title = $this->input->post('nav_title');
		$nav_group = $this->input->post('nav_group');
		$edit_id = $this->input->post('edit_id');
		$company_id = decode_value($this->input->post('company_id'));
		$where = array(
			'nav_title' => trim($nav_title),
			'nav_group' => $nav_group,
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
			$this->form_validation->set_message('menu_exists', sprintf(get_label('alredy_exist'), 'Title'));
			return false;
		} else {
			return true;
		}
	}
} /* end of files */
