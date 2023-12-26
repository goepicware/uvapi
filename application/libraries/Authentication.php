<?php

if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
class Authentication {
	protected $ci;
	public function __construct() {
		$this->ci = & get_instance ();
	}
	
	/* Master adminpanel authenticaion */
	function admin_authentication() {
		$ninja_admin = $this->ci->session->userdata ( "nc_admin_id" );
		($ninja_admin == "") ? redirect ( admin_url () ) : '';
		if($ninja_admin == 2 && ($this->ci->uri->segment(2) != "restaurants"  ) 		) {
			redirect ( admin_url ('restaurants') ) ;
		}
	}
	/* callcenter authenticaion */
	function callcenter_authentication() {
		$ninja_admin = $this->ci->session->userdata ( "call_company_admin_id" );
		($ninja_admin == "") ? redirect ( callcenter_url () ) : '';
	}
	
	/* client adminpanel authenticaion */
	function company_authentication($module_slug = null) {
		/* check module permission */
		$modules = array ();
		$module_slug = strtolower($module_slug);
		$modules = $this->ci->session->userdata ( 'camp_module_permission' );  
		if (isset($modules) && ! in_array ( $module_slug, $modules ) &&  $this->ci->session->userdata('camp_admin_type') == "SubAdmin") {
			echo get_label ( 'access_denied' );
			exit ();
		}
		
		$ninja_admin = $this->ci->session->userdata ( "camp_company_admin_id" );
		$ninja_company_id = $this->ci->session->userdata ( "camp_company_id" );
		if($ninja_company_id !='')
		{
			$segment2= $this->ci->uri->segment(2);
			$segment3= $this->ci->uri->segment(3); 
			$records = $result = $this->ci->Mydb->get_record('*','clients',array('client_id'=>$ninja_company_id));

			if(!empty($records) && strtotime(date('Y-m-d')) > strtotime($records['client_next_payment_made']) && $segment3 != 'payment' && $segment3 !='deletecard' && $records['client_payment_method'] == 'online')
			{
				redirect(camp_url ()."settings/payment");
			}

			/*if(!empty($records) && $records['client_payment_method'] == 'online' && $records['client_payment_made'] == 0 && $segment3 != 'payment' && $segment3 !='deletecard')
			{
				redirect(camp_url ()."settings/payment");
			}*/
		}
		($ninja_admin == "") ? redirect ( camp_url () ) : '';
	}
	
	
	function session_set_change($companyid,$company_userid) {
		
		$company =$this->ci->Mydb->get_record('client_id, client_app_id,client_date_format,client_time_format,client_currency,client_country,client_language,client_records_perpage,client_brand_enable','clients',array('client_id' => $companyid,'client_app_id !='=>"",'client_status'=>'A'));
		
		$check_details = $this->ci->Mydb->get_record ('user_id,user_fname,user_lname,user_app_id,user_company_id,user_username,user_email_address,user_status,user_password,user_type,user_group_id', 'company_users', array ('user_id' => $company_userid) );
		
		if(!empty($check_details) && !empty($company)) {
			$session_datas = array('camp_company_id' => $check_details['user_company_id'],
				'camp_company_app_id' => $check_details['user_app_id'], 
				'camp_company_admin_id'=>$check_details['user_id'],
				'camp_admin_type'=>$check_details['user_type'],
				'camp_company_firstlast_name'=>stripslashes($check_details['user_fname'].(!empty($check_details['user_lname'])?" ".$check_details['user_lname']:"")),
				'camp_admin_username'=>$check_details['user_username'],
				'camp_admin_language'=>$company['client_language'],
				'camp_admin_country'=>$company['client_country'],
				'camp_admin_currency'=>$company['client_currency'],
				'camp_admin_dateformat'=>$company['client_date_format'],
				'camp_admin_timeformat'=>$company['client_time_format'],
				'camp_admin_brand_enabled'=>$company['client_brand_enable'],
				'camp_admin_records_perpage'=>$company['client_records_perpage']
			);
			$this->ci->session->set_userdata($session_datas);
			
			/* update Company folder start here .. */
			$company_folder = $this->ci->Mydb->get_record('client_folder_name,client_name,client_category_modifier_enable','clients',array('client_id' => $check_details['user_company_id'], 'client_app_id' => $check_details['user_app_id'] ));
			if(isset($company_folder['client_folder_name']))
			{
				create_folder($company_folder['client_folder_name']);
				$this->ci->session->set_userdata('camp_company_folder',$company_folder['client_folder_name']);
				$this->ci->session->set_userdata('camp_company_name',$company_folder['client_name']);
				$this->ci->session->set_userdata('camp_company_category_modifier',$company_folder['client_category_modifier_enable']);
			}
			$this->ci->session->set_userdata('camp_module_permission',array('all modules'));
		}
		
	}

	/* Business  adminpanel authenticaion */
	function business_authentication() {
		$business_admin = $this->ci->session->userdata ( "business_admin_id" );
		$business_company_id = $this->ci->session->userdata ( "business_company_id" );

		if($business_company_id !='')
		{
			$segment2= $this->ci->uri->segment(2);
			$segment3= $this->ci->uri->segment(3); 
			$records = $result = $this->ci->Mydb->get_record('*','clients',array('client_id'=>$business_company_id));

			if(!empty($records) && $records['client_payment_made'] == 0 && $records['client_payment_method'] == 'online')
			{
				$this->session_set_change($business_company_id,$business_admin);
				redirect(camp_url ()."settings/payment");
			}
		}
		
		//redirect(camp_url ()."settings/payment");
		$business_default_outlet = $this->ci->session->userdata ( "business_default_outlet" );
		$records = validate_outlet($business_default_outlet);
		if ( $this->ci->session->userdata ( "business_admin_type" ) == "MainAdmin") {
			($business_admin == "") ? redirect ( base_url () ) : '';
		} else {
			($business_admin == "" || empty($records)) ? redirect ( base_url () ) : '';
		}
	} 
}
 
/* End of file authentication.php */
/* Location: ./application/libraries/authentication.php */
