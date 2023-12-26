<?php
/**************************
Project Name	: POS
Created on		: 04  Nov, 2016
Last Modified 	: 04  Nov, 2016
Description		:  this file contains business panel api controller..
***************************/

if (! function_exists ( 'bp_outlet_delivery_timing' )) {
	function bp_outlet_delivery_timing($outlet_id) {
		$CI = & get_instance ();
		$records = $CI->Mydb->get_record ( 'outlet_delivery_timing', 'pos_outlet_management', array (
				'outlet_id' => $outlet_id 
		) );
		if (! empty ( $records )) {
			return $records ['outlet_delivery_timing'];
		} else {
			return '';
		}
	}
}

/* this function used to validate outlet */
if (! function_exists ( 'push_order_notify' )) {

	function push_order_notify($app_id,$company_id,$outlet_id,$order_primary_id) {

		$CI = & get_instance ();

		/*Outlet info*/
		$records = $CI->Mydb->get_record ( 'outlet_id,outlet_name', 'outlet_management', array (
				'outlet_app_id' => $app_id,
				'outlet_company_id' => $company_id,
				'outlet_id' => $outlet_id 
		) );
		$outlet_name = $records['outlet_name'];

		/*Outlet info*/
		$records = $CI->Mydb->get_record ( 'order_local_no,order_date', 'orders', array ('order_primary_id' => $order_primary_id) );
		$order_local_no = $records['order_local_no'];
		$order_date = $records['order_date'];

		$advance_order_flg = 'No';
		$current_order_flg = 'No';

		if(strtotime($order_date) == strtotime(date('d-m-Y'))) {
			$current_order_flg = 'Yes';
		} else {
			$advance_order_flg = 'Yes';
		}

		/*Get device token*/
		$outlet_res = $CI->Mydb->get_all_records ( '*', 'pos_push_management', array ('push_outlet_id' => $outlet_id));
		foreach ( $outlet_res as $outlet_arr )
		{
			$ios_device_token = $outlet_arr['push_iosdevice_token'];
		    $android_device_token = $outlet_arr['push_androiddevice_token'];

		    if($ios_device_token != '' && $ios_device_token != '(null)') {

				$CI->load->library ( 'push' );

				$countPush = 0;

				$data = array(); //- Outlet('.$outlet_name.')
				$data['aps']['alert'] = 'You have new order('.$order_local_no.')';
				$data['aps']['sound'] = 'AlarmRing.mp3';
				$data['aps']['current_order'] = $current_order_flg;
				$data['aps']['advance_order'] = $advance_order_flg;

				$status = $CI->push->push_message_ios ( $ios_device_token, $data,$countPush );
				$countPush++;
				
				echo '<br/>';

			}

		}

		return true;
	}
}

/* this function used to validate outlet */
if (! function_exists ( 'validate_outlet' )) {
	function validate_outlet($app_id,$company_id,$outlet_id = null) {
		$CI = & get_instance ();
		
		$records = $CI->Mydb->get_record ( 'outlet_id,outlet_availability,outlet_delivery_timing', 'outlet_management', array (
				'outlet_unquie_id' => $app_id,
				'outlet_company_id' => $company_id,
				'outlet_id' => $outlet_id 
		) );
		
		return $records;
	}
}

