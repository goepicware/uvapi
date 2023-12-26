<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(dirname(__FILE__) . '/Stripe-subscription/init.php');

class Stripeps
{
	
	//private $api_key;
	public function __construct(){
		//$this->ci()->config->load('stripe');		
		//$this->api_key =  $this->ci()->config->item('stripe_api_key');
		//$this->api_key = 'sk_test_cyyLDY8BAjqLecO1kS1QDg3p'; // Test API Key
		//$this->api_key = 'sk_live_6oYaQj619jXqgk4BzQaLvZ9u'; // Live API Key
	}
	
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
	function __getToken($api_key = '', $card, $opts = null){
		try{
			\Stripe\Stripe::setApiKey($api_key);
			$result = \Stripe\Token::create($card,$opts);
			return $result['id'];
		}
		catch(Stripe_CardError $e) {
			$body = $e->getJsonBody();
			$err  = $body['error'];
			return $err['message'];
		} catch (Stripe_InvalidRequestError $e) {
			return  $e->getMessage();
		} catch (Stripe_AuthenticationError $e) {
			return  $e->getMessage();
		} catch (Stripe_ApiConnectionError $e) {
			return  $e->getMessage();
		} catch (Stripe_Error $e) {
			return  $e->getMessage();
		} catch (Exception $e) {
			return  $e->getMessage();
		}
	}

	/* Payment processing */
	/**
	  * create  card
      *  @param  Array
	  *  @param[amount] - amount in currency - required
	  *  @param[currency_code] - String  - required
	  *  @param[customer_id] - String customer_id or Card info is required
	  *  @param[product_name] -  String  --Optional
      *  @param[capture] - Boolean    -- optional  - default true.
	  *  @param[card]  - Array   Card info  is requried if doen't have customer_id
	  *         - @card[card_number] - String
	  *         - @card[exp_month] - Int (mm)
	  *         - @card[exp_year] - Int (YYYY)
	  *         - @card[cvc] - String	  
	  *  @return Charge object
	  */
	
	
	public function process($payment,$payment_reference='ninja',$stripe_envir=''){	
		try{		
		
			$this->ci()->config->load('stripe');
			$api_key = $this->ci()->config->item($payment_reference.'_stripe_api_key_'.$stripe_envir);
			$currency_code = $this->ci()->config->item($payment_reference.'_currency_code');
			
			$data = array(
  				"amount" =>$payment['amount']*100,
 				"currency" => $currency_code,				
  				"description" => $payment['product_name'],
				"capture"   => $payment['capture'],
				);
			if(isset($payment['customer_id']) && $payment['customer_id']){
				$data["customer"] = $payment['customer_id'];
			}elseif(isset($payment['card']) && is_array($payment['card'])){
				$card = array("card" => $payment['card']);
				$token = $this->__getToken($api_key, $card);
				$data["source"] = $token;
			}
			$response = \Stripe\Charge::create($data,$api_key);
			return $response;
		}	
		catch (Exception $e) {
			$e_json = $e->getJsonBody();
			return $error = $e_json['error'];
		}	
        
	}
	
	
	
	/**
	 *  Create a Customer
	 * @param array
	 * @param['email']   -- required
	 * @param['']
	 * @return Object or error message
	 */
	 public function create_customer($customer,$payment_reference='ninja',$stripe_envir=''){
		 
		 try{	
			$this->ci()->config->load('stripe');
			$api_key = $this->ci()->config->item($payment_reference.'_stripe_api_key_'.$stripe_envir);
			$currency_code = $this->ci()->config->item($payment_reference.'_currency_code');
			
			$data = array(		 
				"email" => $customer['email']		
			);
			if(isset($customer['metadata']) && is_array($customer['metadata'])){
				$data['metadata']  = $customer['metadata'];				  
			}
			$response = \Stripe\Customer::create($data,
				$api_key			
			);
			return $response;				
		} catch (Exception $e) {
		    $e_json = $e->getJsonBody();
			return $error = $e_json['error'];
		}	
	 }
	 
	 
	  /**
	  * retrieve the Customer
      *  @param String 
	  *  @return Customer object
	  */
     
