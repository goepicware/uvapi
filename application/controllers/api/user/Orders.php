<?php

/**************************
Project Name	: White Label
Created on		: 27 Oct, 2023
Last Modified 	: 27 Oct, 2023
Description		: Orders related functions

 ***************************/
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Orders extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->table = "orders";
		$this->customer_details = "orders_customer_details";
		$this->orderitems = "order_items";
		$this->combotable = "order_menu_set_components";
		$this->outlet = "outlet_management";
		$this->order_status = "order_status";
		$this->uv_payment = "uv_payment";
		$this->promotion_history = "promotion_history";
		$this->order_outlet = "order_outlet";
		$this->load->library('form_validation');
		$this->load->helper('order');
	}

	public function placeorder_post()
	{
		$unquieid = post_value('unquieid');
		$company = app_validation(post_value('unquieid'));
		$company_id = $company['company_id'];
		$this->form_validation->set_rules('customerID', 'lang:rest_customer_id', 'required');
		$this->form_validation->set_rules('subTotal', 'lang:rest_sub_total', 'required');
		$this->form_validation->set_rules('grandTotal', 'lang:rest_grand_total', 'required');
		$this->form_validation->set_rules('availabilityID', 'lang:rest_availability_id', 'required');
		$this->form_validation->set_rules('products', 'lang:rest_products', 'required');
		$this->form_validation->set_rules('locationID', 'lang:location_id', 'required');
		if ($this->form_validation->run() == TRUE) {
			$customerID = decode_value(post_value('customerID'));
			$locationID = decode_value(post_value('locationID'));
			$orderDate = post_value('orderDate');
			$instantOrder = post_value('instantOrder');
			$zoneID = (post_value('zoneID') != "") ? decode_value(post_value('zoneID')) : '';
			if ($instantOrder == 'Yes') {
				$orderDate = date('Y-m-d H:i:s');
			}
			$availabilityID = post_value('availabilityID');
			$availabilityName = post_value('availabilityName');
			$startTime = post_value('startTime');
			$endTime = post_value('endTime');
			$orderSource = post_value('orderSource');

			$company_array = array(
				'order_company_unique_id' => $unquieid
			);

			$order_id = get_guid($this->table, 'order_id', $company_array);
			$local_order_no = get_local_ordeno($unquieid, $orderSource);
			$products = $this->object_to_array(json_decode($this->input->post('products')));
			$totalDiscount = post_value('totalDiscount');
			$discountType = post_value('discountType');
			$totalItem = post_value('totalItem');

			$data = array(
				'order_company_id' => $company_id,
				'order_company_unique_id' => $unquieid,
				'order_id' => $order_id,
				'order_location_id' => $locationID,
				'order_local_no' => $local_order_no,
				'order_delivery_charge' => post_value('deliveryCharge'),
				'order_additional_delivery' => post_value('additionalDeliveryCharge'),
				'order_tax_charge' => post_value('taxCharge'),
				'order_tax_calculate_amount' =>  post_value('taxAmount'),
				'order_tax_charge_inclusive' => '',
				'order_tax_calculate_amount_inclusive' => '',
				'order_sub_total' =>  post_value('subTotal'),
				'order_total_amount' => post_value('grandTotal'),
				'order_date' => $orderDate,
				'order_status' => 1,
				'order_availability_id' => $availabilityID,
				'order_availability_name' => $availabilityName,
				'order_pickup_time_slot_from' => (!empty($startTime)) ? $startTime : '',
				'order_pickup_time_slot_to' => (!empty($endTime)) ? $endTime : '',
				'order_source' => post_value('orderSource'),
				'order_payment_getway_type' => post_value('paymentGetway'),
				'order_payment_mode' => post_value('paymentMethod'),
				'order_payment_getway_status' => ucwords(post_value('paymentStatus')),
				'order_zone_id' => $zoneID,
				'order_discount_applied' => (!empty($discountType)) ? 'Yes' : 'No',
				'order_discount_type' => (!empty($discountType)) ? $discountType : '',
				'order_discount_amount' => ($totalDiscount > 0) ? $totalDiscount : '',
				'order_created_on' => current_date(),
			);
			$order_primary_id = $this->Mydb->insert($this->table, $data);
			if (!empty($order_primary_id)) {
				$this->createOrderCustomer($order_primary_id, $order_id, $customerID);
				$this->createItems($order_primary_id, $order_id, $products);
				$this->createOrderOutlet($company_id, $unquieid, $order_primary_id,  $products);
				$discountDetails = (!empty($this->input->post('discountDetails'))) ? $this->object_to_array(json_decode($this->input->post('discountDetails'))) : [];
				if (!empty($discountDetails)) {
					$this->createPromotion($unquieid, $company_id, $customerID, $totalItem, post_value('subTotal'), $order_primary_id, $order_id, $discountDetails);
				}
				$paymentReferenceID = post_value('paymentReferenceID');
				if (!empty($paymentReferenceID)) {
					$this->Mydb->update($this->uv_payment, array('payment_order_id' => $paymentReferenceID), array('order_primary_id' => $order_primary_id));
				}
				$emailArray = array(
					'email_order_id' => $order_primary_id,
					'email_company_id' => $company_id,
					'email_company_unique_id' => $unquieid,
					'email_created_on' => current_date(),
					'email_status' => 'Pending'
				);
				$this->Mydb->insert('email_notification', $emailArray);


				$return_array = array(
					'status' => "ok",
					'message' => get_label('rest_order_success'),
					'result' => array(
						'order_id' => $order_id,
						'local_order_no' => $local_order_no,
						'order_primary_id' => $order_primary_id
					)
				);
				$this->set_response($return_array, success_response());
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => get_label('order_creation_error'),
					'form_error' => validation_errors()
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
	public function orderList_get()
	{
		$unquieid = $this->get('unquieid');
		$company = app_validation($unquieid);
		$orderType = $this->get('orderType');
		$customerID = decode_value($this->get('customerID'));

		$where = "order_company_unique_id='" . $unquieid . "' AND order_customer_id='" . $customerID . "'";
		if (!empty($orderType)) {
			if ($orderType == "Ongoing") {
				$where .= " AND  order_status!='4' AND order_status!='5'";
			} else if ($orderType == "Ongoing") {
				$where .= " AND  (order_status='1' OR order_status='2' OR order_status='3')";
			} else if ($orderType == "completed") {
				$where .= " AND  order_status='4'";
			} else if ($orderType == "canceled") {
				$where .= " AND  order_status='5'";
			}
		}
		$join = array();
		$i = 0;
		$join[$i]['select']    = 'CONCAT(order_customer_fname, " ", order_customer_lname) AS custmoerName, order_customer_email AS email, order_customer_mobile_no AS phone';
		$join[$i]['condition'] = "order_primary_id = order_customer_order_primary_id";
		$join[$i]['table']     = $this->customer_details;
		$join[$i]['type']      = "INNER";
		$i++;

		$join[$i]['select']    = 'SUM(item_qty) AS totalItem, GROUP_CONCAT(item_outlet_id) AS storeID';
		$join[$i]['condition'] = "order_primary_id = item_order_primary_id";
		$join[$i]['table']     = $this->orderitems;
		$join[$i]['type']      = "INNER";
		$i++;

		/* $join[$i]['select']    = 'GROUP_CONCAT(outlet_name) AS storeName, GROUP_CONCAT(outlet_image) AS storeImage';
		$join[$i]['condition'] = "item_outlet_id = outlet_id";
		$join[$i]['table']     = $this->outlet;
		$join[$i]['type']      = "INNER";
		$i++;
 */
		$join[$i]['select']    = 'status_name';
		$join[$i]['condition'] = "order_status = status_id";
		$join[$i]['table']     = $this->order_status;
		$join[$i]['type']      = "INNER";
		$i++;

		$ordersList = $this->Mydb->get_all_records('order_primary_id, order_local_no AS orderNumber, order_total_amount, order_driver_phone, order_driver_name, order_delivary_type AS deliverySource, order_date, order_total_amount AS totalAmount, order_status AS orderStatus',  $this->table, $where, '', '', array('order_date' => 'ASC'), '', array('order_primary_id'), $join);
		if (!empty($ordersList)) {
			$storeID = array_column($ordersList, 'storeID');
			if (!empty($storeID)) {
				$storeID = implode(',', array_filter(array_unique($storeID)));

				$storeWhere = "outlet_id IN (" . $storeID . ")";
				$storeDetails = $this->Mydb->get_all_records('outlet_id, outlet_name, outlet_image', $this->outlet, $storeWhere);

				if (!empty($storeDetails)) {
					$storeDetail = array_combine(array_column($storeDetails, 'outlet_id'), $storeDetails);
					foreach ($ordersList as $key => $val) {
						$storeID = (!empty($val['storeID'])) ? array_unique(explode(',', $val['storeID'])) : '';
						unset($ordersList[$key]['storeID']);
						if (!empty($storeID)) {
							$storeDetails = array();
							foreach ($storeID as $storeKey => $store) {
								$storeDetails[$storeKey]['storeID'] = $storeKey;
								$storeDetails[$storeKey]['name'] = (!empty($storeDetail[$store])) ? $storeDetail[$store]['outlet_name'] : '';
								$storeDetails[$storeKey]['image'] = (!empty($storeDetail[$store])) ? $storeDetail[$store]['outlet_image'] : '';
							}
							$ordersList[$key]['store'] = array_values($storeDetails);
						}
					}
				}
			}
		}

		$orderWhere = "order_company_unique_id='" . $unquieid . "' AND order_customer_id='" . $customerID . "'";
		$join = array();
		$i = 0;
		$join[$i]['select']    = '';
		$join[$i]['condition'] = "order_primary_id = order_customer_order_primary_id";
		$join[$i]['table']     = $this->customer_details;
		$join[$i]['type']      = "INNER";
		$i++;
		$totalOrders = $this->Mydb->get_all_records('COUNT(order_status) AS orderCount, order_status',  $this->table, $orderWhere, '', '', '', '', array('order_status'), $join);
		$resultOrderCount = (!empty($totalOrders)) ? array_combine(array_column($totalOrders, 'order_status'), $totalOrders) : '';
		$return_array = array(
			'status' => "ok",
			'result' => $ordersList,
			'ordercount' => $resultOrderCount,
		);
		$this->set_response($return_array, success_response());
	}
	public function orderDetails_get()
	{
		$unquieid = $this->get('unquieid');
		app_validation($unquieid);
		$customerID = decode_value($this->get('customerID'));
		$orderNumber = $this->get('orderNumber');

		$where = "order_company_unique_id='" . $unquieid . "' AND order_customer_id='" . $customerID . "' AND order_local_no='" . $orderNumber . "'";

		$join = array();
		$i = 0;
		$join[$i]['select']    = 'CONCAT(order_customer_fname, " ", order_customer_lname) AS custmoerName, order_customer_email AS email, order_customer_mobile_no AS phone, order_customer_address_line1 AS address,  order_customer_remarks AS addressRemarks';
		$join[$i]['condition'] = "order_primary_id = order_customer_order_primary_id";
		$join[$i]['table']     = $this->customer_details;
		$join[$i]['type']      = "INNER";
		$i++;

		$join[$i]['select']    = 'status_name';
		$join[$i]['condition'] = "order_status = status_id";
		$join[$i]['table']     = $this->order_status;
		$join[$i]['type']      = "INNER";
		$i++;

		$ordersList = $this->Mydb->get_all_records('order_primary_id, order_local_no AS orderNumber, order_total_amount, order_driver_phone, order_delivary_type AS deliverySource, order_date, order_total_amount AS totalAmount, order_sub_total AS subTotal, order_status AS orderStatus, order_delivery_charge AS deliveryCharge',  $this->table, $where, '', '', array('order_date' => 'ASC'), '', array('order_primary_id'), $join);
		if (!empty($ordersList)) {
			$orderItem = $this->Mydb->get_all_records('item_id AS itemID, item_outlet_id AS storeID, item_product_id AS itemProductID, item_name AS itemName, item_image AS itemImage, item_sku AS itemSKU, item_specification AS itemNote, item_qty AS itemQuantity, item_unit_price aS itemPrice, item_total_amount AS itemTotalPrice ', $this->orderitems, array('item_order_primary_id' => $ordersList[0]['order_primary_id']));
			$order_items = array();
			if (!empty($orderItem)) {
				$orderOutlet = implode(',', array_filter(array_unique(array_column($orderItem, 'storeID'))));
				$outletWhere = "outlet_id IN (" . $orderOutlet . ")";
				$outlets = $this->Mydb->get_all_records('outlet_id, outlet_name, outlet_image', $this->outlet, $outletWhere);
				$outletList = (!empty($outlets)) ? array_combine(array_column($outlets, 'outlet_id'), $outlets) : [];

				$orderItemID = implode(',', array_filter(array_unique(array_column($orderItem, 'itemID'))));
				$comobWhere = "menu_order_primary_id='" . $ordersList[0]['order_primary_id'] . "' AND menu_item_id IN (" . $orderItemID . ")";
				$comobProduct = $this->Mydb->get_all_records('menu_menu_component_id, menu_menu_component_name, menu_product_id, menu_product_name, menu_product_sku, menu_product_qty, menu_product_price, menu_item_id', $this->combotable, $comobWhere);

				$comobSet = array();
				if (!empty($comobProduct)) {
					foreach ($comobProduct as $val) {
						$orderItemID = $val['menu_item_id'];
						$comboSets[$orderItemID][$val['menu_menu_component_id']]['comboSetId'] = $val['menu_menu_component_id'];
						$comboSets[$orderItemID][$val['menu_menu_component_id']]['comboSetname'] = $val['menu_menu_component_name'];
						$comboSets[$orderItemID][$val['menu_menu_component_id']]['productDetails'][] = array(
							'productID' => $val['menu_product_id'],
							'productName' => $val['menu_product_name'],
							'productSKU' => $val['menu_product_sku'],
							'productPrice' =>  $val['menu_product_price'],
							'quantity' => $val['menu_product_qty'],
						);
					}
				}

				foreach ($orderItem as $key => $val) {
					$order_items[$val['storeID']]['storeID'] = $val['storeID'];
					$order_items[$val['storeID']]['storeName'] = (!empty($outletList[$val['storeID']])) ? $outletList[$val['storeID']]['outlet_name'] : '';
					$order_items[$val['storeID']]['storeImage'] = (!empty($outletList[$val['storeID']])) ? $outletList[$val['storeID']]['outlet_image'] : '';
					$val['comboset'] = (!empty($comboSets[$val['itemID']])) ? array_values($comboSets[$val['itemID']]) : array();
					$order_items[$val['storeID']]['items'][] = $val;
				}
			}
			$ordersList[0]['orderTotalItems'] = count($orderItem);
			$ordersList[0]['orderItems'] = (!empty($order_items)) ? array_values($order_items) : [];

			$ordersList[0]['discount'] = $this->Mydb->get_all_records('promotion_history_promocode AS promoCode, promotion_history_applied_amt AS promoAmount', $this->promotion_history, array('promotion_history_order_primary_id' => $ordersList[0]['order_primary_id']));
		}

		$return_array = array(
			'status' => "ok",
			'result' => $ordersList,
		);
		$this->set_response($return_array, success_response());
	}

	public	function generatePDF_get($unquieid = null, $company = array(), $orderDetails = "", $returnData = "No")
	{
		if (empty($orderDetails)) {
			$unquieid = $this->get('unquieid');
			$company = app_validation($unquieid);
			$order_primary_id = $this->get('orderID');
			$download = $this->get('download');

			$company_currency = $company['company_currency'];

			$where = array(
				'order_primary_id' => $order_primary_id,
				'order_company_unique_id' => $unquieid
			);
			$orderDetails = orderDetails($where);
		}

		if (!empty($orderDetails)) {
			/* load  data */
			$data = $orderDetails;
			$data['company'] = $company;
			$data['company_currency'] = $company_currency;
			$file_location = '';
			$pdfNameTxt = 'order-' . date('YmdHis') . '-' . $orderDetails['order_list'][0]['order_local_no'] . '.pdf';

			$html = $this->load->view("order_pdf_view", $data, true);

			$file_location = FCPATH . "media/" . "order-pdf/" . $pdfNameTxt;

			// Include the main TCPDF library (search for installation path).
			require(FCPATH . 'application/libraries/TCPDF-master/tcpdf.php');

			// create new PDF document
			$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

			// set document information
			$pdf->SetCreator(PDF_CREATOR);
			$pdf->SetAuthor('Ninja POS');
			$pdf->SetTitle('Order');

			// set default monospaced font
			$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

			// set margins
			$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
			$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
			$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

			// set auto page breaks
			$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

			// set image scale factor
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

			// set some language-dependent strings (optional)
			if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
				require_once(dirname(__FILE__) . '/lang/eng.php');
				$pdf->setLanguageArray($l);
			}

			// ---------------------------------------------------------

			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);

			// set font
			$pdf->SetFont('dejavusans', '', 10);

			// add a page
			$pdf->AddPage();


			// output the HTML content
			$pdf->writeHTML($html, true, false, true, false, '');

			// reset pointer to the last page
			$pdf->lastPage();
			// ---------------------------------------------------------
			//Close and output PDF document
			if (!empty($download)) {
				$pdf->Output($pdfName, 'D');
				exit;
			} else {
				$pdf->Output($file_location, 'F');
			}

			$pdf_url = PDF_SOURCE . $pdfNameTxt;

			if ($pdf_url != '') {
				if ($returnData == "Yes") {
					return array(
						'status' => 'ok',
						'pdf_url' => $pdf_url,
						'message' => get_label('reset_pdf_success')
					);
				} else {
					$this->response(array(
						'status' => 'ok',
						'pdf_url' => $pdf_url,
						'message' => get_label('reset_pdf_success')
					), success_response());
				}
			} else {

				$this->response(array(
					'status' => 'error',
					'message' => get_label('reset_pdf_failure')
				), success_response());
			}
		} else {
			$this->response(array(
				'status' => 'error',
				'message' => get_label('reset_pdf_failure')
			), success_response());
		}
	}

	public	function sendMail_get()
	{

		$unquieid = $this->get('unquieid');
		$company = app_validation($unquieid);
		$order_primary_id = $this->get('orderID');
		$orderNumber = $this->get('orderNumber');
		if (!empty($order_primary_id) || !empty($orderNumber)) {
			$method = $this->get('method');
			$sendwithattach = $this->get('sendwithattach');
			$where = array(
				'order_company_unique_id' => $unquieid
			);
			if (!empty($order_primary_id)) {
				$order_primary_id = decode_value($order_primary_id);
				$where = array_merge($where, array(
					'order_primary_id' => $order_primary_id
				));
			} else if (!empty($orderNumber)) {
				$where = array_merge($where, array(
					'order_local_no' => $orderNumber
				));
			}

			$orderDetails = orderDetails($where);

			if (!empty($orderDetails)) {
				$allowSentMail = 1;
				if (!empty($method)) {
					if ($method == 'direct') {
						$checkingEmail = $this->Mydb->get_record('email_status', 'email_notification', array('email_order_id' => $orderDetails['order_list'][0]['order_primary_id'], 'email_status' => 'Pending'));
						if (empty($checkingEmail)) {
							$checkingEmail = 0;
						}
					}
				}
				if ($allowSentMail == 1) {
					/* load  data */
					$data = $orderDetails;
					$data['company'] = $company;
					$orderDetails = orderDetails($where);
					$pdfURL = "";
					if (!empty($sendwithattach) && $sendwithattach == 'Yes') {
						$pdf_data = $this->generatePDF_get($unquieid, $company, $orderDetails, 'Yes');
						$pdfURL = (!empty($pdf_data['pdf_url'])) ? $pdf_data['pdf_url'] : "";
					}

					$content = $this->load->view("order_email", $data, true);
					$email_template_id = get_emailtemplate($unquieid, 'orderemail');
					if (!empty($email_template_id)) {
						$check_arr = array('[NAME]', '[COMPANY_NAME]', '[ORDER_DETAILS]');
						$replace_arr = array(ucfirst(stripslashes($order_list[0]['customer_name'])), $company['company_name'], $content);

						$this->load->library('myemail');
						$mailSent = $this->myemail->send_client_mail($company, 'praba9717@gmail.com', $email_template_id, $check_arr,  $replace_arr, $pdfURL);
						if (!empty($mailSent)) {
							$this->Mydb->update('email_notification', array('email_order_id' => $order_primary_id), array('email_status' => 'Sent', 'email_updated_on' => current_date()));
						} else {
							$this->Mydb->update('email_notification', array('email_order_id' => $order_primary_id), array('email_status' => 'Failed', 'email_updated_on' => current_date()));
						}
						$this->response(array(
							'status' => 'ok',
							'message' => get_label('email_sent_success')
						), success_response());
					}
				} else {
					$this->response(array(
						'status' => 'error',
						'message' => get_label('invalid_order_id')
					), success_response());
				}
			} else {
				$this->response(array(
					'status' => 'error',
					'message' => get_label('email_already_sent')
				), success_response());
			}
		} else {
			$this->response(array(
				'status' => 'error',
				'message' => get_label('order_id_req')
			), success_response());
		}
	}

	private function createOrderCustomer($order_primary_id, $order_id, $customerID)
	{

		$data = array(
			'order_customer_order_primary_id' => $order_primary_id,
			'order_customer_order_id' => $order_id,
			'order_customer_id' => $customerID,
			'order_customer_fname' => post_value('fitstName'),
			'order_customer_lname' => post_value('lastName'),
			'order_customer_email' => post_value('email'),
			'order_customer_mobile_no' => post_value('phone'),
			'order_customer_address_line1' => post_value('address'),
			'order_customer_city' =>  post_value('city'),
			'order_customer_state' => post_value('state'),
			'order_customer_country' => post_value('country'),
			'order_customer_postal_code' =>  post_value('postalCode'),
			'order_customer_billing_address_line1' => post_value('billingaddress'),
			'order_customer_billing_postal_code' => post_value('billingpostalCode'),
			'order_customer_created_on' => current_date(),
		);
		$this->Mydb->insert($this->customer_details, $data);
	}

	private function createOrderOutlet($company_id, $unquieid, $order_primary_id, $products)
	{
		$outletList = [];
		if (!empty($products)) {
			foreach ($products as $item) {
				$outletList[$item['shopID']][] = $item['itemTotalPrice'];
			}
		}
		if (!empty($outletList)) {
			foreach ($outletList as $outletID => $outletAmt) {
				if (!empty($outletAmt)) {
					$data = array(
						'outlet_company_id' => $company_id,
						'outlet_company_unique_id' => $unquieid,
						'outlet_order_primary_id' => $order_primary_id,
						'outlet_id' => $outletID,
						'outlet_sub_total_amount' => array_sum($outletAmt),
						'outlet_grand_total_amount' => array_sum($outletAmt),
					);
					$this->Mydb->insert($this->order_outlet, $data);
				}
			}
		}
	}



	private function createItems($order_primary_id, $order_id, $products)
	{
		if (!empty($products)) {
			foreach ($products as $item) {
				$data = array(
					'item_order_primary_id' => $order_primary_id,
					'item_order_id'	=> $order_id,
					'item_outlet_id' => $item['shopID'],
					'item_product_id'	=> $item['productID'],
					'item_name'	=> $item['itemName'],
					'item_image' => $item['itemImage'],
					'item_sku' => $item['itemSKU'],
					'item_specification' => $item['itemRemarks'],
					'item_qty'	=> $item['itemQuantity'],
					'item_unit_price' => $item['itemPrice'],
					'item_original_unit_price' => $item['itemPrice'],
					'item_total_amount' => $item['itemTotalPrice'],
					'item_created_on'	=> current_date(),

				);
				$itemID = $this->Mydb->insert($this->orderitems, $data);
				$comobSet = (!empty($item['comobSet'])) ? $item['comobSet'] : [];
				if (!empty($comobSet)) {
					$this->createComobSet($order_primary_id, $order_id, $itemID, $comobSet);
				}
			}
		}
	}

	private function createComobSet($order_primary_id, $order_id, $itemID, $comobSet)
	{
		if (!empty($comobSet)) {
			foreach ($comobSet as $combo) {
				if (!empty($combo['productDetails'])) {
					foreach ($combo['productDetails'] as $product) {
						$comboItem = array(
							'menu_order_primary_id' => $order_primary_id,
							'menu_item_id' => $itemID,
							'menu_order_id' => $order_id,
							'menu_menu_component_id' => $combo['comboSetId'],
							'menu_menu_component_name' => $combo['comboSetname'],
							'menu_product_id' => $product['productID'],
							'menu_product_name' => $product['productName'],
							'menu_product_sku' => $product['productSKU'],
							'menu_product_qty' => $product['quantity'],
							'menu_product_price' => $product['productPrice'],
							'menu_created_on' => current_date(),
						);
						$this->Mydb->insert($this->combotable, $comboItem);
					}
				}
			}
		}
	}

	private function createPromotion($unquieid, $company_id, $customerID, $totalItem, $subTotal, $order_primary_id, $order_id, $discountDetails)
	{
		if (!empty($discountDetails)) {
			foreach ($discountDetails as $promo) {
				$promoItem = array(
					'promotion_history_company_unique_id' => $unquieid,
					'promotion_history_company_id' => $company_id,
					'promotion_history_customer_id' => $customerID,
					'promotion_history_order_primary_id' => $order_primary_id,
					'promotion_history_order_id' => $order_id,
					'promotion_history_promotion_id' => $promo['promotion_id'],
					'promotion_history_promocode' => $promo['promotion_code'],
					'promotion_history_cart_quantity' => $totalItem,
					'promotion_history_cart_amount' => $subTotal,
					'promotion_history_category_id' => $promo['promotion_category'],
					'promotion_history_applied_amt' => $promo['promotion_amount'],
					'promotion_history_delivery_charge' => $promo['promotion_delivery_charge_applied'],
					'promotion_history_created_on' => current_date(),
					'promotion_history_created_ip' => get_ip(),
				);
				$this->Mydb->insert($this->promotion_history, $promoItem);
				echo $this->db->last_query();
			}
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