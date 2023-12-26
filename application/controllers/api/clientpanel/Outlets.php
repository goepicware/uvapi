<?php

/**************************
 Project Name	: White Label
Created on		: 01 Aug, 2023
Last Modified 	: 14 Aug, 2023
Description		: Outlet details
 ***************************/
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Outlets extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->table = "outlet_management";
		$this->client_table = "clients";
		$this->outlet_availability = "outlet_availability";
		$this->site_location = "site_location";
		$this->brands = "brands";
		$this->load->library('form_validation');
		$this->load->library('Authorization_Token');
		$this->form_validation->set_error_delimiters('<p>', '</p>');
		$this->load->library('common');
		$this->primary_key = 'outlet_id';
		$this->company_id = "outlet_company_id";
		$this->load->helper('businessapi');
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
					$company_id = decode_value($this->input->get('company_id'));
					$name = $this->input->get('name');
					$status = $this->input->get('status');
					$email = $this->input->get('email');
					$contactnumber = $this->input->get('contactnumber');
					$select_array = array(
						'outlet_id',
						'outlet_name',
						'outlet_slug',
						'outlet_email',
						'outlet_phone',
						'outlet_unit_number1',
						'outlet_unit_number2',
						'outlet_address_line1',
						'outlet_created_on',
						'outlet_availability',
						'outlet_postal_code',
						'outlet_sequence'
					);

					$limit = $offset =  '';
					$like =  array();
					$get_limit = $this->input->get('limit');
					$post_offset = (int) $this->input->get('offset');
					if ((int) $get_limit != 0) {
						$limit = (int) $get_limit;
					}
					$post_offset = ($post_offset > 1) ? $post_offset - 1 : 0;
					$offset = ($post_offset > 0 ? $post_offset * $limit : 0);

					$where = array(
						'outlet_company_id' => $company_id,
					);
					if ($status != "") {
						$where = array_merge($where, array('outlet_availability' => $status));
					}
					if (!empty($name)) {
						$like = array_merge($like, array("outlet_name" => $name));
					}
					if (!empty($email)) {
						$like = array_merge($like, array("outlet_email" => $email));
					}
					if (!empty($contactnumber)) {
						$like = array_merge($like, array("outlet_phone" => $contactnumber));
					}

					if (!empty($userDetails)) {
						if ($userDetails['company_user_type'] == 'SubAdmin') {
							$where = array_merge($where, array("outlet_id" => $userDetails['company_user_permission_outlet']));
						}
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
						'message' => get_label('invalid_user'),
						'form_error' => ''
					), something_wrong()); /* error message */
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
					$this->primary_key . ' AS value',
					'outlet_name AS label',
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
				$company_id = decode_value($this->input->get('company_id'));
				$company_admin_id = decode_value($this->input->get('company_admin_id'));
				$get_company_details = $this->Mydb->get_record('company_unquie_id', 'company', array('company_id' => $company_id));
				$id = decode_value($this->input->get('detail_id'));
				$where = array('outlet_id' => $id, 'outlet_company_id' => $company_id);
				$result = $this->Mydb->get_record('*', $this->table, $where);
				if (!empty($result)) {

					$join = array();
					$join[0]['select'] = "av_name";
					$join[0]['table'] = "availability";
					$join[0]['condition'] = "oa_availability_id = av_id";
					$join[0]['type'] = "INNER";
					$outlet_availability = $this->Mydb->get_all_records('oa_availability_id', 'outlet_availability', array('oa_outlet_id' => $id, 'oa_company_id' => $company_id), null, null, null, null, null, $join);
					$result['availability'] = $outlet_availability;

					$result['siteLocation']  = $this->Mydb->get_record('sl_location_id AS value, sl_name AS label', $this->site_location, array('sl_location_id' => $result['outlet_location_id']));

					$result['outlet_brand']  = $this->Mydb->get_record('brand_id AS value, brand_name AS label', $this->brands, array('brand_id' => $result['brand_id']));
					$result['assign_tags'] = array();
					if (!empty($result['outlet_tag_id'])) {
						$tagWhere = " tag_primary_id IN (" . $result['outlet_tag_id'] . ") AND tag_company_id=" . $company_id . "";
						$result['assign_tags']  = $this->Mydb->get_all_records('tag_primary_id AS value, tag_name AS label', 'outlet_tags', $tagWhere);
					}


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
				$this->form_validation->set_rules('outlet_name', 'lang:outlet_name', 'required|callback_outletnameexists');
				$this->form_validation->set_rules('outlet_postal_code', 'lang:outlet_postal_code', 'required');
				if ($this->form_validation->run() == TRUE) {
					$outlet_id = '';
					$this->outletaddedit($outlet_id);
					$this->set_response(array(
						'status' => 'success',
						'message' => "Outlet has been addded successfully",
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

	public function update_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$error_tag = $this->post('error_element');
				$this->form_validation->set_rules('outlet_name', 'lang:outlet_name', 'required|callback_outletnameexists');
				$this->form_validation->set_rules('outlet_postal_code', 'lang:outlet_postal_code', 'required');
				if ($this->form_validation->run() == TRUE) {
					$outlet_id = decode_value($this->input->post('edit_id'));
					$this->outletaddedit($outlet_id);
					$this->set_response(array(
						'status' => 'success',
						'message' => "Outlet has been updated successfully",
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
				$outlet_id = decode_value($this->input->post('delete_id'));
				if (!empty($company_id)) {
					$user_arr = array();
					$where = array(
						'outlet_id' => trim($outlet_id)
					);
					$result = $this->Mydb->get_record('*', $this->table, $where);
					if (!empty($result)) {
						$this->Mydb->delete($this->table, array('outlet_id' => $result['outlet_id'], 'outlet_company_id' => $company_id));
						$return_array = array('status' => "ok", 'message' => 'Oultet deleted successfully.',);
						$this->set_response($return_array, success_response());
					} else {
						$this->set_response(array('status' => 'error', 'message' => 'No Record Found.', 'form_error' => ''), something_wrong());
					}
				} else {
					$this->set_response(array('status' => 'error', 'message' => 'Oultet Id field is required', 'form_error' => ''), something_wrong());
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

	public function outletaddedit($outlet_id = null)
	{
		$action = post_value('action');

		/* Outlet tat time update */
		$outlet_availability = $this->input->post('availability');
		$outlet_availability_new = (!empty($outlet_availability)) ? explode(',', $outlet_availability) : [];


		$outlet_availability = (!empty($outlet_availability)) ? explode(',', $outlet_availability) : array();
		$outlet_dine_tat = $this->input->post('tat_time_dine');
		$outlet_delivery_tat = $this->input->post('tat_time_delivery');
		$outlet_pickup_tat = $this->input->post('tat_time_pickup');
		$outlet_informations 	= $this->input->post('outlet_informations', false);


		/*primary or secondary outlet*/
		$secondary_outlet = $this->input->post('secondary_outlet');

		$outlet_makedefault = '0';
		if (post_value('default_outlet') != '') {
			$outlet_makedefault = '1';
		}
		$outlet_restrict_callcenter = '0';
		if (post_value('outlet_restrict_callcenter') != '') {
			$outlet_restrict_callcenter = '1';
		}
		$outlet_menu_pdf = '';
		if (isset($_FILES['outlet_menu_pdf']['name']) && $_FILES['outlet_menu_pdf']['name'] != "") {
			$upload_image = $this->common->upload_image('outlet_menu_pdf', get_company_folder() . "/outlet");
			$outlet_menu_pdf = $upload_image;
		}

		$company_id = decode_value($this->input->post('company_id'));
		$company_admin_id = decode_value($this->input->post('company_admin_id'));
		$get_company_details = $this->Mydb->get_record('company_unquie_id', 'company', array('company_id' => $company_id));

		$Itemarray = array(
			'outlet_name' 			=> stripslashes(post_value('outlet_name')),
			'outlet_email' 			=> post_value('outlet_email'),
			'outlet_pos_id' 		=> post_value('outlet_pos_id'),
			'outlet_location_id'	=> post_value('siteLocation'),
			'brand_id'				=> post_value('brand_id'),
			'outlet_makedefault' 	=> $outlet_makedefault,
			'outlet_phone' 			=> post_value('outlet_phone'),
			'outlet_address_line1'	=> post_value('outlet_address_line1'),
			'outlet_address_line2'	=> post_value('outlet_address_line2'),
			'outlet_postal_code'	=> post_value('outlet_postal_code'),
			'outlet_unit_number1'	=> post_value('outlet_unit_number1'),
			'outlet_unit_number2'	=> post_value('outlet_unit_number2'),
			'outlet_delivery_timing' => post_value('outlet_delivery_timing'),
			'outlet_availability'	=> post_value('outlet_availability'),
			'outlet_created_on' 	=> current_date(),
			'outlet_log_update_on' 	=> current_date(),
			'outlet_created_by' 	=> $company_admin_id,
			'outlet_created_ip' 	=> get_ip(),
			'outlet_dine_tat' 		=> $outlet_dine_tat,
			'outlet_delivery_tat' 	=> post_value('outlet_delivery_tat'),
			'outlet_pickup_tat' 	=> post_value('outlet_pickup_tat'),
			'outlet_informations' 	=> $outlet_informations,
			'outlet_menu_pdf' 		=> $outlet_menu_pdf,
			'outlet_sequence' 		=> (int)post_value('outlet_sequence'),
			'outlet_tax_amount'		=> post_value('tax_surcharge'),
			'outlet_map_link' 		=> post_value('outlet_map_link'),
			'outlet_image'          => post_value('outlet_image'),
			'outlet_banner_image'  	=> post_value('outlet_banner_image'),
			'outlet_tag_id'			=> post_value('tag_id'),
			'outlet_time_info'		=> post_value('outlet_time_info'),
			'outlet_offer_info'		=> post_value('outlet_offer_info')
		);


		if ($action == 'add') {

			$Itemarray = array_merge($Itemarray,  array(
				'outlet_company_id' => $company_id,
				'outlet_unquie_id' => $get_company_details['company_unquie_id']
			));

			$outlet_array = array(
				'outlet_company_id' => $company_id,
				'outlet_unquie_id' => $get_company_details['company_unquie_id']
			);

			$outlet_slug = make_slug(stripslashes(post_value('outlet_name')), $this->table, 'outlet_slug', $outlet_array);
			$Itemarray = array_merge($Itemarray, array('outlet_slug' => $outlet_slug));

			$OutletID = $this->Mydb->insert($this->table, $Itemarray);
		} else if ($action == 'edit') {

			$this->Mydb->update($this->table, array($this->primary_key => $outlet_id), $Itemarray);
			$OutletID = $outlet_id;
		}
		if ($OutletID) {
			if ($action == 'edit') {
				$this->Mydb->delete($this->outlet_availability, array('oa_outlet_id' => $OutletID));
			}
			if (!empty($outlet_availability_new)) {
				$available_insert_array = array();
				foreach ($outlet_availability_new as $available) {
					$available_insert_array[] = array(
						'oa_company_id'		=> $company_id,
						'oa_company_app_id'	=> $get_company_details['company_unquie_id'],
						'oa_outlet_id'		=> $OutletID,
						'oa_availability_id' => $available,
						'oa_created_on' 	=> current_date(),
						'oa_created_by' 	=> $company_admin_id,
						'oa_created_ip' 	=> get_ip()
					);
				}
				if (!empty($available_insert_array)) {
					$this->db->insert_batch($this->outlet_availability, $available_insert_array);
				}
			}
		}
	}

	public function changeOutletStatus_post()
	{
		$company_id = decode_value($this->input->post('company_id'));
		$company = client_validation($company_id); /* validate app */
		$app_id = $company['company_unquie_id'];

		$error_tag = $this->post('error_element');

		/* add form validation */
		$this->form_validation->set_rules('outlet_id', 'lang:bp_outlet_id', 'required|trim');
		$this->form_validation->set_rules('outlet_status', 'lang:bp_outlet_status', 'required|trim');
		$this->form_validation->set_rules('logged_id', 'lang:bp_logged_id', 'required');
		if ($this->form_validation->run() == true) {

			$outlet_id =  trim($this->post('outlet_id'));
			$status =  ((int) post_value('outlet_status') == 1 ? 1 : 0);
			$admin_id =  trim($this->post('logged_id'));

			$validation = validate_outlet($app_id, $company_id, $outlet_id);

			if (!empty($validation)) {

				$where = array(
					'outlet_unquie_id' => $app_id,
					'outlet_company_id' => $company_id,
					'outlet_id' => $outlet_id
				);
				$update_values = array(
					'outlet_availability' => $status,
					'outlet_updated_on' => current_date(),
					'outlet_updated_by' => $admin_id,
					'outlet_updated_ip' => get_ip()
				);

				$this->Mydb->update($this->table, $where, $update_values);

				/* track outlet status - */
				$track_arr = array(
					'outlet_track_company_id' => $company_id,
					'outlet_track_app_id' => $app_id,
					'outlet_track_changed_status' => $status,
					'outlet_track_changed_by ' => $admin_id,
					'outlet_track_changed_date' => current_date(),
					'outlet_track_changed_ip' => get_ip(),
					'outlet_track_outlet_id' => $outlet_id,
					'outlet_track_changed_panel' => 'Businesspanel'
				);

				$this->Mydb->insert('outlet_status_tracking', $track_arr);

				/* track productivity log */
				$current_date = date("Y-m-d");
				$company_details = $this->Mydb->get_record('company_start_time,company_end_time', 'company', array(
					'company_id' => $company_id
				));
				if (!empty($company_details)) {

					$out_status = ($status == 1) ? "Open" : "Close";
					$entry_exists = $this->Mydb->get_record('productivity_log_type', 'productivity_log', array(
						"DATE_FORMAT(productivity_start_date,'%Y-%m-%d') = " => $current_date,
						'productivity_outlet_id' => $outlet_id, 'productivity_log_for' => 'SHOUTDOWN'
					));

					$db_status = ($validation['outlet_availability'] == 1) ? "Open" : 'Close';

					if ($company_details['company_start_time'] != "" && $company_details['company_end_time'] != "") {
						$start_time = date("Y-m-d G:i:s", strtotime($current_date . " " . $company_details['company_start_time']));
					} else {
						$start_time = current_date();
					}


					/* if no entry in table add first entry and update time diff */
					$first_entry_id = "";
					if (empty($entry_exists)) {

						$first_entry_id  = $this->insert_tat($app_id, $company_id, $admin_id, $outlet_id, $validation['outlet_availability'], $start_time, '', $db_status, 'SHOUTDOWN');

						$to_time1 = strtotime(current_date());
						$from_time1 = strtotime($start_time);
						$diff_time1 = round(abs($to_time1 - $from_time1) / 60, 2);

						$this->Mydb->update('productivity_log', array(
							'productivity_log_id' => $first_entry_id,
							'productivity_outlet_id' => $outlet_id,
							'productivity_log_type' => $db_status,
							'productivity_log_for' => 'SHOUTDOWN'
						), array(
							'productivity_end_date' => current_date(),
							'productivity_time_diff' => $diff_time1
						));
					}

					$log_id = $this->insert_tat($app_id, $company_id, $admin_id, $outlet_id, $status, current_date(), '', $out_status, 'SHOUTDOWN');

					/* if already first entry in tabel.. update time diff.. */
					if ($first_entry_id == "") {

						$find_end = $this->Mydb->get_record('productivity_log_id,productivity_start_date', 'productivity_log', array(
							'productivity_log_id !=' => $log_id,
							'productivity_outlet_id' => $outlet_id,
							'productivity_log_for' => 'SHOUTDOWN'
						), array(
							'productivity_log_id' => 'DESC'
						), 1);


						if (!empty($find_end)) {

							$to_time = strtotime(current_date());
							$from_time = strtotime($find_end['productivity_start_date']);
							$diff_time = round(abs($to_time - $from_time) / 60, 2);

							$this->Mydb->update('productivity_log', array(
								'productivity_log_id' => $find_end['productivity_log_id'],
								'productivity_outlet_id' => $outlet_id,
								'productivity_log_type' => $db_status,
								'productivity_log_for' => 'SHOUTDOWN'
							), array(
								'productivity_end_date' => current_date(),
								'productivity_time_diff' => $diff_time
							));
						}
					}
				}

				$return_array = array('status' => "ok", 'message' => get_label('bp_outlet_status_sucess'));
				$this->set_response($return_array, success_response());
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

	private function insert_tat($app_id, $company_id, $admin_id, $outlet_id, $tat_time = null, $start_date = null, $end_date = null, $status = null, $status_for = null)
	{
		/* insert tracking entry */
		$tat_entry = array(
			'productivity_outlet_id' => $outlet_id,
			'productivity_company_id' => $company_id,
			'productivity_app_id' => $app_id,
			'productivity_panel' => 'Businesspanel',
			'productivity_tat' => $tat_time,
			'productivity_updated_by' => $admin_id,
			'productivity_start_date' => $start_date,
			'productivity_end_date' => $end_date,
			'productivity_log_type' => $status,
			'productivity_log_for' =>  $status_for
		);

		return $this->Mydb->insert('productivity_log', $tat_entry);
	}

	public function outletnameexists()
	{
		$outlet_name = $this->input->post('outlet_name');
		$edit_id = $this->input->post('edit_id');
		$company_id = decode_value($this->input->post('company_id'));
		$where = array(
			'outlet_name' => trim($outlet_name),
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
			$this->form_validation->set_message('outletnameexists', sprintf(get_label('alredy_exist'), get_label('outlet_name')));
			return false;
		} else {
			return true;
		}
	}


	/* this function used to track outlet status chnages.. */
	private function track_outlet_status($outlets, $status, $company_id, $company_admin_id, $company_app_id)
	{
		$outlets = (is_array($outlets)) ? $outlets : array($outlets);

		if (!empty($outlets)) {
			foreach ($outlets as $id) {

				$track_arr = array(
					'outlet_track_company_id' => $company_id,
					'outlet_track_app_id' => $company_app_id,
					'outlet_track_changed_status' => $status,
					'outlet_track_changed_by ' => $company_admin_id,
					'outlet_track_changed_date' => current_date(),
					'outlet_track_changed_ip' => get_ip(),
					'outlet_track_outlet_id' => $id,
					'outlet_track_changed_panel' => 'Camppanel'
				);

				$this->Mydb->insert('outlet_status_tracking', $track_arr);
			}
		}
	}
} /* end of files */
