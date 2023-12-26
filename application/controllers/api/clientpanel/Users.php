<?php

/**************************
Project Name	: White Label
Created on		: 01 Sep, 2023
Last Modified 	: 01 Sep, 2023
Description		: Ordering Panel Login details

 ***************************/
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Users extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p>', '</p>');
		$this->table = "company_user";
		$this->user_groups = "company_user_groups";
		$this->outlet_management = "outlet_management";
		$this->load->library('common');
		$this->label = get_label('a_users_label');
		$this->load->library('Authorization_Token');
		$this->primary_key = 'company_user_id';
		$this->company_id = 'company_user_company_id';
	}

	public function list_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$select_array = array('company_user_id', "CONCAT(company_user_fname, ' ', company_user_lname) AS name", 'company_username', 'company_user_email_address', 'company_user_status', 'company_user_created_on');
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
				$email = $this->input->get('email');
				$status = $this->input->get('status');
				$userrole = $this->input->get('userrole');
				$where = array("$this->primary_key !=" => '', $this->company_id => $company_id, 'company_user_type' => 'SubAdmin');
				if (!empty($status)) {
					$where = array_merge($where, array('company_user_status' => $status));
				}
				if (!empty($userrole)) {
					$where = array_merge($where, array('company_user_group_id' => $userrole));
				}
				if (!empty($email)) {
					$like = array("company_user_email_address" => $email);
				}
				if (!empty($name)) {
					$where = array_merge($where, array("(company_user_fname LIKE '%" . $name . "%' OR company_user_lname LIKE '%" . $name . "%')" => NULL));
				}

				$order_by = array($this->primary_key => 'DESC');

				$i = 0;
				$join[$i]['select'] = "usergroup_name";
				$join[$i]['table'] = $this->user_groups;
				$join[$i]['condition'] = "company_user_group_id = usergroup_id";
				$join[$i]['type'] = "INNER";
				$i++;

				$join[$i]['select'] = "outlet_name";
				$join[$i]['table'] = $this->outlet_management;
				$join[$i]['condition'] = "company_user_permission_outlet = outlet_id";
				$join[$i]['type'] = "LEFT";

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
					$result['user_role'] = $this->Mydb->get_record('usergroup_id AS value, usergroup_name AS label', $this->user_groups, array('usergroup_id' => $result['company_user_group_id']));

					$result['outlet'] = $this->Mydb->get_record('outlet_id AS value, outlet_name AS label', $this->outlet_management, array('outlet_id' => $result['company_user_permission_outlet']));

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
				$this->form_validation->set_rules('first_name', 'lang:first_name', 'required');
				$this->form_validation->set_rules('user_name', 'lang:rest_login_bpanel_username', 'required|callback_username_exists');
				$this->form_validation->set_rules('password', 'lang:rest_login_bpanel_password', 'required|min_length[6]');
				$this->form_validation->set_rules('email', 'lang:res_email', 'required|callback_email_exists');
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

				$this->form_validation->set_rules('first_name', 'lang:first_name', 'required');
				if (post_value('password') != "") {
					$this->form_validation->set_rules('password', 'lang:rest_login_bpanel_password', 'min_length[6]');
				}
				$this->form_validation->set_rules('email', 'lang:res_email', 'required|callback_email_exists');
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

	public function updatepassword_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$this->form_validation->set_rules('company_admin_id', 'lang:user_id', 'required');
				$this->form_validation->set_rules('oldpassword', 'lang:rest_oldpassword_required', 'required');
				$this->form_validation->set_rules('password', 'lang:rest_login_bpanel_password', 'required|min_length[6]');
				if ($this->form_validation->run() == TRUE) {
					$loginID = decode_value(post_value('company_admin_id'));
					$company_id = decode_value($this->input->post('company_id'));
					$user = $this->Mydb->get_record('company_user_id, company_user_password', $this->table, array('company_user_id' => $loginID, 'company_user_company_id' => $company_id));
					if (!empty($user)) {

						$oldpassword = post_value('oldpassword');
						$password_verify = check_hash($oldpassword, $user['company_user_password']);
						if ($password_verify == 'Yes') {
							$password = do_bcrypt($this->input->post('password'));
							$this->Mydb->update($this->table, array($this->primary_key => $user['company_user_id']), array('company_user_password' => $password, 'company_user_password_key' => ''));

							$this->set_response(array(
								'status' => 'success',
								'message' => get_label('reset_password_update'),
								'form_error' => '',
							), success_response()); /* success message */
						} else {
							$this->set_response(array(
								'status' => 'error',
								'message' => get_label('rest_old_password_notmatch'),
								'form_error' => ''
							), something_wrong()); /* error message */
						}
					} else {
						$this->set_response(array(
							'status' => 'error',
							'message' => get_label('invalid_user'),
						), something_wrong()); /* error message */
					}
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

	public function addedit($edit_id = null)
	{
		if (!empty($edit_id)) {
			$edit_id = decode_value($edit_id);
		}
		$action = post_value('action');
		$data = array(
			'company_user_fname' 		=> post_value('first_name'),
			'company_user_lname'		=> post_value('last_name'),
			'company_user_email_address' => post_value('email'),
			'company_user_group_id' 	=> post_value('user_role'),
			'company_user_permission_outlet' => post_value('outlet_id'),
			'company_user_profile_picture' => post_value('profile_picture'),
			'company_user_status' 		=> ($this->input->post('status') == "A" ? 'A' : 'I')
		);

		if ($action == 'add') {
			$company_id = decode_value($this->input->post('company_id'));
			$company_admin_id = decode_value($this->input->post('company_admin_id'));
			$getCompanyDetails = getCompanyUniqueID($company_id);
			$data = array_merge(
				$data,
				array(
					'company_username' 	=> post_value('user_name'),
					'company_user_password' => do_bcrypt($this->input->post('password')),
					'company_user_type' => 'SubAdmin',
					'company_user_company_id' => $company_id,
					'company_user_unquie_id' => (!empty($getCompanyDetails)) ? $getCompanyDetails : '',
					'company_user_created_on' => current_date(),
					'company_user_created_by' => $company_admin_id,
					'company_user_created_ip' => get_ip()
				)
			);

			$this->Mydb->insert($this->table, $data);
		} else {
			$password = post_value('password');
			if (!empty($password)) {
				$data = array_merge(
					$data,
					array('company_user_password' => do_bcrypt($this->input->post('password')))
				);
			}

			$data = array_merge(
				$data,
				array(
					'company_user_updated_on' => current_date(),
					'company_user_updated_by' => $company_admin_id,
					'company_user_updated_ip' => get_ip()
				)
			);
			$this->Mydb->update($this->table, array($this->primary_key => $edit_id), $data);
		}
	}
	public function username_exists()
	{
		$user_name = $this->input->post('user_name');
		$edit_id = $this->input->post('edit_id');
		$company_id = decode_value($this->input->post('company_id'));
		$where = array(
			'company_username' => trim($user_name),
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
			$this->form_validation->set_message('username_exists', sprintf(get_label('alredy_exist'), get_label("rest_login_bpanel_username")));
			return false;
		} else {
			return true;
		}
	}

	public function email_exists()
	{
		$email = $this->input->post('email');
		$edit_id = $this->input->post('edit_id');
		$company_id = decode_value($this->input->post('company_id'));
		$where = array(
			'company_user_email_address' => trim($email),
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
			$this->form_validation->set_message('email_exists', sprintf(get_label('alredy_exist'), get_label('res_email')));
			return false;
		} else {
			return true;
		}
	}
} /* end of files */
