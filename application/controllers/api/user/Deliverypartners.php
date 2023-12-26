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

class Deliverypartners extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library(array(
			'form_validation',
		));
		$this->form_validation->set_error_delimiters('<p>', '</p>');
		$this->APIURL = "https://delivery-partners-integrator-sandbox-adchrwkija-uc.a.run.app/v1/";
		$this->token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJkYXRhIjp7InRva2VuIjp7ImNhdGVnb3J5IjoiU0VDUkVUIiwiZW52IjoiU0FOREJPWCIsInByZW1pdW1BY2Nlc3MiOnt9fSwidXNlciI6eyJjbGllbnRJZCI6IldFZXYwanlNSnhMWThkMmo3djkwIiwidXNlcklkIjoiSllvZnMwUHR4NHE2ak5aeDBrTHQiLCJlbWFpbCI6InRkYXJ0Z2FsbGVyeUBtYWlsLmNvbSIsInJvbGUiOiJPV05FUiIsInR5cGUiOiJDTElFTlQifX0sInR5cGUiOiJBQ0NFU1MiLCJpYXQiOjE2NjE3NzkzNzB9.iPCjrYBWFEL3Llvpv7xxf0GlIxufGMOsWqiFwO9MC8A";
		$this->siteLocation = "site_location";
		$this->uv_delivery = "uv_delivery";
	}

	public function response_post()
	{
		$txt  = @file_get_contents('php://input');
		$myFile = "uvdeliveryHookLog.txt";
		$response = json_decode($txt);
		//echo $txt;
		$fh = fopen($myFile, 'a') or die("can't open file");
		//parse_str($txt, $resposenArray);
		/*if (!empty($response)) {
			$this->Mydb->update(
				$this->uv_payment,
				array('payment_order_id' => $response->no),
				array(
					'callback_data' => $txt,
					'payment_status' => $response->status,
					'callback_response_on' => current_date()
				)
			);
		}*/
		$stringData = 'Response:' . $txt . "\n\n\n";
		fwrite($fh, $stringData);
	}

	public function loadPartnersList_post()
	{
		$unquieid = post_value('unquieid');
		app_validation($unquieid);
		$this->form_validation->set_rules('locationID', 'lang:locationID', 'required');
		$this->form_validation->set_rules('address', 'lang:address', 'required');
		$this->form_validation->set_rules('latitude', 'lang:deliverylatitude', 'required');
		$this->form_validation->set_rules('longitude', 'lang:deliverylongitude', 'required');
		$this->form_validation->set_rules('state', 'lang:deliverystate', 'required');
		$this->form_validation->set_rules('postalCode', 'lang:deliverypostalCode', 'required');
		$this->form_validation->set_rules('firstName', 'lang:rest_customer_fname', 'required');
		$this->form_validation->set_rules('email', 'lang:rest_customer_email', 'required');
		$this->form_validation->set_rules('phone', 'lang:rest_customer_mobile_no', 'required');
		if ($this->form_validation->run() == TRUE) {
			$locationID = decode_value(post_value('locationID'));
			$location = $this->Mydb->get_record("sl_name, sl_location_email, sl_location_phone, sl_pickup_postal_code, sl_latitude, sl_longitude, sl_pickup_address_line1, sl_pickup_city, sl_pickup_province, sl_pickup_district, sl_pickup_village", $this->siteLocation, array('sl_location_id' => $locationID));
			$products = $this->object_to_array(json_decode($this->input->post('products')));
			$orderItem = [];
			foreach ($products as $product) {
				$orderItem[] = array(
					'name' => $product['itemName'],
					'quantity' => $product['itemQuantity'],
					'description' => $product['itemRemarks'],
					'price' => $product['itemTotalPrice'],
					'dimension' => array(
						'height' => 0,
						'width' => 0,
						'depth' => 0,
						'weight' => 0
					)
				);
			}
			$postParams = array(
				"sender" => array(
					'firstName' => $location['sl_name'],
					'email' => $location['sl_location_email'],
					'phone' => $location['sl_location_phone'],
				),
				"origin" => array(
					'address' => $location['sl_pickup_address_line1'],
					'keywords' => '',
					'coordinate' => array(
						'latitude' => $location['sl_latitude'],
						'longitude' => $location['sl_longitude'],
					),
					'village' => $location['sl_pickup_village'],
					'district' => $location['sl_pickup_district'],
					'city' => $location['sl_pickup_city'],
					'province' => $location['sl_pickup_province'],
					'postalCode' => $location['sl_pickup_postal_code'],
				),
				'destinations' => array(
					array(
						'address' => post_value('address'),
						'coordinate' => array(
							'latitude' => post_value('latitude'),
							'longitude' => post_value('longitude'),
						),
						'village' => post_value('village'),
						'district' => post_value('district'),
						'city' => post_value('city'),
						'province' => post_value('state'),
						'postalCode' => post_value('postalCode'),
						'recipient' => array(
							'firstName' => post_value('firstName'),
							'email' => post_value('email'),
							'phone' => post_value('phone'),
						),
						'items' => $orderItem
					)
				)
			);

			$post_Params = json_encode($postParams);
			$headers = array(
				"x-api-key:Bearer " . $this->token,
				"x-api-origin:API_SERVICE_BASIC",
				"Content-Type:application/json"
			);
			$priceDetails = loadCurlPost($this->APIURL . 'delivery-partners/prices/all', $headers, $post_Params);
			if (!empty($priceDetails)) {
				if (!empty($priceDetails->data)) {
					$result = array();
					foreach ($priceDetails->data as $val) {
						$result[$val->vehicleType]['vehicleType'] = ucwords($val->vehicleType);
						$result[$val->vehicleType]['vehicle'][] = $val;
					}
					$result = array_values($result);
					$return_array = array('status' => "ok", 'result' => $result);
					$this->set_response($return_array, success_response());
				} else {
					$this->set_response(array(
						'status' => 'error',
						'message' => 'No Record found',
					), something_wrong()); /* error message */
				}
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => 'No Record found',
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

	public function createPartnerOrder_post()
	{
		$unquieid = post_value('unquieid');
		app_validation($unquieid);
		$this->form_validation->set_rules('locationID', 'lang:locationID', 'required');
		$this->form_validation->set_rules('address', 'lang:address', 'required');
		$this->form_validation->set_rules('latitude', 'lang:deliverylatitude', 'required');
		$this->form_validation->set_rules('longitude', 'lang:deliverylongitude', 'required');
		$this->form_validation->set_rules('state', 'lang:deliverystate', 'required');
		$this->form_validation->set_rules('postalCode', 'lang:deliverypostalCode', 'required');
		$this->form_validation->set_rules('firstName', 'lang:rest_customer_fname', 'required');
		$this->form_validation->set_rules('email', 'lang:rest_customer_email', 'required');
		$this->form_validation->set_rules('phone', 'lang:rest_customer_mobile_no', 'required');
		if ($this->form_validation->run() == TRUE) {
			$customerID = decode_value(post_value('customerID'));
			$locationID = decode_value(post_value('locationID'));
			$location = $this->Mydb->get_record("sl_name, sl_location_email, sl_location_phone, sl_pickup_postal_code, sl_latitude, sl_longitude, sl_pickup_address_line1, sl_pickup_city, sl_pickup_province, sl_pickup_district, sl_pickup_village", $this->siteLocation, array('sl_location_id' => $locationID));
			$deliveryPartner = $this->object_to_array(json_decode($this->input->post('deliveryPartner')));
			$delivery_delivery_order_id = 'UV-' . $customerID . '-' . time();
			$postParams = array(
				"amount" => $deliveryPartner['amount'],
				"customerId" => $customerID,
				"merchantOrderId" => $delivery_delivery_order_id,
				"deliveryToken" => "",
				"deliveryPartnerId" => $deliveryPartner['deliveryPartnerId'],
				"serviceType" => $deliveryPartner['serviceType'],
				"vehicleType" => $deliveryPartner['vehicleType'],
				"sender" => array(
					'firstName' => $location['sl_name'],
					'lastName' => $location['sl_name'],
					'email' => $location['sl_location_email'],
					'phone' => $location['sl_location_phone'],
					"instruction" => ""
				),
				"origin" => array(
					'address' => $location['sl_pickup_address_line1'],
					'keywords' => 'test',
					'coordinate' => array(
						'latitude' => $location['sl_latitude'],
						'longitude' => $location['sl_longitude'],
					),
					'village' => $location['sl_pickup_village'],
					'district' => $location['sl_pickup_district'],
					'city' => $location['sl_pickup_city'],
					'province' => $location['sl_pickup_province'],
					'postalCode' => $location['sl_pickup_postal_code']
				),
				'destinations' => array(
					array(
						"itemCategoryId" => "ixxiIBKFbIgUgFZhVWsj",
						"weightCategoryId" => "Q7uZUL6nmdEpaQNfOqAO",
						"dimensionCategoryId" => "2vMV629jDxUp7PfkEC1Y",
						"dimension" => array(
							"height" => 5,
							"width" => 5,
							"depth" => 5,
							"weight" => 5
						),
						'address' => post_value('address'),
						"keywords" => "tets",
						'coordinate' => array(
							'latitude' => post_value('latitude'),
							'longitude' => post_value('longitude'),
						),
						'village' => post_value('village'),
						'district' => post_value('district'),
						'city' => post_value('city'),
						'province' => post_value('state'),
						'postalCode' => post_value('postalCode'),
						'recipient' => array(
							'firstName' => post_value('firstName'),
							"lastName" => post_value('lastName'),
							'email' => post_value('email'),
							'phone' => post_value('phone'),
							"title" => "",
							"companyName" => "",
							"instruction" => ""
						)
					)
				)
			);

			$post_Params = json_encode($postParams);

			$headers = array(
				"x-api-key:Bearer " . $this->token,
				"x-api-origin:API_SERVICE_BASIC",
				"Content-Type:application/json"
			);
			$priceDetails = loadCurlPost($this->APIURL . 'orders', $headers, $post_Params);

			$data = array(
				'delivery_company_unique_id' => $unquieid,
				'delivery_customer_id' =>  $customerID,
				'delivery_delivery_order_id' => $delivery_delivery_order_id,
				'delivery_request' => $post_Params,
				'delivery_response' => (!empty($priceDetails)) ? json_encode($priceDetails) : '',
				'delivery_created_on' => get_ip(),
				'delivery_created_ip' => current_date(),
			);
			$delivery_id = $this->Mydb->insert($this->uv_delivery, $data);

			if (!empty($priceDetails)) {
				if (!empty($priceDetails->data)) {
					$data = array(
						'delivery_uv_id' => $priceDetails->data
					);
					$this->Mydb->update($this->uv_delivery, array('delivery_id' => $delivery_id), $data);

					$return_array = array('status' => "ok", 'deliveryOrderID' => $priceDetails->data);
					$this->set_response($return_array, success_response());
				} else {
					if (!empty($priceDetails->meta)) {
						$this->set_response(array(
							'status' => 'error',
							'message' => $priceDetails->meta->message,
						), something_wrong()); /* error message */
					} else {
						$this->set_response(array(
							'status' => 'error',
							'message' => 'No Record found',
						), something_wrong()); /* error message */
					}
				}
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => 'No Record found',
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

	public function confirmPartnerOrder_post()
	{
		$unquieid = post_value('unquieid');
		app_validation($unquieid);
		$this->form_validation->set_rules('deliveryOrderID', 'lang:deliveryorderID', 'required');
		if ($this->form_validation->run() == TRUE) {

			$customerID = decode_value(post_value('customerID'));
			$deliveryOrderID = post_value('deliveryOrderID');
			$checkingDelivery = $this->Mydb->get_record('*', $this->uv_delivery, array('delivery_company_unique_id' => $unquieid, 'delivery_customer_id' => $customerID, 'delivery_uv_id' => $deliveryOrderID));
			if (!empty($checkingDelivery)) {
				$headers = array(
					"x-api-key:Bearer " . $this->token,
					"x-api-origin:WOOCOMMERCE_BASIC",
					"Content-Type: application/json"
				);
				$post_Params = array();
				$this->APIURL . 'orders/' . $deliveryOrderID . '/execute';
				$executeDetails = loadCurlPatch($this->APIURL . 'orders/' . $deliveryOrderID . '/execute', $headers, $post_Params);
				if (!empty($executeDetails)) {
					$uptateArray = array(
						'delivery_execute_response' => json_encode($executeDetails),
						'delivery_uv_delivery_id' => (!empty($executeDetails->data)) ? $executeDetails->data->deliveryId : ''
					);
					$this->Mydb->update($this->uv_delivery, array('delivery_uv_id' => $deliveryOrderID), $uptateArray);
					if (!empty($executeDetails->data)) {
						$return_array = array('status' => "ok", 'message' => $executeDetails->meta->message, 'deliveryId' => $executeDetails->data->deliveryId);
						$this->set_response($return_array, success_response());
					} else {

						if (!empty($executeDetails->meta)) {
							$this->set_response(array(
								'status' => 'error',
								'message' => $executeDetails->meta->message,
							), something_wrong()); /* error message */
						} else {

							$this->set_response(array(
								'status' => 'error',
								'message' => get_label('invalid_delivery_order'),
							), something_wrong()); /* error message */
						}
					}
				} else {

					$this->set_response(array(
						'status' => 'error',
						'message' => get_label('invalid_delivery_order'),
					), something_wrong()); /* error message */
				}
			} else {

				$this->set_response(array(
					'status' => 'error',
					'message' => get_label('invalid_delivery_order'),
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
