<?php 
/**************************
 Project Name	: POS
Created on		: 18 Feb, 2016
Last Modified 	: 24 Sep, 2018
Description		: this file contains common setting for admin and client panel..
***************************/

/* get Language label */
if (! function_exists ( 'get_reward_amount' )) {
	function get_reward_amount($label = null) {
		return '1';
	}
}

if (! function_exists ( 'get_s3_link' )) {
	function get_s3_link() {
		$appID = get_company_app_id();
		if($appID=='FCACF7F4-AF83-4EFC-9445-83C270FD1AC2') {
			$folder = 'georges/';
		}
		else if($appID=='3F725DE0-AD9B-48BA-A9AE-958A515D6DDE') {
			$folder = 'srisun/';
		}
		return $folder;
	}
}

/* get Language label */
if (! function_exists ( 'get_label' )) {
	function get_label($label = null) {
		$CI = & get_instance ();
		return ucfirst ( $CI->lang->line ( $label ) );
	}
}

/* get current date */
if (! function_exists ( 'current_date' )) {
	function current_date() {
		return date ( "Y-m-d H:i:s" );
	}
}

/* get ip address */
if (! function_exists ( 'get_ip' )) {
	function get_ip() {
		return $_SERVER ['REMOTE_ADDR'];
	}
}

/* Put Start symbol */
if (! function_exists ( 'get_required' )) {
	function get_required() {
		return '<span class="required_star">*</span>';
	}
}

/* get error tag */
if(!function_exists('get_error_tag'))
{
	function get_error_tag($label=null,$alert_class)
	{
		return '<div class="alert fresh-color '.$alert_class.'" role="alert">'.$label.'</div>';
	}
}
/*On or Off form autocomplet value*/
if (! function_exists ( 'form_autocomplte' )) {
	function form_autocomplte() {
		/* If development mode is enabled */
		return 'on';
	}
}


/* form size  */
if (! function_exists ( 'get_form_size' )) {
	function get_form_size() {
		return 4;
	}
}

if (! function_exists ( 'get_form_editor_size' )) {
	function get_form_editor_size() {
		return 9;
	}
}


/* get date format unique format */
if (! function_exists ( "get_date_formart" )) {
	function get_date_formart($date, $format = "") {
		$CI = & get_instance ();
	//	$date_format=$CI->session->userdata('camp_admin_timeformat');
		$format = ($format != "") ? $format : 'd-m-Y';
		if ($date == "0000:00:00 00:00:00" || $date == "0000:00:00" || $date == NULL) {
			return "N/A";
		} else {
			return date ( $format, strtotime ( $date ) );
		}
	}
}
if (! function_exists ( "get_time_formart" )) {
	function get_time_formart($format = "",$time=null) {
		$format = ($format != "") ? $format : "H:i:s";
		if( empty($time)){
			return "";
		}else{
			return date ( $format, strtotime ( $time ) );
		}
		
	}
}

/* Function used to encode value */
if (! function_exists ( 'encode_value' )) {
	function encode_value($value = '') {
		if ($value != '') {
			return str_replace ( '=', '', base64_encode ( $value ) );
		}
	}
}
/* Function used to decode for encoded value */
if (! function_exists ( 'decode_value' )) {
	function decode_value($value = '') {
		if ($value != '') {
			return base64_decode ( $value );
		}
	}
}

/* Get user key */
if(!function_exists('get_random_key'))
{
	function get_random_key( $length = 20, $table=null, $field_name=null, $value=null, $type='alnum')
	{
		$CI =& get_instance();
		$CI->load->helper('string');

		$randomkey = ($value !="" ? $value : random_string($type,$length));
		$result  = $CI->Mydb->get_record(array($field_name),$table,array($field_name =>trim($randomkey)));
		
		if (!empty($result)) {
		   //	$randomkey = random_string($type,$length);
			return get_random_key( $length, $table, $field_name , "" , $type );
		} else {
			return $randomkey;
		}

	}
}

/* this is used to Inventory enable or not */
if(!function_exists('inventory_status'))
{
	function inventory_status()
	{
		$CI = & get_instance();$status="";	
		$where = array('client_app_id'=>get_company_app_id());
		$outlet_result = $CI->Mydb->get_record( 'client_enable_inventory','pos_clients', $where );
		if (! empty ( $outlet_result )) {
			$status = $outlet_result['client_enable_inventory'];
		}
		return $status;
	}
}


/* Check  GUID exists  */
if(!function_exists('get_guid'))
{
	function get_guid( $table=null, $field_name=null,$where = array() )
	{
		$CI =& get_instance();
		$guid = GUID ();
		$where_arary = array_merge(array($field_name =>trim($guid)),$where);
		$result  = $CI->Mydb->get_record(array($field_name),$table,$where_arary);

		if (!empty($result)) {
			return get_guid(  $table, $field_name );
		} else {
			return $guid;
		}

	}
}

/* chek ajax request .. skip to direct access... */
if (! function_exists ( 'check_ajax_request' )) {
	function check_ajax_request() {
		$CI = & get_instance ();
		if ((! $CI->input->is_ajax_request ())) {	
			redirect ( admin_url () );
			return false;
		}
	}
}

/* cretae bcrypt password... */
if (! function_exists ( 'do_bcrypt' )) {
	function do_bcrypt($password = null) {
		$CI = &get_instance ();
		$CI->load->library ( 'bcrypt' );
		return $CI->bcrypt->hash_password ( $password );
	}
}

/* Compare bcrypt password... */
if (! function_exists ( 'check_hash' )) {
	function check_hash($password = null, $stored_hash=null) {
		$CI = &get_instance ();
		$CI->load->library ( 'bcrypt' );
		if ($CI->bcrypt->check_password ( $password, $stored_hash )) {
			return 'Yes';
			// Password does match stored password.
		} else {
			return 'No';
			// Password does not match stored password.
		}
	}
}

/*  function used to get session values */
if (! function_exists ( 'get_session_value' )) {
	function get_session_value($sess_name) {
		$CI = & get_instance ();
		return  $CI->session->userdata($sess_name);
	}
}

/*  this function used to removed  unwanted chars  */
if ( ! function_exists('post_value'))
{
	function post_value($post_data=null,$xss_flag=null)
	{    $CI =& get_instance();

		if ($CI->input->post($post_data)) {

			if($xss_flag == 'false') {

				$data = addslashes(trim($CI->input->post($post_data,false) ?? ''));

			} else {
				$data = addslashes(trim($CI->input->post($post_data) ?? ''));
			}
		} else {

			$data = addslashes(trim($CI->input->get($post_data) ?? ''));

		}

		return $data;

	}
}

/* this function used to generate generate GUID */
function GUID()
{
	if (function_exists('com_create_guid') === true)
	{
		return trim(com_create_guid(), '{}');
	}

	return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}

/* this function used provide clean putput value...*/
if (! function_exists ( 'output_value' )) {
	function output_value($value = null) {
		return ($value == '') ? "N/A" : ucfirst(stripslashes ( $value ));
	}
}





/* this method used to set Session URL */
if (! function_exists ( 'set_sessionurl' )) {
	function set_sessionurl($data) {
		$CI = & get_instance ();
		$protocol = 'http';
		$re = $protocol . '://' . $_SERVER ['HTTP_HOST'] . $_SERVER ['REQUEST_URI'];
		$CI->session->set_userdata ( $data, $re );
	}
}

