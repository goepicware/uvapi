<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	/**
	* Name:  Twilio
	*
	* Author: Ben Edmunds
	*		  ben.edmunds@gmail.com
	*         @benedmunds
	*
	* Location:
	*
	* Created:  03.29.2011
	*
	* Description:  Twilio configuration settings.
	*
	*
	*/

	/**
	 * Mode ("sandbox" or "prod")
	 **/
	/*$config['mode']   = 'sandbox';*/ 
	$config['mode']   = (isset($_SESSION['sms_session_mode'])) ? $_SESSION['sms_session_mode'] : 'sandbox';
	/**
	 * Account SID
	 **/
	/*$config['account_sid']   = 'ACf55c44d7de90b6e7993baedbc831ce49';*/
    $config['account_sid']   = (isset($_SESSION['sms_account_sid'])) ? $_SESSION['sms_account_sid'] : 'ACf55c44d7de90b6e7993baedbc831ce49';
	/**
	 * Auth Token
	 **/
	/*$config['auth_token']    = 'a6c635e061980fa338378340040ee126';*/
    $config['auth_token']   = (isset($_SESSION['sms_auth_token'])) ? $_SESSION['sms_auth_token'] : 'a6c635e061980fa338378340040ee126';
	/**
	 * API Version
	 **/
	$config['api_version']   = '2010-04-01';

	/**
	 * Twilio Phone Number
	 **/
	/*$config['number']        = '+18063053023';*/
	$config['number']   = (isset($_SESSION['sms_number'])) ? $_SESSION['sms_number'] : '+18063053023';


/* End of file twilio.php */
