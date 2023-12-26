<?php

/**************************
Project Name	: White Label
Created on		: 04 Oct, 2023
Last Modified 	: 04 Oct, 2023
Description		: Banner functions

 ***************************/
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Banner extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->banners = "banners";
	}
	public function listBanner_get()
	{
		$company = app_validation($this->get('unquieid')); /* validate app */
		$company_id = $company['company_id'];
		$banners = $this->Mydb->get_all_records('banner_id, banner_image, banner_description, banner_link', $this->banners, array(
			'banner_company_id' => $company_id,
			'banner_status' => 'A'
		), null, null, array('banner_sequence' => 'ASC'));
		if (!empty($banners)) {
			$return_array = array(
				'status' => "ok",
				'result' => $banners
			);
			$this->set_response($return_array, success_response());
		} else {
			echo json_encode(array(
				'status' => 'error',
				'message' => get_label('no_records_found')
			));
			exit();
		}
	}
}
