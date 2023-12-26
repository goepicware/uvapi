    <table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#fff" style="padding: 15px; margin: 0 0 20px;">
        <?php /* ?>
          <tr><td width="170" style=" padding: 5px 10px;"><?php echo get_label('order_number'); ?></td><td width="10">-</td>
              <td style=" padding: 5px 0;"><?php echo ( isset($order_list[0]['order_id']) && !empty($order_list[0]['order_id']))?$order_list[0]['order_id']:"N/A"; ?></td></tr><?php */ ?>

              <tr><td width="170" style=" padding: 5px 10px;"><?php echo get_label('order_number'); ?></td><td width="10">-</td>
                <td style=" padding: 5px 0;"><?php echo ( isset($order_list[0]['order_local_no']) && !empty($order_list[0]['order_local_no'])) ? $order_list[0]['order_local_no'] : "N/A"; ?></td></tr>

                <tr><td style=" padding: 5px 10px;">Created by</td><td width="10">-</td>
                    <td style=" padding: 5px 0px;">
                        <?php echo ( isset($order_list[0]['order_source']) && ($order_list[0]['order_source'] == 'CallCenter') ) ?
                        stripslashes(ucwords($order_list[0]['order_agent'])) : stripslashes(ucwords($order_list[0]['customer_name']))
                        ?>
                    </td></tr>
                    <tr><td style="padding: 5px 10px;"><?php echo get_label('order_date'); ?></td><td>-</td><td style="padding: 5px 0;"><?php echo get_date_formart($order_list[0]['order_date'], 'l') ?>, <?php echo get_date_formart($order_list[0]['order_date'], 'F d, Y g:i a') ?></td></tr>	
                    <tr><td style=" padding: 5px 10px;"><?php echo get_label('order_availability'); ?></td> <td>-</td> <td style=" padding: 5px 0;"><?php echo ( isset($order_list[0]['order_availability_name']) && ($order_list[0]['order_availability_name'] != '') ) ? $order_list[0]['order_availability_name'] : "N/A" ?></td></tr>

                    <!-- <tr><td style=" padding: 5px 10px;"><?php echo get_label('order_source'); ?></td><td>-</td><td style=" padding: 5px 0;"> <?php echo ( isset($order_list[0]['order_source']) && $order_list[0]['order_source'] != '') ? $order_list[0]['order_source'] : 'N/A' ?></td></tr> -->

                    <tr><td style=" padding: 5px 10px;"><?php echo get_label('order_status'); ?></td><td>-</td><td style=" padding: 5px 0;"> <?php echo ( isset($order_list[0]['status_name']) && $order_list[0]['status_name'] != '') ? $order_list[0]['status_name'] : 'N/A' ?></td></tr>
                </table>

                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 0 20px;">
                    <tr><td bgcolor="#858383" style="padding: 11px 25px; font: normal 16px arial; color:#fff;"><?php echo get_label('customer_details'); ?></td></tr>
                    <tr>
                        <td bgcolor="#fff" style="padding: 15px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr><td width="170" style=" padding: 5px 10px;"><?php echo get_label('order_customer_name'); ?></td><td width="10">-</td><td style=" padding: 5px 0;"> <?php echo ( isset($order_list[0]['customer_name']) && !empty($order_list[0]['customer_name'])) ? ucfirst(stripslashes($order_list[0]['customer_name'])) : 'N/A'; ?></td></tr>
                                <tr> <td style=" padding: 5px 10px;"><?php echo get_label('order_customer_mobile_no'); ?></td> <td>-</td> <td style=" padding: 5px 0;"> <?php echo ( isset($order_list[0]['order_customer_mobile_no']) && $order_list[0]['order_customer_mobile_no'] != '') ? $order_list[0]['order_customer_mobile_no'] : 'N/A' ?></td> </tr>
                                <tr> <td style=" padding: 5px 10px;"><?php echo get_label('order_customer_email'); ?></td> <td>-</td> <td style=" padding: 5px 0;"> <?php echo ( isset($order_list[0]['order_customer_email']) && $order_list[0]['order_customer_email'] != '') ? $order_list[0]['order_customer_email'] : 'N/A' ?></td> </tr>
                                <tr> <td style=" padding: 5px 10px;"><?php echo get_label('order_customer_postal_code'); ?></td> <td>-</td> <td style=" padding: 5px 0;"> <?php echo ( isset($order_list[0]['order_customer_postal_code']) && $order_list[0]['order_customer_postal_code'] != '') ? $order_list[0]['order_customer_postal_code'] : 'N/A' ?></td> </tr>
                                <tr> <td style=" padding: 5px 10px;"><?php echo get_label('order_customer_address_line'); ?></td> <td>-</td> <td style=" padding: 5px 0;"> 
                                    <?php echo ( isset($order_list[0]['order_customer_address_line1']) && $order_list[0]['order_customer_address_line1'] != '') ? ucfirst(stripslashes($order_list[0]['order_customer_address_line1'])) : 'N/A' ?><?php echo","; ?>
                                    <?php echo DEFAULT_COUNTRY; ?>
                                </td> </tr>
                                <tr> <td style=" padding: 5px 10px;"><?php echo get_label('order_customer_unit_no1'); ?></td> <td>-</td> <td style=" padding: 5px 0;"> <?php echo ( isset($order_list[0]['order_customer_unit_no1']) && $order_list[0]['order_customer_unit_no1'] != '') ? stripslashes($order_list[0]['order_customer_unit_no1']) : 'N/A' ?></td> </tr>
                                <tr> <td style=" padding: 5px 10px;"><?php echo get_label('order_customer_unit_no2'); ?></td> <td>-</td> <td style=" padding: 5px 0;"> <?php echo ( isset($order_list[0]['order_customer_unit_no2']) && $order_list[0]['order_customer_unit_no2'] != '') ? stripslashes($order_list[0]['order_customer_unit_no2']) : 'N/A' ?></td> </tr>
                            </table>
                        </td>
                    </tr>

                </table>

                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 0 20px;">
                    <tr><td bgcolor="#858383" style="padding: 11px 25px; font: normal 16px arial; color:#fff;"> <?php echo get_label('order_items'); ?> </td></tr>    

                    <tr>
                        <td bgcolor="#fff" style="padding: 15px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <?php /*  ?><tr>
                      <td width="170" style=" padding: 5px 10px;">Customer Info</td>
                      <td width="10">-</td>
                      <td style=" padding: 5px 10px;"><?php echo ucfirst(stripslashes($order_list[0]['customer_name'])); ?> (<?php echo $order_list[0]['order_customer_mobile_no']; ?>)<br/>
                      <?php echo (isset($order_list[0]['order_customer_address_line1']) && $order_list[0]['order_customer_address_line1']!='')? ucfirst(stripslashes( $order_list[0]['order_customer_address_line1'] ) )."<br/>":""; ?>
                      <?php echo (isset($order_list[0]['order_customer_city']) && $order_list[0]['order_customer_city']!='')? ucfirst(stripslashes( $order_list[0]['order_customer_city'] ) )."<br/>":""; ?>
                      <?php echo $order_list[0]['order_customer_postal_code']; ?></td>
                  </tr> <?php */ ?>
                  <tr class="ltable_outer">                                    
                    <td bgcolor="#f5f5f5" style="padding: 10px;">

                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <thead bgcolor="#5b5a5a" style="background: #5b5a5a; color: #fff;">
                                <tr>
                                    <th style="font-weight: normal; padding: 10px;" width="55%" align="left">Name</th>
                                    <th style="font-weight: normal; padding: 10px;" width="15%" align="left">Unit Price</th>
                                    <th style="font-weight: normal; padding: 10px;" width="15%" align="left">Qty</th>
                                    <th style="font-weight: normal; padding: 10px;" width="15%" align="right">Amount</th>
                                </tr>
                            </thead>

                            <?php foreach ($oder_item as $item) {



                             ?>
                            <tr>
                                <td valign="top" style=" padding: 10px;" >
                                    <b><?php echo ucwords(stripslashes($item['item_name'])); ?></b>
                                    <table style="width: 100%;font-size: 13px; margin: 10px 0 0; padding: 0 0 0 30px;">
                                        <tbody>
                                            <?php
                                            if (!empty($item['modifiers'])) {
                                                $k = 0;
                                                $j = 0;
                                                ?>
                                                <tr>
                                                    <tr>
                                                        <td style="font-weight: bold; text-transform: uppercase;">MODIFIER</td>
                                                    </tr>

        <tr>
            <td>
                <table style="width: 100%; margin: 0 0 2px;">
                    <tbody>
                        <?php

                        foreach ($item['modifiers'] as $modifier) {
                            ?> 
                            <tr>
                                <td><b style="font-weight: bold;"><?php echo $modifier['order_modifier_name']; ?></b>

                                    <?php
                                    if (!empty($modifier['modifiers_values'])) {
                                        $i = 0;
                                        foreach ($modifier['modifiers_values'] as $modifier_value) {
                                            ?>
                                            <p style="margin: 0;"><?php echo $modifier_value['order_modifier_name']; ?> <?php echo ((float)$modifier_value['order_modifier_price'] > 0) ? '( '.show_price_client($modifier_value['order_modifier_price'], $company['client_currency']).' )' : ''; ?></p>
                                            <?php
                                        }
                                    }
                                    ?>               

                                </td>
                            </tr>
                            <?php
                        }

                        ?>  

                    </tbody>
                </table>
            </td>
        </tr>


                                                    <?php
                                                }
                                                ?>

                                                 <?php if(!empty($item['set_menu_component'])){


                      foreach($item['set_menu_component'] as $menu_component){
                        $j=0;
                        $menu_set = "";
                       foreach($menu_component['product_details'] as $product_detail){


                          if($j!=0)
                          {
                               $menu_set .=",<br>";
                          }else
                          {
                            $menu_set .="<br><br>";
                          }
                          $pro_price = ($product_detail['menu_product_price'] > 0 ? " (+".show_price_client($product_detail['menu_product_price'],$company['client_currency']).")" : '');
                          $pro_qty = ($product_detail['menu_product_qty'] == 0? 1 : $product_detail['menu_product_qty']).$pro_price;

                          $menu_set .= $pro_qty." X ".$product_detail['menu_product_name'];

                          if(!empty($product_detail['modifiers']))
                          {
                              $menu_set .= "<br>";
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
                                            $menu_set .= "<br>";
                                           }
                                           $k=1;
                                           $menu_set .=stripslashes($menu_modifier_value['order_modifier_name']).'<br>';


                                       }
                                   }
                                   $k=0;
                                   $i++;

                              }
                              $menu_set .= "";
                          }


                           $j++;

                      }

                      echo $menu_set;


                      }

                  }
                  ?>

                                                <?php
                                                if (!empty($item['addons'])) {
                                                    ?>
                                                    <tr>
                                                        <td style="font-weight: bold; text-transform: uppercase;">ADD-ONS</td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <table style="width: 100%; margin: 0 0 2px;">
                                                                <tbody>
                                                                    <?php
                                                                    foreach ($item['addons'] as $addons_order) {
                                                                        if ($addons_order['oa_addons_qty'] != '0') {
                                                                            ?> 
                                                                            <tr>
                                                                                <td><?php echo $addons_order['oa_addons_qty']; ?> X  <?php echo $addons_order['oa_addons_name']; ?></td>
                                                                                <td style="text-align: right;">  <?php echo "(+ " . show_price_client($addons_order['oa_addons_price'], $company['client_currency']) . ")"; ?> </td>
                                                                            </tr>
                                                                            <?php }
                                                                            ?>
                                                                            <?php
                                                                        }
                                                                        ?>               
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                    }
                                                    ?>
                                                    
                                                    <?php
                                                    if (!empty($item['addons_setup'])) {
                                                        ?>
                                                        <tr>
                                                            <td style="font-weight: bold; text-transform: uppercase;">ADD-ONS</td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <table style="width: 100%; margin: 0 0 2px;">
                                                                    <tbody>
                                                                        <?php
                                                                        foreach ($item['addons_setup'] as $addons_setup) {
                                                                            if ($addons_setup['as_setup_title'] != '') {
                                                                                ?>
                                                                                <tr>
                                                                                    <td>
                                                                                       <?php echo $addons_setup['as_setup_title']; ?>
                                                                                   </td>
                                                                               </tr> 
                                                                               <?php foreach ($addons_setup['addons_setup_values'] as $addons_val) { ?>
                                                                               <tr>
                                                                                <td><?php echo $addons_val['asv_setup_val_title']; ?> X  <?php echo $addons_val['asv_setup_val_qty']; ?>
                                                                                    <?php echo "(+ " . show_price_client($addons_val['asv_setup_val_price'], $company['client_currency']) . ")"; ?>
                                                                                </td>
                                                                            </tr>
                                                                            <?php } ?>
                                                                            <?php }
                                                                            ?>
                                                                            <?php
                                                                        }
                                                                        ?>               
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                    }
                                                    ?>
                                                    
                                                    <?php
                                                    if (!empty($item['setup'])) {  ?>
                                                    <tr>
                                                        <td style="font-weight: bold; text-transform: uppercase;">SETUP</td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <table style="width: 100%; margin: 0 0 2px;">
                                                                <tbody>
                                                                    <tr>
                                                                        <td><b><?php echo $item['setup'][0]['os_setup_name']; ?></b></td>  
                                                                    </tr> 
                                                                    <tr>
                                                                        <td><?php echo $item['setup'][0]['os_setup_qty']; ?> X<?php echo $item['setup'][0]['os_setup_description']; ?></td> <td style="text-align: right;"><?php echo "(+ " . show_price_client($item['setup'][0]['os_setup_price'], $company['client_currency']) . ")"; ?> 
                                                                    </td>  
                                                                </tr> 
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                            ?>
                                            <?php if (!empty($item['equipment'])) {  ?>

                                            <tr>
                                                <td style="font-weight: bold; text-transform: uppercase;">EQUIPMENT</td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <table style="width: 100%; margin: 0 0 2px;">
                                                        <tbody>

                                                            <?php
                                                            foreach ($item['equipment'] as $equipment_order) {
                                                                if ($equipment_order['oe_equipment_qty'] != '0') {
                                                                    ?> 
                                                                    <tr>  <td> <?php echo $equipment_order['oe_equipment_qty']; ?> X  <?php echo $equipment_order['oe_equipment_description']; ?></td> <td style="text-align: right;"><?php echo "(+ " . show_price_client($equipment_order['oe_equipment_price'], $company['client_currency']) . ")"; ?></td>  </tr> 
                                                                    <?php
                                                                }
                                                                ?>
                                                                <?php
                                                            }
                                                            ?>  

                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                        <?php if ($item['item_specification'] != "") { ?>
                                        <tr>
                                            <td style="font-weight: bold; text-transform: uppercase;">Special Instructions</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <table style="width: 100%; margin: 0 0 2px;">
                                                    <tbody>
                                                        <tr>
                                                            <td><?php echo $item['item_specification']; ?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>

                            </td>

                            <td  valign="top" style=" padding: 10px;"><?php echo show_price_client($item['item_unit_price'], $company['client_currency']); ?></td>
                            <td  valign="top" style=" padding: 10px;"><?php echo $item['item_qty']; ?></td>
                            <td  valign="top" style=" padding: 10px;" align="right"><?php echo show_price_client($item['item_total_amount'], $company['client_currency']); ?></td>
                        </tr>

                        <?php } ?>

                        <?php if ($order_list[0]['order_venue_type'] == 'hall' && $order_list[0]['order_hall_charges'] > 0) { ?>
                        <tr>
                            <td valign="top" style=" padding: 10px;" >
                                <b>Catering Hall</b>
                                <table style="width: 100%; margin: 0 0 2px;">
                                    <tbody>
                                        <tr><td></td></tr>
                                        <tr>
                                            <td><p style="width: 100%;margin: 10px 0 0; padding: 0 0 0 30px;"><?php echo $order_list[0]['order_hall_name']; ?></p></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                            <td>&nbsp;</td><td>&nbsp;</td>
                            <td style=" padding: 10px;" align="right"><?php echo show_price_client($order_list[0]['order_hall_charges'], $company['client_currency']); ?></td>   
                        </tr>
                        <?php } ?>

                        <tfoot>
                            <tr>
                                <td colspan="3" align="right" style=" padding: 10px;"><?php echo get_label('order_subtotal'); ?> </td>                                                    
                                <td style=" padding: 10px;" align="right"><?php echo show_price_client($order_list[0]['order_sub_total'], $company['client_currency']); ?></td>
                            </tr>
                            <?php
                            if (isset($order_list[0]['order_discount_amount']) && $order_list[0]['order_discount_amount'] != '' && $order_list[0]['order_discount_amount'] != 0) {
                                ?>

                                <tr>
                                    <td colspan="3" align="right" style=" padding: 10px; ">Discount Amount</td>

                                    <td style=" padding: 10px;" align="right"> <?php echo ( isset($order_list[0]['order_discount_amount']) && $order_list[0]['order_discount_amount'] != '') ? show_price_client($order_list[0]['order_discount_amount'], $company['client_currency']) : 'N/A' ?></td>
                                </tr>

                                <?php } ?>
                                
                                
                                <?php
							   if ( (isset($order_list[0]['order_delivery_charge']) && $order_list[0]['order_delivery_charge']!='' &&  $order_list[0]['order_delivery_charge']!=0 )) {
							   
							   $delivery_charge_total=$order_list[0]['order_delivery_charge'];
							   ?>
								 <tr>
								<td colspan="3" align="right" style=" padding: 10px; "><?php echo get_label('order_delivery_charge'); ?></td>

								<td style=" padding: 10px;" align="right"><?php echo show_price_client( $delivery_charge_total,$company['client_currency']); ?></td>
								</tr>
							<?php } ?>
							<?php
							   if ((isset($order_list[0]['order_additional_delivery']) && $order_list[0]['order_additional_delivery']!='' &&  $order_list[0]['order_additional_delivery']!=0 )) {
							   $addi_delivery_charge_total = $order_list[0]['order_additional_delivery'];
							   ?>
							 <tr>
								<td colspan="3" align="right" style=" padding: 10px; ">Additional Delivery Charge</td>
								<td style=" padding: 10px;" align="right"><?php echo show_price_client( $addi_delivery_charge_total,$company['client_currency']); ?></td>
								</tr>
							<?php } ?>

							<?php
							if ( isset($order_list[0]['order_subcharge_amount']) && (float)$order_list[0]['order_subcharge_amount'] > 0 ) {
						   ?>
							 <tr>
								<td colspan="3" align="right" style=" padding: 10px; ">Surcharge</td>
								<td style=" padding: 10px;" align="right"><?php echo show_price_client( $order_list[0]['order_subcharge_amount'],$company['client_currency']); ?></td>
							</tr>

							<?php } ?>

                             <?php
                              if (isset($order_list[0]['order_service_charge']) && $order_list[0]['order_service_charge'] != '' && $order_list[0]['order_service_charge'] != 0) {
                             ?>

                                    <tr>
                                        <td colspan="3" align="right" style=" padding: 10px; ">Service Charge(<?php echo floatval($order_list[0]['order_service_charge']); ?>%)</td>

                                        <td style=" padding: 10px;" align="right"> <?php echo ( isset($order_list[0]['order_service_charge_amount']) && $order_list[0]['order_service_charge_amount'] != '') ? show_price_client($order_list[0]['order_service_charge_amount'], $company['client_currency']) : 'N/A' ?></td>
                                    </tr>

                                    <?php } ?>

                                    <?php
                                    if (isset($order_list[0]['order_tax_charge']) && $order_list[0]['order_tax_charge'] != '' && $order_list[0]['order_tax_charge'] != 0) {
                                        ?>

                                        <tr>
                                            <td colspan="3" align="right" style=" padding: 10px; ">GST (<?php echo floatval($order_list[0]['order_tax_charge']); ?>%)</td>

                                            <td style=" padding: 10px;" align="right"> <?php echo ( isset($order_list[0]['order_tax_calculate_amount']) && $order_list[0]['order_tax_calculate_amount'] != '') ? show_price_client($order_list[0]['order_tax_calculate_amount'], $company['client_currency']) : 'N/A' ?></td>
                                        </tr>

                                        <?php } ?>

                                        <tr>
                                            <td colspan="3" align="right" style=" padding: 10px;"><?php echo get_label('order_total_amount'); ?> </td>                                              
                                            <td style=" padding: 10px;" align="right"><?php echo show_price_client($order_list[0]['order_total_amount'], $company['client_currency']); ?></td>
                                        </tr>

                                    <!--<tr>
                                       <td colspan="4" align="right" style=" padding: 10px;"><?php // echo get_label('expected_amount');  ?></td>                                                    
                                       <td style=" padding: 10px;"><?php // echo show_price_client($order_list[0]['order_total_amount'],$company['client_currency']); ?></td>
                                   </tr> -->
                               </tfoot>
                           </table>
                       </td>
                   </tr>
               </table>
           </td>
       </table>
