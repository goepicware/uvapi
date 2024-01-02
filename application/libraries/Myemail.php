<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**************************
 Project Name	:  Pos
Created on		: Feb 29, 2016
Last Modified 	: Feb 29, 2016
Description		: Common email library
 ***************************/
class Myemail
{
	protected $ci;

	public function __construct()
	{
		$this->ci = &get_instance();
	}

	/* this function used to send e-email in masteradmin panel */

	function send_admin_mail($to_email_address, $template_id, $chk_arr, $rep_arr)
	{

		$this->ci =  &get_instance();
		$template_table = "pos_admin_email_templates";
		$setting_table = "pos_master_admin_settings";

		$query = " SELECT e.email_subject,e.email_content, s.settings_from_email,s.settings_admin_email,s.settings_site_title, s.settings_mail_from_smtp,
 	s.settings_smtp_host,s.settings_smtp_user,s.settings_smtp_pass,s.settings_smtp_port,s.settings_mailpath,s.settings_email_footer FROM  $template_table as e
 	LEFT JOIN $setting_table as s ON  s.settings_id =1  WHERE  e.email_id = '" . $template_id . "'  ";
		$result = $this->ci->Mydb->custom_query_single($query);

		if (!empty($result)) {
			/* get basic mail config values */
			$to_email = ($to_email_address == '') ? $result['settings_admin_email']  : $to_email_address;
			$from_email = $result['settings_from_email'];
			$from_email_sendgrid = 'no-reply@ninjaos.com';
			$site_title = ucfirst($result['settings_site_title']);
			$subject = $result['email_subject'];
			$email_content = $result['email_content'];
			$result['client_sendgrid_enabled'] = 1;
			/* merge contents */
			$chk_arr1 = array('[LOGOURL]', '[BASEURL]', '[COPY-CONTENT]', '[ADMIN-EMAIL]', '[SITE-TITLE]');
			$rep_array2 = array(load_lib() . "theme/images/email_logo.png", base_url(), $result['settings_email_footer'], $result['settings_admin_email'], $site_title);
			$final_chk_arr = array_merge($chk_arr, $chk_arr1);
			$final_rep_arr = array_merge($rep_arr, $rep_array2);
			$message1 = str_replace($final_chk_arr, $final_rep_arr, $email_content);
			$datas = array('CONTENT' => $message1);
			$this->ci->load->library(array('parser', 'email'));
			$message = $this->ci->parser->parse('email_template_head', $datas, true);

			//echo $message; exit;

			/* mail part */
			if ($result['client_sendgrid_enabled'] == 1) {

				//require '../sendgrid-php/autoload.php'; // If you're using Composer (recommended)
				// Comment out the above line if not using Composer
				require("sendgrid-php/sendgrid-php.php");
				// If not using Composer, uncomment the above line and
				// download sendgrid-php.zip from the latest release here,
				// replacing <PATH TO> with the path to the sendgrid-php.php file,
				// which is included in the download:
				// https://github.com/sendgrid/sendgrid-php/releases

				$email = new \SendGrid\Mail\Mail();
				$email->setFrom(trim($from_email_sendgrid), $site_title);
				$email->setSubject($subject);
				$email->addTos(send_grid_to_addredd_values(trim($to_email), $name));
				$email->addContent("text/html", $message);

				$sendgrid = new \SendGrid('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');
				try {
					$response = $sendgrid->send($email);
					return 1;
				} catch (Exception $e) {
					// echo 'Caught exception: '. $e->getMessage() ."\n";
					return 0;
				}
			} else {

				if ($result['settings_mail_from_smtp'] == 1) {
					$config['smtp_host']	= $result['settings_smtp_host'];
					$config['smtp_user']	= $result['settings_smtp_user'];
					$config['smtp_pass']	= $result['settings_smtp_pass'];
					$config['smtp_port']	= $result['settings_smtp_port'];
					$config['mailpath'] 	= $result['settings_mailpath'];
					$config['protocol'] 	= 'smtp';
					$config['smtp_crypto']  = 'tls';
				} else {
					$config['protocol'] 	= 'sendmail';
				}


				$config['charset'] 		= 'iso-8859-1';
				$config['wordwrap'] 	= TRUE;
				$config['charset'] 		= "utf-8";
				$config['mailtype'] 	= "html";
				$config['newline'] 		= "\r\n";
				$this->ci->email->initialize($config);
				$this->ci->email->from($from_email, $site_title);
				$this->ci->email->to($to_email);
				$this->ci->email->subject($subject);
				$this->ci->email->message($message);
				$email_status = $this->ci->email->send();

				if ($email_status) {
					return 1;
				} else {
					return 0;
				}
			}
		}
	}