     public function retrieve_customer($customer_id,$payment_reference='ninja',$stripe_envir=''){
		try{			
		
			$this->ci()->config->load('stripe');
			$api_key = $this->ci()->config->item($payment_reference.'_stripe_api_key_'.$stripe_envir);
			$currency_code = $this->ci()->config->item($payment_reference.'_currency_code');
			
			$response = \Stripe\Customer::retrieve($customer_id,$api_key);
			return $response;
		}catch (Exception $e) {
			$e_json = $e->getJsonBody();
			return $error = $e_json['error'];
		}		
	}
	 
	 /**
	  * Delete a Customer
	  *  @param String 
	  *  @return Delteted Customer object
	  */
	 
	 public function delete_customer($customer_id,$payment_reference='ninja',$stripe_envir=''){
		 try{	
			
			$this->ci()->config->load('stripe');
			$api_key = $this->ci()->config->item($payment_reference.'_stripe_api_key_'.$stripe_envir);
			$currency_code = $this->ci()->config->item($payment_reference.'_currency_code');
			
			$cus = \Stripe\Customer::retrieve($customer_id,$api_key );			
			$response = $cus->delete();
			return $response;
		} catch (Exception $e) {
			$e_json = $e->getJsonBody();
			return $error = $e_json['error'];
	    }		
	 }
	 
	 
	 /**
	  *  Update the Customer
      *  @param String,  String, Array
	  *  @data keys are email, default_source,  description
	  *  exp_month,  exp_year, name.
	  *  @return Customer object
	  */
	 
	  public function update_customer($customer_id, $data,$payment_reference='ninja',$stripe_envir=''){		
		try{
			$this->ci()->config->load('stripe');
			$api_key = $this->ci()->config->item($payment_reference.'_stripe_api_key_'.$stripe_envir);
			$currency_code = $this->ci()->config->item($payment_reference.'_currency_code');
			
			
			$customer = \Stripe\Customer::retrieve($customer_id,$api_key );
			foreach($data as $k=>$v)
				$customer->{$k} = $v;
			$response = $customer->save();
			return $response;
		}
		catch (Exception $e) {
			$e_json = $e->getJsonBody();
			return $error = $e_json['error'];
		}		
	}
	
	
	/**
     *  Create a plan 
	 *  @param array
	 *  @array[amount]  is a positive integer in cents (or 0 for a free plan)
	 *  @array[interval] should be day, week, month or year.
	 *  @array['name']  String
	 *  @array['currency']  3-letter ISO code for currency.
	 *  @array['id']    Unique identifier or a primary key from your own database. 
	 *  @array['interval_count'] -Optional  The number of intervals between each subscription billing. For      example, interval=month and     *  interval_count=3 bills every 3 months. Maximum of one year interval allowed (1 year, 12 months, or 52 weeks).
     * @array['metadata'] - Array - Additional information about the plan	 
	 *  @return Stripe_Plan or error message
	 **/
	
	public function create_plan($plan,$payment_reference='ninja',$stripe_envir=''){
		
		try{		

			$this->ci()->config->load('stripe');
			$api_key = $this->ci()->config->item($payment_reference.'_stripe_api_key_'.$stripe_envir);
			$currency_code = $this->ci()->config->item($payment_reference.'_currency_code');
			$interval = $this->ci()->config->item($payment_reference.'_subscribe_period');
			
			
			$data =  array(
			  "amount" => $plan['amount']*100,  
			  "interval" => $interval,  
			  "name" => $plan['name'],
			  "currency" => $currency_code, 
			  "id" => $plan['id'],
			  "interval_count" => isset($plan['interval_count'])?$plan['interval_count']:1,			  
                );
			if(isset($plan['trial_period_days'])){
				$data['trial_period_days'] = $plan['trial_period_days'];
			}
			if(isset($plan['metadata'])){
				$data['metadata'] = $plan['metadata'];
			}
			$response = \Stripe\Plan::create($data,
				 $api_key		
			);
			return $response;
		} catch (Exception $e) {
			$e_json = $e->getJsonBody();
			return $error = $e_json['error'];
		}
	}
	
	
	public function delete_plan($plan_id,$payment_reference='ninja',$stripe_envir=''){
		
		try{		

			$this->ci()->config->load('stripe');
			$api_key = $this->ci()->config->item($payment_reference.'_stripe_api_key_'.$stripe_envir);
			$currency_code = $this->ci()->config->item($payment_reference.'_currency_code');

			\Stripe\Stripe::setApiKey($api_key);
			$plan = \Stripe\Plan::retrieve($plan_id);
			$response = $plan->delete();
			return $response;
			
		} catch (Exception $e) {
			$e_json = $e->getJsonBody();
			return $error = $e_json['error'];
		}
	}
	
