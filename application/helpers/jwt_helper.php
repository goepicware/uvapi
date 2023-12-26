<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// create admin token
if(!function_exists('create_jwt'))
{
    function create_jwt($user_info)
    {
        $CI =& get_instance();
        $CI->load->library('JWT');
        $token = $CI->jwt->encode(array(
            'consumerKey' => CONSUMER_KEY,
            'user_details' => $user_info,
            'issuedAt' => date(DATE_ISO8601, strtotime("now")),
            'ttl' => CONSUMER_TTL
        ), CONSUMER_SECRET);
        return $token;
    }
}

    // validate admin user token
if(!function_exists('validate_jwt'))
{
    function validate_jwt($token)
    {
        $CI =& get_instance();
        $CI->load->library('JWT');
        try {

            $decodeToken = $CI->jwt->decode($token, CONSUMER_SECRET);          
            // validate token is not expired
            $ttl_time = strtotime($decodeToken->issuedAt);
            $now_time = strtotime(date(DATE_ISO8601, strtotime("now")));

            if(($now_time - $ttl_time) > $decodeToken->ttl) {
                throw new Exception('Expired');
            } else {
                $user_id = $decodeToken->user_details->user_id;

                $user_info = $CI->Mydb->get_record('jwt_token', 'admin_users', array('admin_id'=>$user_id));
                
                if(!empty($user_info) && $token == $user_info['jwt_token']) {
                    return true;
                }

                return false;
            }

        } catch (Exception $e) {
            return false;
        }

    }
}

    // decode admin user token
if(!function_exists('decode_jwt'))
{
    function decode_jwt($token)
    {
        $CI =& get_instance();
        $CI->load->library('JWT');
        try {
            $decodeToken = $CI->jwt->decode($token, CONSUMER_SECRET);
            return $decodeToken;
        } catch (Exception $e) {
            return false;
        }
    }
}

   // create customer token
if(!function_exists('create_jwt_front'))
{
    function create_jwt_front($customer_info)
    {
        $CI =& get_instance();
        $CI->load->library('JWT');
        $token = $CI->jwt->encode(array(
            'customerkey' => CUSTOMER_KEY,
            'customer_details' => $customer_info,
            'issuedAt' => date(DATE_ISO8601, strtotime("now")),
            'ttl' => CUSTOMER_TTL
        ), CUSTOMER_SECRET);
        return $token;
    }
}

// validate customer token
if(!function_exists('validate_jwt_front'))
{
    function validate_jwt_front($token)
    {
        $CI =& get_instance();
        $CI->load->library('JWT');
        try {

            $decodeToken = $CI->jwt->decode($token, CUSTOMER_SECRET);          
            // validate token is not expired
            $ttl_time = strtotime($decodeToken->issuedAt);
            $now_time = strtotime(date(DATE_ISO8601, strtotime("now")));

            if(($now_time - $ttl_time) > $decodeToken->ttl) {
                throw new Exception('Expired');
            } else {
                return true;
                $customer_id = $decodeToken->customer_details->customer_id;

                $customer_info = $CI->Mydb->get_record('customer_jwt_token', 'customer', array('customer_id'=>$customer_id));
                
                if(!empty($customer_info) && $token == $customer_info['customer_jwt_token']) {
                    return true;
                }

                return false;
            }

        } catch (Exception $e) {
            return false;
        }

    }
}

// decode customer token
if(!function_exists('decode_jwt_front'))
{
    function decode_jwt_front($token)
    {
        $CI =& get_instance();
        $CI->load->library('JWT');
        try {
            $decodeToken = $CI->jwt->decode($token, CUSTOMER_SECRET);
            return $decodeToken;
        } catch (Exception $e) {
            return false;
        }
    }
}

