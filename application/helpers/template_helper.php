<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

  if ( ! function_exists('get_header'))
  {
      function get_header($name=null)
      {
          $ci =& get_instance();
          return $ci->template->get_header($name);
      }
  }
 
  if ( ! function_exists('get_sidebar'))
  {
      function get_sidebar($name=null)
      {
          $ci =& get_instance();
          return $ci->template->get_sidebar($name);
      }
  }
  
  if ( ! function_exists('get_includes'))
  {
  	function get_includes($name=null)
  	{
  		$ci =& get_instance();
  		return $ci->template->get_includes($name);
  	}
  }
 
  if ( ! function_exists('get_footer'))
  {
      function get_footer($name=null)
      {
          $ci =& get_instance();
          return $ci->template->get_footer($name);
      }
  }
 
  if ( ! function_exists('get_template_part'))
  {
      function get_template_part($slug, $name=null)
      {
          $ci =& get_instance();
          return $ci->template->get_template_part($slug, $name);
      }
  }
  
  if ( ! function_exists('get_basic_editor'))
  {
      function get_basic_editor($name=array())
      {
          $ci =& get_instance();
          return $ci->template->get_basic_editor($name);
      }
  }
  
  if ( ! function_exists('get_editor'))
  {
      function get_editor($name=array())
      {
          $ci =& get_instance();
          return $ci->template->get_editor($name);
      }
  }
  
  if ( ! function_exists('get_template'))
  {
      function get_template($view,$data=null)
      {
          $ci =& get_instance();
          return $ci->template->get_template($view,$data);
      }
  }
  
  if ( ! function_exists('isAjaxRequest')) {
  	function isAjaxRequest(){
  		if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
  			redirect(base_url());
  		}	else {
  			return true;
  		}
  	}
  }  
  
  /*load meta tags */
  if (!function_exists('load_meta_tags')) {
  
  	function load_meta_tags($data) {
  		$CI = & get_instance();
  		$title = get_site_title($data['metatitle']);
  		$meta = array(
  				array('name' => 'robots', 'content' => 'index,follow'),
  				array('name' => 'description', 'content' => (isset($data['metacontent']) && $data['metacontent']!="")  ? $data['metacontent'] : $title ),
  				array('name' => 'keywords', 'content' => (isset($data['metakeyword']) && $data['metakeyword'] !='' )? $data['metakeyword'] : $title),
  				array('name' => 'Content-type', 'content' => 'text/html; charset=utf-8', 'type' => 'equiv'));
  			
  		echo "<title>" . $title . "</title>";
  		echo meta($meta);
  		echo "<meta charset=\"utf-8\">";
  		echo "<link rel=\"shortcut icon\" href=\"".skin_url()."images/favicon.ico\" type=\"image/x-icon\">";
  		echo "<link rel=\"icon\" href=\"".skin_url()."images/favicon.ico\" type=\"image/x-icon\">";
  		echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no\" />";
  		echo '<meta http-equiv="X-UA-Compatible" content="IE=Edge" />';
  
  	}
  }
  /*LOAD CSS */
 
  if (!function_exists('load_css')) {
  	function load_css($css) {
  		$CI = & get_instance();
  		foreach ($css as $file) {
  			echo link_tag(skin_url() . "css/" . $file);
  		}
  
  	}
  }
  
  
  /* lOAD js*/
  if (!function_exists('load_js')) {
  	function load_js($files) {
  		foreach ($files as $file) {
  			echo "<script type='text/javascript' src = \"" . skin_url() . "js/" . $file . "\" ></script>";
  		}
  	}
  }
  
  /* lOAD lib  js*/
  if (!function_exists('load_lib_js')) {
  	function load_lib_js($files) {
  		foreach ($files as $file) {
  			echo "<script type='text/javascript' src = \"" . load_lib() . "" . $file . "\" ></script>";
  		}
  	}
  }
  
  /* lOAD lib js*/
  if (!function_exists('load_lib_css')) {
  	function load_lib_css($files) {
  		foreach ($files as $file) {
  			echo link_tag(load_lib()  . $file);
  		}
  	}
  }
  

  if (!function_exists('load_css_callcenter')) {
  	function load_css_callcenter($css) {
  		$CI = & get_instance();  		
  		foreach ($css as $file) {
  			echo link_tag(callcenter_skin_url() . "css/" . $file);
  		}
  
  	}
  }
  if (!function_exists('load_js_callcenter')) {
  	function load_js_callcenter($css) {
  		$CI = & get_instance();
  		foreach ($css as $file) {
  			echo "<script type='text/javascript' src = \"" . callcenter_skin_url() . "js/" . $file . "\" ></script>";
  		}
  
  	}
  }

  
