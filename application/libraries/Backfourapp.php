<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once('parse-php-sdk/autoload.php');

use Parse\ParseClient;
use Parse\ParsePush;

use Parse\ParseObject;
use Parse\ParseQuery;
use Parse\ParseACL;
use Parse\ParseUser;
use Parse\ParseInstallation;
use Parse\ParseException;
use Parse\ParseAnalytics;
use Parse\ParseFile;
use Parse\ParseCloud;

use Parse\HttpClients\ParseCurlHttpClient;
use Parse\HttpClients\ParseStreamHttpClient;

class Backfourapp {

	 public function __construct() {

		$this->url = 'https://api.parse.com/1.4/push';

		$this->app_id = 'vp8n2TqHPj7CBiSCBHecJPi9axRU8IQcpUU0phXr';
		$this->rest_key = 'ufSNtb62Ntm4Xtn9QhG1RTv5K5VDMTEYErVm3wWv';
		$this->master_key = '8zg9klTLf0AkrOPQGKroLGNpjA1vukYTtMzabN4o';

	}

	/*Credential and channel Details*/
	private function getConfigDetails($app_id)
	{

		$config_arr = array(
		//Georges
		'FCACF7F4-AF83-4EFC-9445-83C270FD1AC2' => array(
		'bapp_app_id'=>'vp8n2TqHPj7CBiSCBHecJPi9axRU8IQcpUU0phXr',
		'bapp_rest_key'=>'ufSNtb62Ntm4Xtn9QhG1RTv5K5VDMTEYErVm3wWv',
		'bapp_master_key'=>'8zg9klTLf0AkrOPQGKroLGNpjA1vukYTtMzabN4o',
		'bapp_channel_name'=>'Georges',
		),
		//Spize
		'F60DC85C-6801-4536-8102-65D9A8666940' => array(
		'bapp_app_id'=>'f2EdwVDFAuMya1akvDDtljbcFpVuuIWmrIYJd2Pb',
		'bapp_rest_key'=>'nHzEyr6zKKUvMV04bMciDJo0PbxvJbWkQWU3hCth',
		'bapp_master_key'=>'xmH7ZzSJumNgQsEN9egmPFdkCEnWMdUGutEEOiSd',
		'bapp_channel_name'=>'Spize',
		),
		//Nelsonbar
		'F2442DB2-9852-4B33-AF11-B96DB1CD2D44' => array(
		'bapp_app_id'=>'Bc2d89m5Ro0MF6mQ75L1Mr0mP57aTtqqu16OYs8G',
		'bapp_rest_key'=>'hMCQiXWtPtJXQ6wBLk6dYejt67orX7rAbY42sdZ9',
		'bapp_master_key'=>'LtB9kuN26qAOy2rGgO2R8p18rXTsEWgp2L8H9Vdw',
		'bapp_channel_name'=>'NelsonBar',
		),

		);

		if(array_key_exists($app_id,$config_arr)) {
			return $config_arr[$app_id];
		} else {
			return array();
		}
	
	}

	public function send_to_channel($data,$app_id)
	{

		$url = $this->url;
		$response = '';
		$config_arr = $this->getConfigDetails($app_id);

		if(!empty($config_arr)) {

			$app_id = $this->app_id;
			$rest_key = $this->rest_key;
			$master_key = $this->master_key;

			ParseClient::initialize( $config_arr['bapp_app_id'], $config_arr['bapp_rest_key'], $config_arr['bapp_master_key'] );

			ParseClient::setServerURL('https://parseapi.back4app.com','/');

			/*Using channels*/
			$query = ParseInstallation::query();

			$query->equalTo("channels", $config_arr['bapp_channel_name']);

			$response = ParsePush::send(array(
			"where" => $query,
			"data" => $data
			), true);

		}

		return $response;

	}

	/*Sent notificationindividual device*/
	public function send_to_device($deviceid,$data,$app_id)
	{
		$url = $this->url;

		$response = '';
		$config_arr = $this->getConfigDetails($app_id);

		if(!empty($config_arr)) {

		$app_id = $this->app_id;
		$rest_key = $this->rest_key;
		$master_key = $this->master_key;

		ParseClient::initialize( $config_arr['bapp_app_id'], $config_arr['bapp_rest_key'], $config_arr['bapp_master_key'] );

		ParseClient::setServerURL('https://parseapi.back4app.com','/');

		/*Using channels*/
		$query = ParseInstallation::query();
		$query->equalTo("deviceToken",$deviceid);

		$response = ParsePush::send(array(
		"where" => $query,
		"data" => $data
		), true);
		
		}

		return $response;

	}

}
