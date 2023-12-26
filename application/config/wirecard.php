<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/* Configuration for wirecard(DBS) payment - spize App*/

$config['spize_check'] = 'spize';

/* spize App - Test Mode*/
$config['spize_secret_key_test'] = "ABC123456";
$config['spize_merchant_id_test'] = "20151111011";
$config['spize_api_mode_test'] = "test"; 
$config['spize_payment_test'] = "https://test.wirecard.com.sg/easypay2/paymentpage.do";

/* spize App - Live Mode*/
$config['spize_secret_key_live'] = "6Vp7Hp3Lr";
$config['spize_merchant_id_live'] = "111201950741";
$config['spize_api_mode_live'] = "live"; 
$config['spize_payment_live'] = "https://api.wirecard.com.sg/easypay2/paymentpage.do";

/* spize App - Test && Live Mode*/
$config['spize_currency_code'] = "SGD";
$config['spize_return_url'] = "https://ccpl.ninjaos.com/account/wirecardreturn";
$config['spize_status_url'] = "https://ccpl.ninjaos.com/account/wirecardstatus";

/* spize App End*/