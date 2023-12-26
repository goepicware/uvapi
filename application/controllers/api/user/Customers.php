<?php

/**************************
Project Name	: White Label
Created on		: 18 Aug, 2023
Last Modified 	: 04 Sep, 2023
Description		: Customer Address Templates
 ***************************/
error_reporting(-1);

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

class Customers extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library(array('form_validation'));
		$this->form_validation->set_error_delimiters('<p>', '</p>');

		$this->customers = "customers";
	}

	public function login_post()
	{
		$unquieid = post_value('unquieid');
		$company = app_validation($unquieid);
		$company_id = $company['company_id'];
		$this->form_validation->set_rules('token', 'lang:token', 'required');
		if ($this->form_validation->run() == TRUE) {
			$token = post_value('token');
			$headers = array(
				"Authorization:" . $token,
				"fcm_token:abcdabcd"
			);
			$customerData = loadCurlget(UVCR_LINK . 'master/v4/member', $headers, '');
			if (!empty($customerData)) {
				$result = array();
				if (!empty($customerData->data)) {
					$customerDetails = $customerData->data;
					$gender = strtolower($customerDetails->sex);
					$customerUVID = $customerDetails->id;
					$checkingCust = $this->Mydb->get_record('customer_id AS customerID, customer_first_name, customer_last_name', $this->customers, array('customer_pos_id' => $customerUVID, 'customer_company_id' => $company_id));

					$result = array('customerUVID' => $customerUVID, 'firstName' => $customerDetails->first_name, 'lastName' => $customerDetails->last_name, 'email' => $customerDetails->email, 'primary_phone' => $customerDetails->primary_phone);

					if (!empty($checkingCust)) {
						$customerID = $checkingCust['customerID'];
					} else {
						$customer_gender = "O";
						if ($gender == "male") {
							$customer_gender = "M";
						} else if ($gender == "female") {
							$customer_gender = "F";
						}
						$data = array(
							'customer_company_id' => $company_id,
							'customer_unquie_app_id' => $unquieid,
							'customer_pos_id' =>  $customerUVID,
							'customer_first_name' => $customerDetails->first_name,
							'customer_last_name' => $customerDetails->last_name,
							'customer_email' => $customerDetails->email,
							'customer_gender' => $customer_gender,
							'customer_phone' => $customerDetails->primary_phone,
							'customer_birthdate' => (!empty($customerDetails->birthday)) ? date('Y-m-d', strtotime($customerDetails->birthday)) : '',
							'customer_status' => 'A',
							'customer_created_ip' => get_ip(),
							'customer_created_on' => current_date(),
						);
						$customerID = $this->Mydb->insert($this->customers, $data);
					}
				}
				if (!empty($customerID)) {
					$result['customerID'] = $customerID;
					$return_array = array('status' => "ok", 'result' => $result);
					$this->set_response($return_array, success_response());
				} else {
					$this->set_response(array(
						'status' => 'error',
						'message' => get_label('rest_customer_exists'),
						'form_error' => ''
					), something_wrong()); /* error message */
				}
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => get_label('rest_customer_exists'),
					'form_error' => ''
				), something_wrong()); /* error message */
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => get_label('rest_form_error'),
				'form_error' => validation_errors()
			), something_wrong()); /* error message */
		}
	}
}
