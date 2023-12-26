<?php  
/**************************
 Project Name	: Pastamania
Created on		: Sep 05, 2015
Last Modified 	: Sep  07, 2015
Description		: Extending cart libraies
***************************/
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MY_Cart extends CI_Cart
{
	
	
	public function __construct()
	{
		parent::__construct();
	   $this->product_name_rules = '\.\:\-_ a-z0-9|(\W)';
	}
	
	/* add cart and update quantity information  */
    function addcart($data,$product_id,$post_prodct_qty)
    {
    	/* Over write cart quantity */
    	$cart_tems = $this->contents();
    	
    		$cart_action = "add";
    		if(!empty($cart_tems))
    		  {
		    	foreach($cart_tems as $cart)
		    	  {
			    	if($cart['id']==$product_id)
			    	  { 
			    		$row_id = $cart['rowid'];
			    		$cart_action = "update";;
			    		$cart_qty = $cart['qty'];
			    		break;
			    	  }
		    	  }	 
    		  } 
	    	
 
	    	 if($cart_action == "add"){ 
	    	   	$this->insert($data);
	    	   	return true;
	    	   }
	    	 elseif($cart_action == "update"){
	    	   	
	    	 	$updated_qty = $cart_qty + $post_prodct_qty;
	    	 	$update_data = array(
	    	 			'rowid' => $row_id,
	    	 			'qty'   => $updated_qty);
	    	 	
	    	 	 $this->update($update_data);
	    	   	 return true;
	    	    }  
	    	  else{
	    	  	 return false;
	    	   } 
	    	  
    	
    }
    
    function addnewcart($data,$product_id,$post_prodct_qty)
    {
    	/* Over write cart quantity */
    	$cart_tems = $this->contents();
		$cart_action = "add";
		$this->insert($data);
		return true;
    }
    
    function _save_cart()
    {
    	// Let's add up the individual prices and set the cart sub-total
    	//echo "<pre>"; print_r($this->_cart_contents); exit;
    	$this->_cart_contents['total_items'] = $this->_cart_contents['cart_total'] = 0;
    	foreach ($this->_cart_contents as $key => $val)
    	{
    		// We make sure the array contains the proper indexes
    		if ( ! is_array($val) OR ! isset($val['price'], $val['qty']))
    		{
    			continue;
    		}
    		if($val['item_type'] == 'condiments')
    		{
    			$this->_cart_contents['cart_total'] += ($val['price']);
    		}
    		else 
    		{
    			$this->_cart_contents['cart_total'] += ($val['price'] * $val['qty']);
    			$this->_cart_contents['total_items'] += $val['qty'];
    		}
    		
    		$this->_cart_contents[$key]['subtotal'] = ($this->_cart_contents[$key]['price'] * $this->_cart_contents[$key]['qty']);
    	}
    
    	// Is our cart empty? If so we delete it from the session
    	if (count($this->_cart_contents) <= 2)
    	{
    		$this->CI->session->unset_userdata('cart_contents');
    
    		// Nothing more to do... coffee time!
    		return FALSE;
    	}
    
    	// If we made it this far it means that our cart has data.
    	// Let's pass it to the Session class so it can be stored
    	$this->CI->session->set_userdata(array('cart_contents' => $this->_cart_contents));
    
    	// Woot!
    	return TRUE;
    }
    
   // function total_items()
    //{
    	//$this->CI =& get_instance();
    	//$this->load->library('session');
    		// print_r($this->CI->session->all_userdata()); 
      //  return   $_SESSION['cart_contents']['total_items'] = 666;
         // $this->CI->session->set_userdata('cart_contents',array('total_items'=>44));
          // $car =  $this->CI->session->userdata('cart_contents');
           //return $car['total_items'];
    	// return $this->CI->contents['total_items'];
   // }
   
   function get_total_quantity()
   {
	   $quantity=0;
	   foreach ($this->_cart_contents as $key => $val)
	   {
		   $quantity+=$val['qty'];
	   }
	   return $quantity;
   }
   
   function get_cart_id()
   {
	   $product_id='';
	   foreach ($this->_cart_contents as $key => $val)
	   {
			if($product_id !='' && $val['id'] !='')
			{
				$product_id.=";";		
			}
			if($val['id'] !='')
			$product_id.=$val['id']."|".$val['price'];
	   }
	   return $product_id;
   }
    
    
}  
