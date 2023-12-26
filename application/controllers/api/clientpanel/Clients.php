<?php

/**************************
 Project Name	: Pos
Created on		: 31 Aug, 2016
Last Modified 	: 31 Aug, 2016
Description		: Loyalty details

 ***************************/
defined('BASEPATH') or exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions

require APPPATH . '/libraries/REST_Controller.php';

class Clients extends REST_Controller
{

	function __construct()
	{

		parent::__construct();
		$this->table = "company";
		$this->user_table = "company_user";
		$this->login_history_table = "company_admin_login_history";
		$this->company_availability_table = "company_availability";
		$this->client_payment_history = "client_payment_history";
		$this->av_table = "availability";
		$this->settings = "company_settings";
		$this->primary_key = 'company_id';
		$this->label = "Client";
		$this->load->library('form_validation');
		$this->load->library('Authorization_Token');
		$this->form_validation->set_error_delimiters('<p>', '</p>');
		$this->load->library(array(
			'stripepv4',
			'stripeps'
		));
	}

	public function login_post()
	{

		$error_tag = $this->post('error_element');

		$username =  trim($this->input->post('username'));
		$password =  trim($this->input->post('password'));
		$allowaccess = (!empty($this->input->post('allowaccess'))) ? $this->input->post('allowaccess') : '';

		$this->form_validation->set_rules('username', 'Username', 'required|trim');
		$this->form_validation->set_rules('password', 'Password', 'required|min_length[' . PASSWORD_LENGTH . ']|trim');

		if ($this->form_validation->run() == true) {

			if ($allowaccess == "masteradmin") {

				$check_details = $this->Mydb->get_record(' master_admin_username as user_lname, master_admin_password as user_password, master_admin_status as company_user_status', "master_admin", array('master_admin_username' => $username));

				$check_details2 = $this->Mydb->get_record('company_user_fname,company_user_id,company_user_unquie_id,company_user_company_id,company_user_type,company_user_group_id', $this->user_table, array('company_user_type' => 'MainAdmin', 'company_username' => $username));

				$check_details = array_merge($check_details, $check_details2);
			} else {

				$check_details = $this->Mydb->get_record('company_user_id ,company_user_fname,company_user_lname,company_user_unquie_id,company_user_company_id,company_username,company_user_email_address,company_user_status,company_user_password as master_admin_password,company_user_type,company_user_group_id,company_user_permission_outlet', $this->user_table, array('company_username' => $username));
			}

			$session_datas = array();

			if ($check_details) {

				$password_verify = check_hash($password, $check_details['master_admin_password']);

				if ($password_verify === "Yes") {

					if ($check_details['company_user_status'] == 'A') {

						$user_permission_outlet = (!empty($check_details['company_user_permission_outlet'])) ? $check_details['company_user_permission_outlet'] : '';
						$user_fname = (!empty($check_details['company_user_fname'])) ? $check_details['company_user_fname'] : '';

						$company = $this->Mydb->get_record('company_id, company_unquie_id,company_date_format,company_time_format,company_currency,company_country,company_language,company_records_perpage', 'pos_company', array('company_unquie_id' => $check_details['company_user_unquie_id'], 'company_unquie_id !=' => "", 'company_status' => 'A'));

						// /* store last login details...*/
						$this->Mydb->insert($this->login_history_table, array('login_time' => current_date(), 'login_ip' => get_ip(), 'login_client_id' => $check_details['company_user_id']));


						/* update Company folder start here .. */
						$company_folder = $this->Mydb->get_record('company_folder_name,company_name', 'pos_company', array('company_id' => $check_details['company_user_company_id'], 'company_unquie_id' => $check_details['company_user_unquie_id']));


						if (isset($company_folder['company_folder_name'])) {
							create_folder($company_folder['company_folder_name']);
						}

						$session_datas = array(
							'company_id' => $check_details['company_user_company_id'],
							'company_unquie_id' => $check_details['company_user_unquie_id'],
							'company_firstlast_name' => stripslashes($user_fname . (!empty($check_details['company_user_fname']) ? " " . $check_details['company_user_lname'] : "")),
							'admin_username' => $check_details['company_username'],
							'company_admin_id' => $check_details['company_user_id'],
							'admin_type' => $check_details['company_user_type'],
							'admin_language' => $company['company_language'],
							'admin_country' => $company['company_country'],
							'admin_currency' => $company['company_currency'],
							'admin_dateformat' => $company['company_date_format'],
							'admin_timeformat' => $company['company_time_format'],
							//'admin_brand_enabled'=>$company['company_brand_enable'],
							'admin_records_perpage' => $company['company_records_perpage'],
							'user_permission_outlet' => $user_permission_outlet,
							'company_folder' => $company_folder['company_folder_name'],
							'company_name' => $company_folder['company_name'],
						);

						/*if ($check_details['company_user_type'] == "SubAdmin") {

							$query = " SELECT m.module_slug,m.module_id, ump.permission_action_add, ump.permission_action_edit, ump.permission_action_view, ump.permission_action_delete, ump.permission_action_full FROM pos_company_user_module_permission as ump,
											 pos_company_modules as m WHERE ump.permission_group_id = " . $this->db->escape($check_details['company_user_group_id']) . " AND ump.permission_company_id = " . $this->db->escape($check_details['company_user_company_id']) . "
											 AND ump.permission_module_id = m.module_id AND m.module_exclude = 'No'	";

							$module_result = $this->Mydb->custom_query($query);

							if (!empty($module_result)) {

								$action_permission = array();
								foreach ($module_result as $allowed) {
									if ($allowed['permission_action_add'] == 1) {
										$action_permission[$allowed['module_slug']][] = "add";
									}
									if ($allowed['permission_action_edit'] == 1) {
										$action_permission[$allowed['module_slug']][] = "edit";
									}
									if ($allowed['permission_action_view'] == 1) {
										$action_permission[$allowed['module_slug']][] = "view";
									}
									if ($allowed['permission_action_delete'] == 1) {
										$action_permission[$allowed['module_slug']][] = "delete";
									}
									if ($allowed['permission_action_full'] == 1) {
										$action_permission[$allowed['module_slug']][] = "full";
									}
								}

								if (!empty($action_permission)) {

									$session_datas['camp_module_action'] =  $action_permission;
								}

								$module_slug = array_column($module_result, 'module_slug');
								$this->session->set_userdata('camp_module_permission', $module_slug);
								$session_datas['camp_module_permission'] =  $module_slug;
							} else if (empty($module_result) && $check_details['company_user_type'] == "SubAdmin") {

								$this->set_response(array('status' => 'error', 'message' => get_label('access_denied')), something_wrong());
							}
						} else {*/

							$token_data['uid'] = $check_details['master_admin_id'];
							$token_data['username'] = $check_details['master_admin_username'];
							$tokenData = $this->authorization_token->generateToken($token_data);

							$return_array = array('status' => "ok", 'message' => get_label('reset_login_success'), 'result' => $session_datas, 'token' => $tokenData);
							$this->set_response($return_array, success_response());
						/*}*/
					}
				} else {
					/*invalid login detail*/
					$this->set_response(array('status' => 'error', 'message' => get_label('rest_invalid_password'), 'form_error' => ''), something_wrong());
				}
			} else {

				/*invalid username*/
				$this->set_response(array('status' => 'error', 'message' => get_label('rest_username_not_found'), 'form_error' => ''), something_wrong());
			}
		} else {

			/* custom error element */
			if ($error_tag != "") {
				$this->form_validation->set_error_delimiters('<' . $error_tag . '>', '</' . $error_tag . '>');
			}

			$this->set_response(array(
				'status' => 'error', 'message' => get_label('rest_form_error'), 'form_error' => validation_errors()
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
				$where = array($this->primary_key => $company_id);
				$result = $this->Mydb->get_record('*', $this->table, $where);
				if (!empty($result)) {
					$setting = client_validation($company_id);
					$result = array_merge($result, $setting);
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
	public function update_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$this->form_validation->set_rules('company_name', 'lang:company_name', 'required');
				$this->form_validation->set_rules('company_site_url', 'lang:company_site_url', 'required');
				/* |callback_subcategory_exists */
				/* $this->form_validation->set_rules('status', 'lang:status', 'required'); */
				if ($this->form_validation->run() == TRUE) {
					$edit_id = decode_value($this->input->post('company_id'));
					$company_admin_id = decode_value($this->input->post('company_admin_id'));
					$updArray = array(
						'company_name' => post_value('company_name'),
						'company_site_url' => post_value('company_site_url'),
						'company_logo' => post_value('company_logo'),
						'company_owner_name' => post_value('company_owner_name'),
						'company_postal_code' => post_value('company_postal_code'),
						'company_unit_number' => post_value('company_unit_no'),
						'company_floor_number' => post_value('company_floor_no'),
						'company_address' => post_value('company_address'),
						'company_contact_number' => post_value('company_contact_number'),
						'company_email_address' => post_value('company_email'),
						'company_updated_on' => current_date(),
						'company_updated_by' => $company_admin_id,
						'company_updtaed_ip' => get_ip()
					);
					$this->Mydb->update($this->table, array($this->primary_key => $edit_id), $updArray);

					$setting = $this->Mydb->get_record('setting_value', $this->settings, array('company_id' => $edit_id));
					$settingArray = array();
					if (!empty($setting) && !empty($setting['setting_value'])) {
						$settingArray = json_decode($setting['setting_value'], true);
					}
					$settingArray['company_max_order_handle'] = post_value('company_max_order_handle');
					$settingArray['company_tax_type'] = post_value('company_tax_type');
					$settingArray['company_tax_percentage'] = post_value('company_tax_percentage');
					$settingArray['company_gst_no'] = post_value('company_gst_no');
					$settingArray['company_invoice_logo'] = post_value('company_invoice_logo');
					$settingArray['enable_promotion_code_popup'] = post_value('enable_promotion_code_popup');
					$settingArray['enable_normal_popup'] = post_value('enable_normal_popup');
					$settingArray['first_time_order_promotion'] = post_value('first_time_order_promotion');
					$settingArray['new_signup_promotion'] = post_value('new_signup_promotion');
					$settingArray['company_reward_point'] = post_value('company_reward_point');
					$settingArray['company_review_point'] = post_value('company_review_point');
					$settingArray['loyalty_percentage'] = post_value('loyalty_percentage');
					$settingArray['loyalty_percentage'] = post_value('loyalty_percentage');
					$settingArray['loyalty_expiryon'] = post_value('loyalty_expiryon');
					$settingArray['social_media'] = (post_value('social_media') != "") ? stripcslashes(post_value('social_media')) : '';
					$settingArray['email_from_name'] = post_value('email_from_name');
					$settingArray['admin_email'] = post_value('admin_email');
					$settingArray['order_notification_email'] = post_value('order_notification_email');
					$settingArray['email_footer_content'] = post_value('email_footer_content');
					$settingArray['email_setting_type'] = post_value('email_setting_type');
					$settingArray['from_email'] = post_value('from_email');
					$settingArray['smtp_host'] = post_value('smtp_host');
					$settingArray['smtp_username'] = post_value('smtp_username');
					$settingArray['smtp_password'] = post_value('smtp_password');
					$settingArray['smtp_port'] = post_value('smtp_port');
					$settingArray['smtp_mail_path'] = post_value('smtp_mail_path');
					$settingArray['enable_maintenance_mode'] = post_value('enable_maintenance_mode');
					$settingArray['maintenance_mode_description'] = post_value('maintenance_mode_description');
					$settingArray['assign_availability'] = post_value('assign_availability');

					$this->Mydb->update($this->settings, array($this->primary_key => $edit_id), array('setting_value' => json_encode($settingArray)));
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
} /* end of files */
