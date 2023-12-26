<?php
/**************************
 Project Name	: POS
Created on		: 30 March, 2016
Last Modified 	: 30 March, 2016
Description		: Page contains common REST settings
***************************/

if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );


if (! function_exists ( 'insert_audit_data' )) {
	function insert_audit_data($module_name,$action_type,$user_id,$user_type,$via,$log_json,$company_id,$app_id) {
		$CI = & get_instance ();
		
		$insert_array = array(
		"audit_module_name" => $module_name,
		"audit_action_type"=>$action_type,
		"audit_user_id"=>$user_id,
		"audit_user_type"=>$user_type,
		"audit_ip_address" => get_ip(),
		"audit_created_on" => current_date (),
		"audit_action_via" =>  $via,
		"audit_log_josn" => $log_json,
		"audit_company_id" => $company_id,
		"audit_app_id" => $app_id
		
		);
		
		$insert_id = $CI->Mydb->insert ( "audit_logs", $insert_array );
	}
}

/* for notify to rider via sms and email*/
if(!function_exists('send_own_rider_notification')){
	function send_own_rider_notification($app_id, $order_primary_id,$rider_id   ){
		$CI=& get_instance();
			$rider_detail = $CI->Mydb->get_record ( array (
					'rider_fname',
					'rider_lname',
					'rider_mobile_no',
                    'rider_email_address'					
			), 'riders', array (
					'rider_id' => $rider_id,
					'rider_company_app_id' => $app_id 
			) );
			
			
		$records=$CI->Mydb->get_record('company_rider_notification_enable,company_rider_notification_via,company_id,company_app_id','company',array("company_unquie_id" => $app_id));
		//$orders=$CI->Mydb->get_record('','pos_orders',array("order_company_app_id" => $app_id, "order_primary_id" => $order_primary_id ));
		
		$join [0] ['select'] = "pos_outlet_management.outlet_name,pos_outlet_management.outlet_unit_number1,pos_outlet_management.outlet_unit_number2,pos_outlet_management.outlet_address_line1,pos_outlet_management.outlet_address_line2,,pos_outlet_management.outlet_postal_code";
		$join [0] ['table'] = "pos_outlet_management";
		$join [0] ['condition'] = "pos_orders.order_outlet_id = pos_outlet_management.outlet_id";
		$join [0] ['type'] = "LEFT";
		
		$join [1] ['select'] = "pos_orders_customer_details.order_customer_fname,pos_orders_customer_details.order_customer_mobile_no,pos_orders_customer_details.order_customer_unit_no1,pos_orders_customer_details.order_customer_unit_no2,pos_orders_customer_details.order_customer_address_line1 ,pos_orders_customer_details.order_customer_address_line2,pos_orders_customer_details.order_customer_postal_code   ";
		$join [1] ['table'] = "pos_orders_customer_details";
		$join [1] ['condition'] = "pos_orders.order_primary_id   = pos_orders_customer_details.order_customer_order_primary_id ";
		$join [1] ['type'] = "LEFT";
  
       $orders=$CI->Mydb->get_all_records('pos_orders.order_outlet_id,pos_orders.order_primary_id,pos_orders.order_local_no,pos_orders.order_date, pos_orders.order_delivery_charge ','pos_orders',array("order_company_app_id" => $app_id, "order_primary_id" => $order_primary_id,'order_availability_id' => '634E6FA8-8DAF-4046-A494-FFC1FCF8BD11' ),'','','','','',$join);
	  
	   $return_array = array();
		if( !empty($rider_detail) && !empty($orders) && !empty($records) && isset($records['company_rider_notification_enable']) && $records['company_rider_notification_enable'] == 1 ) {
			
		   $order_date =  date ( "m/d/Y g:i a", strtotime ( $orders[0]['order_date'] ) )	;
		   if($records['company_rider_notification_via'] == 2 ){
			   if($rider_detail["rider_mobile_no"] != "") {
				   
				   $outlet_address = get_default_address_format($orders[0]['order_customer_address_line1'],$orders[0]['order_customer_unit_no1'],$orders[0]['order_customer_unit_no2'],$orders[0]['order_customer_address_line2'],'',$orders[0]['order_customer_postal_code']);
				   send_sms_notify($records['company_id'], $rider_detail["rider_mobile_no"], strip_tags("Hi ".$rider_detail["rider_fname"].", New Order assigned. Order No: ".$orders[0]['order_local_no'].", Delivery date & time: ".$order_date.", Address: ".$orders[0]['outlet_name'] .", ".$outlet_address.", Customer No: ".$orders[0]['order_customer_mobile_no']));
			   }
		   }else if($records['company_rider_notification_via'] == 1){
			   
			  // $emai_logo = $base_url."media/email-logo/email-logo.jpg";

				$CI->load->library('myemail');
				$outlet_address = get_default_address_format($orders[0]['outlet_address_line1'],$orders[0]['outlet_unit_number1'],$orders[0]['outlet_unit_number2'],$orders[0]['outlet_address_line2'],'',$orders[0]['outlet_postal_code']);
				$customer_address = get_default_address_format($orders[0]['order_customer_address_line1'],$orders[0]['order_customer_unit_no1'],$orders[0]['order_customer_unit_no2'],$orders[0]['order_customer_address_line2'],'',$orders[0]['order_customer_postal_code']);

				$check_arr = array('[NAME]','[OUTLET_NAME ]','[OUTLET_ADDRESS ]','[ORDER_NO]','[CUSTOMER_NAME]','[CUSTOMER_MOBILE]','[DELIVERY_ADDRESS]','[DELIVERY_DATE]','[DELIVERY_CHARGE]');
				$replace_arr = array($rider_detail['rider_fname'],$orders[0]['outlet_name'],$outlet_address,$orders[0]['order_local_no'],$orders[0]['order_customer_fname'],$orders[0]['order_customer_mobile_no'],$customer_address,$order_date,$orders[0]['order_delivery_charge']);
				
				$email_template_id = 1692;
				
				if($email_template_id != '') 
				{
					$CI->myemail->send_client_mail($rider_detail['rider_email_address'],$email_template_id,$check_arr,$replace_arr,$records['company_id'],$records['company_unquie_id']);
					$return_array = array ('status' => "ok",'message' => "Notification sent");


				}
		   }
		}
		
		
		return $return_array;
	}
}

