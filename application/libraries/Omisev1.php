<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__) . '/omise/lib/Omise.php');
class Omisev1
{	
	public function __construct(){

	}

	public function processToken($appID, $payment, $customer_data, $app_name='', $redirect_uri	=	'', $three_d_enabled='', $outletID='')
	{
		$CleintDetails = $this->getCleintDetails($appID, $outletID);
		
		if(!empty($CleintDetails)) {
			try{
				define('OMISE_PUBLIC_KEY', $CleintDetails['public_key']);
				define('OMISE_SECRET_KEY', $CleintDetails['secret_key']);
				define('OMISE_API_VERSION', '2019-05-29');
				$response = '';
				if($customer_data['create_customer']=='no') {

					$params	=	array(
					  	'amount' => $payment['amount']*100,
					  	'currency' => 'sgd',
					  	'card' => $payment['token'],
					  	'capture'=> false,
					  	'description' => $payment['product_name'],
						'metadata' => array("Outlet Name" =>$payment['outlet_name'])
					);
					if($three_d_enabled=='1') {
						$params['return_uri']  = $redirect_uri;	
					}
					$response = OmiseCharge::create($params);
				}
				else {
					$customer = OmiseCustomer::create(array(
					  		'email' => $customer_data['customer_email'],
					  		'description' => trim(ucwords($customer_data['customer_first_name'].' '.$customer_data['customer_last_name'])),
					  		'card' => $payment['token']
						)
					);
					if(!empty($customer['id'])) {
						$params	=	array(
						  	'amount' => $payment['amount']*100,
						  	'currency' => 'sgd',
						  	'customer' => $customer['id'],
						  	'capture'=> false,
						  	'description' => $payment['product_name'],
							'metadata' => array("Outlet Name" =>$payment['outlet_name'])
						);
						if($three_d_enabled=='1') {
							$params['return_uri']  = $redirect_uri;
							
						}
						$response = OmiseCharge::create($params);
					}
					else {
						return array('message'=>$customer, 'response'=>$customer);
						exit;
					}
				}
				return $response;				
			}
			catch (Exception $e) {
				$e_json = $e->getMessage();
				return $error = $e_json;
			}	
		}
	}

	public function capture($chargeID, $appID, $outletID='')
	{
		$CleintDetails = $this->getCleintDetails($appID, $outletID);
		if(!empty($CleintDetails)) {
			define('OMISE_PUBLIC_KEY', $CleintDetails['public_key']);
			define('OMISE_SECRET_KEY', $CleintDetails['secret_key']);
			define('OMISE_API_VERSION', '2019-05-29');
			if(!empty($chargeID)) {
				try{
					$charge = OmiseCharge::retrieve($chargeID);
					$charge->capture();
					$response = OmiseCharge::retrieve($chargeID);
					return $response;
				}
				catch (Exception $e) {
					$e_json = $e->getMessage();
					return array('message'=>$e_json, 'response'=>$e);
					exit;
				}	
			}
		}
	}

	public function retrieve_charge($chargeID, $appID, $outletID='')
	{
		$CleintDetails = $this->getCleintDetails($appID, $outletID);
		if(!empty($CleintDetails)) {
			define('OMISE_PUBLIC_KEY', $CleintDetails['public_key']);
			define('OMISE_SECRET_KEY', $CleintDetails['secret_key']);
			define('OMISE_API_VERSION', '2019-05-29');
			if(!empty($chargeID)) {
				try{
					$response = OmiseCharge::retrieve($chargeID);
					return $response;
				}
				catch (Exception $e) {
					$e_json = $e->getMessage();
					return array('message'=>$e_json, 'response'=>$e);
					exit;
				}	
			}
		}
	}

	

	public function getCleintDetails($appID, $outletID=null)
	{
		if(!empty($appID)) {
			$CI =& get_instance();
			$clients = $CI->db->select('client_omise_mode, client_omise_secret_live, client_omise_secret_test, client_omise_public_live, client_omise_public_test')->from('clients')->where('client_app_id', $appID)->get()->result();
			$result = array();
			if(!empty($clients)) {
				$clients = $clients[0];

				$outletpayment = $CI->Mydb->get_record('config_details', 'outlet_payment_config', array('outlet_id'=>$outletID, 'stauts'=>1, 'payment_gatway'=>'OMISE'));
				if(!empty($outletpayment)) {
					$config_details = (!empty($outletpayment['config_details']))?unserialize($outletpayment['config_details']):'';
					
					if(!empty($config_details)) {
						if(!empty($config_details['outlet_omise_mode']) && $config_details['outlet_omise_mode']==1) {
							$result['secret_key']=$config_details['outlet_omise_secret_live'];
							$result['public_key']=$config_details['outlet_omise_public_live'];
						}
						else {
							$result['secret_key']=$config_details['outlet_omise_secret_test'];
							$result['public_key']=$config_details['outlet_omise_public_test'];
							
						}
					}
					
				}
				else {
					if($clients->client_omise_mode==1) {
						$result['secret_key'] = $clients->client_omise_secret_live;
						$result['public_key'] = $clients->client_omise_public_live;
					}
					else {
						$result['secret_key'] = $clients->client_omise_secret_test;
						$result['public_key'] = $clients->client_omise_public_test;
					}
				}

				return $result;				
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
		
	}
	
	
	public function searchPaymentDetails($appID,$card_id, $outletID='') 
	{
		$CleintDetails = $this->getCleintDetails($appID, $outletID);
		
		if(!empty($CleintDetails)) { 
			
			
			define('OMISE_SECRET_KEY', $CleintDetails['secret_key']);
			
			$search = OmiseCharge::search()->filter(array(
				'captured' => false,
				'created'  => "today",
			));
			
			$response = $search['data'];
			
			return $response;
			exit;
			
		}
	}

}
