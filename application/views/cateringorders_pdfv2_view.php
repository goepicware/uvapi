
<style>
.pleading-paper-size {
    width: 6.35in !important;
    max-width: 8.5in !important;
    padding-left: 1in !important;
    padding-right: 1in !important;

    font-family: Arial, Helvetica, sans-serif;
    font-size: 12px;
}
.normal {
    text-align: left;
}
.page-break {
    page-break-after: always;
    page-break-inside: avoid;
    clear:both;
}
.page-break-before {
    page-break-before: always;
    page-break-inside: avoid;
    clear:both;
}
p, td {
    margin: 0 !important;
}
h1 {
    page-break-after:always;
    page-break-inside: avoid;
    clear:both;
}
@media print {
    .pleading-paper-size {
        background-image: none;
    }
}
</style>

<table class="responsive_w" width="100%" style="background:#fff; max-width: 600px; margin:10px auto; -webkit-text-size-adjust:none; border-collapse: collapse; font-family: arial, sans-serif; font-size: 12px;">

    <tr>
        <td width="50%">
			
			
            <?php
            $folder = $this->lang->line('invoice_image_folder_name');
            if ($order_list[0]['client_invoice_main_logo'] !='' && file_exists(FCPATH . "media/dev_team/" . $folder . "/" . $order_list[0]['client_invoice_main_logo'])) {
            ?>

           <img src="<?php echo base_url() . "media/dev_team/" . $folder . "/" . $order_list[0]['client_invoice_main_logo']; ?>" alt="logo" />
            <?php } ?>
        </td>
        <td width="50%">
            <table width="100%" style="font-family: arial, sans-serif; font-size:12px;">                
                <tr>
                    <td style="font-weight:bold;color:#333;font-family: arial, sans-serif;  font-size:14px;text-align: right; text-transform: uppercase;">TAX INVOICE</td>
                </tr>
                <tr>
                    <td style="text-transform: uppercase;font-weight:normal;color:#333;font-family: arial, sans-serif;  font-size:12px;text-align: right;">
                        <?php if ($order_list[0]['client_gst_no']) { ?>	
                            GST Reg No.  <?php echo " " . $order_list[0]['client_gst_no']; ?>
                        <?php } ?>	
                    </td>
                </tr>
                <tr>
                    <td style="text-transform: uppercase;font-weight:normal;color:#787878;font-family: arial, sans-serif;  font-size:12px;text-align: right;">ORDER NO: <span style="font-weight:bold;color:#000;"><?php echo $order_list[0]['order_local_no']; ?></span></td>
                </tr>
                <tr>
                    <td style="text-transform: uppercase;font-weight:normal;color:#787878;font-family: arial, sans-serif;  font-size:12px;text-align: right;">DATE OF ORDER: 
                        <span style="font-weight:bold;color:#000;">
                            <?php echo  date('d-m-y h:i a', strtotime($order_list[0]['order_created_on']));?>
                        </span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr height="10">
        <td colspan="2"></td>
    </tr>
    <tr>
        <td colspan="2" style="color: #787878;font-family: arial, sans-serif;  font-size:12px;">
            <?php echo ucfirst(output_value($order_list[0]['outlet_name'])); ?> 
            <?php echo ($order_list[0]['outlet_phone'] != "") ? "(Tel : " . $order_list[0]['outlet_phone'] . ")" : ""; ?>
        </td>
    </tr>
    <tr height="10">
        <td colspan="2"></td>
    </tr>
    <tr>
        <td>
            <table width="100%" style="font-family: arial, sans-serif; font-size:12px;">
                <tr>
                    <td style="font-weight:bold; color:#000; font-family: arial, sans-serif; font-size:12px;"><strong><?php echo stripslashes(ucwords($order_list[0]['customer_name'])); ?> 
                           <?php echo $order_list[0]['outlet_unit_number1'], $order_list[0]['outlet_unit_number2'], stripslashes($order_list[0]['outlet_address_line1']), ' ', Singapore . " ", $order_list[0]['outlet_postal_code']; ?>
                        </strong>
                    </td>
                </tr>
                <tr>
                    <td style="font-family: arial, sans-serif; font-size:12px;color:#787878;"><?php echo (isset($order_list[0]['order_customer_mobile_no']) && $order_list[0]['order_customer_mobile_no'] != "") ? "(Tel :" . $order_list[0]['order_customer_mobile_no'] . ")" : ""; ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-family: arial, sans-serif; font-size:12px;color:#787878;">Email: <?php echo $order_list[0]['order_customer_email']; ?></td>
                </tr>
            </table>
        </td>
        <td style="text-align: right;">
            <table width="100%" style="font-family: arial, sans-serif; font-size:12px;">
               <tr height="10">
                    <td></td>
                </tr>
                
                <tr>
                    <td style="font-family: arial, sans-serif; font-size:12px;color:#787878;">Function date: <?php echo date('d-m-Y', strtotime($order_list[0]['order_date'])); ?></td>
                </tr>
                <tr>
                    <td style="font-family: arial, sans-serif; font-size:12px;color:#787878;">Function Time:  <?php echo date('g:i a', strtotime($order_list[0]['order_date'])); ?></td>
                </tr>
                
                <tr>
                    <td style="font-family: arial, sans-serif; font-size:12px;color:#787878;">No Of Pax:
						<?php $total_pax = 0; ?>
						<?php foreach ($oder_item as $item) { ?>
							<?php $total_pax+= $item['item_qty']; ?>
						<?php } ?>
						<?php echo $total_pax; ?>	
                    </td>
                </tr>
                
            </table>
        </td>
    </tr>
    <tr height="10">
        <td colspan="2"></td>
    </tr>
    <tr>
        <td colspan="2">
            
            <table  cellpadding="5" cellspacing="0" width="100%" style="font-family: arial, sans-serif; font-size:12px;border:1px solid #b5b5b5; border-collapse: collapse; ">
                <tr bgcolor="#f3f3f3">
                    <th style="font-weight:bold; color:#000; font-family: arial, sans-serif; font-size:12px;text-transform:uppercase; padding:7px;text-align: center;border: 1px solid #b5b5b5;background:#f3f3f3; width:20%;">NO</th>
                    <th style="font-weight:bold; color:#000; font-family: arial, sans-serif; font-size:12px;text-transform:uppercase; padding:7px;text-align: center;border: 1px solid #b5b5b5;background:#f3f3f3; width:40%;">DESCRIPTION</th>
                    <th style="font-weight:bold; color:#000; font-family: arial, sans-serif; font-size:12px;text-transform:uppercase; padding:7px;text-align: center;border: 1px solid #b5b5b5;background:#f3f3f3; width:20%;">NO OF ITEMS</th>
                    <th style="font-weight:bold; color:#000; font-family: arial, sans-serif; font-size:12px;text-transform:uppercase; padding:7px;text-align: center;border: 1px solid #b5b5b5;background:#f3f3f3; width:20%;">TOTAL</th>
                </tr>

                <?php $i = 1; ?>

                <?php foreach ($oder_item as $item) { ?>
                  
                    <tr>
                        <td style="color:#787878; font-family: arial, sans-serif; font-size:12px; padding:7px;text-align: center; border: 1px solid #b5b5b5;"><?php echo $i; ?></td>
                        <td style="color:#787878; font-family: arial, sans-serif; font-size:12px; padding:7px; text-align: left; border: 1px solid #b5b5b5;">
                            <table width="100%">
                                <tr>
                                    <td style="color:#000; font-family: arial, sans-serif; font-size:12px; font-weight: bold;">
                                        <?php echo ucwords(stripslashes($item['item_name'])); ?>
                                    </td>
                                </tr>                              
								<?php if($order_list[0]['order_breaktime_enable'] == 'Yes' && count($order_list[0]['order_breaktime_count']) > 0) { ?>	
								<tr>
                                    <td style="color:#666; font-family: arial, sans-serif; font-size:12px; font-weight: bold;text-align:right;">
                                       Break <?php echo ((int)$item['item_breaktime_indexflag'] + 1); ?> : <?php echo date("h:i a", strtotime($item['item_breaktime_started'])); ?>
                                    </td>
                                </tr>
							   <?php } ?>
                            <?php
                            if (!empty($item['modifiers'])) {
                                $k = 0;
                                $j = 0;
                                ?>
                                <tr>
                                    <td style="color:#000; font-family: arial, sans-serif; font-size:12px; font-weight: bold;">
                                        MODIFIER
                                    </td>
                                </tr>                                
                                <?php foreach ($item['modifiers'] as $modifier) { ?> 
                                <tr>
                                    <td style="color:#666; font-family: arial, sans-serif; font-size:12px; font-weight: bold;">
                                        <?php echo $modifier['order_modifier_name']; ?>
                                    </td>
                                </tr>                                    
                                    <?php if (!empty($modifier['modifiers_values'])) { ?>
                                        <?php foreach ($modifier['modifiers_values'] as $modifier_value) { ?>
                                            <tr>
                                                <td>
                                                    <?php echo $modifier_value['order_modifier_name'] ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    <?php } ?>		
                                    
                                <?php } ?>
                            <?php } ?>

                            <?php if (!empty($item['addons_order'])) { ?>  
                                <tr>
                                    <td style="color:#000; font-family: arial, sans-serif; font-size:12px; font-weight: bold;">
                                        ADD-ONS
                                    </td>
                                </tr>                                  

                                <?php foreach ($item['addons_order'] as $addons_order) { ?>		<?php if ($addons_order['oa_addons_qty'] != '0') { ?>
                                    <tr>
                                        <td>
                                            <?php echo $addons_order['oa_addons_qty']; ?> X  <?php echo $addons_order['oa_addons_name']; ?><?php echo "(+ " . show_price_client($addons_order['oa_addons_price']) . ")"; ?>
                                        </td>
                                    </tr> 
                                        
                                    <?php } ?>

                                <?php } ?>	
                            <?php } ?>
							
							<?php
							if (!empty($item['addons_setup'])) {
								?>
								<tr>
									<td style="color:#000; font-family: arial, sans-serif; font-size:12px; font-weight: bold;">ADD-ONS</td>
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
															<td style="color:#666; font-family: arial, sans-serif; font-size:12px; font-weight: bold;">
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

                            <?php if (!empty($item['setup_order'])) { ?>  
                                <tr>
                                    <td style="color:#000; font-family: arial, sans-serif; font-size:12px; font-weight: bold;">
                                        SETUP
                                    </td>
                                </tr>                                
                                <tr>
                                    <td style="color:#666; font-family: arial, sans-serif; font-size:12px; font-weight: bold;">
                                        <?php echo $item['setup_order'][0]['os_setup_name']; ?>
                                    </td>
                                </tr>                                			     
                                <tr>
                                    <td>
                                        <?php echo $item['setup_order'][0]['os_setup_qty'];?> X<?php echo $item['setup_order'][0]['os_setup_description'];?>
                                        <?php echo "(+ ".show_price_client($item['setup_order'][0]['os_setup_price']).")";?>
                                    </td>
                                </tr>                                

                            <?php } ?>	

                            <?php if (!empty($item['equipment_order'])) { ?>  
                                <tr>
                                    <td style="color:#000; font-family: arial, sans-serif; font-size:12px; font-weight: bold;">
                                        EQUIPMENT
                                    </td>
                                </tr>                                   

                                <?php foreach ($item['equipment_order'] as $equipment_order) { ?>

                                    <?php if ($equipment_order['oe_equipment_qty'] != '0') { ?>					     
                                        <tr>
                                            <td>
                                                <?php echo $equipment_order['oe_equipment_qty']; ?> X  <?php echo $equipment_order['oe_equipment_name']; ?>
                                                <?php echo "(+ " . show_price_client($equipment_order['oe_equipment_price']) . ")"; ?>
                                            </td>
                                        </tr>

                                    <?php } ?>

                                <?php } ?>
                                
                                <?php if($item['item_specification'] != '') { ?>
									
									<tr>
										<td style="color:#000; font-family: arial, sans-serif; font-size:12px; font-weight: bold;">
											Special instruction 
										</td>
									</tr> 
                                
									<tr>
										<td>
											<?php echo stripslashes($item['item_specification']); ?>
										</td>
									</tr> 
                               
                               <?php } ?> 
                                	
                            <?php } ?>
                            </table>
                        </td>
                        <td style="color:#787878; font-family: arial, sans-serif; font-size:12px; padding:7px;text-align: center; border: 1px solid #b5b5b5;">
                            <?php echo $item['item_qty'].' '."Pax"; ?>
                        </td>
                        <td style="color:#787878; font-family: arial, sans-serif; font-size:12px; padding:7px;text-align: center; border: 1px solid #b5b5b5;">
                            <?php echo show_price_client($item['item_total_amount']); ?>
                        </td>
                    </tr>

                    <?php $i++ ?>

                <?php } ?>
                
                <?php if($order_list[0]['order_venue_type'] == 'hall') { ?>
				
					<tr bgcolor="#f3f3f3">
						<td colspan="3" style="    color: #000;	font-family: arial, sans-serif;	font-size: 12px;	padding: 7px;	text-align: right; font-weight:600;border: 1px solid #b5b5b5;"><strong><?php echo ucwords(stripslashes($order_list[0]['order_hall_name'])).' '.'('."Hall".')'; ?></strong></td>
						<td colspan="1" style="    color: #000;	font-family: arial, sans-serif;	font-size: 12px;	padding: 7px;	 text-align: right; font-weight:600;border: 1px solid #b5b5b5; "><strong><?php echo show_price_client($order_list[0]['order_hall_charges']); ?></strong></td>
					</tr>
                
                <?php } ?>
                
                <tr bgcolor="#f3f3f3">
                    <td colspan="3" style="    color: #000;	font-family: arial, sans-serif;	font-size: 12px;	padding: 7px;	text-align: right; font-weight:600;border: 1px solid #b5b5b5;"><strong>Subtotal</strong></td>
                    <td colspan="1" style="    color: #000;	font-family: arial, sans-serif;	font-size: 12px;	padding: 7px;	 text-align: right; font-weight:600;border: 1px solid #b5b5b5; "><strong><?php echo show_price_client($order_list[0]['order_sub_total']); ?></strong></td>
                </tr>

                    <?php if ($order_list[0]['order_availability_name'] != "Pickup") {
                        ?>
                
                        <?php if (isset($order_list[0]['order_delivery_charge']) && $order_list[0]['order_delivery_charge'] != '' && $order_list[0]['order_delivery_charge'] > 0) { ?>
                            <tr bgcolor="#f3f3f3">
                                <td colspan="3" style="    color: #000;	font-family: arial, sans-serif;	font-size: 12px;	padding: 7px;	text-align: right; font-weight:600;border: 1px solid #b5b5b5;"><strong>Delivery Charge</strong></td>
                                <td colspan="1" style="    color: #000;	font-family: arial, sans-serif;	font-size: 12px;	padding: 7px;	text-align: right; font-weight:600;border: 1px solid #b5b5b5;"><strong><?php echo show_price_client($order_list[0]['order_delivery_charge']); ?></strong></td>
                            </tr>

                        <?php } ?>
                    <?php } ?>

                    <?php if ($order_list[0]['order_discount_amount'] > 0) { ?>
                    <tr bgcolor="#f3f3f3">
                            <td colspan="3" style="    color: #000;	font-family: arial, sans-serif;	font-size: 12px;	padding: 7px;	text-align: right; font-weight:600;border: 1px solid #b5b5b5;"><strong><?php echo get_label('order_discount'); ?> </strong></td>
                            <td colspan="1" style="    color: #000;	font-family: arial, sans-serif;	font-size: 12px;	padding: 7px; text-align: right; font-weight:600;border: 1px solid #b5b5b5;"><strong><?php echo show_price_client($order_list[0]['order_discount_amount']); ?></strong></td>
                    </tr>
                    <?php } ?> 
                
                <?php
                if ($order_list[0]['order_tax_charge'] != '' && $order_list[0]['order_tax_charge'] != '0') {
                    ?>
                    <tr bgcolor="#f3f3f3">
                        <td colspan="3" style="    color: #000;	font-family: arial, sans-serif;	font-size: 12px;	padding: 7px; text-align: right; font-weight:600;border: 1px solid #b5b5b5;"><strong>GST (<?php echo $order_list[0]['order_tax_charge']; ?>%) </strong></td>
                        <td colspan="1" style="    color: #000;	font-family: arial, sans-serif;	font-size: 12px;	padding: 7px; text-align: right; font-weight:600;border: 1px solid #b5b5b5;"><strong><?php echo ( isset($order_list[0]['order_tax_calculate_amount']) && $order_list[0]['order_tax_calculate_amount'] != '') ? show_price_client($order_list[0]['order_tax_calculate_amount']) : 'N/A' ?></strong></td>

                    </tr>
                <?php } ?>
                
                <tr bgcolor="#f3f3f3">
                    <td valign="top" colspan="3" style="    color: #000;	font-family: arial, sans-serif;	font-size: 12px; text-align: right; font-weight:600;border: 1px solid #b5b5b5;"><strong>Grand Total</strong></td>
                    <td valign="top" colspan="1" style="  color: #000;	font-family: arial, sans-serif;	font-size: 12px; text-align: right; font-weight:600;border: 1px solid #b5b5b5;">
                        <strong><?php echo show_price_client($order_list[0]['order_total_amount']); ?></strong></td>

                </tr>
                
                <?php if($order_list[0]['paid_amount'] != ''){ ?>
					
					<? $remaining_amount = $order_list[0]['order_total_amount'] - $order_list[0]['paid_amount']?>
					
					<tr bgcolor="#f3f3f3">
                    <td valign="top" colspan="3" style="    color: #000;	font-family: arial, sans-serif;	font-size: 12px; text-align: right; font-weight:600;border: 1px solid #b5b5b5;"><strong>Paid Amount</strong></td>
                    <td valign="top" colspan="1" style="  color: #000;	font-family: arial, sans-serif;	font-size: 12px; text-align: right; font-weight:600;border: 1px solid #b5b5b5;">
                        <strong><?php echo show_price_client($order_list[0]['paid_amount']); ?></strong></td>
					</tr>
					
					<tr bgcolor="#f3f3f3">
                    <td valign="top" colspan="3" style="    color: #000;	font-family: arial, sans-serif;	font-size: 12px; text-align: right; font-weight:600;border: 1px solid #b5b5b5;"><strong>Pending Amount</strong></td>
                    <td valign="top" colspan="1" style="  color: #000;	font-family: arial, sans-serif;	font-size: 12px; text-align: right; font-weight:600;border: 1px solid #b5b5b5;">
                        <strong><?php echo show_price_client($remaining_amount); ?></strong></td>
					</tr>
					
				<?php } ?>	
				
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="height: 70px;"></td>
    </tr>
    
    <tr >
        <td colspan="2">			
            <table width="100%" style="font-family: arial, sans-serif; font-size:12px; border-collapse: collapse;">
                <tr>
                    <td width="5%"></td>
                    <td width="50%" style="color:#000; font-family: arial, sans-serif; font-size:12px;  text-align: left;text-decoration: overline; text-decoration-color: #333;">Authorise Signature</td>
                    <td width="45%" align="right" style="color:#000; font-family: arial, sans-serif; font-size:12px; text-align: right;text-decoration: overline; text-decoration-color: #333;">Customer Signature</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
