<?php

/**************************
Project Name	: White Label
Created on		: 18 Aug, 2023
Last Modified 	: 04 Sep, 2023
Description		: Page contains common REST settings
 ***************************/

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/* this function used validate APP ID */
if (!function_exists('orderDetails')) {
	function orderDetails($where)
	{
		$CI = &get_instance();
		$i = 0;
		$join[$i]['select'] = "order_customer_id,CONCAT(order_customer_fname, ' ', order_customer_lname) AS customer_name,order_customer_email,order_customer_mobile_no,
		order_customer_unit_no1,order_customer_unit_no2,order_customer_tower_number,order_customer_address_line1,order_customer_address_line2,order_customer_city,
		order_customer_state,order_customer_country,order_customer_postal_code,order_customer_created_on";
		$join[$i]['table'] = "pos_orders_customer_details";
		$join[$i]['condition'] = "order_customer_order_primary_id = order_primary_id";
		$join[$i]['type'] = "LEFT";
		$i++;

		$join[$i]['select'] = "outlet_name,outlet_delivery_timing,outlet_unit_number1,outlet_unit_number2,outlet_address_line1,
		outlet_address_line2,outlet_postal_code,outlet_phone";
		$join[$i]['table'] = "pos_outlet_management";
		$join[$i]['condition'] = "order_outlet_id = pos_outlet_management.outlet_id";
		$join[$i]['type'] = "LEFT";
		$i++;

		$join[$i]['select'] = "company_name,company_owner_name,company_site_url,company_address, company_unit_number, company_floor_number, company_postal_code,company_logo,company_country,company_folder_name,company_email_address";
		$join[$i]['table'] = "company";
		$join[$i]['condition'] = "order_company_id = company_id";
		$join[$i]['type'] = "LEFT";
		$i++;

		$join[$i]['select'] = "order_method_name";
		$join[$i]['table'] = "pos_order_methods";
		$join[$i]['condition'] = "order_payment_mode = order_method_id  ";
		$join[$i]['type'] = "LEFT";


		/* order table values */
		$select_values = array(
			'order_primary_id',
			'order_id',
			'order_location_id',
			'order_shop_type',
			'order_outlet_id',
			'order_delivery_charge',
			'order_tax_charge',
			'order_tax_calculate_amount',
			'order_discount_applied',
			'order_discount_amount',
			'order_sub_total',
			'order_total_amount',
			'order_payment_mode',
			'order_date',
			'order_status',
			'order_availability_id',
			'order_availability_name',
			'order_pickup_time',
			'order_pickup_outlet_id',
			'order_source',
			'order_callcenter_admin_id',
			'order_local_no',
			'order_created_on',
			'order_tat_time',
			'order_remarks',
			'order_company_unique_id',
			'order_pickup_time_slot_from',
			'order_pickup_time_slot_to',
			'order_additional_delivery',
			'order_service_charge',
			'order_service_charge_amount',
			'order_voucher_discount_amount',
		);

		/* get order details */
		$order_list = $CI->Mydb->get_all_records($select_values, "orders", $where, '', '', '', '', '', $join);
		$order_items = array();
		if (!empty($order_list)) {
			foreach ($order_list as $orkey => $odlist) {

				$orderItem = $CI->Mydb->get_all_records('item_id AS itemID, item_outlet_id AS storeID, item_product_id AS itemProductID, item_name AS itemName, item_image AS itemImage, item_sku AS itemSKU, item_specification AS itemNote, item_qty AS itemQuantity, item_unit_price aS itemPrice, item_total_amount AS itemTotalPrice ', "order_items", array('item_order_primary_id' => $odlist['order_primary_id']));

				if (!empty($orderItem)) {
					$orderOutlet = implode(',', array_filter(array_unique(array_column($orderItem, 'storeID'))));
					$outletWhere = "outlet_id IN (" . $orderOutlet . ")";
					$outlets = $CI->Mydb->get_all_records('outlet_id, outlet_name, outlet_image', 'outlet_management', $outletWhere);
					$outletList = (!empty($outlets)) ? array_combine(array_column($outlets, 'outlet_id'), $outlets) : [];

					$orderItemID = implode(',', array_filter(array_unique(array_column($orderItem, 'itemID'))));
					$comobWhere = "menu_order_primary_id='" . $odlist['order_primary_id'] . "' AND menu_item_id IN (" . $orderItemID . ")";
					$comobProduct = $CI->Mydb->get_all_records('menu_menu_component_id, menu_menu_component_name, menu_product_id, menu_product_name, menu_product_sku, menu_product_qty, menu_product_price, menu_item_id', 'order_menu_set_components', $comobWhere);

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
			}

			/* load  data */
			$data['order_list'] = $order_list;
			$data['oder_item'] = $order_items;
			return $data;
		}
	}
}