if (! function_exists ( 'bp_order_details' )) {

	function bp_order_details($app_id,$company_id,$order_id,$order_type=1, $outlet_id=null) {

		$CI = & get_instance ();

		$outletData = $CI->Mydb->get_all_records('outlet_id, outlet_name', 'outlet_management', array('outlet_app_id'=> $app_id));
		$outlet_Data = array();
		if(!empty($outletData)) {
			$ooutlet_id = array_column($outletData, 'outlet_id');
			$outlet_Data = array_combine($ooutlet_id, $outletData);
		}

		$site_location = $CI->Mydb->get_all_records('sl_location_id,sl_name,  sl_pickup_postal_code, sl_pickup_postal_code, sl_pickup_unit_number1, sl_pickup_unit_number2, sl_pickup_address_line1, sl_pickup_address_line2', 'site_location', array('sl_app_id'=> $app_id));
		$sitelocation = array();
		if(!empty($site_location)) {
			$sl_location_id = array_column($site_location, 'sl_location_id');
			$sitelocation  = array_combine($sl_location_id, $site_location);

		}

		$where = array ('order_company_app_id' => $app_id, 'order_company_id' => $company_id, 'order_id' => $order_id );

		$join [0] ['select'] = "order_customer_id,CONCAT_WS(' ',order_customer_fname,order_customer_lname) AS customer_name,order_customer_email,order_customer_mobile_no,		order_customer_unit_no1,order_customer_unit_no2,order_customer_address_line1,order_customer_address_line2,order_customer_city,
		order_customer_state,order_customer_country,order_customer_postal_code,order_customer_send_gift, order_recipient_name,order_recipient_contact_no,order_gift_message,order_customer_created_on";
		$join [0] ['table'] = "pos_orders_customer_details";
		$join [0] ['condition'] = "order_customer_order_primary_id = order_primary_id";
		$join [0] ['type'] = "LEFT";

		$join [1] ['select'] = "customer_unique_id,(CASE 
		WHEN customer_membership_type='Kakis' THEN 'Kakis'		
        WHEN customer_membership_type='Normal'  THEN ''
        ELSE customer_membership_type
    END) AS membership_type";
		$join [1] ['table'] = "customers";
		$join [1] ['condition'] = "order_customer_id = customer_id  ";
		$join [1] ['type'] = "LEFT";

		/* join tables - Status table */
		$join [2] ['select'] = "status_name";
		$join [2] ['table'] = "pos_order_status";
		$join [2] ['condition'] = "order_status = status_id AND status_enabled='A' ";
		$join [2] ['type'] = "LEFT";

		/* join tables - admin user table */
		$join [3] ['select'] = "CONCAT(user_fname, ' ', user_lname) AS order_agent";
		$join [3] ['table'] = "pos_company_users";
		$join [3] ['condition'] = "user_id = order_callcenter_admin_id";
		$join [3] ['type'] = "LEFT";

		/* join tables - Status table */
		$join [4] ['select'] = "order_method_name";
		$join [4] ['table'] = "pos_order_methods";
		$join [4] ['condition'] = "order_payment_mode = order_method_id  ";
		$join [4] ['type'] = "LEFT";

		$join [5] ['select'] = "assigned_order_rider_id";
		$join [5] ['table'] = "pos_rider_assigned_orders";
		$join [5] ['condition'] = "order_id = assigned_order_order_id";
		$join [5] ['type'] = "LEFT";

		$join [6] ['select'] = "rider_fname,rider_lname,rider_mobile_no";
		$join [6] ['table'] = "pos_riders";
		$join [6] ['condition'] = "rider_id = assigned_order_rider_id";
		$join [6] ['type'] = "LEFT";
		
		$join [7] ['select'] = "padd_api_type,padd_api_order_id,padd_api_driver_id,padd_api_driver_name,padd_api_driver_phone";
		$join [7] ['table'] = "pos_api_driver_details";
		$join [7] ['condition'] = "padd_order_id = order_id";
		$join [7] ['type'] = "LEFT";

		$join [8] ['select'] = "outlet_name,outlet_smartsend_id";
		$join [8] ['table'] = "pos_outlet_management";
		$join [8] ['condition'] = "outlet_id = order_outlet_id ";
		$join [8] ['type'] = "LEFT";
		
		/*$join [6] ['select'] = "order_baby_name,order_baby_parent_name,order_baby_card_color,order_baby_image,order_baby_dob,order_baby_msg,order_baby_cart_type";
		$join [6] ['table'] = "order_babypack_details";
		$join [6] ['condition'] = "order_primary_id = order_baby_order_primary_id";
		$join [6] ['type'] = "LEFT";
*/
		/* order table values */
		$select_values = array (
				'order_primary_id',
				'order_company_app_id',
				'order_tat_time',
				'delivery_agent',
                'delivery_order_reference_id',
                'delivery_order_status',
				'order_id',
				'order_outlet_id',
				'order_location_id',
				'order_delivery_charge',
				'order_tax_charge',
				'order_discount_applied',
				'order_discount_amount',
				'order_discount_type',
				'order_promocode_name',
				'order_sub_total',
				'order_total_amount',
				'order_payment_mode',
				'order_payment_getway_type',
				'order_payment_retrieved',
				'order_cash_payment',
				'order_date',
				'order_status',
				'order_availability_id',
				'order_availability_name',
				'order_pickup_time',
				'order_pickup_outlet_id',
				'order_source',
				'order_callcenter_admin_id',
				'order_local_no',
				'order_remarks',
				'order_table_number' ,
				'order_status',
				'order_additional_delivery',
				'order_tax_charge',
				'order_tax_calculate_amount',
				'order_tax_charge_inclusive',
				'order_tax_calculate_amount_inclusive',
				'order_service_charge',
				'order_service_charge_amount',
				'order_subcharge_amount',
				'order_special_discount_amount',
		        'order_special_discount_type',
		        'order_promocode_name',
		        'order_redeemed_points',
		        'order_carpal_api_order_id',
				'order_delivary_type' ,
				'order_smart_send_id' ,
				'order_smartsend_accesstoken' ,
				'order_pickup_time_slot_from',
				'order_pickup_time_slot_to',
				'order_cutlery',
				'(CASE 
		WHEN pos_orders.order_status="1" THEN "Received"		
        WHEN pos_orders.order_availability_id="718B1A92-5EBB-4F25-B24D-3067606F67F0" && pos_orders.order_status="2" THEN "Ready to pickup"
        WHEN pos_orders.order_availability_id="EF9FB350-4FD4-4894-9381-3E859AB74019" && pos_orders.order_status="2" THEN "Ready to eat"
        ELSE pos_order_status.status_name
    END) AS status_name'
		);

		/* order item values */
		$select_product_values = array (
				'item_image',
				'item_id',
				'item_product_id',
				'item_outlet_id',
				'item_name',
				'item_sku',
				'item_qty',
				'item_unit_price',
				'item_total_amount',
				'item_created_on',
				'item_placed_on' 
		);

		$select_modifier_values = array (
				'order_item_id',
				'order_modifier_id',
				'order_modifier_parent',
				'order_modifier_name',
				'order_modifier_price',
				'order_modifier_qty',
				'order_modifier_type' 
		);
		/* get order list from query */
		$order_list = $CI->Mydb->get_all_records($select_values, 'pos_orders', $where, '', '', '', '', array('order_primary_id'), $join );

		$output = array();

		/* check order empty */
		$outletID = array();
		if (! empty ( $order_list )) {

			foreach ( $order_list as $odlist ) { // print_r($odlist); exit;

				if($odlist['order_company_app_id'] === '59145A47-4517-41B4-9D92-F6BA15DB571A'){
					$odlist['order_customer_address_line1'] = $odlist['order_customer_unit_no1'].', '.$odlist['order_customer_address_line1'];
					$odlist['order_customer_unit_no1'] = '';
				}
				
		
			    $odlist['order_payment_statustext'] = get_payment_status_app($odlist['order_company_app_id'],$odlist['order_payment_mode'],$odlist['order_payment_retrieved'],'',$odlist['order_cash_payment']);
			
				$posLogData = $CI->Mydb->get_all_records ( 'order_id', 'pos_order_pos_log', array (
						'order_id' => $odlist ['order_primary_id'] 
				) );

				$additionalcharge = $CI->Mydb->get_all_records('order_additionalcharge_title, additionalcharge_allocate, additionalcharge_value, order_additionalcharge_amount', 'order_additionalcharge', array('order_additionalcharge_app_id'=>$app_id, 'order_additionalcharge_order_primary_id'=>$odlist['order_primary_id']));
				$odlist['additionalcharge'] = array();;
				if(!empty($additionalcharge)) {
					$odlist['additionalcharge'] = $additionalcharge;
				}

				$join1 = array();
				$join1 [0] ['select'] = "status_name";
				$join1 [0] ['table'] = "pos_order_status";
				$join1 [0] ['condition'] = "outlet_order_status = status_id AND status_enabled='A' ";
				$join1 [0] ['type'] = "LEFT";
				$outlet_Status = $CI->Mydb->get_all_records ( 'outlet_id, outlet_order_status, outlet_tax, outlet_tax_amount, outlet_order_commission_amount, outlet_sub_total_amount', 'order_outlet', array (
						'outlet_order_primary_id' => $odlist ['order_primary_id'] 
				), '', '', '', '', '', $join1  );
				$outletStatus = array();

				if(!empty($outlet_Status)) {
					$outlet_id_status = array_column($outlet_Status, 'outlet_id');
					$outletStatus = array_combine($outlet_id_status, $outlet_Status);
					foreach ($outletStatus as $oskey => $osval) {
						if($osval['outlet_order_status']==2) {
							$outletStatus[$oskey]['status_name'] = 'Ready to Collect';
							/*if($odlist['order_availability_id']==PICKUP_ID) {
								$outletStatus[$oskey]['status_name'] = 'Ready to pickup';
							}*/
						}
					}
				}
				
				$odlist['order_pos_log_count'] = count($posLogData);
			
				/* Kinshun Delivery integration */
                if($odlist['delivery_agent']=='Kin shun'){
                    $order_details = $CI->Mydb->get_record('*','delivery_order',array('order_primary_id'=>$odlist ['order_primary_id']));
                    
                    $odlist['kinshun_delivery_details'] =$order_details;
                }else{
                    $odlist['kinshun_delivery_details'] = array();
                }
                /* end Kinshun Delivery integration */
			
				/* get product details.. */
				$itemWhere = array('item_order_primary_id' => $odlist ['order_primary_id']);
				if(!empty($outlet_id)) {
					$itemWhere = array_merge($itemWhere, array('item_outlet_id'=>$outlet_id));
				}
				
				$order_items = $CI->Mydb->get_all_records ( '*', 'pos_order_items', $itemWhere );
				//echo $this->db->last_query();
				$fianl = array ();
				$itemPrice = 0;
				if (! empty ( $order_items )) { // echo 2;
					$i = 0;
					foreach ( $order_items as $items ) {
						if(!in_array($items['item_outlet_id'], $outletID)) {
							$outletID[] = $items['item_outlet_id'];
						}
						/* get modifier values... */
						$modifier_array = $extra_modifier_array = array (); /* old code null */

						$modifier_array = bp_product_modifiers_get ( $odlist ['order_primary_id'], $items ['item_id'], 'Modifier', 'order_item_id', 'callback' );
						$items['modifiers'] = $modifier_array;

						/* get extra modifiers */

						$extra_modifier_array = bp_product_extra_modifiers_get ( $odlist ['order_primary_id'], $items ['item_id'], 'Modifier', 'order_item_id', 'callback' );
						$items['extra_modifiers'] = $extra_modifier_array;

						/* get menu set component values */
						$menu_components = array ();
						$menu_components = bp_product_menu_component_get ( $odlist ['order_primary_id'], $items ['item_id'], 'MenuSetComponent', 'order_menu_primary_id', 'callback' );

						$items['set_menu_component'] = $menu_components;
						
						$items['item_name'] = str_replace("\n","",strip_tags($items ['item_name']));
						$outletkey = array_search($items['item_outlet_id'], $outletID);
						$outletInfoDetails = (!empty($outlet_Data[$items['item_outlet_id']]))?$outlet_Data[$items['item_outlet_id']]:'';
						if(!empty($outletStatus)) {

							if(!empty($outletStatus[$items['item_outlet_id']])) {
								$outletInfoDetails = array_merge($outletInfoDetails, $outletStatus[$items['item_outlet_id']]);
							}
							
						}						

						$fianl[$outletkey]['outletinfo'] = $outletInfoDetails;
						$fianl[$outletkey]['outletitem'][] = $items;
						$itemPrice+=($items ['item_unit_price']*$items ['item_qty']);

					}
				}

				if(strtotime(date('d-m-Y',strtotime($odlist['order_date']))) == strtotime(date('Y-m-d'))) {
					$odlist['order_type'] = 'current';
				} else {
					$odlist['order_type'] = 'advance';
				}

				if($odlist['customer_unique_id']!=null) {
					$odlist['customer_name'] = $odlist['customer_name'].' ('.$odlist['customer_unique_id'].(($odlist['membership_type'] != '')?'-'.$odlist['membership_type']:'').')';
				}

					/*If assigned third party rider details*/
					if($odlist['padd_api_order_id'] != '' && $odlist['order_delivary_type'] == 'carpal'){

					  $odlist['rider_fname'] = $odlist['padd_api_type']."/".$odlist['padd_api_order_id']."/".$odlist['padd_api_driver_name'];
					   $odlist['rider_mobile_no'] = $odlist['padd_api_driver_phone'];
					}
					else if($odlist['padd_api_order_id'] == '' && $odlist['order_delivary_type'] == 'carpal'){

						 $odlist['rider_fname'] = 'Carpal/'.$odlist['order_carpal_api_order_id']; 
					}
					else if($odlist['order_carpal_api_order_id'] != '' && $odlist['order_delivary_type'] == 'lalamove'){
						if($odlist['padd_api_driver_name'] == '' && $odlist['padd_api_driver_phone'] =='') {
							 $odlist['rider_fname'] = 'Lalamove/'.$odlist['order_carpal_api_order_id'];
						} else {

							 $odlist['rider_fname'] = 'Lalamove/'.$odlist['padd_api_driver_name']; 
							 $odlist['rider_mobile_no'] = $odlist['padd_api_driver_phone'];
							 
						}
					} else {}


				if($odlist['order_date'] != "") {
					$order_datetime = date("d-m-Y h:i A",strtotime("-30 minutes",strtotime($odlist['order_date'])));
					$odlist['order_date_before_30minit'] = $order_datetime;
				}

				//echo $itemPrice;
				//echo '<br />';
				//echo $outlet_id;

				if(!empty($outlet_id)) {
					$odlist['order_sub_total'] = number_format($itemPrice,2,".","") ;
					$odlist['order_total_amount'] = number_format($itemPrice,2,".","") ;
					$odlist['order_delivery_charge'] = "0.00";
					$odlist['order_discount_amount'] = "0.00";
					$odlist['order_tax_charge'] = "0.00";
				}
				if(!empty($sitelocation[$odlist['order_location_id']])) {
					$odlist['outlet_info'] = $sitelocation[$odlist['order_location_id']];
				}
				//print'<pre>';print_r($odlist);
				$odlist['items'] = $fianl;
			 	$output[] = $odlist;
			}

		}
		
		return $output;
	}
}
if (! function_exists ( 'bp_product_modifiers_get' )) {

	function bp_product_modifiers_get($order_id = "", $item_id = "", $type, $field, $response = null) {

		$CI = & get_instance ();
		$result = array ();
		$modifiers = $CI->Mydb->get_all_records ( 'order_modifier_id,order_modifier_name', 'order_modifiers', array (
				'order_modifier_type' => $type,
				$field => $item_id,
				'order_modifier_parent' => '' 
		) );

		if (! empty ( $modifiers )) {

			foreach ( $modifiers as $modvalues ) {
				/* get modifier values */
				$modifier_values = $CI->Mydb->get_all_records ( array (
						'order_modifier_id',
						'order_modifier_name',
						'order_modifier_qty',
						'order_modifier_price' 
				), 'order_modifiers', array (
						'order_modifier_type' => $type,
						$field => $item_id,
						'order_modifier_parent' => $modvalues ['order_modifier_id'] 
				) );
				
				if (! empty ( $modifier_values )) {
					$modvalues ['modifiers_values'] = $modifier_values;
					$result [] = $modvalues;
				}
			}
		}
		return $result;
		
	}
	
}

