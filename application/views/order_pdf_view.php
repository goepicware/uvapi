<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title></title>
    <style type="text/css">
        br {
            display: none;
        }
    </style>
</head>
<?php $currecnySymbol = $company['company_currency_symbol']; ?>

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
                            <td style="font-size: 28px; font-weight: bold; color: #1a1150;">Receipt</td>
                        </tr>
                        <tr>
                            <td style="font-size: 12px; color: #1a1150;"> GST Registration No: <?php echo $company['company_gst_no']; ?></td>
                        </tr>
                    </table>
                </td>
                <td style="width: 10%;"></td>
                <td style="width: 45%; text-align: right;">
                    <?php
                    if (!empty($company['company_invoice_logo'])) {
                    ?>
                        <img src="<?= $company['company_invoice_logo'] ?>" alt="Cedele" style="width: 150px;" />
                    <?php
                    }
                    ?>
                </td>
            </tr>
        </table>

        <table style="width: 100%; vertical-align: middle;" border="0" cellpadding="0" cellspacing="0" width="100%" valign="bottom">
            <tr>
                <td style="width: 45%; color: #1a1150; padding: 0;">
                    <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">

                        <tr>
                            <td style="height: 10px;"></td>
                        </tr>
                        <tr>
                            <td>
                                <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td style="width: 40%; color: #1a1150; font-size: 11px; line-height: 18px;"><b>Order ID:</b></td>
                                        <td style="width: 60%; color: #1a1150; font-size: 11px; line-height: 18px; text-align: left;"><b><?php echo '#' . '' . $order_list[0]['order_local_no']; ?></b></td>
                                    </tr>
                                </table>
                                <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td style="width: 40%; color: #1a1150; font-size: 11px; line-height: 18px;"><b>Order On:</b></td>
                                        <?php
                                        $date_time_txt = (!empty($order_list[0]['order_created_on'])) ? explode(" ", $order_list[0]['order_created_on']) : array();
                                        $date_str = (!empty($date_time_txt[0])) ? date('d-F-Y', strtotime($date_time_txt[0])) : '';
                                        $time_str = (!empty($order_list[0]['order_created_on'])) ? date('h:i:A', strtotime($order_list[0]['order_created_on'])) : '';
                                        $day_str = (!empty($date_time_txt[1])) ? date('D', strtotime($date_time_txt[1])) : '';
                                        ?>
                                        <td style="width: 60%; color: #1a1150; font-size: 11px; line-height: 18px; text-align: left;"><b><?php echo $day_str; ?>,<?php echo $date_str ?></b></td>
                                    </tr>
                                    <tr>
                                        <td style="height: 2px;" colspan="2"></td>
                                    </tr>
                                </table>
                                <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td style="width: 40%; color: #1a1150; font-size: 11px; line-height: 18px;"><b><?php if ($order_list[0]['order_availability_name'] == 'Delivery') { ?>Request Outlet:<?php } else { ?>Pickup Outlet:<?php } ?></b></td>
                                        <td style="width: 60%; color: #1a1150; font-size: 11px; line-height: 18px; text-align: left;"><b><?php echo ucfirst(output_value($order_list[0]['outlet_name'])); ?></b></td>
                                    </tr>
                                </table>
                                <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td style="width: 40%; color: #1a1150; font-size: 11px; line-height: 18px;"><b>Order Taken By:</b></td>
                                        <td style="width: 60%; color: #1a1150; font-size: 11px; line-height: 18px; text-align: left;"><b><?php echo $order_list[0]['order_source']; ?></b></td>
                                    </tr>
                                    <tr>
                                        <td style="height: 2px;" colspan="2"></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width: 10%;">

                </td>
                <td style="width: 45%;">
                    <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td>

                                <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">

                                    <?php
                                    $order_date_time_txt = (!empty($order_list[0]['order_date'])) ? explode(" ", $order_list[0]['order_date']) : array();
                                    $order_date_str = (!empty($order_date_time_txt[0])) ? date('d F Y', strtotime($order_date_time_txt[0])) : '';
                                    $order_time_str = (!empty($order_list[0]['order_date'])) ? date('h:i:A', strtotime($order_list[0]['order_date'])) : '';
                                    $order_day_str = (!empty($order_date_time_txt[1])) ? date('D', strtotime($order_date_time_txt[1])) : '';
                                    ?>

                                    <tr>
                                        <td style="height: 10px;"></td>
                                    </tr>
                                    <tr>
                                        <td style="width: 45%; color: #1a1150; font-size: 11px; line-height: 18px;"><b><?php echo $availability_name; ?> Date:</b></td>
                                        <td style="width: 55%; color: #1a1150; font-size: 11px; line-height: 18px; text-align: right;"><b><?php echo $order_day_str; ?>,<?php echo $order_date_str ?></b></td>
                                    </tr>
                                </table>
                                <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td style="width: 45%; color: #1a1150; font-size: 11px; line-height: 18px;"><b>Time:</b></td>
                                        <td style="width: 55%; color: #1a1150; font-size: 11px; line-height: 18px; text-align: right;"><b><?php echo $order_time_str; ?></b></td>
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
                        <tr>
                            <td style="height: 1px;"></td>
                        </tr>
                        <tr>
                            <td style="color: #1a1150; font-size: 11px; padding-top: 12px; line-height: 18px;"><b> Bill To:</b></td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #1a1150; padding: 5px;">
                                <table border="0" cellpadding="0" cellspacing="0" style="width: 100%;" width="100%">
                                    <tr>
                                        <td style="height: 1px;"></td>
                                    </tr>
                                    <tr>
                                        <td style="color: #1a1150; font-size: 11px; line-height: 18px;"><?php echo stripslashes(ucwords($order_list[0]['customer_name'])); ?>
                                        </td>
                                    </tr>

                                    <?php
                                    if (!empty($order_list[0]['order_customer_billing_address_line1'])) {

                                        $billing_address = get_default_address_format($order_list[0]['order_customer_billing_address_line1'], $order_list[0]['order_customer_billing_unit_no1'], $order_list[0]['order_customer_billing_unit_no2'], $order_list[0]['order_customer_billing_address_line2'], '', $order_list[0]['order_customer_billing_postal_code']);
                                    } else {

                                        $billing_address = get_default_address_format($order_list[0]['order_customer_address_line1'], $order_list[0]['order_customer_unit_no1'], $order_list[0]['order_customer_unit_no2'], $order_list[0]['order_customer_address_line2'], '', $order_list[0]['order_customer_postal_code']);
                                    }
                                    ?>
                                    <?php if ($order_list[0]['order_availability_name'] == 'Delivery') { ?>
                                        <tr>
                                            <td style="color: #1a1150; font-size: 11px; line-height: 18px;"><?php echo $billing_address; ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <tr>
                                        <td style="height: 10px;"></td>
                                    </tr>
                                    <!--<tr>
                                            <td> -</td>
                                        </tr>-->
                                    <tr>
                                        <td style="color: #1a1150; font-size: 11px; line-height: 18px;">Contact No: <?php if ($order_list[0]['order_customer_mobile_no'] != '' && $order_list[0]['order_customer_mobile_no'] != 'null') { ?>
                                                <?php echo $order_list[0]['order_customer_mobile_no']; ?>
                                            <?php } ?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="height: 1px;"></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width: 10%;"></td>
                <td style="width: 45%;">
                    <table>
                        <tr>
                            <td style="height: 1px;"></td>
                        </tr>
                        <?php if ($order_list[0]['order_availability_name'] != "Pickup") { ?>
                            <tr>
                                <td style="color: #1a1150; font-size: 11px; padding-top: 12px; line-height: 18px;"><b> <?php if ($order_list[0]['order_availability_name'] == 'Delivery') { ?> Deliver To: <?php } else { ?> Pickup At: <?php } ?></b></td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #1a1150; padding: 5px;">
                                    <table border="0" cellpadding="0" cellspacing="0" style="width: 100%;" width="100%">
                                        <tr>
                                            <td style="height: 1px;"></td>
                                        </tr>
                                        <tr>
                                            <td style="color: #1a1150; font-size: 11px; line-height: 18px;"><?php echo stripslashes(ucwords($order_list[0]['customer_name'])); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="color: #1a1150; font-size: 11px; line-height: 18px;"><?php echo get_default_address_format($order_list[0]['order_customer_address_line1'], $order_list[0]['order_customer_unit_no1'], $order_list[0]['order_customer_unit_no2'], $order_list[0]['order_customer_address_line2'], DEFAULT_COUNTRY, $order_list[0]['order_customer_postal_code']); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="height: 10px;"></td>
                                        </tr>
                                        <!--<tr>
                                                <td> -</td>
                                            </tr>-->
                                        <tr>
                                            <td style="color: #1a1150; font-size: 11px; line-height: 18px;">Contact No: <?php if ($order_list[0]['order_customer_mobile_no'] != '' && $order_list[0]['order_customer_mobile_no'] != 'null') { ?><?php echo $order_list[0]['order_customer_mobile_no']; ?>
                                            <?php } ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="height: 1px;"></td>
                                        </tr>
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
                                        <tr>
                                            <td style="height: 1px;"></td>
                                        </tr>
                                        <tr>
                                            <td style="color: #1a1150; font-size: 11px; line-height: 18px;"><?php echo stripslashes(ucwords($order_list[0]['outlet_name'])); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="color: #1a1150; font-size: 11px; line-height: 18px;"><?php echo get_default_address_format($order_list[0]['outlet_address_line1'], $order_list[0]['outlet_unit_number1'], $order_list[0]['outlet_unit_number2'], $order_list[0]['outlet_address_line2'], "", $order_list[0]['outlet_postal_code']); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="height: 10px;"></td>
                                        </tr>
                                        <!--<tr>
                                                <td> -</td>
                                            </tr>-->
                                        <tr>
                                            <td style="color: #1a1150; font-size: 11px; line-height: 18px;">Contact No: <?php if ($order_list[0]['outlet_phone'] != '' && $order_list[0]['outlet_phone'] != 'null') { ?><?php echo $order_list[0]['outlet_phone']; ?>
                                            <?php } ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="height: 1px;"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                        <?php } ?>
                    </table>
                </td>
            </tr>
        </table>
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td style="height: 8px;"></td>
            </tr>
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
            <?php
            foreach ($oder_item as $key => $groupvalue) {
                $inc_val = '1';
            ?>
                <tr bgcolor="#ffffff">
                    <td valign="top" colspan="5" style="font-size: 11px;">
                        <?php echo $groupvalue['storeName']; ?>
                    </td>
                </tr>
                <?php
                foreach ($groupvalue['items'] as $pkey => $item) {
                ?>

                    <tr bgcolor="#ffffff">
                        <td valign="top" align="center" style="font-size: 11px; color: #1a1150; border-bottom: none; border-top: none;">
                            <?php echo $inc_val; ?>
                        </td>
                        <td align="left" valign="top" style="font-size: 11px; color: #1a1150; border-bottom: none; border-top: none;"><b><?php echo ucwords(stripslashes($item['itemName'])); ?>
                                <?php echo (!empty($item['itemNote'])) ? "(" . $item['item_specification'] . ")" : ""; ?></b>
                            <?php
                            if (!empty($item['comboset'])) {
                                echo ' <ul style="margin:0">';
                                $j = 0;
                                foreach ($item['comboset'] as $menu_component) {
                                    $menu_set = $menu_component['comboSetname'] . '<br/>';
                                    foreach ($menu_component['productDetails'] as $product_detail) {
                                        $menu_set .= "<div>";
                                        $pro_price = ($product_detail['productPrice'] > 0 ? " (+$" . $product_detail['productPrice'] . ")" : '');
                                        $pro_qty = ($product_detail['quantity'] == 0 ? 1 : $product_detail['quantity']);
                                        $menu_set .= $pro_qty . " X " . stripslashes($product_detail['productName']) . $pro_price;
                                        $menu_set .= "</div>";
                                        $j++;
                                    }
                                    echo "<li>" . $menu_set . "</li>";
                                }
                                echo "</ul>";
                            }
                            ?>
                        </td>
                        <td align="center" valign="top" style="font-size: 11px; color: #1a1150; border-bottom: none; border-top: none;">
                            <?php echo $item['itemQuantity']; ?>
                        </td>
                        <td align="center" valign="top" style="font-size: 11px; color: #1a1150; border-bottom: none; border-top: none; text-align: right;">
                            <?php echo show_price_client($item['itemPrice'], $currecnySymbol); ?>
                        </td>
                        <td align="center" valign="top" style="font-size: 11px; color: #1a1150; border-bottom: none; border-top: none;">
                            <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td style="width: 100%; font-size: 11px; color: #1a1150; text-align: right;">
                                        <?php echo show_price_client($item['itemTotalPrice'], $currecnySymbol); ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <?php $inc_val++;
                    ?>
            <?php
                }
            }
            ?>
        </table>

        <!-- Footer start -->
        <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td style="height: 8px;"></td>
            </tr>
        </table>
        <table style="width: 100%;" border="0" cellspacing="0" width="100%">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <table style="width: 100%;" border="0" cellspacing="0" width="100%">
                        <tr>
                            <td style="width: 40%; font-size: 11px; color: #1a1150; vertical-align: top; line-height: 18px;"><b>Remarks:</b></td>
                            <td style="width: 60%; font-size: 11px; color: #1a1150; vertical-align: top; line-height: 18px;"><?php echo $order_list[0]['order_remarks']; ?>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <table border="1" style="width: 100%; border-collapse: collapse; " cellpadding="3" cellspacing="0" width="100%">
                        <tr>
                            <td style="width: 50%;" bgcolor="#dddddd">
                                <table style="width: 100%;" border="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td style="width: 2%"></td>
                                        <td style="width: 98%; background: #ddd; color: #1a1150; font-size: 11px; line-height: 18px; padding: 0 5px;"><b>Sub-total Amount</b></td>
                                    </tr>
                                </table>
                            </td>
                            <td style="width: 50%; padding: 0 5px; text-align: right; ">
                                <?php echo show_price_client($order_list[0]['order_sub_total'], $currecnySymbol); ?>
                            </td>
                        </tr>
                        <?php
                        if ($order_list[0]['order_availability_name'] != "Pickup") {
                            if (isset($order_list[0]['order_delivery_charge']) && $order_list[0]['order_delivery_charge'] > 0) {
                        ?>
                                <tr>
                                    <td style="width: 50%;" bgcolor="#dddddd">
                                        <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td style="width: 2%"></td>
                                                <td style="width: 98%; background: #ddd; color: #1a1150; font-size: 11px; line-height: 18px; padding: 0 5px;"><b>Delivery Charge</b></td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td style="width: 50%; padding: 0 5px; text-align: right;">
                                        <?php echo show_price_client($order_list[0]['order_delivery_charge'], $currecnySymbol); ?>
                                    </td>
                                </tr>
                            <?php
                            }
                        }
                        if ($order_list[0]['order_tax_calculate_amount'] != '' && $order_list[0]['order_tax_calculate_amount'] != '0') {
                            ?>

                            <tr>
                                <td style="width: 50%;" bgcolor="#dddddd">
                                    <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td style="width: 2%"></td>
                                            <td style="width: 98%; background: #ddd; color: #1a1150; font-size: 11px; line-height: 18px; padding: 0 5px;"><b>GST
                                                    <?php
                                                    if ($order_list[0]['order_tax_charge'] > 0) {
                                                    ?>
                                                        (<?php echo floatval($order_list[0]['order_tax_charge']);  ?>%)
                                                    <?php
                                                    }
                                                    ?>
                                                </b>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td style="width: 50%; padding: 0 5px; text-align: right;">
                                    <?php echo show_price_client($order_list[0]['order_tax_calculate_amount'], $currecnySymbol)  ?>
                                </td>
                            </tr>

                        <?php }
                        ?>
                        <tr>
                            <td style="width: 50%;" bgcolor="#dddddd">
                                <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td style="width: 2%"></td>
                                        <td style="width: 98%; background: #ddd; color: #1a1150; font-size: 11px; line-height: 18px; padding: 0 5px;"><b>Total Amount</b></td>
                                    </tr>
                                </table>
                            </td>
                            <td style="width: 50%; padding: 0 5px; text-align: right;">
                                <?php echo show_price_client($order_list[0]['order_total_amount'], $currecnySymbol); ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td style="height: 30px;"></td>
            </tr>
        </table>

        <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td>
                    <table style="width: 100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td style="height: 1px;"></td>
                        </tr>
                        <tr>
                            <td style="color: #1a1150; line-height: 18px; font-size: 11px;"><b><?php echo stripslashes(ucwords($order_list[0]['company_name'])); ?></b></td>
                        </tr>
                        <tr>
                            <td style="color: #1a1150; line-height: 18px; font-size: 11px;"><?php echo $order_list[0]['company_address']; ?>,
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #1a1150; line-height: 18px; font-size: 11px;">
                                <?php
                                if ($order_list[0]['company_email_address'] != '' && $order_list[0]['company_email_address'] != 'null') {
                                    echo 'Email: ' . $order_list[0]['company_email_address'];
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #1a1150; line-height: 18px; font-size: 11px;">Website: <?php echo $company['company_site_url']; ?>

                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php
                                if (!empty($company['company_invoice_logo'])) {
                                ?>
                                    <img src="<?= $company['company_invoice_logo'] ?>" alt="Cedele" style="width: 150px;" />
                                <?php
                                }
                                ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

    </div>

</body>

</html>