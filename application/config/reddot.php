<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/* Configuration for red dot payment - spize App*/

$config['spize_check'] = 'spize';

/* spize App - Test Mode*/
$config['spize_secret_key_test'] = "ouddymfyIb7KCxZbdlm334faGJsuCDX8RuOh5M9B9MhvG4DpAbUrFJhJzs434Rl8og52NG14bRB1IEqjDKAzLC3pcjdzbth4GOOOO2Vtqmof1rTA7ZB3p836WFUpuU6i";
$config['spize_merchant_id_test'] = "1007777937";
$config['spize_api_mode_test'] = "test"; 
$config['spize_payment_test'] = "https://secure-dev.reddotpayment.com/service/payment-api";
$getTrans_test = 'https://secure-dev.reddotpayment.com/service/Merchant_processor/query_redirection';
$config['spize_gettrans_test'] = $getTrans_test;

/* spize App - Live Mode*/
$config['spize_secret_key_live'] = "6cLd5sihnbSCXVlrFeRVjFwpshpuCWd5nsYZB447ByW6x107mdrHhnAdjYAUFsC2QVmQEZqkRzPXMAJawCI2rGK5e2S9imdEV7pAkk5EwyoGWLWMzAtoVkGjc4EZPnKr";
$config['spize_merchant_id_live'] = "0000023007";
$config['spize_api_mode_live'] = "live"; 
$config['spize_payment_live'] = "https://secure.reddotpayment.com/service/payment-api";
$getTrans_live = 'https://secure.reddotpayment.com/service/Merchant_processor/query_redirection';
$config['spize_gettrans_live'] = $getTrans_live;

/* spize App - Test && Live Mode*/
$config['spize_currency_code'] = "SGD";
$config['spize_redirect_url'] = "http://ccpl.ninjaos.com/account/reddotsuccess";
$config['spize_notify_url'] = "http://ccpl.ninjaos.com/account/reddotnotify";
$config['spize_back_url'] = "http://ccpl.ninjaos.com/account/reddotfailure";

/* spize App End*/

