<?php
/**************************
 Project Name	: POS
 Created on		: 30 March, 2016
 Last Modified 	: 30 March, 2016
 Description		: Page contains common REST settings - Api v2 version common helper file
 ***************************/

if (! defined ( 'BASEPATH' ))
exit ( 'No direct script access allowed' );

/* this function used validate APP ID */
if(!function_exists('app_validation_v2'))
{
	function app_validation_v2($app_id)
	{
		$CI =& get_instance();
		$select_array = array (
				'client_id',
				'client_app_id',
				'client_name',
				'client_email_address',
				'client_from_email',
				'client_notify_email',
				'client_site_url',
				'client_defalut_timezone',
				'client_date_format',
				'client_time_format',
				'client_currency',
				'client_folder_name',
				'client_category_modifier_enable',
				'client_promocode_enable',
				'client_tax_enable',
				'client_tax_surcharge',
				'client_delivery_enable',
				'client_delivery_surcharge',
				'client_country',
				'client_loyality_enable',
				'client_voucher_enable',
				'client_gift_enable',
				'client_timer_enable',
				'client_condiment_enable',
				'client_delivery_surcharge',
				'client_timer_enable',
				'client_start_time',
				'client_timeslot_enable',
				'client_holiday_enable',
				'client_end_time',
				'client_reward_point',
				'client_promocode_options',
				'client_promo_code',
				'client_newsletter_default_group',
				'client_promo_code_normal_popup_enable',
				'client_shipping_country',
				'client_country_currency',
				'client_country_currency_difference',
				'client_currency_api',
				'client_chief_recommandation_enable',
				'client_delivery_charges',
				'client_catering_buffet',
				'client_catering_deliverycharge',
				'client_new_product_option_enable',
				'client_highlight_product_option_enable',
				'client_delivery_time_setting_enable',
				'client_product_day_availability_enable',
				'client_subcategory_day_availability_enable',
				'client_category_day_availability_enable',
				'client_product_special_days_enable',
				'client_catering_notify_email',
				'client_reservation_notify_email',
				'client_catering_functionalroom',
		         'client_cache_enable',
		         'client_page_cache',
		         'client_query_cache'

		         );

		         $company = $CI->Mydb->get_record($select_array,'clients', array('client_app_id' =>addslashes($app_id),'client_status'=>'A'));
		         if(empty($company)){
		         	echo json_encode(array('status'=>'error','message' => get_label('app_invalid'))); exit;
		         } else {
		         	return $company;
		         }
	}
}

/* this function used to apply cache */
if (! function_exists ( 'applyCache' )) {
	function applyCache($cache,$dbcache) {
		if($cache =="Yes" && $dbcache == "Yes"){
			return get_instance()->db->cache_on();
		}
	}
}