if(!function_exists('send_sms_notify')){
	function send_sms_notify($company_id, $customer_mobile, $textMessage ){
		
		$CI=& get_instance();
		$CI->load->library('twiliosms');
		
		$company_sms_option = $CI->Mydb->get_record ( 'company_id,company_unquie_id,company_app_name,company_sms_settings_enable,company_sms_period,company_sms_count_total,company_sms_startfrom,company_sms_count_balance,company_name', 'clients', array ('company_id' => $company_id));
		
		$company_sms_settings_enable = $company_sms_option['company_sms_settings_enable'];
		$company_sms_count_balance = $company_sms_option['company_sms_count_balance'];
		$return_array = array();
		if((int)$company_sms_settings_enable == 1) {
			
			if((int)$company_sms_count_balance>0) {
			
			$sms_record = $CI->Mydb->get_record('*', 'pos_sms_setting', array('smssetting_company_id' => $company_id));
				
				if($sms_record['smssetting_mode'] == 1) {
			
					$sms_session_mode = 'prod';
					
					$CI->twiliosms->mode = $sms_session_mode;
					$CI->twiliosms->account_sid = $sms_record['smssetting_account_sid_live'];
					$CI->twiliosms->auth_token = $sms_record['smssetting_auth_token_live'];
					$CI->twiliosms->api_version = '2010-04-01';
					$CI->twiliosms->number = $sms_record['smssetting_from_number_live'];
					
					$sms_from_num = $sms_record['smssetting_from_number_live'];
					
				} else {		
					$sms_session_mode = 'sandbox';
					
					$CI->twiliosms->mode = $sms_session_mode;
					$CI->twiliosms->account_sid = $sms_record['smssetting_account_sid_test'];
					$CI->twiliosms->auth_token = $sms_record['smssetting_auth_token_test'];
					$CI->twiliosms->api_version = '2010-04-01';
					$CI->twiliosms->number = $sms_record['smssetting_from_number_test'];	
					
					$sms_from_num = $sms_record['smssetting_from_number_test'];
				}				
				
				if($customer_mobile != ''){
					
					if (strpos($customer_mobile, '+65') !== false) {
						$to_num = $customer_mobile;
					} else if ((strpos($customer_mobile, '65') !== false) && (strlen($customer_mobile)>=8)) {
						$to_num = '+65'.$customer_mobile;
					} else if (strlen($customer_mobile)==8) {
						$to_num = '+65'.$customer_mobile;
					} else {
						$sms_reason = 'Invalid phone number.';
					}
						
				}else{
					$sms_reason = 'Phone number was empty';
				}
				/*if($_SERVER['REMOTE_ADDR']==='157.51.225.165') {
					echo 'Numwerr'.$to_num;
				}*/
				$sms_response = $sms_log = null;
							
				$from_num = $sms_from_num;
				
				$message = $textMessage;
				
				if(!empty($to_num)) {
					
					$CI->twiliosms->valid_mode();
							
					$smsResponse = $CI->twiliosms->sms($from_num, $to_num, $message);
				
					if(!empty($smsResponse)) {

						 $sms_response  = ($smsResponse->IsError == 1)?'failed':'sent';
						 $sms_log  = (!empty($smsResponse->ResponseText)) ? (array)$smsResponse->ResponseText : (array)$smsResponse->ErrorMessage;		 
						 $sms_reason = json_encode($sms_log);

					} else {
						
						 $sms_response  = 'failed';
					}
				} else {
						
					$sms_response  = 'failed';
				}
				
				if($sms_response == 'sent') {
											
					$update_sms_countBln = (int)$company_sms_count_balance - 1;
					$updateArray=array('company_sms_count_balance' => $update_sms_countBln);
					$updateWhere = array('company_id' => $company_id);
					$ClntUpdate = $CI->Mydb->update('clients',$updateWhere,$updateArray);
					
					$return_array = array ('status' => "ok",'message' => "Success",'sms_reason' => $sms_reason, 'sms_response' => $sms_response);

				 
					
				}
				
				if($sms_response == "failed"){
					
				 $return_array = array ('status' => 'error', 'message' => "Twilio sms error ".$smsResponse->ErrorMessage) ;
					
					//$CI->set_response (array ('status' => 'error', 'message' => "Please enter a valid phone number."), something_wrong () );
					
				}
			}else{
				
			 $return_array = array ('status' => 'error', 'message' => "Please add sms count for this APP."); /* error message */
				
			}
				
				
			
		}else{
			$return_array = array ('status' => 'error', 'message' => "Please enble sms option for this APP."); /* error message */
		}
		
		return $return_array;
	}	
}

