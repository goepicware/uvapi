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

class Paymentuvcr extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library(array(
			'form_validation',
		));
		$this->form_validation->set_error_delimiters('<p>', '</p>');
		$this->paymentURL = "https://sandboxapinero.uvcr.me/";
		$this->uv_payment = "uv_payment";
	}

	public function response_patch()
	{
		$txt  = @file_get_contents('php://input');
		$myFile = "uvcrHookLog.txt";
		$response = json_decode($txt);
		//echo $txt;
		$fh = fopen($myFile, 'a') or die("can't open file");
		//parse_str($txt, $resposenArray);
		if (!empty($response)) {
			$this->Mydb->update(
				$this->uv_payment,
				array('payment_order_id' => $response->no),
				array(
					'callback_data' => $txt,
					'payment_status' => $response->status,
					'callback_response_on' => current_date()
				)
			);
		}
		$stringData = 'Response:' . $txt . "\n\n\n";
		fwrite($fh, $stringData);
	}

	public function processPayment_post()
	{
		$unquieid = post_value('unquieid');
		app_validation($unquieid);
		$this->form_validation->set_rules('locationID', 'lang:locationID', 'required');
		$this->form_validation->set_rules('customerID', 'lang:rest_customer_id', 'required');
		$this->form_validation->set_rules('grandTotal', 'lang:rest_paid_amount', 'required');
		$this->form_validation->set_rules('firstName', 'lang:rest_customer_fname', 'required');
		$this->form_validation->set_rules('email', 'lang:rest_customer_email', 'required');
		$this->form_validation->set_rules('phone', 'lang:rest_customer_mobile_no', 'required');
		$this->form_validation->set_rules('siteURL', 'lang:res_site_url', 'required');
		if ($this->form_validation->run() == TRUE) {
			$siteURL = post_value('siteURL');
			$customerID = decode_value(post_value('customerID'));
			$orderID = 'PUV-' . $customerID . '-' . time();
			$grandTotal = post_value('grandTotal');
			$amount = number_format($grandTotal, 0, '', '');
			$deliveryChareg = post_value('deliveryCharge');
			$refreshToken = post_value('refresh_token');
			$headers = array(
				"Content-Type:application/json",
				'Authorization:' . $refreshToken,
				"fcm_token:abcdabcd",
				"device_utc_offset:+0700"
			);
			$newToken = loadCurlPost($this->paymentURL . 'auth-new/user/refresh-token', $headers, array(), 1, 1);
			if (!empty($newToken) && !empty($newToken->data)) {
				$products = $this->object_to_array(json_decode($this->input->post('products')));
				$orderItem = [];
				foreach ($products as $product) {
					$orderItem[] = array(
						'id' => $product['productID'],
						'price' => (int) number_format($product['itemTotalPrice'], 0, '', ''),
						'disc_price' => 0,
						'quantity' => (int) $product['itemQuantity'],
						'name' => $product['itemName']
					);
				}

				$paymentRequest = array(
					"callback_url" => base_url('api/user/paymentuvcr/response'),
					"return_url" => $siteURL, //required
					"order_id" => $orderID, //required
					"amount" => $amount, //required
					"payment_method" => array(
						"VABCA",
						"VAMANDIRI",
						"GOPAY",
						"AKULAKU"
					), //required
					"payload" => array(
						"item_details" => $orderItem,
						"additional_fee" => ($deliveryChareg > 0) ? array(
							array(
								"fee_name" => "Delivery Fee",
								"amount" => number_format($deliveryChareg, 0, '', ''),
								"strikethrough" => false
							),
						) : array()
					),
				);
				$post_Params = stripcslashes(json_encode($paymentRequest));
				$headers = array(
					"Content-Type:application/json",
					'Authorization:' . $newToken->data->token
				);
				/* 'Authorization: Basic MTox' */
				$payemntDetails = loadCurlPost($this->paymentURL . 'payment/widget/', $headers, $post_Params, 1, 1);
				$data = array(
					'payment_company_unique_id' => $unquieid,
					'payment_order_id' =>  $orderID,
					'customer_id' => $customerID,
					'request_amount' => $amount,
					'request_datas' => $post_Params,
					'session_token' => (!empty($payemntDetails) && !empty($payemntDetails->data)) ? $payemntDetails->data->session_token : '',
					'response_data' => (!empty($payemntDetails)) ? stripcslashes(json_encode($payemntDetails)) : '',
					'payment_status' => 'Pending',
					'created_ip' => get_ip(),
					'created_on' => current_date(),
				);
				$this->Mydb->insert($this->uv_payment, $data);
				if (!empty($payemntDetails)) {
					if (!empty($payemntDetails->data)) {
						if (!empty($payemntDetails->data->redirect_url)) {
							$return_array = array('status' => "ok", 'paymentOrderID' => $orderID, 'redirectURL' => $payemntDetails->data->redirect_url);
							$this->set_response($return_array, success_response());
						} else {
							$this->set_response(array(
								'status' => 'error',
								'message' => get_label('rest_something_wrong'),
								'form_error' => ''
							), something_wrong()); /* error message */
						}
					} else {
						if (!empty($payemntDetails->errors)) {
							$this->set_response(array(
								'status' => 'error',
								'message' => $payemntDetails->errors[0]->message,
								'form_error' => ''
							), something_wrong()); /* error message */
						} else {
							$this->set_response(array(
								'status' => 'error',
								'message' => get_label('rest_something_wrong'),
								'form_error' => ''
							), something_wrong()); /* error message */
						}
					}
				} else {
					$this->set_response(array(
						'status' => 'error',
						'message' => get_label('rest_something_wrong'),
						'form_error' => ''
					), something_wrong()); /* error message */
				}
			} else {
				if (!empty($newToken) && !empty($newToken->errors)) {
					$this->set_response(array(
						'status' => 'error',
						'message' => $newToken->error[0]->message,
						'form_error' => ''
					), something_wrong()); /* error message */
				} else {
					$this->set_response(array(
						'status' => 'error',
						'message' => get_label('rest_something_wrong'),
						'form_error' => ''
					), something_wrong()); /* error message */
				}
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => get_label('rest_form_error'),
				'form_error' => validation_errors()
			), something_wrong()); /* error message */
		}
	}
	public function checkPaymentStatus_post()
	{
		$unquieid = post_value('unquieid');
		app_validation($unquieid);
		$this->form_validation->set_rules('customerID', 'lang:rest_customer_id', 'required');
		$this->form_validation->set_rules('paymentReferenceID', 'lang:rest_payment_reference', 'required');
		if ($this->form_validation->run() == TRUE) {
			$paymentReferenceID = post_value('paymentReferenceID');
			$customerID = decode_value(post_value('customerID'));
			$checkPayment = $this->Mydb->get_record('payment_status', $this->uv_payment, array('customer_id' => $customerID, 'payment_order_id' => $paymentReferenceID, 'payment_company_unique_id' => $unquieid));
			if (!empty($checkPayment)) {
				$return_array = array('status' => "ok", 'paymentOrderID' => $paymentReferenceID, 'paymentStatus' => $checkPayment['payment_status']);
				$this->set_response($return_array, success_response());
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => get_label('invalid_payment_reference'),
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
}