/*  $this method used to load pagination config..  */
if ( ! function_exists('pagination_config'))
{
	function pagination_config($uri_string,$total_rows,$limit,$uri_segment,$num_links=2)
	{
		$CI = & get_instance ();
		$CI->load->library('pagination');
		$config = array();
		$config['full_tag_open'] = '<nav><ul class="pagination pagination-sm">';
		$config['full_tag_close'] = '</ul></nav>';
		$config['first_tag_open'] = $config['last_tag_open']   = $config['next_tag_open']  = $config['prev_tag_open'] = 	$config['num_tag_open'] =  '<li>';
		$config['first_tag_close'] = $config['last_tag_close'] = $config['next_tag_close']  = $config['prev_tag_close'] =   $config['num_tag_close'] =  '</li>';
		$config['next_link'] = '&gt;';
		$config['prev_link'] = '&lt;';
		$config['cur_tag_open']  = '<li class="active"> <a>';
		$config['cur_tag_close'] = "</li> </a>";
		$config['num_links'] = $num_links; 
		$config['base_url'] = $uri_string;
		$config['uri_segment'] = $uri_segment;
		$config['total_rows'] = $total_rows;
		$config['per_page'] = $limit;
		return $config;
	}
}

/* this method used to show records count */
if ( ! function_exists('show_record_info'))
{
	function show_record_info($total_rows,$start,$end)
	{
		if(($start+$end) > $total_rows) {
			$end = $total_rows;
		} else {
			$end = $start+$end;
		}

		return ((int)$total_rows== 0 ? " ": 'Showing <b>'.($start+1).'</b> to<b> '.$end.'</b> of <b> '.$total_rows.' </b>entries');
	}
}	

/* this method used to get loading image */
if(!function_exists('loading_image'))
{
	function loading_image($class=null)
	{
		return  '<img src="'.load_lib("theme/images/loading_icon_default.gif").'" alt="loading.."  class="'.$class.'"/>';
	}
}

/* this method used to get loading image */
if(!function_exists('show_image'))
{
	function show_image($foldername=null,$imagename=null,$additional=null,$s3_link=null)
	{
		$filepath=$foldername;
		if($filepath !='')
		{
			$filepath.="/";
		}
		$filepath.=$imagename;
		if($filepath !='')
		{
			if($s3_link==1) {
				return '<img src="'.$foldername.$imagename.'" '.$additional.'>';
			}
			else {
				return '<img src="'.media_url().get_company_folder().'/'.$filepath.'" '.$additional.'>';
			}
			
		}
		else
		{
			return "N/A";
		}
	}
}


if (! function_exists ( 'get_status_dropdown_custom' )) {
	function get_status_dropdown_custom($selected = null, $addStatus=array(),$extra=null) 
	{

		$status	=	array (
			' ' => get_label('select_status'),
			'A' => 'Active',
			'I' => 'Inactive',
			'low' => 'Low Stock Quantity',
		);
		if(!empty($addStatus)){
			$status	=	$status + $addStatus;
		}
		
		$extra = ($extra == "")?  'class="" id="status"' : $extra;
		return form_dropdown ( 'status', $status, $selected, $extra );
	}
}

/* Get Admin Status dropdown */
if (! function_exists ( 'get_status_dropdown' )) {
	function get_status_dropdown($selected = null, $addStatus=array(),$extra=null) 
	{

		$status	=	array (
			' ' => get_label('select_status'),
			'A' => 'Active',
			'I' => 'Inactive',
		);
		if(!empty($addStatus)){
			$status	=	$status + $addStatus;
		}
		
		$extra = ($extra == "")?  'class="" id="status"' : $extra;
		return form_dropdown ( 'status', $status, $selected, $extra );
	}
}


/* Get Admin Membership dropdown */
if (! function_exists ( 'get_membership_dropdown' )) {
	function get_membership_dropdown($selected = null, $addStatus=array(),$extra=null,$app_id=null) 
	{
		$CI = & get_instance ();

		if($app_id == georges_app_id || $app_id == nelsonbar_app_id) {

			$membership	=	array (
				' ' => get_label('select_membership'),
				'Normal' => 'Non-Kaki',
				'Kakis' => 'Kakis',
			);

		} else {
			
			$membership	=	array (
				' ' => get_label('select_membership'),
				'Normal' => 'Non Member'
			);
			
			$where = array (
				'membership_app_id' => $app_id,
				'membership_status'=>'A'
			);

			$join [0] ['select'] = 'name';
			$join [0] ['table'] = 'membership_list';
			$join [0] ['condition'] = 'membership_list_assigned.membership_id = membership_list.id';
			$join [0] ['type'] = 'INNER';

			$select_array = array ('membership_id ','membership_app_id','membership_display_name','membership_min_spend');

			$membership_assigned_res = $CI->Mydb->get_all_records ( $select_array, 'membership_list_assigned',$where,  '' ,'',array('membership_min_spend'=> 'DESC'),'','',$join);

			foreach($membership_assigned_res as $assigned_obj) {

				$membership_display_name = $assigned_obj['membership_display_name'];
				
				$membership[$membership_display_name] = $membership_display_name;

			}


		}
		
		
		if(!empty($addStatus)){
			$membership	=	$membership + $addStatus;
		}
		
		$extra = ($extra == "")?  'class="" id="status"' : $extra;
		return form_dropdown ( 'membership', $membership, $selected, $extra );
	}
}


/* Get Admin customer type dropdown */
if (! function_exists ( 'get_customer_type_dropdown' )) {
	function get_customer_type_dropdown($selected = null, $addStatus=array(),$extra=null) 
	{

		$customer_type	=	array (
			' ' => get_label('select_customer_type'),
			'Normal' => 'Normal',
			'Staff' => 'Staff',
			'Corporate' => 'Corporate'
		);
		if(!empty($addStatus)){
			$customer_type	=	$customer_type + $addStatus;
		}
		
		$extra = ($extra == "")?  'class="" id="status"' : $extra;
		return form_dropdown ( 'customer_type', $customer_type, $selected, $extra );
	}
}


if (! function_exists ( 'get_unsubcriber_dropdown' )) {
	function get_unsubcriber_dropdown($selected = null, $addStatus=array(),$extra=null) 
	{

		$status	=	array (
			' ' => get_label('select_status'),
			'A' => 'Subscribed',
			'I' => 'Unsubscribed',
		);
		if(!empty($addStatus))
		{
			$status	=	$status + $addStatus;
		}		
		$extra = ($extra == "")?  'class="" id="status"' : $extra;
		return form_dropdown ( 'unsubcriber_status', $status, $selected, $extra );
	}
}
if (! function_exists ( 'get_unsubcriber_dropdown1' )) {
	function get_unsubcriber_dropdown1($selected = null, $addStatus=array(),$extra=null) 
	{

		$status	=	array (
			' ' => get_label('select_status'),
			'I' => 'Unsubscribed',
			'A' => 'Subscribed',

		);
		if(!empty($addStatus)){
			$status	=	$status + $addStatus;
		}
		
		$extra = ($extra == "")?  'class="" id="status"' : $extra;
		return form_dropdown ( 'unsubcriber_status', $status, $selected, $extra );
	}
}
/* this method used to create client logo check user folder nameexists**/
if (!function_exists('create_folder')) {
	function create_folder($folder_name) {
		$src = FCPATH.'media/default/*';
		$dst = FCPATH.'media/'.$folder_name.'/';
		$command = exec( "cp -r $src $dst" );

	}
}

