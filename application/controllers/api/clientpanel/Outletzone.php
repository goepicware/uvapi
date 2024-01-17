<?php

/**************************
 Project Name	: White Label
Created on		: 01 Aug, 2023
Last Modified 	: 14 Aug, 2023
Description		: Outlet Zone details

 ***************************/
defined('BASEPATH') or exit('No direct script access allowed');
// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';
class Outletzone extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->table = "pos_outlet_zone_management";
		$this->area_table = "outlet_zone_area_coverage";
		$this->outlet_availability = "outlet_zone_availability";
		$this->site_location = "site_location";
		$this->load->library('common');
		$this->load->library('Authorization_Token');
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p>', '</p>');
		$this->primary_key = 'zone_id';
		$this->primary_area_key = 'oa_id';
	}

	public function list_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$select_array = array('zone_id ', 'zone_outlet_id', 'zone_name', 'zone_min_amount', 'zone_delivery_charge', 'zone_created_on', 'zone_status');
				$limit = $offset =  '';
				$like = array();
				$get_limit = $this->input->get('limit');
				$post_offset = (int) $this->input->get('offset');
				if ((int) $get_limit != 0) {
					$limit = (int) $get_limit;
				}
				$post_offset = ($post_offset > 1) ? $post_offset - 1 : 0;
				$offset = ($post_offset > 0 ? $post_offset * $limit : 0);
				$company_id = decode_value($this->input->get('company_id'));
				$name = $this->input->get('name');
				$status = $this->input->get('status');
				$where = array(
					'zone_company_id' => $company_id,
				);
				if (!empty($status)) {
					$where = array_merge($where, array('zone_status' => $status));
				}
				if (!empty($name)) {
					$like = array_merge($like, array("zone_name" => $name));
				}

				$totalPages = 0;

				$total_records = $this->Mydb->get_num_rows($this->primary_key, $this->table, $where, null, null, null, $like);
				if (!empty($limit)) {
					$totalPages = ceil($total_records / $limit);
				}

				$result =  $this->Mydb->get_all_records($select_array, $this->table, $where, $limit, $offset,  array(
					$this->primary_key => 'DESC'
				), $like, array($this->primary_key));

				if (!empty($result)) {
					$return_array = array('status' => "ok", 'message' => 'success', 'result' => $result, 'totalRecords' => $total_records, 'totalPages' => $totalPages);
					$this->set_response($return_array, success_response());
				} else {
					$this->set_response(array('status' => 'error', 'message' => 'No Record Found.'), something_wrong());
				}
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => 'Authentication token verification failed',
					'form_error' => ''
				), something_wrong()); /* error message */
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => 'Authentication failed',
				'form_error' => ''
			), something_wrong()); /* error message */
		}
	}

	public function details_get($zone_id = null)
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$id = decode_value($this->input->get('zone_id'));
				$company_id = decode_value($this->input->get('company_id'));
				$where = array('zone_id' => $id);
				$result = $this->Mydb->get_record('*', $this->table, $where);
				if (!empty($result)) {

					$join = array();
					$join[0]['select'] = "av_name";
					$join[0]['table'] = "availability";
					$join[0]['condition'] = "oza_availability_id = av_id";
					$join[0]['type'] = "INNER";
					$outlet_availability = $this->Mydb->get_all_records('oza_availability_id', 'outlet_zone_availability', array('oza_outlet_zone_id' => $id, 'oza_company_id' => $company_id), null, null, null, null, null, $join);
					$result['zone_availability'] = $outlet_availability;

					$result['site_location'] = $this->Mydb->get_record('sl_location_id AS value, sl_name AS label', $this->site_location, array('sl_location_id' => $result['zone_site_location_id']));

					//echo $this->db->last_query();

					$return_array = array('status' => "ok", 'message' => 'success', 'result' => $result);

					$this->set_response($return_array, success_response());
				} else {
					$this->set_response(array('status' => 'error', 'message' => 'No Record Found.'), something_wrong());
				}
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => 'Authentication token verification failed',
					'form_error' => ''
				), something_wrong()); /* error message */
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => 'Authentication failed',
				'form_error' => ''
			), something_wrong()); /* error message */
		}
	}

	public function add_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {

				$this->form_validation->set_rules('zone_name', 'lang:zone_name', 'required');
				$this->form_validation->set_rules('zone_availability', 'lang:outlet_availability_option', 'required');
				$this->form_validation->set_rules('zone_postal_code', 'lang:company_postal_code', 'required');
				$this->form_validation->set_rules('zone_address_line1', 'lang:company_address', 'required');
				$this->form_validation->set_rules('status', 'lang:bp_status', 'required');

				if ($this->form_validation->run() == TRUE) {
					$this->outletzoneaddedit();
					$this->set_response(array(
						'status' => 'success',
						'message' => "Outlet Zone has been addded successfully",
						'form_error' => '',
					), success_response()); /* success message */
				} else {
					$this->set_response(array(
						'status' => 'error',
						'message' => get_label('rest_form_error'),
						'form_error' => validation_errors()
					), something_wrong()); /* error message */
				}
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => 'Authentication token verification failed',
					'form_error' => ''
				), something_wrong()); /* error message */
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => 'Authentication failed',
				'form_error' => ''
			), something_wrong()); /* error message */
		}
	}

	public function update_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$error_tag = $this->post('error_element');
				$this->form_validation->set_rules('zone_name', 'lang:zone_name', 'required');
				$this->form_validation->set_rules('zone_availability', 'lang:outlet_availability_option', 'required');
				$this->form_validation->set_rules('zone_postal_code', 'lang:company_postal_code', 'required');
				$this->form_validation->set_rules('zone_address_line1', 'lang:company_address', 'required');
				$this->form_validation->set_rules('status', 'lang:bp_status', 'required');
				if ($this->form_validation->run() == TRUE) {
					$zone_id = $this->input->post('edit_id');

					$this->outletzoneaddedit($zone_id);
					$this->set_response(array(
						'status' => 'success',
						'message' => "Outlet has been updated successfully",
						'form_error' => '',
					), success_response()); /* success message */
				} else {
					$this->set_response(array(
						'status' => 'error',
						'message' => get_label('rest_form_error'),
						'form_error' => validation_errors()
					), something_wrong()); /* error message */
				}
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => 'Authentication token verification failed',
					'form_error' => ''
				), something_wrong()); /* error message */
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => 'Authentication failed',
				'form_error' => ''
			), something_wrong()); /* error message */
		}
	}

	public function addmapdata_post()
	{
		if (post_value('company_id') && post_value('map_random_id')) {
			$company_id = decode_value($this->input->post('company_id'));
			$map_zone_id = decode_value($this->input->post('map_zone_id'));
			$where = array('map_company_id' => $company_id,	'map_random_id' => post_value('map_random_id'));
			if (!empty($map_zone_id)) {
				$where = array_merge($where, array('map_zone_id' => $map_zone_id));
			}
			$checkingZone = $this->Mydb->get_record('map_primary_id', 'outlet_zone_temp', $where);
			if (!empty($checkingZone)) {
				$Itemarray =  array(
					'map_zone_id' => $map_zone_id,
					'outlet_marker_location' => post_value('outlet_marker_location'),
					'region_type' => post_value('region_type'),
					'region_points' => post_value('region_points'),
					'region_radius' => post_value('region_radius'),
					'region_create_on' => current_date(),
				);
				$this->Mydb->update('outlet_zone_temp', array('map_primary_id' => $checkingZone['map_primary_id']), $Itemarray);
			} else {
				$Itemarray =  array(
					'map_company_id' => $company_id,
					'map_random_id' => post_value('map_random_id'),
					'map_zone_id' => $map_zone_id,
					'outlet_marker_location' => post_value('outlet_marker_location'),
					'region_type' => post_value('region_type'),
					'region_points' => post_value('region_points'),
					'region_radius' => post_value('region_radius'),
					'region_create_on' => current_date(),
				);
				$this->Mydb->insert('outlet_zone_temp', $Itemarray);
			}

			echo json_encode(array('status' => 'ok'));
		}
	}

	public function outletzoneaddedit($zone_id = null)
	{
		if (!empty($zone_id)) {
			$zone_id = decode_value($zone_id);
		}

		$outletid = $this->input->post('inpt_outletid');
		$company_id = decode_value($this->input->post('company_id'));

		$where = array('map_company_id' => $company_id,	'map_random_id' => post_value('map_random_id'));
		if (!empty($zone_id)) {
			$where = array_merge($where, array('map_zone_id' => $zone_id));
		}
		$checkingZone = $this->Mydb->get_record('map_primary_id, region_points, region_radius, region_type', 'outlet_zone_temp', $where);
		//echo post_value('region_points');

		$area_point[0] = (!empty($checkingZone)) ? $checkingZone['region_points'] : [];
		$area_radius[0] = (!empty($checkingZone)) ? $checkingZone['region_radius'] : [];
		$area_type[0] = (!empty($checkingZone)) ? $checkingZone['region_type'] : [];
		$action = post_value('action');

		$company_admin_id = decode_value($this->input->post('company_admin_id'));
		$get_company_details = $this->Mydb->get_record('company_unquie_id', 'company', array('company_id' => $company_id));

		for ($i = 0; $i < count($area_point); $i++) {
			if ($area_type[$i] == 'polygon' || 'polyline' == $area_type[$i]) {
				$overall_lat = '';
				$overall_lng = '';
				$outlet_region_array = explode('),', $area_point[$i]);
				foreach ($outlet_region_array as $outlet_point) {
					$reg_point = explode(',', $outlet_point);
					if ($overall_lat != '')
						$overall_lat .= ",";
					if ($overall_lng != '')
						$overall_lng .= ",";
					$overall_lat .= str_replace(array('(', ')'), array('', ''), $reg_point[0]);
					$overall_lng .= str_replace(array('(', ')'), array('', ''), $reg_point[1]);
				}
				if ($i == 0) {
					$area_point_primary = $overall_lat . "|" . $overall_lng;
				}
			} else {
				if ($i == 0) {
					$area_point_primary = $area_point[$i];
				}
			}
			if ($i == 0) {
				$area_radius_primary = $area_radius[$i];
				$area_type_primary = $area_type[$i];
			}
		}

		$Itemarray = array(
			'zone_name' => post_value('zone_name'),
			'zone_outlet_id' => post_value('outlet'),
			'zone_site_location_id' => post_value('site_location_id'),
			'zone_company_id' => $company_id,
			'zone_app_id' => $get_company_details['company_unquie_id'],
			'zone_postal_code' => post_value('zone_postal_code'),
			'zone_address_line1' => post_value('zone_address_line1'),
			'zone_min_amount' => post_value('zone_min_amount'),
			'zone_delivery_charge' => post_value('zone_delivery_charge'),
			'zone_additional_delivery_charge' => post_value('zone_additional_delivery_charge'),
			'zone_free_delivery' => post_value('zone_free_delivery'),
			'zone_region_colour' => post_value('zone_region_colour'),
			'zone_status' => (post_value('status') == "A" ? 'A' : 'I'),
		);
		if ($action == 'add') {
			$Itemarray = array_merge($Itemarray, array(
				'zone_created_on' => current_date(),
				'zone_created_by' => $company_admin_id,
				'zone_created_ip' => get_ip()
			));
			$zone_id = $this->Mydb->insert($this->table, $Itemarray);

			createAuditLog("Zone", stripslashes(post_value('zone_name')), "Add", $company_admin_id, 'Web', '', $company_id, $get_company_details['company_unquie_id']);
		} else if ($action == 'edit') {
			$Itemarray = array_merge($Itemarray, array(
				'zone_updated_on' => current_date(),
				'zone_updated_by' => $company_admin_id,
				'zone_updated_ip' => get_ip()
			));

			$this->Mydb->update($this->table, array($this->primary_key => $zone_id), $Itemarray);

			createAuditLog("Zone", stripslashes(post_value('zone_name')), "Update", $company_admin_id, 'Web', '', $company_id, $get_company_details['company_unquie_id']);
		}
		if (!empty($zone_id)) {
			if (!empty($checkingZone)) {
				$this->Mydb->delete('outlet_zone_temp', array('map_primary_id' => $checkingZone['map_primary_id']));
			}


			$area_point_primary = $area_point_secondary = $area_radius_primary = $area_radius_secondary = $area_type_primary = $area_type_secondary = '';
			for ($i = 0; $i < count($area_point); $i++) {
				if ($area_type[$i] == 'polygon' || 'polyline' == $area_type[$i]) {
					$overall_lat = '';
					$overall_lng = '';
					$outlet_region_array = explode('),', $area_point[$i]);
					if (count($outlet_region_array) > 1) {
						foreach ($outlet_region_array as $outlet_point) {
							$reg_point = explode(',', $outlet_point);
							if ($overall_lat != '')
								$overall_lat .= ",";
							if ($overall_lng != '')
								$overall_lng .= ",";
							$overall_lat .= str_replace(array('(', ')'), array('', ''), $reg_point[0]);
							$overall_lng .= str_replace(array('(', ')'), array('', ''), $reg_point[1]);
						}
						if ($i == 0) {
							$area_point_primary = $overall_lat . "|" . $overall_lng;
						}
					} else {
						if ($i == 0) {
							$area_point_primary = $outlet_region_array[0];
						}
					}
				} else {
					if ($i == 0) {
						$area_point_primary = $area_point[$i];
					}
				}
				if ($i == 0) {
					$area_radius_primary = $area_radius[$i];
					$area_type_primary = $area_type[$i];
				}
			}
			if ($action == 'edit') {
				$this->Mydb->delete($this->area_table, array('oa_outlet_zone_id' => $zone_id));
			}
			$area_insert_array = array(
				'oa_company_id' => $company_id,
				'oa_app_id' => $get_company_details['company_unquie_id'],
				'oa_outlet_id' => post_value('outlet'),
				'oa_outlet_zone_id' => $zone_id,
				'oa_region_type_primary' => (!empty($area_type_primary)) ? $area_type_primary : '',
				'oa_region_points_primary' => (!empty($area_point_primary)) ? $area_point_primary : '',
				'oa_region_radius_primary' => (!empty($area_radius_primary)) ? $area_radius_primary : '',
				'oa_region_marker_location' => post_value('outlet_marker_location'),
				'oa_updated_on' => current_date(),
				'oa_updated_by' => $company_admin_id,
				'oa_updated_ip' => get_ip()
			);
			$area_insert_id = $this->Mydb->insert($this->area_table, $area_insert_array);
			if ($action == 'edit') {
				$this->Mydb->delete($this->outlet_availability, array('oza_outlet_zone_id' => $zone_id));
			}
			if ($this->input->post('zone_availability') != "") {
				$available_insert_array = array();
				$zone_availability = $this->input->post('zone_availability');
				$zone_availability_new = explode(',', $zone_availability);
				foreach ($zone_availability_new as $available) {
					$available_insert_array[] = array(
						'oza_company_id' => $company_id,
						'oza_company_app_id' => $get_company_details['company_unquie_id'],
						'oza_outlet_id' => post_value('outlet'),
						'oza_outlet_zone_id' => $zone_id,
						'oza_availability_id' => $available,
						'oza_created_on' => current_date(),
						'oza_created_by' => $company_admin_id,
						'oza_created_ip' => get_ip()
					);
				}
				if (!empty($available_insert_array)) {
					$area_insert_id = $this->db->insert_batch($this->outlet_availability, $available_insert_array);
				}
			}
		}
	}

	public function delete_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$company_id = decode_value($this->input->post('company_id'));
				$zone_id = decode_value($this->input->post('zone_id'));
				$company_admin_id = decode_value($this->input->post('company_admin_id'));
				if (!empty($company_id)) {
					$get_company_details = $this->Mydb->get_record('company_unquie_id', 'company', array('company_id' => $company_id));
					$where = array(
						'zone_id' => trim($zone_id)
					);
					$result = $this->Mydb->get_record('zone_id, zone_name', $this->table, $where);
					if (!empty($result)) {
						$this->Mydb->delete($this->table, array('zone_id' => $zone_id, 'zone_company_id' => $company_id));
						$this->Mydb->delete($this->area_table, array('oa_outlet_zone_id' => $zone_id, 'oa_company_id' => $company_id));

						createAuditLog("Zone", stripslashes($result['zone_name']), "Delete", $company_admin_id, 'Web', '', $company_id, $get_company_details['company_unquie_id']);

						$return_array = array('status' => "ok", 'message' => 'Oultet Zone deleted successfully.',);
						$this->set_response($return_array, success_response());
					} else {
						$this->set_response(array('status' => 'error', 'message' => 'No Record Found.', 'form_error' => ''), something_wrong());
					}
				} else {
					$this->set_response(array('status' => 'error', 'message' => 'Zone Id field is required', 'form_error' => ''), something_wrong());
				}
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => 'Authentication token verification failed',
					'form_error' => ''
				), something_wrong()); /* error message */
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => 'Authentication failed',
				'form_error' => ''
			), something_wrong()); /* error message */
		}
	}



	public function action_post()
	{

		$ids = ($this->input->post('multiaction') == 'Yes' ? $this->input->post('zone_id') : decode_value($this->input->post('changeId')));
		$postaction = $this->input->post('postaction');
		$company_id = decode_value($this->input->post('company_id'));
		$company_admin_id = decode_value($this->input->post('company_admin_id'));
		$get_company_details = $this->Mydb->get_record('company_unquie_id', 'company', array('company_id' => $company_id));
		$company_app_id =  $get_company_details['company_unquie_id'];
		$response = array(
			'status' => 'error',
			'msg' => get_label('something_wrong'),
			'action' => '',
			'form_error' => '',
			'multiaction' => $this->input->post('multiaction')
		);
		/* Delete */
		$wherearray = array('zone_company_id' => $company_id, 'zone_app_id' => $company_app_id);
		$wherearray_oa = array('oa_company_id' => $company_id, 'oa_app_id' => $company_app_id);

		if ($postaction == 'Delete' && !empty($ids)) {
			$this->audit_action($ids, $postaction);
			if (is_array($ids)) {
				$this->Mydb->delete_where_in($this->table, 'zone_id', $ids, $wherearray);
				$this->Mydb->delete_where_in($this->area_table, 'oa_outlet_zone_id', $ids, $wherearray_oa);
				$response['msg'] = sprintf($this->lang->line('success_message_delete'), $this->module_label);
			} else {
				$this->Mydb->delete($this->table, array('zone_id' => $ids, 'zone_company_id' => $company_id, 'zone_app_id' => $company_app_id));
				$this->Mydb->delete($this->area_table, array('oa_outlet_zone_id' => $ids, 'oa_company_id' => $company_id, 'oa_app_id' => $company_app_id));
				$response['msg'] = sprintf($this->lang->line('success_message_delete'), $this->module_label);
			}
			$response['status'] = 'success';
			$response['action'] = $postaction;
		}


		$where_array = array('zone_company_id' => $company_id, 'zone_app_id' => $company_app_id);
		/* Activation */
		if ($postaction == 'Activate' && !empty($ids)) {
			$update_values = array(
				"zone_status" => 'A',
				"zone_updated_on" => current_date(),
				'zone_updated_by' => $company_admin_id,
				'zone_updated_ip' => get_ip()
			);

			if (is_array($ids)) {
				$this->Mydb->update_where_in($this->table, $this->primary_key, $ids, $update_values, $where_array);
				$response['msg'] = sprintf($this->lang->line('success_message_activate'), $this->module_labels);
			} else {

				$this->Mydb->update_where_in($this->table, $this->primary_key, array(
					$ids
				), $update_values, $where_array);
				$response['msg'] = sprintf($this->lang->line('success_message_activate'), $this->module_label);
			}
			/* track outlet status */
			$this->track_outlet_status($ids, 1);
			$this->audit_action($ids, $postaction);
			$response['status'] = 'success';
			$response['action'] = $postaction;
		}

		/* Deactivation */
		if ($postaction == 'Deactivate' && !empty($ids)) {
			$update_values = array(
				"zone_status" => 'I',
				"zone_updated_on" => current_date(),
				'zone_updated_by' => $company_admin_id,
				'zone_updated_ip' => get_ip()
			);

			if (is_array($ids)) {
				$this->Mydb->update_where_in($this->table, $this->primary_key, $ids, $update_values, $where_array);
				$response['msg'] = sprintf($this->lang->line('success_message_deactivate'), $this->module_labels);
			} else {
				$this->Mydb->update_where_in($this->table, $this->primary_key, array(
					$ids
				), $update_values, $where_array);
				$response['msg'] = sprintf($this->lang->line('success_message_deactivate'), $this->module_label);
			}
			/* track outlet status */
			$this->track_outlet_status($ids, 0);
			$this->audit_action($ids, $postaction);
			$response['status'] = 'success';
			$response['action'] = $postaction;
		}

		$this->set_response($response, success_response()); /* success message */
	}
} /* end of files */
