<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );



/* curl Execution using CURL library */
if (! function_exists ( 'curlExecution' )) {
	function curlExecution($endpoint_url,$fields_string=''){
		$CI = &get_instance ();
		//  Calling cURL Library
		$CI->load->library('curl');
		//  Setting URL To Fetch Data From
		$CI->curl->create($endpoint_url);
		$CI->curl->option('POSTFIELDS', $fields_string);
		// For SSL Sites. Check whether the Host Name you are connecting to is valid
		$CI->curl->option('SSL_VERIFYPEER', true);
		//  Ensure that the server is the server you mean to be talking to
		$CI->curl->option('SSL_VERIFYHOST', true);
		//  To Temporarily Store Data Received From Server
		$CI->curl->option('buffersize', 10);
		//  To support Different Browsers
		$CI->curl->option('useragent', 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.8) Gecko/20100722 Firefox/3.6.8 (.NET CLR 3.5.30729)');
		//  To Receive Data Returned From Server
		$CI->curl->option('returntransfer', 1);
		//  To follow The URL Provided For Website
		$CI->curl->option('followlocation', 1);
		//  To Retrieve Server Related Data
		$CI->curl->option('HEADER', true);
		//  To Set Time For Process Timeout
		$CI->curl->option('connecttimeout', 600);
		//  To Execute 'option' Array Into cURL Library & Store Returned Data Into $data
		$data = $CI->curl->execute();
		return $data;
	}

}


if (! function_exists ( 'getCURLresult' )) {
	function getCURLresult($url){

	$_h = curl_init ();
	curl_setopt ( $_h, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $_h, CURLOPT_URL, $url );

	$menu_result = curl_exec ( $_h );
	curl_close ( $_h );
	return json_decode ( $menu_result );
}
}

if (!function_exists('lat_lang_curl_setup')) {
    function lat_lang_curl_setup($service_url)
    {
        $_h = curl_init();
        curl_setopt($_h, CURLOPT_HEADER, 0);
        curl_setopt($_h, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($_h, CURLOPT_HTTPGET, 1);
        curl_setopt($_h, CURLOPT_URL, $service_url);
        curl_setopt($_h, CURLOPT_DNS_USE_GLOBAL_CACHE, false);
        curl_setopt($_h, CURLOPT_DNS_CACHE_TIMEOUT, 2);
        $result = curl_exec($_h);
        curl_close($_h);
        $res_obj = json_decode($result);
        $lat_res = $res_obj->results;
        $lat = $lat_res[0]->LATITUDE;
        $lang = $lat_res[0]->LONGITUDE;
        return array('lat' => $lat, 'lang' => $lang);
    }
}