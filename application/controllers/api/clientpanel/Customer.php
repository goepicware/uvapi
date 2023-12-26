<?php

/**************************
Project Name	: White Label
Created on		: 19 Aug, 2023
Last Modified 	: 28 Aug, 2023
Description		: Customer details

 ***************************/
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Customer extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p>', '</p>');
		$this->table = "customers";
		$this->load->library('common');
		$this->label = "Customer";
		$this->load->library('Authorization_Token');
		$this->primary_key = 'customer_id';
		$this->company_id = 'customer_company_id';
	}

	public function list_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$select_array = array('customer_id', "CONCAT(customer_first_name, ' ', customer_last_name) AS customer_name", 'customer_email', 'customer_phone', 'customer_status', 'customer_created_on');
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
				$phone = $this->input->get('phone');
				$status = $this->input->get('status');
				$where = array("$this->primary_key !=" => '', $this->company_id => $company_id);
				if (!empty($name)) {
					$where = array_merge($where, array("(customer_first_name LIKE '%" . $name . "%' OR customer_last_name LIKE '%" . $name . "%')" => NULL));
				}
				if (!empty($email)) {
					$like = array_merge($like, array("customer_email" => $email));
				}
				if (!empty($phone)) {
					$like = array_merge($like, array("customer_phone" => $phone));
				}
				if (!empty($status)) {
					$where = array_merge($where, array('customer_status' => $status));
				}
				$order_by = array($this->primary_key => 'DESC');
				$total_records = $this->Mydb->get_num_rows($this->primary_key, $this->table, $where, null, null, null, $like);


				$totalPages = (!empty($limit)) ? ceil($total_records / $limit) : 0;

				$result = $this->Mydb->get_all_records($select_array, $this->table, $where, $limit, $offset,  $order_by, $like, array($this->primary_key));
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
					"CONCAT(customer_first_name, ' ', customer_last_name, ' (', customer_email, ')') AS label",
				);
				$where = array(
					$this->company_id => $company_id
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
				$this->form_validation->set_rules('first_name', 'lang:first_name', 'required');
				$this->form_validation->set_rules('password', 'lang:rest_login_bpanel_password', 'required|min_length[6]');
				$this->form_validation->set_rules('email', 'lang:res_email', 'required|callback_customeremail_exists');
				$this->form_validation->set_rules('phone', 'lang:res_phone_no', 'required|callback_customerphone_exists');
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
				$this->form_validation->set_rules('email', 'lang:res_email', 'required|callback_customeremail_exists');
				$this->form_validation->set_rules('phone', 'lang:res_phone_no', 'required|callback_customerphone_exists');
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
			'customer_first_name' => post_value('first_name'),
			'customer_last_name' => post_value('last_name'),
			'customer_email' => post_value('email'),
			'customer_phone' => post_value('phone'),
			'customer_type' => post_value('customer_type'),
			'customer_birthdate' => post_value('birthdate'),
			'customer_status' => ($this->input->post('status') == "A" ? 'A' : 'I'),
		);

		if ($action == 'add') {
			$company_id = decode_value($this->input->post('company_id'));
			$company_admin_id = decode_value($this->input->post('company_admin_id'));
			$getCompanyDetails = getCompanyUniqueID($company_id);
			$data = array_merge(
				$data,
				array(
					'customer_password' => do_bcrypt($this->input->post('password')),
					'customer_company_id' 		=> $company_id,
					'customer_unquie_app_id' 	=> (!empty($getCompanyDetails)) ? $getCompanyDetails : '',
					'customer_created_on'		=> current_date(),
					'customer_created_by' 		=> $company_admin_id,
					'customer_updated_ip' 		=> get_ip()
				)
			);

			$this->Mydb->insert($this->table, $data);
		} else {
			$password = post_value('password');
			if (!empty($password)) {
				$data = array_merge(
					$data,
					array('customer_password' => do_bcrypt($this->input->post('password')))
				);
			}
			$data = array_merge(
				$data,
				array(
					'customer_updated_on'	=> current_date(),
					'customer_updated_by' 	=> $company_admin_id,
					'customer_updated_ip' 	=> get_ip()
				)
			);
			$this->Mydb->update($this->table, array($this->primary_key => $edit_id), $data);
		}
	}

	public function import_post()
	{

		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$this->form_validation->set_rules('cusatomer_file', 'lang:import_file', 'callback_validate_file');
				if ($this->form_validation->run() == TRUE) {

					$company_id = decode_value($this->input->post('company_id'));
					$company_admin_id = decode_value($this->input->post('company_admin_id'));
					$getCompanyDetails = getCompanyUniqueID($company_id);

					if (pathinfo($_FILES['cusatomer_file']['name'], PATHINFO_EXTENSION) == 'csv') {
						if ($_FILES['cusatomer_file']['name'] != '') {
							$handle = fopen($_FILES["cusatomer_file"]["tmp_name"], "r");
							$i = 0;
							$totalImport = 0;
							while (($line_of_text = fgetcsv($handle, 2000, ",")) !== FALSE) {
								if ($i > 0) {
									$firstName = $line_of_text[0];
									$lastName = $line_of_text[1];
									$email = $line_of_text[2];
									$password =  $line_of_text[3];
									$phone = $line_of_text[4];
									$dob = (!empty($line_of_text[5])) ? date('Y-m-d', strtotime($line_of_text[5])) : '';
									$status = ($line_of_text[6] == 'A') ? 'A' : 'I';
									if (!empty($firstName) && !empty($email) && !empty($phone) && !empty($password)) {
										$enchPwd = do_bcrypt($password);

										$checkingEmail = $this->Mydb->get_record('customer_id', $this->table, array('customer_email' => $email, 'customer_company_id' => $company_id));
										$checkingPhone = $this->Mydb->get_record('customer_id', $this->table, array('customer_phone' => $phone, 'customer_company_id' => $company_id));
										if (empty($checkingEmail) && empty($checkingPhone)) {
											$data = array(
												'customer_first_name' => $firstName,
												'customer_last_name' => $lastName,
												'customer_email' => $email,
												'customer_phone' => $phone,
												'customer_type' => 'Normal',
												'customer_birthdate' => $dob,
												'customer_status' => $status,
												'customer_password' => $enchPwd,
												'customer_company_id' 		=> $company_id,
												'customer_unquie_app_id' 	=> (!empty($getCompanyDetails)) ? $getCompanyDetails : '',
												'customer_created_on'		=> current_date(),
												'customer_created_by' 		=> $company_admin_id,
												'customer_updated_ip' 		=> get_ip()
											);
											$this->Mydb->insert($this->table, $data);
											$totalImport++;
										}
									}
								}
								$i++;
							}
						}
					}
					if ($totalImport > 0) {
						$this->set_response(array(
							'status' => 'success',
							'message' => sprintf(get_label('success_message_import'), $this->label),
							'form_error' => '',
						), success_response()); /* success message */
					} else {
						$this->set_response(array(
							'status' => 'error',
							'message' => get_label('fail_import'),
							'form_error' => ''
						), something_wrong());
					}
				} else {
					$this->set_response(array(
						'status' => 'error',
						'message' => get_label('rest_form_error'),
						'form_error' => validation_errors()
					), something_wrong());
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

	public function customeremail_exists()
	{
		$email = $this->input->post('email');
		$edit_id = $this->input->post('edit_id');
		$company_id = decode_value($this->input->post('company_id'));
		$where = array(
			'customer_email' => trim($email),
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
			$this->form_validation->set_message('customeremail_exists', sprintf(get_label('alredy_exist'), get_label('res_email')));
			return false;
		} else {
			return true;
		}
	}
	public function customerphone_exists()
	{
		$phone = $this->input->post('phone');
		$edit_id = $this->input->post('edit_id');
		$company_id = decode_value($this->input->post('company_id'));
		$where = array(
			'customer_phone' => trim($phone),
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
			$this->form_validation->set_message('customerphone_exists', sprintf(get_label('alredy_exist'), get_label('res_phone_no')));
			return false;
		} else {
			return true;
		}
	}
	public function validate_file()
	{
		if (isset($_FILES['cusatomer_file']['name']) && $_FILES['cusatomer_file']['name'] != "") {
			if ($this->common->valid_file($_FILES['cusatomer_file']) == "No") {
				$this->form_validation->set_message('validate_file', get_label('upload_valid_csv_file'));
				return false;
			}
		}

		return true;
	}
} /* end of files */
