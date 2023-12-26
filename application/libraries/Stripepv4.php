<?php 
/*Used for georges*/
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(dirname(__FILE__) . '/Stripe/lib/Stripe.php');

class Stripepv4
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
		$api_key = $this->ci()->config->item($payment_reference.'_stripe_api_key_'.$stripe_envir);
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
	
	public function processToken($payment,$payment_reference,$config_file_name=null,$stripe_key=null, $customer_data, $app_name='', $app_id, $stripe_envir){
		
		/* getting values from the config */
		$config_file_name = ($config_file_name ==""? "stripe" : $config_file_name);
			
		try {
			$this->ci()->config->load('stripe');
			$api_key =  $this->ci()->config->item($payment_reference.'_stripe_api_key_'.$stripe_envir) ;
			$currency_code = $this->ci()->config->item($payment_reference.'_currency_code');
			Stripe::setApiKey($api_key);
			$create_customer = 'no';
			
			/*** Collect Customers Data ***/
			
			$customer_email = isset($customer_data['customer_email']) ? $customer_data['customer_email'] : '';
			$customer_stripe_id = isset($customer_data['customer_stripe_id_'.$stripe_envir]) ? $customer_data['customer_stripe_id_'.$stripe_envir] : '';
			$customer_id = isset($customer_data['customer_id']) ? $customer_data['customer_id'] : '';
			
			$create_customer = isset($customer_data['create_customer']) ? strtolower($customer_data['create_customer']) : 'no';
			$card_id = isset($customer_data['card_id']) ? $customer_data['card_id'] : '';
			 
			if($create_customer=='no' && $card_id==''){
				$response = Stripe_Charge::create(array(
					"amount" =>$payment['amount']*100,
					"currency" => $currency_code,
					"source" => $payment['token'],
					"description" => $payment['product_name'],
					"capture" => false,
					"metadata" => array("Outlet Name" =>$payment['outlet_name'])
				));
			} elseif($customer_stripe_id!=''){
				
				/*** Retrive customers details ***/
				$customer = Stripe_Customer::retrieve($customer_stripe_id);
				
				if(isset($customer->id)){
					
					/*** Save new card ***/
					if($create_customer=='yes'){
						$card_data = $customer->sources->create(array("source" => $payment['token']));
						$customer->default_source = $card_data->id;
						$customer->save();
					}
					
					/*** Set default from saved card ***/
					if($card_id!='') {
						$customer->default_source = $card_id;
						$customer->save();
					}
					
					$charge_parm = array(
						"amount" =>$payment['amount']*100,
						"currency" => $currency_code,
						"description" => $payment['product_name'],
						"customer" => $customer_stripe_id,
						"capture" => false,
						"metadata" => array("Outlet Name" =>$payment['outlet_name'])
					);
					
					/*** Charge customer by customer_stripe_id ***/
					$response = Stripe_Charge::create($charge_parm);
				} else {
					/*** For failed customer_stripe_id ***/
					$customer_stripe_id = '';
					$create_customer = 'yes';
				}
			}
			
			if($create_customer=='yes' && $customer_stripe_id==''){
				
				/*** Create new customer ***/
				$customer = Stripe_Customer::create(array(
					  "source" => $payment['token'],
					  "email"=> $customer_email
					)
				);
				
				/*** Charge Customer by customer_stripe_id ***/
				$response = Stripe_Charge::create(array(
					"amount" => $payment['amount']*100,
					"currency" => $currency_code,
					"description" => $payment['product_name'],
					"customer" => $customer->id,
					"capture" => false,
					"metadata" => array("Outlet Name" =>$payment['outlet_name'])
				));
				
				$customer_stripe_id = $customer->id;
			}
			
			
		} catch(Stripe_CardError $e) {
		
		} catch (Stripe_InvalidRequestError $e) {
			/*** If no customer_id exit in Database and not exit in stripe ***/
			if($customer_stripe_id!='' && $create_customer=='yes'){
				$customer_data['customer_stripe_id_'.$stripe_envir] = '';
				return $this->processToken($payment,$payment_reference,$config_file_name,$stripe_key,$customer_data, $app_name, $app_id, $stripe_envir);
			}
		} catch (Stripe_AuthenticationError $e) {
		
		} catch (Stripe_ApiConnectionError $e) {
		
		} catch (Stripe_Error $e) {
		
		} catch (Exception $e) {
		
		}
		
		if(isset($e)){
			$e_json = $e->getJsonBody();
			return $error = $e_json['error'];  //error message here
		} else {
			/*** Update Stripe Customer ID ***/
			if($customer_id!=''){
				$where = array('customer_id' => $customer_id);
				$update_data = array('customer_stripe_id_'.$stripe_envir => $customer_stripe_id);
				$this->ci()->Mydb->update('pos_customers', $where, $update_data);
			}
			
			return $response;
		}
	}
	
	public function getSavedCards($customer_id,$reference,$config_file_name=null,$stripe_envir){
		$config_file_name = ($config_file_name ==""? "stripe" : $config_file_name);
			
		try{
			$this->ci()->config->load($config_file_name);

			$api_key =  $this->ci()->config->item($reference.'_stripe_api_key_'.$stripe_envir);

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
			$api_key =  $this->ci()->config->item($reference.'_stripe_api_key_'.$stripe_envir);
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
			$api_key =  $this->ci()->config->item($reference.'_stripe_api_key_'.$stripe_envir);
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
		$api_key =  $this->ci()->config->item($payment_reference.'_stripe_api_key_'.$stripe_envir) ;

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
			$api_key =  $this->ci()->config->item($reference.'_stripe_api_key_'.$stripe_envir);
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
			$api_key =  $this->ci()->config->item($reference.'_stripe_api_key_'.$stripe_envir);
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
