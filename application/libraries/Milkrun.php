<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Milkrun {

    protected $ci;

    public $api_base_url;

    public $x_api_key;

    public $app_token;

    
    function __construct() {
        /*initialize the CI super-object*/
        $this->ci =& get_instance();

        $this->api_base_url = "";
        
        // $this->x_api_key = '82cf60ef421b3cfe3b94985fbccc3c374c1d176b0c91d71d9b90dc88fb7aeafd829e341c17a37203';
        
        $this->x_api_key = "";
        
        $this->table = 'clients';

        $app_id = (!empty(API_APP_ID))?API_APP_ID:$this->ci->session->userdata('camp_company_app_id');

        $this->get_api_config($app_id);
    }

    function get_api_config($app_id){
        
        // $app_id = ($app_id!="")? $app_id : $this->ci->session->userdata('camp_company_app_id');    
        
        $client_rec = $this->ci->Mydb->get_record ( 'client_id', $this->table, array('client_app_id' => $app_id ));
        $setting_array = $this->ci->Mydb->get_all_records("setting_key, setting_value", "client_settings", array('client_id'=>$client_rec ['client_id']));

		if(!empty($setting_array)){
			$settings_key = array_column($setting_array, 'setting_key');
			$settings_value = array_column($setting_array, 'setting_value');
			$get_combine_details = array_combine($settings_key, $settings_value);
			$client_rec = array_merge($client_rec, $get_combine_details);
		}
        // print_r($client_rec); exit;
        if(!empty($client_rec)) {
            if($client_rec['client_milkrun_mode'] == "live") {
                $this->x_api_key = $client_rec['client_milkrun_live_key'];
                $this->api_base_url = 'https://milkrun.info/api/integration/merchants/';
            } else if($client_rec['client_milkrun_mode'] == "sandbox") {
                $this->x_api_key = $client_rec['client_milkrun_sandbox_key'];
                $this->api_base_url = 'https://uat.milkrun.info/api/integration/merchants/';
            }
        }
    }

    public function load_post_curl($url, $params, $method)
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
                'X-Api-Key:'.$this->x_api_key,
            ),
        ));

        $output = curl_exec($curl);
                        
        curl_close($curl);

        return $output;
    }        
    
    public function get_quotations($order_id, $caronly, $remarks, $order_date)
    {                               
        $order_details = $this->ci->Mydb->get_record('*', 'orders', array('order_id'=>$order_id));
        
        $order_customer = $this->ci->Mydb->get_record('*','orders_customer_details',array('order_customer_order_primary_id'=>$order_details['order_primary_id']));   
        
        $outlet_details = $this->ci->Mydb->get_record('outlet_name, outlet_phone, outlet_address_line1, outlet_address_line2, outlet_postal_code, outlet_unit_number1, outlet_unit_number2, outlet_marker_latitude, outlet_marker_longitude','outlet_management',array('outlet_id'=>$order_details['order_outlet_id']));

        
        $customerAddr = '';
        $outletAddr = '';
        $unitNumTxt = '';
        $address = "";
        if($order_customer['order_customer_address_line1'] != '') {
            $customerAddr = $this->getAddressFmt( $order_customer['order_customer_address_line1'], $order_customer['order_customer_postal_code']);
        }

        if($order_customer['order_customer_unit_no1'] != '') { 

            $unitNumTxt = ($order_customer['order_customer_unit_no2'] != '') ? '#'.$order_customer['order_customer_unit_no1'].'-'.$order_customer['order_customer_unit_no2']:$order_customer['order_customer_unit_no1'];

        } else if($order_customer['order_customer_unit_no2'] != '') {

            $unitNumTxt = '#'.$order_customer['order_customer_unit_no2'];

        }

        if($unitNumTxt != ""){
            $unitNumTxt = $unitNumTxt.', ';
        }

        $userLatLangArr=$this->getUserLatLang($order_customer['order_customer_postal_code']);
        

        $order_destinations = array(
            array(
                "delivery_address"=> $unitNumTxt.$customerAddr,
                "delivery_lat"=> $userLatLangArr['latitude'],
                "delivery_lng"=> $userLatLangArr['longitude'],
                "delivery_address_details"=>  $unitNumTxt.$customerAddr,
                "customer_phone_number"=> "+65".$order_customer['order_customer_mobile_no'],
                // "customer_phone_number"=> "+6590013157",
                "note"=> $remarks,
                "distance"=> "",
                "duration"=> "",
                "delivery_proof"=> null
            )
        );

         

        $unitNumTxt ="";
        $outletAddr = $this->getAddressFmt( $outlet_details['outlet_address_line1'], $outlet_details['outlet_postal_code']);
        if($outlet_details['outlet_unit_number1'] != '') { 

            $unitNumTxt = ($outlet_details['outlet_unit_number2'] != '') ? '#'.$outlet_details['outlet_unit_number1'].'-'.$outlet_details['outlet_unit_number2']:$outlet_details['outlet_unit_number1'];
    
        } else if($outlet_details['outlet_unit_number2'] != '') {
    
            $unitNumTxt = '#'.$outlet_details['outlet_unit_number2'];
    
        }

        if($unitNumTxt != ""){
            $unitNumTxt = $unitNumTxt.', ';
        }

        $outlet_phone = " +65".$outlet_details['outlet_phone'];
        $outletAddr =  $unitNumTxt.$outletAddr.$outlet_phone;
        $post_data = json_encode(array(
            "pick_up_address" => $outletAddr, 
            "pick_up_address_details" =>  $outletAddr, 
            "pick_up_lat"=> $outlet_details['outlet_marker_latitude'], 
            "pick_up_lng"=> $outlet_details['outlet_marker_longitude'], 
            "order_destinations"=> json_encode($order_destinations),
            "car_only"     => $caronly,
            'customized_merchant_name' => $outlet_details['outlet_name'],
            'customized_merchant_phone_number' => trim($outlet_phone),
           /* "scheduled_time"=> $order_date,*/
        ));

        $response = array( 
            'response'=>'error',
            "status_code" => 400,
            'error_message' => 'No address found'
        );
        $this->get_api_config($order_details['order_company_app_id']);
        /* API URL */
         $url = $this->api_base_url . 'orders'; 
        //  print_r($order_destinations); exit;
                    
        $curl_response = $this->load_post_curl($url, $post_data,"POST");    
        
        $decoded_response = json_decode($curl_response, true); 
        // echo "<pre>", print_r($decoded_response); exit;
        
        if(array_key_exists('error', $decoded_response))
        {
            $response = array( 
                'response'=>'error',
                "status_code" => 400,
                'error_code'=>$decoded_response['code'],
                'error_message' => $decoded_response['error']
            );
        }
        else
        {
            $this->ci->Mydb->update('orders', array('order_primary_id' => $order_details['order_primary_id']), array("order_milkrun_status"=> $decoded_response['status'],"order_delivary_type"=>"Milkrun","order_milkrun_quote_id"=> $decoded_response['uuid']));    

            $this->ci->Mydb->delete ( 'pos_milkrun_order_details', array (
                'milkrun_api_order_id' => $order_details['order_id'],
				'milkrun_api_primary_order_id' => $order_details['order_primary_id']
            ));

            $this->ci->Mydb->insert ( 'pos_milkrun_order_details', array (

				'milkrun_api_req_user_name' => $outlet_details['outlet_name'],

				'milkrun_api_req_user_contactno' => $outlet_details['outlet_phone'],

				'milkrun_api_delivery_user_name' => $order_customer['order_customer_fname']." ".$order_customer['order_customer_lname'],

				'milkrun_api_delivery_user_contactno' => $order_customer['order_customer_mobile_no'],

				'milkrun_api_schedule_date' => $order_date,

				'milkrun_api_service_type' => ($caronly==true?"Car":"Bike"),

				'milkrun_api_pickup_location' => $outletAddr,

				'milkrun_api_fee_price' => $decoded_response['delivery_fee'],

				'milkrun_api_order_id' => $order_details['order_id'],

				'milkrun_api_ref_id' => $decoded_response['uuid'],

				'milkrun_api_primary_order_id' => $order_details['order_primary_id'],

			));

           // echo $this->ci->db->last_query();
            //exit;

            $response = array(  
                'response'=>'success',
                "status_code" => 200,
                'message' => "Order placed successfully!",
                'data'=>$decoded_response,
            );
        }                    
        
        return $response;    
    }
    
    public function submit_order($order_id, $scheduled_time="", $app_id="", $estimatecostinput=0)
    {    

        if($app_id!="") {
            $this->get_api_config($app_id);
        }else {
            $app_id = $this->ci->session->userdata('camp_company_app_id');
        }
        
        /* API URL */
        $url = $this->api_base_url."orders/".$order_id."/submit";  

        $data=array(
            "uuid"     => $order_id,
            "scheduled_time"     => $scheduled_time
        );

        $order_data = json_encode($data);

        $client_rec = $this->ci->Mydb->get_record ( 'client_id', $this->table, array('client_app_id' => $app_id));

        // $order_details = $this->get_order_details($order_id, $this->ci->session->userdata('camp_company_app_id'));

        $get_client_details = $this->ci->Mydb->get_all_records( 'setting_key, setting_value', "client_settings", array ('client_id' => $client_rec['client_id']));

        if(!empty($get_client_details)){
            $settings_key = array_column(($get_client_details), 'setting_key');
            $settings_value = array_column(($get_client_details), 'setting_value');
            $get_combine_details = array_combine($settings_key, $settings_value);
        }

        $calculated_amount = 0;                
        if(!empty($get_combine_details['milkrun_payment_amount'])){
            $client_amount = $get_combine_details['milkrun_payment_amount'];
            $calculated_amount = $client_amount  - $estimatecostinput;
        }
        // print_r($get_combine_details); exit;
        // echo 'calc_amt ~ '.$calculated_amount.'<br> milkrun_payment_amount ~ '.$get_combine_details['milkrun_payment_amount'].'<br> estimate cost input ~ '.$estimatecostinput; exit;
        if($calculated_amount > 0){

            $curl_response = $this->load_post_curl($url, $order_data, "POST");    
            
            $decoded_response = json_decode($curl_response, true);        
            // print_r($decoded_response); exit;                
            
            if(array_key_exists('error', $decoded_response))
            {
                $response = array(  
                    'response'=>'error',
                    "status_code" => 400,
                    'error_code'=>$decoded_response['code'],
                    'error_message' => $decoded_response['error']
                );
            }
            else
            {
                $this->ci->Mydb->update('orders', array('order_milkrun_quote_id' => $order_id), array('order_delivary_type'=> "milkrun", "order_milkrun_id"=> $decoded_response['uuid'],"order_milkrun_status"=> $decoded_response['status'], 'order_status' => 2 ));    
                
                $response = array( 
                    'response'=>'success',
                    "status_code" => 200,
                    'message' => "Order placed successfully!",
                    'data'=>$decoded_response,
                );

                // debit from milkrun credits

                $client_rec = $this->ci->Mydb->get_record ( 'client_id', $this->table, array('client_app_id' => $this->ci->session->userdata('camp_company_app_id') ));

                $get_client_details = $this->ci->Mydb->get_all_records( 'setting_key, setting_value', "client_settings", array ('client_id' => $client_rec['client_id']));
                if(!empty($get_client_details)){
                    $settings_key = array_column(($get_client_details), 'setting_key');
                    $settings_value = array_column(($get_client_details), 'setting_value');
                    $get_combine_details = array_combine($settings_key, $settings_value);
                }

                $calculated_amount = 0;     
                $additional_debit = 0;           
                if(!empty($get_combine_details['milkrun_payment_amount'])){
                    $client_amount = $get_combine_details['milkrun_payment_amount'];
                    $calculated_amount = $client_amount  - $decoded_response['delivery_fee'];
                }

                $additional_debit = 0.50;
                $calculated_amount = $client_amount  - $decoded_response['delivery_fee'] - $additional_debit;
                
                // echo 'calc_amt ~ '.$calculated_amount.'<br> milkrun_payment_amount ~ '.$get_combine_details['milkrun_payment_amount'].'<br> estimate cost input ~ '.$estimatecostinput; exit;

                if($calculated_amount > 0){
                    $updArray = array(
                        'setting_value'=> $calculated_amount
                    );
                    $this->ci->Mydb->update ( 'client_settings', array ('setting_key'=>'milkrun_payment_amount', 'client_id'=> $client_rec ['client_id'] ), $updArray );

                    $milkrun_pay_log = array (
                        'milkrun_pay_client_id'    => $client_rec ['client_id'], 
                        'milkrun_pay_order_id'     => $decoded_response['status'],
                        "milkrun_pay_type"         => 'D', 
                        'milkrun_pay_client_amount'=> $calculated_amount,
                        'milkrun_pay_amount'       => $decoded_response['delivery_fee'],
                        'milkrun_additional_debit' => $additional_debit,
                        'milkrun_pay_created'      => current_date()
                    );
                    $this->ci->Mydb->insert('milkrun_payment_history', $milkrun_pay_log);

                }

                // debit from milkrun credits
            }   
        }else{
            $response = array(  
                'response'=>'error',
                "status_code" => 400,
                'error_message' => 'Insufficient Milkrun Credits'
            );
        }
        
        return $response; 
    }              


    public function get_order_details($order_id)
    {    
        /* API URL */
        $url = $this->api_base_url."orders/".$order_id;  

        $data=array(
            "uuid"     => $order_id,
            // "scheduled_time"     => $scheduled_time,
        );

        $order_data = json_encode($data);

        $curl_response = $this->load_post_curl($url, $order_data,"GET");    
        
        $decoded_response = json_decode($curl_response, true);                           

        if(array_key_exists('error', $decoded_response))
        {
            $response = array(  
                'response'=>'error',
                "status_code" => 400,
                'error_code'=>$decoded_response['code'], 
                'error_message' => $decoded_response['error']
            );
        }
        else
        {
            $response = array( 
                'response'=>'success',
                "status_code" => 200,
                'message' => "Order placed successfully!",
                'data'=>$decoded_response,
            );
        }                    

        return $response; 
    }    

    public function cancel_order($order_id)
    {    
        /* API URL */
        $url = $this->api_base_url."orders/".$order_id;  

        $data=array(
            "uuid"     => $order_id,
            // "scheduled_time"     => $scheduled_time,
        );

        $order_data = json_encode($data);

        $curl_response = $this->load_post_curl($url, $order_data, "DELETE");    
        
        $decoded_response = json_decode($curl_response, true);
        if(array_key_exists('error', $decoded_response))
        {
            $response = array(  
                'response'=>'error',
                'status' => 'error',
                "status_code" => 400,
                'error_code'=>$decoded_response['code'], 
                'error_message' => $decoded_response['error']
            );
        }
        else
        {

            $this->ci->Mydb->update('orders', array('order_milkrun_quote_id' => $order_id), array("order_milkrun_id"=> $decoded_response['uuid'],"order_milkrun_status"=> $decoded_response['status'], 'order_status' => 3 ));    

            $response = array(
                'response'=>'success',
                'status' => 'ok',
                "status_code" => 200,
                'message' => "Rider cancelled successfully!",
                'data'=>$decoded_response,
            );
        }                  

        return $response; 
    }               



    public function getUserLatLang($postal_code) {

        $userLatLangArr = array();

        

        $zip_resut = $this->ci->Mydb->get_record(array(

            'zip_latitude',

            'zip_longitude',

            'zip_id'

        ), 'zipcodes', array(

            'zip_code' => $postal_code

        ));

        $zip_status = '';

        if (!empty($zip_resut) && ($zip_resut['zip_latitude'] != '') && ($zip_resut['zip_longitude'] != '')) {

            $userLatLangArr['latitude']   = $zip_resut['zip_latitude'];

            $userLatLangArr['longitude']  = $zip_resut['zip_longitude'];

        } else {

            $this->ci->load->helper('curl');

            $url = MAPAPI_LINK.$postal_code;

            $zip_resut  = getCURLresult($url);

            if (!empty($zip_resut) && !empty($zip_resut->results)) {

                $zip_status = "OK";

                $latitude = $zip_resut->results [0]->LATITUDE;

                $longitude = $zip_resut->results [0]->LONGITUDE;

            }

        }

        

        return $userLatLangArr;

    }

    public function getAddressFmt( $addressline1, $postal_code ) {

        $customerAddr =  '';

        

        $custAddrLine = $addressline1;

        if(strpos(strtolower($custAddrLine), 'singapore') !== false) {

            $custAddrLine = str_replace("singapore","",strtolower($custAddrLine));

        }

        $custAddrLine = trim($custAddrLine);

        $custAddrLine = str_replace(",","",$custAddrLine);

        

        $customerAddr = $custAddrLine.', Singapore '.$postal_code;

        

        return $customerAddr;

    }

    public function get_order_status($orderid, $app_id){
        
        $this->get_api_config($app_id);
        
        $order_details = $this->ci->Mydb->get_record('order_primary_id,order_milkrun_id,order_company_app_id','orders',array('order_id'=>$orderid));
        $order_id = $order_details['order_milkrun_id']; 
        
        $url = $this->api_base_url."orders/".$order_id;
        $data=array(
            "uuid"     => $order_id,
            // "token"       => $this->app_token, 
            // "username"    => "merchant1@gmail.com", 
            // "orderId"     => $order_details['order_milkrun_id']
        );
        $order_data = json_encode($data);
        $curl_response = $this->load_post_curl($url, $order_data, "GET");    
        $ordervalue = json_decode($curl_response, true);   
        // echo $url; exit;
        // print_r($data); exit;

        // $ch = curl_init();   
        // curl_setopt($ch, CURLOPT_URL, $url);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $order_data);
        // $output = curl_exec($ch);  
        // curl_close($ch);
        // $ordervalue = json_decode($output);
        // print_r($ordervalue); exit;

        if(!empty($ordervalue)){ 
            
            $this->ci->Mydb->update('orders', array('order_primary_id' => $order_details['order_primary_id']), array("order_milkrun_status"=> $ordervalue['status']));
            
            // $driverinfo = $this->get_driver_info($order_details['order_milkrun_id'],$ordervalue->driverId);
            $driver_data = array ( 
    
                'padd_order_id' => $orderid,
    
                'padd_api_order_id' => $order_details['order_primary_id'],
    
                // 'padd_api_driver_id' => $ordervalue->driverId,
    
                'padd_api_driver_name' => $ordervalue['driver_name'],
    
                'padd_api_driver_phone' => $ordervalue['driver_phone_number'],
    
                'padd_api_type' => 'milkrun',
    
            );
            // print_r($driver_data); exit;
            $this->ci->Mydb->delete ( 'api_driver_details', array ('padd_api_order_id' => $order_details['order_primary_id'],'padd_order_id' => $orderid));
            $this->ci->Mydb->insert ( 'api_driver_details', $driver_data); 

            if($ordervalue['status'] == 'delivered'){

                $client_rec = $this->ci->Mydb->get_record ( 'client_id', $this->table, array('client_app_id' => $order_details['order_company_app_id'] ));

                $get_client_details = $this->Mydb->get_all_records( 'setting_key, setting_value', "client_settings", array ('client_id' => $client_rec['client_id']));
                if(!empty($get_client_details)){
                    $settings_key = array_column(($get_client_details), 'setting_key');
                    $settings_value = array_column(($get_client_details), 'setting_value');
                    $get_combine_details = array_combine($settings_key, $settings_value);
                }

                $calculated_amount = 0;                
                if(!empty($get_combine_details['milkrun_payment_amount'])){
                    $client_amount = $get_combine_details['milkrun_payment_amount'];
                    $calculated_amount = $client_amount  - $ordervalue['delivery_fee'];
                }
                

                if($calculated_amount > 0){
                    $updArray = array(
                        'setting_value'=> $ordervalue['delivery_fee']
                    );
                    $this->Mydb->update ( 'client_settings', array ('setting_key'=>'milkrun_payment_amount', 'client_id'=> $client_rec ['client_id'] ), $updArray );

                    $milkrun_pay_log = array (
                        'milkrun_pay_client_id'    => $client_rec ['client_id'], 
                        'milkrun_pay_order_id'     => $ordervalue['status'],
                        "milkrun_pay_type"         => 'D', 
                        'milkrun_pay_client_amount'=> $calculated_amount,
                        'milkrun_pay_amount'       => $ordervalue['delivery_fee'],
                        'milkrun_pay_created'      => current_date()
                    );
                    $this->Mydb->insert('milkrun_payment_history', $milkrun_pay_log);

                }

            }
            
            $res = array(
    
                'status'=>'success',
    
                'order_id' => $orderid, 
    
                // 'driver_id' => $ordervalue->driverId,
    
                'driver_name' => $ordervalue['driver_name'],
    
                'driver_phone' =>  $ordervalue['driver_phone_number'],
    
                'msg' => 'Status Updated Successfully',
    
            );
    
        
        }else{
            $res = array(
    
                'status'=>'error',
    
                'response' => 'Driver Details Not Updated',
    
                'msg' => 'Driver Details Not Updated',
    
            );
        }
        return $res; 
    
        
    
    
    }


}