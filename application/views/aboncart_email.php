                             
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 0 20px;">
<tr><td bgcolor="#858383" style="padding: 11px 25px; font: normal 16px arial; color:#fff;">  <?php echo "Cart Items"; ?> </td></tr>    

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
	</tr>
</thead>

<?php
	foreach($oder_item as $item) {
?>
<tr>
<td style=" padding: 10px;"><?php echo ucwords(stripslashes($item['cart_item_product_name'])); ?></td>

<td  style=" padding: 10px;">
<?php /*
if(!empty($item['modifiers']))
		{
			$overall_section = array();
			foreach($item['modifiers'] as $order_modifier)
			{
				$order_modifier_name = stripcslashes($order_modifier['cart_modifier_name']);
				$sel_modifier_values = '';

				if(!empty($order_modifier['modifiers_values']))
				{
					foreach($order_modifier['modifiers_values'] as $order_modifier_value)
					{
						if($sel_modifier_values !='' && $order_modifier_value !='')
						{
							$sel_modifier_values.=",";
						}
						$sel_modifier_values.=stripcslashes($order_modifier_value['cart_modifier_name']);
					}
				}

				/*$overall_section[] = $order_modifier_name.': '.$sel_modifier_values;*/

		/*		$overall_section[] = $sel_modifier_values;
                print_r($overall_section);
			}

		} */
?>
						<?php if(!empty($item['modifiers']))  
							  {
							  $k=0;$j=0;	  
							  foreach($item['modifiers'] as $modifier){ 

								  $modifiers="";
								  $modifiers .= $modifier['cart_modifier_name'];
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
										  $modifiers .=$modifier_value['cart_modifier_name'];
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

					</td>

					<td  style=" padding: 10px;"><?php echo show_price_client($item['cart_item_unit_price'],$company['client_currency']); ?></td>
					<td  style=" padding: 10px;"><?php echo $item['cart_item_qty']; ?></td>
					<td  style=" padding: 10px;"><?php echo show_price_client($item['cart_item_total_price'],$company['client_currency']); ?></td>
					</tr>
					<?php } ?>

					<?php if ( isset($cart_details['cart_delivery_charge']) && $cart_details['cart_delivery_charge']!='' &&  $cart_details['cart_delivery_charge']!=0 ) { ?>

					<thead bgcolor="#5b5a5a" style="background: #5b5a5a; color: #fff;">
						<tr>
							<th style="font-weight: normal; padding: 10px;" align="left">Surcharges</th>
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
						<td style=" padding: 10px;"> <?php echo ( isset($cart_details['cart_delivery_charge']) && $cart_details['cart_delivery_charge']!='')? show_price_client($cart_details['cart_delivery_charge'],$company['client_currency']):'N/A' ?></td>
					</tr>
					<?php } ?>



					<tfoot>
					   
						<tr>
							<td colspan="4" align="right" style=" padding: 10px;"><?php echo "Cart Total Amount"; ?> -</td>                                                    
							 <td style=" padding: 10px;"><?php echo show_price_client($cart_details['cart_grand_total'],$company['client_currency']); ?></td>
						</tr>
					 
						 <!--<tr>
							<td colspan="4" align="right" style=" padding: 10px;"><?php echo get_label('expected_amount'); ?></td>                                                    
							<td style=" padding: 10px;"><?php echo show_price_client($order_list[0]['order_total_amount'],$company['client_currency']); ?></td>
						</tr> -->
					</tfoot>
				</table>
														
			</td>
		</tr>
	</table>
	</td>
</table>