/* Make SEO friendly url */
if (! function_exists ( 'make_slug' )) {
	function make_slug($title, $table_name, $field_name, $chk_where = null) {
		$CI = & get_instance ();
		$page_uri = '';
		$code_entities_match = array (
			' ',
			'&quot;',
			'!',
			'@',
			'#',
			'$',
			'%',
			'^',
			'&',
			'*',
			'(',
			')',
			'+',
			'{',
			'}',
			'|',
			':',
			'"',
			'<',
			'>',
			'?',
			'[',
			']',
			'',
			';',
			"'",
			',',
			'.',
			'_',
			'/',
			'~',
			'`',
			'=',
			'---',
			'--',
			"'",
			'–'
		);

		$code_entities_replace = array (
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-'
		);

		$text = str_replace ( $code_entities_match, $code_entities_replace, $title );


		$t = htmlentities ( $text, ENT_QUOTES, 'UTF-8' );
		$page_urii = trim ( strtolower ( $t ), "-" );
		$page_uri_where = array (
			$field_name => $page_urii
		);

		$where = (! empty ( $chk_where )) ? array_merge ( $page_uri_where, $chk_where ) : $page_uri_where;

		$result = $CI->Mydb->get_record ( array (
			$field_name
		), $table_name, $where );
		$CI->load->helper('string');
		//$page_uri = (!empty($result) ) ? $result [$field_name] . "-" . random_string ( 'alnum', 50 ) : $page_urii;
        //echo $CI->db->last_query();
		//echo '<br>';
		//print_r($result);
		//exit;
		//return strtolower ( $page_uri );




		if (!empty($result)) {


			$appID = '';
			
			if(!empty($chk_where)){

				$appID = (!empty($chk_where['outlet_unquie_id']))?$chk_where['outlet_unquie_id']:'';

			}

			if($appID == '59145A47-4517-41B4-9D92-F6BA15DB571A') {
				
				return $page_urii;

			}else{

				$re_page = $result [$field_name] . "-" . random_string ( 'alnum', 25 );

				return make_slug($re_page, $table_name, $field_name, $chk_where  );
				
			}


		} else {
			return $page_urii;
		}
	}
}

/* this method used to  get sequence order */
if(!function_exists('get_sequence'))
{
	function get_sequence($select,$table,$where)
	{	$CI = & get_instance ();
		$record = $CI->Mydb->get_record($select,$table,$where,array($select => 'DESC'));
		return (!empty($record)? (int)$record[$select] + 1 : 1 );
	}
}


/* this method used to add sort by option */
if (! function_exists ( 'add_sort_by' )) {
	function add_sort_by($filed_name, $module) {
		$CI = & get_instance ();
		
		if ( get_session_value ( $module . "_order_by_field" ) !="" && get_session_value ( $module . "_order_by_field" ) == $filed_name && get_session_value ( $module . "_order_by_value" ) != "") {
			$icon  = (get_session_value ( $module . "_order_by_value" ) == "ASC")? 'desc' : 'asc';
			return '&nbsp;<a  data="' . $filed_name . '" class="sort_'.$icon.'"  title=" ' . get_label ( 'order_by_'.$icon ) . ' "><i class="fa fa-sort-alpha-'.$icon.' t sort_icon"></i></a>';
			
		} else {
			
			return '&nbsp;<a  data="' . $filed_name . '" class="sort_asc"  title=" ' . get_label ( 'order_by_asc' ) . ' "><i class="fa fa-sort sort_icon"></i></a>';
		}

	}
}

/* Get Country list    */
if(!function_exists('get_countries'))
{
	function get_countries($where='',$selected='',$extra='')
	{
		$CI=& get_instance();
		$where_array=($where=='')? array('country_id !='=>'') :  $where ;
		$records=$CI->Mydb->get_all_records('country_id,country_name,timezone','countries',$where_array,'','',array('country_name'=>"ASC"));
		$data=array(''=>get_label('select_country'));
		if(!empty($records))
		{
			foreach($records as $value)
			{
				$data[$value['country_name']."-".$value['timezone']] = stripslashes($value['country_name']);
			}
		}
		$extra=($extra!='')?  $extra : 'class="form-control" id="client_country" ' ;

		return  form_dropdown('client_country',$data,$selected,$extra);
	}
}

/* Get Country list new   */
if(!function_exists('get_countries_one'))
{
	function get_countries_one($where='',$selected='',$extra='')
	{
		$CI=& get_instance();
		$where_array=($where=='')? array('country_id !='=>'') :  $where ;
		$records=$CI->Mydb->get_all_records('country_id,country_name,timezone','countries',$where_array,'','',array('country_name'=>"ASC"));
		$data=array(''=>get_label('select_country'));
		if(!empty($records))
		{
			foreach($records as $value)
			{
				$data[$value['country_id']."-".$value['timezone']] = stripslashes($value['country_name']);
			}
		}
		$extra=($extra!='')?  $extra : 'class="form-control" id="client_country" ' ;

		return  form_dropdown('client_country',$data,$selected,$extra);
	}
}


/* Get Country list    */
if(!function_exists('get_country_name'))
{
	function get_country_name($country_id=null,$all=null)
	{
		$CI=& get_instance();
		$where="";
		$where_array=($where=='')? array('country_id ='=>$country_id) :  $where ;
		$records=$CI->Mydb->get_all_records('country_id,country_name,timezone,country_flag','countries',$where_array,'','',array('country_name'=>"ASC"));

		if($all != null) {
			return $records;
		} else {
			return  $records[0]['country_name'];
		}
	}
}

/* Get Currency name */
if(!function_exists('get_currency_name'))
{
	function get_currency_name($symbol,$all=null)
	{
		$CI = & get_instance();
		$where="";
		$where_array = ($where=='')? array('currency_symbol ='=>$symbol) :  $where ;
		$records = $CI->Mydb->get_all_records('currency_country,currency_name,currency_symbol','currency',$where_array,'','',array('currency_country'=>"ASC"));

		if($all != null) {
			return $records;
		} else {
			return  $records[0]['currency_name'];
		}
	}
}

/* Get Country list    */
if(!function_exists('get_countries_multiple'))
{
	function get_countries_multiple($where='',$selected='',$extra='',$multiple=null)
	{
		$CI=& get_instance();
		$where_array=($where=='')? array('country_id !='=>'') :  $where ;
		$records=$CI->Mydb->get_all_records('country_id,country_name,timezone','countries',$where_array,'','',array('country_name'=>"ASC"));
		
		$data = ($multiple =="" ) ? array(''=>get_label('select_country')) : array();	
		if(!empty($records))
		{
			foreach($records as $value)
			{
				$data[$value['country_id']] = stripslashes($value['country_name']);
			}
		}
		$extra=($extra!='')?  $extra : 'class="form-control" id="client_shipping_country[]" ' ;

		return  form_dropdown('client_shipping_country[]',$data,$selected,$extra.$multiple);
	}
}

/* Get Country list - customers   */
if(!function_exists('get_all_countries'))
{
	function get_all_countries($where='',$selected='',$extra='')
	{

		$CI=& get_instance();
		$where_array=($where=='')? array('country_id !='=>'') :  $where ;
		$records=$CI->Mydb->get_all_records('country_id,country_name,timezone','countries',$where_array,'','',array('country_name'=>"ASC"));
		$data=array(''=>get_label('select_country'));
		if(!empty($records))
		{
			foreach($records as $value)
			{
				$data[$value['country_id']] = stripslashes($value['country_name']);
			}
		}
		$extra=($extra!='')?  $extra : 'class="form-control" id="customer_country" ' ;
		

		return  form_dropdown('customer_country',$data,$selected,$extra);
	}
}
/* Get Currency list    */
if(!function_exists('get_currency'))
{
	function get_currency($where='',$selected='',$extra='')
	{
		$CI=& get_instance();
		$where_array=($where=='')? array('currency_symbol !='=>'') :  $where ;
		$records=$CI->Mydb->get_all_records('currency_id,currency_symbol,currency_name','currency',$where_array,'','',array('currency_name'=>"ASC"));
		$data=array(''=>get_label('select_currency'));
		if(!empty($records))
		{
			foreach($records as $value)
			{
				$data[$value['currency_symbol']] = stripslashes($value['currency_name']);
			}
		}
		$extra=($extra!='')?  $extra : 'class="form-control" id="client_currency" ' ;

		return  form_dropdown('client_currency',$data,$selected,$extra);
	}
}
/* Get Currency list    */
if(!function_exists('get_currency1'))
{
	function get_currency1($where='',$selected='',$extra='',$name='')
	{
		$CI=& get_instance();
		$where_array=($where=='')? array('currency_symbol !='=>'') :  $where ;
		$records=$CI->Mydb->get_all_records('currency_id,currency_symbol,currency_name','currency',$where_array,'','',array('currency_name'=>"ASC"));
		$data=array(''=>get_label('select_currency'));
		if(!empty($records))
		{
			foreach($records as $value)
			{
				$data[$value['currency_symbol']] = stripslashes($value['currency_name']);
			}
		}
		$extra=($extra!='')?  $extra : 'class="form-control" id="client_currency" ' ;

		return  form_dropdown($name,$data,$selected,$extra);
	}
}


