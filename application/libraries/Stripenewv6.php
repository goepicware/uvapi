<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
//print_R(dirname(__FILE__));die;
require_once(dirname(__FILE__) . '/Stripenewv6/init.php');

class Stripenewv6
{
	/**
	 * Get an instance of CodeIgniter
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function ci()
	{
		return get_instance();
	}


}
