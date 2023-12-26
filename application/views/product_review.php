
                    <table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#fff" style="padding: 15px; margin: 0 0 20px;">
					    <tr><td width="170" style=" padding: 5px 10px;"><?php echo get_label('order_number'); ?></td><td width="10">-</td>
                        <td style=" padding: 5px 0;"><?php echo ( isset($order_list['order_local_no']) && !empty($order_list['order_local_no']))?$order_list['order_local_no']:"N/A"; ?></td></tr>
                        
                        <tr><td style=" padding: 5px 10px;">Created by</td><td width="10">-</td>
                        <td style=" padding: 5px 0px;">
                        <?php echo ( isset($order_list['order_source']) && ($order_list['order_source']=='CallCenter') )?
                        stripslashes(ucwords($order_list['order_agent'])):stripslashes(ucwords($order_list['customer_name'])) ?>
                        </td></tr>                                                                     
                        <tr><td style=" padding: 5px 10px;"><?php echo get_label('order_status'); ?></td><td>-</td><td style=" padding: 5px 0;"> <?php echo ( isset($order_list['status_name']) && $order_list['status_name']!='')?$order_list['status_name']:'N/A' ?></td></tr>
                    </table>
                                                           
                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 0 20px;">
                        <tr><td bgcolor="#858383" style="padding: 11px 25px; font: normal 16px arial; color:#fff;"> <?php echo get_label('order_items'); ?> </td></tr>    
                          
                        <tr>
                            <td bgcolor="#fff" style="padding: 15px;">
                                <table border="0" cellpadding="0" cellspacing="0" width="100%">                               
                                <tr class="ltable_outer">                                    
                                    <td bgcolor="#f5f5f5" style="padding: 10px;">
                                        
                                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                <thead bgcolor="#5b5a5a" style="background: #5b5a5a; color: #fff;">
                                                    <tr>
                                                        <th style="font-weight: normal; padding: 10px;" align="left">Name</th>
                                                        <th style="font-weight: normal; padding: 10px;"align="left">Modifier</th>
                                                        <th style="font-weight: normal; padding: 10px;"align="left">Unit Price</th>
                                                        <th style="font-weight: normal; padding: 10px;" align="left">Qty</th>
                                                        <th style="font-weight: normal; padding: 10px;"align="left">Amount</th>
                                                        <th style="font-weight: normal; padding: 10px;"align="left">Review</th>
                                                    </tr>
                                                </thead>

                                                <?php foreach($oder_item as $item) { ?>
                                                <tr>
                                                <td style=" padding: 10px;"><?php echo ucwords(stripslashes($item['item_name'])); ?></td>

                                                <td  style=" padding: 10px;">

                                                    <?php if(!empty($item['modifiers']))  
                                                          {
                                                          $k=0;$j=0;	  
                                                          foreach($item['modifiers'] as $modifier){ 

                                                              $modifiers="";
                                                              $modifiers .= $modifier['order_modifier_name'];
                                                              if(!empty($modifier['modifiers_values']))
                                                              {
                                                                  $i=0;
                                                                  foreach($modifier['modifiers_values'] as $modifier_value)
                                                                  {
                                                                      if($k==0)
                                                                      {
                                                                       $modifiers .= "-";
                                                                      }
                                                                      $k=1;
                                                                      if($i!=0)
                                                                      {
                                                                          $modifiers .= ",";
                                                                      }
                                                                      $modifiers .=$modifier_value['order_modifier_name'];
                                                                      //$modifiers .="</br>";
                                                                      $i++;
                                                                  }	  
                                                              }
                                                              if($j!=0)
                                                              {

                                                               echo ",";
                                                              }
                                                              echo "(".$modifiers.")";
                                                              $k=0;
                                                              $j++;
                                                      }  } ?>

                                                      <?php if(!empty($item['set_menu_component'])){


                                                          foreach($item['set_menu_component'] as $menu_component){ 
                                                            $j=0; 	  
                                                            $menu_set = "";
                                                           foreach($menu_component['product_details'] as $product_detail){ 	  


                                                              if($j!=0)
                                                              {
                                                                   $menu_set .=" ,";
                                                              }
                                                              $menu_set .= $product_detail['menu_product_name'];

                                                              if(!empty($product_detail['modifiers']))
                                                              {
                                                                  $menu_set .= "(";
                                                                  $i=0;
                                                                  $k=0;
                                                                  foreach($product_detail['modifiers'] as $menu_modifier)
                                                                  {
                                                                       if($i!=0)
                                                                       {
                                                                           $menu_set .=",";
                                                                       }
                                                                       $menu_set .= $menu_modifier['order_modifier_name'];

                                                                       if(!empty($menu_modifier['modifiers_values']))
                                                                       {

                                                                           foreach($menu_modifier['modifiers_values'] as $menu_modifier_value)
                                                                           {

                                                                               if($k==0)
                                                                               {
                                                                                $menu_set .= "-";
                                                                               }
                                                                               $k=1;
                                                                               $menu_set .=$menu_modifier_value['order_modifier_name'];


                                                                           }
                                                                       }
                                                                       $k=0;
                                                                       $i++;

                                                                  }
                                                                  $menu_set .= ")";
                                                              }


                                                               $j++;

                                                          }	  
                                                          echo $menu_set;


                                                          }	 

                                                      }
                                                      ?>


                                                </td>

                                                <td  style=" padding: 10px;"><?php echo show_price_client($item['item_unit_price'],$company['client_currency']); ?></td>
                                                <td  style=" padding: 10px;"><?php echo $item['item_qty']; ?></td>
                                                <td  style=" padding: 10px;"><?php echo show_price_client($item['item_total_amount'],$company['client_currency']); ?></td>
                                                <td style=" padding: 10px;"><a target="_blank" href="<?php echo $company['client_site_url'].'/product/review/'.$auth_key;?>"><?php echo "Write Review" ?></a></td>
                                                </tr>
                                                <?php } ?>

                                                <?php if ( isset($order_list['order_delivery_charge']) && $order_list['order_delivery_charge']!='' &&  $order_list['order_delivery_charge']!=0 ) { ?>

                                                <thead bgcolor="#5b5a5a" style="background: #5b5a5a; color: #fff;">
                                                    <tr>
                                                        <th style="font-weight: normal; padding: 10px;" align="left">Surcharges</th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th style="font-weight: normal; padding: 10px;" align="left">Amount</th>
                                                    </tr>
                                                </thead>
                                                <tr>
                                                    <td style=" padding: 10px;"><?php echo get_label('order_delivery_charge'); ?></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td style=" padding: 10px;"> <?php echo ( isset($order_list['order_delivery_charge']) && $order_list['order_delivery_charge']!='')? show_price_client($order_list['order_delivery_charge'],$company['client_currency']):'N/A' ?></td>
                                                </tr>
                                                <?php } ?>



                                                <tfoot>
                                                   
                                                    <tr>
														
                                                           <td></td>
                                                           <td colspan="4" align="right" style=" padding: 10px;"><?php echo get_label('order_total_amount'); ?> -</td>                                                  
                                                         <td style=" padding: 10px;"><?php echo show_price_client($order_list['order_total_amount'],$company['client_currency']); ?></td>
                                                    </tr>
                                                 
                                                     <!--<tr>
                                                        <td colspan="4" align="right" style=" padding: 10px;"><?php echo get_label('expected_amount'); ?></td>                                                    
                                                        <td style=" padding: 10px;"><?php echo show_price_client($order_list['order_total_amount'],$company['client_currency']); ?></td>
                                                    </tr> -->
                                                </tfoot>
                                            </table>
                                                                        
                                    </td>
                                </tr>
                            </table>
                            </td>
                        </table>
                    </td>
                </tr>                        
                <tr>
                    <td>
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 0 20px;">                            
                            <tr><td bgcolor="#858383" style="padding: 11px 25px; font: normal 16px arial; color:#fff;"><?php echo get_label('payment'); ?></td></tr>         
                            <tr>
                                <td bgcolor="#fff" style="padding: 15px;">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                     <tr>
                                      
                                      <td width="170" style=" padding: 5px 0;"><?php echo get_label('order_payment_mode'); ?></td>
                                      <td width="10">-</td>
                                      <td style=" padding: 5px 0;"><?php echo $order_list['order_method_name'].((isset($order_list['order_payment_getway_type']) && $order_list['order_payment_getway_type']!='')?(' - '.ucfirst($order_list['order_payment_getway_type'])):''); ?></td>

                                      </tr>
                         
                                    </table>
                                </td>
                            </tr>
                        </table>
                   
           

