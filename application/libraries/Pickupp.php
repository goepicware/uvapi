<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Pickupp
{
    protected $ci;

    public $api_base_url;

    public $x_api_key;

    public function __construct()
    {
        /*initialize the CI super-object*/

        $this->ci = &get_instance();

        /* $this->api_base_url = 'https://gateway-uat.hk.pickupp.io/v2/merchant/';

        $this->x_api_token = 'bmluamFvc0BwaWNrdXBwLmlvOmQ0YzcyYjM4NTdmYWY5NDc3MzUzNDMzZjE0MGRjMWVi'; */

        $this->ci->load->helper('curl');

        $this->table = 'clients';

        $app_id = (!empty(API_APP_ID))?API_APP_ID:$this->ci->session->userdata('camp_company_app_id'); 
        $this->get_api_config($app_id);

    }

    function get_api_config($app_id){     

        $client_rec = $this->ci->Mydb->get_record ( 'client_id', $this->table, array('client_app_id' => $app_id ));
        $setting_array = $this->ci->Mydb->get_all_records("setting_key, setting_value", "client_settings", array('client_id'=>$client_rec ['client_id']));
        if(!empty($setting_array)){
            $settings_key = array_column($setting_array, 'setting_key');
            $settings_value = array_column($setting_array, 'setting_value');
            $get_combine_details = array_combine($settings_key, $settings_value);
            $client_rec = array_merge($client_rec, $get_combine_details);
        }

        if(!empty($client_rec)) {
            if($client_rec['client_pickupp_mode'] == "live") {
                $this->x_api_token = $client_rec['client_pickupp_live_key'];
                /* $this->api_base_url = 'https://portal.sg.pickupp.io/'; */
                $this->api_base_url = 'https://gateway.sg.pickupp.io/v2/merchant/';
            } else if($client_rec['client_pickupp_mode'] == "sandbox") {
                $this->x_api_token = $client_rec['client_pickupp_sandbox_key'];

                $this->api_base_url = 'https://gateway-uat.hk.pickupp.io/v2/merchant/';
            }
        }
    }

    public function load_post_curl($url, $method, $params)
    {
        $curl = curl_init($url);

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_HTTPHEADER => array(
                'Content-Type:application/json',
                'Authorization:' . $this->x_api_token,
            ),
        ));

        $output = curl_exec($curl);

        curl_close($curl);

        return $output;
    }

    public function load_get_curl($url, $get_data)
    {
        $curl = curl_init();

        if (!empty($get_data)) {
            $data = http_build_query($get_data);
            $url = $url . "?" . $data;
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => array(
                'Content-Type:application/json',
                'Authorization:' . $this->x_api_token,
            ),
        ));

        $output = curl_exec($curl);

        curl_close($curl);

        return $output;
    }

    public function load_delete_curl($url, $delete_data)
    {
        $curl = curl_init();

        if (!empty($delete_data)) {
            $data = http_build_query($delete_data);
            $url = $url . "?" . $data;
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_HTTPHEADER => array(
                'Content-Type:application/json',
                'Authorization:' . $this->x_api_token,
            ),
        ));

        $output = curl_exec($curl);

        curl_close($curl);

        return $output;
    }

    public function prepare_pickupp_data($order_id, $pickup_time, $weight, $service_type, $service_time = "-1", $pickup_notes, $dropoff_notes)
    {
        $order_details = $this->ci->Mydb->get_record('*', 'orders', array('order_id' => $order_id));

        $order_customer = $this->ci->Mydb->get_record('*', 'orders_customer_details', array('order_customer_order_primary_id' => $order_details['order_primary_id']));

        $outlet_details = $this->ci->Mydb->get_record('outlet_name, outlet_phone, outlet_address_line1, outlet_address_line2, outlet_postal_code, outlet_unit_number1, outlet_unit_number2, outlet_marker_latitude, outlet_marker_longitude', 'outlet_management', array('outlet_id' => $order_details['order_outlet_id']));

        $customerAddr = '';
        $outletAddr = '';
        $unitNumTxt = '';
        $address = "";

        $customerName = $order_customer['order_customer_fname'] . " " . $order_customer['order_customer_lname'];
        $customerMobile = $order_customer['order_customer_mobile_no'];
        $customerPostalCode = $order_customer['order_customer_postal_code'];

        if ($order_customer['order_customer_address_line1'] != '') {
            $customerAddr = $this->getAddressFmt($order_customer['order_customer_address_line1'], $customerPostalCode);
        }

        if ($order_customer['order_customer_unit_no1'] != '') {

            $unitNumTxt = ($order_customer['order_customer_unit_no2'] != '') ? '#' . $order_customer['order_customer_unit_no1'] . '-' . $order_customer['order_customer_unit_no2'] : $order_customer['order_customer_unit_no1'];

        } else if ($order_customer['order_customer_unit_no2'] != '') {

            $unitNumTxt = '#' . $order_customer['order_customer_unit_no2'];
        }

        if(!empty($order_customer['order_customer_tower_number'])){
            $unitNumTxt = !empty($unitNumTxt) ? $unitNumTxt.', '.$order_customer['order_customer_tower_number'] : $order_customer['order_customer_tower_number'];
        }

        $customerAddr = !empty($unitNumTxt) ? $unitNumTxt.', '.$customerAddr : $customerAddr;

        $url = "https://developers.onemap.sg/commonapi/search?returnGeom=Y&getAddrDetails=Y&searchVal=" . $order_customer['order_customer_postal_code'];
        $customerLatLang = lat_lang_curl_setup($url);

        $unitNumTxt = "";
        $outletAddr = $this->getAddressFmt($outlet_details['outlet_address_line1'], $outlet_details['outlet_postal_code']);
        if ($outlet_details['outlet_unit_number1'] != '') {

            $unitNumTxt = ($outlet_details['outlet_unit_number2'] != '') ? '#' . $outlet_details['outlet_unit_number1'] . '-' . $outlet_details['outlet_unit_number2'] : $outlet_details['outlet_unit_number1'];

        } else if ($outlet_details['outlet_unit_number2'] != '') {

            $unitNumTxt = '#' . $outlet_details['outlet_unit_number2'];

        }
        $outletAddr = $unitNumTxt . $outletAddr;

        $data = array(
            'pickup_contact_person' => $outlet_details['outlet_name'],
            'pickup_contact_phone' => $outlet_details['outlet_phone'],
            'pickup_address_line_1' => stripslashes($outletAddr),
            'pickup_latitude' => $outlet_details['outlet_marker_latitude'],
            'pickup_longitude' => $outlet_details['outlet_marker_longitude'],
            'pickup_time' => $pickup_time,
            'pickup_zip_code' => $outlet_details['outlet_postal_code'],
            'pickup_city' => 'Singapore',
            'pickup_notes' => $pickup_notes,

            'dropoff_contact_person' => $customerName,
            'dropoff_contact_phone' => $customerMobile,
            'dropoff_address_line_1' => stripslashes($customerAddr),
            'dropoff_zip_code' => $customerPostalCode,
            'dropoff_city' => "Singapore",
            'dropoff_latitude' => $customerLatLang['lat'],
            'dropoff_longitude' => $customerLatLang['lang'],
            'dropoff_notes' => $dropoff_notes,

            'region' => "SG",
            'weight' => $weight,
            'origin' => 'API',
            'service_type' => $service_type,
            'service_time' => $service_time,
            // 'item_name' => $item_name,
            // 'items' => array($items),

            'client_reference_number' => $order_details['order_local_no']
        );

        if($order_details['order_payment_mode'] == 1){
            $data['cash_on_delivery_amount'] = $order_details['order_total_amount'];
        }

        $outlet_details['outletAddr'] = $outletAddr;
        $customer_details['customerName'] = $customerName;
        $customer_details['customerMobile'] = $customerMobile;

        return array('api_data' => $data, 'order_details' => $order_details, 'outlet_details' => $outlet_details, 'customer_details' => $customer_details);
    }

    public function quote_order($order_id, $pickup_time, $weight, $service_type, $service_time = "-1", $pickup_notes = "", $dropoff_notes = "")
    {

        /* $returnResponse = array(
            'response' => 'error',
            "status_code" => 2001,
            'error_code' => "",
            // 'error_message' => $this->x_api_token,
            'error_message' => $this->api_base_url,
        );

        return $returnResponse; */

        $data = $this->prepare_pickupp_data($order_id, $pickup_time, $weight, $service_type, $service_time, $pickup_notes, $dropoff_notes);

        $array_data = $data['api_data'];
        // echo "<pre>", print_r($array_data, 1); die;
        $url = $this->api_base_url . 'orders/quote';

        $curl_response = $this->load_get_curl($url, $array_data);
        $decoded_response = json_decode($curl_response, true);

        if ($decoded_response['meta']['error_message'] == null || empty($decoded_response['meta']['error_message'])) {
            $returnResponse = array(
                'response' => 'success',
                "status_code" => 200,
                'message' => "Quote details",
                'data' => $decoded_response['data'],
            );
        } else {
            $returnResponse = array(
                'response' => 'error',
                "status_code" => $decoded_response['meta']['code'],
                'error_code' => $decoded_response['meta']['error_type'],
                'error_message' => $decoded_response['meta']['error_message'],
            );
        }

        return $returnResponse;
    }

    public function create_order($order_id, $pickup_time, $weight, $service_type, $service_time = "-1", $pickup_notes = "", $dropoff_notes = "", $quote_amount = 0)
    {
        $url = $this->api_base_url . 'orders/single';

        $alldata = $this->prepare_pickupp_data($order_id, $pickup_time, $weight, $service_type, $service_time, $pickup_notes, $dropoff_notes);

        $data = $alldata['api_data'];
        $order_details = $alldata['order_details'];
        $outlet_details = $alldata['outlet_details'];
        $customer_details = $alldata['customer_details'];

        // echo "<pre>", print_r($data, 1);die;

        $post_data = json_encode($data);

        $curl_response = $this->load_post_curl($url, "POST", $post_data);
        $decoded_response = json_decode($curl_response, true);
        // echo "<pre>", print_r($decoded_response, 1);die;
        if ($decoded_response['meta']['error_message'] == null || empty($decoded_response['meta']['error_message'])) {

            $decoded_response = $decoded_response['data'];

            $this->ci->Mydb->update('orders', array('order_primary_id' => $order_details['order_primary_id']),
                array("order_delivary_type" => "pickupp", "order_pickupp_uuid" => $decoded_response['id'],
                    "order_pickupp_order_id" => $decoded_response['order_number'],
                    "order_pickupp_status" => $decoded_response['status'],
                    "order_pickupp_ref_id" => $decoded_response['client_reference_number'],
                ));

            /* echo $this->ci->db->last_query();
            exit; */

            /* $returnResponse = array(
            'response' => 'error',
            'message' => $this->ci->db->last_query(),
            );
            return $returnResponse; */

            $this->ci->Mydb->insert('pos_pickupp_order_details', array(

                'pickupp_api_req_user_name' => $outlet_details['outlet_name'],

                'pickupp_api_req_user_contactno' => $outlet_details['outlet_phone'],

                'pickupp_api_delivery_user_name' => $customer_details['customerName'],

                'pickupp_api_delivery_user_contactno' => $customer_details['customerMobile'],

                'pickupp_api_schedule_date' => $pickup_time,

                'pickupp_api_service_type' => $service_type,

                'pickupp_api_pickup_location' => $outlet_details['outletAddr'],

                'pickupp_api_fee_price' => $quote_amount,

                'pickupp_api_order_id' => $order_details['order_id'],

                'pickupp_api_ref_id' => $decoded_response['order_number'],

                'pickupp_api_primary_order_id' => $order_details['order_primary_id'],

            ));

            $returnResponse = array(
                'response' => 'success',
                "status_code" => 200,
                'message' => "Order created successfully!",
                'data' => $decoded_response,
            );
        } else {
            $returnResponse = array(
                'response' => 'error',
                "status_code" => $decoded_response['meta']['code'],
                'error_code' => $decoded_response['meta']['error_type'],
                'error_message' => $decoded_response['meta']['error_message'],
            );
        }

        return $returnResponse;
    }

    public function get_order($order_id, $history = true)
    {
        $url = $this->api_base_url . 'orders/' . $order_id;

        $data = ($history) ? array('include_history' => true) : '';

        $curl_response = $this->load_get_curl($url, $data);

        $decoded_response = json_decode($curl_response, true);

        if ($decoded_response['meta']['error_message'] == null || empty($decoded_response['meta']['error_message'])) {
            $returnResponse = array(
                'response' => 'success',
                "status_code" => 200,
                'message' => "Order details",
                'data' => $decoded_response['data'],
            );
        } else {
            $returnResponse = array(
                'response' => 'error',
                "status_code" => $decoded_response['meta']['code'],
                'error_code' => $decoded_response['meta']['error_type'],
                'error_message' => $decoded_response['meta']['error_message'],
            );
        }

        return $returnResponse;
    }

    public function update_order($order_id, $pickup_contact_person, $pickup_contact_phone, $pickup_notes, $dropoff_contact_person, $dropoff_contact_phone, $dropoff_notes)
    {
        $url = $this->api_base_url . 'orders/' . $order_id;

        $data = array(
            'pickup_contact_person' => $pickup_contact_person,
            'pickup_contact_phone' => $pickup_contact_phone,
            'pickup_notes' => $pickup_notes,
            'dropoff_contact_person' => $dropoff_contact_person,
            'dropoff_contact_phone' => $dropoff_contact_phone,
            'dropoff_notes' => $dropoff_notes,
        );

        $post_data = json_encode($data);

        $curl_response = $this->load_post_curl($url, "PUT", $post_data);

        $decoded_response = json_decode($curl_response, true);

        if ($decoded_response['meta']['error_message'] == null || empty($decoded_response['meta']['error_message'])) {
            $returnResponse = array(
                'response' => 'success',
                "status_code" => 200,
                'message' => "Order updated successfully!",
                'data' => $decoded_response['data'],
            );
        } else {
            $returnResponse = array(
                'response' => 'error',
                "status_code" => $decoded_response['meta']['code'],
                'error_code' => $decoded_response['meta']['error_type'],
                'error_message' => $decoded_response['meta']['error_message'],
            );
        }

        return $returnResponse;
    }

    public function print_order($order_id)
    {
        if (empty($order_id)) {
            $returnResponse = array(
                'response' => 'error',
                "status_code" => 400,
                'error_code' => "",
                'error_message' => "Order id is empty!",
            );
            return $returnResponse;
        }

        $url = $this->api_base_url . 'prints';

        $data = array(
            'order_ids' => array($order_id),
            'type' => "waybill",
            'language' => "en",
        );

        $post_data = json_encode($data);

        $curl_response = $this->load_post_curl($url, "POST", $post_data);

        $decoded_response = json_decode($curl_response, true);

        if ($decoded_response['meta']['error_message'] == null || empty($decoded_response['meta']['error_message'])) {
            $returnResponse = array(
                'response' => 'success',
                "status_code" => 200,
                'message' => "Order print link",
                'data' => $decoded_response['data'],
            );
        } else {
            $returnResponse = array(
                'response' => 'error',
                "status_code" => $decoded_response['meta']['code'],
                'error_code' => $decoded_response['meta']['error_type'],
                'error_message' => $decoded_response['meta']['error_message'],
            );
        }

        return $returnResponse;
    }

    public function cancel_order($order_id, $cancel_reason = "")
    {
        /* $returnResponse = array(
            'response' => 'error',
            "status_code" => 2001,
            'error_code' => $this->x_api_token,
            'error_message' => $this->x_api_token,
        );

        return $returnResponse; */
        $order_where = array('order_pickupp_order_id' => $order_id);

        $order_details = $this->ci->Mydb->get_record('order_id, order_primary_id, order_delivary_type, order_pickupp_status', 'orders', $order_where);


        if($order_details['order_delivary_type'] != "pickupp"){
            $returnResponse = array(
                'response' => 'error',
                "status_code" => "",
                'error_code' => "",
                'error_message' => "Order already cancelled / Didn't assigned to pickupp",
            );
            return $returnResponse;
        }

        // if($order_details['order_delivary_type'] == 'pickupp' && in_array($order_details['order_pickupp_status'], array('SCHEDULED', 'scheduled', 'CONTACTING_AGENT', 'contacting_agent', 'ACCEPTED', 'accepted', 'DRAFT', 'draft')) == false){
        //     $returnResponse = array(
        //         'response' => 'error',
        //         "status_code" => "",
        //         'error_code' => "",
        //         'error_message' => "Cannot cancel the pickupp order",
        //     );
        //     return $returnResponse;
        // }        

        $url = $this->api_base_url . 'orders/' . $order_id;

        $data = array('cancellation_reason' => $cancel_reason);

        $curl_response = $this->load_delete_curl($url, $data);

        $decoded_response = json_decode($curl_response, true);

        if ($decoded_response['meta']['error_message'] == null || empty($decoded_response['meta']['error_message'])) {

            /* Update cancel status & reason in pickupp table */
            /* $cancel_update = $this->ci->Mydb->update(
                'pos_pickupp_order_details', array('pickupp_api_ref_id' => $order_id),
                array(
                    "pickupp_api_status" => "cancelled",
                    'pickupp_api_cancel_reason' => $cancel_reason,
                )
            ); */

            $cancel_delete = $this->ci->Mydb->delete(
                'pos_pickupp_order_details', array('pickupp_api_ref_id' => $order_id)
            );

            if ($cancel_delete) {
                /* Remove pickupp related details from orders table */
                $this->ci->Mydb->update(
                    'orders', $order_where,
                    array(
                    "order_delivary_type" => "",
                    "order_pickupp_uuid" => "",
                    "order_pickupp_order_id" => "",
                    "order_pickupp_status" => "",
                    "order_pickupp_ref_id" => "",
                    'order_status' => '1',
                    )
                );

                /* Remove driver details for cancelled order */
                 $this->ci->Mydb->delete('api_driver_details', array('padd_order_id' => $order_details['order_id']));

                $returnResponse = array(
                    'response' => 'success',
                    'status' => 'ok',
                    "status_code" => 200,
                    'message' => "Order cancelled!",
                    'data' => array('order_id' => $order_id),
                );
                return $returnResponse;
            } else {
                $returnResponse = array(
                    'response' => 'error',
                    'status' => 'error',
                    "status_code" => "",
                    'error_code' => "",
                    'error_message' => "Unable to remove the order from pickupp",
                );
                return $returnResponse;
            }
        } else {
            $returnResponse = array(
                'response' => 'error',
                'status' => 'error',
                "status_code" => $decoded_response['meta']['code'],
                'error_code' => $decoded_response['meta']['error_type'],
                'error_message' => $decoded_response['meta']['error_message'],
            );
            return $returnResponse;
        }

    }

    public function getAddressFmt($addressline1, $postal_code)
    {

        $customerAddr = '';

        $custAddrLine = $addressline1;

        if (strpos(strtolower($custAddrLine), 'singapore') !== false) {

            $custAddrLine = str_replace("singapore", "", strtolower($custAddrLine));
        }

        $custAddrLine = trim($custAddrLine);

        $custAddrLine = str_replace(",", "", $custAddrLine);

        $customerAddr = $custAddrLine . ', Singapore ' . $postal_code;

        return $customerAddr;

    }

    public function get_order_status($orderid, $history = false)
    {
        $url = $this->api_base_url . 'orders/' . $orderid;

        $data = ($history) ? array('include_history' => true) : '';

        $curl_response = $this->load_get_curl($url, $data);

        $decoded_response = json_decode($curl_response, true);

        if ($decoded_response['meta']['error_message'] == null || empty($decoded_response['meta']['error_message'])) {

            $order_status = $decoded_response['data']['status'];

            $order_where = array('order_pickupp_order_id' => $orderid);

            $this->Mydb->update('orders', $order_where, array("order_pickupp_status" => $order_status));

            $returnResponse = array(
                'response' => 'success',
                "status_code" => 200,
                'message' => "Order status updated",
                'data' => $decoded_response['data'],
            );
        } else {
            $returnResponse = array(
                'response' => 'error',
                "status_code" => $decoded_response['meta']['code'],
                'error_code' => $decoded_response['meta']['error_type'],
                'error_message' => $decoded_response['meta']['error_message'],
            );
        }

        return $returnResponse;

        $order_details = $this->ci->Mydb->get_record('order_primary_id, order_pickupp_order_id, order_pickupp_status', 'orders', array('order_id' => $orderid));
        $order_id = $order_details['order_pickupp_order_id'];

        if (!empty($ordervalue)) {

            $res = array(

                'status' => 'success',

                'order_id' => $orderid,

                // 'driver_id' => $ordervalue->driverId,

                'driver_name' => $ordervalue['delivery_agent_name'],

                'driver_phone' => $ordervalue['delivery_agent_phone'],

                'msg' => 'Status Updated Successfully',

            );

        } else {
            $res = array(

                'status' => 'error',

                'response' => 'Delivery agent details not updated',

                'msg' => 'Delivery agent details not updated',

            );
        }
        return $res;

    }

}
