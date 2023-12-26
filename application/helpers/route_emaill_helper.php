<?php 
	/* get email template id */
if (! function_exists ( 'get_emailtemplate' )) {
	function get_emailtemplate($company_app_id, $label) {
		$CI = & get_instance ();
		$emailTemp = $CI->Mydb->get_record('email_id', 'email_templates', array('email_unquie_id'=>$company_app_id, 'email_config_key'=>$label));
		if(!empty($emailTemp)) {
			return $emailTemp['email_id'];
		}
	}
}

