<?php
/**************************
Project Name	: Ninjaos
Created on		: 30-1-2020
Last Modified 	: 30-1-2020
Description		: cybersource payment gatway
***************************/

define ('HMAC_SHA256', 'sha256');
/*define ('SECRET_KEY', 'b4e003a60fc647b186e1f9f1db94bdeb68e0e0183ec24c5b95e99848ab4bb9887b5150cc7fd14992a7865d8d06742dd5d0a4d80f6e6f4ae79ddf55772047bc11c7309295fab944feae7768a2b3b0057776fe667c4bb1447c89b54ac11915dd928c49eb2265724d3db700c5e9d79910a76351b2cf52ee4f05a0b9d2c77587eb8a');*/
define ('SECRET_KEY', 'ef02f2c686e649a2bdf7b990c646486b3b981bc6f0fe417cac04a56cf85b2b902f063c06e89040e8aae906c1fdd8bb65f1570e7c2f6f43a99d4b6475a5e426fe07c093c06d9649e59f07a0cae3fc4a11de45489753b74b6c8a2f175ab0bba486dbfc096e50c14e198b55a6b8383893cb2899da1d87274a24a86a6ebdba0ac637');

if (! function_exists ( 'get_signature_value' )) {

	function get_signature_value($params) {
		return signData(buildDataToSign($params), SECRET_KEY);
	}
	
	function signData($data, $secretKey) {
		return base64_encode(hash_hmac('sha256', $data, $secretKey, true));
	}

	function buildDataToSign($params) {
			$signedFieldNames = explode(",",$params["signed_field_names"]);
			foreach ($signedFieldNames as $field) {
			   $dataToSign[] = $field . "=" . $params[$field];
			}
			return commaSeparate($dataToSign);
	}

	function commaSeparate ($dataToSign) {
		return implode(",",$dataToSign);
	}
}
