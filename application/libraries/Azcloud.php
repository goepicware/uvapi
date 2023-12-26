<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
* Name :  AZ Cloud
* Created :  17.11.2021
*/

class Azcloud
{

    protected $ci;
    protected $x_api_key;    
    protected $x_client;   
    protected $api_base_url;  
    protected $x_machine_id;  

    function __construct(){

        $this->ci =& get_instance();
        $this->x_api_key = '$2a$10$JIkqZTRuOmLWnLF79LVUoupvlxy/fUNENKH3NNxO870/DaFGzJ562';
        $this->x_client = 'hainanbaouat';
        $this->x_machine_id = 'hainanbao_website';
        $this->api_base_url = 'http://vivipos.net/onlineorderapi/uat/';       
        // $this->api_base_url = 'http://vivipos.net/onlineorderapi/api3/';       

    }

    public function submit_order($order_id, $company_id, $app_id){
        
        $url = $this->api_base_url."transaction";
        
        $order_records = $this->ci->Mydb->get_record("*", "orders", array("order_primary_id" => $order_id, "order_company_app_id" => $app_id));
    
        $order_item = $this->ci->Mydb->get_all_records('item_id,item_product_id,item_name,item_qty,item_total_amount,item_specification,item_unit_price','order_items',array('item_order_primary_id'=>$order_records['order_primary_id']));
    
        // echo '<pre>'; print_r($order_item); 
        // exit;
 
        if (! empty ( $order_item )) {

            $i = 0;
            $item_specification = "";

            foreach ( $order_item as $items ) {
            /* get modifiers */
            $modifier_array = $extra_modifier_array = array (); 
            $modifier_array = $this->product_modifiers_get ( $order_id, $items ['item_id'], 'Modifier', 'order_item_id', 'callback' );
            $order_item [$i] ['modifiers'] = $modifier_array;
            /* get set_menu_component */
            $menu_components = array ();
            $menu_components = $this->product_menu_component_get ( $order_id, $items ['item_id'], 'MenuSetComponent', 'order_menu_primary_id', 'callback' );
            $order_item [$i] ['set_menu_component'] = $menu_components;
            $i ++;

            if($items['item_specification'] != ""){
                $item_specification = $item_specification.$items['item_specification'].', ';
            }
            }

            $item_specification = substr($item_specification, 0, -2);
            // echo $item_specification; exit;
        }

        $order_customer = $this->ci->Mydb->get_record('order_customer_id,order_customer_fname,order_customer_lname,order_customer_email,order_customer_mobile_no,order_customer_unit_no1,order_customer_unit_no2,order_customer_address_line1,order_customer_address_line2,order_customer_city,order_customer_state,order_customer_country,order_customer_postal_code,order_customer_created_on, order_customer_postal_code','orders_customer_details',array('order_customer_order_primary_id'=>$order_records['order_primary_id']));


        // customer address
        $unit1 = ($order_customer['order_customer_unit_no1'] ? $order_customer['order_customer_unit_no1'].', ' : '');
        $unit2 = ($order_customer['order_customer_unit_no2'] ? $order_customer['order_customer_unit_no2'].', ' : '');
        $addr1 = ($order_customer['order_customer_address_line1'] ? $order_customer['order_customer_address_line1'].', ' : '');
        $postal_code = ($order_customer['order_customer_postal_code'] ? $order_customer['order_customer_postal_code'] : '');
        $customer_address = $unit1.$unit2.$addr1.$postal_code;

        $pickup_time = strtotime($order_records['order_date']);
        $created_time = strtotime($order_records['order_created_on']);
        $destination = ($order_records['order_availability_name'] == 'Dine In' ? 'IN' : 'OUT');

        $customer_details = $this->ci->Mydb->get_record('customer_gender,customer_birthdate','customers',array('customer_id'=>$order_records['order_primary_id']));

       $order_total_list = array();

       foreach ($order_item as $prod) {

        $modifiers = array();

        $product_get_id = $this->ci->Mydb->get_record('product_revel_id, product_id','products',array('product_id'=>$prod['item_product_id']));

        $prnt_product_id = $prod['item_product_id'];

        $modifiers_arr = $prod['modifiers'];

        $menu_set_component = $prod['set_menu_component'];

        if(count($modifiers_arr) > 0) {

          foreach ($modifiers_arr as $mod) {

             if (!empty($mod['modifiers_values'])) {

              foreach ($mod ['modifiers_values'] as $displayvalues) { 

                $modifier_value_id = $displayvalues ['order_modifier_id'];

                $query = "SELECT count(alias_product_id) as cnt, product_price, product_name, product_revel_id FROM  pos_product_assigned_alias as psa, pos_products as p  WHERE p.product_status = 'A' AND  psa.alias_product_parent_id = " . $this->db->escape ( $prnt_product_id ) . " AND psa.alias_company_app_id = " . $this->db->escape ( $order_records['order_company_app_id'] ) . "  AND psa.alias_modifier_value_id IN ('" . $modifier_value_id . "') AND p.product_id = psa.alias_product_id";

                $modProducts = $this->ci->db->query($query)->result_array();
                
                $posMdItemId = $modProducts[0]['product_revel_id'];

                 $modifiers[] = array(
                            "code"=> $posMdItemId, 
                            "qty"=> $displayvalues['order_modifier_qty'], 
                            "name"=>  $displayvalues['order_modifier_name'], 
                            "sub_total_with_tax"=>  $displayvalues['order_modifier_price'],
                           );
             }

           }
         }

        } else if(count($menu_set_component)>0) {

               foreach ($menu_set_component as $menu_set) {

                 if (isset($menu_set ['product_details']) && !empty($menu_set ['product_details'])) {
                    
                     foreach ($menu_set ['product_details'] as $prodcombo) { 

                        $posItemId = $this->get_product_posid($order_records['order_company_app_id'],$prodcombo['menu_product_id']);

                        $modifiers[] = array(
                            "code"=> $posItemId, 
                            "qty"=> $prodcombo['menu_product_qty'], 
                            "name"=>  $prodcombo['menu_product_name'], 
                            "sub_total_with_tax"=> $prodcombo['menu_product_price'],
                           );

                     }

                 }

               }

        }

        $order_total_list[] =  array("name" =>$prod['item_name'],
                                              "no" =>  $prod['item_id'],
                                              "qty" => $prod['item_qty'], 
                                              "price" => $prod['item_unit_price'],
                                              "total" => $prod['item_total_amount'],
                                              "discount" => "0.00",
                                              "subtotal" => $prod['item_total_amount'],
                                              "condiments" => array()
                                            );

       }

       $customer_name = $order_customer['order_customer_fname']. " ".$order_customer['order_customer_lname'];  

       $order_status_name = $this->ci->Mydb->get_record('status_name','order_status',array('status_order'=>$order_records['order_status']));

        
        $data=array(
            "id" => $order_id, //this id became ref1 or refId
            "outletId" => $order_records['order_outlet_id'],
            "terminalId" => "", // null, will be fill by vivipos after accept order
            "member" => array(
                'memberId' => $order_customer['order_customer_id'],
                'name' => $customer_name,
                'gender' => $customer_details['customer_gender'],
                'dob' => $customer_details['customer_birthdate'],
                'points' => 0,
                'ref1' => "",
                "note" => ""
            ),
            "customerName" => $customer_name, // deliver / pick-up person name
            "customerAddress" => $customer_address, // deliver / pick-up address
            "customerPhone" => $order_customer['order_customer_mobile_no'],
            "pickupTime" => $pickup_time, //epoch time *10 digits only
            "createdDatetime" => $created_time, //epoch time, creation datetime on mobile *10 digits only
            "completedDatetime" => 0, // zero or null, will be fill by vivipos after order complete status
            "orderStatus" => strtoupper($order_status_name['status_name']), // SUBMITTED / ACCEPTED / REJECTTED / CANCELLED / PREPARING / COOKING / READY / COMPLETED
            "kitchenStatus" => "", // SUBMITTED / ACCEPTED / REJECTTED / CANCELLED / PREPARING / COOKING / READY
            "receivedType" => "0", // 0 for “Order Now”, 1 for “Pre Order”, 2 for “Delivery”
            "total" => $order_records['order_total_amount'],
            "itemSubtotal" => $order_records['order_sub_total'],
            "balance" => 0,
            "discountDetails" => array(),
            "transDiscount" => 1.00,
            "tax" => number_format($order_records['order_tax_calculate_amount'],4), // need 4 digits
            "taxType" => "INC", // either INC / EXC
            "bookingCharge" => 0, // zero for now
            "deliveryCharge" => 0, // zero for now
            "guessCount" => 1, // 1 for now
            "items" => $order_total_list,
            "payments" => array(
                    "type" => stripslashes($order_records['order_method_name']),
                    "name" => ($order_records['order_method_name'] == "creditcard" ? "VISA" : ""),
                    "amount" => $order_records['order_total_amount'],
                    "ref1" => "",
                    "ref2" => ""
                ),
            "paymentStatus" => ($order_records['order_payment_retrieved'] == "Yes" ? "PAID" : "PENDING"), // PAID (if no balance) / PENDING / COD
            "assignedTable" => "", // null,
            "destination" => $destination, // IN for dine-in, OUT for take-away
            "remark" => stripslashes($order_records['order_remarks']) //customer customized comments
        );
      
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type:application/json',
                'x-api-key:'.$this->x_api_key,
                'x-client:'.$this->x_client,
                'x-machine-id:'.$this->x_machine_id
            )
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        //echo $response; exit;
        $decoded_response = json_decode($response, true);   