	/**
	 * Subscribe a customer to plan
	 * @param array,  array, String	 *
	 * @array['plan']  Unique identifier or a primary key from your own database. 
	 * @array['customer'] The identifier of the customer to subscribe. 
	 * @array['quantity']  -Optional The quantity you'd like to apply to the subscription you're creating.
	 * @array['trial_end']   -Optional Unix timestamp representing the end of the trial period the customer will get before being charged  * for the first time.
	 * @array['tax_percent']  -Optional A positive decimal (with at most four decimal places) between 1 and 100. 
	 *
	 * @card['name'] ['number'], ['exp_month'], ['exp_year'], ['cvc']
	 * @return Stripe_Customer Object
	 **/
	public function subscribe_customer_to_plan($customer,$payment_reference='ninja',$stripe_envir=''){
		
		try{
			$this->ci()->config->load('stripe');
			$api_key = $this->ci()->config->item($payment_reference.'_stripe_api_key_'.$stripe_envir);
			$currency_code = $this->ci()->config->item($payment_reference.'_currency_code');
			
			
			\Stripe\Stripe::setApiKey($api_key);
			$response = \Stripe\Subscription::create(array(
			  "source" => null,   
			  "plan" => $customer['plan'],  
			  "customer" => $customer['customer'],
			  "coupon" => isset($customer['coupon'])? $customer['coupon']:null,
			  "quantity" => isset($customer['quantity'])? $customer['quantity']:1,
              "trial_end" => isset($customer['trial_end'])? $customer['trial_end']:null,
			  "tax_percent" => isset($customer['tax_percent'])? $customer['tax_percent']:null,
			  "metadata" =>isset($customer['metadata'])? $customer['metadata']:array(),
               )					
			);
			return $response;
		 }catch (Exception $e) {
			$e_json = $e->getJsonBody();
			return $error = $e_json['error'];
		}	
	}
	
	
	 /**
	  * retrieve the card
      *  @param String customer_id, String card_id			
	  *  @return Card object
	  */
     
     public function retrieve_subscription($subscription_id,$payment_reference='ninja',$stripe_envir=''){
		try{
			$this->ci()->config->load('stripe');
			$api_key = $this->ci()->config->item($payment_reference.'_stripe_api_key_'.$stripe_envir);
			$currency_code = $this->ci()->config->item($payment_reference.'_currency_code');
			
			
			\Stripe\Stripe::setApiKey($api_key);
			$response = \Stripe\Subscription::retrieve($subscription_id);
			return $response;
		}catch (Exception $e) {
			$e_json = $e->getJsonBody();
			return $error = $e_json['error'];
		}			
	}
	
	
   /**
      * Calcel the subscription
	  * @param String,  Boolean
	  * @return Subscription Object or  Error message Array
	*/
	
