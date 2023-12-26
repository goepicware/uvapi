<?php

/**************************
Project Name	: White Label
Created on		: 18 Aug, 2023
Last Modified 	: 21 Aug, 2023
Description		: Catelog Subcategory Details

 ***************************/
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Subcategory extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p>', '</p>');
		$this->table = "product_subcategories";
		$this->categories = "product_categories";
		$this->assigned_outlets = "sub_category_assigned_outlets";
		$this->category_availability = "product_category_availability";
		$this->day_availability = "product_subcategory_day_availability";
		$this->load->library('common');
		$this->label = "Sub Category";
		$this->load->library('Authorization_Token');
		$this->primary_key = 'pro_subcate_primary_id';
		$this->company_id = 'pro_subcate_company_id';
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
					$select_array = array('pro_subcate_primary_id', 'pro_subcate_name', 'pro_subcate_sequence', 'pro_subcate_status', 'pro_subcate_created_on');
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
					$categoryID = $this->input->get('categoryID');
					$where = array("$this->primary_key !=" => '', $this->company_id => $company_id);
					if (!empty($storeID)) {
						$where = array_merge($where, array('pao_outlet_id' => $storeID));
					}
					if (!empty($status)) {
						$where = array_merge($where, array('pro_cate_status' => $status));
					}
					if (!empty($categoryID)) {
						$where = array_merge($where, array('pro_subcate_category_primary_id' => $categoryID));
					}
					if (!empty($userDetails)) {
						if ($userDetails['company_user_type'] == 'SubAdmin') {
							$where = array_merge($where, array("pao_outlet_id" => $userDetails['company_user_permission_outlet']));
						}
					}
					if (!empty($name)) {
						$like = array("pro_subcate_name" => $name);
					}
					$order_by = array($this->primary_key => 'DESC');

					$join = array();

					$i = 0;
					$join[$i]['select'] = "pro_cate_name";
					$join[$i]['table'] = $this->categories;
					$join[$i]['condition'] = "pro_subcate_category_primary_id = pro_cate_primary_id";
					$join[$i]['type'] = "LEFT";
					$i++;

					$join[$i]['select'] = "";
					$join[$i]['table'] = $this->assigned_outlets;
					$join[$i]['condition'] = "pro_subcate_primary_id = pao_sub_category_primary_id";
					if (!empty($storeID)) {
						$join[$i]['type'] = "INNER";
					} else {
						$join[$i]['type'] = "LEFT";
					}
					$i++;

					$join[$i]['select'] = "outlet_name";
					$join[$i]['table'] = "outlet_management";
					$join[$i]['condition'] = "pao_outlet_id = outlet_id";
					$join[$i]['type'] = "INNER";

					$total_records = $this->Mydb->get_num_join_rows($this->primary_key, $this->table, $where, null, null, null, $like, null, $join);

					$totalPages = (!empty($limit)) ? ceil($total_records / $limit) : 0;

					$result = $this->Mydb->get_all_records($select_array, $this->table, $where, $limit, $offset,  $order_by, $like, array($this->primary_key), $join);
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
	public function subcateonly_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$company_id = decode_value($this->input->get('company_id'));
				$storeID = $this->input->get('storeID');
				$categoryID = $this->input->get('categoryID');

				$i = 0;
				$join[$i]['select'] = "";
				$join[$i]['table'] = $this->assigned_outlets;
				$join[$i]['condition'] = "pro_subcate_primary_id = pao_sub_category_primary_id";
				if (!empty($storeID)) {
					$join[$i]['type'] = "INNER";
				} else {
					$join[$i]['type'] = "LEFT";
				}
				$i++;

				$select_array = array('pro_subcate_id AS value', 'pro_subcate_name AS label');
				$where = array(
					$this->company_id => $company_id,
					'pro_subcate_status' => 'A',
					'pro_subcate_name!=' => NULL
				);
				if (!empty($categoryID)) {
					$where = array_merge($where, array('pro_subcate_category_id' => $categoryID));
				}
				if (!empty($storeID)) {
					$where = array_merge($where, array('pao_outlet_id' => $storeID));
				}
				$result =  $this->Mydb->get_all_records($select_array, $this->table, $where, '', '',  array(
					$this->primary_key => 'DESC'
				), '', array($this->primary_key), $join);

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
	public function dropdownlist_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$company_id = decode_value($this->input->get('company_id'));
				$storeID = $this->input->get('storeID');
				$i = 0;
				$join[$i]['select'] = "";
				$join[$i]['table'] = $this->assigned_outlets;
				$join[$i]['condition'] = "pro_subcate_primary_id = pao_sub_category_primary_id";
				if (!empty($storeID)) {
					$join[$i]['type'] = "INNER";
				} else {
					$join[$i]['type'] = "LEFT";
				}
				$i++;

				$join[$i]['select'] = "pro_cate_id, pro_cate_name";
				$join[$i]['table'] = $this->categories;
				$join[$i]['condition'] = "pro_subcate_category_primary_id = pro_cate_primary_id";
				$join[$i]['type'] = "INNER";
				$i++;

				$select_array = array('pro_subcate_id', 'pro_subcate_name');
				$where = array(
					$this->company_id => $company_id,
					'pro_cate_status' => 'A',
					'pro_subcate_status' => 'A',
					'pro_cate_name!=' => NULL,
					'pro_subcate_name!=' => NULL
				);
				if (!empty($storeID)) {
					$where = array_merge($where, array('pao_outlet_id' => $storeID));
				}
				$result =  $this->Mydb->get_all_records($select_array, $this->table, $where, '', '',  array(
					$this->primary_key => 'DESC'
				), '', array($this->primary_key), $join);

				if (!empty($result)) {
					$finalResult = [];
					$categoryList =  array_unique(array_column($result, 'pro_cate_id'));
					$j = 0;
					foreach ($categoryList as $val) {
						$i = 0;
						foreach ($result as $val1) {
							if ($val1['pro_cate_id'] == $val) {
								$finalResult[$j]['label'] = $val1['pro_cate_name'];
								$finalResult[$j]['options'][$i]['label'] = $val1['pro_subcate_name'];
								$finalResult[$j]['options'][$i]['value'] = $val . '_' . $val1['pro_subcate_id'];
								$i++;
							}
						}
						$j++;
					}

					$return_array = array('status' => "ok", 'message' => 'success', 'result' => $finalResult);
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

					$category = $this->Mydb->get_record('pro_cate_primary_id, pro_cate_id, pro_cate_name', $this->categories, array('pro_cate_primary_id' => $result['pro_subcate_category_primary_id']));
					$result['category'] = $category;

					$join = array();
					$join[0]['select'] = "av_name AS label";
					$join[0]['table'] = "availability";
					$join[0]['condition'] = "cate_availability_id = av_id";
					$join[0]['type'] = "INNER";
					$outlet_availability = $this->Mydb->get_all_records('cate_availability_id AS value', $this->category_availability, array('cate_availability_category_primary_id' => $result['pro_subcate_primary_id'], 'cate_availability_type' => 'Subcategory'), null, null, null, null, null, $join);
					$result['subcat_availability'] = $outlet_availability;

					$join = array();
					$join[0]['select'] = "outlet_name AS label";
					$join[0]['table'] = "outlet_management";
					$join[0]['condition'] = "pao_outlet_id = outlet_id";
					$join[0]['type'] = "INNER";
					$outlet_availability = $this->Mydb->get_all_records('pao_outlet_id AS value', $this->assigned_outlets, array('pao_sub_category_primary_id' => $result['pro_subcate_primary_id']), null, null, null, null, null, $join);
					$result['subcat_outlet'] = $outlet_availability;

					$dayAvail = $this->Mydb->get_record('*', $this->day_availability, array('subcat_company_id' => $company_id,	'subcat_id' => $id));
					$dayAvailList = array();
					if (!empty($dayAvail)) {
						$todayDate = date('Y-m-d') . ' ';
						$dayAvailList = array(
							array('day' => 'Mon', 'checked' => ($dayAvail['subcat_monday_check'] == '1') ? 'Yes' : 'No', 'start' => ($dayAvail['subcat_monday_check'] == '1') ? $todayDate . $dayAvail['subcat_monday_start_time'] : '', 'end' => ($dayAvail['subcat_monday_check'] == '1') ? $todayDate . $dayAvail['subcat_monday_end_time'] : ''),
							array('day' => 'Tue', 'checked' => ($dayAvail['subcat_tuesday_check'] == '1') ? 'Yes' : 'No', 'start' => ($dayAvail['subcat_tuesday_check'] == '1') ? $todayDate . $dayAvail['subcat_tuesday_start_time'] : '', 'end' => ($dayAvail['subcat_tuesday_check'] == '1') ? $todayDate . $dayAvail['subcat_tuesday_end_time'] : ''),
							array('day' => 'Wed', 'checked' => ($dayAvail['subcat_wednesday_check'] == '1') ? 'Yes' : 'No', 'start' => ($dayAvail['subcat_wednesday_check'] == '1') ? $todayDate . $dayAvail['subcat_wednesday_start_time'] : '', 'end' => ($dayAvail['subcat_wednesday_check'] == '1') ? $todayDate . $dayAvail['subcat_wednesday_end_time'] : ''),
							array('day' => 'Thu', 'checked' => ($dayAvail['subcat_thursday_check'] == '1') ? 'Yes' : 'No', 'start' => ($dayAvail['subcat_thursday_check'] == '1') ? $todayDate . $dayAvail['subcat_thursday_start_time'] : '', 'end' => ($dayAvail['subcat_thursday_check'] == '1') ? $todayDate . $dayAvail['subcat_thursday_end_time'] : ''),
							array('day' => 'Fri', 'checked' => ($dayAvail['subcat_friday_check'] == '1') ? 'Yes' : 'No', 'start' => ($dayAvail['subcat_friday_check'] == '1') ? $todayDate . $dayAvail['subcat_friday_start_time'] : '', 'end' => ($dayAvail['subcat_friday_check'] == '1') ? $todayDate . $dayAvail['subcat_friday_end_time'] : ''),
							array('day' => 'Sat', 'checked' => ($dayAvail['subcat_friday_check'] == '1') ? 'Yes' : 'No', 'start' => ($dayAvail['subcat_friday_check'] == '1') ? $todayDate . $dayAvail['subcat_saturday_start_time'] : '', 'end' => ($dayAvail['subcat_friday_check'] == '1') ? $todayDate . $dayAvail['subcat_saturday_end_time'] : ''),
							array('day' => 'Sun', 'checked' => ($dayAvail['subcat_sunday_check'] == '1') ? 'Yes' : 'No', 'start' => ($dayAvail['subcat_sunday_check'] == '1') ? $todayDate . $dayAvail['subcat_sunday_start_time'] : '', 'end' => ($dayAvail['subcat_sunday_check'] == '1') ? $todayDate . $dayAvail['subcat_sunday_end_time'] : ''),

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

				$this->form_validation->set_rules('category', 'lang:pro_cate_label', 'required');
				$this->form_validation->set_rules('subcate_name', 'lang:pro_subcate_name', 'required|callback_subcategory_exists');
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
				$this->form_validation->set_rules('category', 'lang:pro_cate_label', 'required');
				$this->form_validation->set_rules('subcate_name', 'lang:pro_subcate_name', 'required|callback_subcategory_exists');
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
						$this->primary_key => trim($delete_id ?? '')
					);
					$result = $this->Mydb->get_record($this->primary_key, $this->table, $where);
					if (!empty($result)) {
						$this->Mydb->delete($this->table, array($this->primary_key => $result[$this->primary_key]));

						$this->Mydb->delete($this->category_availability, array('cate_availability_category_primary_id' => $result[$this->primary_key], 'cate_availability_type' => 'Subcategory'));
						$this->Mydb->delete($this->assigned_outlets, array('pao_sub_category_primary_id' => $result[$this->primary_key]));

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
		$subcate_name = post_value('subcate_name');
		$category = (post_value('category') != "") ? explode('_', post_value('category')) : [];
		$data = array(
			'pro_subcate_category_primary_id' => (!empty($category)) ? $category[0] : '',
			'pro_subcate_category_id'		=> (!empty($category)) ? $category[1] : '',
			'pro_subcate_name'				=> $subcate_name,
			'pro_subcate_description'		=> post_value('description'),
			'pro_subcate_image'				=> post_value('image'),
			'pro_subcate_active_image'		=> post_value('active_image'),
			'pro_subcate_default_image'		=> post_value('default_image'),
			'pro_subcate_status'			=> post_value('status'),
			'pro_subcate_lead_time'			=> post_value('lead_time')

		);
		$company_id = decode_value($this->input->post('company_id'));
		$company_admin_id = decode_value($this->input->post('company_admin_id'));
		$getCompanyDetails = getCompanyUniqueID($company_id);
		if ($action == 'add') {

			$company_array = array($this->company_id => $company_id, 'pro_subcate_unqiue_id' => $getCompanyDetails);
			$pro_subcate_id = get_guid($this->table, 'pro_subcate_id', $company_array);

			$slug = make_slug($subcate_name, $this->table, 'pro_subcate_slug', array($this->company_id => $company_id));
			$company_array['pro_subcate_category_primary_id'] = (!empty($category)) ? $category[0] : '';
			$pro_subcate_sequence = ((int)$this->input->post('sequence') == 0) ?  get_sequence('pro_subcate_sequence', $this->table, $company_array) : $this->input->post('sequence');

			$data = array_merge(
				$data,
				array(
					'pro_subcate_sequence '	=> $pro_subcate_sequence,
					'pro_subcate_id'		=> $pro_subcate_id,
					'pro_subcate_slug'		=> $slug,
					'pro_subcate_company_id' => $company_id,
					'pro_subcate_unqiue_id' => (!empty($getCompanyDetails)) ? $getCompanyDetails : '',
					'pro_subcate_created_on' => current_date(),
					'pro_subcate_created_by' => $company_admin_id,
					'pro_subcate_created_ip' => get_ip()
				)
			);

			$edit_id = $this->Mydb->insert($this->table, $data);
		} else {

			$slug = make_slug($subcate_name, $this->table, 'pro_subcate_slug', array("$this->primary_key !=" => $edit_id, $this->company_id => $company_id));

			$data = array_merge(
				$data,
				array(
					'pro_subcate_sequence '		=> post_value('sequence'),
					'pro_subcate_slug'			=> $slug,
					'pro_subcate_updated_on'	=> current_date(),
					'pro_subcate_updated_by'	=> $company_admin_id,
					'pro_subcate_updated_ip'	=> get_ip()
				)
			);
			$this->Mydb->update($this->table, array($this->primary_key => $edit_id), $data);
			$catDetail = $this->Mydb->get_record('pro_subcate_id', $this->table, array($this->primary_key => $edit_id));
			$pro_subcate_id = $catDetail['pro_subcate_id'];
		}
		if (!empty($edit_id)) {
			if ($action == 'edit') {
				$this->Mydb->delete($this->category_availability, array('cate_availability_category_primary_id' => $edit_id, 'cate_availability_type' => 'Subcategory'));
				$this->Mydb->delete($this->assigned_outlets, array('pao_sub_category_primary_id' => $edit_id));
				$this->Mydb->delete($this->day_availability, array('subcat_company_id' => $company_id,	'subcat_id' => $edit_id));
			}

			$assignOutlet = (post_value('assign_outlet') != "") ? explode(',', post_value('assign_outlet')) : array();
			if (!empty($assignOutlet)) {
				foreach ($assignOutlet as $val) {
					$outletArray = array(
						'pao_outlet_id' 		=> $val,
						'pao_sub_category_id' 	=> $pro_subcate_id,
						'pao_sub_category_primary_id' => $edit_id,
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
						'cate_availability_category_id' => $pro_subcate_id,
						'cate_availability_category_primary_id' => $edit_id,
						'cate_availability_company_id' 	=> $company_id,
						'cate_availability_company_app_id' 	=> $getCompanyDetails,
						'cate_availability_type'		=> 'Subcategory',
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
					'subcat_company_unique_id' => $getCompanyDetails,
					'subcat_company_id' => $company_id,
					'subcat_id' => $edit_id,
					'subcat_monday_start_time' => $monstart,
					'subcat_monday_end_time' => $monend,
					'subcat_tuesday_start_time' => $tuestart,
					"subcat_tuesday_end_time" => $tueend,
					"subcat_wednesday_start_time" => $wedstart,
					"subcat_wednesday_end_time" => $wedend,
					"subcat_thursday_start_time" => $thustart,
					"subcat_thursday_end_time" => $thuend,
					"subcat_friday_start_time" =>  $fristart,
					"subcat_friday_end_time" => $friend,
					"subcat_saturday_start_time" => $satstart,
					"subcat_saturday_end_time" => $satend,
					"subcat_sunday_start_time" => $sunstart,
					"subcat_sunday_end_time" => $sunend,
					"subcat_monday_check" => $moncheck,
					"subcat_tuesday_check" => $tuecheck,
					"subcat_wednesday_check" => $wedcheck,
					"subcat_thursday_check" => $thucheck,
					"subcat_friday_check" => $fricheck,
					"subcat_saturday_check" => $satcheck,
					"subcat_sunday_check" => $suncheck,
					'subcat_created_on' => current_date(),
					"subcat_created_by" => $company_admin_id,
					"subcat_created_ip" => get_ip()
				);
				$this->Mydb->insert($this->day_availability, $dayAvailArray);
			}
		}
	}

	/* this method used check category or alredy exists or not */
	public function subcategory_exists()
	{
		$name = $this->input->post('subcate_name');
		$category = (post_value('category') != "") ? explode('_', post_value('category')) : [];
		$edit_id = $this->input->post('edit_id');
		$company_id = decode_value($this->input->post('company_id'));
		$where = array(
			'pro_subcate_name' => trim($name ?? ''),
			'pro_subcate_category_primary_id' => (!empty($category)) ? $category[0] : '',
			$this->company_id => $company_id
		);
		if (!empty($edit_id)) {
			$where = array_merge($where, array(
				$this->primary_key . " !=" => decode_value($edit_id)
			));
		}
		$result = $this->Mydb->get_record($this->primary_key, $this->table, $where);
		if (!empty($result)) {
			$this->form_validation->set_message('subcategory_exists', sprintf(get_label('alredy_exist'), get_label('pro_subcate_name')));
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
