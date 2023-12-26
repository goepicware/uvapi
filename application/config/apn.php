<?php
/*
|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
|| Apple Push Notification Configurations
|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
*/


/*
|--------------------------------------------------------------------------
| APN Permission file 
|--------------------------------------------------------------------------
|
| Contains the certificate and private key, will end with .pem
| Full server path to this file is required.
|
*/
/*
$config['PermissionFile'] = realpath('./application/config').'/copperpro.pem';
$config['PassPhrase'] = 'cc@123';
*/
$config['PermissionFile'] = realpath('./application/config').'/ninjabiz.pem';
$config['PassPhrase'] = 'ninja@123';

$config['PermissionFile_georges'] = realpath('./application/config').'/GeorgesProduction.pem';
$config['PassPhrase_georges'] = 'georges@456';

$config['PermissionFile_spize'] = realpath('./application/config').'/spizePro.pem';
$config['PassPhrase_spize'] = 'spize@456';

$config['PermissionFile_nelsonbar'] = realpath('./application/config').'/NelsonProduction.pem';
$config['PassPhrase_nelsonbar'] = 'nelson@456';
/*
|--------------------------------------------------------------------------
| APN Services
|--------------------------------------------------------------------------
*/
$config['Sandbox'] = false;
/*development gate way*/
$config['PushGatewaySandbox'] = 'ssl://gateway.sandbox.push.apple.com:2195';

/*production gate way*/
$config['PushGateway'] = 'ssl://gateway.push.apple.com:2195';

$config['FeedbackGatewaySandbox'] = 'ssl://feedback.sandbox.push.apple.com:2196';
$config['FeedbackGateway'] = 'ssl://feedback.push.apple.com:2196';

/*
|--------------------------------------------------------------------------
| APN Connection Timeout
|--------------------------------------------------------------------------
*/
$config['Timeout'] = 60;


/*
|--------------------------------------------------------------------------
| APN Notification Expiry (seconds)
|--------------------------------------------------------------------------
| default: 86400 - one day
*/
$config['Expiry'] = 86400;
