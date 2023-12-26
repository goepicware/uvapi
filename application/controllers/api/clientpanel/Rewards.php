<?php
/**************************
Project Name	: White Label
Created on		: 25 Aug, 2023
Last Modified 	: 25 Aug, 2023
Description		: Rewards

***************************/
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
require APPPATH . '/libraries/REST_Controller.php';
class Rewards extends REST_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->library ( 'form_validation' );
		$this->form_validation->set_error_delimiters ( '<p>', '</p>' );
		$this->table = 'loyality_points';
		$this->table_customer = 'customers';
		$this->table_orders = 'orders';
		$this->orders_customer_details = 'orders_customer_details';
		$this->load->library('Authorization_Token');
		$this->primary_key = 'lh_id';
        $this->load->helper('loyalty');
	}

	public function list_get(){
		$headers = $this->input->request_headers();
		if(isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if($decodedToken['status']){
				$select_array = array ('usergroup_id','usergroup_name','usergroup_created_on');
				$limit = $offset = $like = $next = $previous = '';
				$get_limit = $this->input->get ( 'limit' );
				$post_offset = ( int ) $this->input->get ( 'offset' );
				if (( int ) $get_limit != 0) {
					$limit = ( int ) $get_limit;
				}
				$post_offset = ($post_offset>1)?$post_offset-1:0;
				$offset = ($post_offset > 0 ? $post_offset * $limit : 0);

				$company_id = decode_value($this->input->get ( 'company_id' ));
				$company_admin_id = decode_value($this->input->get ( 'company_admin_id' ));
				$where = array ("$this->primary_key !=" => '', $this->company_id => $company_id);
				
				$order_by = array ($this->primary_key => 'DESC');

                $join [0] ['select'] ='customer_id,customer_unique_id,customer_first_name,customer_last_name';
                $join [0] ['table'] = $this->table_customer;
                $join [0] ['condition'] = 'customer_id = lh_customer_id';
                $join [0] ['type'] = 'LEFT';
        
                $join [1] ['select'] ='order_total_amount';
                $join [1] ['table'] = 'orders';
                $join [1] ['condition'] = 'order_primary_id = lh_ref_id';
                $join [1] ['type'] = 'LEFT';
        
                $groupby = $this->primary_key;

                
				$total_records = $this->Mydb->get_num_join_rows ($this->table.'.*', $this->table, $where, '', null, null, $like,$groupby,$join);
				
                echo '<pre>';
                print_r($total_records);
                exit;
                
				$totalPages = (!empty($limit))?ceil($total_records / $limit):0;		
                

				$result = $this->Mydb->get_all_records ( $select_array, $this->table, $where, $limit, $offset,  array (
					$this->primary_key => 'DESC'
				), '', array ($this->primary_key));
				if (! empty ( $result )) {
					$return_array = array ('status' => "ok",'message' => 'success','result' => $result, 'totalRecords' => $total_records, 'totalPages' => $totalPages);
					$this->set_response ( $return_array, success_response () );
				} else {
					$this->set_response(array('status'=>'error','message'=>get_label('no_records_found')), something_wrong());
				}
			}else{
				$this->set_response ( array (
					'status' => 'error',
					'message' => get_label('token_verify_faild'),
					'form_error' => ''
				), something_wrong () ); /* error message */
			}
		}else{
			$this->set_response ( array (
				'status' => 'error',
				'message' => get_label('token_faild'),
				'form_error' => ''
			), something_wrong () ); /* error message */
		}		
	}

	public function dropdownlist_get(){
		$headers = $this->input->request_headers();		
		if(isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if($decodedToken['status']){
				$company_id = decode_value($this->input->get('company_id'));
				$select_array = array(
					$this->primary_key.' AS value',
					'usergroup_name AS label',
				);
				$where = array (
					$this->company_id => $company_id
				);
				$result = $this->Mydb->get_all_records ( $select_array, $this->table, $where);          
				if (! empty ( $result )) {
					$return_array = array ('status' => "ok",'message' => 'success','result' => $result);
					$this->set_response ( $return_array, success_response () );
				} else {
					$this->set_response(array('status'=>'error','message'=>get_label('no_records_found')), something_wrong());
				}
			}else{
				$this->set_response ( array (
					'status' => 'error',
					'message' => get_label('token_verify_faild'),
				), something_wrong () ); /* error message */
			}
		}else{
			$this->set_response ( array (
				'status' => 'error',
				'message' => get_label('token_faild'),
			), something_wrong () ); /* error message */
		}		
	}

	public function details_get(){
		$headers = $this->input->request_headers();		
		if(isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if($decodedToken['status']){
				$id = decode_value($this->input->get('detail_id'));
				$company_id = decode_value($this->input->get ( 'company_id' ));
				$where = array($this->primary_key => $id, $this->company_id => $company_id);
				$result = $this->Mydb->get_record ( '*', $this->table, $where);
				if (! empty ( $result )) {
					$return_array = array ('status' => "ok",'message' => 'success','result' => $result);
					$this->set_response ( $return_array, success_response () );
				} else {
					$this->set_response(array('status'=>'error','message'=>get_label('no_records_found')), something_wrong());
				}
			}else{
				$this->set_response ( array (
					'status' => 'error',
					'message' => get_label('token_verify_faild'),
					'form_error' => ''
				), something_wrong () ); /* error message */
			}
		}else{
			$this->set_response ( array (
				'status' => 'error',
				'message' => get_label('token_faild'),
				'form_error' => ''
			), something_wrong () ); /* error message */
		}		
	}

	public function add_post(){
		$headers = $this->input->request_headers();
		if(isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if($decodedToken['status']){
				$this->form_validation->set_rules('role_name','Role Name','required|trim|strip_tags');
				if ($this->form_validation->run () == TRUE) {
					$this->addedit();
					$this->set_response ( array (
						'status' => 'success',
						'message' => sprintf ( get_label ( 'success_message_add' ), $this->label ),
						'form_error' => '', 
					), success_response () ); /* success message */
				}
			}else{
				$this->set_response ( array (
					'status' => 'error',
					'message' => get_label('token_verify_faild'),
					'form_error' => ''
				), something_wrong () ); /* error message */
			}
		}else{
			$this->set_response ( array (
				'status' => 'error',
				'message' => get_label('token_faild'),
				'form_error' => ''
			), something_wrong () ); /* error message */
		}
	}

	public function update_post(){
		$headers = $this->input->request_headers();		
		if(isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if($decodedToken['status']){
				$this->form_validation->set_rules('role_name','Role Name','required|trim|strip_tags');
				if ($this->form_validation->run () == TRUE) {
					$edit_id = $this->input->post('edit_id');
					$this->addedit($edit_id);
					$this->set_response ( array (
						'status' => 'success',
						'message' => sprintf ( get_label ( 'success_message_edit' ), $this->label ),
						'form_error' => '', 
					), success_response () ); /* success message */
				}else{
					$this->set_response ( array (
						'status' => 'error',
						'message' => get_label ( 'rest_form_error' ),
						'form_error' => validation_errors () 
					), something_wrong () ); /* error message */
				}
			}else{
				$this->set_response ( array (
					'status' => 'error',
					'message' => get_label('token_verify_faild'),
					'form_error' => ''
				), something_wrong () ); /* error message */
			}
		}else{
			$this->set_response ( array (
				'status' => 'error',
				'message' => get_label('token_faild'),
				'form_error' => ''
			), something_wrong () ); /* error message */
		}
	}

	public function delete_post() {
		$headers = $this->input->request_headers();		
		if(isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);			
			if($decodedToken['status']){
				$company_id = decode_value($this->input->post ( 'company_id' ));
				$delete_id = decode_value($this->input->post ( 'delete_id' ));
				if (!empty($company_id)) {
					$where = array (
						$this->primary_key => trim ( $delete_id ) 
					);
					$result = $this->Mydb->get_record ( $this->primary_key, $this->table, $where);
					if (! empty ( $result )) {
						$this->Mydb->delete ( $this->table, array ($this->primary_key => $result[$this->primary_key]));
						$return_array = array ('status' => "ok",'message' => sprintf ( get_label ( 'success_message_delete' ), $this->label ));
						$this->set_response ( $return_array, success_response () );

					} else {
						$this->set_response(array('status'=>'error','message'=>get_label('no_records_found'), 'form_error' => ''), something_wrong());
					}
				}else{
					$this->set_response(array('status'=>'error','message'=>sprintf ( get_label ( 'field_required' ), 'Company ID' ), 'form_error' => ''), something_wrong());
				}
			}else{
				$this->set_response ( array (
					'status' => 'error',
					'message' => get_label('token_verify_faild'),
					'form_error' => ''
				), something_wrong () ); /* error message */
			}
		}else{
			$this->set_response ( array (
				'status' => 'error',
				'message' => get_label('token_faild'),
				'form_error' => ''
			), something_wrong () ); /* error message */
		}
	}

	public function addedit($edit_id = null){
		if(!empty($edit_id)){
			$edit_id = decode_value($edit_id);
		}
		$action = post_value('action');
		$data = array(
			'usergroup_name' => post_value('role_name'),
		);

		if($action == 'add'){
			$company_id = decode_value($this->input->post('company_id'));
			$company_admin_id = decode_value($this->input->post('company_admin_id'));
			$data = array_merge(
				$data,
				array(
					'usergroup_company_id' 	=> $company_id,
					'usergroup_created_on'	=> current_date(),
					'usergroup_created_by' 	=> $company_admin_id,
					'usergroup_created_ip' 	=> get_ip ()					
				)
			);
			
			$this->Mydb->insert($this->table,$data);
		}else{
			$data = array_merge(
				$data,
				array(
					'usergroup_updated_on'	=> current_date(),
					'usergroup_updated_by' 	=> $company_admin_id,
					'usergroup_updated_ip' 	=> get_ip ()					
				)
			);
			$this->Mydb->update($this->table,array($this->primary_key => $edit_id),$data);
		}
	}

	public function action_post() {
		$ids = ($this->input->post ( 'multiaction' ) == 'Yes' ? $this->input->post ( 'id' ) : decode_value ( $this->input->post ( 'changeId' ) ));		
		$postaction = $this->input->post ( 'postaction' );
		$company_id = decode_value($this->input->post('company_id'));
		$company_admin_id = decode_value($this->input->post('company_admin_id'));
		$get_company_details = $this->Mydb->get_record('company_unquie_id', 'company', array('company_id' => $company_id));
		$company_app_id =  $get_company_details['company_unquie_id'];
		$response = array (
			'status' => 'error',
			'msg' => get_label ( 'something_wrong' ),
			'action' => '',
			'form_error' => '', 
			'multiaction' => $this->input->post ( 'multiaction' ) 
		);

		/* Delete */
		$wherearray=array('menu_company_id' => $company_id, 'menu_unquie_id'=>$company_app_id);
		if ($postaction == 'Delete' && ! empty ( $ids )) {
			$this->audit_action($ids, $postaction);
			// $this->Mydb->delete_where_in($this->table,'client_id',$ids,'');
			if (is_array ( $ids )) {
				$this->Mydb->delete_where_in($this->table,$this->primary_key,$ids,$wherearray);
				$response ['msg'] = sprintf ( get_label ( 'success_message_delete' ), $this->module_label );
			} else {
				$this->Mydb->delete($this->table,array($this->primary_key=>$ids,'menu_company_id' => $company_id, 'menu_unquie_id'=>$company_app_id));
				$response ['msg'] = sprintf ( get_label ( 'success_message_delete' ), $this->module_label );
			}
			$response ['status'] = 'success';
			$response ['action'] = $postaction;
		}
		
		$where_array = array ( 'menu_company_id' => $company_id, 'menu_unquie_id'=>$company_app_id);
		/* Activation */
		if ($postaction == 'Activate' && ! empty ( $ids )) {
			$update_values = array (
				"menu_status" => 'A',
				"menu_updated_on" => current_date (),
				'menu_updated_by' => $company_admin_id,
				'menu_updated_ip' => get_ip () 
			);
			
			if (is_array ( $ids )) {
				$this->Mydb->update_where_in ( $this->table, $this->primary_key, $ids, $update_values, $where_array );
				$response ['msg'] = sprintf ( get_label ( 'success_message_activate' ), $this->module_labels );
			} else {
				
				$this->Mydb->update_where_in ( $this->table, $this->primary_key, array (
					$ids 
				), $update_values, $where_array );
				$response ['msg'] = sprintf ( get_label ( 'success_message_activate' ), $this->module_label );
			}
			/* track outlet status */
			$this->track_outlet_status ( $ids, 1 );
			$this->audit_action($ids, $postaction);
			$response ['status'] = 'success';
			$response ['action'] = $postaction;
		}
		
		/* Deactivation */
		if ($postaction == 'Deactivate' && ! empty ( $ids )) {
			$update_values = array (
				"menu_status" => 'I',
				"menu_updated_on" => current_date (),
				'menu_updated_by' => $company_admin_id,
				'menu_updated_ip' => get_ip () 
			);
			
			if (is_array ( $ids )) {
				$this->Mydb->update_where_in ( $this->table, $this->primary_key, $ids, $update_values, $where_array );
				$response ['msg'] = sprintf ( get_label ( 'success_message_deactivate' ), $this->module_labels );
			} else {
				$this->Mydb->update_where_in ( $this->table, $this->primary_key, array (
					$ids 
				), $update_values, $where_array );
				$response ['msg'] = sprintf ( get_label ( 'success_message_deactivate' ), $this->module_label );
			}
			$response ['status'] = 'success';
			$response ['action'] = $postaction;
		}
		
		$this->set_response ( $response, success_response () ); /* success message */
	}

} /* end of files */
