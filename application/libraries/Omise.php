<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__) . '/omise/lib/Omise.php');
class Omise
{	
	public function __construct(){

	}

	public function processToken($appID, $payment, $customer_data, $app_name='')
	{
		$CleintDetails = $this->getCleintDetails($appID);

		if($payment ['stripe_key'] !='' && $payment ['omise_public_key'] !=''){

           $CleintDetails['secret_key'] = $payment ['stripe_key'];
           $CleintDetails['public_key'] = $payment ['omise_public_key'];
		}
		//print_R($CleintDetails['public_key']);die;
		if(!empty($CleintDetails)) {
			try{
				define('OMISE_PUBLIC_KEY', $CleintDetails['public_key']);
				define('OMISE_SECRET_KEY', $CleintDetails['secret_key']);
				define('OMISE_API_VERSION', '2019-05-29');
				$response = '';
				if($customer_data['create_customer']=='no') {
					$response = OmiseCharge::create(array(
					  	'amount' => $payment['amount']*100,
					  	'currency' => 'sgd',
					  	'card' => $payment['token'],
					  	'capture'=> false,
					  	'description' => $payment['product_name'],
						'metadata' => array("Outlet Name" =>$payment['outlet_name'])
					));
				}
				else {
					$customer = OmiseCustomer::create(array(
					  		'email' => $customer_data['customer_email'],
					  		'description' => trim(ucwords($customer_data['customer_first_name'].' '.$customer_data['customer_last_name'])),
					  		'card' => $payment['token']
						)
					);
					if(!empty($customer['id'])) {
						$response = OmiseCharge::create(array(
						  	'amount' => $payment['amount']*100,
						  	'currency' => 'sgd',
						  	'customer' => $customer['id'],
						  	'capture'=> false,
						  	'description' => $payment['product_name'],
							'metadata' => array("Outlet Name" =>$payment['outlet_name'])
						));
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

	public function capture($chargeID, $appID , $payment)
	{
		$CleintDetails = $this->getCleintDetails($appID);
		if($payment ['stripe_key'] !='' && $payment ['omise_public_key'] !=''){

           $CleintDetails['secret_key'] = $payment ['stripe_key'];
           $CleintDetails['public_key'] = $payment ['omise_public_key'];
		}
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

	public function getCleintDetails($appID)
	{
		if(!empty($appID)) {
			$CI =& get_instance();
			$clients = $CI->db->select('client_omise_mode, client_omise_secret_live, client_omise_secret_test, client_omise_public_live, client_omise_public_test')->from('clients')->where('client_app_id', $appID)->get()->result();
			$result = array();
			if(!empty($clients)) {			
				$clients = $clients[0];					
				if($clients->client_omise_mode==1) {
					$result['secret_key'] = $clients->client_omise_secret_live;
					$result['public_key'] = $clients->client_omise_public_live;
				}
				else {
					$result['secret_key'] = $clients->client_omise_secret_test;
					$result['public_key'] = $clients->client_omise_public_test;
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
	
	
	public function processdirectCapture($appID, $payment, $customer_data, $app_name='')
	{
		$CleintDetails = $this->getCleintDetails($appID);

		if($payment ['stripe_key'] !='' && $payment ['omise_public_key'] !=''){

           $CleintDetails['secret_key'] = $payment ['stripe_key'];
           $CleintDetails['public_key'] = $payment ['omise_public_key'];
		}

		if(!empty($CleintDetails)) {
			try{
				define('OMISE_PUBLIC_KEY', $CleintDetails['public_key']);
				define('OMISE_SECRET_KEY', $CleintDetails['secret_key']);
				define('OMISE_API_VERSION', '2019-05-29');
				$response = '';
				if($customer_data['create_customer']=='no') {
					$response = OmiseCharge::create(array(
					  	'amount' => $payment['amount']*100,
					  	'currency' => 'sgd',
					  	'card' => $payment['token'],
					  	'description' => $payment['product_name'],
						'metadata' => array("Outlet Name" =>$payment['outlet_name'])
					));
				}
				else {
					$customer = OmiseCustomer::create(array(
					  		'email' => $customer_data['customer_email'],
					  		'description' => trim(ucwords($customer_data['customer_first_name'].' '.$customer_data['customer_last_name'])),
					  		'card' => $payment['token']
						)
					);
					if(!empty($customer['id'])) {
						$response = OmiseCharge::create(array(
						  	'amount' => $payment['amount']*100,
						  	'currency' => 'sgd',
						  	'customer' => $customer['id'],
						  	'capture'=> false,
						  	'description' => $payment['product_name'],
							'metadata' => array("Outlet Name" =>$payment['outlet_name'])
						));
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

	
	public function searchPaymentDetails($appID,$card_id) 
	{
		$CleintDetails = $this->getCleintDetails($appID);
		
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
	
	
	public function retrieve_charge($chargeID, $appID)
	{
		$CleintDetails = $this->getCleintDetails($appID);
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
	
	
}
