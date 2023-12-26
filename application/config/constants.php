<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);


//define ("COMMON_PUSH_APPS", serialize (array ("F60DC85C-6801-4536-8102-65D9A8666940")));


/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESCTRUCTIVE') OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

/* developer created constants */
define('API_URL','718B1A92-5EBB-4F25-B24D-3067606F67F0/');

define('DELIVERY_ID','634E6FA8-8DAF-4046-A494-FFC1FCF8BD11');
define('PICKUP_ID','718B1A92-5EBB-4F25-B24D-3067606F67F0');
define('DINEIN_ID','EF9FB350-4FD4-4894-9381-3E859AB74019');
define('CATERING_ID','EB62AF63-0410-47CC-9464-038E796E28C4');
define('RESERVATION_ID','79FA4C7F-75A1-4A95-B7CE-81ECA2575363');
define('EVENTS_ID','AF70EE93-2B8B-474D-9078-044F259637F3');
define('BENTO_ID','7B30BB03-14BD-47E4-B9B1-9731F9A3BC9C');
define('MAD_BAR_ID','471745F6-0AEC-4641-9802-6DA1968D5D79');

/* Default country*/
define('DEFAULT_COUNTRY','Singapore');

/* custon  values */

define('PASSWORD_LENGTH',6);
define('PERMISSION_MODULE_ID',14);
define('DEFAULT_FOLDER','dev_team');


/*google map key*/
define('MAPKEY','AIzaSyCdP-_7USWtwCr6L0HFIHEJqUZJmea9c4E');

/*App Name for push notifi*/
define ("COMMON_PUSH_APPS", serialize (array ("F60DC85C-6801-4536-8102-65D9A8666940","F2442DB2-9852-4B33-AF11-B96DB1CD2D44")));

/*Georges*/
define('georges_min_spend',500); //Upgrade membership

/*Nelsonbar min spend*/
define('nelsonbar_min_spend',600); //Upgrade membership


/* Callcenter catering*/
define('PDF_SOURCE','http://marketplace.goepicware.com/media/order-pdf/'); 
define('IAMGE_SOURCE','http://marketplace.goepicware.com/media/dev_team/'); 
define('PRODUCT_THUMP','products/main-image/');
define('PRODUCT_GALLERY','products/gallery-image/');
define('SUBCATEGORY_GALLERY','products/subcategory-image/');
define('CATEGARY_IMAGE','products/category-image/');
define('TESTIMONIAL','testimonial/');
define('BRAND_TAG','brandtag/');
define('TAG','tag/');


/* Customer jwt*/
define('CUSTOMER_KEY','XuVjHfuhnNSieGrXH2dW0KPJunlc2kBH');
define('CUSTOMER_SECRET','nti4wW0TGMnFWfZ2qTPPdU4B2jutwMbY');
define('CUSTOMER_TTL',7200000); //In seconds

define('MAPAPI_LINK','https://developers.onemap.sg/commonapi/search?returnGeom=Y&getAddrDetails=Y&searchVal=');
define('UVCR_LINK','https://sandboxapinero.uvcr.me/');
