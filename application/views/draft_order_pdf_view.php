<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title></title>
        <style type="text/css">
            br{ display: none; }
        </style>
    </head>
    <body>
        <div style="margin: 0 auto; width: 768px; font-family: arial">
            <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                <?php
                /* Order  Type */
                if ($order_list[0]['order_availability_name'] == 'Delivery') {
                    $availability_name = 'Delivery';
                } else {
                    $availability_name = 'Pickup';
                }
                ?>
                <tr>
                    <td style="width: 45%;">
                        <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td style="font-size: 28px; font-weight: bold; color: #1a1150;">Sales Quotation</td>
                            </tr>
                            <tr>
                                <td style="font-size: 12px; color: #1a1150;"> GST Registration No: <?php echo $order_list[0]['client_gst_no']; ?></td>
                            </tr>
                            <tr>
                                <td style="font-size: 12px; color: #1a1150;"> RCB No: 199702636M</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 10%;"></td>
                    <td style="width: 45%; text-align: right;">
						<img src="https://cedelemarket.com.sg/static/media/logo.012996ad.png"alt="Cedele" style="width: 150px;" />
                        </td>
                </tr>
            </table>

            <table style="width: 100%; vertical-align: middle;" border="0" cellpadding="0" cellspacing="0" width="100%" valign="bottom">
                <tr>
                    <td style="width: 45%; color: #1a1150; padding: 0;">
                        <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">

                            <tr><td style="height: 10px;"></td></tr>
                            <tr>
                                <td>
                                    <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td style="width: 40%; color: #1a1150; font-size: 11px; line-height: 18px;"><b>Quotation No:</b></td>
                                            <td style="width: 60%; color: #1a1150; font-size: 11px; line-height: 18px; text-align: left;"><b>QTESP19-00000636</b></td>
                                        </tr>										
                                        <tr>
                                            <td style="width: 40%; color: #1a1150; font-size: 11px; line-height: 18px;"><b>Quotation Date:</b></td>
                                           
                                            <td style="width: 60%; color: #1a1150; font-size: 11px; line-height: 18px; text-align: left;"><b><?php echo date("D, d M Y h:m A"); ?></b></td>
                                        </tr>
                                        <tr>
                                            <td style="height: 2px;" colspan="2"></td>
                                        </tr>
                                        <tr>
                                            <td style="width: 40%; color: #1a1150; font-size: 11px; line-height: 18px;"><b>Order Taken By:</b></td>
                                            <td style="width: 60%; color: #1a1150; font-size: 11px; line-height: 18px; text-align: left;"><b><?php echo $order_list[0]['order_source']; ?></b></td>
                                        </tr>
										<tr>
                                            <td style="height: 2px;" colspan="2"></td>
                                        </tr>
                                        <tr>
                                            <td style="width: 40%; color: #1a1150; font-size: 11px; line-height: 18px;"><b>Attn To:</b></td>
                                            <td style="width: 60%; color: #1a1150; font-size: 11px; line-height: 18px; text-align: left;"><?php echo ucfirst(output_value($order_list[0]['outlet_name'])); ?></td>
                                        </tr>
                                        <tr>
                                            <td style="height: 2px;" colspan="2"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 10%;" >
					<?php //echo'<pre>'; print_r($order_list[0]); ?>
                    </td>
                    <td style="width: 45%;" >
                        <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td>
                                    <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr><td style="height: 10px;"></td></tr>
                                        <tr>
                                            <td style="width: 50%; color: #1a1150; font-size: 11px; line-height: 18px;"><b>Original Doc No:</b></td>
                                            <td style="width: 50%; color: #1a1150; font-size: 11px; line-height: 18px; text-align: right;"><b>QTESP19-00000636</b></td>
                                        </tr>
                                        <tr>
                                            <td style="width: 50%; color: #1a1150; font-size: 11px; line-height: 18px;"><b>BD Ref No:</b></td>
                                            <td style="width: 50%; color: #1a1150; font-size: 11px; line-height: 18px; text-align: right;"><b></b></td>
                                        </tr>
										<tr>
                                            <td style="height: 2px;" colspan="2"></td>
                                        </tr>
                                        <?php if($order_list[0]['order_availability_name'] == 'Pickup') { ?>
										<tr>
                                            <td style="width: 50%; color: #1a1150; font-size: 11px; line-height: 18px;"><b>Pick-up Outlet:</b></td>
                                            <td style="width: 50%; color: #1a1150; font-size: 11px; line-height: 18px; text-align: right;"><b><?php echo ucfirst(output_value($order_list[0]['outlet_name'])); ?></b></td>
                                        </tr>
                                        <tr>
                                            <td style="width: 50%; color: #1a1150; font-size: 11px; line-height: 18px;"><b>Pick-up Time from Outlet:</b></td>
                                            <td style="width: 50%; color: #1a1150; font-size: 11px; line-height: 18px; text-align: right;"><b><?php echo get_date_formart($order_list[0]['order_date'], 'h:i A'); ?></b></td>
                                        </tr>
                                        <?php } ?>
										<tr>
                                            <td style="height: 2px;" colspan="2"></td>
                                        </tr>
                                        <?php if($order_list[0]['order_availability_name'] == 'Delivery') { ?>
										<tr>
                                            <td style="width: 50%; color: #1a1150; font-size: 11px; line-height: 18px;"><b>Delivery Date:</b></td>
                                            <td style="width: 50%; color: #1a1150; font-size: 11px; line-height: 18px; text-align: right;"><b><?php echo get_date_formart($order_list[0]['order_date'], "D, d M Y"); ?></b></td>
                                        </tr>
										<tr>
                                            <td style="width: 50%; color: #1a1150; font-size: 11px; line-height: 18px;"><b>Time to Customer:</b></td>
                                            <td style="width: 50%; color: #1a1150; font-size: 11px; line-height: 18px; text-align: right;"><b><?php echo get_date_formart($order_list[0]['order_date'], 'h:i A'); ?></b></td>
                                        </tr>
										<?php } ?>
										<tr>
                                            <td style="height: 2px;" colspan="2"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>
            </table>
            <table border="0" cellpadding="0" cellspacing="0" style="width: 100%;" width="100%">
                <tr>
                    <td style="width: 45%;">
                        <table>
                            <tr><td style="height: 1px;"></td></tr>
                            <tr>
                                <td style="color: #1a1150; font-size: 11px; padding-top: 12px; line-height: 18px;"><b> Bill To:</b></td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #1a1150; padding: 5px;">
                                    <table border="0" cellpadding="0" cellspacing="0" style="width: 100%;" width="100%">
                                        <tr><td style="height: 1px;"></td></tr>
                                        <tr>
                                            <td style="color: #1a1150; font-size: 11px; line-height: 18px;">
                                                <?php echo stripslashes(ucwords($order_list[0]['order_customer_fname'].' '.$order_list[0]['order_customer_lname'])); ?>
                                            </td>
                                        </tr>

                                        <?php
                                        if ($order_list[0]['order_customer_billing_address_line1'] != '') {

                                            $billing_address = get_default_address_format($order_list[0]['order_customer_billing_address_line1'], $order_list[0]['order_customer_billing_unit_no1'], $order_list[0]['order_customer_billing_unit_no2'], $order_list[0]['order_customer_billing_address_line2'], DEFAULT_COUNTRY, $order_list[0]['order_customer_billing_postal_code']);
                                        } else {

                                            $billing_address = get_default_address_format($order_list[0]['order_customer_address_line1'], $order_list[0]['order_customer_unit_no1'], $order_list[0]['order_customer_unit_no2'], $order_list[0]['order_customer_address_line2'], DEFAULT_COUNTRY, $order_list[0]['order_customer_postal_code']);
                                        }
                                        ?>
										<?php if($order_list[0]['order_availability_name'] == 'Delivery') { ?> 
                                        <tr>
                                            <td style="color: #1a1150; font-size: 11px; line-height: 18px;">
                                                <?php echo $billing_address; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="height: 10px;"></td>
                                        </tr>
                                        <?php } ?>
                                        
                                        <tr> 
                                            <td style="color: #1a1150; font-size: 11px; line-height: 18px;">
                                                Tel No: 
                                                <?php if ($order_list[0]['order_customer_mobile_no'] != '' && $order_list[0]['order_customer_mobile_no'] != 'null') { ?>
                                                    <?php echo $order_list[0]['order_customer_mobile_no']; ?>
                                                <?php } ?>
                                            </td>
                                        </tr>

                                        <tr><td style="height: 1px;"></td></tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 10%;"></td>
                    <td style="width: 45%;">
                        <table>
                            <tr><td style="height: 1px;"></td></tr>
                            <?php if ($order_list[0]['order_availability_name'] != "Pickup") { ?>
                                <tr>
                                    <td style="color: #1a1150; font-size: 11px; padding-top: 12px; line-height: 18px;"><b> <?php if ($order_list[0]['order_availability_name'] == 'Delivery') { ?> Deliver To: <?php } else { ?>  Pickup At: <?php } ?></b></td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #1a1150; padding: 5px;">
                                        <table border="0" cellpadding="0" cellspacing="0" style="width: 100%;" width="100%">
                                            <tr><td style="height: 1px;"></td></tr>
                                            <tr>
                                                <td style="color: #1a1150; font-size: 11px; line-height: 18px;">
                                                    <?php echo stripslashes(ucwords($order_list[0]['order_customer_fname'].' '.$order_list[0]['order_customer_lname'])); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="color: #1a1150; font-size: 11px; line-height: 18px;">
                                                    <?php echo get_default_address_format($order_list[0]['order_customer_address_line1'], $order_list[0]['order_customer_unit_no1'], $order_list[0]['order_customer_unit_no2'], $order_list[0]['order_customer_address_line2'], DEFAULT_COUNTRY, $order_list[0]['order_customer_postal_code']); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="height: 10px;"></td>
                                            </tr>
                                            
                                            <tr> 
                                                <td style="color: #1a1150; font-size: 11px; line-height: 18px;">
                                                    Tel No:
                                                    <?php if ($order_list[0]['order_customer_mobile_no'] != '' && $order_list[0]['order_customer_mobile_no'] != 'null') { ?>
                                                        <?php echo $order_list[0]['order_customer_mobile_no']; ?>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <tr><td style="height: 1px;"></td></tr>
                                        </table>
                                    </td>
                                </tr>
                            <?php } else { ?>

                                <tr>
                                    <td style="color: #1a1150; font-size: 11px; padding-top: 12px; line-height: 18px;"><b> Pickup At:</b></td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #1a1150; padding: 5px;">
                                        <table border="0" cellpadding="0" cellspacing="0" style="width: 100%;" width="100%">
                                            <tr><td style="height: 1px;"></td></tr>
                                            <tr>
                                                <td style="color: #1a1150; font-size: 11px; line-height: 18px;">
                                                    <?php echo stripslashes(ucwords($order_list[0]['order_customer_fname'].' '.$order_list[0]['order_customer_lname'])); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="color: #1a1150; font-size: 11px; line-height: 18px;">
                                                    <?php echo stripslashes($order_list[0]['order_customer_mobile_no']); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="height: 10px;"></td>
                                            </tr>
                                            <tr>
                                                <td style="color: #1a1150; font-size: 11px; line-height: 18px;">
                                                    <?php echo stripslashes(ucwords($order_list[0]['outlet_name'])); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="color: #1a1150; font-size: 11px; line-height: 18px;"><?php echo get_default_address_format($order_list[0]['outlet_address_line1'], $order_list[0]['outlet_unit_number1'], $order_list[0]['outlet_unit_number2'], $order_list[0]['outlet_address_line2'], DEFAULT_COUNTRY, $order_list[0]['outlet_postal_code']); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="height: 10px;"></td>
                                            </tr>
                                            
                                            <tr> 
                                                <td style="color: #1a1150; font-size: 11px; line-height: 18px;">
                                                    Tel No:
                                                    <?php if ($order_list[0]['outlet_phone'] != '' && $order_list[0]['outlet_phone'] != 'null') { ?>
                                                        <?php echo $order_list[0]['outlet_phone']; ?>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <tr><td style="height: 1px;"></td></tr>
                                        </table>
                                    </td>
                                </tr>

                            <?php } ?>	
                        </table>
                    </td>
                </tr>
            </table>
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr><td style="height: 8px;"></td></tr>
            </table>
            <!-- Header end -->
            <!-- Order info -->

            <?php $company_currency_symbol = '$'; ?>

            <table bgcolor="#dddddd" border="1" cellpadding="5" cellspacing="0" width="100%" style="border-collapse: collapse; font-weight: bold;">
                <tr>
                    <th align="center" width="5%" style="font-size: 13px;">No.</th>
                    <th align="center" width="50%" style="font-size: 13px;">Menu</th>
                    <th align="center" width="15%" style="font-size: 13px;">Qty</th>
                    <th align="center" width="15%" style="font-size: 13px;">Price/Unit</th>
                    <th align="center" width="15%" style="font-size: 13px;">Amount</th>
                </tr>
                <?php $inc_val = '1'; ?>
                <?php foreach ($order_item as $item) { ?>

                    <tr bgcolor="#ffffff">
                        <td valign="top" align="center" style="font-size: 11px; color: #1a1150; border-bottom: none; border-top: none;">
                            <?php echo $inc_val; ?>
                        </td>
                        <td align="left" valign="top" style="font-size: 11px; color: #1a1150; border-bottom: none; border-top: none;">
                            <b><?php echo ucwords(stripslashes($item['item_name'])); ?><?php echo ($item['item_specification'] != "") ? "(" . $item['item_specification'] . ")" : ""; ?></b>
                            <?php
                            if (!empty($item['modifiers'])) {
                                echo ' <ul>';

                                $k = 0;
                                $j = 0;
                                foreach ($item['modifiers'] as $modifier) {

                                    $modifiers = "";
                                    $modifiers .= stripslashes($modifier['order_modifier_name']);
                                    if (!empty($modifier['modifiers_values'])) {
                                        $i = 0;
                                        foreach ($modifier['modifiers_values'] as $modifier_value) {
                                            if ($k == 0) {
                                                $modifiers .= "&nbsp;&nbsp;";
                                            }
                                            $k = 1;

                                            $modifiers .= stripslashes($modifier_value['order_modifier_name']);

                                            $i++;
                                        }
                                    }

                                    echo "<li>" . $modifiers . "</li>";
                                    $k = 0;
                                    $j++;
                                }

                                echo ' </ul>';
                            }
                            ?>
                            <?php
                            if (!empty($item['extra_modifiers'])) {
                                echo ' <ul>';
                                //  echo "".get_label('lbl_extra_modifier')."";
                                $x = 0;
                                $y = 0;
                                foreach ($item['extra_modifiers'] as $extra_modifier) {   // print_r($extra_modifiers); exit;
                                    $extra_modifiers = "";
                                    $extra_modifiers .= stripslashes($extra_modifier['order_extra_modifier_name']);
                                    if (!empty($extra_modifier['modifiers_values'])) {
                                        $i = 0;
                                        foreach ($extra_modifier['modifiers_values'] as $extra_modifiers_value) {
                                            if ($x == 0) {
                                                $extra_modifiers .= "&nbsp;&nbsp;";
                                            }
                                            $x = 1;

                                            $extra_modifiers .= stripslashes($extra_modifiers_value['order_extra_modifier_name']) . "(" . $company_currency_symbol . ' ' . $extra_modifiers_value['order_extra_modifier_price'] . ")";

                                            $i++;
                                        }
                                    }

                                    echo "<li>" . $extra_modifiers . "</li>";
                                    $x = 0;
                                    $y++;
                                }

                                echo "</ul>";
                            }
                            ?>
                            <?php
                            if (!empty($item['set_menu_component'])) {
                                echo ' <ul>';
                                $j = 0;
                                foreach ($item['set_menu_component'] as $menu_component) {

                                    $menu_set = "";
                                    foreach ($menu_component['product_details'] as $product_detail) {

                                        if ($j != 0) {
                                            $menu_set .= "";
                                        }
                                        $pro_price = ($product_detail['menu_product_price'] > 0 ? " (+" . show_price($product_detail['menu_product_price']) . ")" : '');
                                        $pro_qty = ($product_detail['menu_product_qty'] == 0 ? 1 : $product_detail['menu_product_qty']);
                                        $menu_set .= "<li>".$pro_qty . " X " . stripslashes($product_detail['menu_product_name']) . $pro_price;

                                        if (!empty($product_detail['modifiers'])) {
                                            $menu_set .= "(";
                                            $i = 0;
                                            $k = 0;
                                            foreach ($product_detail['modifiers'] as $menu_modifier) {
                                                if ($i != 0) {
                                                    /* modifier comma */
                                                    $menu_set .= "";
                                                }
                                                $menu_set .= stripslashes($menu_modifier['order_modifier_name']);

                                                if (!empty($menu_modifier['modifiers_values'])) {

                                                    foreach ($menu_modifier['modifiers_values'] as $menu_modifier_value) {

                                                        if ($k == 0) {
                                                            $menu_set .= "-";
                                                        }
                                                        $k = 1;
                                                        $menu_set .= stripslashes($menu_modifier_value['order_modifier_name']);
                                                    }
                                                }
                                                $k = 0;
                                                $i++;
                                            }
                                            $menu_set .= ")";
                                        }
                                        
                                        $menu_set .= "</li>";
										
										
										
                                        $j++;
                                    }
                                    echo  $menu_set;
                                }
                                echo "</ul>";
                            }
                            ?>
                        </td>
                        <td align="center" valign="top" style="font-size: 11px; color: #1a1150; border-bottom: none; border-top: none;">
                            <?php echo $item['item_qty']; ?>
                        </td>
                        <td align="center" valign="top" style="font-size: 11px; color: #1a1150; border-bottom: none; border-top: none;">
                            <?php echo $company_currency_symbol . ' ' . $item['item_unit_price']; ?>
                        </td>
                        <td align="center" valign="top" style="font-size: 11px; color: #1a1150; border-bottom: none; border-top: none;">
                            <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td style="width: 40%; font-size: 11px; color: #1a1150; text-align: left;">
                                        <?php echo $company_currency_symbol; ?>
                                    </td>
                                    <td style="width: 60%; font-size: 11px; color: #1a1150; text-align: right;">
                                        <?php echo $item['item_total_amount']; ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <?php $inc_val++; ?>
                <?php } ?>
            </table>
			
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr><td style="height: 8px;"></td></tr>
            </table>
			
            <table style="width: 100%;" border="0" cellspacing="0" width="100%">
                <tr>
                    <td style="width: 50%; vertical-align: top;">
                        <table style="width: 100%;" border="0" cellspacing="0" width="100%">
                            <tr>
                                <td style="width: 30%; font-size: 11px; color: #1a1150; vertical-align: top; line-height: 18px;"><b>Payment Term:</b></td>
                                <?php
                                if ($order_list[0]['order_payment_mode'] == '1') {
                                    $payment_type = 'CASH';
                                } else if ($order_list[0]['order_payment_mode'] == '2') {
                                    $payment_type = 'ONLINE';
                                } else if ($order_list[0]['order_payment_mode'] == '3') {
                                    $payment_type = 'STRIPE';
                                } else if ($order_list[0]['order_payment_mode'] == '4') {
                                    $payment_type = 'PROMOTION';
                                } else {
                                    $payment_type = 'POS';
                                }
                                ?>
                                
                                <td style="width: 70%; font-size: 11px; color: #1a1150; vertical-align: top; line-height: 18px;">PAID BY
                                <?php if($payment_type != 'CASH') { ?> 
									<?php echo $payment_type; ?>
                                <?php } ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 30%; font-size: 11px; color: #1a1150; vertical-align: top; line-height: 18px;"><b>Remarks:</b></td>
                                <td style="width: 70%; font-size: 11px; color: #1a1150; vertical-align: top; line-height: 18px;"><?php echo $order_list[0]['order_remarks']; ?>
                                </td>
                            </tr>
                        </table>    
                    </td>
                    <td style="width: 50%; vertical-align: top;">
                        <table border="1" style="width: 100%; border-collapse: collapse; " cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td style="width: 50%;" bgcolor="#dddddd">
                                    <table style="width: 100%;" border="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td style="width: 2%"></td>
                                            <td style="width: 98%; background: #ddd; color: #1a1150; font-size: 11px; line-height: 18px; padding: 0 5px;"><b>Sub-total Amount</b></td>
                                        </tr>
                                    </table>
                                </td>
                                <td style="width: 50%; padding: 0 5px;">
                                    <table style="width: 100%;" border="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td style="width: 50%; color: #1a1150; font-size: 11px; line-height: 18px;">SGD</td>
                                            <td style="color: #1a1150; font-size: 11px; line-height: 18px; text-align: right; width: 50%;">

                                                <table style="width:100%">
                                                    <tr>
                                                        <td style="width: 90%; font-size: 11px;"><?php echo $order_list[0]['order_sub_total']; ?></td>
                                                        <td style="width: 10%"></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
							<?php if ($order_list[0]['order_discount_amount'] > 0) { ?>
                                <tr>
                                    <td style="width: 50%;" bgcolor="#dddddd">
                                        <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td style="width: 2%"></td>
                                                <td style="width: 98%; background: #ddd; color: #1a1150; font-size: 11px; line-height: 18px; padding: 0 5px;"><b>Promo Code</b></td>
                                            </tr>
                                        </table>
                                    </td>

                                    <td style="width: 50%; padding: 0 5px;">
                                        <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td style="width: 50%; color: #1a1150; font-size: 11px; line-height: 18px;">SGD</td>
                                                <td style="color: #1a1150; font-size: 11px; line-height: 18px; text-align: right; width: 50%;">
                                                    <table style="width:100%">
                                                        <tr>
                                                            <td style="width: 90%; font-size: 11px;"><?php echo number_format($order_list[0]['order_discount_amount'],2); ?></td>
                                                            <td style="width: 10%"></td>
                                                        </tr>
                                                    </table>

                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            <?php } ?>
							<?php if ($order_list[0]['order_availability_name'] != "Pickup") { ?>
								<?php if (isset($order_list[0]['order_delivery_charge']) && $order_list[0]['order_delivery_charge'] != '') { ?>
									<tr>
                                        <td style="width: 50%;" bgcolor="#dddddd">
                                            <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td style="width: 2%"></td>
                                                    <td style="width: 98%; background: #ddd; color: #1a1150; font-size: 11px; line-height: 18px; padding: 0 5px;"><b>Delivery Charge</b></td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td style="width: 50%; padding: 0 5px;">
                                            <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td style="width: 50%; color: #1a1150; font-size: 11px; line-height: 18px;">SGD</td>
                                                    <td style="color: #1a1150; font-size: 11px; line-height: 18px; text-align: right; width: 50%;">
                                                        <table style="width:100%">
                                                            <tr>
                                                                <td style="width: 90%; font-size: 11px;"><?php echo $order_list[0]['order_delivery_charge']; ?></td>
                                                                <td style="width: 10%"></td>
                                                            </tr>
                                                        </table></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
									<?php } ?>
									<?php if (isset($order_list[0]['order_additional_delivery']) && $order_list[0]['order_additional_delivery'] > 0) { ?>
                                    <tr>
                                        <td style="width: 50%;" bgcolor="#dddddd">
                                            <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td style="width: 2%"></td>
                                                    <td style="width: 98%; background: #ddd; color: #1a1150; font-size: 11px; line-height: 18px; padding: 0 5px;"><b>Additional Deliverty Charge</b></td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td style="width: 50%; padding: 0 5px;">
                                            <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td style="width: 50%; color: #1a1150; font-size: 11px; line-height: 18px;">SGD</td>
                                                    <td style="color: #1a1150; font-size: 11px; line-height: 18px; text-align: right; width: 50%;">
                                                        <table style="width:100%">
                                                            <tr>
                                                                <td style="width: 90%; font-size: 11px;"><?php echo $order_list[0]['order_additional_delivery']; ?></td>
                                                                <td style="width: 10%"></td>
                                                            </tr>
                                                        </table></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } ?>
							
							<?php if (isset($order_list[0]['order_special_discount_amount']) && $order_list[0]['order_special_discount_amount'] > 0) { ?>
                                    <tr>
                                        <td style="width: 50%;" bgcolor="#dddddd">
                                            <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td style="width: 2%"></td>
                                                    <td style="width: 98%; background: #ddd; color: #1a1150; font-size: 11px; line-height: 18px; padding: 0 5px;"><b>Discount</b></td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td style="width: 50%; padding: 0 5px;">
                                            <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td style="width: 50%; color: #1a1150; font-size: 11px; line-height: 18px;">SGD</td>
                                                    <td style="color: #1a1150; font-size: 11px; line-height: 18px; text-align: right; width: 50%;">
                                                        <table style="width:100%">
                                                            <tr>
                                                                <td style="width: 90%; font-size: 11px;"><?php echo $order_list[0]['order_special_discount_amount']; ?></td>
                                                                <td style="width: 10%"></td>
                                                            </tr>
                                                        </table></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                <?php } ?>

                            <?php if ($order_list[0]['order_service_charge'] != '' && $order_list[0]['order_service_charge'] != '0') { ?>

                                <tr>
                                    <td style="width: 50%;" bgcolor="#dddddd">
                                        <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td style="width: 2%"></td>
                                                <td style="width: 98%; background: #ddd; color: #1a1150; font-size: 11px; line-height: 18px; padding: 0 5px;"><b>Service Charge (<?php echo floatval($order_list[0]['order_service_charge']); ?>%)</b>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td style="width: 50%; padding: 0 5px;">
                                        <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td style="width: 50%; color: #1a1150; font-size: 11px; line-height: 18px;">SGD</td>
                                                <td style="color: #1a1150; font-size: 11px; line-height: 18px; text-align: right; width: 50%;">
                                                    <table style="width:100%">
                                                        <tr>
                                                            <td style="width: 90%; font-size: 11px;"><?php echo ( isset($order_list[0]['order_service_charge_amount']) && $order_list[0]['order_service_charge_amount'] != '') ? $order_list[0]['order_service_charge_amount'] : 'N/A' ?></td>
                                                            <td style="width: 10%"></td>
                                                        </tr>
                                                    </table> 
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                            <?php } ?>

                            <tr>
                                <td style="width: 50%;" bgcolor="#dddddd">
                                    <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td style="width: 2%"></td>
                                            <td style="width: 98%; background: #ddd; color: #1a1150; font-size: 11px; line-height: 18px; padding: 0 5px;"><b>Total Amount (Inclusive GST)</b></td>
                                        </tr>
                                    </table>
                                </td>
                                <td style="width: 50%; padding: 0 5px;">
                                    <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td style="width: 50%; color: #1a1150; font-size: 11px; line-height: 18px;">SGD</td>
                                            <td style="color: #1a1150; font-size: 11px; line-height: 18px; text-align: right; width: 50%;">
                                                <table style="width:100%">
                                                    <tr>
                                                        <td style="width: 90%; font-size: 11px;"><?php echo $order_list[0]['order_total_amount']; ?></td>
                                                        <td style="width: 10%"></td>
                                                    </tr>
                                                </table>

                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            
                            <tr>
                                <td style="width: 50%;" bgcolor="#dddddd">
                                    <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td style="width: 2%"></td>
                                            <td style="width: 98%; background: #ddd; color: #1a1150; font-size: 11px; line-height: 18px; padding: 0 5px;"><b>GST Inclusive (7%)</b></td>
                                        </tr>
                                    </table>
                                </td>
                                <td style="width: 50%; padding: 0 5px;">
                                    <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td style="width: 50%; color: #1a1150; font-size: 11px; line-height: 18px;">SGD</td>
                                            <td style="color: #1a1150; font-size: 11px; line-height: 18px; text-align: right; width: 50%;">
                                                <table style="width:100%">
                                                    <tr>
                                                        <td style="width: 90%; font-size: 11px;"><?php echo get_inclusive_gst_amount($order_list[0]['order_total_amount']); ?></td>
                                                        <td style="width: 10%"></td>
                                                    </tr>
                                                </table>

                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                        </table>
						<table border="0" style="width: 100%; border-collapse: collapse; " cellpadding="0" cellspacing="0" width="100%">
							<tr><td style="height: 10px;"></td></tr>
							<tr>
								<td style="font-size: 11px; color: #1a1150; vertical-align: top; line-height: 18px;"><b>Acceptance</b></td>
							</tr>
							<tr>
								<td style="font-size: 11px; color: #1a1150; vertical-align: top; line-height: 18px;">I / We confirm & accept the above quote</td>
							</tr>
							<tr>
								<td style="height: 50px;"></td>
							</tr>
							<tr>
								<td style="font-size: 11px; color: #1a1150; vertical-align: top; line-height: 18px; border-top: 1px solid #000; text-align: center;">Signature / Co Stamp / Date</td>
							</tr>
						</table>
					</td>
                </tr>
            </table>

			<table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td style="height: 280px;"></td>
                </tr>
            </table>
            <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%" style="border: 1px solid #000;">
                <tr>
					<td width="10" border="0"></td>
                    <td width="100%" style="color: #1a1150; line-height: 18px; font-size: 11px;">
						<table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%" >
							<tr>
								<td style="height: 10px;"></td>
							</tr>
							<tr><td style="font-size: 11px; font-weight: bold; color: #1a1150; vertical-align: top; line-height: 18px;">Terms & Conditions :</td></tr>
							<tr><td style="font-size: 11px; font-weight: bold; color: #1a1150; vertical-align: top; line-height: 18px;"><b>1.</b> Prices are inclusive of 7% GST.</td></tr>
							<tr><td style="font-size: 11px; color: #1a1150; vertical-align: top; line-height: 18px;"><b>2.</b> Prices and availability of items are subject to change without prior notice.</td></tr>
							<tr><td style="font-size: 11px; color: #1a1150; vertical-align: top; line-height: 18px;"><b>3.</b> <b>Orders are to be placed 2 working days in advance before 11am. Working hours are between 9am to 6pm from Monday to Friday except public
							holidays.</b> For urgent orders that require less than 2 working days,please email us at <a href="mailto:orders@cedeledepot.com" style="text-decoration: underline; color: #1a1150;">orders@cedeledepot.com</a>. We will try our best to accommodate your needs. </td></tr>
							<tr><td style="font-size: 11px; color: #1a1150; vertical-align: top; line-height: 18px;"><b>4.</b> Not valid for any further discounts including tenant passes and vouchers.</td></tr>
							<tr><td style="font-size: 11px; color: #1a1150; vertical-align: top; line-height: 18px;"><b>5.</b> For payment via credit cards, full payment upon confirmation of order is required.</td></tr>
							<tr><td style="font-size: 11px; color: #1a1150; vertical-align: top; line-height: 18px;"><b>6.</b> Payment can be made via credit card in advance or company cheque upon delivery.</td></tr>
							<tr><td style="font-size: 11px; color: #1a1150; vertical-align: top; line-height: 18px;"><b>7.</b> A penalty of 50% of the total order amount will be levied upon cancellation of confirmed orders.</td></tr>
							<tr><td style="font-size: 11px; font-weight: bold; color: #1a1150; vertical-align: top; line-height: 18px;"><b>8.</b> No cancellation or changes are allowed for confirmed orders once full order amount has been billed.</td></tr>
							<tr><td style="font-size: 11px; font-weight: bold; color: #1a1150; vertical-align: top; line-height: 18px;"><b>9.</b> Minimum order of $50 for delivery. For orders below $300, standard delivery charge of $25 per location applies. Free delivery for orders above $300
							(except for Festive T&C).</td></tr>
							<tr><td style="font-size: 11px; color: #1a1150; vertical-align: top; line-height: 18px;"><b>10.</b> Our food items are presented on disposable packaging. Prices exclude set-ups and decorative items.</td></tr>
							<tr><td style="font-size: 11px; font-weight: bold; color: #1a1150; vertical-align: top; line-height: 18px;"><b>11.</b> We deliver daily from 7am to 8pm.</td></tr>
							<tr><td style="font-size: 11px; color: #1a1150; vertical-align: top; line-height: 18px;"><b>12.</b> For delivery to Sentosa, Tuas, Jurong Island, Jurong Shipyard and Jurong Port, an additional $15 location fee applies.</td></tr>
							<tr><td style="font-size: 11px; color: #1a1150; vertical-align: top; line-height: 18px;"><b>13.</b> For delivery timing between 7am-9am, an additional $15 surcharge applies.</td></tr>
							<tr><td style="font-size: 11px; color: #1a1150; vertical-align: top; line-height: 18px;"><b>14.</b> We endeavour to get your food to you on-time. Delivery times are usually within the 1-hour time slot you have selected. <b style="text-decoration: underline;"><i>Please allow an additional grace
							period of 45 minutes after your preferred delivery time slot for unforeseen weather and traffic conditions.</i></b> While we endeavour to fulfil all deliveries, The
							Bakery Depot Pte Ltd shall not be liable or responsible for any late delivery or failure to deliver due to unforeseen circumstances beyond our control or due to the
							absence of the recipient or wrong address given. Charges for re-delivery applies.</td></tr>
							<tr><td style="font-size: 11px; color: #1a1150; vertical-align: top; line-height: 18px;"><b>15.</b> The Bakery Depot Pte Ltd reserves the right to amend any of the terms & conditions without prior notice.</td></tr>
							<tr>
								<td style="height: 10px;"></td>
							</tr>
						</table>
					</td>
					<td width="10" border="0"></td>
				</tr>
            </table>

            <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td>
                        <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr><td style="height: 1px;"></td></tr>
                            <tr>
                                <td style="color: #1a1150; line-height: 18px; font-size: 11px;"><b>The Bakery Depot Pte Ltd</b></td>
                            </tr>
                            <tr>
                                <td style="color: #1a1150; line-height: 18px; font-size: 11px;">1 Kaki Buki Road 1, Enterprise One, #05 - 12/13/14. Singapore 415934, Tel: (65) 6446 5795, Fax: (65) 6446 5795. Email: contactus@cedeledpot.com</td>
                            </tr>
                            <tr>
                                <td style="color: #1a1150; line-height: 18px; font-size: 11px;">Website: www.cedelepot.com</td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="https://cedelemarket.com.sg/static/media/logo.012996ad.png"alt="Cedele" style="width: 150px;" />
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

        </div>

    </body>
</html>
