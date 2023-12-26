<?php

if (! defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 *
 * Created: 06.03.2018
 */
class Reddotpay
{

    /**
     * CodeIgniter global
     *
     * @var string
     *
     */
    protected $ci;

    public function __construct()
    {
        $this->ci = & get_instance();
        $this->ci->config->load('reddot');
    }

    function reddotPayment($requestData=null,$appName=null,$payMode=null)
    {
		
		if(!empty($requestData)) {
			
			$appNametxt = $this->ci->config->item($appName.'_check');
			if($appNametxt == $appName) {
				$ptmmode = $this->ci->config->item($appName.'_api_mode_'.$payMode);
				if($ptmmode == $payMode) {
					$requestData['mid'] 	 	 = $this->ci->config->item($appName.'_merchant_id_'.$ptmmode);
					$requestData['ccy'] 	 	 = $this->ci->config->item($appName.'_currency_code');
					$requestData['api_mode'] 	 = "direct_n3d";
					$requestData['payment_type'] = "S";
					
					$secretkeyTxt = $this->ci->config->item($appName.'_secret_key_'.$ptmmode);
					
					$signature = $this->signPaymentRequest($secretkeyTxt, $requestData);
					$requestData['signature'] = $signature;
					$jsonRequest = json_encode($requestData);
					
					$response = $this->makePayment($jsonRequest,$appName,$ptmmode);
				} else {
					$response = array('status'=>'error','message'=>'Pay Mode was wrong.','responseData'=>array());
				}
			} else {
				$response = array('status'=>'error','message'=>'App Name was wrong.','responseData'=>array());
			}
			
		} else {
			$response = array('status'=>'error','message'=>'Request data should not empty.','responseData'=>array());
		}
		
        return $response; 
		
    }
	
	function signPaymentRequest($secret_key, $params) {
		$fields_for_sign = array('mid', 'order_id', 'payment_type','amount', 'ccy');
		if (isset($params['payer_id'])) {
			$fields_for_sign[] = 'payer_id';
		}
		$aggregated_field_str = "";
		foreach ($fields_for_sign as $f) {
		$aggregated_field_str .= trim($params[$f]);
		}
		if ($params['api_mode'] == 'direct_n3d' || $params['api_mode'] == 'redirection_n3d') {
			$pan = '';
			if (isset($params['card_no'])) {
			$pan = trim($params['card_no']);
			} else if (isset($params['token_id'])) {
			$pan = trim($params['token_id']);
			}
			
			$first_6 = substr($pan, 0, 6);
			$last_4 = substr($pan, -4);
			$aggregated_field_str .= $first_6 . $last_4;
			if (isset($params['exp_date'])) {
			$aggregated_field_str .=
			trim($params['exp_date']);
			}
			
			if (isset($params['cvv2'])) {
			$cvv2 = trim($params['cvv2']);
			$last_digit_cvv2 = substr($cvv2, -1);
			$aggregated_field_str .= $last_digit_cvv2;
			}
		}
		$aggregated_field_str .= $secret_key;
		$signature = hash('sha512', $aggregated_field_str);
		return $signature;
	}
	
	function makePayment($jsonRequest,$appName=null,$ptmmode=null) {
		
		$url = $this->ci->config->item($appName.'_payment_'.$ptmmode);
		
		$curl = curl_init($url);
		curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_POST => 1,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_POSTFIELDS => $jsonRequest,
		CURLOPT_HTTPHEADER => array('Content-Type: application/json')
		));
		$response = curl_exec($curl);
		$curl_errno = curl_errno($curl);
		$curl_err = curl_error($curl);
		curl_close($curl);
		
		$responseArray = json_decode($response, true);
		
		if((!isset($responseArray['response_status'])) && ($responseArray['response_msg'] == 'successful') && ((int)$responseArray['response_code'] == 0)){
			$result = array('status'=>'success','message'=>$responseArray['response_msg'],'responseData'=>$responseArray);
		} else {
		  
		    $result = array('status'=>'error','message'=> (isset($responseArray['acquirer_response_msg'])? $responseArray['acquirer_response_msg'] : $responseArray['response_msg']),'responseData'=>$responseArray);
		}
		
		return $result;
	}
	
	function reddotRedirectPay($requestData=null,$appName=null,$payMode=null,$cust_id=null)
    {
		
		if(!empty($requestData)) {
			
			$appNametxt = $this->ci->config->item($appName.'_check');
			if($appNametxt == $appName) {
				$ptmmode = $this->ci->config->item($appName.'_api_mode_'.$payMode);
				if($ptmmode == $payMode) {
					
					$reftxt = $appName."-".$cust_id."-".$payMode;
					$paramTxt = "?reftxt=".$reftxt;
					
					$redirectUrl = $this->ci->config->item($appName.'_redirect_url').$paramTxt;
					$notifyUrl = $this->ci->config->item($appName.'_notify_url');
					$backUrl = $this->ci->config->item($appName.'_back_url').$paramTxt;
					
					$merchantId = $this->ci->config->item($appName.'_merchant_id_'.$ptmmode);
					
					$requestData['redirect_url'] = $redirectUrl;
					$requestData['notify_url'] 	 = $notifyUrl;
					$requestData['back_url'] 	 = $backUrl;
					$requestData['ccy'] 	 	 = $this->ci->config->item($appName.'_currency_code');
					$requestData['mid'] 	 	 = $merchantId;
					$requestData['api_mode'] 	 = "redirection_hosted";
					$requestData['payment_type'] = "S";
					
					/*$requestData['merchant_reference'] = "Spize Reference";*/
					$requestData['merchant_reference'] = $reftxt;
					
					$secretkeyTxt = $this->ci->config->item($appName.'_secret_key_'.$ptmmode);
					
					$signature = $this->signRedirectPayRequest($secretkeyTxt, $requestData);
					
					$requestData['signature'] = $signature;
					$jsonRequest = json_encode($requestData);
					
					$response = $this->makeRedirectPay($jsonRequest,$appName,$ptmmode);
				} else {
					$response = array('status'=>'error','message'=>'Pay Mode was wrong.','responseData'=>array());
				}
			} else {
				$response = array('status'=>'error','message'=>'App Name was wrong.','responseData'=>array());
			}
		} else {
			$response = array('status'=>'error','message'=>'Request data should not empty.','responseData'=>array());
		}
		
        return $response; 
    }
	
	function signRedirectPayRequest($secret_key, $params) {
	   $fields_for_sign = array('mid', 'order_id', 'payment_type', 'amount', 'ccy');

	   $aggregated_field_str = "";
	   foreach ($fields_for_sign as $f) {
		  $aggregated_field_str .= trim($params[$f]);
	   }

	   $aggregated_field_str .= $secret_key;
	   $signature = hash('sha512', $aggregated_field_str);
	 
	   return $signature;
	}
	
	function makeRedirectPay($jsonRequest,$appName=null,$ptmmode=null) {
		
		
		$url = $this->ci->config->item($appName.'_payment_'.$ptmmode);
		
		$curl = curl_init($url);
		curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_POST => 1,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_POSTFIELDS => $jsonRequest,
		CURLOPT_HTTPHEADER => array('Content-Type: application/json')
		));
		$response = curl_exec($curl);
		$curl_errno = curl_errno($curl);
		$curl_err = curl_error($curl);
		curl_close($curl);
		
		$responseArray = json_decode($response, true);
		
		if((!empty($responseArray)) && (isset($responseArray['payment_url'])) && (isset($responseArray['signature']))){
			/*header('Location: '.$responseArray['payment_url']);
			exit;*/
			$result = array('status'=>'success','paymentUrl'=>$responseArray['payment_url'],'responseData'=>$responseArray,'requestData'=>$jsonRequest);
		} else {
		  
		    $result = array('status'=>'error','message'=> (isset($responseArray['acquirer_response_msg'])? $responseArray['acquirer_response_msg'] : $responseArray['response_msg']),'responseData'=>$responseArray,'requestData'=>$jsonRequest);
		}
		
		return $result;
	}
	
	function getTransactionById($transaction_id,$appName=NULL,$ptmmode=NULL) {
		
		$requestData = array(
		   'request_mid' => $this->ci->config->item($appName.'_merchant_id_'.$ptmmode),
		   'transaction_id' => $transaction_id
		);
		
		$secretkeyTxt = $this->ci->config->item($appName.'_secret_key_'.$ptmmode);
		
		$signature = $this->signRedirectTrans($secretkeyTxt, $requestData);
			
		$requestData['signature'] = $signature;
		$jsonRequest = json_encode($requestData);
		
		$url = $this->ci->config->item($appName.'_gettrans_'.$ptmmode);
		
		$curl = curl_init($url);
		curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_POST => 1,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_POSTFIELDS => $jsonRequest,
		CURLOPT_HTTPHEADER => array('Content-Type: application/json')
		));
		$response = curl_exec($curl);
		$curl_errno = curl_errno($curl);
		$curl_err = curl_error($curl);
		curl_close($curl);
		
		$responseArray = json_decode($response, true);
		
		if((!empty($responseArray)) && ($responseArray['response_msg']=='successful') && ($responseArray['transaction_id'] == $transaction_id)){
			$result = array('status'=>'success','message'=>$responseArray['acquirer_response_msg'],'responseData'=>$responseArray,'requestData'=>$jsonRequest);
		} else {
		    $result = array('status'=>'error','message'=> (isset($responseArray['acquirer_response_msg'])? $responseArray['acquirer_response_msg'] : $responseArray['response_msg']),'responseData'=>$responseArray,'requestData'=>$jsonRequest);
		}
		
		return $result;
	}
	
	function signRedirectTrans($secret_key, $params) {
		unset($params['signature']);
		$data_to_sign = "";
		$this->recursive_generic_array_sign($params, $data_to_sign);
		$data_to_sign .= $secret_key;
		$signature = hash('sha512', $data_to_sign);
		return $signature;
	}

	function recursive_generic_array_sign(&$params, &$data_to_sign)
	{
		ksort($params);
		foreach ($params as $v) {
			if (is_array($v)) {
				$this->recursive_generic_array_sign($v, $data_to_sign);
			}
			else {
				$data_to_sign .= $v;
			}
		}
	}

}
