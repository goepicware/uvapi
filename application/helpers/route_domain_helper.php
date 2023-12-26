<?php
/* get business center domains list */
if (! function_exists ( 'get_bc_domains_list' )) {

	function get_bc_domains_list() {

		$CI = & get_instance ();

		$arr = array (
					array('url' => 'https://mars.ninjaos.com','name'=> 'NINJA PRO'),
					array('url' => 'https://ccpl.ninjaos.com','name'=> 'NINJA ENTERPRISE'),
					array('url' => 'https://venus.ninjaos.com','name'=> 'NINJA ENTERPRISE APP'),
					array('url' => 'https://pluto.ninjaos.com','name'=> 'NINJA ENTERPRISE TWO'),
					//array('url' => 'https://jupiter.ninjaos.com','name'=> 'JUPITER'),
				);

		return $arr;

	}
}
