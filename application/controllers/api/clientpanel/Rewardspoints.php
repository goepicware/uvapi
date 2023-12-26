<?php

/**************************
Project Name	: White Label
Created on		: 11 Sep, 2023
Last Modified 	: 11 Sep, 2023
Description		: Rewards Points details

 ***************************/
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Rewardspoints extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p>', '</p>');
		$this->load->helper('loyalty');
		$this->table = "loyality_points";
		$this->orders = "orders";
		$this->customers = "customers";
		$this->load->library('common');
		$this->label = get_label('rewardspoints');
		$this->load->library('Authorization_Token');
		$this->primary_key = 'lh_id';
		$this->company_id = 'customer_unquie_app_id';
	}

	public function list_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$select_array = array(
					'lh_id AS id',
					'lh_credit_points AS credit_points',
					'lh_debit_points AS debit_points',
					'lh_currency_symbol AS currency_symbol',
					'lh_reward_per_point AS reward_per_point',
					'lh_reward_per_amount AS reward_per_amount',
					'lh_from AS from',
					'lh_reason AS reason',
					'lh_debit_reason AS debit_reason',
					'lh_expiry_on AS expiry_on',
					'lh_status AS status',
					'lh_source AS source',
					'lh_created_on AS created_on'
				);
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
				$where = array("$this->primary_key !=" => '', 'customer_company_id' => $company_id);
				if (!empty($name)) {
					$where = array_merge($where, array("(customer_first_name LIKE '%" . $name . "%' OR customer_last_name LIKE '%" . $name . "%')" => NULL));
				}
				if (!empty($email)) {
					$like = array_merge($like, array("customer_email" => $email));
				}


				$orderByArr = array($this->primary_key => 'DESC');

				$j = 0;
				$join[$j]['select'] = "order_total_amount";
				$join[$j]['table'] = $this->orders;
				$join[$j]['condition'] = "order_primary_id = lh_ref_id";
				$join[$j]['type'] = "LEFT";
				$j++;

				$join[$j]['select'] = "CONCAT_WS(' ',customer_first_name,customer_last_name) AS customer_name, customer_email";
				$join[$j]['table'] = $this->customers;
				$join[$j]['condition'] = "customer_id = lh_customer_id";
				$join[$j]['type'] = "LEFT";
				$j++;


				$total_records = $this->Mydb->get_num_join_rows($this->primary_key, $this->table, $where, '', '', '', $like, array($this->primary_key), $join);


				$totalPages = (!empty($limit)) ? ceil($total_records / $limit) : 0;

				$result = $this->Mydb->get_all_records($select_array, $this->table, $where, $limit, $offset, $orderByArr, $like, array($this->primary_key), $join);

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
	public function credit_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$this->form_validation->set_rules('reward_customer', 'lang:rest_customer_id', 'required|trim|callback_customer_exists');
				$this->form_validation->set_rules('reward_points', 'lang:rewardspoints', 'required|trim|numeric');
				$this->form_validation->set_rules('expiry_days', 'lang:expiry_days', 'trim|numeric|max_length[2]');
				$this->form_validation->set_rules('reason', 'lang:reason', 'required|trim');
				if ($this->form_validation->run() == TRUE) {
					$this->addedit();
					$this->set_response(array(
						'status' => 'success',
						'message' => sprintf(get_label('success_message_cridit'), $this->label),
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
	public function debit_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$this->form_validation->set_rules('reward_customer', 'lang:rest_customer_id', 'required|trim|callback_customer_exists');
				$this->form_validation->set_rules('reward_points', 'lang:rewardspoints', 'required|trim|numeric');
				$this->form_validation->set_rules('expiry_days', 'lang:expiry_days', 'trim|numeric|max_length[2]');
				$this->form_validation->set_rules('reason', 'lang:reason', 'required|trim');
				if ($this->form_validation->run() == TRUE) {
					$this->addedit();
					$this->set_response(array(
						'status' => 'success',
						'message' => sprintf(get_label('success_message_debit'), $this->label),
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
	public function addedit($edit_id = null)
	{

		if (!empty($edit_id)) {
			$edit_id = decode_value($edit_id);
		}
		$action = post_value('action');
		$company_id = decode_value($this->input->post('company_id'));
		$company_admin_id = decode_value($this->input->post('company_admin_id'));
		$getCompanyDetails = client_validation($company_id);
		if ($action == 'credit') {
			if (!empty($getCompanyDetails) && !empty($getCompanyDetails['company_currency'])) {
				$this->load->helper('loyalty');
				$ref_id = 0;
				$from_mode = 'pos';
				$expiry_flag = 'yes';
				$days = $this->input->post('expiry_days');
				$loyalty_expiry_on = (int)$days;

				if ($days == 0) {
					$loyalty_expiry_on = (int)$getCompanyDetails['client_loyalty_expiry_on'];
				}
				$date = new DateTime();
				$date->add(new DateInterval('P' . $loyalty_expiry_on . 'D'));
				$expiry_on = $date->format('Y-m-d H:i:s');

				$insert_loyalty_arr = array(
					'lh_customer_id' => post_value('reward_customer'),
					'lh_credit_points' => post_value('reward_points'),
					'lh_currency_symbol' => $getCompanyDetails['company_currency'],
					'lh_reward_per_point' => $getCompanyDetails['company_reward_point'],
					'lh_reward_per_amount' => get_reward_amount(),
					'lh_from' => $from_mode,
					'lh_ref_id' => $ref_id,
					'lh_reason' => post_value('reason'),
					'lh_expiry_flag' => $expiry_flag,
					'lh_expiry_on' => $expiry_on,
					'lh_source' => 'Admin',
					'lh_created_on' => current_date(),
					'lh_created_by' =>  $company_admin_id,
					'lh_created_ip' => get_ip()
				);
				$this->Mydb->insert($this->table, $insert_loyalty_arr);
			}
		} else if ($action == 'debit') {

			$customer_id = post_value('reward_customer');
			$points 	 = post_value('reward_points');
			$reason		 = post_value('reason');
			if ($points > 0) {
				$redeemed_amount = floatval($points);
				$current_date = date('Y-m-d');
				$point_amount = 0;
				$points_return_arr = array();

				$where = array(
					'lh_customer_id' => $customer_id,
					'lh_expiry_on >=' => current_date(),
					'lh_expiry_flag' => 'yes'
				);

				$orderby = array('lh_created_on' => 'ASC');
				/*Get rreward points list*/
				$res_set = $this->Mydb->get_all_records('lh_id,lh_reward_per_point,lh_reward_per_amount,lh_debit_points,(lh_credit_points-lh_debit_points) as reward_points', $this->table, $where, '', '', $orderby);
				foreach ($res_set as $res_arr) {
					$reward_per_point = floatval($res_arr['lh_reward_per_point']);
					$reward_per_amount = floatval($res_arr['lh_reward_per_amount']);
					$debit_points = floatval($res_arr['lh_debit_points']);
					$remaining_points = floatval($res_arr['reward_points']);
					if ($remaining_points != 0) {
						if ($point_amount >= $redeemed_amount) break;
						$temp_point_amount = $point_amount;
						/*---------------------------------------------------*/
						$point_amount += ($remaining_points / $reward_per_point) * $reward_per_amount;
						if ($point_amount > $redeemed_amount) {
							$bal_amount = $redeemed_amount - $temp_point_amount;
							/*remaing points need to reach the redeemed amount*/
							$need_points = ($bal_amount * $reward_per_point) / $reward_per_amount;
							$remaining_points = $need_points;
						}
						/*------------------Debit points update---------------*/
						$debit_points = $debit_points + $remaining_points;
						if ($this->Mydb->update($this->table, array('lh_id' => $res_arr['lh_id']), array('lh_debit_points' => $debit_points, 'lh_debit_reason' => post_value('reason')))) {
							$points_return_arr[$res_arr['lh_id']] = $remaining_points;
						}
					}
				}

				/*------------------------------------------------------*/

				$loyality_history_arr = array(
					'lh_redeem_point' => $points,
					'lh_redeem_amount' => $redeemed_amount,
					'lh_order_primaryid' => '0',
					'lh_order_id' => '0',
					'lh_customer_id' => $customer_id,
					'lh_redeem_history' => json_encode($points_return_arr),
					'lh_created_on' => $current_date,
					'lh_created_by' => $company_admin_id,
					'lh_created_ip' => get_ip(),
					'lh_source' => 'Admin',
					'lh_reason' => $reason
				);
				$this->Mydb->insert('loyality_history', $loyality_history_arr);
			} else {
				$result['status'] = 'error';
				$result['message'] = 'Points not zero';
			}
		}
	}
	/*Check customer exist or not*/
	public function customer_exists()
	{
		$reward_customer = $this->input->post('reward_customer');
		$company_id = decode_value($this->input->post('company_id'));
		$where = array(
			'customer_id' => trim($reward_customer),
			'customer_company_id' => $company_id,
			'customer_status' => 'A',
		);
		$result = $this->Mydb->get_record('customer_id', 'customers', $where);
		if (empty($result)) {
			$this->form_validation->set_message('customer_exists', get_label('rest_customer_exists'));
			return false;
		} else {
			return true;
		}
	}
} /* end of files */
