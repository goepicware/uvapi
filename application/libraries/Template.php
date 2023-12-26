<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
  
  class Template
  {
      private $ci;
      public $tp_name;
      private $data = array();
 
      public function __construct() {
          $this->ci = &get_instance();
      }
 
      public function set($name='') {
          $this->tp_name = $name;
      }
 
      public function load($name = 'index') {
		 // echo $name; exit;
          $this->load_file($name);
      }
 
      public function get_header($name) {
          if(isset($name)) {
              $file_name = "header-{$name}.php";
              $this->load_file($file_name);
          }
          else {
              $this->load_file('header');
          }
      }
 
      public function get_sidebar($name) {
          if(isset($name)) {
              $file_name = "sidebar-{$name}.php";
              $this->load_file($file_name);
          }
          else {
              $this->load_file('sidebar');
          }
      }
      
      public function get_includes($name) {
      	if(isset($name)) {
      		$file_name = "{$name}-includes.php";
      		$this->load_file($file_name);
      	}

      }
      
 
      public function get_footer($name) {
          if(isset($name)) {
              $file_name = "footer-{$name}.php";
              $this->load_file($file_name);
          }
          else {
              $this->load_file('footer');
          }
      }
 
      public function get_template_part($slug, $name) {
          if(isset($name)) {
              $file_name = "{$slug}-{$name}.php";
              $this->load_file($file_name);
          }
          else{
              $this->load_file($slug);
          }
      }
      
      public function get_basic_editor($name) {
          
              $file_name = "editor-basic.php";
              $data = $name;
              $this->load_file($file_name,$data);
          
      }
      
       public function get_editor($name) {
          
              $file_name = "editor.php";
              $data = $name;
              $this->load_file($file_name,$data);
          
      }
      
      public function load_file($name,$data='')
      {
         
          if($data)
          {
              
              $this->ci->load->view($this->tp_name.'/'.$name,$data);
          }
          else {
			
              $data = $this->get_data();
              if(!$data){
                  $data = array();
              }
              //echo $this->tp_name.'/'.$name; exit;
              $this->ci->load->view($this->tp_name.'/'.$name,$data);
          }
      }
      
      public function get_template($view,$data)
      {
              
              return $this->ci->load->view($view,$data,true);
           
      }
 
      
      public function set_data($data='') {
          $this->data = $data;
      }
      
      
      public function get_data($key='') {
          
          if(isset($this->data[$key])) {
              return $this->data[$key];
          }
          else {
			  
              return $this->data;
          }
          
      }
  }