	function send_client_mail1($to_email_address, $template_id, $chk_arr, $rep_arr, $client_id, $app_id, $pdf_url = null)
	{
		//echo $to_email_address;
		//echo $template_id;
		//print_r($chk_arr);
		//print_r($rep_arr);
		// echo $client_id;
		//echo $app_id;
		//exit;

		$this->ci =  &get_instance();

		$client_template_table = "pos_email_templates";
		$setting_table = "pos_clients";

		$query = " SELECT e.email_subject,e.email_content,s.client_from_email,s.client_to_email,s.client_site_name,s.client_sendmail_form_smptp,s.client_logo,s.client_folder_name,s.client_site_url,s.client_smpt_host,s.client_smpt_user,s.client_smpt_password,s.client_smpt_port,s.client_mail_path,s.client_email_footer_content FROM  $client_template_table as e
 	LEFT JOIN $setting_table as s ON  s.client_id ='" . $client_id . "' AND s.client_app_id='" . $app_id . "'  WHERE  e.email_id = '" . $template_id . "'";

		$result = $this->ci->Mydb->custom_query_single($query);
		//echo '<pre>';
		// print_r($result);
		//exit;
		if (!empty($result)) {
			/* get basic mail config values */
			$to_email = ($to_email_address == '') ? $result['client_to_email']  : $to_email_address;
			$from_email = $result['client_to_email'];
			$site_title = ucfirst($result['client_site_name']);
			$subject = stripcslashes($result['email_subject']);
			$email_content = stripcslashes($result['email_content']);

			/* merge contents */
			$base_url = ($result['client_site_url'] != '') ? $result['client_site_url']  : base_url();

			$chk_arr1 = array('[BASEURL]', '[COPY-CONTENT]', '[ADMIN-EMAIL]', '[SITE-TITLE]');
			$rep_array2 = array($base_url, $result['client_email_footer_content'], $result['client_to_email'], $site_title);

			$final_chk_arr = array_merge($chk_arr, $chk_arr1);
			$final_rep_arr = array_merge($rep_arr, $rep_array2);

			$message1 = str_replace($final_chk_arr, $final_rep_arr, $email_content);
			$datas = array('CONTENT' => $message1);

			$this->ci->load->library(array('parser', 'email'));

			$message = $this->ci->parser->parse('email_template_head', $datas, true);

			/* mail part */

			if ($result['client_sendmail_form_smptp'] == 1) {
				//echo "1";
				//exit;
				$config['smtp_host']	= $result['client_smpt_host'];
				$config['smtp_user']	= $result['client_smpt_user'];
				$config['smtp_pass']	= $result['client_smpt_password'];
				$config['smtp_port']	= $result['client_smpt_port'];
				$config['mailpath'] 	= $result['client_mail_path'];
				$config['protocol'] 	= 'sendmail';
			} else {
				//echo "2";
				//exit;
				$config['protocol'] 	= 'sendmail';
			}

			$config['charset'] 		= 'iso-8859-1';
			$config['wordwrap'] 	= TRUE;
			$config['charset'] 		= "utf-8";
			$config['mailtype'] 	= "html";
			$config['newline'] 		= "\r\n";
			$this->ci->email->initialize($config);
			$this->ci->email->from($from_email, $site_title);
			$this->ci->email->to($to_email);
			$this->ci->email->subject($subject);
			$this->ci->email->message($message);
			if (!empty($pdf_url)) {
				$this->ci->email->attach($pdf_url);
			}
			$email_status = $this->ci->email->send();



			/*$headers = "From: " . $from_email . "\r\n";
		$headers .= "Reply-To: ". $from_email . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
	    mail($to_email, $subject, $message, $headers)
	    * */


			if ($email_status) {

				echo $this->ci->email->print_debugger();
				echo '<pre>';
				print_r(error_get_last());
				echo '1';
				exit;
				return 1;
			} else {
				echo $this->ci->email->print_debugger();
				echo '<pre>';
				print_r(error_get_last());
				echo '2';
				exit;
				return 0;
			}
		}
	}
	function send_client_mail($compDetails, $to_email_address, $template_id, $chk_arr, $rep_arr, $pdf_url = null, $subject = null)
	{

		$this->ci =  &get_instance();

		$client_template_table = "pos_email_templates";

		$query = " SELECT email_subject,email_content FROM  pos_email_templates WHERE email_company_id ='" . $compDetails['company_id'] . "' AND email_id = '" . $template_id . "'";

		$result = $this->ci->Mydb->custom_query_single($query);

		if (!empty($compDetails) && !empty($result)) {
			/* get basic mail config values */
			$to_email = ($to_email_address == '') ? $compDetails['admin_email']  : $to_email_address;
			$from_email = $compDetails['client_to_email'];
			$from_email_sendgrid = $compDetails['from_email'];
			$site_title = ucfirst($compDetails['company_site_name']);
			if (empty($subject)) {
				$subject = $result['email_subject'];
			}

			$email_content = stripcslashes($result['email_content']);

			/* merge contents */
			$base_url = ($compDetails['company_site_url'] != '') ? $compDetails['company_site_url']  : base_url();

			$chk_arr1 = array('[BASEURL]', '[COPY-CONTENT]', '[ADMIN-EMAIL]', '[SITE-TITLE]');
			$rep_array2 = array($base_url, $compDetails['email_footer_content'], $compDetails['admin_email'], $site_title);

			$final_chk_arr = array_merge($chk_arr, $chk_arr1);
			$final_rep_arr = array_merge($rep_arr, $rep_array2);

			$message1 = str_replace($final_chk_arr, $final_rep_arr, $email_content);
			$datas = array('CONTENT' => $message1);

			$this->ci->load->library(array('parser', 'email'));

			$message = $this->ci->parser->parse('email_template_head', $datas, true);

			/* mail part */
			if ($compDetails['company_sendgrid_enabled'] == 1) {

				//require '../sendgrid-php/autoload.php'; // If you're using Composer (recommended)
				// Comment out the above line if not using Composer
				require("sendgrid-php/sendgrid-php.php");
				// If not using Composer, uncomment the above line and
				// download sendgrid-php.zip from the latest release here,
				// replacing <PATH TO> with the path to the sendgrid-php.php file,
				// which is included in the download:
				// https://github.com/sendgrid/sendgrid-php/releases

				$email = new \SendGrid\Mail\Mail();
				$email->setFrom(trim($from_email_sendgrid), $site_title);
				$email->setSubject($subject);
				$email->addTos(send_grid_to_addredd_values(trim($to_email), $name));
				$email->addContent("text/html", $message);

				if (!empty($pdf_url)) {
					$pdf_url =  str_replace("/var/www/html/", base_url(),  $pdf_url);
					//echo $file; exit;
					$file_encoded = base64_encode(file_get_contents($pdf_url));
					$email->addAttachment(
						$file_encoded,
						"application/pdf",
						'Order Details ',
						"attachment"
					);
				}

				$sendgrid = new \SendGrid('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');
				try {
					$response = $sendgrid->send($email);
					return 1;
				} catch (Exception $e) {
					// echo 'Caught exception: '. $e->getMessage() ."\n";
					return 0;
				}
			} else {
				if (!empty($compDetails['smtp_username'])) {
					//echo "1";
					//exit;
					$config['smtp_host']	= $compDetails['smtp_host'];
					$config['smtp_user']	= $compDetails['smtp_username'];
					$config['smtp_pass']	= $compDetails['smtp_password'];
					$config['smtp_port']	= $compDetails['smtp_port'];
					$config['mailpath'] 	= $compDetails['smtp_mail_path'];
					$config['smtp_crypto']  = 'tls';
					$config['protocol'] 	= 'smtp';
				} else {
					//echo "2";
					//exit;
					$config['protocol'] 	= 'sendmail';
				}

				$config['charset'] 		= 'iso-8859-1';
				$config['wordwrap'] 	= TRUE;
				$config['charset'] 		= "utf-8";
				$config['mailtype'] 	= "html";
				$config['newline'] 		= "\r\n";
				$this->ci->email->initialize($config);
				$this->ci->email->from($from_email, $site_title);
				$this->ci->email->to($to_email);
				$this->ci->email->subject($subject);
				$this->ci->email->message($message);
				if (!empty($pdf_url)) {
					$this->ci->email->attach($pdf_url);
				}
				$email_status = $this->ci->email->send();



				/*$headers = "From: " . $from_email . "\r\n";
		$headers .= "Reply-To: ". $from_email . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
	    mail($to_email, $subject, $message, $headers)
	    * */


				if ($email_status) {

					//echo $this->ci->email->print_debugger();
					//echo '<pre>';
					// print_r(error_get_last());
					//echo '1';
					// exit;
					return 1;
				} else {
					echo $this->ci->email->print_debugger();
					//echo '<pre>';
					//print_r(error_get_last());
					//echo '2';
					// exit;
					return 0;
				}
			}
		}
	}

