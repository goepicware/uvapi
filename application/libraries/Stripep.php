<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(dirname(__FILE__) . '/Stripe/lib/Stripe.php');

class Stripep
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
		try{
		$this->ci()->config->load('stripe');
		$api_key = $this->ci()->config->item($payment_reference.'_stripe_api_key');
		$currency_code = $this->ci()->config->item($payment_reference.'_currency_code');
		
		$card = array("card" => $payment['card']);
	    $token =$this->__getToken($api_key, $card);
		$response = Stripe_Charge::create(array(
  				"amount" =>$payment['amount']*100,
 				"currency" => $currency_code,
  				"source" => $token,
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
		
		/* getting values from the config */
		//$config_file_name = ($config_file_name ==""? "stripe" : $config_file_name);
			
		try{
		$this->ci()->config->load('stripe');
		$api_key =  $this->ci()->config->item($payment_reference.'_stripe_api_key') ;
		$currency_code = $this->ci()->config->item($payment_reference.'_currency_code');
		Stripe::setApiKey($api_key);	
		
		/* $customer = Stripe_Customer::create(array(
		  "source" => $payment['token'],
		  "description" => "Dine in Hero",
		  "email"=>'rmarktest@gmail.com'
		  )
		); 
		$customer_array = $customer->__toArray(true);
		*/
		$response = Stripe_Charge::create(array(
  				"amount" =>$payment['amount']*100,
 				"currency" => $currency_code,
  				"source" => $payment['token'],
				"description" => $payment['product_name'],
				"metadata" 		=> array('Outlet Name'=>$payment['outlet_name'])
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
}