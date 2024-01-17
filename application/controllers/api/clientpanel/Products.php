<?php

/**************************
Project Name	: White Label
Created on		: 18 Aug, 2023
Last Modified 	: 19 Aug, 2023
Description		: Catelog Category Templates

 ***************************/
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Products extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p>', '</p>');
		$this->table = "products";
		$this->assigned_outlets = "product_assigned_outlets";
		$this->product_availability = "product_availability";
		$this->product_assigned_tags = "product_assigned_tags";
		$this->product_tags = "product_tags";
		$this->product_day_availability = "product_day_availability";
		$this->time_availability = "product_time_availability";
		$this->products_stock = "products_stock_auto_update";
		$this->category = "product_categories";
		$this->subcategory = "product_subcategories";
		$this->product_types = "product_types";
		$this->product_combos = "product_combos";
		$this->load->library('common');
		$this->label = "Product";
		$this->load->library('Authorization_Token');
		$this->primary_key = 'product_primary_id';
		$this->company_id = 'product_company_id';
	}

	public function list_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$logID = decode_value($this->input->get('logID'));
				$userDetails = getUserDetails($logID);
				if (!empty($userDetails)) {
					$select_array = array('product_primary_id', 'product_name', 'product_sku', 'product_sequence', 'product_status', 'product_price', 'product_stock', 'product_created_on');
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
					$storeID = $this->input->get('storeID');
					$name = $this->input->get('name');
					$status = $this->input->get('status');
					$categoryID = $this->input->get('categoryID');
					$subcategoryID = $this->input->get('subcategoryID');
					$where = array("$this->primary_key !=" => '', $this->company_id => $company_id);
					if (!empty($status)) {
						$where = array_merge($where, array('product_status' => $status));
					}
					if (!empty($categoryID)) {
						$where = array_merge($where, array('product_category_id' => $categoryID));
					}
					if (!empty($subcategoryID)) {
						$where = array_merge($where, array('product_subcategory_id' => $subcategoryID));
					}
					if (!empty($storeID)) {
						$where = array_merge($where, array('pao_outlet_id' => $storeID));
					}
					if (!empty($userDetails)) {
						if ($userDetails['company_user_type'] == 'SubAdmin') {
							$where = array_merge($where, array("pao_outlet_id" => $userDetails['company_user_permission_outlet']));
						}
					}
					if (!empty($name)) {
						$like = array("product_name" => $name);
					}

					$order_by = array($this->primary_key => 'DESC');

					$join = array();


					$i = 0;
					$join[$i]['select'] = "product_type_name";
					$join[$i]['table'] = $this->product_types;
					$join[$i]['condition'] = "product_type = product_type_id";
					$join[$i]['type'] = "INNER";
					$i++;

					$join[$i]['select'] = "";
					$join[$i]['table'] = $this->assigned_outlets;
					$join[$i]['condition'] = "product_primary_id = pao_product_primary_id";
					if (!empty($storeID)) {
						$join[$i]['type'] = "INNER";
					} else {
						$join[$i]['type'] = "LEFT";
					}
					$i++;

					$join[$i]['select'] = "outlet_name";
					$join[$i]['table'] = "outlet_management";
					$join[$i]['condition'] = "pao_outlet_id = outlet_id";
					$join[$i]['type'] = "INNER";
					$i++;

					$join[$i]['select'] = "pro_cate_name";
					$join[$i]['table'] = $this->category;
					$join[$i]['condition'] = "product_category_id = pro_cate_id";
					$join[$i]['type'] = "INNER";
					$i++;

					$join[$i]['select'] = "pro_subcate_name";
					$join[$i]['table'] = $this->subcategory;
					$join[$i]['condition'] = "product_subcategory_id = pro_subcate_id";
					$join[$i]['type'] = "INNER";

					$total_records = $this->Mydb->get_num_join_rows($this->primary_key, $this->table, $where, null, null, null, $like, null, $join);

					$totalPages = (!empty($limit)) ? ceil($total_records / $limit) : 0;

					$result = $this->Mydb->get_all_records($select_array, $this->table, $where, $limit, $offset,  $order_by, $like, array($this->primary_key), $join);
					if (!empty($result)) {
						$return_array = array('status' => "ok", 'message' => 'success', 'result' => $result, 'totalRecords' => $total_records, 'totalPages' => $totalPages);
						$this->set_response($return_array, success_response());
					} else {
						$this->set_response(array('status' => 'error', 'message' => get_label('no_records_found')), something_wrong());
					}
				} else {
					$this->set_response(array(
						'status' => 'error',
						'message' => get_label('invalid_user'),
						'form_error' => ''
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

	public function listProductSetup_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$return_array = array('status' => "ok", 'message' => 'success', 'result' => array('recommendation'));
				$this->set_response($return_array, success_response());
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

	public function dropdownlist_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$company_id = decode_value($this->input->get('company_id'));
				$select_array = array(
					$this->primary_key,
					'product_primary_id',
					'product_id',
					'product_name'
				);
				$where = array(
					$this->company_id => $company_id,
					'product_status' => 'A'
				);
				$result = $this->Mydb->get_all_records($select_array, $this->table, $where);
				if (!empty($result)) {
					$return_array = array('status' => "ok", 'message' => 'success', 'result' => $result);
					$this->set_response($return_array, success_response());
				} else {
					$this->set_response(array('status' => 'error', 'message' => get_label('no_records_found')), something_wrong());
				}
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => get_label('token_verify_faild'),
				), something_wrong()); /* error message */
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => get_label('token_faild'),
			), something_wrong()); /* error message */
		}
	}

	public function dropdownlistWithCategory_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$company_id = decode_value($this->input->get('company_id'));
				$storeID = $this->input->get('storeID');
				$join = array();
				$i = 0;

				$join[$i]['select'] = "";
				$join[$i]['table'] = $this->assigned_outlets;
				$join[$i]['condition'] = "product_primary_id = pao_product_primary_id";
				if (!empty($storeID)) {
					$join[$i]['type'] = "INNER";
				} else {
					$join[$i]['type'] = "LEFT";
				}
				$i++;

				$join[$i]['select'] = "pro_cate_primary_id, pro_cate_name";
				$join[$i]['table'] = $this->category;
				$join[$i]['condition'] = "product_category_id = pro_cate_id";
				$join[$i]['type'] = "INNER";
				$i++;

				$join[$i]['select'] = "pro_subcate_primary_id, pro_subcate_name";
				$join[$i]['table'] = $this->subcategory;
				$join[$i]['condition'] = "product_subcategory_id = pro_subcate_id";
				$join[$i]['type'] = "INNER";

				$select_array = array(
					$this->primary_key,
					'product_primary_id',
					'product_id',
					'product_name'
				);
				$where = array('product_company_id' => $company_id);
				if (!empty($storeID)) {
					$where = array_merge($where, array('pao_outlet_id' => $storeID));
				}
				$result = $this->Mydb->get_all_records($select_array, $this->table, $where, $limit, $offset,  array(
					$this->primary_key => 'DESC'
				), '', array($this->primary_key), $join);

				if (!empty($result)) {
					$finalResult = array();
					foreach ($result as $val) {
						$finalResult[$val['pro_cate_primary_id']]['cate_id'] =  $val['pro_cate_primary_id'];
						$finalResult[$val['pro_cate_primary_id']]['cate_name'] =  $val['pro_cate_name'];
						$finalResult[$val['pro_cate_primary_id']]['sub_category'][$val['pro_subcate_primary_id']]['subcate_id'] =  $val['pro_subcate_primary_id'];
						$finalResult[$val['pro_cate_primary_id']]['sub_category'][$val['pro_subcate_primary_id']]['subcate_name'] =  $val['pro_subcate_name'];
						$finalResult[$val['pro_cate_primary_id']]['sub_category'][$val['pro_subcate_primary_id']]['products'][$val['product_primary_id']]['product_primary_id'] =  $val['product_id'];
						$finalResult[$val['pro_cate_primary_id']]['sub_category'][$val['pro_subcate_primary_id']]['products'][$val['product_primary_id']]['product_id'] =  $val['product_id'];
						$finalResult[$val['pro_cate_primary_id']]['sub_category'][$val['pro_subcate_primary_id']]['products'][$val['product_primary_id']]['product_name'] =  $val['product_name'];
					}
					$final_Result = array();
					if (!empty($finalResult)) {
						$i = 0;
						foreach ($finalResult as $valu) {
							$final_Result[$i]['cate_id'] = $valu['cate_id'];
							$final_Result[$i]['cate_name'] = $valu['cate_name'];
							$j = 0;
							foreach ($valu['sub_category'] as  $value) {
								$final_Result[$i]['sub_category'][$j]['subcate_id'] = $value['subcate_id'];
								$final_Result[$i]['sub_category'][$j]['subcate_name'] = $value['subcate_name'];
								$final_Result[$i]['sub_category'][$j]['products'] = array_values($value['products']);
								$j++;
							}
							$i++;
						}
					}

					$return_array = array('status' => "ok", 'message' => 'success', 'result' => $final_Result);
					$this->set_response($return_array, success_response());
				} else {
					$this->set_response(array('status' => 'error', 'message' => get_label('no_records_found')), something_wrong());
				}
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => get_label('token_verify_faild'),
				), something_wrong()); /* error message */
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => get_label('token_faild'),
			), something_wrong()); /* error message */
		}
	}

	public function simpleproductlist_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$company_id = decode_value($this->input->get('company_id'));
				$storeID = $this->input->get('storeID');
				$select_array = array(
					$this->primary_key,
					'product_primary_id',
					'product_id',
					'product_name'
				);
				$where = array(
					$this->company_id => $company_id,
					'product_status' => 'A',
					'product_type' => 1
				);
				if (!empty($storeID)) {
					$where = array_merge($where, array('pao_outlet_id' => $storeID));
				}

				$join = array();
				$i = 0;
				$join[$i]['select'] = "";
				$join[$i]['table'] = $this->assigned_outlets;
				$join[$i]['condition'] = "product_primary_id = pao_product_primary_id";
				$join[$i]['type'] = "INNER";

				$result = $this->Mydb->get_all_records($select_array, $this->table, $where, null, null,  array(
					$this->primary_key => 'DESC'
				), '', array($this->primary_key), $join);
				if (!empty($result)) {
					$return_array = array('status' => "ok", 'message' => 'success', 'result' => $result);
					$this->set_response($return_array, success_response());
				} else {
					$this->set_response(array('status' => 'error', 'message' => get_label('no_records_found')), something_wrong());
				}
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => get_label('token_verify_faild'),
				), something_wrong()); /* error message */
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => get_label('token_faild'),
			), something_wrong()); /* error message */
		}
	}

	public function productType_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$select_array = array(
					'product_type_id AS value',
					'product_type_name AS label',

				);
				$where = array(
					'product_type_status' => 'A'
				);
				$result = $this->Mydb->get_all_records($select_array, $this->product_types, $where);
				if (!empty($result)) {
					$return_array = array('status' => "ok", 'message' => 'success', 'result' => $result);
					$this->set_response($return_array, success_response());
				} else {
					$this->set_response(array('status' => 'error', 'message' => get_label('no_records_found')), something_wrong());
				}
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => get_label('token_verify_faild'),
				), something_wrong()); /* error message */
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => get_label('token_faild'),
			), something_wrong()); /* error message */
		}
	}

	public function dropdownalllist_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$company_id = decode_value($this->input->get('company_id'));
				$select_array = array(
					$this->primary_key,
					'pro_cate_id',
					'pro_cate_name',

				);
				$where = array(
					$this->company_id => $company_id,
					'pro_cate_status' => 'A'
				);
				$result = $this->Mydb->get_all_records($select_array, $this->table, $where);
				if (!empty($result)) {
					$return_array = array('status' => "ok", 'message' => 'success', 'result' => $result);
					$this->set_response($return_array, success_response());
				} else {
					$this->set_response(array('status' => 'error', 'message' => get_label('no_records_found')), something_wrong());
				}
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => get_label('token_verify_faild'),
				), something_wrong()); /* error message */
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => get_label('token_faild'),
			), something_wrong()); /* error message */
		}
	}

	public function details_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$id = decode_value($this->input->get('detail_id'));
				$company_id = decode_value($this->input->get('company_id'));
				$where = array($this->primary_key => $id, $this->company_id => $company_id);
				$result = $this->Mydb->get_record('*', $this->table, $where);
				if (!empty($result)) {
					$producttype =  $result['product_type'];
					$product_type = $this->Mydb->get_record('product_type_id AS value, product_type_name As label', $this->product_types, array('product_type_id' => $producttype));
					$result['product_type'] = $product_type;

					if ($producttype == '2') {
						$finalComobProducts = array();
						$combo_products = $this->Mydb->get_all_records('combo_id, combo_name, combo_max_select AS max_select, combo_min_select AS min_select, combo_sort_order AS sequence, combo_price_apply AS apply_price, combo_modifier_apply AS apply_modifier, combo_disable_validation AS disable_validation, combo_pieces_count AS piecescount, single_selection AS single_selection, combo_multipleselection_apply AS multipleselection_apply', $this->product_combos, array('combo_product_primary_id' => $result['product_primary_id']));
						if (!empty($combo_products)) {
							$comobID = array_column($combo_products, 'combo_id');
							$finalCombo = array();
							if (!empty($comobID)) {
								$comobID = implode(',', $comobID);
								$comboWhere = 'pro_combo_id IN (' . $comobID . ')';
								$join = array();
								$i = 0;
								$join[$i]['select'] = "product_primary_id, product_name";
								$join[$i]['table'] = $this->table;
								$join[$i]['condition'] = "pro_product_id = product_id";
								$join[$i]['type'] = "LEFT";
								$i++;

								$join[$i]['select'] = "pro_group_name";
								$join[$i]['table'] = 'product_groups';
								$join[$i]['condition'] = "pos_cp.pro_group_id = pro_group_primary_id";
								$join[$i]['type'] = "LEFT";
								$comboProList = $this->Mydb->get_all_records('pro_combo_id, pro_product_id, pro_is_default, pos_cp.pro_group_id', 'combo_products AS pos_cp', $comboWhere, null, null, null, null, null, $join);

								if (!empty($comboProList)) {
									foreach ($comboProList as $val) {
										$finalCombo[$val['pro_combo_id']][] = $val;
									}
								}
							}
							foreach ($combo_products as $key => $val) {
								$comboPro = $comboGroup = array();
								$defaultproduct = "";
								if (!empty($finalCombo[$val['combo_id']])) {
									foreach ($finalCombo[$val['combo_id']] as $value) {
										if (!empty($value['pro_product_id'])) {
											$comboPro[] = array('value' => $value['pro_product_id'] . '_' . $value['pro_product_id'], 'label' => $value['product_name']);
										} else if (!empty($value['pro_group_id'])) {
											$comboGroup[] = array('value' => $value['pro_group_id'], 'label' => $value['pro_group_name']);
										}
										if ($val['pro_is_default'] == 'Yes' && !empty($value['pro_product_id'])) {
											$defaultproduct = array('value' => $value['pro_product_id'] . '_' . $value['pro_product_id'], 'label' => $value['product_name']);
										}
									}
								}

								$finalComobProducts[$key] = $val;
								$finalComobProducts[$key]['products'] = $comboPro;
								$finalComobProducts[$key]['combogroup'] = $comboGroup;
								$finalComobProducts[$key]['defaultproduct'] = $defaultproduct;
							}
						}

						$result['combo_products'] = $finalComobProducts;
					} else {
						$result['combo_products'] = array();
					}

					$time_availability = $this->Mydb->get_all_records('avbl_based_on, avbl_time_type, avbl_days, avbl_str_time, avbl_end_time, avbl_str_datetime, avbl_end_datetime, avbl_stock, avbl_stock_validate', $this->time_availability, array('avbl_product_primary_id' => $result['product_primary_id']));
					$result['time_availability'] = $time_availability;



					$subcategory = $this->Mydb->get_record('CONCAT(pro_subcate_category_id, "_", pro_subcate_id) value, pro_subcate_name As label', $this->subcategory, array('pro_subcate_id' => $result['product_subcategory_id']));
					$result['subcategory'] = $subcategory;

					$join = array();
					$join[0]['select'] = "av_name AS label";
					$join[0]['table'] = "availability";
					$join[0]['condition'] = "product_availability_id = av_id";
					$join[0]['type'] = "INNER";
					$outlet_availability = $this->Mydb->get_all_records('av_id AS value', $this->product_availability, array('product_availability_product_primary_id' => $result['product_primary_id']), null, null, null, null, null, $join);
					$result['availability'] = $outlet_availability;


					$join = array();
					$join[0]['select'] = "outlet_name AS label";
					$join[0]['table'] = "outlet_management";
					$join[0]['condition'] = "pao_outlet_id = outlet_id";
					$join[0]['type'] = "INNER";
					$outlet_availability = $this->Mydb->get_all_records('pao_outlet_id AS value', $this->assigned_outlets, array('pao_product_primary_id' => $result['product_primary_id']), null, null, null, null, null, $join);
					$result['outlet'] = $outlet_availability;
					$result['voucher_free_product'] = "";
					$result['voucher_included_product'] = array();
					$result['voucher_reedmed_availability'] = array();
					if ($producttype === "5" && !empty($result['product_voucher_food_normal_product_id'])) {
						$result['voucher_free_product'] = $this->Mydb->get_record('CONCAT(product_primary_id, "_", product_id) value, product_name label', $this->table, array('product_id' => $result['product_voucher_food_normal_product_id']));


						$voucher_included_product = $this->Mydb->get_all_records("GROUP_CONCAT(food_discount_product_id, ',') AS food_discount_product_id", 'product_voucher_food_discount', array('food_discount_food_product_id' => $result['product_primary_id']), null, null, null, null, array('food_discount_food_product_id'));
						if (!empty($voucher_included_product)) {

							$discountProductId = "'" . implode("','", explode(",", $voucher_included_product[0]['food_discount_product_id'])) . "'";

							$foodProWhere = "product_id IN (" . $discountProductId . ")";
							$VoucherIncludePro = $this->Mydb->get_all_records('CONCAT(product_primary_id, "_", product_id) value, product_name label', $this->table, $foodProWhere);
							$result['voucher_included_product'] = $VoucherIncludePro;
						}

						if (!empty($result['product_voucher_reedmed_availability'])) {

							$reedmed_availability = "'" . implode("','", explode(",", $result['product_voucher_reedmed_availability'])) . "'";
							$whereAvail = "av_id IN (" .   $reedmed_availability . ")";

							$result['voucher_reedmed_availability'] = $this->Mydb->get_all_records('av_name AS label, av_id AS value', 'pos_availability', $whereAvail);
						}
					}


					$join = array();
					$join[0]['select'] = "pro_tag_name AS label";
					$join[0]['table'] = $this->product_tags;
					$join[0]['condition'] = "tag_id = pro_tag_id";
					$join[0]['type'] = "INNER";
					$result['assign_tags'] = $this->Mydb->get_all_records('tag_id AS value', $this->product_assigned_tags, array('tag_product_primary_id' => $result['product_primary_id'], 'pro_tag_status' => 'A'), null, null, null, null, null, $join);

					$return_array = array('status' => "ok", 'message' => 'success', 'result' => $result);
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

	public function add_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {

				$this->form_validation->set_rules('product_type', 'lang:product_type', 'required');
				$this->form_validation->set_rules('product_name', 'lang:rest_product_name', 'required|callback_productname_exists');
				$this->form_validation->set_rules('sku', 'lang:product_sku', 'required');
				$this->form_validation->set_rules('stock', 'lang:prod_avail_stock', 'required|numeric');
				$this->form_validation->set_rules('price', 'lang:product_price', 'required');
				$this->form_validation->set_rules('category', 'lang:product_categorie', 'required');
				$this->form_validation->set_rules('status', 'lang:status', 'required');
				if ($this->form_validation->run() == TRUE) {

					$this->addedit();
					$this->set_response(array(
						'status' => 'success',
						'message' => sprintf(get_label('success_message_add'), $this->label),
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

	public function update_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$this->form_validation->set_rules('product_type', 'lang:product_type', 'required');
				$this->form_validation->set_rules('product_name', 'lang:rest_product_name', 'required|callback_productname_exists');
				$this->form_validation->set_rules('sku', 'lang:product_sku', 'required');
				$this->form_validation->set_rules('stock', 'lang:prod_avail_stock', 'required|numeric');
				$this->form_validation->set_rules('price', 'lang:product_price', 'required');
				$this->form_validation->set_rules('category', 'lang:product_categorie', 'required');
				$this->form_validation->set_rules('status', 'lang:status', 'required');
				if ($this->form_validation->run() == TRUE) {
					$edit_id = $this->input->post('edit_id');
					$this->addedit($edit_id);
					$this->set_response(array(
						'status' => 'success',
						'message' => sprintf(get_label('success_message_edit'), $this->label),
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

	public function delete_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$company_id = decode_value($this->input->post('company_id'));
				$delete_id = decode_value($this->input->post('delete_id'));
				$company_admin_id = decode_value($this->input->post('company_admin_id'));
				if (!empty($company_id)) {
					$getCompanyDetails = getCompanyUniqueID($company_id);

					$where = array(
						$this->primary_key => trim($delete_id)
					);
					$result = $this->Mydb->get_record($this->primary_key . ', product_id, product_name', $this->table, $where);
					if (!empty($result)) {
						$this->Mydb->delete($this->table, array($this->primary_key => $result[$this->primary_key]));

						$this->Mydb->delete($this->product_availability, array('product_availability_product_primary_id' => $result[$this->primary_key]));
						$this->Mydb->delete($this->assigned_outlets, array('pao_product_primary_id' => $result[$this->primary_key]));
						$this->Mydb->delete($this->product_assigned_tags, array('tag_product_primary_id' => $result[$this->primary_key]));

						$this->Mydb->delete($this->time_availability, array(
							'avbl_company_app_id' => $getCompanyDetails,
							'avbl_product_primary_id' => $result[$this->primary_key],
							'avbl_product_id' => $result['product_id']
						));

						$all_combos = $this->Mydb->get_all_records('combo_id', $this->product_combos, array('combo_product_primary_id' => $result[$this->primary_key]));
						if (!empty($all_combos)) {
							$combo_in = array_column($all_combos, 'combo_id');
							$this->Mydb->delete_where_in('combo_products', 'pro_combo_id', $combo_in, array(
								'pro_combo_id !=' => "",
							));
							$this->Mydb->delete_where_in($this->product_combos, 'combo_id', $combo_in, array(
								'combo_id !=' => "",
							));
						}

						$this->Mydb->delete('product_voucher_food_discount', array(
							'food_discount_food_product_id' => $result[$this->primary_key]
						));

						createAuditLog("Product", stripslashes($result['product_name']), "Delete", $company_admin_id, 'Web', '', $company_id, $getCompanyDetails);

						$return_array = array('status' => "ok", 'message' => sprintf(get_label('success_message_delete'), $this->label));
						$this->set_response($return_array, success_response());
					} else {
						$this->set_response(array('status' => 'error', 'message' => get_label('no_records_found'), 'form_error' => ''), something_wrong());
					}
				} else {
					$this->set_response(array('status' => 'error', 'message' => sprintf(get_label('field_required'), 'Company ID'), 'form_error' => ''), something_wrong());
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
		$product_name = post_value('product_name');
		$product_type = post_value('product_type');
		$parantProduct = (post_value('product_type') != "") ? explode('_', post_value('product_type')) : "";

		$category = (post_value('category') != "") ? explode('_', post_value('category')) : "";

		$voucher_type = post_value('voucher_type');
		$voucher_free_products = (post_value('voucher_free_products') != "" && $voucher_type == 'f') ? explode('_', post_value('voucher_free_products')) : "";
		$data = array(
			'product_type'			=> $product_type,
			'product_name'			=> $product_name,
			'product_shop_type'		=> post_value('shop_type'),
			'product_alias'			=> post_value('alias_name'),
			'product_sku'			=> post_value('sku'),
			'product_tag_info'		=> post_value('tag_info'),
			'product_stock' 		=> post_value('stock'),
			'product_stock_min' 	=> post_value('stock_min'),
			'product_stock_alert' 	=> (post_value('low_stock_alert') != "") ? post_value('low_stock_alert') : 0,
			'product_parent_primary_id'			=> (!empty($parantProduct)) ? $parantProduct[0] : "",
			'product_parent_id'		=> (!empty($parantProduct)) ? $parantProduct[1] : "",
			'product_category_id'	=> (!empty($category)) ? $category[0] : "",
			'product_subcategory_id' => (!empty($category)) ? $category[1] : "",
			'product_short_description'			=> post_value('short_description'),
			'product_long_description'			=> post_value('long_description'),
			'product_minimum_quantity'			=> post_value('min_quantity'),
			'product_maximum_quantity'			=> post_value('max_quantity'),
			'product_thumbnail'		=> post_value('thumbnail'),
			'product_gallery'		=> post_value('gallery'),
			'product_status'		=> post_value('status'),
			'product_cost'			=> post_value('cost'),
			'product_price'			=> post_value('price'),
			'product_special_price'	=> post_value('special_price'),
			'product_special_price_from_date'			=> post_value('special_price_from_date'),
			'product_special_price_to_date'			=> post_value('special_price_to_date'),
			'product_meta_title'	=> post_value('meta_title'),
			'product_meta_keywords'	=> post_value('meta_keywords'),
			'product_meta_description'			=> post_value('meta_description'),
			'product_lead_time'		=> post_value('lead_time'),
			'product_lead_time_minutes'			=> post_value('lead_time_minutes'),
			'product_leadtime_basedonqty'			=> post_value('leadtime_basedonqty'),
			'product_apply_minmax_select'			=> post_value('apply_minmax_select'),
			'product_pos_id'		=> post_value('pos_id'),
			'product_disply_source'	=> post_value('product_disply_source'),
			'product_paired_products'	=> post_value('paired_products'),

			'product_voucher' => ($product_type == "5") ? $voucher_type : '',
			'product_voucher_expiry_date' => ($product_type == "5" && post_value('voucher_expity_date') != "") ? date('Y-m-d', strtotime(post_value('voucher_expity_date'))) : '',
			'product_voucher_number_of_dates'			=> ($product_type == "5") ? post_value('voucher_number_date') : '',
			'product_voucher_reedmed_availability'			=> ($product_type == "5") ? post_value('reedmed_availability') : '',
			'product_voucher_food_option' => ($voucher_type == 'f') ? post_value('voucher_food_option') : '',
			'product_voucher_food_discount_type' => ($voucher_type == 'f' && post_value('voucher_food_option') == "2") ? post_value('voucher_food_discount_type') : '',
			'product_voucher_food_discount_val' => ($voucher_type == 'f' && post_value('voucher_food_option') == "2") ? post_value('food_voucher_disc_val') : '',
			'product_voucher_food_normal_product_id' => $voucher_free_products[1],
			'product_voucher_increase_qty'		=> ($product_type == "5") ? post_value('voucher_increase_qty') : '',
			'product_voucher_points'		=> ($product_type == "5") ? post_value('product_voucher_points') : '',
			'product_availability_errorinfo' => post_value('availability_errorinfo'),
			'product_recommendation' => post_value('recommendation'),
		);

		$company_id = decode_value($this->input->post('company_id'));
		$company_admin_id = decode_value($this->input->post('company_admin_id'));
		$getCompanyDetails = getCompanyUniqueID($company_id);

		$pro_sequence = ((int)$this->input->post('sequence') == 0) ?  get_sequence('product_sequence', $this->table, $company_array) : $this->input->post('sequence');

		if ($action == 'add') {
			$company_array = array($this->company_id => $company_id, 'product_company_unique_id' => $getCompanyDetails);
			$product_id = get_guid($this->table, 'product_id', $company_array);

			$slug = make_slug($product_name, $this->table, 'product_slug', array($this->company_id => $company_id));

			$data = array_merge(
				$data,
				array(
					'product_id'		=> $product_id,
					'product_slug'		=> $slug,
					'product_sequence' 	=> $pro_sequence,
					'product_company_id' => $company_id,
					'product_company_unique_id' 	=> (!empty($getCompanyDetails)) ? $getCompanyDetails : '',
					'product_created_on' => current_date(),
					'product_created_by' => $company_admin_id,
					'product_created_ip' => get_ip()
				)
			);

			$edit_id = $this->Mydb->insert($this->table, $data);
			createAuditLog("Product", stripslashes(post_value('product_name')), "Add", $company_admin_id, 'Web', '', $company_id, $getCompanyDetails);
			$this->product_stock_log($product_id, post_value('stock'), $company_id,  $getCompanyDetails, 'add');
		} else {
			$proDetail = $this->Mydb->get_record('product_id', $this->table, array($this->primary_key => $edit_id));

			$product_id = $proDetail['product_id'];

			$this->product_stock_log($product_id, post_value('stock'), $company_id,  $getCompanyDetails, '');
			$slug = make_slug($product_name, $this->table, 'product_id', array("$this->primary_key !=" => $edit_id, $this->company_id => $company_id));

			$data = array_merge(
				$data,
				array(
					'product_sequence '		=> post_value('sequence'),
					'product_slug'			=> $slug,
					'product_updated_on'	=> current_date(),
					'product_updated_by'	=> $company_admin_id,
					'product_updated_ip'	=> get_ip()
				)
			);
			if (post_value('stock_min') != "") {
				if (post_value('stock') > post_value('stock_min')) {
					$data = array_merge(
						$data,
						array('product_stock_alert_email_status' => '')
					);
				}
			}

			$this->Mydb->update($this->table, array($this->primary_key => $edit_id), $data);
			createAuditLog("Product", stripslashes(post_value('product_name')), "Update", $company_admin_id, 'Web', '', $company_id, $getCompanyDetails);
		}
		if (!empty($edit_id)) {
			if ($action == 'edit') {
				$this->Mydb->delete($this->product_availability, array('product_availability_product_primary_id' => $edit_id));
				$this->Mydb->delete($this->assigned_outlets, array('pao_product_primary_id' => $edit_id));
			}

			$assignOutlet = (post_value('assign_outlet') != "") ? explode(',', post_value('assign_outlet')) : array();
			if (!empty($assignOutlet)) {
				foreach ($assignOutlet as $val) {
					$outletArray = array(
						'pao_outlet_id' 		=> $val,
						'pao_product_id ' 		=> $product_id,
						'pao_product_primary_id ' => $edit_id,
						'pao_company_id' 		=> $company_id,
						'pao_company_app_id' 	=> $getCompanyDetails,
						'pao_updated_on' 		=> current_date(),
						'pao_updated_by' 		=> $company_admin_id,
						'pao_updated_ip' 		=> get_ip()
					);
					$this->Mydb->insert($this->assigned_outlets, $outletArray);
				}
			}

			$assignAvailability = (post_value('assign_availability') != "") ? explode(',', post_value('assign_availability')) : array();
			if (!empty($assignAvailability)) {
				foreach ($assignAvailability as $val) {
					$availArray = array(
						'product_availability_id' 			=> $val,
						'product_availability_product_id' => $product_id,
						'product_availability_product_primary_id' => $edit_id,
						'product_availability_company_id' 	=> $company_id,
						'product_availability_company_app_id' 	=> $getCompanyDetails,
						'product_availability_updated_on' 	=> current_date(),
						'product_availability_updated_by' 	=> $company_admin_id,
						'product_availability_updated_ip' 	=> get_ip()
					);
					$this->Mydb->insert($this->product_availability, $availArray);
				}
			}

			$assignTags = (post_value('assign_tags') != "") ? explode(',', post_value('assign_tags')) : array();
			if (!empty($assignTags)) {
				foreach ($assignTags as $val) {
					$tagsArray = array(
						'tag_id' 			=> $val,
						'tag_product_id' => $product_id,
						'tag_product_primary_id' => $edit_id,
						'tag_company_id' 	=> $company_id,
						'tag_company_app_id' 	=> $getCompanyDetails,
						'tag_updated_on' 	=> current_date(),
						'tag_updated_by' 	=> $company_admin_id,
						'tag_updated_ip' 	=> get_ip()
					);
					$this->Mydb->insert($this->product_assigned_tags, $tagsArray);
				}
			}


			$comobset = ($this->post('comobset') != "" ? json_decode($this->post('comobset')) : array());
			$comobset = (!empty($comobset)) ? $this->object_to_array($comobset) : array();
			$this->insert_combo_products($action, $comobset, $edit_id, $product_id);

			$dayavailability = ($this->post('dayavailability') != "" ? json_decode($this->post('dayavailability')) : array());
			$dayavailability = (!empty($dayavailability)) ? $this->object_to_array($dayavailability) : array();
			$this->insert_product_timeavailability($action, $edit_id, $product_id, $getCompanyDetails, $dayavailability);
			$product_ids = post_value('voucher_included_products');
			$this->insert_food_vou_disc_prod($edit_id, $product_ids, $action);
		}
	}

	public function importsimple_post()
	{

		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$this->form_validation->set_rules('import_file', 'lang:import_file', 'callback_validate_file');
				if ($this->form_validation->run() == TRUE) {

					$company_id = decode_value($this->input->post('company_id'));
					$company_admin_id = decode_value($this->input->post('company_admin_id'));
					$type = $this->input->post('type');
					$getCompanyDetails = getCompanyUniqueID($company_id);

					if (pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION) == 'csv') {
						if ($_FILES['import_file']['name'] != '') {
							$handle = fopen($_FILES["import_file"]["tmp_name"], "r");
							$i = 0;
							$totalImport = 0;
							while (($line_of_text = fgetcsv($handle, 2000, ",")) !== FALSE) {
								if ($i > 0) {
									if (!empty($line_of_text[0]) && !empty($line_of_text[4]) && !empty($line_of_text[6]) && !empty($line_of_text[2])) {
										$checkingPro = $this->Mydb->get_record('product_primary_id', $this->table, array('product_name' => $line_of_text[0], 'product_company_id' => $company_id));
										if (empty($checkingPro)) {
											$importPro = $this->importProduct($line_of_text, $company_id, $company_admin_id, $getCompanyDetails, $type);
											if (!empty($importPro)) {
												$totalImport++;
											}
										}
									}
								}
								$i++;
							}
						}
					}
					if ($totalImport > 0) {
						$this->set_response(array(
							'status' => 'success',
							'message' => sprintf(get_label('success_message_import'), $this->label),
							'form_error' => '',
						), success_response()); /* success message */
					} else {
						$this->set_response(array(
							'status' => 'error',
							'message' => get_label('fail_import'),
							'form_error' => ''
						), something_wrong());
					}
				} else {
					$this->set_response(array(
						'status' => 'error',
						'message' => get_label('rest_form_error'),
						'form_error' => validation_errors()
					), something_wrong());
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

	private function importProduct($ProData, $company_id, $company_admin_id, $getCompanyDetails, $type)
	{
		$company_array = array($this->company_id => $company_id, 'product_company_unique_id' => $getCompanyDetails);
		$product_id = get_guid($this->table, 'product_id', $company_array);
		$product_name = $ProData[0];
		$slug = make_slug($product_name, $this->table, 'product_slug', array($this->company_id => $company_id));
		$pro_sequence = get_sequence('product_sequence', $this->table, $company_array);
		$availability = (!empty($ProData[9])) ? explode(',', $ProData[9]) : '';
		$availabilityID = array();
		if (!empty($availability)) {
			foreach ($availability as $val) {
				$avail = $this->Mydb->get_record('av_id', 'availability', array('av_name' => $val));
				if (!empty($avail)) {
					$availabilityID[] = $avail['av_id'];
				}
			}
		}
		$outletID = (!empty($ProData[16])) ? explode(',', $ProData[16]) : '';

		$category = (!empty($ProData[2])) ? $ProData[2] : '';
		$subcategory = (!empty($ProData[3])) ? $ProData[3] : '';
		$catAndSubcat = $this->createCategory($category, $subcategory, $availabilityID, $outletID, $company_id, $company_admin_id, $getCompanyDetails);
		$catId = $subcatId = "";
		if (!empty($catAndSubcat)) {
			$catId = (!empty($catAndSubcat['pro_cate_id'])) ? $catAndSubcat['pro_cate_id'] : '';
			$subcatId = (!empty($catAndSubcat['pro_subcate_id'])) ? $catAndSubcat['pro_subcate_id'] : '';
		}

		$data = array(
			'product_type'			=> ($type == 'Combo Product') ? 2 : 1,
			'product_name'			=> $product_name,
			'product_shop_type'		=> 1,
			'product_alias'			=> (!empty($ProData[1])) ? $ProData[1] : '',
			'product_sku'			=> (!empty($ProData[4])) ? $ProData[4] : '',
			'product_stock' 		=> (!empty($ProData[18])) ? $ProData[18] : '',
			'product_category_id'	=> $catId,
			'product_subcategory_id' => $subcatId,
			'product_short_description'	=> (!empty($ProData[7])) ? $ProData[7] : '',
			'product_long_description'	=> (!empty($ProData[8])) ? $ProData[8] : '',
			'product_thumbnail'		=> (!empty($ProData[11])) ? $ProData[11] : '',
			'product_gallery'		=> (!empty($ProData[12])) ? $ProData[12] : '',
			'product_status'		=> 'A',
			'product_cost'			=> (!empty($ProData[5])) ? $ProData[5] : '',
			'product_price'			=> (!empty($ProData[6])) ? $ProData[6] : '',
			'product_meta_title'	=> (!empty($ProData[13])) ? $ProData[13] : '',
			'product_meta_keywords'	=> (!empty($ProData[14])) ? $ProData[14] : '',
			'product_meta_description' => (!empty($ProData[15])) ? $ProData[15] : '',
			'product_pos_id'		 => (!empty($ProData[17])) ? $ProData[17] : '',
			'product_id'		=> $product_id,
			'product_slug'		=> $slug,
			'product_sequence' 	=> $pro_sequence,
			'product_company_id' => $company_id,
			'product_company_unique_id' 	=> (!empty($getCompanyDetails)) ? $getCompanyDetails : '',
			'product_created_on' => current_date(),
			'product_created_by' => $company_admin_id,
			'product_created_ip' => get_ip()
		);
		$product_primary_id = $this->Mydb->insert($this->table, $data);
		if (!empty($availabilityID)) {
			foreach ($availabilityID as $val) {
				$availArray = array(
					'product_availability_id' 			=> $val,
					'product_availability_product_id' => $product_id,
					'product_availability_product_primary_id' => $product_primary_id,
					'product_availability_company_id' 	=> $company_id,
					'product_availability_company_app_id' 	=> $getCompanyDetails,
					'product_availability_updated_on' 	=> current_date(),
					'product_availability_updated_by' 	=> $company_admin_id,
					'product_availability_updated_ip' 	=> get_ip()
				);
				$this->Mydb->insert($this->product_availability, $availArray);
			}
		}

		if (!empty($outletID)) {
			foreach ($outletID as $val) {
				$outletArray = array(
					'pao_outlet_id' 		=> $val,
					'pao_product_id ' 		=> $product_id,
					'pao_product_primary_id ' => $product_primary_id,
					'pao_company_id' 		=> $company_id,
					'pao_company_app_id' 	=> $getCompanyDetails,
					'pao_updated_on' 		=> current_date(),
					'pao_updated_by' 		=> $company_admin_id,
					'pao_updated_ip' 		=> get_ip()
				);
				$this->Mydb->insert($this->assigned_outlets, $outletArray);
			}
		}
		if ($type == 'Combo Product') {
			$comobset = array();
			$comboTitle =  (!empty($ProData[19])) ? explode('~', $ProData[19]) : '';
			if (!empty($comboTitle)) {
				$comboPro =  (!empty($ProData[20])) ? explode('~', $ProData[20]) : '';
				$combodefaultPro =  (!empty($ProData[21])) ? explode('~', $ProData[21]) : '';
				$comboGroup =  (!empty($ProData[22])) ? explode('~', $ProData[22]) : '';
				$comboMin =  (!empty($ProData[23])) ? explode('~', $ProData[23]) : '';
				$combodMax =  (!empty($ProData[24])) ? explode('~', $ProData[24]) : '';
				$comboApplyPrice =  (!empty($ProData[25])) ? explode('~', $ProData[25]) : '';
				$comboApplyModifier =  (!empty($ProData[26])) ? explode('~', $ProData[26]) : '';
				$comboMultipleSelections =  (!empty($ProData[27])) ? explode('~', $ProData[27]) : '';
				foreach ($comboTitle as $key => $val) {
					$comobset[$key]['combo_name'] = $val;
					$splitcomboPro = (!empty($comboPro[$key])) ? explode('|', $comboPro[$key]) : array();
					$comobProList = array();
					if (!empty($splitcomboPro)) {
						foreach ($splitcomboPro as $proKey => $pro) {
							$proDetails = $this->Mydb->get_record('product_primary_id, product_id', $this->table, array($this->company_id => $company_id, 'product_name' => $pro));
							if (!empty($proDetails)) {
								$comobProList[$proKey]['value'] = $proDetails['product_primary_id'] . '_' . $proDetails['product_id'];
							}
						}
					}
					$defaultPro = array();
					if (!empty($combodefaultPro[$key])) {
						$proDetails = $this->Mydb->get_record('product_primary_id, product_id', $this->table, array($this->company_id => $company_id, 'product_name' => $combodefaultPro[$key]));
						if (!empty($proDetails)) {
							$defaultPro['value'] = $proDetails['product_primary_id'] . '_' . $proDetails['product_id'];
						}
					}
					$comobGroupList = array();
					if (!empty($comboGroup[$key])) {
						$splitcomboGroup = explode('|', $comboGroup[$key]);
						foreach ($splitcomboGroup as $proKey => $pro) {
							$groupDetails = $this->Mydb->get_record('pro_group_primary_id', 'product_groups', array('pro_group_company_id' => $company_id, 'pro_group_name' => $pro));
							if (!empty($groupDetails)) {
								$comobGroupList[$proKey]['value'] = $groupDetails['pro_group_primary_id'];
							}
						}
					}


					$comobset[$key]['products'] = $comobProList;
					$comobset[$key]['defaultproduct'] = $defaultPro;
					$comobset[$key]['combogroup'] = $comobGroupList;
					$comobset[$key]['min_select'] = (!empty($comboMin[$key])) ? $comboMin[$key] : '';
					$comobset[$key]['max_select'] = (!empty($combodMax[$key])) ? $combodMax[$key] : '';
					$comobset[$key]['sequence'] = $key + 1;
					$comobset[$key]['piecescount'] = '';
					$comobset[$key]['apply_price'] = (!empty($comboApplyPrice[$key])) ? (($comboApplyPrice[$key] == "Yes") ? 1 : 0) : '';
					$comobset[$key]['apply_modifier'] = (!empty($comboApplyModifier[$key])) ? (($comboApplyModifier[$key] == "Yes") ? 1 : 0) : '';
					$comobset[$key]['multipleselection_apply'] = (!empty($comboMultipleSelections[$key])) ? (($comboMultipleSelections[$key] == "Yes") ? 1 : 0) : '';
				}
				if (!empty($comobset)) {
					$this->insert_combo_products('add', $comobset, $product_primary_id, $product_id);
				}
			}
		}

		return $product_primary_id;
	}

	private function createCategory($category, $subcategory, $availabilityID, $outletID, $company_id, $company_admin_id, $getCompanyDetails)
	{
		$checkcategory = $this->Mydb->get_record('pro_cate_primary_id, pro_cate_id', $this->category, array('pro_cate_name' => $category, 'pro_cate_company_id' => $company_id, 'pro_cate_unqiue_id' => $getCompanyDetails));
		$categoryPrimaryId = $pro_cate_id = $subcategoryPrimaryId = $pro_subcate_id = "";
		if (empty($checkcategory)) {
			$company_array = array('pro_cate_company_id' => $company_id, 'pro_cate_unqiue_id' => $getCompanyDetails);
			$pro_cate_id = get_guid($this->category, 'pro_cate_id', $company_array);

			$slug = make_slug($cate_name, $this->category, 'pro_cate_slug', array('pro_cate_company_id' => $company_id));
			$cat_sequence = get_sequence('pro_cate_sequence', $this->category, $company_array);
			$data = array(
				'pro_cate_name'		=> $category,
				'pro_cate_sequence'	=> $cat_sequence,
				'pro_cate_status'	=> 'A',
				'pro_cate_id'		=> $pro_cate_id,
				'pro_cate_slug'		=> $slug,
				'pro_cate_company_id'	=> $company_id,
				'pro_cate_unqiue_id' 	=> (!empty($getCompanyDetails)) ? $getCompanyDetails : '',
				'pro_cate_created_on' => current_date(),
				'pro_cate_created_by' => $company_admin_id,
				'pro_cate_created_ip' => get_ip()
			);
			$categoryPrimaryId = $this->Mydb->insert($this->category, $data);
			$pro_cate_id = $pro_cate_id;

			if (!empty($availabilityID)) {
				foreach ($availabilityID as $val) {
					$availArray = array(
						'cate_availability_id' 			=> $val,
						'cate_availability_category_id' => $pro_cate_id,
						'cate_availability_category_primary_id' => $categoryPrimaryId,
						'cate_availability_company_id' 	=> $company_id,
						'cate_availability_company_app_id' 	=> $getCompanyDetails,
						'cate_availability_type'		=> 'Category',
						'cate_availability_updated_on' 	=> current_date(),
						'cate_availability_updated_by' 	=> $company_admin_id,
						'cate_availability_updated_ip' 	=> get_ip()
					);
					$this->Mydb->insert('product_category_availability', $availArray);
				}
			}

			if (!empty($outletID)) {
				foreach ($outletID as $val) {
					$outletArray = array(
						'pao_outlet_id' 		=> $val,
						'pao_category_id' 		=> $pro_cate_id,
						'pao_category_primary_id' => $categoryPrimaryId,
						'pao_company_id' 		=> $company_id,
						'pao_company_app_id' 	=> $getCompanyDetails,
						'pao_updated_on' 		=> current_date(),
						'pao_updated_by' 		=> $company_admin_id,
						'pao_updated_ip' 		=> get_ip()
					);
					$this->Mydb->insert('category_assigned_outlets', $outletArray);
				}
			}
		} else {
			$categoryPrimaryId = $checkcategory['pro_cate_primary_id'];
			$pro_cate_id = $checkcategory['pro_cate_id'];
		}
		if (!empty($categoryPrimaryId)) {
			$subcategory = $this->createSubCategory($categoryPrimaryId, $pro_cate_id, $subcategory, $availabilityID, $outletID, $company_id, $company_admin_id, $getCompanyDetails);
			if (!empty($subcategory)) {
				$subcategoryPrimaryId = $subcategory['pro_subcate_primary_id'];
				$pro_subcate_id = $subcategory['pro_subcate_id'];
			}
		}

		return array('categoryPrimaryId' => $categoryPrimaryId, 'pro_cate_id' => $pro_cate_id, 'subcategoryPrimaryId' => $subcategoryPrimaryId,  'pro_subcate_id' => $pro_subcate_id);
	}

	private function createSubCategory($categoryPrimaryId, $pro_cate_id, $subcategory, $availabilityID, $outletID, $company_id, $company_admin_id, $getCompanyDetails)
	{
		$subcategoryPrimaryId = $pro_subcate_id = '';
		$checkSubCat = $this->Mydb->get_record('pro_subcate_primary_id, pro_subcate_id', $this->subcategory, array('pro_subcate_name' => $subcategory, 'pro_subcate_company_id' => $company_id));
		if (empty($checkSubCat)) {

			$company_array = array('pro_subcate_company_id' => $company_id, 'pro_subcate_unqiue_id' => $getCompanyDetails);
			$pro_subcate_id = get_guid($this->subcategory, 'pro_subcate_id', $company_array);

			$slug = make_slug($subcategory, $this->subcategory, 'pro_subcate_slug', array('pro_subcate_company_id' => $company_id));
			$company_array['pro_subcate_category_primary_id'] = (!empty($category)) ? $category[0] : '';
			$pro_subcate_sequence = get_sequence('pro_subcate_sequence', $this->subcategory, $company_array);

			$data = array(
				'pro_subcate_category_primary_id' => $categoryPrimaryId,
				'pro_subcate_category_id'		=> $pro_cate_id,
				'pro_subcate_name'				=> $subcategory,
				'pro_subcate_status'			=> 'A',
				'pro_subcate_sequence '	=> $pro_subcate_sequence,
				'pro_subcate_id'		=> $pro_subcate_id,
				'pro_subcate_slug'		=> $slug,
				'pro_subcate_company_id' => $company_id,
				'pro_subcate_unqiue_id' => (!empty($getCompanyDetails)) ? $getCompanyDetails : '',
				'pro_subcate_created_on' => current_date(),
				'pro_subcate_created_by' => $company_admin_id,
				'pro_subcate_created_ip' => get_ip()
			);
			$subcategoryPrimaryId = $this->Mydb->insert($this->subcategory, $data);

			if (!empty($availabilityID)) {
				foreach ($availabilityID as $val) {
					$availArray = array(
						'cate_availability_id' 			=> $val,
						'cate_availability_category_id' => $pro_subcate_id,
						'cate_availability_category_primary_id' => $subcategoryPrimaryId,
						'cate_availability_company_id' 	=> $company_id,
						'cate_availability_company_app_id' 	=> $getCompanyDetails,
						'cate_availability_type'		=> 'Subcategory',
						'cate_availability_updated_on' 	=> current_date(),
						'cate_availability_updated_by' 	=> $company_admin_id,
						'cate_availability_updated_ip' 	=> get_ip()
					);
					$this->Mydb->insert('product_category_availability', $availArray);
				}
			}

			if (!empty($outletID)) {
				foreach ($outletID as $val) {
					$outletArray = array(
						'pao_outlet_id' 		=> $val,
						'pao_sub_category_id' 	=> $pro_subcate_id,
						'pao_sub_category_primary_id' => $subcategoryPrimaryId,
						'pao_company_id' 		=> $company_id,
						'pao_company_app_id' 	=> $getCompanyDetails,
						'pao_updated_on' 		=> current_date(),
						'pao_updated_by' 		=> $company_admin_id,
						'pao_updated_ip' 		=> get_ip()
					);
					$this->Mydb->insert('sub_category_assigned_outlets', $outletArray);
				}
			}
		} else {
			$subcategoryPrimaryId = $checkSubCat['pro_subcate_primary_id'];
			$pro_subcate_id = $checkSubCat['pro_subcate_id'];
		}
		return array('subcategoryPrimaryId' => $subcategoryPrimaryId,  'pro_subcate_id' => $pro_subcate_id);
	}

	/* insert product time avaiablity */
	private function insert_product_timeavailability($action, $primary_id, $product_id, $getCompanyDetails, $dayavailability)
	{

		if ($action == "edit") {
			$this->Mydb->delete($this->time_availability, array(
				'avbl_company_app_id' => $getCompanyDetails,
				'avbl_product_primary_id' => $primary_id,
				'avbl_product_id' => $product_id
			));
		}

		if (!empty($dayavailability)) {
			foreach ($dayavailability as $value) {
				if (!empty($value['data'])) {
					foreach ($value['data'] as $dataval) {
						foreach ($dataval as $key => $timeVal) {
							if (!empty($timeVal)) {
								foreach ($timeVal as $timeSlotVal) {
									$avail = 0;
									$crArr = array(
										'avbl_company_app_id' => $getCompanyDetails,
										'avbl_product_primary_id' => $primary_id,
										'avbl_product_id' => $product_id,
										'avbl_based_on' => $value['type'],
										'avbl_stock' => ($timeSlotVal['stock']) ? $timeSlotVal['stock'] : "",
										'avbl_stock_validate' => ($timeSlotVal['validate']) ? "Yes" : "No",
										'avbl_created_on' => current_date(),
									);

									if ($key == 'daybased') {
										$avlDays = $timeSlotVal['day'];
										$crtStrTm = (!empty($timeSlotVal['from'])) ? date('H:i:s', strtotime($timeSlotVal['from'])) : '';
										$crtEndTm = (!empty($timeSlotVal['to'])) ? date('H:i:s', strtotime($timeSlotVal['to'])) : '';
										if (!empty($crtStrTm) && !empty($crtEndTm)) {
											$avail++;
										}
										$crArr = array_merge($crArr, array(
											'avbl_time_type' => 'day',
											'avbl_days' => $avlDays,
											'avbl_str_time' => $crtStrTm,
											'avbl_end_time' => $crtEndTm,
										));
									} else if ($key == 'datebased') {
										$crtStrDateTm = (!empty($timeSlotVal['from'])) ? date('Y-m-d H:i:s', strtotime($timeSlotVal['from'])) : '';
										$crtEndDateTm = (!empty($timeSlotVal['to'])) ? date('Y-m-d H:i:s', strtotime($timeSlotVal['to'])) : '';
										if (!empty($crtStrDateTm) && !empty($crtEndDateTm)) {
											$avail++;
										}
										$crArr = array_merge($crArr, array(
											'avbl_time_type' => 'date',
											'avbl_str_datetime' => $crtStrDateTm,
											'avbl_end_datetime' => $crtEndDateTm
										));
									}
									if ($avail > 0) {
										$this->Mydb->insert($this->time_availability, $crArr);
									}
								}
							}
						}
					}
				}
			}
		}
	}

	private function insert_combo_products($action, $comobset, $primary_id, $product_id)
	{

		/* remove old entry's */
		if ($action == "edit") {
			$all_combos = $this->Mydb->get_all_records('combo_id', $this->product_combos, array('combo_product_primary_id' => $primary_id));
			if (!empty($all_combos)) {
				$combo_in = array_column($all_combos, 'combo_id');
				$this->Mydb->delete_where_in('combo_products', 'pro_combo_id', $combo_in, array(
					'pro_combo_id !=' => "",
				));
				$this->Mydb->delete_where_in($this->product_combos, 'combo_id', $combo_in, array(
					'combo_id !=' => "",
				));
			}
		}
		if (!empty($comobset)) {
			foreach ($comobset as $val) {
				$combo_id = $this->Mydb->insert(
					$this->product_combos,
					array(
						'combo_name' 				=> (!empty($val['combo_name'])) ? $val['combo_name'] : '',
						'combo_qty' 				=> '1',
						'combo_is_saving' 			=> 'No',
						'combo_product_primary_id' 	=> $primary_id,
						'combo_product_id' 			=> $product_id,
						'combo_max_select'			=> (!empty($val['max_select'])) ? $val['max_select'] : "",
						'combo_min_select' 			=> (!empty($val['min_select'])) ? $val['min_select'] : "",
						'combo_sort_order' 			=> (!empty($val['sequence'])) ? $val['sequence'] : "",
						'combo_price_apply' 		=> (!empty($val['apply_price']) && $val['apply_price'] == "1") ? 1 : 0,
						'combo_modifier_apply' 		=> (!empty($val['apply_modifier']) && $val['apply_modifier'] == "1") ? 1 : 0,
						'combo_multipleselection_apply' => (!empty($val['multipleselection_apply']) && $val['multipleselection_apply'] == "1") ? 1 : 0,
						'combo_disable_validation' 	=> (!empty($val['disable_validation']) && $val['disable_validation'] == "1") ? 1 : 0,
						'combo_pieces_count' 		=> (!empty($val['piecescount'])) ? $val['piecescount'] : "",
						'single_selection'			=> (!empty($val['single_selection']) && $val['single_selection'] == "1") ? 1 : 0
					)
				);
				$pro_arr = (!empty($val['products'])) ? $val['products'] : array();
				/* insert combo products */
				if (!empty($combo_id) && !empty($pro_arr)) {
					$default_product_id  = (!empty(($val['defaultproduct']))) ? $val['defaultproduct']['value'] : "";
					foreach ($pro_arr as $pro) {
						$comboPorduct  = (!empty($pro)) ? explode('_', $pro['value']) : array();
						$is_default = (!empty($comboPorduct) && $pro['value'] == $default_product_id) ? "Yes" : "No";
						$this->Mydb->insert('combo_products', array(
							'pro_combo_id' 		=> $combo_id,
							'pro_product_id' 	=> (!empty($comboPorduct)) ? $comboPorduct[1] : '',
							'pro_is_default' 	=> $is_default
						));
					}
				}
				$grp_arr = (!empty($val['combogroup'])) ? $val['combogroup'] : array();
				/* insert combo groups */
				if (!empty($combo_id) && !empty($grp_arr)) {
					foreach ($grp_arr as $grp) {
						$this->Mydb->insert('combo_products', array(
							'pro_combo_id' => $combo_id,
							'pro_group_id' => $grp['value']
						));
					}
				}
			}
		}
		/* update product tablel.... */
		$status = (!empty($combo_id)) ? 'Yes' : 'No';
		$this->Mydb->update('products', array('product_id' => $product_id), array('product_is_combo' => $status));
	}

	private function insert_food_vou_disc_prod($insert_id, $product_ids, $action)
	{
		/* if update delete old records.. */
		if ($action == "edit") {
			$this->Mydb->delete('product_voucher_food_discount', array(
				'food_discount_food_product_id' => $insert_id
			));
		}
		if (!empty($product_ids)) {
			$productids =  explode(',', $product_ids);
			$products  = $this->Mydb->get_all_records_where_in('product_category_id,product_subcategory_id,product_id', 'products', 'product_id', $productids, array(
				'product_sequence' => "ASC"
			));
			$food_discount_food_product_id = $insert_id;
			if (!empty($products)) {
				foreach ($products as $key) {
					$food_vou_disc_details = array(
						'food_discount_food_product_id' => $food_discount_food_product_id,
						'food_discount_category_id' => $key['product_category_id'],
						'food_discount_subcategory_id' => $key['product_subcategory_id'],
						'food_discount_product_id' => $productids[1]
					);
					/* To insert product bulk discount - End */
					$this->Mydb->insert('product_voucher_food_discount', $food_vou_disc_details);
				}
			}
		}
	}

	private function product_stock_log($id = null, $stock = null, $company_id,  $getCompanyDetails, $method = '',)
	{
		$type = 'P';
		if (!empty($id)) {
			$record = $this->Mydb->get_record('product_stock', $this->table, array(
				'product_primary_id' => $id,
				'product_company_id' => $company_id,
				'product_company_unique_id' => $getCompanyDetails
			));

			if ($record['product_stock'] > $stock) {
				$stock_val = $record['product_stock'] - $stock;
				$type = 'S';
			} else if ($record['product_stock'] < $stock) {
				$stock_val = $stock - $record['product_stock'];
				$type = 'P';
			} else {
				$stock_val = $stock;
			}
			if ($record['product_stock'] != $stock || $method == 'add') {
				$this->Mydb->insert('product_stock_log', array(
					'product_stock_company_id' => $company_id,
					'product_stock_company_unquie_id' => $getCompanyDetails,
					'product_stock_product_id' => $id,
					'product_stock_type' => $type,
					'product_stock_value' => $stock_val,
					'product_stock_created' => current_date(),
					'product_stock_created_ip' => get_ip(),
				));
			}
		}
	}

	/* this method used check product name or alredy exists or not */
	public function productname_exists()
	{
		$name = $this->input->post('product_name');
		$edit_id = $this->input->post('edit_id');
		$category = explode('_', post_value('category'));
		$company_id = decode_value($this->input->post('company_id'));
		$where = array(
			'product_name' => trim($name)
		);
		if (!empty($edit_id)) {
			$where = array_merge($where, array(
				$this->primary_key . " !=" => decode_value($edit_id)
			));
		}

		$where = array_merge(array(
			'product_category_id' => $category[0],
			'product_subcategory_id' => $category[1],
			$this->company_id => $company_id,
		), $where);
		$result = $this->Mydb->get_record($this->primary_key, $this->table, $where);
		if (!empty($result)) {
			$this->form_validation->set_message('productname_exists', sprintf(get_label('alredy_exist'), get_label('rest_product_name')));
			return false;
		} else {
			return true;
		}
	}
	/* this method used check product sku or alredy exists or not */
	private function validate_sku()
	{
		$sku = $this->input->post('product_sku');
		$edit_id = $this->input->post('edit_id');
		$company_id = decode_value($this->input->post('company_id'));
		$where = array(
			'product_sku' => trim($sku)
		);
		if ($edit_id != "") {
			$where = array_merge($where, array(
				$this->primary_key . " !=" => $edit_id
			));
		}

		$where = array_merge(array(
			$this->company_id => $company_id,
		), $where);
		$result = $this->Mydb->get_record($this->primary_key, $this->table, $where);

		if (!empty($result)) {
			$this->form_validation->set_message('validate_sku', get_label('product_sku_exist'));
			return false;
		} else {
			return true;
		}
	}

	/* this method used to validate posted category and subcategory */
	private function validate_subcategory()
	{
		$category = explode('~', post_value('subcategory'));
		$company_id = decode_value($this->input->post('company_id'));
		if (count($category) == 2) {
			$category_val = $this->Mydb->get_record('pro_subcate_id', $this->subcategory, array(
				$this->primary_key => $company_id,
				'pro_subcate_category_id' => $category[0],
				'pro_subcate_id' => $category[1]
			));

			if (empty($category_val)) {
				$this->form_validation->set_message('validate_subcategory', get_label('category_invalid'));
				return false;
			} else {
				return true;
			}
		} else {
			return false;
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

	public function action_post()
	{
		$ids = ($this->input->post('multiaction') == 'Yes' ? $this->input->post('id') : decode_value($this->input->post('changeId')));
		$postaction = $this->input->post('postaction');
		$company_id = decode_value($this->input->post('company_id'));
		$company_admin_id = decode_value($this->input->post('company_admin_id'));
		$get_company_details = $this->Mydb->get_record('company_unquie_id', 'company', array('company_id' => $company_id));
		$company_app_id =  $get_company_details['company_unquie_id'];
		$response = array(
			'status' => 'error',
			'msg' => get_label('something_wrong'),
			'action' => '',
			'form_error' => '',
			'multiaction' => $this->input->post('multiaction')
		);

		/* Delete */
		$wherearray = array('email_company_id' => $company_id, 'email_unquie_id' => $company_app_id);
		if ($postaction == 'Delete' && !empty($ids)) {
			$this->audit_action($ids, $postaction);
			// $this->Mydb->delete_where_in($this->table,'client_id',$ids,'');
			if (is_array($ids)) {
				$this->Mydb->delete_where_in($this->table, $this->primary_key, $ids, $wherearray);
				$response['msg'] = sprintf(get_label('success_message_delete'), $this->module_label);
			} else {
				$this->Mydb->delete($this->table, array($this->primary_key => $ids, 'email_company_id' => $company_id, 'email_unquie_id' => $company_app_id));
				$response['msg'] = sprintf(get_label('success_message_delete'), $this->module_label);
			}
			$response['status'] = 'success';
			$response['action'] = $postaction;
		}


		$this->set_response($response, success_response()); /* success message */
	}

	public function validate_file()
	{
		if (isset($_FILES['import_file']['name']) && $_FILES['import_file']['name'] != "") {
			if ($this->common->valid_file($_FILES['import_file']) == "No") {
				$this->form_validation->set_message('validate_file', get_label('upload_valid_csv_file'));
				return false;
			}
		}

		return true;
	}
} /* end of files */
