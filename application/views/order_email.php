 <?php
  defined('BASEPATH') or exit('No direct script access allowed');
  $currecnySymbol = $company['company_currency_symbol'];
  ?>
 <table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#ffffff" style="padding: 15px; margin: 0 0 20px;">
   <tr>
     <td width="170" style=" padding: 5px 10px;">
       <?= get_label('order_number'); ?>
     </td>
     <td width="10">-</td>
     <td style=" padding: 5px 0;">
       <?= (isset($order_list[0]['order_local_no']) && !empty($order_list[0]['order_local_no'])) ? $order_list[0]['order_local_no'] : "N/A"; ?>
     </td>
   </tr>
   <tr>
     <td style=" padding: 5px 10px;">Created by</td>
     <td width="10">-</td>
     <td style=" padding: 5px 0px;">
       <?= (isset($order_list[0]['order_source']) && ($order_list[0]['order_source'] == 'CallCenter')) ? stripslashes(ucwords($order_list[0]['order_agent'])) : stripslashes(ucwords($order_list[0]['customer_name'])) ?>
     </td>
   </tr>
   <?php
    if ($order_list[0]['order_availability_id'] == DELIVERY_ID || $order_list[0]['order_availability_id'] == MAD_BAR_ID || $order_list[0]['order_availability_id'] == BENTO_ID) {
    ?>
     <tr>
       <td style=" padding: 5px 10px;">
         <?= get_label('delivery_date'); ?>
       </td>
       <td>-</td>
       <td style=" padding: 5px 0;">
         <?= get_date_formart($order_list[0]['order_date'], 'l') ?>,
         <?= get_date_formart($order_list[0]['order_date'], 'F d, Y g:i a') ?>
       </td>
     </tr>
     <?php
      if (isset($order_list[0]['outlet_name']) && isset($order_list[0]['outlet_address_line1']) && isset($order_list[0]['outlet_postal_code'])) {
      ?>
       <tr>
         <td style=" padding: 5px 10px;">Order Handling By</td>
         <td>-</td>
         <td style=" padding: 5px 0;">
           <?php $unit = ($order_list[0]['outlet_unit_number2'] !== "" && $order_list[0]['outlet_unit_number1'] !== "" ? " #" . $order_list[0]['outlet_unit_number1'] . "-" . $order_list[0]['outlet_unit_number2'] . ", " : ($order_list[0]['outlet_unit_number1'] !== '' ? " #" . $order_list[0]['outlet_unit_number1'] . ", " : ""));
            echo (($order_list[0]['outlet_name'] != '') ? ucfirst(stripslashes($order_list[0]['outlet_name'])) . ", " : "") . (($order_list[0]['outlet_address_line1'] != '') ? ucfirst(stripslashes($order_list[0]['outlet_address_line1'])) . ", " : "") . (($order_list[0]['outlet_address_line2'] != '') ? ucfirst(stripslashes($order_list[0]['outlet_address_line2'])) . ", " : "") . $unit . DEFAULT_COUNTRY . " " . $order_list[0]['outlet_postal_code'];
            ?>
         </td>
       </tr>
     <?php
      }
    } else if ($order_list[0]['order_availability_id'] == PICKUP_ID) {
      ?>
     <tr>
       <td style=" padding: 5px 10px;">
         <?= get_label('pickup_date'); ?>
       </td>
       <td>-</td>
       <td style=" padding: 5px 0;">
         <?= get_date_formart($order_list[0]['order_date'], 'l'); ?>,
         <?= get_date_formart($order_list[0]['order_date'], 'F d, Y g:i a'); ?>
       </td>
     </tr>
     <?php
      if (isset($order_list[0]['outlet_name']) && isset($order_list[0]['outlet_address_line1']) && isset($order_list[0]['outlet_postal_code'])) {
      ?>
       <tr>
         <td style=" padding: 5px 10px;">
           <?= get_label('pickup_location'); ?>
         </td>
         <td>-</td>
         <td style=" padding: 5px 0;">
           <?php $unit = ($order_list[0]['outlet_unit_number2'] !== "" && $order_list[0]['outlet_unit_number1'] !== "" ? " #" . $order_list[0]['outlet_unit_number1'] . "-" . $order_list[0]['outlet_unit_number2'] . ", " : ($order_list[0]['outlet_unit_number1'] !== '' ? " #" . $order_list[0]['outlet_unit_number1'] . ", " : ""));
            echo (($order_list[0]['outlet_name'] != '') ? ucfirst(stripslashes($order_list[0]['outlet_name'])) . ", " : "") . (($order_list[0]['outlet_address_line1'] != '') ? ucfirst(stripslashes($order_list[0]['outlet_address_line1'])) . ", " : "") . (($order_list[0]['outlet_address_line2'] != '') ? ucfirst(stripslashes($order_list[0]['outlet_address_line2'])) . ", " : "") . $unit . DEFAULT_COUNTRY . " " . $order_list[0]['outlet_postal_code'];
            ?>
         </td>
       </tr>
     <?php
      }
    } else {
      ?>
     <tr>
       <td style=" padding: 5px 10px;">Dine in Date & Time</td>
       <td>-</td>
       <td style=" padding: 5px 0;">
         <?= get_date_formart($order_list[0]['order_date'], 'l') ?>,
         <?= get_date_formart($order_list[0]['order_date'], 'F d, Y g:i a') ?>
       </td>
     </tr>
   <?php
    }
    ?>
   <tr>
     <td style=" padding: 5px 10px;"><?= get_label('order_availability'); ?></td>
     <td>-</td>
     <td style=" padding: 5px 0;">
       <?= (isset($order_list[0]['order_availability_name']) && ($order_list[0]['order_availability_name'] != '')) ? $order_list[0]['order_availability_name'] : "N/A" ?>
     </td>
   </tr>
   <?php
    if ($order_list[0]['order_availability_id'] == DINEIN_ID) {
    ?>
     <tr>
       <td style=" padding: 5px 10px;"><?= "Table No"; ?></td>
       <td>-</td>
       <td style=" padding: 5px 0;">
         <?= (isset($order_list[0]['order_table_number']) && $order_list[0]['order_table_number'] != '') ? $order_list[0]['order_table_number'] : 'N/A' ?>
       </td>
     </tr>
   <?php
    }
    ?>
   <tr>
     <td style=" padding: 5px 10px;"><?= get_label('order_special_note'); ?></td>
     <td>-</td>
     <td style=" padding: 5px 0;">
       <?= (isset($order_list[0]['order_remarks']) && $order_list[0]['order_remarks'] != '') ? stripcslashes($order_list[0]['order_remarks']) : 'N/A'; ?>
     </td>
   </tr>
   <tr>
     <td style=" padding: 5px 10px;"><?= get_label('order_status'); ?></td>
     <td>-</td>
     <td style=" padding: 5px 0;">
       <?= (isset($order_list[0]['status_name']) && $order_list[0]['status_name'] != '') ? $order_list[0]['status_name'] : 'N/A' ?>
     </td>
   </tr>
 </table>

 <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 0 20px;">
   <tr>
     <td bgcolor="#858383" style="padding: 11px 25px; font: normal 16px arial; color:#ffffff;">
       <?= get_label('customer_details'); ?>
     </td>
   </tr>
   <tr>
     <td bgcolor="#ffffff" style="padding: 15px;">
       <table border="0" cellpadding="0" cellspacing="0" width="100%">
         <tr>
           <td width="170" style=" padding: 5px 10px;"><?= get_label('order_customer_name'); ?></td>
           <td width="10">-</td>
           <td style=" padding: 5px 0;">
             <?= (isset($order_list[0]['customer_name']) && !empty($order_list[0]['customer_name'])) ? ucfirst(stripslashes($order_list[0]['customer_name'])) : 'N/A'; ?>
           </td>
         </tr>
         <tr>
           <td style=" padding: 5px 10px;">
             <?= get_label('order_customer_mobile_no'); ?>
           </td>
           <td>-</td>
           <td style=" padding: 5px 0;">
             <?= (isset($order_list[0]['order_customer_mobile_no']) && $order_list[0]['order_customer_mobile_no'] != '') ? $order_list[0]['order_customer_mobile_no'] : 'N/A' ?>
           </td>
         </tr>
         <tr>
           <td style=" padding: 5px 10px;"><?= get_label('order_customer_email'); ?></td>
           <td>-</td>
           <td style=" padding: 5px 0;">
             <?= (isset($order_list[0]['order_customer_email']) && $order_list[0]['order_customer_email'] != '') ? $order_list[0]['order_customer_email'] : 'N/A' ?>
           </td>
         </tr>
         <?php
          if ($order_list[0]['order_availability_id'] == DELIVERY_ID || $order_list[0]['order_availability_id'] == MAD_BAR_ID || $order_list[0]['order_availability_id'] == BENTO_ID) {
          ?>
           <tr>
             <td style=" padding: 5px 10px;"><?= get_label('order_customer_postal_code'); ?></td>
             <td>-</td>
             <td style=" padding: 5px 0;">
               <?= (isset($order_list[0]['order_customer_postal_code']) && $order_list[0]['order_customer_postal_code'] != '') ? $order_list[0]['order_customer_postal_code'] : 'N/A' ?>
             </td>
           </tr>
           <tr>
             <td style=" padding: 5px 10px;"><?= get_label('order_customer_unit_no1'); ?></td>
             <td>-</td>
             <td style=" padding: 5px 0;">
               <?= (isset($order_list[0]['order_customer_unit_no1']) && $order_list[0]['order_customer_unit_no1'] != '') ? stripslashes($order_list[0]['order_customer_unit_no1']) : 'N/A' ?>
             </td>
           </tr>
           <tr>
             <td style=" padding: 5px 10px;"><?= get_label('order_customer_unit_no2'); ?></td>
             <td>-</td>
             <td style=" padding: 5px 0;">
               <?= (isset($order_list[0]['order_customer_unit_no2']) && $order_list[0]['order_customer_unit_no2'] != '') ? stripslashes($order_list[0]['order_customer_unit_no2']) : 'N/A' ?>
             </td>
           </tr>

           <tr>
             <td style=" padding: 5px 10px;"><?= get_label('order_customer_address_line1'); ?></td>
             <td>-</td>
             <td style=" padding: 5px 0;">
               <?= (isset($order_list[0]['order_customer_address_line1']) && $order_list[0]['order_customer_address_line1'] != '') ? ucfirst(stripslashes($order_list[0]['order_customer_address_line1'])) : 'N/A' ?>
             </td>
           </tr>
           <tr>
             <td style=" padding: 5px 10px;"><?= get_label('order_customer_address_line2'); ?></td>
             <td>-</td>
             <td style=" padding: 5px 0;">
               <?= (isset($order_list[0]['order_customer_address_line2']) && $order_list[0]['order_customer_address_line2'] != '') ? ucfirst(stripslashes($order_list[0]['order_customer_address_line2'])) : 'N/A' ?>
             </td>
           </tr>

           <?php
            if (!empty($order_list[0]['order_customer_send_gift']) && $order_list[0]['order_customer_send_gift'] == 'Yes') {
            ?>
             <tr>
               <td style=" padding: 5px 10px;">Recipient Name</td>
               <td>-</td>
               <td style=" padding: 5px 0;">
                 <?= (isset($order_list[0]['order_recipient_name']) && $order_list[0]['order_recipient_name'] != '') ? $order_list[0]['order_recipient_name'] : 'N/A' ?>
               </td>
             </tr>
             <tr>
               <td style=" padding: 5px 10px;">Recipient contact No.</td>
               <td>-</td>
               <td style=" padding: 5px 0;">
                 <?= (isset($order_list[0]['order_recipient_contact_no']) && $order_list[0]['order_recipient_contact_no'] != '') ? $order_list[0]['order_recipient_contact_no'] : 'N/A' ?>
               </td>
             </tr>
             <tr>
               <td style=" padding: 5px 10px;">Gift message</td>
               <td>-</td>
               <td style=" padding: 5px 0;">
                 <?= (isset($order_list[0]['order_gift_message']) && $order_list[0]['order_gift_message'] != '') ? $order_list[0]['order_gift_message'] : 'N/A' ?>
               </td>
             </tr>
           <?php } ?>

         <?php } ?>

       </table>
     </td>
   </tr>

 </table>

 <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 0 20px;">
   <tr>
     <td bgcolor="#858383" style="padding: 11px 25px; font: normal 16px arial; color:#ffffff;"> <?= get_label('order_items'); ?> </td>
   </tr>

   <tr>
     <td bgcolor="#ffffff" style="padding: 15px;">
       <table border="0" cellpadding="0" cellspacing="0" width="100%">

         <tr class="ltable_outer">
           <td bgcolor="#f5f5f5" style="padding: 10px;">

             <table border="0" cellpadding="0" cellspacing="0" width="100%">
               <thead bgcolor="#5b5a5a" style="background: #5b5a5a; color: #ffffff;">
                 <tr>
                   <th width="35%" style="font-weight: normal; padding: 10px;" align="center">Name</th>
                   <th width="25%" style="font-weight: normal; padding: 10px;" align="center">Combo Set</th>
                   <th style="font-weight: normal; padding: 10px;" align="center">Unit Price</th>
                   <th style="font-weight: normal; padding: 10px;" align="center">Qty</th>
                   <th style="font-weight: normal; padding: 10px;" align="center">Amount</th>
                 </tr>
               </thead>

               <?php
                foreach ($oder_item as $key => $store) {
                ?>
                 <tr>
                   <td style=" padding: 10px; font: normal 16px arial; font-weight: bold; background: #b6b5b5; color: #FFF;" colspan="5">
                     <?php echo $store['storeName']; ?>
                   </td>
                 </tr>
                 <?php
                  foreach ($store['items'] as $item) {
                  ?>

                   <tr>
                     <td style=" padding: 10px; font: normal 16px arial;">
                       <?= ucwords(stripslashes($item['itemName'])); ?>
                       <?= ($item['itemNote'] != "") ? "(" . $item['itemNote'] . ")" : ""; ?>
                     </td>
                     <td style=" padding: 10px;  font: normal 16px arial;">

                       <?php
                        if (!empty($item['comboset'])) {
                          foreach ($item['comboset'] as $menu_component) {
                            $j = 0;
                            $menu_set = "<div>" . $menu_component['comboSetname'] . "</div>";
                            foreach ($menu_component['productDetails'] as $product_detail) {
                              if ($j != 0) {
                                $menu_set .= ",<br>";
                              } else {
                                $menu_set .= "<br>";
                              }
                              $pro_price = ($product_detail['productPrice'] > 0 ? " (+" . show_price_client($product_detail['productPrice'], $currecnySymbol) . ")" : '');
                              $pro_qty = ($product_detail['quantity'] == 0 ? 1 : $product_detail['quantity']) . $pro_price;
                              $menu_set .= $pro_qty . " X " . $product_detail['productName'];
                              $j++;
                            }
                            echo $menu_set;
                          }
                        }
                        ?>
                     </td>
                     <td style=" padding: 10px; font: normal 16px arial; text-align:right"><?php echo show_price_client($item['itemPrice'], $currecnySymbol); ?></td>
                     <td style=" padding: 10px; font: normal 16px arial;  text-align:center"><?php echo $item['itemQuantity']; ?></td>
                     <td style=" padding: 10px; font: normal 16px arial;  text-align:right"><?php echo show_price_client($item['itemTotalPrice'], $currecnySymbol); ?></td>
                   </tr>
               <?php
                  }
                }
                ?>
               <tfoot>
                 <tr>
                   <td colspan="4" style=" padding: 10px;font: normal 16px arial; text-align:right"><?= get_label('order_subtotal'); ?> </td>
                   <td style=" padding: 10px;font: normal 16px arial; text-align:right">
                     <?php echo show_price_client($order_list[0]['order_sub_total'], $currecnySymbol); ?>
                   </td>
                 </tr>
                 <?php
                  if (!empty($promoHistory)) {
                    foreach ($promoHistory as $promo) {
                  ?>
                     <tr>
                       <td colspan="4" style=" padding: 10px;font: normal 16px arial; text-align:right">Discount (<?= $promo['promotion_history_promocode']; ?>) </td>
                       <td style=" padding: 10px;font: normal 16px arial; text-align:right">
                         <?php
                          if ($promo['promotion_history_delivery_charge'] == "Yes") {
                            echo 'Free Delivery';
                          } else {
                            echo '- ' . show_price_client($promo['promotion_history_applied_amt'], $currecnySymbol);
                          }
                          ?>
                       </td>
                     </tr>
                   <?php
                    }
                  }

                  if ((isset($order_list[0]['order_delivery_charge']) && $order_list[0]['order_delivery_charge'] != '' &&  $order_list[0]['order_delivery_charge'] != 0)) {

                    $delivery_charge_total = $order_list[0]['order_delivery_charge'];
                    ?>
                   <tr>
                     <td colspan="4" align="right" style=" padding: 10px; font: normal 16px arial;"><?php echo get_label('order_delivery_charge'); ?></td>
                     <td style=" padding: 10px;font: normal 16px arial;" align="right"> <?= show_price_client($delivery_charge_total, $currecnySymbol); ?></td>
                   </tr>
                 <?php
                  }


                  if (isset($order_list[0]['order_tax_calculate_amount']) && $order_list[0]['order_tax_calculate_amount'] != '' &&  $order_list[0]['order_tax_calculate_amount'] != 0) {
                  ?>
                   <tr>
                     <td colspan="4" align="right" style=" padding: 10px; font: normal 16px arial; ">Gst
                       <?php
                        if ($order_list[0]['order_tax_charge'] > 0) {
                        ?>
                         (<?php echo floatval($order_list[0]['order_tax_charge']);  ?>%)
                       <?php
                        }
                        ?>
                     </td>

                     <td style=" padding: 10px; font: normal 16px arial;" align="right">
                       <?php echo (isset($order_list[0]['order_tax_calculate_amount']) && $order_list[0]['order_tax_calculate_amount'] != '') ? show_price_client($order_list[0]['order_tax_calculate_amount'], $currecnySymbol) : 'N/A' ?></td>
                   </tr>

                 <?php
                  }
                  ?>
                 <tr>
                   <td colspan="4" align="right" style=" padding: 10px; font: normal 16px arial;"><?php echo get_label('order_total_amount'); ?> </td>
                   <td style=" padding: 10px; font: normal 16px arial;" align="right"><?= show_price_client($order_list[0]['order_total_amount'], $currecnySymbol); ?></td>
                 </tr>
               </tfoot>
             </table>
           </td>
         </tr>
       </table>
     </td>
   </tr>
 </table>

 <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 0 20px;">
   <tr>
     <td bgcolor="#858383" style="padding: 11px 25px; font: normal 16px arial; color:#ffffff;"><?php echo get_label('payment'); ?></td>
   </tr>
   <tr>
     <td bgcolor="#ffffff" style="padding: 15px;">
       <table border="0" cellpadding="0" cellspacing="0" width="100%">
         <tr>

           <td width="170" style=" padding: 5px 0;"><?php echo get_label('order_payment_mode'); ?></td>
           <td width="10">-</td>
           <td style=" padding: 5px 0;"><?php echo $order_list[0]['order_method_name'] . ((isset($order_list[0]['order_payment_getway_type']) && $order_list[0]['order_payment_getway_type'] != '') ? (' - ' . ucfirst($order_list[0]['order_payment_getway_type'])) : ''); ?></td>

         </tr>
       </table>
     </td>
   </tr>
 </table>