if (! function_exists ( 'bp_product_extra_modifiers_get' )) {

	function bp_product_extra_modifiers_get($order_id = "", $item_id = "", $type, $field, $response = null) {
		$CI = & get_instance ();
		
		$result = array ();
		$modifiers = $CI->Mydb->get_all_records ( 'order_extra_modifier_id,order_extra_modifier_name', 'order_extra_modifiers', array (
				
				'order_extra_modifier_item_id' => $item_id,
				'order_extra_modifier_parent' => '' 
		) );
		
		if (! empty ( $modifiers )) {
			
			foreach ( $modifiers as $modvalues ) {
				/* get modifier values */
				$modifier_values = $CI->Mydb->get_all_records ( array (
						'order_extra_modifier_id',
						'order_extra_modifier_name',
						'order_extra_modifier_qty',
						'order_extra_modifier_price' 
				), 'order_extra_modifiers', array (
						
						'order_extra_modifier_item_id' => $item_id,
						'order_extra_modifier_parent' => $modvalues ['order_extra_modifier_id'] 
				) );
				
				if (! empty ( $modifier_values )) {
					$modvalues ['modifiers_values'] = $modifier_values;
					$result [] = $modvalues;
				}
			}
		}
		return $result;
	}
	
}

if (! function_exists ( 'bp_product_menu_component_get' )) {

	function bp_product_menu_component_get($order_id = "", $item_id = "", $type, $field, $response = null) {
		$CI = & get_instance ();
		
		$result = $output_result = array ();
		$com_set = $CI->Mydb->get_all_records ( array (
				'menu_menu_component_id',
				'menu_menu_component_name' 
		), 'order_menu_set_components', array (
				'menu_item_id' => $item_id 
		), '', '', '', '', 'menu_menu_component_id' );
		
		$set_value = array ();
		if (! empty ( $com_set )) {
			
			foreach ( $com_set as $set ) {
				
				$set_value ['menu_component_id'] = $set ['menu_menu_component_id'];
				$set_value ['menu_component_name'] = $set ['menu_menu_component_name'];
				
				/* get prodict details */
				$menu_items = $CI->Mydb->get_all_records ( array (
						'menu_primary_id',
						'menu_product_id',
						'menu_product_name',
						'menu_product_qty',
						'menu_product_sku' 
				), 'order_menu_set_components', array (
						'menu_item_id' => $item_id,
						'menu_menu_component_id' => $set ['menu_menu_component_id'] 
				) );
				$product_details = array ();
				if (! empty ( $menu_items )) {
					
					foreach ( $menu_items as $items ) {
						$items ['modifiers'] = bp_product_modifiers_get ( $order_id, $items ['menu_primary_id'], 'MenuSetComponent', $field, 'callback' );
						$product_details [] = $items;
					}
					
					$set_value ['product_details'] = $product_details;
					// $set_value['product_details']['modifiers'] = $modifiers;
					$output_result [] = $set_value;
				}
			}
		}
		return $output_result;
	}
	
}

