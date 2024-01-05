<?php

/**************************
Project Name	: White Label
Created on		: 13 Oct, 2023
Last Modified 	: 13 Oct, 2023
Description		: Cart related functions

 ***************************/
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Cart extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->table = "cart_details";
		$this->cart_items = "cart_items";
		$this->combotable = "cart_menu_set_components";
		$this->products = "products";
		$this->outlet = "outlet_management";
	}
	public function loadCartDetails_get()
	{
		$unquieid = $this->get('unquieid');
		$customerID = decode_value(post_value('customerID'));
		$availabilityID = post_value('availabilityID');
		$cartDetails = $this->Mydb->get_record('cart_id AS cartID, cart_total_items AS totalItem, cart_sub_total AS subTotal, cart_grand_total AS grandTotal', $this->table, array('cart_company_unquie_id' => $unquieid,  'cart_customer_id' => $customerID,  'cart_availability_id' => $availabilityID));
		if (!empty($cartDetails)) {
			$cartItem = $this->Mydb->get_all_records('cart_item_id AS itemID, cart_item_outlet AS storeID, cart_item_product_id AS productID, cart_item_product_name AS itemName, cart_item_product_sku AS itemSKU, cart_item_product_image AS itemImage, cart_item_qty AS itemQuantity, cart_item_unit_price AS itemUnitPrice, cart_item_total_price AS itemTotalPrice, cart_item_special_notes AS itemNotes', $this->cart_items, array('cart_item_cart_id' => $cartDetails['cartID']));
			$outletList = [];
			if (!empty($cartItem)) {
				$ItemoutletIDs = array_filter(array_unique(array_column($cartItem, 'storeID')));
				$Itemoutlet_IDs = (!empty($ItemoutletIDs)) ? implode(',', $ItemoutletIDs) : '';
				if (!empty($Itemoutlet_IDs)) {
					$outletWhere = "outlet_unquie_id='" . $unquieid . "' AND outlet_id IN(" . $Itemoutlet_IDs . ")";
					$outlet = $this->Mydb->get_all_records('outlet_id, outlet_name', $this->outlet, $outletWhere);
					$outletID = array_column($outlet, 'outlet_id');
					$outletName = array_column($outlet, 'outlet_name');
					$finalOutlet = array_combine($outletID, $outletName);
					$ItemIDs = array_filter(array_unique(array_column($cartItem, 'itemID')));
					$comboSets = array();
					if (!empty($ItemIDs)) {
						$ItemIDs = (!empty($ItemIDs)) ? implode(',', $ItemIDs) : '';
						$comboWhere = "cart_menu_component_cart_item_id IN(" . $ItemIDs . ")";
						$comboProduct = $this->Mydb->get_all_records('*', $this->combotable, $comboWhere);
						if (!empty($comboProduct)) {
							foreach ($comboProduct as $val) {
								$cartItemID = $val['cart_menu_component_cart_item_id'];
								$comboSets[$cartItemID][$val['cart_menu_component_id']]['comboSetId'] = $val['cart_menu_component_id'];
								$comboSets[$cartItemID][$val['cart_menu_component_id']]['comboSetname'] = $val['cart_menu_component_name'];
								$comboSets[$cartItemID][$val['cart_menu_component_id']]['productDetails'][] = array(
									'productID' => $val['cart_menu_component_product_id'],
									'productName' => $val['cart_menu_component_product_name'],
									'productSKU' => $val['cart_menu_component_product_sku'],
									'productPrice' =>  $val['cart_menu_component_product_price'],
									'quantity' => $val['cart_menu_component_product_qty'],
								);
							}
						}
					}


					$finalCartItem = array();
					foreach ($cartItem as $val) {
						$storeID = $val['storeID'];
						unset($val['storeID']);
						$outletList[] = $storeID;
						$finalCartItem[$storeID]['storeID'] = $storeID;
						$finalCartItem[$storeID]['storeName'] = (!empty($finalOutlet[$storeID])) ? $finalOutlet[$storeID] : '';
						$val['comboset'] = (!empty($comboSets[$val['itemID']])) ? array_values($comboSets[$val['itemID']]) : array();
						$finalCartItem[$storeID]['item'][] =  $val;
					}


					$cartDetails['store'] = array_values($finalCartItem);
					$cartDetails['storeList'] = array_unique($outletList);
					$return_array = array('status' => "ok", 'result' => $cartDetails);
					$this->set_response($return_array, success_response());
				} else {
					$this->set_response(array(
						'status' => 'error',
						'message' => 'There are no items in your cart',
						'form_error' => ''
					), something_wrong()); /* error message */
				}
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => 'There are no items in your cart',
					'form_error' => ''
				), something_wrong()); /* error message */
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => 'There are no items in your cart',
				'form_error' => ''
			), something_wrong()); /* error message */
		}
	}
	public function createCart_post()
	{
		$unquieid = post_value('unquieid');
		$company = app_validation(post_value('unquieid'));
		$company_id = $company['company_id'];
		$cartID = $this->CartCartDetails($unquieid);
		if (!empty($cartID)) {
			$this->cartItemInsert($cartID);
			$this->updatePrice($cartID);
			$unquieid = post_value('unquieid');
			if (post_value('type') == "update") {
				$return_array = array('status' => "ok", 'message' => get_label('rest_cart_updated'));
			} else {
				$return_array = array('status' => "ok", 'message' => get_label('rest_product_added'));
			}
			$this->set_response($return_array, success_response());
		}
	}
	public function updateCartItem_post()
	{
		$unquieid = post_value('unquieid');
		app_validation($unquieid);
		$shopID = decode_value(post_value('shopID'));
		$itemID = post_value('itemID');
		$checkingItem = $this->Mydb->get_record('cart_item_cart_id, cart_item_unit_price', $this->cart_items, array('cart_item_id' => $itemID, 'cart_item_outlet' => $shopID));
		if (!empty($checkingItem)) {
			$quantity = post_value('quantity');
			if ($quantity > 0) {
				$totalPrice = (float)$checkingItem['cart_item_unit_price'] * (int)$quantity;
				$data = array(
					'cart_item_qty' => $quantity,
					'cart_item_total_price' => $totalPrice,
					'cart_item_updated_on' => current_date(),
				);
				$this->Mydb->update($this->cart_items, array('cart_item_id' => $itemID), $data);
			} else {
				$this->Mydb->delete($this->cart_items, array('cart_item_id' => $itemID));
			}
			$this->updatePrice($checkingItem['cart_item_cart_id']);
			$return_array = array('status' => "ok", 'message' => get_label('rest_cart_updated'));
			$this->set_response($return_array, success_response());
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => get_label('rest_invalid_cart_item'),
				'form_error' => ''
			), something_wrong()); /* error message */
		}
	}
	public function deleteCartItem_post()
	{
		$unquieid = post_value('unquieid');
		app_validation($unquieid);
		$itemID = post_value('itemID');
		if (!empty($itemID)) {
			$itemIDs = explode(',', $itemID);
			$checkingItem = $this->Mydb->get_record('cart_item_cart_id, cart_item_unit_price', $this->cart_items, array('cart_item_id' => $itemIDs[0]));
			if (!empty($checkingItem)) {
				$deleteWhere = " cart_item_id IN (" . $itemID . ")";
				$this->Mydb->delete($this->cart_items, $deleteWhere);
				$this->updatePrice($checkingItem['cart_item_cart_id']);
				$return_array = array('status' => "ok", 'message' => get_label('rest_cart_updated'));
				$this->set_response($return_array, success_response());
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => get_label('rest_invalid_cart_item'),
					'form_error' => ''
				), something_wrong()); /* error message */
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => get_label('rest_invalid_cart_item'),
				'form_error' => ''
			), something_wrong()); /* error message */
		}
	}

	public function removeCart_post()
	{
		$unquieid = post_value('unquieid');
		app_validation($unquieid);
		if (!empty(post_value('customerID'))) {
			$customerID = decode_value(post_value('customerID'));
			$checkingItem = $this->Mydb->get_record('cart_id', $this->table, array('cart_customer_id' => $customerID));
			if (!empty($checkingItem)) {
				$cartID = $checkingItem['cart_id'];
				$this->Mydb->delete($this->combotable, array('cart_menu_component_cart_id' => $cartID));
				$this->Mydb->delete($this->cart_items, array('cart_item_cart_id' => $cartID));
				$this->Mydb->delete($this->table, array('cart_id' => $cartID));
				$return_array = array('status' => "ok", 'message' => get_label('rest_cart_delete_success'));
				$this->set_response($return_array, success_response());
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => get_label('rest_cart_empty'),
					'form_error' => ''
				), something_wrong()); /* error message */
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => get_label('rest_cart_empty'),
				'form_error' => ''
			), something_wrong()); /* error message */
		}
	}

	function CartCartDetails($unquieid)
	{
		$customerID = decode_value(post_value('customerID'));
		$locationID = decode_value(post_value('locationID'));
		$checkingCart = $this->Mydb->get_record('cart_id', $this->table, array(
			'cart_company_unquie_id'		=> $unquieid,
			'cart_customer_id'		=> $customerID,
			'cart_availability_id' => post_value('availabilityID')
		));
		if (!empty($checkingCart)) {
			$cartID = $checkingCart['cart_id'];
		} else {
			$data = array(
				'cart_company_unquie_id' => $unquieid,
				'cart_customer_id' => $customerID,
				'cart_shop_type' => '1',
				'cart_location_id' => $locationID,
				'cart_availability_id' => post_value('availabilityID'),
				'cart_availability_name' => post_value('availabilityName'),
				'cart_created_on' => current_date(),
			);
			$cartID = $this->Mydb->insert($this->table, $data);
		}
		return $cartID;
	}

	private function updatePrice($cartID)
	{
		$checkingCartItem = $this->Mydb->get_all_records('SUM(cart_item_qty) AS totalItem, SUM(cart_item_unit_price) AS totalUnitPrice, SUM(cart_item_total_price) AS totalPrice',  $this->cart_items, array('cart_item_cart_id' => $cartID), '', '', '', '', 'cart_item_cart_id');
		if (!empty($checkingCartItem)) {
			$totalUnitPrice = $checkingCartItem[0]['totalUnitPrice'];
			$totalPrice = $checkingCartItem[0]['totalPrice'];
			$totalItem = $checkingCartItem[0]['totalItem'];
			$data = array(
				'cart_total_items' => $totalItem,
				'cart_sub_total' => $totalPrice,
				'cart_grand_total' => $totalPrice,
			);
			$this->Mydb->update($this->table, array('cart_id' => $cartID), $data);
		}
	}

	function cartItemInsert($cartID)
	{
		$allowUpdate = "No";
		$productType = $this->input->post('productType');
		if ($productType == 'Simple') {
			$cart_item = $this->Mydb->get_record('cart_item_id, cart_item_qty', $this->cart_items, array(
				'cart_item_cart_id' => $cartID,
				'cart_item_product_id' => post_value('productID')
			));
			if (!empty($cart_item)) {
				$allowUpdate = "Yes";
				$cart_item_id = $cart_item['cart_item_id'];
			}
		}
		$customerID = decode_value(post_value('customerID'));
		$locationID = decode_value(post_value('locationID'));
		$shopID = decode_value(post_value('shopID'));
		$productID = post_value('productID');
		$quantity = post_value('quantity');
		$product = $this->Mydb->get_record('product_price, product_sku, product_name, product_alias, product_thumbnail, product_special_price, product_special_price_from_date, product_special_price_to_date', $this->products, array('product_id' => $productID));
		if (!empty($product)) {
			if ($productType == 'Simple') {

				$orderDate = date('Y-m-d');
				$productPrice =  $product['product_price'];
				if (!empty($product['product_special_price']) && $product['product_special_price'] > 0 && !empty($product['product_special_price_from_date']) && !empty($product['product_special_price_to_date'])) {
					if ($product['product_special_price_from_date'] <= $orderDate  && $product['product_special_price_to_date'] >= $orderDate) {
						$productPrice =  $product['product_special_price'];
					}
				}
			} else {
				$productPrice =  post_value('productPrice');
			}
			$productName = (!empty($product['product_alias'])) ? stripslashes($product['product_alias']) : stripslashes($product['product_name']);
			if ($allowUpdate === "Yes") {
				$totalPrice = (float)$productPrice * (int)$quantity;
				$data = array(
					'cart_item_qty' => $quantity,
					'cart_item_unit_price' => $productPrice,
					'cart_item_actual_unit_price' => $productPrice,
					'cart_item_total_price' => $totalPrice,
					'cart_item_updated_on' => current_date(),
				);
				$this->Mydb->update($this->cart_items, array('cart_item_id' => $cart_item_id), $data);
			} else {

				$totalPrice = (float)$productPrice * (int)$quantity;
				if ($productType == 'Combo') {
					$totalPrice = post_value('productTotalPrice');
				}
				$data = array(
					'cart_item_availability_id'		=> post_value('availabilityID'),
					'cart_item_location_id'	=> $locationID,
					'cart_item_outlet' => $shopID,
					'cart_item_customer_id' => $customerID,
					'cart_item_cart_id' => $cartID,
					'cart_item_product_id' => post_value('productID'),
					'cart_item_product_name' => $productName,
					'cart_item_product_sku' => $product['product_sku'],
					'cart_item_product_image' => (!empty($product['product_thumbnail'])) ? $product['product_thumbnail'] : '',
					'cart_item_qty' => $quantity,
					'cart_item_unit_price' => $productPrice,
					'cart_item_actual_unit_price' => $productPrice,
					'cart_item_total_price' => $totalPrice,
					'cart_item_type' => $productType,
					'cart_item_special_notes' => post_value('specialNotes'),
					'cart_item_created_on' => current_date(),
				);
				$cartItemID = $this->Mydb->insert($this->cart_items, $data);
				if ($productType == 'Combo') {
					$comboset = $_POST['comboset'];
					if (!empty($comboset)) {
						$comboset = $this->object_to_array($comboset);
						$this->CreateComboSet($cartID, $cartItemID, $comboset);
					}
				}
			}
		}
	}
	function CreateComboSet($cartID, $cartItemID, $comboset)
	{

		if (!empty($comboset)) {
			$comboset = json_decode($comboset, true);
			foreach ($comboset as $val) {
				if (!empty($val['productDetails'])) {
					foreach ($val['productDetails'] as $product) {
						$comboItem = array(
							'cart_menu_component_cart_id' => $cartID,
							'cart_menu_component_cart_item_id' => $cartItemID,
							'cart_menu_component_id' => $val['comboSetId'],
							'cart_menu_component_name' => $val['comboSetname'],
							'cart_menu_component_product_id' => $product['productID'],
							'cart_menu_component_product_name' => $product['productName'],
							'cart_menu_component_product_sku' => $product['productSKU'],
							'cart_menu_component_product_qty' => $product['quantity'],
							'cart_menu_component_product_price' => $product['productPrice']
						);
						$this->Mydb->insert($this->combotable, $comboItem);
					}
				}
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
