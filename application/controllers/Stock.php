<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Stock extends CI_Controller
{

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     * 		http://example.com/index.php/welcome
     *	- or -
     * 		http://example.com/index.php/welcome/index
     *	- or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see https://codeigniter.com/user_guide/general/urls.html
     */
    function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<p>', '</p>');
    }

    public function index()
    {
        $this->load->view('welcome_message');
    }

    public function lowStockAlert()
    {
        $join = array();
        $i = 0;
        $join[$i]['select']    = '';
        $join[$i]['condition'] = "product_primary_id = pao_product_primary_id";
        $join[$i]['table']     = 'product_assigned_outlets';
        $join[$i]['type']      = "INNER";
        $i++;

        $join[$i]['select']    = 'outlet_name, outlet_email';
        $join[$i]['condition'] = "pao_outlet_id = outlet_id";
        $join[$i]['table']     = 'outlet_management';
        $join[$i]['type']      = "INNER";
        $i++;

        $products = $this->Mydb->get_all_records('product_primary_id, product_name, product_alias, product_company_unique_id, product_sku, product_stock', 'products', array('product_stock_alert' => '1', 'product_stock_min>' => 0, 'product_status' => 'A', 'product_stock_min>=product_stock' => NULL, 'product_company_unique_id' => 'A90F5FAC-8561-41E0-822B-2531CD32CBA7', "product_stock_alert_email_status!='1'" => NULL), '', '', '', '', array('product_primary_id'), $join);
        if (!empty($products)) {
            $this->load->library('myemail');
            foreach ($products as $val) {
                $company = app_validation($val['product_company_unique_id']);
                $email_template_id = get_emailtemplate($val['product_company_unique_id'], 'lowstock');
                if (!empty($email_template_id)) {
                    $productName = (!empty($val['product_alias'])) ? stripcslashes($val['product_alias']) : stripcslashes($val['product_name']);
                    $check_arr = array('[NAME]', '[PRODUCTNAME]', '[SKU]', '[CURRENTSTOCK]');
                    $replace_arr = array(ucfirst(stripslashes($val['outlet_name'])), $productName, $val['product_sku'], $val['product_stock']);
                    $subject = "Low Stock Alert " . ucwords($productName);
                    $mailSent = $this->myemail->send_client_mail($company, $val['outlet_email'], $email_template_id, $check_arr,  $replace_arr, '', $subject);
                    if (!empty($mailSent)) {
                        $this->Mydb->update('products', array('product_primary_id' => $val['product_primary_id']), array('product_stock_alert_email_status' => '1'));
                    }
                }
            }
        }

        echo json_encode(array('status' => 'ok'));
    }
}
