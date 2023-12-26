<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* this function used to  200 response */
if(!function_exists('success_response'))
{
	function success_response()
	{
		return 200;
	}
}

/* this function used to  success response */
if(!function_exists('notfound_response'))
{
	function notfound_response()
	{
		return 200;
	}
}

/* this function used to  success response */
if(!function_exists('something_wrong'))
{
	function something_wrong()
	{
		return 200;
	}
}

/* this function used to get admin_user_id from Request Header */
if(!function_exists('get_customer_id'))
{
	function get_customer_id()
	{
		$CI =& get_instance();

        $JWT_string = $CI->input->server('HTTP_AUTH');

        if($JWT_string) {
        	
        	$CI->load->library('JWT');
        	list($name,$JWT_token) = explode(":",$JWT_string);

	        $decodeToken = $CI->jwt->decode($JWT_token, CUSTOMER_SECRET);

	        return $decodeToken->customer_details->customer_id;
        } else {
        	return false;
        }
        
	}


}






