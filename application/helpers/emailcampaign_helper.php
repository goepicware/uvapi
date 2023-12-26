<?php
/**************************
 Project Name	: POS
Created on		: 19 Sep, 2016
Last Modified 	: 20 Sep, 2016
Description		: this file contains Emailcampaign..
***************************/

/* Revert back loyality points */
if (! function_exists ( 'cron_emailcampaign' )) {

	function cron_emailcampaign($app_id,$ref_id,$customer_id,$for) {

		$CI = & get_instance ();

		/*Refer campanel helper - get_email_campaign_type
		 * '1'-> 'Last visit',
		   '2' => 'Loyalty cash value',
		   '3' => 'Abandon cart',
		   '4' => 'Product review', 
		 * */

		$res_set = '';

		if(campaign_enable_opt($app_id,$for)) {

			/*Last visit*/
			if ($for == '1') {

				$res_set = $CI->Mydb->get_record(array('login_time as date_on'), 'customer_login_history',array('login_id'=>$ref_id));
				$date_on = $res_set['date_on'];

			}

			/*Loyalty point reminder*/
			if ($for == '2') {
				if($app_id=="97440967-AC09-422A-B5C2-BBA4D8C3D989")
				{
				$res_set = $CI->Mydb->get_record(array('lh_created_on as date_on'), 'loyality_history',array('lh_id'=>$ref_id));
				$date_on = $res_set['date_on'];	
				}
				else
				{
				$res_set = $CI->Mydb->get_record(array('lh_expiry_on as date_on'), 'loyality_points',array('lh_id'=>$ref_id));
				$date_on = $res_set['date_on'];
			    }
			}

			/*Abandoncart*/
			if ($for == '3') {

				$res_set = $CI->Mydb->get_record(array('cart_created_on as date_on'), 'cart_details',array('cart_id'=>$ref_id));
				$date_on = $res_set['date_on'];
			}

			/*Orer Product review*/
			if ($for == '4') {

				$res_set = $CI->Mydb->get_record(array('order_created_on as date_on'), 'orders',array('order_primary_id'=>$ref_id));
				$date_on = $res_set['date_on'];
			}

			if(!empty($res_set))
				campaign_insert_opt($app_id,$ref_id,$customer_id,$for,$date_on);
		}

		return true;
	}
}

if (! function_exists ( 'campaign_insert_opt' )) {

	function campaign_insert_opt($app_id,$ref_id,$customer_id,$for,$date_on) {

		$CI = & get_instance ();

		/*Get Email campaign List*/

		$ecamp_res_set = $CI->Mydb->get_all_records ('emailcampaign_days,emailcampaign_hours,emailcampaign_email_template', 'email_campaign', array (
				'emailcampaign_status' => 'A',
				'emailcampaign_app_id' => $app_id,
				'emailcampaign_type' => $for
		) );

		foreach($ecamp_res_set as $email_camp)
		{
			$days = (int)$email_camp['emailcampaign_days'];
			$hours = (int)$email_camp['emailcampaign_hours'];

			$date = new DateTime($date_on);

			if($days != 0) 
			{
				if($app_id=="97440967-AC09-422A-B5C2-BBA4D8C3D989")
				{
				$date->add(new DateInterval('P'.$days.'D'));
				}
				else
				{
					if ($for == '2') { /*Loyalty minus days*/
						$date->sub(new DateInterval('P'.$days.'D'));
					} else {
						$date->add(new DateInterval('P'.$days.'D'));
					}
			    }
			}

			$cron_date = $date->format('Y-m-d H:i:s');
			$cron_time = strtotime($cron_date);

			if($hours != 0) 
			{
				if($app_id=="97440967-AC09-422A-B5C2-BBA4D8C3D989")
				{
				$cron_time = $cron_time + ($hours*60*60);
				}
				else
				{
					if ($for == '2') { /*Loyalty minus days*/
						$cron_time = $cron_time - ($hours*60*60);
					} else {
						$cron_time = $cron_time + ($hours*60*60);
					}
			    }
			}

			$template_id = $email_camp['emailcampaign_email_template'];

			$insert_data = array(
			'cron_customer_id' => $customer_id,
			'cron_ref_id' => $ref_id,
			'cron_from' => $for,
			'cron_template_id' => $template_id,
			'cron_timestamp' => $cron_time,
			'cron_days' => $days,
			'cron_hours' => $hours,
			);

			$empty_res = $CI->Mydb->get_record(array('cron_id'), 'email_cron',array('cron_ref_id'=>$ref_id, 'cron_from'=> $for, 'cron_timestamp' => $cron_time));

			if(empty($empty_res)) {

				$insert_id =  $CI->Mydb->insert('email_cron',$insert_data);

				if ($for == '1' || $for == '3') { /*If last visit and abandon cart delete existing cron entries*/

					$exist_res = $CI->Mydb->get_record(array('cron_id'), 'email_cron',array('cron_id != '=>$insert_id ,'cron_customer_id' => $customer_id,'cron_from'=> $for,'cron_status' => '0'));
					if($exist_res) {

						$CI->Mydb->delete('email_cron',array('cron_id!=' => $insert_id ,'cron_customer_id' => $customer_id,'cron_from'=> $for,'cron_status' => '0'));

					}
				}

			}
		}

		return true;
	}
}

/*Check if individual campaign option are enabled or not*/
if (! function_exists ( 'campaign_enable_opt' )) {

	function campaign_enable_opt($app_id,$for) {

		$CI = & get_instance ();

		$opt = 0;

		$client_res = $CI->Mydb->get_record(array('client_last_visit_days_enable','client_loyalty_cash_value_enable','client_abandon_cart_enable','client_product_review_enable'), 'clients',array('client_app_id'=>$app_id));
		if (! empty ( $client_res )) {

			if ($client_res['client_last_visit_days_enable'] == '1' && $for == '1') {
				 $opt = 1;
			} elseif ($client_res['client_loyalty_cash_value_enable'] == '1' && $for == '2') {
				$opt = 1;
			} elseif ($client_res['client_abandon_cart_enable'] == '1' && $for == '3') {
				$opt = 1;
			} elseif ($client_res['client_product_review_enable'] == '1' && $for == '4') {
				$opt = 1;
			} else {}
		}

		return $opt;
	}
}
