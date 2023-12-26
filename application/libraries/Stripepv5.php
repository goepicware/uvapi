<?php 
/*Used for Ninjapro latest auth and capture*/
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(dirname(__FILE__) . '/Stripe/lib/Stripe.php');

class Stripepv5
{
	/**
	 * Get an instance of CodeIgniter
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function ci()
	{
		return get_instance();
	}

	/* Creating Token  */
	function __getToken($api_key='', $card){
		
		Stripe::setApiKey($api_key);		
		$result = Stripe_Token::create($card);
		return $result['id'];
	}

	/* Payment processing */
	public function process($payment,$payment_reference){

		/* getting values from the config */
		try {

		$this->ci()->config->load('stripe');
		$api_key = $this->ci()->config->item($payment_reference.'_stripe_api_key');
		$currency_code = $this->ci()->config->item($payment_reference.'_currency_code');

		$card = array("card" => $payment['card']);
	    $token =$this->__getToken($api_key, $card);
		$response = Stripe_Charge::create(array(
  				"amount" =>$payment['amount']*100,
 				"currency" => $currency_code,
  				"source" => $token,
				"capture" => false,
  				"description" => $payment['product_name']
				));
				
		
		}
		
		catch(Stripe_CardError $e) {
		
		}
		catch (Stripe_InvalidRequestError $e) {
			
		} catch (Stripe_AuthenticationError $e) {
			
		} catch (Stripe_ApiConnectionError $e) {
			
		} catch (Stripe_Error $e) {
			 
		} catch (Exception $e) {
			
		}
		if(isset($e)){
			$e_json = $e->getJsonBody();
			return $error = $e_json['error'];  //error message here
		}else{
			return $response;
		}
	}
	
	public function processToken($payment,$payment_reference,$config_file_name=null,$stripe_key=null){

		try{
			$this->ci()->config->load('stripe');
			$api_key =  $this->ci()->config->item($payment_reference.'_stripe_api_key') ;
			$currency_code = $this->ci()->config->item($payment_reference.'_currency_code');
			Stripe::setApiKey($api_key);	

				$response = Stripe_Charge::create(array(
					"amount" =>$payment['amount']*100,
					"currency" => $currency_code,
					"source" => $payment['token'],
					"description" => $payment['product_name'],
					"capture" => false,
					"metadata" => array("Outlet Name" =>$payment['outlet_name'])
				));

		}

		catch(Stripe_CardError $e) {
		
		}
		catch (Stripe_InvalidRequestError $e) {
		
		} catch (Stripe_AuthenticationError $e) {
		
		} catch (Stripe_ApiConnectionError $e) {
		
		} catch (Stripe_Error $e) {
		
		} catch (Exception $e) {
		
		}

		if(isset($e)){
			$e_json = $e->getJsonBody();
		
			return $error = $e_json['error'];  //error message here
		} else{
			return $response;
		}

	}
	
	public function getSavedCards($customer_id,$reference,$config_file_name=null,$stripe_envir){
		$config_file_name = ($config_file_name ==""? "stripe" : $config_file_name);
			
		try{
			$this->ci()->config->load($config_file_name);

			$api_key =  $this->ci()->config->item($reference.'_stripe_api_key');

			Stripe::setApiKey($api_key);	

			$response = Stripe_Customer::retrieve($customer_id);
		}
		
		catch(Stripe_CardError $e) {
		
		}
		catch (Stripe_InvalidRequestError $e) {
		
		} catch (Stripe_AuthenticationError $e) {
		
		} catch (Stripe_ApiConnectionError $e) {
		
		} catch (Stripe_Error $e) {
		
		} catch (Exception $e) {
		
		}
		if(isset($e)){
			$e_json = $e->getJsonBody();
		
			return $error = $e_json['error'];  //error message here
		} else {
			return $response;
		}
	}
	
	public function deleteCard($customer_id, $card_id, $reference, $config_file_name=null,$stripe_envir){
	
		$config_file_name = ($config_file_name ==""? "stripe" : $config_file_name);
			
		try{  
			 $this->ci()->config->load($config_file_name);
			$api_key =  $this->ci()->config->item($reference.'_stripe_api_key');
			Stripe::setApiKey($api_key);
			$customer = Stripe_Customer::retrieve($customer_id);
		    $response =  $customer->sources->retrieve($card_id)->delete();
		}
	
		catch(Stripe_CardError $e) {
	
		}
		catch (Stripe_InvalidRequestError $e) {
	
		} catch (Stripe_AuthenticationError $e) {
	
		} catch (Stripe_ApiConnectionError $e) {
	
		} catch (Stripe_Error $e) {
	
		} catch (Exception $e) {
	
		}
		if(isset($e)){
			$e_json = $e->getJsonBody();
	
			return $error = $e_json['error'];  //error message here
		} else {
			return $response;
		}
	
	}
	