	public function cancel_subscription($subscription_id, $at_period_end=false,$payment_reference='ninja',$stripe_envir=''){					
		try{
			$this->ci()->config->load('stripe');
			$api_key = $this->ci()->config->item($payment_reference.'_stripe_api_key_'.$stripe_envir);
			$currency_code = $this->ci()->config->item($payment_reference.'_currency_code');
			
			
			$sub = \Stripe\Subscription::retrieve($subscription_id,$api_key );			
			$response = $sub->cancel(array('at_period_end'=>$at_period_end));
			return $response;
		 }		 
		 catch (Exception $e) {
			$e_json = $e->getJsonBody();
			return $error = $e_json['error'];
		}       
			
	}
	  /**
	  * create  card
      *  @param String,  Array, Boolean
	  *  @card[card_number] 	- String		-- Required
	  *  @card[exp_month] 		- Int (mm)   	-- Required
	  *  @card[exp_year] 		- Int (YYYY)  	-- Required
	  *  @card[cvc]  			- String        -- Required
	  *
	  *  @card[name] 			- String
	  *  @card[address_line1] 	- String
	  *  @card[address_line2] 	- String
	  *  @card[address_city] 	- String
	  *  @card[address_state]  	- String
	  *  @card[address_zip]		- String
	  *  @card[address_country] - String   
	  
	  *  @return Card object
	  */
	
	
	  public function create_card($customer_id, $card, $set_default_card = false,$payment_reference='ninja',$stripe_envir=''){	
			try{
				
				$this->ci()->config->load('stripe');
				$api_key = $this->ci()->config->item($payment_reference.'_stripe_api_key_'.$stripe_envir);
				$currency_code = $this->ci()->config->item($payment_reference.'_currency_code');
				
				
		  			$card = array("card" => $card);
					$token =$this->__getToken($api_key, $card, array("customer" => $customer_id));				 
				 
				 /*  Adding a card into a customer account */ 
				 
				 $customer_obj = \Stripe\Customer::retrieve($customer_id,$api_key);
				 if (is_object($customer_obj)) {
					 if ($customer_obj->deleted == 1){
						return $error = "Stripe customer has been deleted!";
					 }
				 }
				  
				  
				  $card_obj = $customer_obj->sources->create(array("source" => $token),$api_key);
				 
				  if($set_default_card){
					  if(is_object($card_obj)){
						  $customer_obj->default_source = $card_obj->id;
						   $customer_obj->save();
					  }
				  }
				  return $card_obj;
			}		 
			catch (Exception $e) {
			$e_json = $e->getJsonBody();
			return $error = $e_json['error'];
		 }       
	  }
	
	  /**
	  * Update the card
      *  @param String,  String, Array
	  *  @data keys are address_city,  address_country, address_line1, address_line2, address_zip
	  *  exp_month,  exp_year, name.
	  *  @return Card object
	  */
	 
	  public function update_card($customer_id, $card_id, $data,$payment_reference='ninja',$stripe_envir=''){		
		try{
			$this->ci()->config->load('stripe');
			$api_key = $this->ci()->config->item($payment_reference.'_stripe_api_key_'.$stripe_envir);
			$currency_code = $this->ci()->config->item($payment_reference.'_currency_code');
			
			$customer = \Stripe\Customer::retrieve($customer_id,$api_key );						
			$card = $customer->sources->retrieve($card_id);			
			foreach($data as $k=>$v){
				if(!empty($v))
					$card->{$k} = $v;
			}
			$response = $card->save();
			return $response;
		}
		catch (Exception $e) {			
			$e_json = $e->getJsonBody();
			return $error = $e_json['error'];
		}		
	}
    
	  /**
	  * Delete the card
      *  @param String, String 
	  *  @return Card object
	  */ 
	
    public function delete_card($customer_id, $card_id,$payment_reference='ninja',$stripe_envir=''){		
		try{
			$this->ci()->config->load('stripe');
			$api_key = $this->ci()->config->item($payment_reference.'_stripe_api_key_'.$stripe_envir);
			$currency_code = $this->ci()->config->item($payment_reference.'_currency_code');
			
			
			\Stripe\Stripe::setApiKey($api_key);
			$customer = \Stripe\Customer::retrieve($customer_id);
			return $response = $customer->sources->retrieve($card_id)->delete();
		}catch (Exception $e) {
			
			$e_json = $e->getJsonBody();
			return $error = $e_json['error'];
		}		
	}

     /**
	  * retrieve the card
      *  @param String customer_id, String card_id			
	  *  @return Card object
	  */
     
