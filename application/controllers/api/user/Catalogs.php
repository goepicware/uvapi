<?php

/**************************
Project Name	: White Label
Created on		: 09 Oct, 2023
Last Modified 	: 09 Oct, 2023
Description		: Catalogs related functions

 ***************************/
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Catalogs extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->products = "products";
		$this->assigned_outlets = "product_assigned_outlets";
		$this->categories = "product_categories";
		$this->subcategories = "product_subcategories";
		$this->outlet_management = "outlet_management";
		$this->site_location = "site_location";
		$this->categoryoutlets = "category_assigned_outlets";
		$this->subcategoryoutlets = "sub_category_assigned_outlets";
		$this->product_combos = "product_combos";
		$this->product_groups_details = "product_groups_details";
	}
	public function listcategory_get()
	{
		$company = app_validation($this->get('unquieid')); /* validate app */
		$company_id = $company['company_id'];
		$storeID = decode_value(post_value('storeID'));
		$join = array();
		$i = 0;
		$join[$i]['select']    = "";
		$join[$i]['condition'] = "pao_category_primary_id = pro_cate_primary_id";
		$join[$i]['table']     = $this->categoryoutlets;
		$join[$i]['type']      = "INNER";
		$i++;

		$join[$i]['select']    = "";
		$join[$i]['condition'] = "pro_cate_id = product_category_id";
		$join[$i]['table']     = $this->products;
		$join[$i]['type']      = "INNER";
		$i++;

		$join[$i]['select']    = "";
		$join[$i]['condition'] = "pao_category_primary_id = pro_subcate_category_primary_id";
		$join[$i]['table']     = $this->subcategories;
		$join[$i]['type']      = "INNER";
		$i++;
		$where = array(
			'pro_cate_company_id' => $company_id,
			'pro_cate_status' => 'A',
			'pro_subcate_status' => 'A',
			'product_status' => 'A',
			'pro_cate_custom_title!=' => NULL,
			'pro_cate_custom_title!=' => ""
		);
		if (!empty($storeID)) {
			$where = array_merge($where, array('pao_outlet_id' => $storeID));
		}
		$category = $this->Mydb->get_all_records('pro_cate_custom_title AS categoryName, pro_cate_image AS categoryImage, pro_cate_slug AS catSlug',  $this->categories, $where, '', '', array('pro_cate_sequence' => 'ASC'), '', array('pro_cate_primary_id'), $join);

		if (!empty($category)) {
			$return_array = array(
				'status' => "ok",
				'result' => $category
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
	public function listproducts_get()
	{

		$company = app_validation($this->get('unquieid')); /* validate app */
		$company_id = $company['company_id'];
		$cateSlug = $this->get('cateSlug');
		$subcateSlug = $this->get('subcateSlug');
		$storeID = decode_value($this->get('storeID'));
		$where = array(
			'product_company_id' => $company_id,
			'product_status' => 'A'
		);
		if (!empty($storeID)) {
			$where = array_merge($where, array('pao_outlet_id' => $storeID));
		}
		if (!empty($cateSlug)) {
			$where = array_merge($where, array('pro_cate_slug' => $cateSlug));
		}
		if (!empty($subcateSlug)) {
			$where = array_merge($where, array('pro_subcate_slug' => $subcateSlug));
		}

		$orderDate = date('Y-m-d');

		$join = array();
		$i = 0;
		$join[$i]['select']    = "";
		$join[$i]['condition'] = "product_primary_id = pao_product_primary_id";
		$join[$i]['table']     = $this->assigned_outlets;
		$join[$i]['type']      = "INNER";
		$i++;

		$join[$i]['select']    = "";
		$join[$i]['condition'] = "product_category_id = pro_cate_id";
		$join[$i]['table']     = $this->categories;
		$join[$i]['type']      = "INNER";
		$i++;

		$join[$i]['select']    = "";
		$join[$i]['condition'] = "product_subcategory_id = pro_subcate_id";
		$join[$i]['table']     = $this->subcategories;
		$join[$i]['type']      = "INNER";
		$i++;

		$products = $this->Mydb->get_all_records('product_id, product_slug, product_type, product_name, product_alias, product_sku, product_short_description, product_minimum_quantity, product_maximum_quantity, product_thumbnail, product_cost, product_price, product_special_price, product_special_price_from_date, product_special_price_to_date', $this->products, $where, '', '', '', '', '', $join);
		if (!empty($products)) {
			foreach ($products as $key => $val) {
				$products[$key]['specialPriceApplicable'] = "No";
				if (!empty($val['product_special_price']) && $val['product_special_price'] > 0 && !empty($val['product_special_price_from_date']) && !empty($val['product_special_price_to_date'])) {
					if ($val['product_special_price_from_date'] <= $orderDate  && $val['product_special_price_to_date'] >= $orderDate) {
						$products[$key]['specialPriceApplicable'] = "Yes";
					}
				}
			}
			$return_array = array(
				'status' => "ok",
				'result' => $products
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

	public function recomentproducts_get()
	{

		$company = app_validation($this->get('unquieid')); /* validate app */
		$company_id = $company['company_id'];
		$latitude = $this->get('latitude');
		$longitude = $this->get('longitude');

		$type = $this->get('type');
		$where = array(
			'product_company_id' => $company_id,
			'product_status' => 'A'
		);
		if (!empty($type)) {
			$like = array('product_recommendation' => $type);
			//$where = array_merge($where, array("product_recommendation LIKE %" . $type . "%" => NULL));
		}
		$join = array();
		$i = 0;
		$join[$i]['select']    = "";
		$join[$i]['condition'] = "product_primary_id = pao_product_primary_id";
		$join[$i]['table']     = $this->assigned_outlets;
		$join[$i]['type']      = "INNER";
		$i++;

		$join[$i]['select']    = "outlet_slug AS outletSlug, outlet_name AS outletName, 111.111 *
		DEGREES(ACOS(LEAST(1.0, COS(RADIANS(' . $latitude . '))
			 * COS(RADIANS(outlet_marker_latitude))
			 * COS(RADIANS(' . $longitude . ' - outlet_marker_longitude))
			 + SIN(RADIANS(' . $latitude . '))
			 * SIN(RADIANS(outlet_marker_latitude))))) AS distance";
		$join[$i]['condition'] = "pao_outlet_id = outlet_id";
		$join[$i]['table']     = $this->outlet_management;
		$join[$i]['type']      = "INNER";
		$i++;

		$join[$i]['select']    = "pro_cate_name AS catName, pro_cate_custom_title AS customTitle, pro_cate_slug AS catSlug";
		$join[$i]['condition'] = "product_category_id = pro_cate_id";
		$join[$i]['table']     = $this->categories;
		$join[$i]['type']      = "INNER";
		$i++;

		$join[$i]['select']    = "pro_subcate_name AS subCatName, pro_subcate_slug AS subCatSlug";
		$join[$i]['condition'] = "product_subcategory_id = pro_subcate_id";
		$join[$i]['table']     = $this->subcategories;
		$join[$i]['type']      = "INNER";
		$i++;

		$products = $this->Mydb->get_all_records('product_id, product_slug, product_type, product_name, product_alias, product_sku, product_short_description, product_minimum_quantity, product_maximum_quantity, product_thumbnail, product_cost, product_price, product_tag_info AS tagInfo', $this->products, $where, '', '', '', $like, '', $join);
		if (!empty($products)) {
			$return_array = array(
				'status' => "ok",
				'result' => $products
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
	public function productdetails_get()
	{

		$company = app_validation($this->get('unquieid')); /* validate app */
		$company_id = $company['company_id'];
		$productSlug = $this->get('productSlug');
		$storeID = decode_value($this->get('storeID'));
		$where = array(
			'product_company_id' => $company_id,
			'product_status' => 'A'
		);
		if (!empty($storeID)) {
			$where = array_merge($where, array('pao_outlet_id' => $storeID));
		}
		if (!empty($productSlug)) {
			$where = array_merge($where, array('product_slug' => $productSlug));
		}

		$join = array();
		$i = 0;
		$join[$i]['select']    = "";
		$join[$i]['condition'] = "product_primary_id = pao_product_primary_id";
		$join[$i]['table']     = $this->assigned_outlets;
		$join[$i]['type']      = "INNER";
		$i++;

		$join[$i]['select']    = "";
		$join[$i]['condition'] = "product_category_id = pro_cate_id";
		$join[$i]['table']     = $this->categories;
		$join[$i]['type']      = "INNER";
		$i++;

		$join[$i]['select']    = "";
		$join[$i]['condition'] = "product_subcategory_id = pro_subcate_id";
		$join[$i]['table']     = $this->subcategories;
		$join[$i]['type']      = "INNER";
		$i++;

		$products = $this->Mydb->get_all_records('product_primary_id, product_id, product_slug, product_type, product_name, product_alias, product_sku, product_short_description AS short_description, product_long_description AS long_description, product_minimum_quantity, product_maximum_quantity, product_thumbnail, product_gallery,  product_cost, product_price, product_apply_minmax_select, product_special_price, product_special_price_from_date, product_special_price_to_date', $this->products, $where, '', '', '', '', '', $join);
		if (!empty($products)) {
			$result = $products[0];
			$orderDate = date('Y-m-d');
			$result['specialPriceApplicable'] = "No";
			if (!empty($result['product_special_price']) && $result['product_special_price'] > 0 && !empty($result['product_special_price_from_date']) && !empty($result['product_special_price_to_date'])) {
				if ($result['product_special_price_from_date'] <= $orderDate  && $result['product_special_price_to_date'] >= $orderDate) {
					$result['specialPriceApplicable'] = "Yes";
				}
			}
			unset($result['product_special_price_from_date']);
			unset($result['product_special_price_to_date']);
			$result['comobset'] = array();
			if ($result['product_type'] == "2") {
				$combo_products = $this->Mydb->get_all_records('combo_id, combo_name, combo_max_select AS max_select, combo_min_select AS min_select, combo_sort_order AS sequence, combo_price_apply AS apply_price, combo_modifier_apply AS apply_modifier, combo_disable_validation AS disable_validation, combo_pieces_count AS piecescount, single_selection AS single_selection, combo_multipleselection_apply AS multipleselection_apply', $this->product_combos, array('combo_product_primary_id' => $result['product_primary_id']));

				$finalCombo = array();
				$finalComobProducts = array();
				if (!empty($combo_products)) {
					$comobID = array_column($combo_products, 'combo_id');
					if (!empty($comobID)) {
						$comobID = implode(',', $comobID);
						$comboWhere = "pro_combo_id IN (" . $comobID . ")";
						$join = array();
						$i = 0;
						$join[$i]['select'] = "product_primary_id, product_id, product_name, product_sku, product_alias, product_price, product_status";
						$join[$i]['table'] = $this->products;
						$join[$i]['condition'] = "pro_product_id = product_id";
						$join[$i]['type'] = "LEFT";
						$i++;

						$join[$i]['select'] = "pro_group_name";
						$join[$i]['table'] = 'product_groups';
						$join[$i]['condition'] = "pos_cp.pro_group_id = pro_group_primary_id";
						$join[$i]['type'] = "LEFT";
						$comboProList = $this->Mydb->get_all_records('pro_combo_id, pro_is_default, pos_cp.pro_group_id', 'combo_products AS pos_cp', $comboWhere, null, null, array('product_sequence' => 'ASC'), null, null, $join);
						if (!empty($comboProList)) {
							foreach ($comboProList as $val) {
								if (empty($val['pro_group_id'])) {
									$productStatus = $val['product_status'];
									$pro_combo_id = $val['pro_combo_id'];
									unset($val['product_status']);
									unset($val['pro_combo_id']);
									unset($val['pro_group_id']);
									if ($productStatus == 'A') {
										unset($val['pro_group_name']);
										$finalCombo[$pro_combo_id][] = $val;
									}
								}
							}
							$comobGroupID = array_filter(array_unique(array_column($comboProList, 'pro_group_id')));
							if (!empty($comobGroupID)) {
								$comboGroupWhere = "group_detail_group_id IN (" . $comobID . ") AND product_status='A'";
								$join = array();
								$i = 0;
								$join[$i]['select'] = "product_primary_id, product_id, product_name, product_sku, product_alias, product_price";
								$join[$i]['table'] = $this->products;
								$join[$i]['condition'] = "group_detail_product_id = product_id";
								$join[$i]['type'] = "INNER";
								$i++;
								$comboGruopProList = $this->Mydb->get_all_records('group_detail_group_id', $this->product_groups_details, $comboGroupWhere, null, null, array('product_sequence' => 'ASC'), null, null, $join);
								if (!empty($comboGruopProList)) {
									foreach ($comboGruopProList as $val) {
										$pro_combo_id = $val['group_detail_group_id'];
										unset($val['group_detail_group_id']);
										$val['pro_is_default'] = "No";
										$finalCombo[$pro_combo_id][] = $val;
									}
								}
							}
						}
					}

					foreach ($combo_products as $key => $val) {
						$finalComobProducts[$key] = $val;
						$finalComobProducts[$key]['products'] = (!empty($finalCombo[$val['combo_id']])) ? $finalCombo[$val['combo_id']] : array();
					}
				}
				$result['comobset'] = $finalComobProducts;
			}
			$finalresult[0] = $result;
			$return_array = array(
				'status' => "ok",
				'result' => $finalresult
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