	function send_client_mail_tempfun($to_email_address, $template_id, $chk_arr, $rep_arr, $client_id, $app_id, $pdf_url = null)
	{

		$this->ci =  &get_instance();

		$client_template_table = "pos_email_templates";
		$setting_table = "pos_clients";

		$query = " SELECT e.email_subject,e.email_content,s.client_from_email,s.client_to_email,s.client_site_name,s.client_sendmail_form_smptp,s.client_logo,s.client_folder_name,s.client_site_url,s.client_smpt_host,s.client_smpt_user,s.client_smpt_password,s.client_smpt_port,s.client_mail_path,s.client_email_footer_content FROM  $client_template_table as e
 	LEFT JOIN $setting_table as s ON  s.client_id ='" . $client_id . "' AND s.client_app_id='" . $app_id . "'  WHERE  e.email_id = '" . $template_id . "'";

		$result = $this->ci->Mydb->custom_query_single($query);

		if (!empty($result)) {
			/* get basic mail config values */
			$to_email = ($to_email_address == '') ? $result['client_to_email']  : $to_email_address;
			$from_email = $result['client_to_email'];
			$site_title = ucfirst($result['client_site_name']);
			$subject = $result['email_subject'];
			$email_content = stripcslashes($result['email_content']);

			/* merge contents */
			$base_url = ($result['client_site_url'] != '') ? $result['client_site_url']  : base_url();

			$chk_arr1 = array('[BASEURL]', '[COPY-CONTENT]', '[ADMIN-EMAIL]', '[SITE-TITLE]');
			$rep_array2 = array($base_url, $result['client_email_footer_content'], $result['client_to_email'], $site_title);

			$final_chk_arr = array_merge($chk_arr, $chk_arr1);
			$final_rep_arr = array_merge($rep_arr, $rep_array2);

			$message1 = str_replace($final_chk_arr, $final_rep_arr, $email_content);
			$datas = array('CONTENT' => $message1);

			$this->ci->load->library(array('parser', 'email'));

			$message = $this->ci->parser->parse('email_template_head', $datas, true);

			/* mail part */


			if ($result['client_sendmail_form_smptp'] == 1) {
				//echo "1er";

				$config['smtp_host']	= $result['client_smpt_host'];
				$config['smtp_user']	= $result['client_smpt_user'];
				$config['smtp_pass']	= $result['client_smpt_password'];
				$config['smtp_port']	= $result['client_smpt_port'];
				$config['mailpath'] 	= $result['client_mail_path'];
				$config['smtp_crypto']  = 'tls';
				$config['protocol'] 	= 'smtp';
			} else {
				//echo "2rewr";

				$config['protocol'] 	= 'sendmail';
			}

			$config['charset'] 	 = 'iso-8859-1';
			$config['wordwrap'] 	= TRUE;
			$config['charset'] 	 = "utf-8";
			$config['mailtype'] 	= "html";
			$config['newline'] 	 = "\r\n";
			$this->ci->email->initialize($config);
			$this->ci->email->from($from_email, $site_title);
			$this->ci->email->to($to_email);
			$this->ci->email->subject($subject);
			$this->ci->email->message($message);
			if (!empty($pdf_url)) {
				$this->ci->email->attach($pdf_url);
			}
			$email_status = $this->ci->email->send();


			/* if ($email_status) 
        {
 	 return 1;
 	 }
 	 else
 	 {
 	 return 0;
 	 } */
		}
	}

