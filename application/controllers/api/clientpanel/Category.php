<?php

/**************************
Project Name	: White Label
Created on		: 18 Aug, 2023
Last Modified 	: 04 Sep, 2023
Description		: Catelog Category Templates

 ***************************/
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Category extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p>', '</p>');
		$this->table = "product_categories";
		$this->assigned_outlets = "category_assigned_outlets";
		$this->category_availability = "product_category_availability";
		$this->day_availability = "product_category_day_availability";
		$this->load->library('common');
		$this->label = "Category";
		$this->load->library('Authorization_Token');
		$this->primary_key = 'pro_cate_primary_id';
		$this->company_id = 'pro_cate_company_id';
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
					$select_array = array('pro_cate_primary_id', 'pro_cate_name', 'pro_cate_sequence', 'pro_cate_status', 'pro_cate_created_on');
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
					$storeID = $this->input->get('storeID');
					$name = $this->input->get('name');
					$status = $this->input->get('status');
					$where = array("$this->primary_key !=" => '', $this->company_id => $company_id);
					if (!empty($storeID)) {
						$where = array_merge($where, array('pao_outlet_id' => $storeID));
					}
					if (!empty($status)) {
						$where = array_merge($where, array('pro_cate_status' => $status));
					}
					if (!empty($name)) {
						$like = array("pro_cate_name" => $name);
					}
					if (!empty($userDetails)) {
						if ($userDetails['company_user_type'] == 'SubAdmin') {
							$where = array_merge($where, array("pao_outlet_id" => $userDetails['company_user_permission_outlet']));
						}
					}

					$order_by = array($this->primary_key => 'DESC');

					$join = array();


					$i = 0;
					$join[$i]['select'] = "";
					$join[$i]['table'] = $this->assigned_outlets;
					$join[$i]['condition'] = "pro_cate_primary_id = pao_category_primary_id";
					$join[$i]['type'] = "INNER";
					$i++;

					$join[$i]['select'] = "outlet_name";
					$join[$i]['table'] = "outlet_management";
					$join[$i]['condition'] = "pao_outlet_id = outlet_id";
					$join[$i]['type'] = "INNER";

					$total_records = $this->Mydb->get_num_join_rows($this->primary_key, $this->table, $where, null, null, null, $like, null, $join);

					$totalPages = (!empty($limit)) ? ceil($total_records / $limit) : 0;

					$result = $this->Mydb->get_all_records(
						$select_array,
						$this->table,
						$where,
						$limit,
						$offset,
						$order_by,
						$like,
						array($this->primary_key),
						$join
					);
					if (!empty($result)) {
						$return_array = array('status' => "ok", 'message' => 'success', 'result' => $result, 'totalRecords' => $total_records, 'totalPages' => $totalPages);
						$this->set_response($return_array, success_response());
					} else {
						$this->set_response(array('status' => 'error', 'message' => get_label('no_records_found')), something_wrong());
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
				$storeID = $this->input->get('storeID');
				$select_array = array(
					$this->primary_key,
					'pro_cate_id',
					'pro_cate_name'
				);
				$where = array(
					$this->company_id => $company_id,
					'pro_cate_status' => 'A'
				);
				if (!empty($storeID)) {
					$where = array_merge($where, array('pao_outlet_id' => $storeID));
				}
				$join = array();
				$join[0]['select'] = "";
				$join[0]['table'] = $this->assigned_outlets;
				$join[0]['condition'] = "pro_cate_id = pao_category_id";
				$join[0]['type'] = "INNER";
				$result = $this->Mydb->get_all_records($select_array, $this->table, $where, null, null, null, null, null, $join);
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
					$join = array();
					$join[0]['select'] = "av_name AS label";
					$join[0]['table'] = "availability";
					$join[0]['condition'] = "cate_availability_id = av_id";
					$join[0]['type'] = "INNER";
					$outlet_availability = $this->Mydb->get_all_records('cate_availability_id AS value', $this->category_availability, array('cate_availability_category_primary_id' => $result['pro_cate_primary_id'], 'cate_availability_type' => 'Category'), null, null, null, null, null, $join);
					$result['cat_availability'] = $outlet_availability;

					$join = array();
					$join[0]['select'] = "outlet_name AS label";
					$join[0]['table'] = "outlet_management";
					$join[0]['condition'] = "pao_outlet_id = outlet_id";
					$join[0]['type'] = "INNER";
					$outlet_availability = $this->Mydb->get_all_records('pao_outlet_id AS value', $this->assigned_outlets, array('pao_category_primary_id' => $result['pro_cate_primary_id']), null, null, null, null, null, $join);
					$result['cat_outlet'] = $outlet_availability;

					$dayAvail = $this->Mydb->get_record('*', $this->day_availability, array('cat_company_id' => $company_id,	'cat_id' => $id));
					$dayAvailList = array();
					if (!empty($dayAvail)) {
						$todayDate = date('Y-m-d') . ' ';
						$dayAvailList = array(
							array('day' => 'Mon', 'checked' => ($dayAvail['cat_monday_check'] == '1') ? 'Yes' : 'No', 'start' => ($dayAvail['cat_monday_check'] == '1') ? $todayDate . $dayAvail['cat_monday_start_time'] : '', 'end' => ($dayAvail['cat_monday_check'] == '1') ? $todayDate . $dayAvail['cat_monday_end_time'] : ''),
							array('day' => 'Tue', 'checked' => ($dayAvail['cat_tuesday_check'] == '1') ? 'Yes' : 'No', 'start' => ($dayAvail['cat_tuesday_check'] == '1') ? $todayDate . $dayAvail['cat_tuesday_start_time'] : '', 'end' => ($dayAvail['cat_tuesday_check'] == '1') ? $todayDate . $dayAvail['cat_tuesday_end_time'] : ''),
							array('day' => 'Wed', 'checked' => ($dayAvail['cat_wednesday_check'] == '1') ? 'Yes' : 'No', 'start' => ($dayAvail['cat_wednesday_check'] == '1') ? $todayDate . $dayAvail['cat_wednesday_start_time'] : '', 'end' => ($dayAvail['cat_wednesday_check'] == '1') ? $todayDate . $dayAvail['cat_wednesday_end_time'] : ''),
							array('day' => 'Thu', 'checked' => ($dayAvail['cat_thursday_check'] == '1') ? 'Yes' : 'No', 'start' => ($dayAvail['cat_thursday_check'] == '1') ? $todayDate . $dayAvail['cat_thursday_start_time'] : '', 'end' => ($dayAvail['cat_thursday_check'] == '1') ? $todayDate . $dayAvail['cat_thursday_end_time'] : ''),
							array('day' => 'Fri', 'checked' => ($dayAvail['cat_friday_check'] == '1') ? 'Yes' : 'No', 'start' => ($dayAvail['cat_friday_check'] == '1') ? $todayDate . $dayAvail['cat_friday_start_time'] : '', 'end' => ($dayAvail['cat_friday_check'] == '1') ? $todayDate . $dayAvail['cat_friday_end_time'] : ''),
							array('day' => 'Sat', 'checked' => ($dayAvail['cat_friday_check'] == '1') ? 'Yes' : 'No', 'start' => ($dayAvail['cat_friday_check'] == '1') ? $todayDate . $dayAvail['cat_saturday_start_time'] : '', 'end' => ($dayAvail['cat_friday_check'] == '1') ? $todayDate . $dayAvail['cat_saturday_end_time'] : ''),
							array('day' => 'Sun', 'checked' => ($dayAvail['cat_sunday_check'] == '1') ? 'Yes' : 'No', 'start' => ($dayAvail['cat_sunday_check'] == '1') ? $todayDate . $dayAvail['cat_sunday_start_time'] : '', 'end' => ($dayAvail['cat_sunday_check'] == '1') ? $todayDate . $dayAvail['cat_sunday_end_time'] : ''),

						);
					}
					$result['day_availability'] = $dayAvailList;

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

				$this->form_validation->set_rules('cate_name', 'lang:pro_cate_name', 'required|callback_category_exists');

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
				$this->form_validation->set_rules('cate_name', 'lang:pro_cate_name', 'required|callback_category_exists');
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
				$company_admin_id = decode_value($this->input->post('company_admin_id'));
				if (!empty($company_id)) {
					$getCompanyDetails = getCompanyUniqueID($company_id);

					$where = array(
						$this->primary_key => trim($delete_id)
					);
					$result = $this->Mydb->get_record($this->primary_key . ', pro_cate_name', $this->table, $where);
					if (!empty($result)) {
						$this->Mydb->delete($this->table, array($this->primary_key => $result[$this->primary_key]));

						$this->Mydb->delete($this->category_availability, array('cate_availability_category_primary_id' => $result[$this->primary_key], 'cate_availability_type' => 'Category'));
						$this->Mydb->delete($this->assigned_outlets, array('pao_category_primary_id' => $result[$this->primary_key]));

						createAuditLog("Category", stripslashes($result['pro_cate_name']), "Delete", $company_admin_id, 'Web', '', $company_id, $getCompanyDetails);

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
		$cate_name = post_value('cate_name');

		$data = array(
			'pro_cate_name'				=> $cate_name,
			'pro_cate_enable_navigation' => post_value('enable_navigation'),
			'pro_cate_custom_title'		=> post_value('custom_title'),
			'pro_cate_sequence'			=> post_value('sequence'),
			'pro_cate_description'		=> post_value('description'),
			'pro_cate_image'			=> post_value('category_image'),
			'pro_cate_default_image'	=> post_value('category_icon'),
			'pro_cate_active_image'		=> post_value('category_active_icon'),
			'pro_cate_status'			=> post_value('status'),
			'pro_cat_lead_time'			=> post_value('lead_time')

		);
		$company_id = decode_value($this->input->post('company_id'));
		$company_admin_id = decode_value($this->input->post('company_admin_id'));
		$getCompanyDetails = getCompanyUniqueID($company_id);
		if ($action == 'add') {

			$company_array = array($this->company_id => $company_id, 'pro_cate_unqiue_id' => $getCompanyDetails);
			$pro_cate_id = get_guid($this->table, 'pro_cate_id', $company_array);

			$slug = make_slug($cate_name, $this->table, 'pro_cate_slug', array($this->company_id => $company_id));

			$data = array_merge(
				$data,
				array(
					'pro_cate_id'		=> $pro_cate_id,
					'pro_cate_slug'		=> $slug,
					'pro_cate_company_id'	=> $company_id,
					'pro_cate_unqiue_id' 	=> (!empty($getCompanyDetails)) ? $getCompanyDetails : '',
					'pro_cate_created_on' => current_date(),
					'pro_cate_created_by' => $company_admin_id,
					'pro_cate_created_ip' => get_ip()
				)
			);

			$edit_id = $this->Mydb->insert($this->table, $data);
			createAuditLog("Category", stripslashes(post_value('cate_name')), "Add", $company_admin_id, 'Web', '', $company_id, $getCompanyDetails);
		} else {

			$slug = make_slug($cate_name, $this->table, 'pro_cate_slug', array("$this->primary_key !=" => $edit_id, $this->company_id => $company_id));

			$data = array_merge(
				$data,
				array(
					'pro_cate_slug'			=> $slug,
					'pro_cate_updated_on'	=> current_date(),
					'pro_cate_updated_by'	=> $company_admin_id,
					'pro_cate_updated_ip'	=> get_ip()
				)
			);
			$this->Mydb->update($this->table, array($this->primary_key => $edit_id), $data);

			createAuditLog("Category", stripslashes(post_value('cate_name')), "Update", $company_admin_id, 'Web', '', $company_id, $getCompanyDetails);

			$catDetail = $this->Mydb->get_record('pro_cate_id', $this->table, array($this->primary_key => $edit_id));
			$pro_cate_id = $catDetail['pro_cate_id'];
		}
		if (!empty($edit_id)) {
			if ($action == 'edit') {
				$this->Mydb->delete($this->category_availability, array('cate_availability_category_primary_id' => $edit_id, 'cate_availability_type' => 'Category'));
				$this->Mydb->delete($this->assigned_outlets, array('pao_category_primary_id' => $edit_id));
				$this->Mydb->delete($this->day_availability, array('cat_company_id' => $company_id,	'cat_id' => $edit_id));
			}

			$assignOutlet = (post_value('assign_outlet') != "") ? explode(',', post_value('assign_outlet')) : array();
			if (!empty($assignOutlet)) {
				foreach ($assignOutlet as $val) {
					$outletArray = array(
						'pao_outlet_id' 		=> $val,
						'pao_category_id' 		=> $pro_cate_id,
						'pao_category_primary_id' => $edit_id,
						'pao_company_id' 		=> $company_id,
						'pao_company_app_id' 	=> $getCompanyDetails,
						'pao_updated_on' 		=> current_date(),
						'pao_updated_by' 		=> $company_admin_id,
						'pao_updated_ip' 		=> get_ip()
					);
					$this->Mydb->insert($this->assigned_outlets, $outletArray);
				}
			}

			$assignAvailability = (post_value('assign_availability') != "") ? explode(',', post_value('assign_availability')) : array();
			if (!empty($assignAvailability)) {
				foreach ($assignAvailability as $val) {
					$availArray = array(
						'cate_availability_id' 			=> $val,
						'cate_availability_category_id' => $pro_cate_id,
						'cate_availability_category_primary_id' => $edit_id,
						'cate_availability_company_id' 	=> $company_id,
						'cate_availability_company_app_id' 	=> $getCompanyDetails,
						'cate_availability_type'		=> 'Category',
						'cate_availability_updated_on' 	=> current_date(),
						'cate_availability_updated_by' 	=> $company_admin_id,
						'cate_availability_updated_ip' 	=> get_ip()
					);
					$this->Mydb->insert($this->category_availability, $availArray);
				}
			}

			$timeavailability = ($this->post('timeavailability') != "") ? json_decode($this->post('timeavailability')) : array();
			$timeavailability = (!empty($timeavailability)) ? $this->object_to_array($timeavailability) : array();
			$moncheck = $tuecheck = $wedcheck =  $thucheck = $fricheck = $satcheck = $suncheck = 0;
			$monstart = $tuestart = $wedstart =  $thustart = $fristart = $satstart = $sunstart = $monend = $tueend = $wedend = $thuend = $friend = $satend = $sunend = "";
			$dayAvail = 0;

			if (!empty($timeavailability)) {
				foreach ($timeavailability as $val) {
					if ($val['checked'] === "Yes" && !empty($val['start']) && !empty($val['end'])) {
						if ($val['day'] == 'Mon') {
							$moncheck = 1;
							$monstart = date('H:i:s', strtotime($val['start']));
							$monend = date('H:i:s', strtotime($val['end']));
						} else if ($val['day'] == 'Tue') {
							$tuecheck = 1;
							$tuestart = date('H:i:s', strtotime($val['start']));
							$tueend = date('H:i:s', strtotime($val['end']));
						} else if ($val['day'] == 'Wed') {
							$wedcheck = 1;
							$wedstart = date('H:i:s', strtotime($val['start']));
							$wedend = date('H:i:s', strtotime($val['end']));
						} else if ($val['day'] == 'Thu') {
							$thucheck = 1;
							$thustart = date('H:i:s', strtotime($val['start']));
							$thuend = date('H:i:s', strtotime($val['end']));
						} else if ($val['day'] == 'Fri') {
							$fricheck = 1;
							$fristart = date('H:i:s', strtotime($val['start']));
							$friend = date('H:i:s', strtotime($val['end']));
						} else if ($val['day'] == 'Sat') {
							$satcheck = 1;
							$satstart = date('H:i:s', strtotime($val['start']));
							$satend = date('H:i:s', strtotime($val['end']));
						} else if ($val['day'] == 'Sun') {
							$suncheck = 1;
							$sunstart = date('H:i:s', strtotime($val['start']));
							$sunend = date('H:i:s', strtotime($val['end']));
						}
						$dayAvail++;
					}
				}
			}
			if ($dayAvail > 0) {
				$dayAvailArray = array(
					'cat_company_unique_id' => $getCompanyDetails,
					'cat_company_id' => $company_id,
					'cat_id' => $edit_id,
					'cat_monday_start_time' => $monstart,
					'cat_monday_end_time' => $monend,
					'cat_tuesday_start_time' => $tuestart,
					"cat_tuesday_end_time" => $tueend,
					"cat_wednesday_start_time" => $wedstart,
					"cat_wednesday_end_time" => $wedend,
					"cat_thursday_start_time" => $thustart,
					"cat_thursday_end_time" => $thuend,
					"cat_friday_start_time" =>  $fristart,
					"cat_friday_end_time" => $friend,
					"cat_saturday_start_time" => $satstart,
					"cat_saturday_end_time" => $satend,
					"cat_sunday_start_time" => $sunstart,
					"cat_sunday_end_time" => $sunend,
					"cat_monday_check" => $moncheck,
					"cat_tuesday_check" => $tuecheck,
					"cat_wednesday_check" => $wedcheck,
					"cat_thursday_check" => $thucheck,
					"cat_friday_check" => $fricheck,
					"cat_saturday_check" => $satcheck,
					"cat_sunday_check" => $suncheck,
					'cat_created_on' => current_date(),
					"cat_created_by" => $company_admin_id,
					"cat_created_ip" => get_ip()
				);
				$this->Mydb->insert($this->day_availability, $dayAvailArray);
			}
		}
	}

	/* this method used check category or alredy exists or not */
	public function category_exists()
	{
		$name = $this->input->post('cate_name');
		$edit_id = $this->input->post('edit_id');
		$company_id = decode_value($this->input->post('company_id'));
		$where = array(
			'pro_cate_name' => trim($name),
			'pro_cate_company_id' => $company_id,
		);
		if (!empty($edit_id)) {
			$where = array_merge($where, array(
				$this->primary_key . " !=" => decode_value($edit_id)
			));
		}
		$result = $this->Mydb->get_record($this->primary_key, $this->table, $where);
		if (!empty($result)) {
			$this->form_validation->set_message('category_exists', sprintf(get_label('alredy_exist'), get_label('pro_cate_name')));
			return false;
		} else {
			return true;
		}
	}

	private function object_to_array($data)
	{
		if (is_array($data) || is_object($data)) {
			$result = array();
			foreach ($data as $key => $value) {
				$result[$key] = $this->object_to_array($value);
			}
			return $result;
		}
		return $data;
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
		$wherearray = array('email_company_id' => $company_id, 'email_unquie_id' => $company_app_id);
		if ($postaction == 'Delete' && !empty($ids)) {
			$this->audit_action($ids, $postaction);
			// $this->Mydb->delete_where_in($this->table,'client_id',$ids,'');
			if (is_array($ids)) {
				$this->Mydb->delete_where_in($this->table, $this->primary_key, $ids, $wherearray);
				$response['msg'] = sprintf(get_label('success_message_delete'), $this->module_label);
			} else {
				$this->Mydb->delete($this->table, array($this->primary_key => $ids, 'email_company_id' => $company_id, 'email_unquie_id' => $company_app_id));
				$response['msg'] = sprintf(get_label('success_message_delete'), $this->module_label);
			}
			$response['status'] = 'success';
			$response['action'] = $postaction;
		}


		$this->set_response($response, success_response()); /* success message */
	}
} /* end of files */