if (! function_exists ( 'currency_format' )) {
	
	function currency_format($value = null) {
		
		if($value!='')
		{
			$CI=& get_instance();
			$where_array=array('currency_symbol ='=>$value)  ;
			$records=$CI->Mydb->get_record('currency_name','currency',$where_array);

			if(!empty($records))
			{
				return stripslashes($records['currency_name']."(".$value.")");
			}
			else
			{
				return stripslashes($value);
			}


		}
		else
		{
			return "N/A";
		}
	}

}

/* Get Language list    */
if(!function_exists('get_language'))
{
	function get_language($where='',$selected='',$extra='')
	{
		$CI=& get_instance();
		$where_array=($where=='')? array('language_id !='=>'') :  $where ;
		$records=$CI->Mydb->get_all_records('language_id,language_name,language_code','languages',$where_array,'','',array('language_name'=>"ASC"));
		$data=array(''=>get_label('select_language'));
		if(!empty($records))
		{
			foreach($records as $value)
			{
				$data[$value['language_code']] = stripslashes($value['language_name']);
			}
		}
		$extra=($extra!='')?  $extra : 'class="form-control" id="client_language" ' ;

		return  form_dropdown('client_language',$data,$selected,$extra);
	}
}


/* Get Language list    */
if(!function_exists('get_dateformat'))
{
	function get_dateformat($selected,$extra)
	{
		$records=array('F j, Y'=>date('F j, Y'),'Y-m-d'=>date('Y-m-d'),'m/d/Y'=>date('m/d/Y'),'m/d/y'=>date('m/d/y'),'d/m/Y'=>date('d/m/Y'),'d-m-Y'=>date('d-m-Y'));
		$data=array(''=>get_label('select_date_format'));
		if(!empty($records))
		{
			foreach($records as $key=>$value)
			{
				$data[$key] = stripslashes($value);
			}
		}
		$extra=($extra!='')?  $extra : 'class="form-control" id="client_date_format" ' ;

		return  form_dropdown('client_date_format',$data,$selected,$extra);
	}
}

/* Get Language list    */
if(!function_exists('get_timeformat'))
{
	function get_timeformat($selected,$extra)
	{
		$records=array('g:i a'=>date('g:i a'),'g:i A'=>date('g:i A'),'H:i'=>date('H:i'));
		$data=array(''=>get_label('select_time_format'));
		if(!empty($records))
		{
			foreach($records as $key=>$value)
			{
				$data[$key] = stripslashes($value);
			}
		}
		$extra=($extra!='')?  $extra : 'class="form-control" id="client_time_format" ' ;

		return  form_dropdown('client_time_format',$data,$selected,$extra);
	}
}

/*  this function used to get uri segment value    */
if(!function_exists('uri_select'))
{
	function uri_select()
	{ 
		$CI=& get_instance();
		return 	decode_value($CI->uri->segment(4)) ;
	}
}	

/* Add tooltip */
if(!function_exists('add_tooltip'))
{
	function add_tooltip($title=null)
	{
		return ' <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="'.get_label($title."_ttip").'" ></i>';
	}
}


/* this method used to output integer vcalue  */
if(!function_exists('output_integer'))
{
	function output_integer($value=null)
	{
		return ($value == 0)? 0 : $value;
	}
}

/* this method used to output integer vcalue  */
if(!function_exists('output_date'))
{
	function output_date($date=null)
	{
		return ($date !="1970-01-01")? $date : "";
	}
}

/* this function used show enabled or disbled status */
if(!function_exists('output_enbled'))
{
	function output_enbled($vlaue=null)
	{
		return  ($vlaue ==1)? "Yes" : "No";
	}
}

/* this function used show unitnumebr  */
if(!function_exists('output_unitno'))
{
	function output_unitno($unitno1=null,$unitno2 =null)
	{
		return   ($unitno1 !="" && $unitno2!="" )? "#".$unitno1."-".$unitno2 : "N/A";
	}
}

/* this function used to show sttaus  */
if(!function_exists('output_status'))
{
	function output_status($status=null)
	{
		return   ($status == "A")? "Active" : "Inactive";
	}
}

/* this function used to  show name  */
if(!function_exists('output_name'))
{
	function output_name($fname=null,$lname=null)
	{
		return ($fname !="" && $lname !="" ) ? ucwords(stripslashes($fname." ".$lname)) :  ( $fname !="" ? ucwords(stripslashes($fname)) : "N/A"); 
	}
}

/* this method used to get company admin  records per page value */
if (! function_exists ( 'company_records_perpage' )) {
	function company_records_perpage() {
		$CI = & get_instance ();
		return $CI->session->userdata('camp_admin_records_perpage');
	}
}

/* this function used to show footer copyright content */
if (! function_exists ( 'footer_content' )) {
	function footer_content() {
		echo "&copy; ".date('Y')." Jankosoft Pvt Ltd";
	}
}


if (! function_exists ( 'get_company_logo' )) {
	function get_company_logo($image_name='',$company_folder='') {
		$CI = & get_instance ();
		$folder=$CI->lang->line('compnay_image_folder_name');
		$src= load_lib()."theme/images/ninjaOS.svg";
		
		if( ($image_name !='') && ($company_folder!='') )
		{
			if(file_exists(FCPATH."media/".$company_folder."/".$folder."/".$image_name))
			{
				$src=media_url().$company_folder."/".$folder."/".$image_name;
			}
		}
		
		return $src;
	}
}
if (! function_exists ( 'get_group_checkbox' )) {
	
	function get_group_checkbox() {
		$groupvar='';
		$CI = & get_instance ();
		$sub_group=$CI->Mydb->get_all_records('sgroup_id,sgroup_groupname','pos_newsletter_subscriber_group',array('sgroup_status'=>'A'),null, null, null,'','','','' );
	   //echo $CI->db->last_query();
	  //exit;
		if(!empty($sub_group))
		{

			foreach($sub_group as $avl) 
			{			  	
				$groupvar='<div class="input_box">';
				$groupvar.='<div class="checkbox3 checkbox-inline checkbox-check checkbox-light">';
				$groupvar.='form_checkbox("subcriber_group_id","yes","","id="groups"")';      
				$groupvar.='<label for="groups" class="chk_box_label"><b></b></label>';
				$groupvar.='</div>';
				$groupvar.='</div>';

			}
		}

		return $groupvar;
	}
}