     public function retrieve_card($customer_id, $card_id,$payment_reference='ninja',$stripe_envir=''){
		try{
			$this->ci()->config->load('stripe');
			$api_key = $this->ci()->config->item($payment_reference.'_stripe_api_key_'.$stripe_envir);
			$currency_code = $this->ci()->config->item($payment_reference.'_currency_code');
			
			\Stripe\Stripe::setApiKey($api_key);
			$customer = \Stripe\Customer::retrieve($customer_id);
			return $response = $customer->sources->retrieve($card_id);
		}catch (Exception $e) {
			$e_json = $e->getJsonBody();
			return $error = $e_json['error'];
		}			
	}
     /**
	  * get all cards
      *  @param customer_id -String 
	  *  @return Card object
	  */
     public function get_all_cards($customer_id,$payment_reference='ninja',$stripe_envir=''){
		try{
			$this->ci()->config->load('stripe');
			$api_key = $this->ci()->config->item($payment_reference.'_stripe_api_key_'.$stripe_envir);
			$currency_code = $this->ci()->config->item($payment_reference.'_currency_code');
			
			\Stripe\Stripe::setApiKey($api_key);
			$response = \Stripe\Customer::retrieve($customer_id)->sources->all(array(
			"object" => "card"
			));
		}catch (Exception $e) {	
			$e_json = $e->getJsonBody();
			return $error = $e_json['error'];
		}		
	 }

   /**
     * Refund  Amount
	 * @param Array
	 * @param['chargeid'] String
	 * @param['amount'] A positive integer in cents representing how much of this charge to refund. 
	 * Can only refund up to the unrefunded amount  remaining of the charge.
	 * @param['reason']  String -Optional
	 * 
     */
     public function refund($refund,$payment_reference='ninja',$stripe_envir=''){
		try{
			$this->ci()->config->load('stripe');
			$api_key = $this->ci()->config->item($payment_reference.'_stripe_api_key_'.$stripe_envir);
			$currency_code = $this->ci()->config->item($payment_reference.'_currency_code');
			
			\Stripe\Stripe::setApiKey($api_key);
			$response = \Stripe\Refund::create(array(
				"charge" => $refund['charge'],
				"amount" => $refund['amount']*100,
				"reason" => isset($refund['reason'])?$refund['reason']:null
			));
			return $response;
		}catch (Exception $e) {
			$e_json = $e->getJsonBody();
			return $error = $e_json['error'];
		}		 
	 }
	 
    /**
	  * Create Coupon code
	  * @praram array
	  * @param['duration']  Can be forever, once, or repeating. - Required
	 
	  * @param['amount_off'] Integer 							- Required if percent_off is not passed
	  * @param['currency']										- Required if amount_off is passed
	  * @param['duration_in_months']							- Required only if duration is repeating
	  * @param['percent_off']  Integer 1-100					- Required if amount_off is not passed
	  
	  * @param['id'] Unique String  							- Optional
	  * @param['max_redemptions'] - Integer -Specifying the 
	  *							number of times the coupon can  - Optional
	  *							be valid.
	  * @param[redeem_by]  - Unix timestamp                     - Optional             
	  *
	  */
	  
	  public function create_coupon($coupon,$payment_reference='ninja',$stripe_envir=''){
		  try{
				$this->ci()->config->load('stripe');
				$api_key = $this->ci()->config->item($payment_reference.'_stripe_api_key_'.$stripe_envir);
				$currency_code = $this->ci()->config->item($payment_reference.'_currency_code');
				
				\Stripe\Stripe::setApiKey($api_key);
				$data = array(
				 "duration" => $coupon['duration'],				  
				 "id" => isset($coupon['id'])?$coupon['id']:null
				);
				
				if(isset($coupon['amount_off']) && (int) $coupon['amount_off']>0){
					$data['amount_off'] = (int) $coupon['amount_off'];					
				}
				if(isset($coupon['percent_off']) && (int) $coupon['percent_off']>0){
					$data['percent_off'] = (int) $coupon['percent_off'];					
				}
				
				if(isset($coupon['max_redemptions']) && (int) $coupon['max_redemptions']>0){
					$data['max_redemptions'] = (int) $coupon['max_redemptions'];
				}				
				if(isset($coupon['redeem_by']) && $coupon['redeem_by']){
					$data['redeem_by'] = $coupon['redeem_by'];
				}
				if(isset($coupon['duration_in_months']) && $coupon['duration_in_months']){
					$data['duration_in_months'] = $coupon['duration_in_months'];
				}
				if(isset($coupon['currency']) && $coupon['currency']){
					$data['currency'] = $coupon['currency'];
				}
				
				return $response = \Stripe\Coupon::create($data);
			}catch (Exception $e) {				
				$e_json = $e->getJsonBody();
				return $error = $e_json['error'];
			}	
	  }
	  
	  
	 /**
	  *  retrieve the InvoiceId
      *  @param String invoice_id			
	  *  @return Invoice object
	  */
     