if (! function_exists ( 'bp_catering_order_details' )) {

	function bp_catering_order_details($app_id,$company_id,$order_id,$order_type=1) {

		$CI = & get_instance ();		
		$tabel = "orders";        
		$common = $where_in = $ouput = array ();		
		$where = array (
				'order_id' => $order_id, 
				'order_company_app_id' => $app_id,				
		);		
					
		/* join tables - Outlet table */
		$join [0] ['select'] = "outlet_name";
		$join [0] ['table'] = "pos_outlet_management";
		$join [0] ['condition'] = "outlet_id = order_outlet_id ";
		$join [0] ['type'] = "LEFT";
		
		/* join tables - Status table */
		$join [1] ['select'] = "status_name";
		$join [1] ['table'] = "pos_order_status";
		$join [1] ['condition'] = "order_status = status_id AND status_enabled='A' ";
		$join [1] ['type'] = "INNER";
		
		/* join tables - admin user table */
		$join [2] ['select'] = "CONCAT(user_fname, ' ', user_lname) AS callcenter_admin_name";
		$join [2] ['table'] = "pos_company_users";
		$join [2] ['condition'] = "user_id = order_callcenter_admin_id";
		$join [2] ['type'] = "LEFT";
		
		/* join tables - Status table */
		$join [3] ['select'] = "order_method_name";
		$join [3] ['table'] = "pos_order_methods";
		$join [3] ['condition'] = "order_payment_mode = order_method_id  ";
		$join [3] ['type'] = "LEFT";
		
		/* join tables - customer table table */
		$join [4] ['select'] = "order_customer_fname,order_customer_lname,order_customer_email,order_customer_mobile_no,order_customer_unit_no1,order_customer_unit_no2,order_customer_address_line1,order_customer_address_line2,order_customer_city,order_customer_state,,order_customer_country,order_customer_postal_code";
		$join [4] ['table'] = "pos_orders_customer_details";
		$join [4] ['condition'] = "order_customer_order_primary_id = order_primary_id";
		$join [4] ['type'] = "INNER ";
		
		$select_values = array (
				'order_primary_id',
				'order_id',
				'order_local_no',
				'order_outlet_id',
				'order_delivery_charge',
				'order_tax_charge',
				'order_tax_calculate_amount',
				'order_discount_applied',
				'order_discount_amount',
				'order_sub_total',
				'order_total_amount',
				'order_payment_mode',
				'order_payment_getway_type',
				'order_date',
				'order_status',
				'order_remarks',
				'order_availability_id',
				'order_availability_name',
				'order_pickup_time',
				'order_pickup_outlet_id',
				'order_source',
				'order_callcenter_admin_id',
				'order_cancel_source',
				'order_cancel_by',
				'order_cancel_remark',
				'order_tat_time',
				'order_discount_applied',
				'order_discount_amount',
				'order_discount_type',
				'order_delivery_waver',
				'order_created_on'
		);			
		/*--------------------------------Where in------------------------------*/
		$result = $CI->Mydb->get_all_records ( $select_values, $tabel, $where, '', '', '', '', '', $join, '' );
		
		if (! empty ( $result )) {
			foreach ( $result as $odlist ) {
				/* get product details.. */
				$order_items = $CI->Mydb->get_all_records ( '*', 'pos_order_items', array (
						'item_order_primary_id' => $odlist ['order_primary_id'] 
				) );
				$fianl = array ();
				if (! empty ( $order_items )) { // echo 2;
					foreach ( $order_items as $items ) {
						/* get modifier values... */
						$modifier_array = $extra_modifier_array = $condiment_array = $addons_array = $setup_array = $equipment_array = array (); /* old code null */
						
						$modifier_array = product_modifiers_get ( $odlist ['order_primary_id'], $items ['item_id'], 'Modifier', 'order_item_id', 'callback' );
						$items ['modifiers'] = $modifier_array;
						
						$addons_array = product_addons_get( $odlist ['order_primary_id'], $items ['item_id'], 'callback' );
						$items ['addons'] = $addons_array;
						
						$setup_array = product_setup_get ( $odlist ['order_primary_id'], $items ['item_id'], 'callback' );
						$items ['setup'] = $setup_array;
						
						$equipment_array = product_equipment_get ( $odlist ['order_primary_id'], $items ['item_id'], 'callback' );
						$items ['equipment'] = $equipment_array;
											
						$fianl [] = $items;
					}
				}
				
				$odlist ['items'] = $fianl;
				$output [] = $odlist;
			}
		}		
	  return $output;
	}
}