/* get email template id
if (! function_exists ( 'get_emailtemplate' )) {

	function get_emailtemplate($company_app_id, $label) {

		$CI = & get_instance ();
		$email_ids=array();	
		$template_id = '';

		$arr = array (

				'4F4C345F-AA24-41B9-BCB4-669CD25F6622' => array (
						'user-registration' => 14,
						'user-forgotpassword' => 13,
						'order-confirmation-delivery' => 12,
						'order-confirmation-pickup' => 33,
						'order-notification' => 11,
						'facebook-user-registration' => 27, 
						'promotion-code' => 28, 
						'order_attachment_email'=>32,
						'order_cancel_template'=>10,
						'order_cancel_admin_template'=>9,
						'contactus-inquiry' => 37 
				),
				'B23A0B49-C200-4962-961B-3E54A25125B4' => array (
						'user-registration' => 3,
						'user-forgotpassword' => 4,
						'order-confirmation-delivery' => 5,
						'order-notification' => 6,
						'facebook-user-registration' => 17, 
						'promotion-code' => 19, 
						'order_attachment_email'=>25,
						'order-confirmation-pickup' => 26,
						'contactus-inquiry' => 36,
						'order_cancel_template'=>7,
						'order_cancel_admin_template'=>8
				), 
				'97440967-AC09-422A-B5C2-BBA4D8C3D989' => array (
						'user-registration' => 34,
						'user-forgotpassword' => 35,
						'promotion-code' => 38, 
						'order-confirmation-delivery' => 39,
						'order-notification' => 40,
						'facebook-user-registration' => 45, 						 
						'order_attachment_email'=>44,
						'order-confirmation-pickup' => 43,						
						'order_cancel_template'=>41,
						'order_cancel_admin_template'=>42,	
						'contactus-inquiry'=>46,
						'catering-request'=>50,
						'catering-payment'=>51,	
						'catering-stripe-payment'=>60,							
				) 
				,
				'6C9AEE37-BC05-4FB8-A351-D51458B83F26' => array (
						'user-registration' => 56,
						'user-forgotpassword' => 57,
						'order-confirmation-delivery' => 58,
						'order-confirmation-pickup' => 58,
						'order-confirmation-dinein' => 58,
						'order_cancel_template'=>59,
				)
		);

		if (isset ( $arr [$company_app_id] [$label] ))
		{
			$template_id = $arr [$company_app_id] [$label];		
			return $template_id;
		}
		else 
		{					
			$email_ids=$arr [$company_app_id];			
			return $email_ids;
		}
	}
} */ 
/* this is used to get the cart details from the api */
if(!function_exists('cart_modifiers'))
{
	function cart_modifiers($cart_id, $cart_item_id, $type, $field = NULL)
	{
		$CI = & get_instance ();
		$result = array ();
		$modifiers = $CI->Mydb->get_all_records ( 'cart_modifier_id,cart_modifier_name', 'cart_modifiers', array (
			'cart_modifier_type' => $type,
			'cart_modifier_parent' => '',
			'cart_modifier_cart_id' => $cart_id,
			'cart_modifier_cart_item_id' => $cart_item_id,
			'cart_modifier_menu_component_primary_key' => $field 
		) );
		
		if (! empty ( $modifiers )) {
			
			foreach ( $modifiers as $modvalues ) {
				/* get modifier values */
				$modifier_values = $CI->Mydb->get_all_records ( array (
					'cart_modifier_id',
					'cart_modifier_name',
					'cart_modifier_price',
					'cart_modifier_qty' 
				), 'cart_modifiers', array (
					'cart_modifier_type' => $type,
					'cart_modifier_parent' => $modvalues ['cart_modifier_id'],
					'cart_modifier_cart_id' => $cart_id,
					'cart_modifier_cart_item_id' => $cart_item_id,
					'cart_modifier_menu_component_primary_key' => $field 
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
/* this is used to get the cart details from the api */
if(!function_exists('view_cart_details'))
{
	function view_cart_details($app_id=null,$cart_id,$returndata = "")
	{
		$CI = & get_instance ();

		$user_cart_details = array();		

		$where = array ('cart_id' => $cart_id);
		$cart_detail = $CI->Mydb->get_all_records ( 'cart_details.*','cart_details', $where);

		if (! empty ( $cart_detail )) 
		{
			foreach($cart_detail as $cart_details)
			{
				$select = array (
					'cart_item_id',
					'cart_item_product_id',
					'cart_item_product_name',
					'cart_item_product_sku',
					'cart_item_product_image',
					'cart_item_qty',
					'cart_item_unit_price',
					'cart_item_total_price',
					'cart_item_type',					
					'cart_item_added_condiment' 
				);

				$all_items = $CI->Mydb->get_all_records ( $select, 'cart_items', array (
					'cart_item_cart_id' => $cart_details ['cart_id'] 
				) );

				$fianl = array ();
				if (! empty ( $all_items )) 
				{
					foreach ( $all_items as $items ) 
					{
						$modifier_array = array ();
						$modifier_array = cart_modifiers ( $cart_details ['cart_id'], $items ['cart_item_id'], 'Modifier' );
						$items ['modifiers'] = $modifier_array;
						$fianl [] = $items;
					}
					$response ['cart_details'] = $cart_details;
					$response ['cart_items'] = $fianl;	            				
					
				}
			}

			$user_cart_details=$response;

		}

		return $user_cart_details;
		
	}
}
/* this is used to get the cart details from the api */
if(!function_exists('groupinsert'))
{
	function groupinsert($id = "",$groupidss="")
	{
		foreach($groupidss as $keys=>$grpvalid)
		{
			$insert_group_array = array (
				'subscriberid' => $id,
				'groupid' => $grpvalid					   						
			);	
			$insert_id1 = $this->Mydb->insert ( "news_groupingscbscriber", $insert_group_array );

		}	
		return $insert_id1;

	}
}
/* Get Review Status dropdown */
if (! function_exists ( 'get_review_status_dropdown' )) {
	function get_review_status_dropdown($selected = null, $addStatus=array(),$extra=null) {

		$status	=	array (
			' ' => get_label('select_status'),
			'1' => 'Approve',
			'2' => 'Reject',
		);
		if(!empty($addStatus)){
			$status	=	$status + $addStatus;
		}
		
		$extra = ($extra == "")?  'class="" id="status"' : $extra;
		return form_dropdown ( 'status', $status, $selected, $extra );
	}
}
/* this is used to Catering enable or not */
if(!function_exists('catering_status'))
{
	function catering_status()
	{
		$CI = & get_instance();
		$status="";			
		$where_in=array('company_id'=>get_company_id(),'company_app_id'=>get_company_app_id(),
			'company_availability_id'=>get_availability_catering_id(),'company_availability_status'=>'A' 
		);
		$availability_catering_result = $CI->Mydb->get_num_rows( '*','company_availability', $where_in );
		
		
		if (! empty ( $availability_catering_result )) 
		{
			
			$status = $availability_catering_result;
		}
		return $status;
	}
}
/* this is used to Catering enable or not */
if(!function_exists('reservation_status'))
{
	function reservation_status()
	{
		$CI = & get_instance();
		$status="";			
		$where_in=array('company_id'=>get_company_id(),'company_app_id'=>get_company_app_id(),
			'company_availability_id'=>get_availability_reservation_id(),'company_availability_status'=>'A' 
		);
		$availability_catering_result = $CI->Mydb->get_num_rows( '*','company_availability', $where_in );
		
		
		if (! empty ( $availability_catering_result )) 
		{
			
			$status = $availability_catering_result;
		}
		return $status;
	}
}
/* Address Format */
if(!function_exists('show_address'))
{
	function show_address($address,$unitcode,$post_code){
		
		$address_val = (($address!='')? ucfirst(stripslashes($address)):"").((trim($unitcode) !='' && trim($unitcode) !='-')?", #".$unitcode."<br />":"");
		return $address_val; 
	}
}


if(!function_exists('get_lng_lat')){
	function get_lng_lat($address = ''){
		$url = "http://maps.google.com/maps/api/geocode/json?address=".urlencode($address)."&sensor=false";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch);
		$response_a = json_decode($response);

		$lat = $response_a->results[0]->geometry->location->lat;
		$long = $response_a->results[0]->geometry->location->lng;
		return $lat.','.$long;
	}
}
if(!function_exists('getAddress')){
	function getAddress($latitude,$longitude){
		if(!empty($latitude) && !empty($longitude)){
			//Send request and receive json data by address
			$geocodeFromLatLong = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($latitude).','.trim($longitude).'&sensor=false'); 
			$output = json_decode($geocodeFromLatLong);
			$status = $output->status;
			//Get address from json data
			$address = ($status=="OK")?$output->results[1]->formatted_address:'';
			//Return address of the given latitude and longitude
			if(!empty($address)){
				return $address;
			}else{
				return false;
			}
		}else{
			return false;   
		}
	}
}
/* get productname */
if(!function_exists('product_name'))
{
	function product_name($product_name="",$product_alias="")
	{
		if($product_alias!="")
		{
			$p_name=$product_alias;
		}
		else
		{
			$p_name=$product_name;
		}     
		return $p_name;
	}
}

/* get productname */
if(!function_exists('get_favi_icon'))
{
	function get_favi_icon()
	{
		$icon = '<link rel="icon" href='.load_lib().'theme/images/favicon-32x32.png type="image/png" sizes="32x32">';   
		return $icon;
	}
}

/* get outlet name */
if(!function_exists('get_outlet_name'))
{
	function get_outlet_name($outlet_id='')
	{
		$CI = & get_instance();	
		$where = array('outlet_id'=> $outlet_id);
		$outlet_result = $CI->Mydb->get_record( 'outlet_name','outlet_management', $where );
		return $outlet_result['outlet_name'];
		
	}
}
/* this is used to Catering enable or not */
if(!function_exists('promocode_advanced_status'))
{
	function promocode_advanced_status()
	{
		$CI = & get_instance();
		$status="";
		$where_in=array('client_app_id'=>get_company_app_id());
		$promocode_advanced_result = $CI->Mydb->get_record( 'client_promo_advanced_enable','clients', $where_in );
		return $promocode_advanced_result['client_promo_advanced_enable'];
	}
}

/*  common notifications  */
if(!function_exists('push_notif_common'))
{
	function push_notif_common($customer_id,$info_arr) {

		$CI = & get_instance ();
		$selectArr = array ('user_android_id','user_ios_id','customer_push_notify');
		$customerDetails = $CI->Mydb->get_all_records ( $selectArr, 'pos_customers', array ('customer_id' => $customer_id ));

		$app_id  = $info_arr['app_id'];
		$msg_txt = $info_arr['msg_txt'];
		$android = $info_arr['android'];
		$ios 	 = $info_arr['ios'];
		$otherMgs = (array_key_exists("otherMsg",$info_arr)) ? $info_arr['otherMsg'] : array();
		$iosApi = (array_key_exists("iosApi",$info_arr)) ? $info_arr['iosApi'] : '';
		
		
		if (!empty($customerDetails) && $customerDetails[0]['customer_push_notify'] == 1) {
			
			$CI->load->library ( 'push' );

			$deviceId = array ($customerDetails [0] ['user_android_id'] );
			$deviceToken = $customerDetails [0] ['user_ios_id'];
			
			if($android == 'yes' && $deviceId != '' && $msg_txt != '' && $app_id != '') {
				$message = array ( "message" => $msg_txt );
				$message = array_merge($message,$otherMgs);
				$CI->push->sendMessage_app ( $deviceId, $message ,$app_id);
			}
			
			if($ios == 'yes' && $deviceToken != '' && $msg_txt != '' && $app_id != '') {
				$message = array ( "alert" => $msg_txt, "sound" => "AlarmRing.aiff" );
				$message = array_merge($message,$otherMgs);
				if($iosApi == 'third-party') {
					$CI->backfourapp->send_to_device($deviceToken,$message,$app_id);
				} else {
					$CI->push->push_message_ios_app ( $deviceToken, $message,'',$app_id );
				}
			}
			
		}
		
	}
}

/*  Activities and notifications  */
if(!function_exists('push_activities'))
{
	function push_activities($customer_id,$from,$info_arr) {

		$CI = & get_instance ();

		$act_content = $msg = '';

		$app_id = $info_arr['app_id'];

		if($app_id == georges_app_id) {

			if($from == 'order_status') {

				$order_status = $info_arr['order_status'];
				$order_no = $info_arr['order_no'];

				$order_text = $info_arr['order_text'];
				
				$act_short_content = 'Your Order is completed';

				if ($order_status == '3') {

					$msg = 'Your order #'.$order_no.' is being '.$order_text.'.';

				} elseif ($order_status == '2') {

					$msg = 'Your order #'.$order_no.' is now '.$order_text.'.';

				} elseif ($order_status == '4') {

					$msg = 'Your order #'.$order_no.' is completed. Enjoy!';

				} elseif ($order_status == '5') {

					$msg = 'Your order #'.$order_no.' has been Cancelled';
					
					$act_short_content = 'Your Order is cancelled';

				} else {}

				$act_for = 'Order';
				$act_redirect = 'Order';
				$act_title = 'Order';

				$act_image = 'notify_order.png';
				$act_ref_id = $info_arr['order_primary_id'];

				
				$act_content = $msg;

			} else if($from == 'Membership') {

				$name = $info_arr['name'];

				$msg = 'Hi '.$name.',Congrats ! Your account is upgraded to a Kakis Member!';

				$act_for = 'Account';
				$act_redirect = 'Account';
				$act_title = 'Kaki Member';

				$act_image = 'notify_member.png';
				$act_ref_id = $customer_id;

				$act_content = $msg;
				$act_short_content = 'Welcome to the Kaki Club';

			} else if($from == 'Birthprmo') {

				$name = stripslashes($info_arr['name']);

				$msg = 'Happy Birthday '.$name.' You have received a birthday voucher from us.';

				$act_for = 'Promotion';
				$act_redirect = 'Promotion';
				$act_title = 'B\'day';

				$act_image = 'notify_b_day.png';
				$act_ref_id = $customer_id;

				$act_content = $msg;
				$act_short_content = 'Happy Birthday '.$name;

			} else if($from == 'Earnpoints') {

				$order_no = $info_arr['order_no'];
				$points = $info_arr['points'];

				$msg = 'You\'ve Earned Dollars ! Congrats you have earned '.$points.' dollars(order #'.$order_no.').';

				$act_for = 'Reward';
				$act_redirect = 'Reward';
				$act_title = 'Reward';

				$act_image = 'notify_b_day.png';
				$act_ref_id = $customer_id;

				$act_content = $msg;
				$act_short_content = 'Dollars';

			} else if($from == 'Mappromo') {

				$prom_name = $info_arr['prom_name'];

				$msg = 'You\'ve Got A Promo ! Enjoy this '.$prom_name.' on your next order!';

				$act_for = 'Promotion';
				$act_redirect = 'Promotion';
				$act_title = 'Promotion';

				$act_image = 'notify_b_day.png';
				$act_ref_id = $customer_id;

				$act_content = $msg;
				$act_short_content = $prom_name;

			} else if($from == 'RewardExpire') {/*Sent notification for points expiry*/

				$points = $info_arr['expiring_points'];
				$expire_days = $info_arr['expire_days'];

				if($points > 1) {
					$points = $points.' Dollars';
				}else {
					$points = $points.' Dollar';
				}
				//'It\'s kind reminder that your '.$points.' will expire in '.$expire_days.' days.
				$msg = 'Your Rewards are about to expire. You have '.$points.' which will expire in '.$expire_days.' days.';

				$act_for = 'RewardExpire';
				$act_redirect = 'Reward';
				$act_title = 'Reward';

				$act_image = 'notify_b_day.png';
				$act_ref_id = $customer_id;

				$act_content = $msg;
				$act_short_content = 'Dollars';

			} else {}

			if($act_content != '') {

				$act_id = $CI->Mydb->insert ( 'pos_customer_activity', array (
					'act_customer_id' => $customer_id,
					'act_for' => $act_for,
					'act_redirect' => $act_redirect,
					'act_title' => $act_title,
					'act_content' => addslashes($act_content),
					'act_short_content'=>$act_short_content,
					'act_image' => $act_image,
					'act_created_on'=>current_date(),
					'act_ref_id'=>$act_ref_id
				) );

				$selectArr = array ('user_android_id','user_ios_id','customer_push_notify');

				$customerDetails = $CI->Mydb->get_all_records ( $selectArr, 'pos_customers', array ('customer_id' => $customer_id ));
				
				//print_r($customerDetails);
				//exit;

				if (! empty ( $customerDetails )) {
					
					$CI->load->library ( 'push' );

					$deviceId = array ($customerDetails [0] ['user_android_id'] );
					$deviceToken = $customerDetails [0] ['user_ios_id'];

					$message = array (
						"message" => $msg ,
						"badge" => push_activities_unread($customer_id),
						"notify_type"=>$act_redirect,
						"notify_id"=>$act_id,
						"notify_customer"=>$customer_id
					);

					/**
					 * **** for android user *****
					 */
					if ($customerDetails [0] ['customer_push_notify'] == '1') {

						if ($customerDetails [0] ['user_android_id'] != '') {

							$status = $CI->push->sendMessage_app ( $deviceId, $message ,$app_id);
						}

						if ($customerDetails [0] ['user_ios_id'] != '') {

							$message = array (
								"alert" => $msg ,
								"badge" => push_activities_unread($customer_id),
								"notify_type"=>$act_redirect,
								"notify_id"=>$act_id,
								"notify_customer"=>$customer_id
							);

							/*$status = $CI->push->push_message_ios_app ( $deviceToken, $message,$countPush,$app_id );
							$countPush ++;*/
							
							/*Send via third party api back4app*/
							$res = $CI->backfourapp->send_to_device($deviceToken,$message,$app_id);

							//print_r($res );
							
						}

					}

				}
			}
			

		} else {
			
			$pushAddIds = unserialize(COMMON_PUSH_APPS);
			
			if(in_array($app_id, $pushAddIds)) {
				
				if($from == 'order_status') {

					$order_status = $info_arr['order_status'];
					$order_no = $info_arr['order_no'];
					$order_text = $info_arr['order_text'];
					
					$act_short_content = '';
					
					if ($order_status == '3') {

						$msg = 'Your order #'.$order_no.' is being processing';
						
						$act_short_content = 'Your Order is processing';

					} elseif ($order_status == '2') {

						$msg = 'Your order #'.$order_no.' is now delivered';
						
						$act_short_content = 'Your Order is delivered';

					} elseif ($order_status == '4') {

						$msg = 'Your order #'.$order_no.' is completed. Enjoy!';
						
						$act_short_content = 'Your Order is completed';

					} elseif ($order_status == '5') {

						$msg = 'Your order #'.$order_no.' has been Cancelled';
						$act_short_content = 'Your Order is cancelled';
					}
					
					$act_for = $act_redirect = $act_title = 'Order';
					
					$act_image = 'notify_order.png';
					$act_ref_id = $info_arr['order_primary_id'];
					
					$act_content = $msg;

				} else if($from == 'Earnpoints') {

					$order_no = $info_arr['order_no'];
					$points = $info_arr['points'];

					$msg = 'You\'ve Earned Dollars ! Congrats you have earned '.$points.' dollars(order #'.$order_no.').';

					$act_for = $act_redirect = $act_title = 'Reward';

					$act_image = 'notify_b_day.png';
					$act_ref_id = $customer_id;

					$act_content = $msg;
					$act_short_content = 'Dollars';

				} else if($from == 'Membership') {

					$name = $info_arr['name'];

					$msg = 'Hi '.$name.',Congrats ! Your account is upgraded to a Kakis Member!';

					$act_for = 'Account';
					$act_redirect = 'Account';
					$act_title = 'Kaki Member';

					$act_image = 'notify_member.png';
					$act_ref_id = $customer_id;

					$act_content = $msg;
					$act_short_content = 'Welcome to the Kaki Club';

				} else {}
				
				if($act_content != '') {
					$act_id = $CI->Mydb->insert ( 'pos_customer_activity', array (
						'act_customer_id' => $customer_id,
						'act_for' => $act_for,
						'act_redirect' => $act_redirect,
						'act_title' => $act_title,
						'act_content' => addslashes($act_content),
						'act_short_content'=>$act_short_content,
						'act_image' => $act_image,
						'act_created_on'=>current_date(),
						'act_ref_id'=>$act_ref_id
					) );
					
					$otherMsg = array (
						"badge" => push_activities_unread($customer_id),
						"notify_type"=>$act_redirect,
						"notify_id"=>$act_id,
						"notify_customer"=>$customer_id
					);
					
					$infoArr = array("app_id"=>$app_id,"msg_txt"=>$msg,"android"=>'yes',"ios"=>'yes',"otherMsg"=>$otherMsg,"iosApi"=>'third-party');
					push_notif_common($customer_id,$infoArr);
				}
				
			}
			
		}

		return $msg;

	}
}

/*  Get Unread notification count */
if(!function_exists('push_activities_unread'))
{
	function push_activities_unread($customer_id) {

		$CI = & get_instance ();
		
		$where = array ('act_customer_id' => $customer_id,'act_read_status'=>'0');
		$res_list = $CI->Mydb->get_record ( 'COUNT(*)', 'customer_activity', $where);
		$notify_count = (int)$res_list['COUNT(*)'];

		return $notify_count;

	}
}

/* get_default_address_format format */
if(!function_exists('get_default_address_format'))
{
	function get_default_address_format($address1=null,$unitno1=null,$unitno2=null,$address2=null,$country="Singapore",$post_code=null){
		$address_line_1 = '';
		if($address1 != "" ) {
			$address_line_1 = ucfirst ( stripslashes ( $address1 ) ) .', '. "<br>" ;
		}
		$address_line_2 = '';
		if ($address2 != "" ) {
			$address_line_2 = ucfirst ( stripslashes ( $address2 ) ) .', '. "<br>" ;
		}
		if($unitno1 != '' && $unitno2 != ''){
			$unit_no = ("#" . $unitno1 . "-" . $unitno2 );
		}else{
			$unit_no = (($unitno1 != "" ? "#" . $unitno1 : ($unitno2 != "" ? "#" . $unitno2 : "")));
		}
		
		if($unit_no != '' && $address_line_1 != ''){
			$unit_no = $unit_no.', ';
		}
		
		if($country != ''){
			
			$country = $country.'  ';
		}
		
		$address_format = $address_line_1.$unit_no.$address_line_2.$country.$post_code;
		
		return $address_format;
	}
}

/* show address format */
if(!function_exists('show_default_address_format'))
{
	function show_default_address_format($unitno1=null,$unitno2=null,$address1=null,$address2=null,$country="Singapore",$post_code=null,$unittower=null){
		
		if($unittower != ""){
			$unit_no = ($unitno1 != "" && $unitno2 != "" && $unittower != "" ? "#" . $unittower . "-". $unitno1 . "-" . $unitno2 ."," : ($unitno1 != "" && $unittower != "" ? "#"  .$unittower . "-". $unitno1."," : ($unitno2 != "" && $unittower != "" ? "#" .$unittower . "-" . $unitno2.","  : "")));
		}else{
			$unit_no = ($unitno1 != "" && $unitno2 != "" ? "#" . $unitno1 . "-" . $unitno2 : ($unitno1 != "" ? "#" . $unitno1 : ($unitno2 != "" ? "#" . $unitno2 : "")));
		}
		if ($address1 != "" && $address2 != "") {
			$address = ucfirst ( stripslashes ( $address1 ) ) . "<br>" . ucfirst ( stripslashes ( $address2 ) );
		}elseif ($address1 != "" ) {
			$address = ucfirst ( stripslashes ( $address1 ) ) . "<br>" ;
		}elseif ($address2 != "" ) {
			$address = ucfirst ( stripslashes ( $address2 ) ) . "<br>" ;
		}

		if($unit_no != ''){
			$unit_no = $unit_no.', ';
		}
		return $address.$unit_no.$country.$post_code;
	}
}
/* show address format */

/* Show price */
if (! function_exists ( 'show_price_format' )) {
	function show_price_format($price) {
		return get_currency_symbol () . number_format ( $price, 2 );
	}
}

if (! function_exists ( 'show_price' )) {
	function show_price($price) {
		return get_currency_symbol () . number_format ( $price, 2 );
	}
}

if(!function_exists('get_currency_symbol'))
{
	function get_currency_symbol($val=null)
	{
		return get_session_value('camp_admin_currency').$val;
	}
}

/* cedele gst cal */

if(!function_exists('get_inclusive_gst_amount'))
{
	function get_inclusive_gst_amount($order_total_amount=null,$gst_percentage=null) {
		
		$order_total_amount = $order_total_amount;
		$order_gst_percentage = '7';
		$order_gst_calculation = '1.07';
		
		$get_gst_total_amount = $order_total_amount / $order_gst_calculation;
		
		$get_gst_amount_only = ($order_gst_percentage / 100) * $get_gst_total_amount;
		
		$gst_inclusive_amount = number_format($get_gst_amount_only,2);
		
		return $gst_inclusive_amount;
	}
}



if(!function_exists('get_inclusivegst_text'))
{
	function get_inclusivegst_text($order_total_amount=null,$gst_percentage=null) {
		
		$incGstAmt = '';
		
		$gst_percentage = 7;
		
		if((float)$order_total_amount>0) {
			$vatDivisor = 1 + ($gst_percentage/100);
			$gstpercentage = $gst_percentage/100;
			$productvalue = $order_total_amount/$vatDivisor;
			$gstval = $productvalue*$gstpercentage;
			$gstAmt = number_format(str_replace(',','',$gstval),2);
			$incGstAmt = "GST Inclusive (".$gst_percentage."%): $".$gstAmt;
		}
		
		return $incGstAmt;
		
	}
}

if(!function_exists('get_payment_status_app'))
{
	function get_payment_status_app($app_id,$payment_mode,$payment_retrieved,$add_tag=null,$cash_pay=null)
	{
		$payment_status = '';
		
		if(($app_id = georges_app_id) || ($app_id = copperchimmey_app_id)) {
			if($payment_mode == '3' || $payment_mode == '2') {
				if($payment_retrieved == 'Yes') {
					$payment_status = 'Success';
				} else {
					$payment_status = 'Failure';
				}
			} else if($payment_mode == '1' && $app_id = copperchimmey_app_id) {
				$payment_status = $cash_pay;
			}
		}
		

		return $payment_status;

	} 
}


/* this function get Site Location Details */
if(!function_exists('get_site_location'))
{
	function get_site_location($app_id, $locationID=null)
	{
		$CI = & get_instance();
		$where = array(
			'sl_app_id'	=>$app_id,
			'sl_status' => 'A'
		);
		if(!empty($locationID)) {
			$where = array_merge($where,array('sl_location_id' => $locationID));
		}
		
		$result = $CI->Mydb->get_all_records('*','site_location',$where);
		$result_set = array();
		if(!empty($result)) {
			$sl_location_id = array_column($result, 'sl_location_id');
			$result_set = array_combine($sl_location_id, $result);
		}
		if(!empty($result_set)) {
			return $result_set;
		}
	}
}


/* this method used for add maintain stock value */	
if(!function_exists('send_grid_to_addredd_values'))
{
	function send_grid_to_addredd_values($to_address, $user_name='') {
		
		$tos = array();
		if($to_address) {
			$exploded = explode(',', $to_address);

			if(!empty($exploded)) {
				foreach($exploded as $user){
					$tos[$user]= $user_name;
				}

			}
		}

		return $tos;
	}
}

if ( ! function_exists('senttwiliosms')) {
	function senttwiliosms($appID,$customer_mobile, $message) {
		$CI = & get_instance();
		$CI->load->library('twiliosms');
		$return_array = array();		
		$company_sms_option = $CI->Mydb->get_record ( 'client_id,client_app_id,client_app_name,client_sms_settings_enable,client_sms_period,client_sms_count_total,client_sms_startfrom,client_sms_count_balance', 'clients', array ('client_app_id' => $appID));		
		$client_sms_settings_enable = $company_sms_option['client_sms_settings_enable'];
		$client_sms_count_balance = $company_sms_option['client_sms_count_balance'];		
		if((int)$client_sms_settings_enable == 1) {
			$company_id = $company_sms_option['client_id'];
			if((int)$client_sms_count_balance>0) {			
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
						$to_num = '+'.$customer_mobile;
					} else if (strlen($customer_mobile)==8) {
						$to_num = '+65'.$customer_mobile;
					} else {
						$sms_reason = 'Invalid phone number.';
					}

				}else{
					$sms_reason = 'Phone number was empty';
				}
				
				$sms_response = $sms_log = null;							
				$from_num = $sms_from_num;				
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
					$update_sms_countBln = (int)$client_sms_count_balance - 1;
					$updateArray=array('client_sms_count_balance' => $update_sms_countBln);
					$updateWhere = array('client_id' => $company_id);
					$ClntUpdate = $CI->Mydb->update('clients',$updateWhere,$updateArray);
					$return_array = array ('status' => "ok",'message' => "OTP has been sent your mobile no.",'sms_reason' => $sms_reason, 'sms_response' => $sms_response);	
				}				
				if($sms_response == "failed"){					
					$return_array = array ('status' => 'error', 'message' => "Please enter a valid phone number.");					
				}
			}else{				
				$return_array = array ('status' => 'error', 'message' => "Please add sms count for this APP.");
			}
		}
		return $return_array;
	}
}



if ( ! function_exists('cancellRiderCancelOrder')) {
	function cancellRiderCancelOrder($company, $orderDetails) {
		$CI = & get_instance();
		$clientSetting = getClientSettings($company['client_id']);
		$orderCustomer = $CI->Mydb->get_record('order_customer_fname, order_customer_mobile_no, order_customer_email', 'orders_customer_details', array('order_customer_order_id'=>$orderDetails['order_id']));
		if(!empty($clientSetting['client_send_rider_cancel_sms']) && $clientSetting['client_send_rider_cancel_sms']==1) {
			/* Customer Message */
			if(!empty($orderCustomer['order_customer_mobile_no'])) {
				$message = 'Dear '.$orderCustomer['order_customer_fname'].', Your order '.$orderDetails['order_local_no'].' has been cancelled. Our team will process a refund back to you in 7-10 working days. Please contact us at '.$company['client_company_phone'].' if you have any queries. '.$company['client_name'].'.';
				$sms = senttwiliosms($orderDetails['order_company_app_id'], '+65'.$orderCustomer['order_customer_mobile_no'], $message);
			}

			/* Admin Message */
			if(!empty($company['client_company_phone'])) {
				$message = 'Dear Admin, Please note, order '.$orderDetails['order_local_no'].' has been cancelled and an SMS has been sent to the customer. '.$company['client_name'].'.';
				$sms = senttwiliosms($orderDetails['order_company_app_id'], '+65'.$company['client_company_phone'], $message);
			}
		}

		$company_users = $CI->Mydb->get_record('user_id', 'company_users', array('user_app_id'=>$company['client_app_id'], 'user_type'=>'MainAdmin'));
		$user_id = '';
		if(!empty($company_users)) {
			$user_id = $company_users['user_id'];
		}

		$postdata = array("app_id" => $company['client_app_id'], "order_primary_id"=>$orderDetails['order_primary_id'], "order_status"=>"5", 'order_remarks'=>'Rider cancelled', 'logged_id'=>$user_id);

		$url = base_url('api/businesscall/change_orderstatus_popup');
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$resp = curl_exec($curl);
		curl_close($curl);
	}
}

if ( ! function_exists('getCompanyUniqueID')) {
	function getCompanyUniqueID($companyID) {
		$CI = & get_instance();
		$company = $CI->Mydb->get_record('company_unquie_id', 'company', array('company_id' => $companyID));
		if(!empty($company)){
			return $company['company_unquie_id'];
		}
	}
}