<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//require(APPPATH . 'third_party/sendgrid/vendor/autoload.php');
require(APPPATH . 'third_party/sendgrid/sendgrid-php.php');


	class Sendgrids
	{
		function __construct()
		{

			$this->key = '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';

		}
		
		
		function sendEmail($from,$to,$subject,$html) {
			
			$ref = null; $log = null;
			
			$email = new \SendGrid\Mail\Mail(); 
			$email->setFrom($from);
			$email->setSubject($subject);
			$email->addTo($to);
			
			$email->addContent(
			"text/html", $html
			);
			$sendgrid = new \SendGrid($this->key);
			try {
				$response = $sendgrid->send($email);
			//	print $response->statusCode() . "\n";
			//	print_r($response->headers());
			//	print $response->body() . "\n";
			
			   $log = $response->headers();

				$status = 1;$msg = $response->body();
			} catch (Exception $e) {
				$status = 0; $msg = $e->getMessage();
			}

			
	
			return array($status,$msg,$log);

		}
	

    }


/* End of file Mailgun.php */