	/* refund captured amount */
	public function refundAmount($token, $reference, $config_file_name=null,$stripe_envir){
		//echo $token; exit;
		$config_file_name = ($config_file_name ==""? "stripe" : $config_file_name);
		try{

			$this->ci()->config->load($config_file_name);
			$api_key =  $this->ci()->config->item($reference.'_stripe_api_key');
			Stripe::setApiKey($api_key);
			 //$stipes	= new Stripe_Charge();
			 //$response = $stipes->refund(array("charge" => $token));
			// print_r($response); exit;
			
			$ch = Stripe_Charge::retrieve($token);
		    $response = $ch->refunds->create();
		    
		    // print_r($response); exit;
		}

		catch(Stripe_CardError $e) {

		}
		catch (Stripe_InvalidRequestError $e) {

		} catch (Stripe_AuthenticationError $e) {

		} catch (Stripe_ApiConnectionError $e) {

		} catch (Stripe_Error $e) {

		} catch (Exception $e) {

		}
		if(isset($e)){
			$e_json = $e->getJsonBody();
			return $error = $e_json['error'];  //error message here
		} else {
			return $response;
		}
	}

public function capture($captureId,$payment_reference,$config_file_name=null,$stripe_key=null,$stripe_envir){

		try{
		$this->ci()->config->load('stripe');
		$api_key =  $this->ci()->config->item($payment_reference.'_stripe_api_key') ;

		$currency_code = $this->ci()->config->item($payment_reference.'_currency_code');
		Stripe::setApiKey($api_key);	

		$ch = Stripe_Charge::retrieve($captureId);
		$response = $ch->capture();
				
		
		}
		
		catch(Stripe_CardError $e) {
		
		}
		catch (Stripe_InvalidRequestError $e) {
		
		} catch (Stripe_AuthenticationError $e) {
		
		} catch (Stripe_ApiConnectionError $e) {
		
		} catch (Stripe_Error $e) {
		
		} catch (Exception $e) {
		
		}
		if(isset($e)){
			$e_json = $e->getJsonBody();
		
			return $error = $e_json['error'];  //error message here
		}else{
			return $response;
		}
	}
	
	/* Caputur amount */
	public function retrieveAmount($captureId,$reference,$config_file_name=null,$stripe_envir){
		$config_file_name = ($config_file_name ==""? "stripe" : $config_file_name);
			
		try{  
			$this->ci()->config->load($config_file_name);
			$api_key =  $this->ci()->config->item($reference.'_stripe_api_key');
			Stripe::setApiKey($api_key);
			$ch = Stripe_Charge::retrieve($captureId);
		   $response = $ch->capture();
			  
		 // print_r($response); exit;
		}
	
		catch(Stripe_CardError $e) {
	
		}
		catch (Stripe_InvalidRequestError $e) {
	
		} catch (Stripe_AuthenticationError $e) {
	
		} catch (Stripe_ApiConnectionError $e) {
	
		} catch (Stripe_Error $e) {
	
		} catch (Exception $e) {
	
		}
		if(isset($e)){
			$e_json = $e->getJsonBody();
	
			return $error = $e_json['error'];  //error message here
		} else {
			return $response;
		}
	}
	
	
	
/* Caputur amount */
	public function getChargeDetails($captureId,$reference,$config_file_name=null,$stripe_envir){
		$config_file_name = ($config_file_name ==""? "stripe" : $config_file_name);
			
		try{  
			$this->ci()->config->load($config_file_name);
			$api_key =  $this->ci()->config->item($reference.'_stripe_api_key');
			Stripe::setApiKey($api_key);
			$response = Stripe_Charge::retrieve($captureId);
		}
	
		catch(Stripe_CardError $e) {
		}
		catch (Stripe_InvalidRequestError $e) {
	
		} catch (Stripe_AuthenticationError $e) {
	
		} catch (Stripe_ApiConnectionError $e) {
	
		} catch (Stripe_Error $e) {
	
		} catch (Exception $e) {
	
		}
		if(isset($e)){
			$e_json = $e->getJsonBody();
	
			return $error = $e_json['error'];  //error message here
		} else {
			return $response;
		}
	}
	
	
}
