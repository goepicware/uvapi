                    <table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#ffffff" style="padding: 15px; margin: 0 0 20px;">
						
						 
					    <tr><td width="170" style=" padding: 5px 10px;">Total Order</td><td width="10">-</td>
                        <td style=" padding: 5px 0;"><?php echo ( $order_count!="") ? $order_count:"0"; ?></td></tr>
                        <?php 
                        if(!empty($order_list))
                        {
							
							foreach($order_list as $key=>$value)
							{
																
                        ?>
                        <tr><td style=" padding: 5px 10px;"><?php echo $key;?></td><td width="10">-</td>
                        <td style=" padding: 5px 0px;">
                        <?php echo $value;?>
                        </td></tr>
                        <?php 
				            }
					     }
                        ?>
                        <tr><td width="170" style=" padding: 5px 10px;">Cancel Order</td><td width="10">-</td>
                        <td style=" padding: 5px 0;"><?php echo ( $order_cancel_count!="") ? $order_cancel_count:"0"; ?></td></tr>
        
                    </table>
                                                   
                    
                        
                   
                   
           

