<?php 
/**************************
Project Name	: Pos
Created on		: 19  Feb, 2016
Last Modified 	: 19  Feb, 2016 
Description		: 23 contains common alert messages for adminpanel and client panel
***************************/

############# Login error messages  for  common  alert labels ############
$lang['flash_loginfailed']	= "Invalid login details";
$lang['flash_invaliddetails'] = "Invalid Details";
$lang['invalid_process'] = "Invalid Process";
$lang['login_error'] = "Please verify your login credentials and try again later";
$lang['forgot_error'] = "This email address is not found in our database";
$lang['forgot_success'] = "Please check your email to reset your password";
$lang['no_records_found'] = "No records found";
$lang['admin_no_records_found'] = "No %s found. ";
$lang['alert_multibleaction']="Please select at least one record before proceeding";
$lang['confirm_delete_selected'] = "Are you sure you want to delete the selected %s?";
$lang['confirm_send_manual_notification'] = "Are you sure you want to send the notification this %s?";
$lang['confirm_delete'] = "Are you sure you want to delete this %s?";
$lang['confirm_delete_image'] = "Are you sure you want to delete this image?";
$lang['confirm_activate'] = "Are you sure you want to activate this %s?";
$lang['confirm_deactivate'] = "Are you sure you want to deactivate this %s?";
$lang['confirm_approve'] = "Are you sure you want to approve this %s?";
$lang['confirm_reject'] = "Are you sure you want to reject this %s?";
$lang['confirm_pending'] = "Are you sure you want to pending this %s?";
$lang['confirm_sequence'] = "Are you sure you want to update Sort Order this %s?";
$lang['success_message_delete'] = "The Selected %s has been deleted successfully.";
$lang['success_message_edit'] = "%s has been updated successfully.";
$lang['success_message_order_status'] = "%s status has been updated successfully.";
$lang['success_message_activate'] = " The Selected %s has been activated successfully.";
$lang['success_message_deactivate'] = "The Selected %s has been deactivated successfully.";
$lang['success_message_approved'] = " The Selected %s has been approved successfully.";
$lang['success_message_rejected'] = "The Selected %s has been rejected successfully.";
$lang['success_message_pending'] = "The Selected %s has been pending successfully.";
$lang['success_message_ungroup'] = "The Selected %s has been ungrouped successfully.";
$lang['success_message_sequence'] = "The Selected %s Sort Order has been updated successfully.";
$lang['success_message_add'] = "%s has been added successfully.";
$lang['success_message_update'] = "%s updated successfully.";
$lang['success_message_addwhatsnew'] = "Successfully add as Whats new product.";
$lang['success_message_removewhatsnew'] = "Successfully removed from Whats new product.";
$lang['success_message_addhighlight']  = "Product successfully add from Highlight product.";
$lang['success_message_removehighlight'] = "Product successfully removed from Highlight product.";
$lang['success_message_addpromotion'] = "Successfully add as Promotion product.";
$lang['success_message_removepromotion'] = "Successfully removed from Promotion product.";
$lang['success_message_addwhatsnew1'] = "Successfully add as Whats new Category.";
$lang['success_message_removewhatsnew1'] = "Successfully removed from Whats new Category.";
$lang['success_message_addhighlight1'] = "Successfully add as Highlight Category.";
$lang['success_message_removehighlight1'] = "Successfully removed from Highlight Category.";
$lang['success_message_addpromotion1'] = "Successfully add as Promotion Category.";
$lang['success_message_removepromotion1'] = "Successfully removed from Promotion Category.";
$lang['success_message_addchief'] = "Product successfully add as Chef Recommended.";
$lang['success_message_removechief'] = "Product successfully removed from Chef Recommended.";
/*use in busness pannel login and switch in client pannnel*/
$lang['no_outlet_found'] = "Outlet";
$lang['invalid_app_id'] = "Invalid App Id";




$lang['ip_error'] = "Oops, Your IP expiry date has been exceeded. Please contact administration for the approval.";
$lang['success_message_email_send'] = "%s Email has been sent successfully";
$lang['failure_message_email_send'] = "%s Email has not been sent successfully";
$lang['success_message_send_notification'] = "%s Notification has been send successfully";
$lang['upload_valid_image'] = "Upload Error file extension is not allowed. You can only upload JPG, JPEG, PNG, GIF files.";
$lang['something_wrong'] = "Something went wrong";
$lang['access_denied'] = "Access Denied";
$lang['error_delete_warning'] = "Access denied, Selected records have a sub items, Please remove sub items and try agian later "; 


/* common login alerts */
$lang['acount_created'] = "Thanks for signing up. An email has been sent to you for verification";
$lang['acount_activated'] = "Congrats! Your account is activated.";
$lang['acount_link_expired'] = "The verification link has been expired";
$lang['acount_login_missmatch'] = "Sorry, there is no match for that username and/or password.";
$lang['acount_not_found'] = "The Username was not found in our records, please try again.";
$lang['changed_password'] = "Your password has been changed successfully!";
$lang['invalid_old_password'] = "Current password is wrong";
//$lang['acount_not_found'] = "Oops! You must have forgotten to register first.";
$lang['account_disabled'] = "Your account has been disabled contact your administrator for more information";
$lang['account_initially_disabled'] = "You already registered with your email account, please check your email and verify your account";
$lang['send_mail_error'] = "Unable to send mail. Please try again later.";
$lang['reset_password_link'] = "Reset link has been sent to your registered email address, please use this to reset your password";
$lang['reset_link_sent'] = "Reset password link already sent to your email address";
$lang['password_changed'] = "Your password has been reset successfully!";
$lang['confirm_delete_selected'] = "Do you want to delete this %s!";


/* voucher alerts */
$lang['voucher_invalid']="Voucher code is invalid";
$lang['voucher_date_expire']="Voucher code is expired";
$lang['voucher_success']="Voucher code is applied successfully";
$lang['voucher_amount_expire']="Voucher amount is used";
$lang['voucher_disabled']="Voucher is not applicable";
$lang['rest_voucher_amount_changed']="Voucher amount is changed";
$lang['cart_amount']="Cart amount";


/* loyality alerts */
$lang['redeem_applied']="Redeem point is applied successfully";
$lang['loyality_disabled']="Redeem point is not applicable";
$lang['invalid_rest_customer']="Invalid Customer ID";
$lang['invalid_redeem_point']="Invalid Redeem Point";
$lang['rest_redeem_amount_changed']="Redeem Point is changed";

$lang['no_payment']="No Payment Information Found !";
$lang['payment_information']="Please update your payment information in your billing area";
$lang['payment_report']='Payment Report';
$lang['client_start_date']='Start Date';
$lang['field_required'] = "%s field is required";
$lang['token_verify_faild'] = "Authentication token verification failed";
$lang['token_faild'] = "Authentication failed";


?>
