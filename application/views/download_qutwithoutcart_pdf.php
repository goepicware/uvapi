<table cellspacing="0" cellpadding="0" width="100%" style="font: 12px Arial, Helvetica, sans-serif; color: #000;">
 <tr>
	  <td width="50%"><img src="<?php echo $client_logo_text; ?>" width="98" height="55" alt="Logo"></td>
	  <td width="50%" style="text-align: right;font-size: 10px;">
		<b style="font-size: 14px;"><?php echo $company_details['client_name']; ?></b><br>
		<?php echo $company_details['client_company_address']; ?><br>
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
						
						<?php if(count($cart_details) > 0) { ?>
							
						<tr>
							<td style="color:#787878; font-family: arial, sans-serif; font-size:15px; padding:7px;line-height:20px;text-align: center; border: 1px solid #b5b5b5;vertical-align: top;"><?php echo $i; ?></td>
							<td style="color:#787878; font-family: arial, sans-serif; font-size:15px;  padding-top: 10px; padding-bottom: 10px; padding-left: 10px; vertical-align: middle; line-height:20px;text-align: left; border: 1px solid #b5b5b5;margin-left:10px;">
								<strong style="font-weight:600; color:#000; font-family: arial, sans-serif; font-size:15px;">
									<?php echo ucwords(stripslashes($cart_details['product_name'])); ?>
								</strong>
								<?php if($cart_details['breaktime_enable']=='Yes' && (int)$cart_details['breaktime_count']>0) { ?><br><div style="font-weight:600; color:#000; font-family: arial, sans-serif; font-size:14px;text-align:right;align:right;float:right">Break 1 : <?php echo date("h:i a", strtotime($cart_details['breaktime_started'])); ?></div><?php } ?><br>
								<?php if(!empty($cart_details['modifiers'])){
								 $k=0;$j=0;	?>
									<strong style="font-weight:600; color:#000; font-family: arial, sans-serif; font-size:15px;">MODIFIER</strong><br>
										<?php foreach($cart_details['modifiers'] as $order_modifier){ ?>
											<b><?php echo stripcslashes($order_modifier->modifier_name);?></b><br>
											<?php if (!empty($order_modifier->modifiers_values)) { ?>
												<?php foreach ($order_modifier->modifiers_values as $modifier_value1) { ?>
												<?php echo $modifier_value1->modifier_value_name ?> <?php echo ((float)$modifier_value1->modifier_value_price > 0) ? '( S$ '.number_format($modifier_value1->modifier_value_price, 2).' )' : ''; ?>,<br>
												<?php } ?>
											<?php } ?>	
										<?php } ?>
								<?php } ?>
								
								
								<?php if(!empty($cart_details['addons_setup'])) { ?>
									
									<br>
								<strong style="font-weight:600; color:#000; font-family: arial, sans-serif; font-size:15px;">ADD-ONS </strong>
								
									<?php foreach($cart_details['addons_setup'] as $addons) { ?>
											 <h3 style="font-weight:600; color:#000; font-family: arial, sans-serif; font-size:15px; margin:0; padding:5px 0 5px 30px;"><?php echo stripslashes($addons->addon_setup_title);?></h3>
											 <?php
												foreach($addons->addons_setup_values as $addons_setup) { ?>
													 <p style="color:#000; font-family: arial, sans-serif; font-size:15px; margin:0; padding:0px 0 0px 60px;"><?php echo stripslashes($addons_setup->addon_setup_val_title);?><?php echo ' ('.$addons_setup->addon_setup_val_qty.' X  S$ '.number_format($addons_setup->addon_setup_val_price, 2).')';?>
												</p>
											<?php } ?>

									<?php } ?>

								<?php } ?>
								
								<?php if(!empty($cart_details['setup'])) { ?> 
									 <br><strong style="font-weight:600; color:#000; font-family: arial, sans-serif; font-size:15px;"><?php echo $cart_details['setup'][0]->setuptype; ?> Setup</strong><br>
									<p style="padding:0px 0 0px 30px;">		
									<?php foreach($cart_details['setup'] as $setupvl){ ?>	 
										
										<b><?php echo $setupvl->setupname;?></b> <br>  <?php echo $setupvl->setupdescription;?> (+ <?php echo $setupvl->setupprice; ?>) <br>
										
									<?php } ?>	
									</p> 
								<?php } ?>
	 
												 
								<?php if(!empty($cart_details['special_notes'])) { ?> 
									 <br><strong style="font-weight:600; color:#000; font-family: arial, sans-serif; font-size:15px;">Special Notes</strong><br>
									<p style="padding:0px 0 0px 30px;">		
									<?php echo $cart_details['special_notes']; ?>	
									</p> 
								<?php } ?>					  
												   
							</td>
							<td style="color:#787878; font-family: arial, sans-serif; font-size:15px; padding:7px;line-height:20px;text-align: center; border: 1px solid #b5b5b5;vertical-align: top;">
								<?php echo $cart_details['product_qty']; ?>
							</td>
							<td style="color:#787878; font-family: arial, sans-serif; font-size:15px; padding:7px;line-height:20px;text-align: center; border: 1px solid #b5b5b5;vertical-align: top;">
								<?php echo $cart_details['product_subTotal']; ?>
							</td>
						</tr>
						
						<?php $i++ ?>
						
						<?php } ?>	
						
						
						
						<?php
						
						$cartGrandTotal = ((float)$cart_details['product_grandTotal'] - (float)$promotion_amount);
						
						$gstAmount = 0;
						$gstPar = (float)$client_gst_charge;
						if($gstPar > 0) {
								$gstAmount  = ($gstPar /100 ) * $cartGrandTotal; 
						} 
						
						$cartGrandTotal = $cartGrandTotal + $gstAmount;
						?>
						
						<tr>
							<td colspan="3" style="    color: #000;	font-family: arial, sans-serif;	font-size: 15px;	padding: 7px;	line-height: 20px;	text-align: right; font-weight:600;border: 1px solid #b5b5b5;    background: #f3f3f3; "><strong>Subtotal</strong></td>
							<td colspan="1" style="    color: #000;	font-family: arial, sans-serif;	font-size: 15px;	padding: 7px;	line-height: 20px; text-align: center; font-weight:600;border: 1px solid #b5b5b5;    background: #f3f3f3;text-align:right;"><strong>S$ 
							<?php echo number_format($cart_details['product_grandTotal'],2); ?>
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
							<td colspan="3" style="    color: #000;	font-family: arial, sans-serif;	font-size: 15px;	padding: 7px;	line-height: 20px;	text-align: right; font-weight:600;border: 1px solid #b5b5b5;    background: #f3f3f3; "><strong>GST(<?php echo $client_gst_charge; ?>%)</strong></td>
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
