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

class Address extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library(array('form_validation'));
		$this->form_validation->set_error_delimiters('<p>', '</p>');
		$this->customerAddress = "customer_secondary_addresses";
	}

	public function addAddress_post()
	{
		$unquieid = post_value('unquieid');
		$company = app_validation($unquieid);
		$company_id = $company['company_id'];
		$customerID = decode_value(post_value('customerID'));
		$this->form_validation->set_rules('state', 'lang:deliverystate', 'required');
		$this->form_validation->set_rules('city', 'lang:deliverycity', 'required');
		$this->form_validation->set_rules('district', 'lang:deliverydistrict', 'required');
		$this->form_validation->set_rules('postal_code', 'lang:deliverypostalCode', 'required');
		$this->form_validation->set_rules('addressname', 'lang:addressname', 'required');
		$this->form_validation->set_rules('latitude', 'lang:deliverylatitude', 'required');
		$this->form_validation->set_rules('longitude', 'lang:deliverylongitude', 'required');
		$this->form_validation->set_rules('address', 'lang:address', 'required');

		if ($this->form_validation->run() == TRUE) {

			$data = array(
				'customer_company_id' => $company_id,
				'customer_unquie_id' => $unquieid,
				'customer_id' => $customerID,
				'addressname' => post_value('addressname'),
				'unit_code' => post_value('unit_code'),
				'address' => post_value('address'),
				'city' => post_value('city'),
				'district' => post_value('district'),
				'village' => post_value('village'),
				'state' => post_value('state'),
				'postal_code' => post_value('postal_code'),
				'country' => post_value('country'),
				'latitude' => post_value('latitude'),
				'longitude' => post_value('longitude'),
				'address_nete' => post_value('address_nete'),
				'status' => 'A',
				'created_ip' => get_ip(),
				'created_on' => current_date(),
			);
			$this->Mydb->insert($this->customerAddress, $data);

			$return_array = array('status' => "ok", 'message' =>  sprintf(get_label('success_message_add'), 'Address'));
			$this->set_response($return_array, success_response());
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => get_label('rest_form_error'),
				'form_error' => validation_errors()
			), something_wrong()); /* error message */
		}
	}
	public function getAddressList_get()
	{
		$unquieid = $this->get('unquieid');
		$company = app_validation($unquieid);
		$customerID = decode_value($this->get('customerID'));
		$addressList = $this->Mydb->get_all_records('unit_code, addressname, address, city, district, village, state, postal_code, country, latitude, longitude, address_nete', $this->customerAddress, array('customer_unquie_id' => $unquieid, 'customer_id' => $customerID, 'status' => 'A'));
		if (!empty($addressList)) {
			$return_array = array('status' => "ok", 'result' =>  $addressList);
			$this->set_response($return_array, success_response());
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => sprintf(get_label('admin_no_records_found'), 'Address'),
				'form_error' => validation_errors()
			), something_wrong()); /* error message */
		}
	}
}
