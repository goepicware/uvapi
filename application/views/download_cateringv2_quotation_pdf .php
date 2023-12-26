<table cellspacing="0" cellpadding="0" width="100%" style="font: 12px Arial, Helvetica, sans-serif; color: #000;">
 <tr>
	  <td width="50%"><img src="<?php echo $client_logo_text; ?>" width="98" height="55" alt="Logo"></td>
	  <td width="50%" style="text-align: right;font-size: 10px;">
		<b style="font-size: 14px;"><?php echo $outlet_details['outlet_name']; ?></b><br>
		<?php echo $outlet_details['outlet_address_line1']; ?>, <?php echo $outlet_details['outlet_postal_code']; ?><br>
		<?php echo date("d/m/Y H:i A"); ?>
	  </td>
 </tr>
 <tr>
	<td colspan="2" style="border-bottom: 1px solid #000;line-height:1px;"></td>
 </tr>
 <tr>
	<td colspan="2" style="line-height:10px;">&nbsp;</td>
 </tr>
 <tr>
	<td colspan="2" align="center" style="font-size: 14px;font-weight:bold;">Quotation</td>
 </tr>
 <tr>
	<td colspan="2" style="line-height:10px;">&nbsp;</td>
 </tr>
 <tr>
	<td colspan="2">
	
	<table align="center" cellpadding="0" cellspacing="0" class="responsive_w" style="background:#fff; margin:10px auto; -webkit-text-size-adjust:none; border-collapse: collapse; font-family: arial, sans-serif;">
		
			<tr>
				<td colspan="2" style="padding:15px 10px;">
					<table width="100%" style="font-family: arial, sans-serif; font-size:16px;border:1px solid #b5b5b5; border-collapse: collapse;">
						<tr>
							<th width="5%" style="font-weight:600; color:#000; font-family: arial, sans-serif; font-size:15px;text-transform:uppercase; padding:7px;text-align: center;border: 1px solid #b5b5b5;background:#f3f3f3;">NO</th>
							<th width="65%" style="font-weight:600; color:#000; font-family: arial, sans-serif; font-size:15px;text-transform:uppercase; padding:7px;text-align: center;border: 1px solid #b5b5b5;background:#f3f3f3;">DESCRIPTION</th>
							<th width="10%" style="font-weight:600; color:#000; font-family: arial, sans-serif; font-size:15px;text-transform:uppercase; padding:7px;text-align: center;border: 1px solid #b5b5b5;background:#f3f3f3;">NO OF ITEMS</th>
							<th width="20%" style="font-weight:600; color:#000; font-family: arial, sans-serif; font-size:15px;text-transform:uppercase; padding:7px;text-align: center;border: 1px solid #b5b5b5;background:#f3f3f3;">TOTAL</th>
						</tr>
						<?php $i = 1; ?>
						
						<?php foreach($cart_details['cart_items'] as $items) { 

							?>
							
						<tr>
							<td style="color:#787878; font-family: arial, sans-serif; font-size:15px; padding:7px;line-height:20px;text-align: center; border: 1px solid #b5b5b5;vertical-align: top;"><?php echo $i; ?></td>
							<td style="color:#787878; font-family: arial, sans-serif; font-size:15px;  padding-top: 10px; padding-bottom: 10px; padding-left: 10px; vertical-align: middle; line-height:20px;text-align: left; border: 1px solid #b5b5b5;margin-left:10px;">
								<strong style="font-weight:600; color:#000; font-family: arial, sans-serif; font-size:15px;">
									<?php echo ucwords(stripslashes($items['cart_item_product_name'])); ?>
								</strong>
								<?php if($cart_details['cart_details']['cart_breaktime_enable']=='Yes' && count($cart_details['cart_details']['cart_breaktime_count'])>0) { ?><br><div style="font-weight:600; color:#000; font-family: arial, sans-serif; font-size:14px;text-align:right;align:right;float:right">Break <?php echo ((int)$items['cart_item_breaktime_indexflag'] + 1); ?> : <?php echo date("h:i a", strtotime($items['cart_item_breaktime_started'])).'-'.date("h:i a", strtotime($items['cart_item_breaktime_ended'])); ?></div><?php } ?><br>
								<?php if(!empty($items['modifiers'])){
								 $k=0;$j=0;	?>
									<strong style="font-weight:600; color:#000; font-family: arial, sans-serif; font-size:15px;">MODIFIER</strong><br>
										<?php foreach($items['modifiers'] as $order_modifier){ ?>
											<b><?php echo stripcslashes($order_modifier['cart_modifier_name']);?></b><br>
											<?php if (!empty($order_modifier['modifiers_values'])) { ?>
												<?php foreach ($order_modifier['modifiers_values'] as $modifier_value1) { ?>
												<?php echo $modifier_value1['cart_modifier_name'] ?> <?php echo ((float)$modifier_value1['cart_modifier_price'] > 0) ? '( S$ '.number_format($modifier_value1['cart_modifier_price'], 2).' )' : ''; ?>,<br>
												<?php } ?>
											<?php } ?>	
										<?php } ?>
								<?php } ?>


							<?php if(!empty($items['set_menu_component'])){

								foreach($items['set_menu_component'] as $menu_component){
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
										$pro_price = ($product_detail['cart_menu_component_product_price'] > 0 ? " (+".show_price_client($product_detail['cart_menu_component_product_price'],$company['client_currency']).")" : '');
										$pro_qty = ($product_detail['cart_menu_component_product_qty'] == 0? 1 : $product_detail['cart_menu_component_product_qty']);

										$menu_set .= $pro_qty." X ".$product_detail['cart_menu_component_product_name'].$pro_price;

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
												$menu_set .= $menu_modifier['cart_modifier_name'];

												if(!empty($menu_modifier['modifiers_values']))
												{

													foreach($menu_modifier['modifiers_values'] as $menu_modifier_value)
													{

														if($k==0)
														{
															$menu_set .= "-<br>";
														}
														$k=1;
														$menu_set .=stripslashes($menu_modifier_value['cart_modifier_name']);
														$menu_set .= '<br>';
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
								
								<?php if(!empty($items['addons'])) { ?>  
									
									<strong style="font-weight:600; color:#000; font-family: arial, sans-serif; font-size:15px;">ADD-ONS</strong><br>  
									
									   <?php foreach($items['addons'] as $addons) { ?>					     
											
											<?php echo $addons['cart_addons_qty'];?> X  <?php echo $addons['cart_addons_name'];?><?php echo "(+ ".$addons['cart_addons_totalprice'].")";?> 
											
									 <?php } ?>	
								<?php } ?>
								
								<?php if(!empty($items['addons_setup'])) { ?>
									
									<br>
								<strong style="font-weight:600; color:#000; font-family: arial, sans-serif; font-size:15px;">ADD-ONS </strong>
								
									<?php foreach($items['addons_setup'] as $addons) { ?>
											 <h3 style="font-weight:600; color:#000; font-family: arial, sans-serif; font-size:15px; margin:0; padding:5px 0 5px 30px;"><?php echo stripslashes($addons['cart_addon_setup_title']);?></h3>
											 <?php
												$t_val_cnt = count($addons['addons_setup_values']);
												foreach($addons['addons_setup_values'] as $addons_setup) { ?>
													 <p style="color:#000; font-family: arial, sans-serif; font-size:15px; margin:0; padding:0px 0 0px 60px;"><?php echo stripslashes($addons_setup['cart_addon_setup_val_title']);?><?php echo ' ('.$addons_setup['cart_addon_setup_val_qty'].' X '.(float)$addons_setup['cart_addon_setup_val_price'].')';?>
												</p>
											<?php } ?>

									<?php } ?>

								<?php } ?>
								
								<?php if(!empty($items['setup'])) { ?> 
									 <br><strong style="font-weight:600; color:#000; font-family: arial, sans-serif; font-size:15px;"><?php echo $items['setup'][0]['cart_setup_type']; ?> Setup</strong><br>
									<p style="padding:0px 0 0px 30px;">		
									<?php foreach($items['setup'] as $setupvl){ ?>	 
										
										<b><?php echo $setupvl['cart_setup_name'];?></b> <br>  <?php echo $setupvl['cart_setup_description'];?> (+ <?php echo $setupvl['cart_setup_tatalprice']; ?>) <br>
										
									<?php } ?>	
									</p> 
								<?php } ?>
	 
								<?php if(!empty($items['equipment'])) { ?> 
									 <br><strong style="font-weight:600; color:#000; font-family: arial, sans-serif; font-size:15px;">Equipment</strong><br>  
									<?php foreach($items['equipment'] as $equipment){ ?>	 
										
										<?php echo $equipment['cart_equipment_qty'];?> X  <?php echo $equipment['cart_equipment_description'];?> <br>
										
									<?php } ?>	
									 
								<?php } ?>		 
												 
								<?php if(!empty($items['cart_item_special_notes'])) { ?> 
									 <br><strong style="font-weight:600; color:#000; font-family: arial, sans-serif; font-size:15px;">Special Notes</strong><br>
									<p style="padding:0px 0 0px 30px;">		
									<?php echo $items['cart_item_special_notes']; ?>	
									</p> 
								<?php } ?>					  
												   
							</td>
							<td style="color:#787878; font-family: arial, sans-serif; font-size:15px; padding:7px;line-height:20px;text-align: center; border: 1px solid #b5b5b5;vertical-align: top;">
								<?php echo $items['cart_item_qty']; ?>
							</td>
							<td style="color:#787878; font-family: arial, sans-serif; font-size:15px; padding:7px;line-height:20px;text-align: center; border: 1px solid #b5b5b5;vertical-align: top;">
								<?php echo $items['cart_item_total_price']; ?>
							</td>
						</tr>
						
						<?php $i++ ?>
						
						<?php } ?>	
						
						<?php if($cart_details['cart_details']['cart_venue_type'] == 'hall') {  ?>
						<tr>
							<td colspan="3" style="    color: #000;	font-family: arial, sans-serif;	font-size: 15px;	padding: 7px;	line-height: 20px;	text-align: right; font-weight:600;border: 1px solid #b5b5b5;    background: #f3f3f3; "><strong>Hall charges</strong></td>
							<td colspan="1" style="    color: #000;	font-family: arial, sans-serif;	font-size: 15px;	padding: 7px;	line-height: 20px; text-align: center; font-weight:600;border: 1px solid #b5b5b5;    background: #f3f3f3;"><strong>S$ <?php echo number_format($cart_details['cart_details']['cart_hall_charges'],2); ?></strong></td>

						</tr>
						<?php } ?>
						
						<?php
						
						$cartGrandTotal = ((float)$cart_details['cart_details']['cart_grand_total'] - (float)$promotion_amount);
						
						$gstAmount = 0;
						$gstPar = (float)$client_service_charge;
						if($gstPar > 0) {
								$gstAmount  = ($gstPar /100 ) * $cartGrandTotal; 
						} 
						
						$cartGrandTotal = $cartGrandTotal + $gstAmount;
						?>
						<?php if($cart_details['cart_venue_type'] == 'hall') {  ?>
						<tr>
							<td colspan="3" style="    color: #000;	font-family: arial, sans-serif;	font-size: 15px;	padding: 7px;	line-height: 20px;	text-align: right; font-weight:600;border: 1px solid #b5b5b5;    background: #f3f3f3; "><strong>Hall charges</strong></td>
							<td colspan="1" style="    color: #000;	font-family: arial, sans-serif;	font-size: 15px;	padding: 7px;	line-height: 20px; text-align: center; font-weight:600;border: 1px solid #b5b5b5;    background: #f3f3f3;text-align:right;"><strong>S$ <?php echo number_format($cart_details['cart_details']['cart_hall_charges'],2); ?></strong></td>

						</tr>
						<?php } ?>
						<tr>
							<td colspan="3" style="    color: #000;	font-family: arial, sans-serif;	font-size: 15px;	padding: 7px;	line-height: 20px;	text-align: right; font-weight:600;border: 1px solid #b5b5b5;    background: #f3f3f3; "><strong>Subtotal</strong></td>
							<td colspan="1" style="    color: #000;	font-family: arial, sans-serif;	font-size: 15px;	padding: 7px;	line-height: 20px; text-align: center; font-weight:600;border: 1px solid #b5b5b5;    background: #f3f3f3;text-align:right;"><strong>S$ 
							<?php echo number_format($cart_details['cart_details']['cart_sub_total'],2); ?>
							</strong></td>

						</tr>
						<?php if((float)$promotion_amount > 0) {  ?>
						<tr>
							<td colspan="3" style="    color: #000;	font-family: arial, sans-serif;	font-size: 15px;	padding: 7px;	line-height: 20px;	text-align: right; font-weight:600;border: 1px solid #b5b5b5;    background: #f3f3f3; "><strong>Discount</strong></td>
							<td colspan="1" style="    color: #000;	font-family: arial, sans-serif;	font-size: 15px;	padding: 7px;	line-height: 20px; text-align: center; font-weight:600;border: 1px solid #b5b5b5;    background: #f3f3f3;text-align:right;"><strong>S$ <?php echo number_format($promotion_amount,2); ?></strong></td>

						</tr>
						<?php } ?>
						<?php if($gstPar > 0) {  ?>
						<tr>
							<td colspan="3" style="    color: #000;	font-family: arial, sans-serif;	font-size: 15px;	padding: 7px;	line-height: 20px;	text-align: right; font-weight:600;border: 1px solid #b5b5b5;    background: #f3f3f3; "><strong>GST(<?php echo $client_service_charge; ?>%)</strong></td>
							<td colspan="1" style="    color: #000;	font-family: arial, sans-serif;	font-size: 15px;	padding: 7px;	line-height: 20px; text-align: center; font-weight:600;border: 1px solid #b5b5b5;    background: #f3f3f3;text-align:right;"><strong>S$ <?php echo number_format($gstAmount,2); ?></strong></td>

						</tr>
						<?php } ?>
						<tr>
							<td colspan="3" style="    color: #000;	font-family: arial, sans-serif;	font-size: 15px;	padding: 7px;	line-height: 20px;	text-align: right; font-weight:600;border: 1px solid #b5b5b5;    background: #f3f3f3; "><strong>Grand Total</strong></td>
							<td colspan="1" style="    color: #000;	font-family: arial, sans-serif;	font-size: 15px;	padding: 7px;	line-height: 20px; text-align: center; font-weight:600;border: 1px solid #b5b5b5;    background: #f3f3f3;text-align:right;"><strong>S$ <?php echo number_format($cartGrandTotal,2); ?></strong></td>

						</tr>

					</table>
				</td>
			</tr>

	</table>

   </td>
 </tr>
</table>		