     public function retrieve_invoice($invoice_id,$payment_reference='ninja',$stripe_envir=''){
		try{
			$this->ci()->config->load('stripe');
			$api_key = $this->ci()->config->item($payment_reference.'_stripe_api_key_'.$stripe_envir);
			$currency_code = $this->ci()->config->item($payment_reference.'_currency_code');
			
			\Stripe\Stripe::setApiKey($api_key);
			return  $response = \Stripe\Invoice::retrieve($invoice_id);			
		}catch (Exception $e) {
			$e_json = $e->getJsonBody();
			return $error = $e_json['error'];
		}		
	} 
	
	/**
	  *  retrieve the InvoiceId
      *  @param String invoice_id			
	  *  @return Invoice object
	  */
     
     public function retrieve_invoice_customer($customer_id,$limit,$starting_after,$filter= array(), $payment_reference='ninja',$stripe_envir=''){
		try{
			$this->ci()->config->load('stripe');
			$api_key = $this->ci()->config->item($payment_reference.'_stripe_api_key_'.$stripe_envir);
			$currency_code = $this->ci()->config->item($payment_reference.'_currency_code');
			
			$invoice_filter['customer'] = $customer_id;
			if($limit !='')
			{
				$invoice_filter['limit'] = $limit;
			}
			if($starting_after !='')
			{
				$invoice_filter['starting_after'] = $starting_after;
			}
			
			if(!empty($filter ))
			{
				foreach($filter as $fkey=>$fval) {
					$invoice_filter[$fkey] = $fval;
				}
			}
			
			\Stripe\Stripe::setApiKey($api_key);
			return $response = \Stripe\Invoice::all($invoice_filter);		
		}catch (Exception $e) {
			$e_json = $e->getJsonBody();
			return $error = $e_json['error'];
		}		
	}
	
	public function retrieve_proration_amount($customer_id,$subscription_id,$new_plan,$proration_date,$payment_reference='ninja',$stripe_envir=''){
		  try{
				$this->ci()->config->load('stripe');
				$api_key = $this->ci()->config->item($payment_reference.'_stripe_api_key_'.$stripe_envir);
				$currency_code = $this->ci()->config->item($payment_reference.'_currency_code');
				
				\Stripe\Stripe::setApiKey($api_key);
			     $invoice = \Stripe\Invoice::upcoming(array(
				  "customer" => $customer_id,
				  "subscription" => $subscription_id,
				  "subscription_plan" => $new_plan, # Switch to new plan
				  "subscription_proration_date" => $proration_date
				));
				// Calculate the proration cost:
				$cost = 0;
				$invoice_item = 0;
				$current_prorations = array();
				
				
				foreach ($invoice->lines->data as $line) {
				  if ($line->period->start == $proration_date) {
					array_push($current_prorations, $line);
					$cost += $line->amount;
					$invoice_item++;
				  }
				}
				return array("cost"=> $cost, 'invoice'=>$invoice_item);
		  }catch (Exception $e) {
				$e_json = $e->getJsonBody();
			return $error = $e_json['error'];
		  }
	}
	
	/**
	  *  retrieve the Event
      *  @param String event_id			
	  *  @return Event object
	  */
	
	public function retrieve_event($id,$payment_reference='ninja',$stripe_envir=''){
		if($id){
			try{
				$this->ci()->config->load('stripe');
				$api_key = $this->ci()->config->item($payment_reference.'_stripe_api_key_'.$stripe_envir);
				$currency_code = $this->ci()->config->item($payment_reference.'_currency_code');
				
				
				\Stripe\Stripe::setApiKey($api_key);
				return $response = \Stripe\Event::retrieve($id);
			}catch (Exception $e) {
					$e_json = $e->getJsonBody();
				return $error = $e_json['error'];
			}
		}else{
			return $error= array("message" => "No such event");
		}
	}
}
