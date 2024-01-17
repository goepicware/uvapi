<?php

/**************************
Project Name	: White Label
Created on		: 19 Aug, 2023
Last Modified 	: 28 Aug, 2023
Description		: Audit Reports details

 ***************************/
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class AuditReports extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p>', '</p>');
		$this->table = "audit_reports";
		$this->company_user = "company_user";
		$this->load->library('common');
		$this->label = "Customer";
		$this->load->library('Authorization_Token');
		$this->primary_key = 'audit_id';
		$this->company_id = 'audit_company_id';
	}

	public function list_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$select_array = array('audit_module_name', "audit_description", 'audit_action_type', 'audit_ip_address', 'audit_created_on', 'audit_action_via');
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
				$module = $this->input->get('module');
				$users = $this->input->get('users');
				$audittype = $this->input->get('audittype');
				$source = $this->input->get('source');
				$from_date = $this->input->get('from_date');
				$to_date = $this->input->get('to_date');
				$where = array("$this->primary_key !=" => '', $this->company_id => $company_id);
				if (!empty($module)) {
					$module = explode(',', $module);
					if (($key = array_search('all', $module)) !== false) {
						unset($module[$key]);
					}
					if (!empty($module)) {
						$moduleWhere = "'" . implode("','", $module) . "'";
						$where = array_merge($where, array("audit_module_name IN(" . $moduleWhere . ")" => NULL));
					}
				}
				if (!empty($users)) {
					$where = array_merge($where, array("audit_user_id" => $users));
				}
				if (!empty($audittype)) {
					$audittypeWhere = str_replace(",", "','", $audittype);
					$where = array_merge($where, array("audit_action_type IN('" . $audittypeWhere . "')" => NULL));
				}
				if (!empty($source)) {
					$where = array_merge($where, array("audit_action_via" => $source));
				}

				if (!empty($from_date) && !empty($to_date)) {
					$where = array_merge($where, array('audit_created_on>=' => date('Y-m-d H:i:s', strtotime($from_date)), 'audit_created_on<=' =>  date('Y-m-d H:i:s', strtotime($to_date))));
				} else if (!empty($from_date)) {
					$where = array_merge($where, array('audit_created_on>=' => date('Y-m-d', strtotime($from_date)) . ' 00:00:00', 'audit_created_on<=' =>  date('Y-m-d', strtotime($from_date)) . ' 23:59:59'));
				}
				$order_by = array($this->primary_key => 'DESC');

				$join = array();
				$i = 0;
				$join[$i]['select'] = "company_username";
				$join[$i]['table'] = $this->company_user;
				$join[$i]['condition'] = "audit_user_id = company_user_id";
				$join[$i]['type'] = "LEFT";
				$i++;

				$total_records = $this->Mydb->get_num_join_rows($this->primary_key, $this->table, $where, null, null, null, $like, null, $join);

				$totalPages = (!empty($limit)) ? ceil($total_records / $limit) : 0;

				$result = $this->Mydb->get_all_records($select_array, $this->table, $where, $limit, $offset,  $order_by, $like, array($this->primary_key), $join);
				//echo $this->db->last_query();


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
				$result = array('Site Location', 'Brand', 'Outlet Tag', 'Outlet', 'Zone', 'Time Slot', 'Menu Group', 'Menu Item', 'Page', 'Static Block', 'Banner', 'FAQ Category', 'FAQ', 'Email Template', 'User Role', 'User', 'Group', 'Tag', 'Category', 'Sub Category', 'Product', 'Promotion', 'Customer');
				$return_array = array('status' => "ok", 'message' => 'success', 'result' => $result);
				$this->set_response($return_array, success_response());
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
} /* end of files */
