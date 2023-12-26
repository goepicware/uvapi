<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    /**
     * Name:  Kin Shun Delivery Integration
     *
     *
     * Created:  18.12.2019
     *
     *
     *
     */
    
    class Kinshun
    {
        protected $ci;
        public $meta;
        public $timestamp;
        public $order_source;
        
        public $mode;
        public $merchant_client_id;
        public $merchant_id;
        public $app_secret;
        public $api_base_url;
        public $app_id;
        public $auth;
        public $client_order_id;
        
        
        
        function __construct()
        {
            
            /*initialize the CI super-object*/
            $this->ci =& get_instance();
            
            $app = array();
            $app['mode']   = 'production';
            
            $app['sandbox_app_secret']   = 'dc4a74af12485e6c9f41c014c0c211aa5816213c';
            $app['sandbox_app_id']   =     "10000040";
            $app['sandbox_endpoint_base_url'] =    "https://sandbox-ap2-open-api.zeek.one/v1.3/";
            $app['sandbox_region']   =     "SG";
            $app['sandbox_profile']   = "ninjaos";
            
            $app['production_app_secret']   = 'd41da6f2aa1d2841d165383c486cbb28013d62f6';
            $app['production_app_id']   =     "1029";
            $app['production_endpoint_base_url'] =    "https://ap2-open-api.zeek.one/v1.3/";
            $app['production_region']   =     "SG";
            $app['production_profile']   = "ninjaos";
            
            $this->order_source = 5;
            $this->timestamp = time();
            //$this->client_order_id = date('Ymd') . substr(microtime(true) * 10000, - 4) . mt_rand(10000, 99999);
            $this->meta = [ "lang" => "en", "region" => "SG" ];
            $this->mode = $app['mode'];
            $this->app_secret = $app[$this->mode.'_app_secret'];
            $this->api_base_url = $app[$this->mode.'_endpoint_base_url'];
            $this->app_id = $app[$this->mode.'_app_id'];
            $this->profile = $app[$this->mode.'_profile'];
            
            
        }
        
        /*Generate Token*/
        function generate_signature($data){
            
            $data['meta'] = $this->meta;
            $data['merchant_id'] = $this->merchant_id;
            //$data['client_merchant_id'] = $this->merchant_client_id;
            $post_data = $data;
            ksort($post_data);
            $sign_arr = [];
            foreach($post_data as $key => $value) {
                if (is_numeric($value) || is_string($value)) {
                    $sign_arr[] = "{$key}={$value}";
                } }
            $str = implode('&', $sign_arr)."&{$this->app_id}&{$this->app_secret}&{$this->timestamp}";
            $signature = md5($str);
            $this->auth = ['appid' => $this->app_id, 'timestamp' => $this->timestamp, 'signature' => $signature];
            
        }
        
        
        
        /* POST curl methods */
        public function postValues($post_arr, $url)
        {
            $post_arr['data']['meta'] = $this->meta;
            $post_arr['data']['merchant_id'] = $this->merchant_id;
            //$post_arr['data']['client_merchant_id'] = $this->merchant_client_id;
            //echo json_encode($post_arr);
            $curl = curl_init();
            curl_setopt_array($curl, array(
                                           CURLOPT_URL => $url,
                                           CURLOPT_RETURNTRANSFER => true,
                                           CURLOPT_ENCODING => "",
                                           CURLOPT_MAXREDIRS => 10,
                                           CURLOPT_TIMEOUT => 0,
                                           CURLOPT_FOLLOWLOCATION => true,
                                           CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                           CURLOPT_CUSTOMREQUEST => "POST",
                                           CURLOPT_POSTFIELDS =>json_encode($post_arr),
                                           CURLOPT_HTTPHEADER => array(
                                                                       "Content-Type: application/json"
                                                                       ),
                                           ));
            $response = curl_exec($curl);
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                return array("status"=>$status,"error"=>$err, "response"=>$response);
            } else {
                return array("status"=>$status, "response"=>$response);
            }
        }
        
        /* Create Order */
        public function create_order($order_id){
            
            $order_details = $this->ci->Mydb->get_record('*','orders',array('order_primary_id'=>$order_id));
            $outlet_details = $this->ci->Mydb->get_record('outlet_kinshun_id, outlet_app_id','outlet_management',array('outlet_id'=>$order_details['order_outlet_id']));
            $this->merchant_id = $outlet_details['outlet_kinshun_id'];
            $this->merchant_client_id  = $outlet_details['outlet_app_id'];
            $this->client_order_id = $order_id;
            
            $order_customer = $this->ci->Mydb->get_record('*','orders_customer_details',array('order_customer_order_primary_id'=>$order_id));
			
			$customerAddr = '';
			if($order_customer['order_customer_address_line1'] != '') {
				$customerAddr = $this->getAddressFmt($order_customer);
			}
			
			$userLatLang = '';
			if($order_customer['order_customer_postal_code'] != '') {
				$userLatLangArr=$this->getUserLatLang($order_customer['order_customer_postal_code']);
				if(!empty($userLatLangArr)) {
					$userLatLang = $userLatLangArr['latitude'].','.$userLatLangArr['longitude'];
				}
			}
			
            $order_item = $this->ci->Mydb->get_all_records('item_product_id,item_name,item_qty,item_total_amount,item_specification','order_items',array('item_order_primary_id'=>$order_id));
            $products = array();
            $product_qty = 0;
            
            foreach($order_item as $item)
            {
                $product_qty += $item['item_qty'];
                /* get modifier values... */
                $modifier_array =  array (); /* old code null */
                $modifiers = $this->get_product_modifiers ( $order_id, $item['item_product_id'], 'Modifier', 'order_item_id', 'callback' );
                /* get menu set component values */
                $menu_components = array ();
                $menu_set = $this->get_product_menu_component ( $order_id, $item['item_product_id'], 'MenuSetComponent', 'order_menu_primary_id', 'callback' );
                
                $product = array(
                                 "product_name"=> $item['item_name'],
                                 "product_num"=> $item['item_qty'],
                                 "product_price"=> $item['item_total_amount']*100,
                                 "product_id"=> $item['item_product_id'],
                                 "product_remark"=> "0800031725",
                                 "item_detail"=> $modifiers.$menu_set);
                $products[]=$product;
            }
            
            $to_time = strtotime($order_details['order_date']);
            $from_time = time();
            $difference = round(abs($to_time - $from_time) / 60,2);
            
            
            $data = ['order_source' => $this->order_source,
            'client_order_id' => $this->timestamp,//$order_id,
            'order_time' => $this->timestamp,
            'is_appoint' => ($difference > 60?2:0), /* 0 is immediate - unscheduled */
            'appoint_time' => date("Y/m/d H:i:s", strtotime($order_details['order_date'])),
            'cod_type' => ($order_details['order_payment_mode']==1?1:2),
            'remark' =>  ($order_details['order_remarks']!=''?$order_details['order_remarks']:'Remarks'),
            'receive' => array(
                               'user_name' => $order_customer['order_customer_fname']." ".$order_customer['order_customer_lname'],
                               'user_phone' => $order_customer['order_customer_mobile_no'],
                               'user_location' => $userLatLang,
                               'user_address' => $customerAddr
                               ),
            'order_detail' => array(
                                    'total_price' => $order_details['order_total_amount']*100,
                                    'product_type' => 1,
                                    'weight_gram' => 1,
                                    'product_num' => $product_qty,
                                    'product_type_num' => 1,
                                    'product_detail' => $products,
                                    )];
            $this->generate_signature($data);
            $parameters = [ 'auth' => $this->auth, 'data' => $data ];
            $api_url = $this->api_base_url.'order/takeaway/create';
            $order_response = $this->postValues($parameters, $api_url );
            $response = json_decode($order_response['response']);
            
            if($order_response['status']==200 && $response->error==0){
                $order_reference_id = $response->data->order_id;
                $this->ci->Mydb->update('orders', array('order_primary_id' => $order_id), array('delivery_order_reference_id' => $order_reference_id,'delivery_agent' =>'Kin shun', 'delivery_order_status'=>$this->get_status('9001')));
                
                $order_delivery = $this->ci->Mydb->get_record('*','delivery_order',array('order_primary_id'=>$order_id));
                if(isset($order_delivery['id']) && $order_delivery['id']!=''){
                    
                    $updatedata = array('delivery_reference_id'=> $order_reference_id,
                                        'delivery_status'=>'',
                                        'delivery_status_description' => '',
                                        'delivery_partner_name' => '',
                                        'delivery_partner_location' =>'',
                                        'delivery_partner_phone' => '',
                                        'cancel' => 0,
                                        'cancel_reason' => ''
                                        );
                    
                    $this->ci->Mydb->update('delivery_order', array('id' => $order_delivery['id']), $updatedata);
                }else{
                     $this->ci->Mydb->insert('delivery_order', array('order_primary_id' => $order_id,'delivery_reference_id' => $order_reference_id));
                }
                
            }
            return $order_response;
            
            
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
		
		public function getAddressFmt($order_customer) {
			$customerAddr = $unitNumTxt = '';
			if($order_customer['order_customer_unit_no1'] != '') {
				$unitNumTxt = ($order_customer['order_customer_unit_no2'] != '') ? '#'.$order_customer['order_customer_unit_no1'].'-'.$order_customer['order_customer_unit_no2']:$order_customer['order_customer_unit_no1'];
			} else if($order_customer['order_customer_unit_no2'] != '') {
				$unitNumTxt = '#'.$order_customer['order_customer_unit_no2'];
			}
			$unitNumTxt = ($unitNumTxt != '') ? $unitNumTxt.', ' : '';
			
			$custAddrLine = $order_customer['order_customer_address_line1'];
			if(strpos(strtolower($custAddrLine), 'singapore') !== false) {
				$custAddrLine = str_replace("singapore","",strtolower($custAddrLine));
			}
			$custAddrLine = trim($custAddrLine);
			$custAddrLine = str_replace(",","",$custAddrLine);
			
			$customerAddr = $unitNumTxt.$custAddrLine.', Singapore '.$order_customer['order_customer_postal_code'];
			
			return $customerAddr;
		}
		
        /* Update Order delivery Status */
        
        public function update_delivery_status($data){
            
            $delivery_status = json_decode($data);
            $updatedata = array('delivery_status'=>$delivery_status->data->order_status,
                                'delivery_status_description' => $this->get_status($delivery_status->data->order_status),
                                'delivery_partner_name' => $delivery_status->data->partner->partner_name,
                                'delivery_partner_location' => $delivery_status->data->partner->partner_location,
                                'delivery_partner_phone' => $delivery_status->data->partner->partner_phone,
                                'cancel' => ($delivery_status->data->cancel!==null?1:0),
                                'cancel_reason' => ($delivery_status->data->cancel!==null?$delivery_status->data->cancel->cancel_reason:'')
                                );
            $updatestatus = $this->ci->Mydb->update('delivery_order', array('delivery_reference_id' => $delivery_status->data->order_id), $updatedata);
            if($delivery_status->data->order_status=='9005'){
                $this->ci->Mydb->update('orders', array('delivery_order_reference_id' =>$delivery_status->data->order_id), array( 'order_status'=>2));
            }
            
           if($delivery_status->data->order_status=='9025'){
                
                $this->ci->Mydb->update('orders', array('delivery_order_reference_id' =>$delivery_status->data->order_id), array('order_status'=>3));
                
                
            }
            
            
            $this->ci->Mydb->update('orders', array('delivery_order_reference_id' =>$delivery_status->data->order_id), array( 'delivery_order_status'=>$this->get_status($delivery_status->data->order_status)));
            return $updatestatus;
        }
        
        /* Predicted merchant quote time */
        
        public function precompletetime($kinshun_order_id){
            
            $order_details = $this->ci->Mydb->get_record('*','orders',array('delivery_order_reference_id'=>$kinshun_order_id));
            $outlet_details = $this->ci->Mydb->get_record('outlet_kinshun_id, outlet_app_id','outlet_management',array('outlet_id'=>$order_details['order_outlet_id']));
            $this->merchant_id = $outlet_details['outlet_kinshun_id'];
            $this->merchant_client_id  = $outlet_details['outlet_app_id'];
            $this->client_order_id = $order_details['order_id'];
            
            $data = ['user_location' => "22.3902837102,114.0042115195"];
            $this->generate_signature($data);
            $parameters = [ 'auth' => $this->auth, 'data' => $data ];
            $api_url = $this->api_base_url.'order/takeaway/precompletetime';
            $precompletetime =  $this->postValues($parameters, $api_url );
            return $precompletetime;
        }
        
        /* Check merchant is supported */
        
        public function isdistributable_order($kinshun_order_id){
            
            $order_details = $this->ci->Mydb->get_record('*','orders',array('delivery_order_reference_id'=>$kinshun_order_id));
            $outlet_details = $this->ci->Mydb->get_record('outlet_kinshun_id, outlet_app_id','outlet_management',array('outlet_id'=>$order_details['order_outlet_id']));
            $this->merchant_id = $outlet_details['outlet_kinshun_id'];
            $this->merchant_client_id  = $outlet_details['outlet_app_id'];
            $this->client_order_id = $order_details['order_id'];
            
            $data = ['profile' => $this->profile,  'user_location' => "22.3902837102,114.0042115195"];
            $this->generate_signature($data);
            $parameters = [ 'auth' => $this->auth, 'data' => $data ];
            $api_url = $this->api_base_url.'order/takeaway/isdistributable';
            $isdistributable =  $this->postValues($parameters, $api_url );
            return $isdistributable;
        }
        
        /* Cancel order */
        
        public function cancel_order($kinshun_order_id){
            
            $order_details = $this->ci->Mydb->get_record('*','orders',array('delivery_order_reference_id'=>$kinshun_order_id));
            $outlet_details = $this->ci->Mydb->get_record('outlet_kinshun_id, outlet_app_id','outlet_management',array('outlet_id'=>$order_details['order_outlet_id']));
            $this->merchant_id = $outlet_details['outlet_kinshun_id'];
            $this->merchant_client_id  = $outlet_details['outlet_app_id'];
            $this->client_order_id = $order_details['order_id'];
            
            $data = ['cancel_reason' => "Requested by customer", 'user_code' => '0000', 'order_id' => $kinshun_order_id];
            $this->generate_signature($data);
            $parameters = [ 'auth' => $this->auth, 'data' => $data ];
            $api_url = $this->api_base_url.'order/takeaway/cancel';
            $order_cancel =  $this->postValues($parameters, $api_url );
            $response = json_decode($order_cancel['response']);
            if($order_cancel['status']==200 && $response->error==0){
                $updatedata = array(
                                    'cancel' => 1,
                                    'cancel_reason' => "Requested by customer"
                                    );
                
                $updatestatus = $this->ci->Mydb->update('delivery_order', array('delivery_reference_id' => $kinshun_order_id), $updatedata);
                $this->ci->Mydb->update('orders', array('delivery_order_reference_id' =>$kinshun_order_id), array( 'delivery_order_status'=>'Order is cancelled', 'order_status'=>3));
                
                
            }
            return $order_cancel;
            
        }
        
        /* Get order Info */
        
        public function get_order_info($kinshun_order_id){
            
            $order_details = $this->ci->Mydb->get_record('*','orders',array('delivery_order_reference_id'=>$kinshun_order_id));
            $outlet_details = $this->ci->Mydb->get_record('outlet_kinshun_id, outlet_app_id','outlet_management',array('outlet_id'=>$order_details['order_outlet_id']));
            $this->merchant_id = $outlet_details['outlet_kinshun_id'];
            $this->merchant_client_id  = $outlet_details['outlet_app_id'];
            $this->client_order_id = $order_details['order_id'];
            
            $data = [ 'order_id' => $kinshun_order_id];
            $this->generate_signature($data);
            $parameters = [ 'auth' => $this->auth, 'data' => $data ];
            $api_url = $this->api_base_url.'order/takeaway/info';
            $order_info = $this->postValues($parameters, $api_url );
            return $order_info;
        }
        
        /* Get Position track */
        
        public function position_track($kinshun_order_id){
            
            $order_details = $this->ci->Mydb->get_record('*','orders',array('delivery_order_reference_id'=>$kinshun_order_id));
            $outlet_details = $this->ci->Mydb->get_record('outlet_kinshun_id, outlet_app_id','outlet_management',array('outlet_id'=>$order_details['order_outlet_id']));
            $this->merchant_id = $outlet_details['outlet_kinshun_id'];
            $this->merchant_client_id  = $outlet_details['outlet_app_id'];
            $this->client_order_id = $order_details['order_id'];
            
            $data = [ 'order_id' => $kinshun_order_id, 'page'=>1];
            $this->generate_signature($data);
            $parameters = [ 'auth' => $this->auth, 'data' => $data ];
            $api_url = $this->api_base_url.'tasker/takeaway/position_track';
            $order_position =  $this->postValues($parameters, $api_url );
            return $order_position;
        }
        
        /* Get Position Latest */
        
        public function position_latest($kinshun_order_id){
            
            $order_details = $this->ci->Mydb->get_record('*','orders',array('delivery_order_reference_id'=>$kinshun_order_id));
            $outlet_details = $this->ci->Mydb->get_record('outlet_kinshun_id, outlet_app_id','outlet_management',array('outlet_id'=>$order_details['order_outlet_id']));
            $this->merchant_id = $outlet_details['outlet_kinshun_id'];
            $this->merchant_client_id  = $outlet_details['outlet_app_id'];
            $this->client_order_id = $order_details['order_id'];
            
            
            $data = [ 'order_id' => $kinshun_order_id];
            $this->generate_signature($data);
            $parameters = [ 'auth' => $this->auth, 'data' => $data ];
            $api_url = $this->api_base_url.'tasker/takeaway/position_latest';
            $order_position_latest =  $this->postValues($parameters, $api_url );
            return $order_position_latest;
        }
        
        /* Get Status */
        
        public function get_status($code)
        {
            $status = array(
                            "9001"=>"Order is created",
                            "9005"=>"Partner accepted order",
                            "9011"=>"Partner arrived merchant",
                            "9015"=>"Delivery in progress",
                            "9017"=>"Partner arrived destination nearby",
                            "9021"=>"Order is completed",
                            "9025"=>"Order is cancelled - before partner picked up food",
                            "9026"=>"Order is cancelled - after partner picked up food",
                            );
            return $status[$code];
            
        }
        
        
        /* get product modifier information information */
        public function get_product_modifiers($order_id = "", $item_id = "", $type, $field, $response = null) {
            $result = array ();
            $modifiers = $this->ci->Mydb->get_all_records ( 'order_modifier_id,order_modifier_name', 'order_modifiers', array (
                                                                                                                               'order_modifier_type' => $type,
                                                                                                                               $field => $item_id,
                                                                                                                               'order_modifier_parent' => ''
                                                                                                                               ) );
            $modifiersstring="";
            if (! empty ( $modifiers ))
            {
                $k=0;$j=0;
                foreach ( $modifiers as $modvalues )
                {
                    
                    $modifiersstring .= $modvalues['order_modifier_name'];
                    /* get modifier values */
                    $modifier_values = $this->ci->Mydb->get_all_records ( array (
                                                                                 'order_modifier_id',
                                                                                 'order_modifier_name',
                                                                                 'order_modifier_qty',
                                                                                 'order_modifier_price'
                                                                                 ), 'order_modifiers', array (
                                                                                                              'order_modifier_type' => $type,
                                                                                                              $field => $item_id,
                                                                                                              'order_modifier_parent' => $modvalues ['order_modifier_id']
                                                                                                              ) );
                    
                    if(!empty($modifier_values))
                    {
                        $i=0;
                        foreach($modifier_values as $modifier_value)
                        {
                            if($k==0)
                            {
                                $modifiersstring .= "-";
                            }
                            $k=1;
                            if($i!=0)
                            {
                                $modifiersstring .= ",";
                            }
                            $modifiersstring .=$modifier_value['order_modifier_name'];
                            $i++;
                        }
                    }
                    if($j!=0)
                    {
                        $modifiersstring .= ",";
                    }
                    $modifiersstring = "(".$modifiers.")";
                    $k=0;
                    $j++;
                    
                    
                }
            }
            return $modifiersstring;
        }
        
        
        /* this function used to product menu component items */
        public function get_product_menu_component($order_id = "", $item_id = "", $type, $field, $response = null) {
            $result = $output_result = array ();
            $com_set = $this->ci->Mydb->get_all_records ( array (
                                                                 'menu_menu_component_id',
                                                                 'menu_menu_component_name'
                                                                 ), 'order_menu_set_components', array (
                                                                                                        'menu_item_id' => $item_id
                                                                                                        ), '', '', '', '', 'menu_menu_component_id' );
            
            $set_value = array ();
            $menu_set = "";
            if (! empty ( $com_set )) {
                $j=0;
                foreach ( $com_set as $set ) {
                    
                    $set_value ['menu_component_id'] = $set ['menu_menu_component_id'];
                    $set_value ['menu_component_name'] = $set ['menu_menu_component_name'];
                    
                    /* get prodict details */
                    $menu_items = $this->ci->Mydb->get_all_records ( array (
                                                                            'menu_primary_id',
                                                                            'menu_product_id',
                                                                            'menu_product_name',
                                                                            'menu_product_sku',
                                                                            'menu_product_qty',
                                                                            'menu_product_price'
                                                                            ), 'order_menu_set_components', array (
                                                                                                                   'menu_item_id' => $item_id,
                                                                                                                   'menu_menu_component_id' => $set ['menu_menu_component_id']
                                                                                                                   ) );
                    $product_details = array ();
                    if (! empty ( $menu_items )) {
                        
                        foreach ( $menu_items as $items ) {
                            if($j!=0)
                            {
                                $menu_set .=", ";
                                
                            }
                            $pro_price = ($items['menu_product_price'] > 0 ? " (+".$items['menu_product_price'].")" : '');
                            $pro_qty = ($items['menu_product_qty'] == 0? 1 : $items['menu_product_qty']);
                            $menu_set .= $pro_qty." X ".$items['menu_product_name'].$pro_price;
                            $menu_set .= $this->get_product_modifiers( $order_id, $items ['menu_primary_id'], 'MenuSetComponent', $field, 'callback' );
                            $j++;
                            
                        }
                        
                    }
                }
            }
            return $menu_set;
        }
        
    }
    /* End of file Kinshun.php */
    
    
    