        if($decoded_response['status'] == 0){

            $update_arr = array(
                'azpos_order_id' => $decoded_response['orderId'],
                'azpos_order_ref_id' => $decoded_response['refId'],
                'azpos_order_response_id' => $decoded_response['responseId'],
                'azpos_company_app_id' => $app_id,
                'azpos_order_request_data' => json_encode($data),
                'azpos_order_response_data' => $response,
                'azpos_order_created_on' => current_date(),
            );

            $insert_orders = $this->ci->Mydb->insert ( 'pos_azpos_order_details', $update_arr);
            
            $return_array = array(
                'status' => 'success',
                'message' => 'Order has been placed successfully'
            );
        }else{
            $return_array = array(
                'status' => 'error',
                'message' => $decoded_response['message']
            );          
        }

        echo json_encode($return_array);
        exit;
    }

    public function get_order_details($order_id){

        $url = $this->api_base_url."transaction?orderId=".$order_id; 
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            // CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type:application/json',
                'x-api-key:'.$this->x_api_key,
                'x-client:'.$this->x_client,
                'x-machine-id:'.$this->x_machine_id
            )
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        
        $decoded_response = json_decode($response, true);        

        if($decoded_response['status'] == 0){

            $return_array = array(
                'status' => 'success',
                'data' => $decoded_response['data']
            );
        }else{
            $return_array = array(
                'status' => 'error',
                'message' => $decoded_response['message']
            );          
        }

        echo json_encode($return_array);
        exit;

    }

    public function update_order_data($order_id){

        $url = $this->api_base_url."transaction?orderId=".$order_id;

        $order_records = $this->order_details($order_id);
        
        $data=array(
            "customerName" => $order_records['customer_name'], // deliver / pick-up person name
            "customerAddress" => $customer_address, // deliver / pick-up address
            "customerPhone" => $order_records['order_customer_mobile_no'],
            "pickupTime" => $pickup_time, //epoch time *10 digits only
            "orderStatus" => $order_records['status_name'],
            "paymentStatus" => ($order_records['order_payment_retrieved'] == "Yes" ? "PAID" : "PENDING"),
            "remark" => stripslashes($order_records['order_remarks'])
        );

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PATCH",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type:application/json',
                'x-api-key:'.$this->x_api_key,
                'x-client:'.$this->x_client,
                'x-machine-id:'.$this->x_machine_id
            )
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        echo $response; exit;
        $decoded_response = json_decode($response, true);        

        if($decoded_response['status'] == 0){

            $return_array = array(
                'status' => 'success',
                'message' => 'Order has been updated successfully'
            );
        }else{
            $return_array = array(
                'status' => 'error',
                'message' => $decoded_response['message']
            );          
        }

        echo json_encode($return_array);
        exit;

    }

    function order_details($order_id){

        $join = array();

        $join [0] ['select'] = "order_customer_id,CONCAT_WS(' ',order_customer_fname, order_customer_lname) AS customer_name,order_customer_email,order_customer_mobile_no,	order_customer_unit_no1,order_customer_unit_no2,order_customer_address_line1,order_customer_address_line2,order_customer_city,	order_customer_state,order_customer_country,order_customer_postal_code,order_customer_created_on, order_customer_postal_code";
		$join [0] ['table'] = "pos_orders_customer_details";
		$join [0] ['condition'] = "order_customer_order_primary_id = order_primary_id";
		$join [0] ['type'] = "LEFT";

        $join [1] ['select'] = "status_name";
		$join [1] ['table'] = "pos_order_status";
		$join [1] ['condition'] = "status_id = order_status";
		$join [1] ['type'] = "LEFT";

        $join [2] ['select'] = "order_method_name";
		$join [2] ['table'] = "pos_order_methods";
		$join [2] ['condition'] = "order_method_id = order_payment_mode";
		$join [2] ['type'] = "LEFT";

        $join [3] ['select'] = "customer_gender,customer_birthdate";
		$join [3] ['table'] = "pos_customers";
		$join [3] ['condition'] = "customer_id = order_customer_id";
		$join [3] ['type'] = "LEFT";

        $order_rec = $this->ci->Mydb->get_all_records('orders.*', 'orders', array('order_id' => $order_id),  '', '', '', '','', $join );
       
        $order_records = $order_rec[0];
        return $order_records;
    }


    public function product_modifiers_get($order_id = "", $item_id = "", $type, $field, $response = null) {

        $result = array();
        $modifiers = $this->ci->Mydb->get_all_records('order_modifier_id,order_modifier_name', 'order_modifiers', array('order_modifier_type' => $type, $field => $item_id, 'order_modifier_parent' => ''));
    
    
        if (!empty($modifiers)) {
    
          foreach ($modifiers as $modvalues) {
    
            /* get modifier values */
            $modifier_values = $this->ci->Mydb->get_all_records(array('order_modifier_id', 'order_modifier_name', 'order_modifier_qty', 'order_modifier_price'), 'order_modifiers', array('order_modifier_type' => $type, $field => $item_id, 'order_modifier_parent' => $modvalues['order_modifier_id']));
    
            if (!empty($modifier_values)) {
    
              $modvalues['modifiers_values'] = $modifier_values;
              $result[] = $modvalues;
            }
    
          }
        }
    
        return $result;
    
      }
   
      
      
      /* this function used to product menu component items */

  public function product_menu_component_get($order_id = "", $item_id = "", $type, $field, $response = null) {
    $result = $output_result = array();
   
    $com_set = $this->ci->Mydb->get_all_records(array('menu_menu_component_id', 'menu_menu_component_name'), 'order_menu_set_components', array('menu_item_id' => $item_id), '', '', '', '', 'menu_menu_component_id');


    $set_value = array();
    if (!empty($com_set)) {

      foreach ($com_set as $set) {

        $set_value['menu_component_id'] = $set['menu_menu_component_id'];
        $set_value['menu_component_name'] = $set['menu_menu_component_name'];

        /* get prodict details */
        $join [0] ['select'] = "product_revel_id";
        $join [0] ['table'] = "pos_products";
        $join [0] ['condition'] = "order_menu_set_components.menu_product_id = pos_products.product_id";
        $join [0] ['type'] = "LEFT";
        $menu_items = $this->ci->Mydb->get_all_records(array('menu_primary_id', 'menu_product_id', 'menu_product_name', 'menu_product_sku', 'menu_product_price', 'menu_product_qty'), 'order_menu_set_components', array('menu_item_id' => $item_id, 'menu_menu_component_id' => $set['menu_menu_component_id']),null,null,null,null,null,$join);
        $product_details = array();


        if (!empty($menu_items)) {

          foreach ($menu_items as $items) {
            $items['modifiers'] = $this->product_modifiers_get($order_id, $items['menu_primary_id'], 'MenuSetComponent', $field, 'callback');

                if($items['menu_product_qty'] > 0){

                     $product_details[] = $items;

                }
            }

          $set_value['product_details'] = $product_details;
          $output_result[] = $set_value;
        }
      }
    }
    return $output_result;
  }



      
    function order_items($order_id){
        $items = array();
        $order_items = $this->ci->Mydb->get_all_records('item_id,item_name as name,item_qty as qty,item_unit_price as price,item_total_amount as total', 'order_items', array('item_order_id' => $order_id));
        foreach($order_items as $key => $item){
            $items[$key]['name'] = $item['name'];
            $items[$key]['no'] = $item['item_id'];
            $items[$key]['qty'] = $item['qty'];
            $items[$key]['price'] = $item['price'];
            $items[$key]['total'] = $item['total'];
            $items[$key]['discount'] = "0.00";
            $items[$key]['subtotal'] = $item['total'];
            $items[$key]['condiments'] = array();
        }
        return $items;
    }

}
/* End of file Azcloud.php */