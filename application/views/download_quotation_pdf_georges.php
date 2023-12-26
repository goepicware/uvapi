<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>FUNCTION-QUOTATION</title>
        <style>
            table{ border-collapse: collapse;}
            p{ margin: 0;}
        </style>
    </head>
    <body>
        <table cellspacing="0" cellpadding="0" width="100%" style="max-width: 768px; margin: 50px auto; font: bold 10px Arial, Helvetica, sans-serif; color: #000; font-weight: bold;">
            <tbody>
                <tr>
                    <td align="center" style=" font-size: 20px; font-weight: bold;">EXTREMERS ADVENTURE PTE LTD</td>
                </tr>
                <tr>
                    <td align="center" style=" font-size: 10px; font-weight: normal;"><a href="<?php echo $company['client_site_url']; ?>" style=" text-decoration: none; color: #000;" title="<?php echo $company['client_site_url']; ?>"><?php echo $company['client_site_url']; ?></a></td>
                </tr>
                <tr>
                    <td style="height: 10px; line-height: 1px; border-bottom: 2px solid #000;"></td>
                </tr>                
                <tr>
                    <td style="height: 10px; line-height: 1px;"></td>
                </tr>    		
                <tr>
                    <td>
                        <table width="100%">
                            <tr>
                                <td width="2%"></td>
                                <td width="96%">
                                    <table width="100%">
                                        <tr>
                                            <td align="center" style=" font-size: 16px; font-weight: normal;">QUOTATION - FUNCTION</td>
                                        </tr>
                                        <tr>
                                            <td style="height: 10px; line-height: 1px;"></td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <table width="100%" cellspacing="0" cellpadding="5" border="1"  style="border-collapse: collapse;">
                                                    <tr>
                                                        <td width="50%" style="vertical-align: middle;">                                                            
                                                            <table width="100%" cellspacing="0" cellpadding="0">
                                                                <tr>
                                                                    <td>Venue: <?php echo ucfirst(output_value($outlet_details['outlet_name'])); ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><?php echo $outlet_details['outlet_unit_number1'], $outlet_details['outlet_unit_number2'], $outlet_details['outlet_address_line1'], ' ', Singapore . " ", $outlet_details['outlet_postal_code']; ?></td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                        <td width="50%" style="vertical-align: middle;">
                                                            <table width="100%" cellspacing="0" cellpadding="0">
                                                                <tr>
                                                                    <td>Customer Details:</td>
                                                                </tr>
                                                                <tr>
                                                                    <td><?php echo $customer_details['customer_first_name'].''. $customer_details['customer_last_name']?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><?php echo $customer_details['customer_email']; ?></td>
                                                                </tr>
                                                                <?php if($customer_details['customer_phone'] != '') { ?>
                                                                <tr>
                                                                 <td><?php echo 'Tel: '.$customer_details['customer_phone']; ?></td>
                                                                </tr>
                                                                <?php } ?>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Guranteed Pax</td>
                                                        <?php foreach($cart_details['cart_items'] as $cart_items) { ?>
															<?php $product_qty += $cart_items['cart_item_qty']; ?>
														<?php } ?>
														<td><?php echo $product_qty; ?></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="height: 20px; line-height: 1px;"></td>
                                        </tr>  
                                        
									 <?php foreach($cart_details['cart_items'] as $cart_items) { ?>
                                        
                                        <tr>
                                            <td>
                                                <table width="100%" cellpadding="5" border="1">
                                                    <tr>
                                                        <td width="50%">
                                                            <table width="100%" cellspacing="0" cellpadding="0">
                                                                <tr>
                                                                    <td><?php echo ucwords(stripslashes($cart_items['cart_item_product_name'])); ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Pax: <?php echo $cart_items['cart_item_qty']; ?></td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                        <td width="50%" valign="top" >
                                                            <table width="100%" cellspacing="0" cellpadding="0">
                                                                <?php
                                                                if (!empty($cart_items['modifiers'])) {
                                                                    $k = 0;
                                                                    $j = 0;
                                                                    ?> 
                                                                <?php foreach ($cart_items['modifiers'] as $modifier) { ?>
                                                                <?php foreach ($modifier['modifiers_values'] as $modifier_value) { ?>
                                                                <tr>
                                                                    <td>
                                                                        <table width="100%" cellspacing="0" cellpadding="0">
                                                                            <tr>
                                                                                <td width="87%" valign="top"><?php echo $modifier_value['cart_modifier_name']; ?></td>
                                                                            </tr>       
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                                <?php } ?>
                                                                <?php } ?>
                                                                <?php } ?>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    
												 <?php if (!empty($cart_items['addons'])) { ?>
                                                    
                                                    <tr>
                                                        <td width="50%">
                                                            <table width="100%" cellspacing="0" cellpadding="0">
                                                                <tr>
                                                                    <td>ADD-ONS</td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                        <td width="50%" valign="top" >
                                                            <table width="100%" cellspacing="0" cellpadding="0">
                                                                
                                                                <?php foreach ($cart_items['addons'] as $addons_order) { ?>
                                                                <?php if ($addons_order['cart_addons_qty'] != '0') { ?>
                                                                <tr>
                                                                    <td>
                                                                        <table width="100%" cellspacing="0" cellpadding="0">
                                                                            <tr>
                                                                                <td width="87%" valign="top">
                                                                                    <?php echo $addons_order['cart_addons_name']; ?>

                                                                                    <?php echo "(" . ($addons_order['cart_addons_qty'] .' X '.'$'. $addons_order['cart_addons_price']).")"; ?>
                                                                                </td>
                                                                            </tr> 
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                                <?php } ?>
                                                                <?php } ?>
                                                                
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    
												<?php } ?>
												
												<?php if (!empty($cart_items['addons_setup'])) { ?>
                                                    
                                                    <tr>
                                                        <td width="50%">
                                                            <table width="100%" cellspacing="0" cellpadding="0">
                                                                <tr>
                                                                    <td>ADD-ONS</td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                        <td width="50%" valign="top" >
                                                            <table width="100%" cellspacing="0" cellpadding="0">
                                                                
                                                                <?php foreach ($cart_items['addons_setup'] as $addons) { ?>
																
																<tr>
                                                                    <td>
																		<?php echo $addons['cart_addon_setup_title']; ?>
																	</td>
																</tr> 	
																	
																<?php foreach ($addons['addons_setup_values'] as $addons_val) { ?>	
                                                                <tr>
                                                                    <td>
                                                                        <table width="100%" cellspacing="0" cellpadding="0">
                                                                            <tr>
																			<td width="87%" valign="top">
																			<?php echo stripslashes($addons_val['cart_addon_setup_val_title']); ?>
																			
																			<?php echo "(" . ($addons_val['cart_addon_setup_val_qty'] .' X '.'$'. $addons_val['cart_addon_setup_val_price']).")"; ?>
																			</td>
                                                                            </tr> 
                                                                        </table>
                                                                    </td>
                                                                </tr>                                                                       
                                                                <?php } ?>
                                                                <tr>
                                                                    <td style="height: 5px; line-height: 1px;"></td>
                                                                </tr>
                                                                <?php } ?>
                                                            </table>
                                                        </td>
                                                    </tr>
												<?php } ?>
												
												 <?php if (!empty($cart_items['cart_setup'])) { ?>
                                                    
                                                    <tr>
                                                        <td width="50%">
                                                            <table width="100%" cellspacing="0" cellpadding="0">
                                                                <tr>
                                                                    <td>SETUP</td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                        <td width="50%" valign="top">
                                                            <table width="100%" cellspacing="0" cellpadding="0">
                                                               
                                                                <tr>
                                                                    <td>
                                                                        <table width="100%" cellspacing="0" cellpadding="0">
																		<tr>
																		<td width="87%" valign="top">
																		<?php echo $cart_items['setup_order']['os_setup_name']; ?>
																		
																		<?php echo "(" . ($cart_items['setup_order']['os_setup_qty'] .' X '.'$'. ($cart_items['setup_order']['os_setup_price'])).")"; ?>
																		
																		</td>
                                                                               
																		</tr>       
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                                
                                                            </table>
                                                        </td>
                                                    </tr>
												<?php } ?>
                                                 
                                                   
											   <?php if (!empty($cart_items['equipment'])) { ?>
                                                    
                                                    <tr>
                                                        <td width="50%">
                                                            <table width="100%" cellspacing="0" cellpadding="0">
                                                                <tr>
                                                                    <td>EQUIPMENT</td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                        <td width="50%" valign="top" >
                                                            <table width="100%" cellspacing="0" cellpadding="0">
                                                                
                                                                <?php foreach ($cart_items['equipment'] as $equipment_order) { ?>
                                                                <?php if ($equipment_order['cart_equipment_qty'] != '0') { ?>
                                                                <tr>
                                                                    <td>
                                                                        <table width="100%" cellspacing="0" cellpadding="0">
                                                                            <tr>
                                                                                <td width="87%" valign="top"><?php echo $equipment_order['cart_equipment_name']; ?>
                                                                                
                                                                                <?php echo "(" . ($equipment_order['cart_equipment_qty'] .' X '.'$'. $equipment_order['cart_equipment_price']).")"; ?>
                                                                                
                                                                                </td>
                                                                                
                                                                            </tr> 
                                                                                
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                                <?php } ?>
                                                                <?php } ?>
                                                                
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <?php } ?>
                                                    
                                                    <?php if (!empty($cart_items['cart_item_special_notes'])) { ?>
                                                    
                                                    <tr>
                                                        <td width="50%">
                                                            <table width="100%" cellspacing="0" cellpadding="0">
                                                                <tr>
                                                                    <td>Special Instructions</td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                        <td width="50%" valign="top" >
                                                            <table width="100%" cellspacing="0" cellpadding="0">
                                                               
                                                                <tr>
                                                                    <td>
                                                                        <table width="100%" cellspacing="0" cellpadding="0">
                                                                            <tr>
                                                                                <td width="87%" valign="top"><?php echo stripslashes($cart_items['cart_item_special_notes']); ?>
                                                                                </td>
                                                                                
                                                                            </tr> 
                                                                                
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                                
                                                                
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <?php } ?>
                                                    
                                                    
                                                </table>
                                            </td>
                                        </tr>
                                        
                                        <?php } ?>
                                        
                                        <tr>
                                            <td style="height: 20px; line-height: 1px;"></td>
                                        </tr>  
                                        <tr>
                                            <td>
                                                <table width="100%" cellpadding="5" cellspacing="0" border="1">
                                                    <tr>
                                                        <td width="50%" style="color: #666;">
                                                            <table width="100%" cellspacing="0" cellpadding="0">
                                                                <tr>
                                                                    <td colspan="2" style=" font-size: 14px; color: #000;">TOTAL FUNCTION PRICE</td>
                                                                </tr>
                                                                
                                                                <?php 
																 /*Gst amount*/
																	$cart_sub_total = $cart_details['cart_details']['cart_sub_total'];
																	$cart_delivery_charge = $client_delivery_surcharge;
																	
																	$amounut_with_delivery = $cart_sub_total + $cart_delivery_charge;
																	
																	if($client_service_charge != ''){
																		
																		$new_service_charge = ($client_service_charge  / 100) * $amounut_with_delivery;  
																		$service_charge = number_format($new_service_charge,2);
																		$amounut_with_delivery += $service_charge;
																	}else{
																		$service_charge = "0.00";
																		$amounut_with_delivery += $service_charge;
																	}
																	
																	if($client_gst_charge != ''){
																		
																		$new_charge = ($client_gst_charge  / 100) * $amounut_with_delivery;  
																		$gst = number_format($new_charge,2);
																	}else{
																		$gst = "0.00";
																	}
																	
																	$grand_total = $cart_sub_total+$gst+$cart_delivery_charge+$service_charge;
																						  
																 ?>
                                                                
                                                                <tr>
                                                                    <td colspan="2" width="90%">
                                                                        <table width="100%" cellspacing="0" cellpadding="2" border="1" >
                                                                            <tr>
                                                                                <td>Subtotal</td>
                                                                                <td align="right">$ <?php echo $cart_details['cart_details']['cart_sub_total']; ?></td>
                                                                            </tr>
                                                                            
                                                                            <?php if ($cart_details['cart_details']['cart_availability_name'] != "Pickup") { ?>
                                                                            <?php if (isset($cart_details['cart_details']['cart_delivery_charge']) && $cart_details['cart_details']['cart_delivery_charge'] != '' && $cart_details['cart_details']['cart_delivery_charge'] > 0) { ?>
                                                                            <tr>
                                                                                <td>Delivery Charge</td>
                                                                                <td align="right">$ <?php echo $cart_details['cart_details']['cart_delivery_charge']; ?></td>
                                                                            </tr>
                                                                            <?php } ?>
                                                                            <?php } ?>
                                                                            
                                                                            <?php if ($cart_details['cart_details']['cart_special_discount'] > 0) { ?>
                                                                            <tr>
                                                                                <td><?php echo get_label('order_discount'); ?></td>
                                                                                <td align="right">$ <?php echo $cart_details['cart_details']['cart_special_discount']; ?></td>
                                                                            </tr>
                                                                            <?php } ?>
                                                                         
                                                                         
                                                                         <?php if($client_service_charge > 0 ) { ?>
                                                                            <tr>
                                                                                <td>Service Charge (<?php echo $client_service_charge;?>%)</td>
                                                                                <td align="right">$ <?php echo $service_charge;?></td>
                                                                            </tr>
																		<?php } ?>   
                                                                            
                                                                            
																		 <?php if($delivery_detail['client_gst_charge'] > 0 ) { ?>
                                                                            <tr>
                                                                                <td>GST Charge (<?php echo $delivery_detail['client_gst_charge'];?>%)</td>
                                                                                <td align="right">$ <?php echo $gst;?></td>
                                                                            </tr>
                                                                            <?php } ?>
                                                                            
                                                                            <tr>
                                                                                <td style="color: #000;">Grand Total</td>
                                                                                <td style="color: #000;" align="right">$ <?php echo number_format($grand_total,2);?></td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                    <td width="35%"></td>
                                                                </tr>
                                                               
                                                            </table>
                                                        </td>
                                                        <td width="50%">
                                                            <table width="100%" cellspacing="0" cellpadding="0">

                                                                <tr>
                                                                    <td style="height: 5px; line-height: 1px;"></td>
                                                                </tr> 
                                                                <tr>
                                                                    <td>Remarks:  </td>
                                                                </tr>
                                                                <tr height="5">
                                                                    <?php if($cart_details['order_remarks'] != '') { ?>
                                                                    <td><?php echo stripslashes($cart_details['order_remarks']); ?></td>
                                                                    <?php } ?>	
                                                                </tr>

                                                            </table>
                                                        </td>
                                                    </tr>	
                                                    <tr>
                                                        <td>
                                                            <table width="100%" cellspacing="0" cellpadding="0">
                                                                <tr>
                                                                    <td>Payment Terms</td>
                                                                </tr>
                                                                				
                                                                <tr>
                                                                    <td>
                                                                        <table width="100%" cellspacing="0" cellpadding="0">
                                                                            
                                                                            <tr>
                                                                                <td width="87%" valign="top">Cheque payable to  Extremers Adventure Pte Ltd</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td width="87%" valign="top">Deposit of 50% required to confirm venue</td>
                                                                            </tr>
                                                                             <tr>
                                                                               <td style="height: 10px; line-height: 1px;"></td>
                                                                            </tr>
                                                                             <tr>
                                                                                <td width="87%" valign="top">Bank Transfer:</td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td width="87%" valign="top">Name of Bank: Standard Chartered Bank</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td width="87%" valign="top">Account Name: EXTREMERS ADVENTURE PTE LTD</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td width="87%" valign="top">Account No.: 0410028657</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td width="87%" valign="top">Bank No.: 7144  /  Branch Code: 004</td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                              
                                                            </table>
                                                        </td>
                                                        <td>
                                                            <table width="100%" cellspacing="0" cellpadding="0">
                                                                
                                                                <tr>
                                                                    <td>Confirmed and accepted by</td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="height: 50px; line-height: 1px;"></td>
                                                                </tr> 
                                                                <tr>
                                                                    <td>(Name & Signature)</td>
                                                                </tr>                                                                
                                                                <tr>
                                                                    <td>Date:</td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="height: 30px; line-height: 1px;"></td>
                                        </tr> 
                                        <tr>
                                            <td>
                                                <table width="100%" cellspacing="0" cellpadding="0">
                                                    <tr>
                                                       <td width="50%">
                                                                <a href="<?php echo base_url(); ?>" title=""><img src="<?php echo base_url(); ?>skin/backend/images/pdf_logo.png" height="46" alt="" /></a>
                                                        </td>

                                                        <td width="50%" style="text-align: right;">
                                                                <a href="<?php echo base_url(); ?>" title=""><img src="<?php echo base_url(); ?>skin/backend/images/madbar.png" alt="" /></a>
                                                        </td>
                                                        
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td width="2%"></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </body>
</html>
