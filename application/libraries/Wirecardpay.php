<?php

if (! defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 *
 * Created: 06.03.2018
 */
class Wirecardpay
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
        $this->ci->config->load('wirecard');
    }

    function wirecardRedirectPay($requestData=null,$appName=null,$payMode=null,$cust_id=null)
    {
		
		if(!empty($requestData)) {
			
			$appNametxt = $this->ci->config->item($appName.'_check');
			if($appNametxt == $appName) {
				$ptmmode = $this->ci->config->item($appName.'_api_mode_'.$payMode);
				if($ptmmode == $payMode) {
					
					date_default_timezone_set('Asia/Singapore');
					
					$validity 		= date('Y-m-d-H:i:s', strtotime("+5 min"));

					$merchantId 	= $this->ci->config->item($appName.'_merchant_id_'.$ptmmode);
					$secretKey 		= $this->ci->config->item($appName.'_secret_key_'.$ptmmode);
					$currencyCode 	= $this->ci->config->item($appName.'_currency_code');
					
					$orderRef 		= $requestData['order_id'];
					$amount 		= $requestData['amount'];
					$transType 		= 'sale';
					
					$secretSeqData 	= $amount.$orderRef.$currencyCode.$merchantId.$transType.$secretKey;
					$signature 		= hash('sha512', $secretSeqData);
					
					$returnUrl 		= $this->ci->config->item($appName.'_return_url');
					$statusUrl 		= $this->ci->config->item($appName.'_status_url');
					$returnUrl		= $returnUrl.'/'.$requestData['merchant_reference'];
					
					$url = $this->ci->config->item($appName.'_payment_'.$ptmmode);
			
					$paymentUrl = $url.'?mid='.$merchantId.'&ref='.$orderRef.'&amt='.$amount.'&cur='.$currencyCode.'&rcard=64&transtype='.$transType.'&returnurl='.$returnUrl.'&statusurl='.$statusUrl.'&version=2&validity='.$validity.'&signature='.$signature;
					
					$requestLog					= array();
					$requestLog['merchant_ref'] = $requestData['merchant_reference'];
					$requestLog['merchantId'] 	= $merchantId;
					$requestLog['secretKey'] 	= $secretKey;
					$requestLog['currencyCode'] = $currencyCode;
					$requestLog['orderRef'] 	= $orderRef;
					$requestLog['amount'] 		= $amount;
					$requestLog['transtype']	= $transType;
					$requestLog['secretSeqData']= $secretSeqData;
					$requestLog['signature'] 	= $signature;
					$requestLog['returnUrl'] 	= $returnUrl;
					$requestLog['statusUrl'] 	= $statusUrl;
					$requestLog['ptmmode'] 		= $ptmmode;
					$requestLog['paymentUrl'] 	= $paymentUrl;
					
					$response = array('status'=>'success','message'=>'Payment Url has been made successfully.','responseData'=>$requestLog,'paymentUrl'=>$paymentUrl);
					
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

}
