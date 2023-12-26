
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
            <?php  if ($compImg != '') { ?>
                <img src="<?php echo $compImg; ?>" alt="logo" />
            <?php } ?>
        </td>
        <td width="50%">
            <table width="100%" style="font-family: arial, sans-serif; font-size:12px;">
				<tr>
                    <td style="font-weight:bold;color:#333;font-family: arial, sans-serif;  font-size:14px;text-align: right; text-transform: uppercase;">QUOTATION INVOICE</td>
                </tr>
                <tr>
                    <td style="text-transform: uppercase;font-weight:normal;color:#787878;font-family: arial, sans-serif;  font-size:12px;text-align: right;">DATE: 
                        <span style="font-weight:bold;color:#000;">
                            <?php echo date('d-m-y'); ?>
                        </span>
                    </td>
                </tr>
                
                <tr>
                    <td style="text-transform: uppercase;font-weight:normal;color:#787878;font-family: arial, sans-serif;  font-size:12px;text-align: right;">TIME: 
                        <span style="font-weight:bold;color:#000;">
                            <?php echo date('h:i:s'); ?>
                        </span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr height="10">
        <td colspan="2"></td>
    </tr>
   
    <tr height="10">
        <td colspan="2"></td>
    </tr>
    <tr>
        <td>
            <table width="100%" style="font-family: arial, sans-serif; font-size:12px;">
                <tr>
                    <td style="font-weight:bold; color:#000;font-family: arial, sans-serif; font-size:12px;">
						<strong>Address:</strong><br>
						<strong><?php echo $order_company_name; ?></strong><br/>
                        <strong>Attn: <?php echo $corporate_order_first_name.''.$corporate_order_last_name ; ?> 
                        </strong><br/>
						<strong><?php echo $products_estimated_catering_address; ?></strong>
                    </td>
                </tr>
                <tr>
                    <td style="font-family: arial, sans-serif; font-size:12px;color:#787878;">
						Tel: <?php echo $corporate_order_phone_number; ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-family: arial, sans-serif; font-size:12px;color:#787878;">Email: <?php echo $corporate_order_email_address; ?></td>
                </tr>
            </table>
        </td>
        
    </tr>
    <tr height="10">
        <td colspan="2"></td>
    </tr>
    <tr>
        <td colspan="2">
            
            <table  cellpadding="5" cellspacing="0" width="100%" style="font-family: arial, sans-serif; font-size:12px;border:1px solid #b5b5b5; border-collapse: collapse;">
                <tr bgcolor="#f3f3f3">
                    <th style="font-weight:bold; color:#000; font-family: arial, sans-serif; font-size:12px;text-transform:uppercase; padding:7px;text-align: center;border: 1px solid #b5b5b5;background:#f3f3f3; width:15%;">NO</th>
                    <th style="font-weight:bold; color:#000; font-family: arial, sans-serif; font-size:12px;text-transform:uppercase; padding:7px;text-align: center;border: 1px solid #b5b5b5;background:#f3f3f3; width:40%;">DESCRIPTION</th>
                    <th style="font-weight:bold; color:#000; font-family: arial, sans-serif; font-size:12px;text-transform:uppercase; padding:7px;text-align: center;border: 1px solid #b5b5b5;background:#f3f3f3; width:15%;">NO OF ITEMS</th>
                    <th style="font-weight:bold; color:#000; font-family: arial, sans-serif; font-size:12px;text-transform:uppercase; padding:7px;text-align: center;border: 1px solid #b5b5b5;background:#f3f3f3; width:15%;">UNIT PRICE</th>
                    <th style="font-weight:bold; color:#000; font-family: arial, sans-serif; font-size:12px;text-transform:uppercase; padding:7px;text-align: center;border: 1px solid #b5b5b5;background:#f3f3f3; width:15%;">TOTAL</th>
                </tr>

               <?php $i = 1; ?>
				<?php if(is_array($request_cart_details)) { ?>
                <?php foreach($request_cart_details as $cart_details) { ?>
                  
                    <tr>
                        <td style="color:#787878; font-family: arial, sans-serif; font-size:12px; padding:7px;text-align: center; border: 1px solid #b5b5b5;"><?php echo $i; ?></td>
                        <td style="color:#787878; font-family: arial, sans-serif; font-size:12px; padding:7px; text-align: left; border: 1px solid #b5b5b5;">
                            <table width="100%">
                                <tr>
                                    <td style="color:#000; font-family: arial, sans-serif; font-size:12px; font-weight: bold;">
                                        <?php echo ucwords(stripslashes($cart_details->product_name)); ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td style="color:#787878; font-family: arial, sans-serif; font-size:12px; padding:7px; text-align: center; border: 1px solid #b5b5b5;">
                            <?php echo $cart_details->product_qty; ?>
                        </td>
                        <td style="color:#787878; font-family: arial, sans-serif; font-size:12px; padding:7px; text-align: center; border: 1px solid #b5b5b5;">
                            <?php echo $cart_details->cart_item_unit_price; ?>
                        </td>
                        <td style="color:#787878; font-family: arial, sans-serif; font-size:12px; padding:7px; text-align: center; border: 1px solid #b5b5b5;">
                            <?php echo $cart_details->product_total_amount; ?>
                        </td>
                    </tr>

                    <?php $i++ ?>

                <?php } ?>
               <?php } ?>
               
                <?php 
                /*Gst amount*/
                $cart_sub_total = $request_cart_details[0]->cart_sub_total; 
                $client_gst_charge = $delivery_detail->client_gst_charge;
                $cart_delivery_charge = $request_cart_details[0]->cart_delivery_charge;

                $amounut_with_delivery = $cart_sub_total + $cart_delivery_charge;
                
                if($client_gst_charge != ''){

                        $new_charge = ($client_gst_charge  / 100) * $cart_sub_total;  
                        $gst = number_format($new_charge,2);
                }else{
                        $gst = "";
                }

                $grand_total = $cart_sub_total+$gst+$cart_delivery_charge;
                $grand_total = number_format($grand_total,2);
                ?>	 
                                
                <tr>                    
                    <td colspan="2" rowspan="5" valign="middle">
                        <table width="100%">
                            <tr height="10">
                                <td></td>
                            </tr>
                            <tr>
                                <td style="font-family: arial, sans-serif;	font-size: 12px; font-weight: bold;">PO #: <?php echo $po_number; ?></td>
                            </tr>
                            <tr>
                                <td style="font-family: arial, sans-serif;	font-size: 12px; font-weight: bold;">PO Date: <?php echo date('d-m-Y', strtotime($po_date)); ?></td>
                            </tr>
                            <tr>
                                <td style="font-family: arial, sans-serif;	font-size: 12px; font-weight: bold;">PR #: <?php echo $pr_number; ?></td>
                            </tr>
                        </table>
                    </td>
                    <td colspan="2" style="    color: #000;	font-family: arial, sans-serif;	font-size: 12px;	padding: 7px; text-align: right; font-weight:600;border-right: 1px solid #b5b5b5;"><strong>Subtotal</strong></td>
                    <td colspan="1" style="    color: #000;	font-family: arial, sans-serif;	font-size: 12px;	padding: 7px; text-align: right; font-weight:600; "><strong><?php echo $request_cart_details[0]->cart_sub_total; ?></strong></td>
                </tr>


                    <tr>
                        <td colspan="2" style="    color: #000;	font-family: arial, sans-serif;	font-size: 12px;	padding: 7px; text-align: right; font-weight:600;border-right: 1px solid #b5b5b5;"><strong>GST</strong></td>
                        <td colspan="1" style="    color: #000;	font-family: arial, sans-serif;	font-size: 12px;	padding: 7px; text-align: right; font-weight:600;"><strong><?php echo $gst;?></strong></td>

                    </tr>
               
                <tr>
                    <td valign="top" colspan="2" style="    color: #000;	font-family: arial, sans-serif;	font-size: 12px; text-align: right; font-weight:600;border-right: 1px solid #b5b5b5;"><strong>Grand Total</strong></td>
                    <td valign="top" colspan="1" style="  color: #000;	font-family: arial, sans-serif;	font-size: 12px; text-align: right; font-weight:600;">
                        <strong><?php echo $grand_total; ?></strong></td>

                </tr>

            </table>
        </td>
    </tr>
    <tr height="15">
        <td colspan="2"></td>
    </tr>
    <tr>
        <td colspan="2" style="color: #333; font-family: arial, sans-serif; font-size:12px; font-weight: bold;">Terms & Conditions:</td>
    </tr>
    <tr>
        <td colspan="2" style="color:#787878; font-family: arial, sans-serif; font-size:12px;">Deposit paid is not refundable</td>
    </tr>
    <tr>
        <td colspan="2" style="color:#787878; font-family: arial, sans-serif; font-size:12px;">Cheque should be made payable to <strong style="color: #333; font-size:12px;">GAYATRI RESTAURANT</strong></td>
    </tr>
    <tr>
        <td colspan="2" style="color:#787878; font-family: arial, sans-serif; font-size:12px;">Food best consumed within 3 hours from function time</td>
    </tr>
    <tr height="80" >
        <td colspan="2" style="text-indent: -9999px">.&nbsp;</td>
    </tr>
    <tr height="80" >
        <td colspan="2" style="text-indent: -9999px">.&nbsp;</td>
    </tr>
    <tr height="80" >
        <td colspan="2" style="text-indent: -9999px">.&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2">
            <table width="100%" height="200px">
                <tr><td></td></tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2">	
            <table width="100%" style="font-family: arial, sans-serif; font-size:12px; border-collapse: collapse;">
                <tr>
                    <td width="5%"></td>
                    <td width="50%" style="color:#000; font-family: arial, sans-serif; font-size:12px; text-align: left;text-decoration: overline; text-decoration-color: #333;">Authorise Signature</td>
                    <td width="45%" align="right" style="color:#000; font-family: arial, sans-serif; font-size:12px; text-align: right;text-decoration: overline; text-decoration-color: #333;">Customer Signature</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