if (! function_exists ( 'product_modifiers_get' )) {

	function product_modifiers_get($order_id = "", $item_id = "", $type, $field, $response = null) {
		$CI = & get_instance ();
		$result = array ();
		$modifiers = $CI->Mydb->get_all_records ( 'order_modifier_id,order_modifier_name', 'order_modifiers', array (
				'order_modifier_type' => $type,
				$field => $item_id,
				'order_modifier_parent' => '' 
		) );
		
		if (! empty ( $modifiers )) {
			
			foreach ( $modifiers as $modvalues ) {
				/* get modifier values */
				$modifier_values = $CI->Mydb->get_all_records ( array (
						'order_modifier_id',
						'order_modifier_name',
						'order_modifier_qty',
						'order_modifier_price' 
				), 'order_modifiers', array (
						'order_modifier_type' => $type,
						$field => $item_id,
						'order_modifier_parent' => $modvalues ['order_modifier_id'] 
				) );
				
				if (! empty ( $modifier_values )) {
					$modvalues ['modifiers_values'] = $modifier_values;
					$result [] = $modvalues;
				}
			}
		}
		return $result;
	}
	
}

if (! function_exists ( 'product_addons_get' )) {

	function product_addons_get($order_id = "", $item_id, $response = null) {
		$CI = & get_instance ();
		$result = array ();
		$addons = $CI->Mydb->get_all_records ( 'oa_addons_p_id,oa_addons_id,oa_addons_name,oa_addons_label,oa_addons_qty,oa_addons_price,oa_addons_total_price', 'order_addons', array (								
				'oa_order_id' => $order_id,
				'oa_order_item_id' => $item_id,				
		) );
	
		if (! empty ( $addons )) 
		{
			
			$result=$addons;
		}
	
		if ($response == 'callback') {
			return $result; /* callback function response */
		} else {
	
			$this->set_response ( array (
					'status' => "ok",
					'result_set' => $result
			), success_response () ); /* API response */
		}
	}
	
}
if (! function_exists ( 'product_setup_get' )) {

	function product_setup_get($order_id = "", $item_id, $response = null) {
		$CI = & get_instance ();
		$result = array ();
		$setup = $CI->Mydb->get_all_records ( 'os_setup_p_id,os_setup_id,os_setup_name,os_setup_type,os_setup_description,os_setup_qty,os_setup_price,os_setup_total_price', 'order_setup', array (								
				'os_order_id' => $order_id,
				'os_order_item_id' => $item_id,				
		) );
	
		if (! empty ( $setup )) 
		{
			
			$result=$setup;
		}
	
		if ($response == 'callback') {
			return $result; /* callback function response */
		} else {
	
			$this->set_response ( array (
					'status' => "ok",
					'result_set' => $result
			), success_response () ); /* API response */
		}
	}
	
}
if (! function_exists ( 'product_equipment_get' )) {

	function product_equipment_get($order_id = "", $item_id, $response = null) {
		$CI = & get_instance ();
		$result = array ();
		$equipments = $CI->Mydb->get_all_records ( 'oe_equipment_p_id,oe_equipment_id,oe_equipment_name,oe_equipment_description,oe_equipment_qty,oe_equipment_price,oe_equipment_total_price', 'order_equipments', array (								
				'oe_order_id' => $order_id,
				'oe_order_item_id' => $item_id,				
		) );
	
		if (! empty ( $equipments )) 
		{
			
			$result=$equipments;
		}
	
		if ($response == 'callback') {
			return $result; /* callback function response */
		} else {
	
			$this->set_response ( array (
					'status' => "ok",
					'result_set' => $result
			), success_response () ); /* API response */
		}
	}
	
}

if(!function_exists('bs_post_contents'))
{
	function bs_post_contents($apiurl=null,$data_json)
	{
		$post_field_string = http_build_query($data_json, '', '&');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $apiurl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_field_string);
		curl_setopt($ch, CURLOPT_POST, true);
		$response = curl_exec($ch);
		curl_close ($ch);
		return $response;
	}
}
