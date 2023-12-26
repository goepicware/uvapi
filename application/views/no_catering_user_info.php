<!-- customer address -->
<div class="event-com-bottom select-outlet controls">
	
	<label>Contact Details</label>	
	<?php
	  if(!empty($corporate_user_contact_list))
	  {
	?>
	<ul>
	  <?php foreach($corporate_user_contact_list as $user_contact_list){ ?>
		<li class="select_banana_halls <?php echo ($this->session->userdata('catering_hall_id') == $user_contact_list->corp_cust_id) ? "active" : "";?>" data-id="<?php echo $user_contact_list->corp_cust_id; ?>">
		  <div class="outlet-info hall_loadcls">
			   <a class="title select_banana_hall_id" href="javascript:void(0);"><?php echo $user_contact_list->corp_dept_name; ?></a>
			   <p class="corp_contact_person" data-value="<?php echo $user_contact_list->corp_contact_person; ?>"><?php echo $user_contact_list->corp_contact_person; ?></p>
			   <p class="corp_designation" data-value="<?php echo $user_contact_list->corp_designation; ?>"><?php echo $user_contact_list->corp_designation; ?></p>
			   <p class="corp_no" data-value="<?php echo $user_contact_list->corp_no; ?>"><?php echo $user_contact_list->corp_no; ?></p>
			   <p class="corp_email" data-value="<?php echo $user_contact_list->corp_email; ?>"><?php echo $user_contact_list->corp_email; ?></p>
			   <a href="javascript:void(0);" class="button get_corporate_user_info"> Get details </a>
		  </div>
		</li>
	  <?php } ?>
	  </ul>
	 <?php } ?>
</div>
<span class="success_div_cls" id="contact_details" style="color:green"></span>