	/* this function used to send newsletter  */
	function send_newsletter_mail($to_email_address, $newletter_ID, $client_id, $app_id)
	{

		$this->ci =  &get_instance();

		$client_template_table = "pos_newsletter";
		$setting_table = "pos_clients";

		$query = " SELECT e.newsletter_subject,e.newsletter_content,e.newsletter_fromEmail,e.newsletter_toEmail,s.client_from_email,  s.client_sendgrid_enabled, s.client_to_email,s.client_site_name,s.client_sendmail_form_smptp,s.client_logo,s.client_folder_name,s.client_site_url,s.client_smpt_host,s.client_smpt_user,s.client_smpt_password,s.client_smpt_port,s.client_mail_path,s.client_email_footer_content FROM  $client_template_table as e
			LEFT JOIN $setting_table as s ON  s.client_id ='" . $client_id . "' AND s.client_app_id='" . $app_id . "'  WHERE  e.newsletter_id = '" . $newletter_ID . "'";



		$result = $this->ci->Mydb->custom_query_single($query);

		if (!empty($result)) {
			/* get basic mail config values */
			$to_email = ($to_email_address == '') ? $result['newsletter_toEmail']  : $to_email_address;
			$from_email = $result['newsletter_fromEmail'];
			$from_email_sendgrid = $result['client_from_email'];
			$site_title = ucfirst($result['newsletter_subject']);
			$subject = $result['newsletter_subject'];
			$email_content = stripcslashes($result['newsletter_content']);

			/* merge contents */
			$base_url = base_url();
			$datas = array('CONTENT' => $email_content);

			$this->ci->load->library(array('parser', 'email'));

			$message = $this->ci->parser->parse('email_template_head', $datas, true);

			/* mail part */

			if ($result['client_sendgrid_enabled'] == 1) {

				//require '../sendgrid-php/autoload.php'; // If you're using Composer (recommended)
				// Comment out the above line if not using Composer
				require("sendgrid-php/sendgrid-php.php");
				// If not using Composer, uncomment the above line and
				// download sendgrid-php.zip from the latest release here,
				// replacing <PATH TO> with the path to the sendgrid-php.php file,
				// which is included in the download:
				// https://github.com/sendgrid/sendgrid-php/releases

				$email = new \SendGrid\Mail\Mail();
				$email->setFrom(trim($from_email_sendgrid), $site_title);
				$email->setSubject($subject);
				$email->addTos(send_grid_to_addredd_values(trim($to_email), $name));
				$email->addContent("text/html", $message);

				$sendgrid = new \SendGrid('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');
				try {
					$response = $sendgrid->send($email);
					return 1;
				} catch (Exception $e) {
					// echo 'Caught exception: '. $e->getMessage() ."\n";
					return 0;
				}
			} else {

				if ($result['client_sendmail_form_smptp'] == 1) {
					//echo "1";
					//exit;
					$config['smtp_host']	= $result['client_smpt_host'];
					$config['smtp_user']	= $result['client_smpt_user'];
					$config['smtp_pass']	= $result['client_smpt_password'];
					$config['smtp_port']	= $result['client_smpt_port'];
					$config['mailpath'] 	= $result['client_mail_path'];
					$config['smtp_crypto']  = 'tls';
					$config['protocol'] 	= 'smtp';
				} else {
					//echo "2";
					//exit;
					$config['protocol'] 	= 'sendmail';
				}

				$config['charset'] 		= 'iso-8859-1';
				$config['wordwrap'] 	= TRUE;
				$config['charset'] 		= "utf-8";
				$config['mailtype'] 	= "html";
				$config['newline'] 		= "\r\n";
				$this->ci->email->initialize($config);
				$this->ci->email->from($from_email, $site_title);
				$this->ci->email->to($to_email);
				$this->ci->email->subject($subject);
				$this->ci->email->message($message);
				$email_status = $this->ci->email->send();

				if ($email_status) {

					return 1;
				} else {
					echo $this->ci->email->print_debugger();
					echo '<pre>';
					print_r(error_get_last());
					echo '0';
					exit;

					return 0;
				}
			}
		}
	}
}
 
/* End of file Myemail.php */
/* Location: ./application/libraries/Myemail.php */
