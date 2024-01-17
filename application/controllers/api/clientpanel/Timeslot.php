<?php

/**************************
Project Name	: White Label
Created on		: 30 Aug, 2023
Last Modified 	: 30 Aug, 2023
Description		: Time Slot details

 ***************************/
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Timeslot extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p>', '</p>');
		$this->table = "advanced_delivery_time_settings";
		$this->time_order_count = "advanced_delivery_time_order_count";
		$this->time_settings_days = "advanced_delivery_time_settings_days";
		$this->time_settings_time = "advanced_delivery_time_settings_time";
		$this->outlet_management = "outlet_management";
		$this->availability = "availability";
		$this->load->library('common');
		$this->label = get_label('timeslot_manage_label');
		$this->load->library('Authorization_Token');
		$this->primary_key = 'delivery_time_setting_id';
		$this->company_id = 'delivery_time_setting_company_id';
	}

	public function list_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$logID = decode_value($this->input->get('logID'));
				$userDetails = getUserDetails($logID);
				if (!empty($userDetails)) {
					$select_array = array('delivery_time_setting_id', "delivery_time_slot_type", 'delivery_time_setting_interval_time', 'delivery_time_setting_status', 'delivery_time_setting_cutt_off', 'delivery_time_setting_created_on');
					$limit = $offset = '';
					$get_limit = $this->input->get('limit');
					$post_offset = (int) $this->input->get('offset');
					if ((int) $get_limit != 0) {
						$limit = (int) $get_limit;
					}
					$post_offset = ($post_offset > 1) ? $post_offset - 1 : 0;
					$offset = ($post_offset > 0 ? $post_offset * $limit : 0);

					$company_id = decode_value($this->input->get('company_id'));
					$status = $this->input->get('status');
					$outlet_id = $this->input->get('outlet_id');

					$where = array("$this->primary_key !=" => '', $this->company_id => $company_id);
					if (!empty($status)) {
						$where = array_merge($where, array('delivery_time_setting_status' => $status));
					}
					if (!empty($outlet_id)) {
						if ($outlet_id == 'Common') {
							$outlet_id = '0';
						}
						$where = array_merge($where, array('delivery_time_setting_outlet_id' => $outlet_id));
					}
					if (!empty($userDetails)) {
						if ($userDetails['company_user_type'] == 'SubAdmin') {
							$where = array_merge($where, array("delivery_time_setting_outlet_id" => $userDetails['company_user_permission_outlet']));
						}
					}

					$order_by = array($this->primary_key => 'DESC');
					$join = array();
					$i = 0;
					$join[$i]['select'] = "av_name";
					$join[$i]['table'] = $this->availability;
					$join[$i]['condition'] = "delivery_time_setting_availability_id = av_id";
					$join[$i]['type'] = "INNER";
					$i++;

					$join[$i]['select'] = "outlet_name";
					$join[$i]['table'] = $this->outlet_management;
					$join[$i]['condition'] = "delivery_time_setting_outlet_id = outlet_id";
					$join[$i]['type'] = "LEFT";

					$total_records = $this->Mydb->get_num_join_rows($this->primary_key, $this->table, $where, '', '', '', $like, array($this->primary_key), $join);


					$totalPages = (!empty($limit)) ? ceil($total_records / $limit) : 0;


					$result = $this->Mydb->get_all_records($select_array, $this->table, $where, $limit, $offset, $order_by, $like, array($this->primary_key), $join);

					if (!empty($result)) {
						$return_array = array('status' => "ok", 'message' => 'success', 'result' => $result, 'totalRecords' => $total_records, 'totalPages' => $totalPages);
						$this->set_response($return_array, success_response());
					} else {
						$this->set_response(array('status' => 'error', 'message' => get_label('no_records_found')), something_wrong());
					}
				} else {
					$this->set_response(array(
						'status' => 'error',
						'message' => get_label('invalid_user'),
						'form_error' => ''
					), something_wrong()); /* error message */
				}
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => get_label('token_verify_faild'),
					'form_error' => ''
				), something_wrong()); /* error message */
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => get_label('token_faild'),
				'form_error' => ''
			), something_wrong()); /* error message */
		}
	}


	public function details_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$id = decode_value($this->input->get('detail_id'));
				$company_id = decode_value($this->input->get('company_id'));
				$where = array($this->primary_key => $id, $this->company_id => $company_id);
				$result = $this->Mydb->get_record('*', $this->table, $where);
				if (!empty($result)) {


					$assign_availability = $this->Mydb->get_record('av_name AS label, av_id AS value', $this->availability, array('av_id' => $result['delivery_time_setting_availability_id']));
					$result['assign_availability'] = $assign_availability;

					$outlet = array();
					if (!empty($result['delivery_time_setting_outlet_id'])) {
						$outlet = $this->Mydb->get_all_records('outlet_id AS value, outlet_name AS label', $this->outlet_management, array('outlet_id' => $result['delivery_time_setting_outlet_id']));
					}
					$result['assign_outlet'] = (!empty($outlet)) ? $outlet[0] : '';

					$join = array();
					$i = 0;
					$join[$i]['select'] = "delivery_time_setting_time_pickup_slot_start_time AS from_time, delivery_time_setting_time_pickup_slot_end_time AS to_time, order_count_type, order_count";
					$join[$i]['table'] = $this->time_settings_time;
					$join[$i]['condition'] = "delivery_time_setting_day_id = delivery_time_setting_time_pickup_days_id";
					$join[$i]['type'] = "LEFT";
					$timeslot = $this->Mydb->get_all_records('delivery_time_setting_day_availablie_day AS avail_days', $this->time_settings_days, array('delivery_time_setting_day_time_setting_primary_id' => $result['delivery_time_setting_id']), null, null, null, null, null, $join);
					$result['timeslot'] = $timeslot;

					$datebasecount = $this->Mydb->get_all_records('available_date AS date, start_time AS from, end_time AS to, order_count', $this->time_order_count, array('delivery_time_setting_id' => $result['delivery_time_setting_id']));
					$result['datebasecount'] = $datebasecount;
					$return_array = array('status' => "ok", 'message' => 'success', 'result' => $result);
					$this->set_response($return_array, success_response());
				} else {
					$this->set_response(array('status' => 'error', 'message' => get_label('no_records_found')), something_wrong());
				}
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => get_label('token_verify_faild'),
					'form_error' => ''
				), something_wrong()); /* error message */
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => get_label('token_faild'),
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
				$this->form_validation->set_rules('timeslot', 'lang:available_days', 'required|callback_timeslot_checking');
				$this->form_validation->set_rules('assign_availability', 'lang:time_availability', 'required|callback_avilablity_outlet_timeslot_exists');
				$this->form_validation->set_rules('slot_type', 'lang:slot_type', 'required');
				if ($this->input->post('slot_type') == 1) {
					$this->form_validation->set_rules('interval_time', 'lang:interval_minutes', 'required');
				}
				$this->form_validation->set_rules('minimum_day', 'lang:minimum_date', 'required');
				$this->form_validation->set_rules('maximum_day', 'lang:maximum_date', 'required');
				$this->form_validation->set_rules('status', 'lang:status', 'required');
				if ($this->form_validation->run() == TRUE) {
					$this->addedit();
					$this->set_response(array(
						'status' => 'success',
						'message' => sprintf(get_label('success_message_add'), $this->label),
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
					'message' => get_label('token_verify_faild'),
					'form_error' => ''
				), something_wrong()); /* error message */
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => get_label('token_faild'),
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
				$this->form_validation->set_rules('timeslot', 'lang:available_days', 'required|callback_timeslot_checking');
				$this->form_validation->set_rules('assign_availability', 'lang:time_availability', 'required|callback_avilablity_outlet_timeslot_exists');
				$this->form_validation->set_rules('slot_type', 'lang:slot_type', 'required');
				if ($this->input->post('slot_type') == 1) {
					$this->form_validation->set_rules('interval_time', 'lang:interval_minutes', 'required');
				}
				$this->form_validation->set_rules('minimum_day', 'lang:minimum_date', 'required');
				$this->form_validation->set_rules('maximum_day', 'lang:maximum_date', 'required');
				$this->form_validation->set_rules('status', 'lang:status', 'required');
				if ($this->form_validation->run() == TRUE) {
					$edit_id = $this->input->post('edit_id');
					$this->addedit($edit_id);
					$this->set_response(array(
						'status' => 'success',
						'message' => sprintf(get_label('success_message_edit'), $this->label),
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
					'message' => get_label('token_verify_faild'),
					'form_error' => ''
				), something_wrong()); /* error message */
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => get_label('token_faild'),
				'form_error' => ''
			), something_wrong()); /* error message */
		}
	}

	public function delete_post()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {
				$company_id = decode_value($this->input->post('company_id'));
				$delete_id = decode_value($this->input->post('delete_id'));
				$company_admin_id = decode_value($this->input->post('company_admin_id'));
				if (!empty($company_id)) {
					$get_company_details = $this->Mydb->get_record('company_unquie_id', 'company', array('company_id' => $company_id));
					$where = array(
						$this->primary_key => trim($delete_id)
					);
					$result = $this->Mydb->get_record($this->primary_key . ', delivery_time_setting_availability_id, delivery_time_setting_outlet_id', $this->table, $where);
					if (!empty($result)) {
						$this->Mydb->delete($this->table, array($this->primary_key => $result[$this->primary_key]));
						$this->Mydb->delete($this->time_order_count, array('delivery_time_setting_id' => $result[$this->primary_key]));
						$this->Mydb->delete($this->time_settings_days, array('delivery_time_setting_day_time_setting_primary_id' => $result[$this->primary_key]));
						$this->Mydb->delete($this->time_settings_time, array('delivery_time_setting_time_time_setting_primary_id' => $result[$this->primary_key]));

						$avail = $this->Mydb->get_record('av_name', 'availability', array('av_id' => $result['delivery_time_setting_availability_id']));
						if (!empty($result['delivery_time_setting_outlet_id'])) {
							$outletDetails  = $this->Mydb->get_record('outlet_name', 'outlet_management', array('outlet_id' => $result['delivery_time_setting_outlet_id']));
							$outlentName = $outletDetails['outlet_name'];
						} else {
							$outlentName = "Common";
						}

						$name = $outlentName . ' - ' . $avail['av_name'];

						createAuditLog("Time Slot", $name, "Delete", $company_admin_id, 'Web', '', $company_id, $get_company_details['company_unquie_id']);

						$return_array = array('status' => "ok", 'message' => sprintf(get_label('success_message_delete'), $this->label));
						$this->set_response($return_array, success_response());
					} else {
						$this->set_response(array('status' => 'error', 'message' => get_label('no_records_found'), 'form_error' => ''), something_wrong());
					}
				} else {
					$this->set_response(array('status' => 'error', 'message' => sprintf(get_label('field_required'), 'Company ID'), 'form_error' => ''), something_wrong());
				}
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => get_label('token_verify_faild'),
					'form_error' => ''
				), something_wrong()); /* error message */
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => get_label('token_faild'),
				'form_error' => ''
			), something_wrong()); /* error message */
		}
	}

	public function addedit($edit_id = null)
	{

		if (!empty($edit_id)) {
			$edit_id = decode_value($edit_id);
		}
		$action = post_value('action');
		$slot_type = post_value('slot_type');

		$data = array(
			'delivery_time_setting_availability_id' => post_value('assign_availability'),
			'delivery_time_setting_outlet_id' =>  post_value('assign_outlet'),
			'delivery_time_slot_type'	=>  $slot_type,
			'delivery_time_setting_cutt_off' => post_value('cut_of_time'),
			'delivery_time_setting_interval_time' => ($slot_type == '1') ? post_value('interval_time') : '',
			'delivery_time_setting_minimum_date' => post_value('minimum_day'),
			'delivery_time_setting_maximum_date' => post_value('maximum_day'),
			'delivery_time_setting_status' => ($this->input->post('status') == "A" ? 'A' : 'I'),

		);

		$company_id = decode_value($this->input->post('company_id'));
		$company_admin_id = decode_value($this->input->post('company_admin_id'));
		$getCompanyDetails = getCompanyUniqueID($company_id);

		$avail = $this->Mydb->get_record('av_name', 'availability', array('av_id' => post_value('assign_availability')));
		if (!empty(post_value('assign_outlet'))) {
			$outletDetails  = $this->Mydb->get_record('outlet_name', 'outlet_management', array('outlet_id' => post_value('assign_outlet')));
			$outlentName = $outletDetails['outlet_name'];
		} else {
			$outlentName = "Common";
		}

		$name = $outlentName . ' - ' . $avail['av_name'];

		if ($action == 'add') {
			$data = array_merge(
				$data,
				array(
					'delivery_time_setting_company_id' => $company_id,
					'delivery_time_setting_company_unquie_id' => (!empty($getCompanyDetails)) ? $getCompanyDetails : '',
					'delivery_time_setting_created_on' => current_date(),
					'delivery_time_setting_created_by' => $company_admin_id,
					'delivery_time_setting_created_ip' => get_ip()
				)
			);
			$edit_id = $this->Mydb->insert($this->table, $data);
			createAuditLog("Time Slot", stripslashes($name), "Add", $company_admin_id, 'Web', '', $company_id, $getCompanyDetails);
		} else {
			$data = array_merge(
				$data,
				array(
					'delivery_time_setting_updated_on' => current_date(),
					'delivery_time_setting_updated_by' => $company_admin_id,
					'delivery_time_setting_updated_ip' => get_ip()
				)
			);
			$this->Mydb->update($this->table, array($this->primary_key => $edit_id), $data);
			createAuditLog("Time Slot", stripslashes($name), "Update", $company_admin_id, 'Web', '', $company_id, $getCompanyDetails);
		}

		if (!empty($edit_id)) {
			if ($action == 'edit') {
				$this->Mydb->delete($this->time_order_count, array('delivery_time_setting_id' => $edit_id));
				$this->Mydb->delete($this->time_settings_days, array('delivery_time_setting_day_time_setting_primary_id' => $edit_id));
				$this->Mydb->delete($this->time_settings_time, array('delivery_time_setting_time_time_setting_primary_id' => $edit_id));
			}

			$timeslot = ($this->post('timeslot') != "" ? json_decode($this->post('timeslot')) : array());
			$timeslot = (!empty($timeslot)) ? $this->object_to_array($timeslot) : array();
			if (!empty($timeslot)) {
				foreach ($timeslot as $val) {
					$dayArray = array(
						'delivery_time_setting_day_time_setting_primary_id' => $edit_id,
						'delivery_time_setting_day_availablie_day' => $val['day'],
					);
					$dayID = $this->Mydb->insert($this->time_settings_days, $dayArray);

					$slot_form = (!empty($val['from'])) ? date('H:i:s', strtotime($val['from'])) : '';
					$slot_to = (!empty($val['to'])) ? date('H:i:s', strtotime($val['to'])) : '';
					if (!empty($slot_form) && !empty($slot_to)) {
						$timeArray = array(
							'delivery_time_setting_time_time_setting_primary_id' => $edit_id,
							'delivery_time_setting_time_pickup_slot_start_time' => $slot_form,
							'delivery_time_setting_time_pickup_slot_end_time' => $slot_to,
							'delivery_time_setting_time_pickup_days_id' => $dayID,
							'order_count_type' => (!empty($val['order_count_type'])) ? $val['order_count_type'] : '',
							'order_count' => (!empty($val['order_count'])) ? $val['order_count'] : '',
						);
						$this->Mydb->insert($this->time_settings_time, $timeArray);
					}
				}
			}

			$datebasecount = ($this->post('datebasecount') != "" ? json_decode($this->post('datebasecount')) : array());
			$datebasecount = (!empty($datebasecount)) ? $this->object_to_array($datebasecount) : array();
			if (!empty($datebasecount)) {
				foreach ($datebasecount as $val) {
					$countArray = array(
						'delivery_time_setting_id' => $edit_id,
						'available_date' => (!empty($val['date'])) ? date('Y-m-d', strtotime($val['date'])) : "",
						'start_time' => (!empty($val['from'])) ? date('H:i:s', strtotime($val['from'])) : "",
						'end_time' => (!empty($val['to'])) ? date('H:i:s', strtotime($val['to'])) : "",
						'order_count' => (!empty($val['order_count'])) ? $val['order_count'] : '',
					);
					$this->Mydb->insert($this->time_order_count, $countArray);
				}
			}
		}
	}

	public function slotDetails_get()
	{
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			if ($decodedToken['status']) {

				$company_id = decode_value($this->input->get('company_id'));
				$company = client_validation($company_id); /* validate company */
				$unique_id = $company['company_unquie_id'];

				$datelist_arr = array();
				$final_output = array();
				$common_output = array();
				$current_day = date('Y-m-d');

				$availability_id = $this->get('availability_id');
				$outletId = $this->get('outletId');
				$tatTime = $this->get('tatTime');

				$min_date_limit = 0;
				$max_date_limit = 60;
				$holidayresult = array();
				$interval_time = 5;
				$tat_time = ($tatTime != '') ? $tatTime : 0;
				$time_data = array();

				$timeslots_data = $this->deteFromOperationalTimeslotsAdvanced($company, $outletId, $availability_id);

				if (!empty($timeslots_data)) {

					/** Operational Timeslots Data format Making - Start **/
					$result_setarray = $timeslots_data['result_set'];
					$holidayresult = $timeslots_data['holidayresult'];
					$interval_time = (int)$result_setarray[0]['interval_time'];
					$cut_off = $result_setarray[0]['cut_off'];
					$minimum_date = $result_setarray[0]['minimum_date'];
					$maximum_date = $result_setarray[0]['maximum_date'];
					$main_slot = $result_setarray[0]['slot'];

					$min_date_limit = (!empty($minimum_date)) ? $minimum_date : 0;
					$max_date_limit = (!empty($maximum_date)) ? $maximum_date : 60;

					$defaultdays = array('sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat');

					$currentslot_str_data = $currentslot_end_data = $currentdayslot_data = $naxtdayslot_data = $its_spl_data = $weekDays = array();

					foreach ($defaultdays as $dayval) {

						if (array_key_exists($dayval, $main_slot)) {
							$temp_dayarr = $main_slot[$dayval];
							$its_spl = 'no';

							$first_slot = reset($temp_dayarr);
							$end_slot = end($temp_dayarr);
							if ($first_slot['slot_time1'] == '00:00' && $end_slot['slot_time2'] == '00:00') {
								$its_spl = 'yes';
							}

							$its_spl_data[$dayval] = $its_spl;
							$t = 0;
							$lst_rcd = count($temp_dayarr) - 1;
							foreach ($temp_dayarr as $day_arr) {

								$slot_time1 = ($its_spl == 'yes' && $t == 0) ? $day_arr['slot_time1'] : $this->set_timewithTAT($tat_time, $day_arr['slot_time1'], 'str');
								$slot_time2 = ($its_spl == 'yes' && $t == $lst_rcd) ? '24:00' : $this->set_timewithTAT($tat_time, $day_arr['slot_time2'], 'end');

								$currentslot_str_data[$dayval][] = $day_arr['slot_time1'];
								$currentslot_end_data[$dayval][] = $slot_time2;

								if ($its_spl == 'yes' && $t == $lst_rcd) {
									$slot_time2_wthouttat = '24:00';
								} else {
									$slot_time2_wthouttat = ($day_arr['slot_time2'] == '00:00') ? '24:00' : $day_arr['slot_time2'];
								}
								if ($interval_time > 0) {
									$range = range(strtotime($day_arr['slot_time1']), strtotime($slot_time2_wthouttat), $interval_time * 60);
									$r = 0;
									foreach ($range as $timetxt) {
										if ((date("H:i", $timetxt) != '00:00') || ($r == 0)) {
											$currentdayslot_data[$dayval][] = date("H:i", $timetxt);
										}
										$r++;
									}
									$rangenew = range(strtotime($slot_time1), strtotime($slot_time2), $interval_time * 60);
								}

								$n = 0;
								foreach ($rangenew as $timetxt1) {
									if ((date("H:i", $timetxt1) != '00:00') || ($n == 0)) {
										$naxtdayslot_data[$dayval][] = date("H:i", $timetxt1);
									}
									$n++;
								}

								$t++;
							}
						} else {
							$currentdayslot_data[$dayval] = array();
							$naxtdayslot_data[$dayval] = array();
							$its_spl_data[$dayval] = 'no';
							$currentslot_str_data[$dayval] = array();
							$currentslot_end_data[$dayval] = array();
							$weekDays[] = array_search($dayval, $defaultdays);
						}
					}

					$time_data['currentslot_str_data'] = $currentslot_str_data;
					$time_data['currentslot_end_data'] = $currentslot_end_data;
					$time_data['currentdayslot_data'] = $currentdayslot_data;
					$time_data['naxtdayslot_data'] = $naxtdayslot_data;
					$time_data['its_spl_data'] = $its_spl_data;
					$time_data['weekDays_data'] = $weekDays;

					/** Operational Timeslots Data - End **/


					if (!empty($time_data)) {
						$this->set_response(array(
							'status' => "success",
							'result_set' => $time_data,
							'timeslot_data' => $timeslots_data
						), success_response());
					} else {
						$this->set_response(array(
							'status' => "error",
							'message' => "Did not have Available Dates for this Outlet.",
							'result_set' => '',
						), notfound_response());
					}
				} else {

					$this->set_response(array(
						'status' => "error",
						'message' => "Operational Timeslots not have Available Dates for this Outlet.",
						'result_set' => '',
					), notfound_response());
				}
			} else {
				$this->set_response(array(
					'status' => 'error',
					'message' => get_label('token_verify_faild'),
					'form_error' => ''
				), something_wrong()); /* error message */
			}
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => get_label('token_faild'),
				'form_error' => ''
			), something_wrong()); /* error message */
		}
	}

	private function deteFromOperationalTimeslotsAdvanced($company, $outlet_id, $availability)
	{

		$unquie_id =  $company['company_unquie_id'];
		$output = array();
		$holidayRestArr = array();


		if ($company['client_holiday_enable'] == 1) {

			$holidayresult = $this->Mydb->get_all_records(array('holiday_date'), 'holidays', array('holiday_company_unquie_id' => $unquie_id, 'holiday_status' => 'A', "holiday_date >=" => date('Y-m-d')), null, null, null, '', '', '', '');

			$holidayArr = (!empty($holidayresult)) ? array_column($holidayresult, 'holiday_date') : array();
			$holidayRestArr = array_unique($holidayArr);
		}


		$where = array('delivery_time_setting_company_unquie_id' => $unquie_id, 'delivery_time_setting_status' => 'A', 'delivery_time_setting_availability_id' => $availability);

		$join = array();
		$join[0]['select'] = "dts.*";
		$join[0]['table'] = $this->table . ' AS dts';
		$join[0]['condition'] = "pos_tsd.delivery_time_setting_day_time_setting_primary_id = dts.delivery_time_setting_id";
		$join[0]['type'] = "LEFT";
		$join[1]['select'] = "at.*";
		$join[1]['table'] = $this->time_settings_time . ' AS at';
		$join[1]['condition'] = "at.delivery_time_setting_time_time_setting_primary_id  = dts.delivery_time_setting_id and  at.delivery_time_setting_time_pickup_days_id = pos_tsd.delivery_time_setting_day_id";
		$join[1]['type'] = "LEFT";

		$group_by = "delivery_time_setting_day_id";
		$order_by = array(
			'delivery_time_setting_time_pickup_slot_start_time' => 'ASC'
		);

		/* Added outlet condition */
		if (!empty($outlet_id)) {
			$where = array_merge($where, array("delivery_time_setting_outlet_id" => $outlet_id));
		} else {
			$where = array_merge($where, array("(delivery_time_setting_outlet_id IS NULL OR delivery_time_setting_outlet_id='0')" => null));
		}


		$result = $this->Mydb->get_all_records('pos_tsd.*', $this->time_settings_days . ' AS pos_tsd', $where, '', '', $order_by, '', $group_by, $join);

		if (!empty($result)) {

			$defalut_values['interval_time'] = $result[0]['delivery_time_setting_interval_time'];
			$defalut_values['cut_off'] = ($result[0]['delivery_time_setting_cutt_off'] == '00:00:00') ? '' : date('H', strtotime($result[0]['delivery_time_setting_cutt_off']));
			$defalut_values['minimum_date'] = $result[0]['delivery_time_setting_minimum_date'];
			$defalut_values['maximum_date'] = $result[0]['delivery_time_setting_maximum_date'];
			$defalut_values['time_slot_type'] = $result[0]['delivery_time_slot_type'];

			$defaultdays = array('mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun');
			$days_result = array();
			foreach ($result as $row) {
				$availablie_day = (!empty($row['delivery_time_setting_day_availablie_day'])) ? explode(',', $row['delivery_time_setting_day_availablie_day']) : array();
				foreach ($defaultdays as $dayval) {
					if (in_array($dayval, $availablie_day)) {
						$slot_array = array('slot_time1' => date('H:i', strtotime($row['delivery_time_setting_time_pickup_slot_start_time'])), 'slot_time2' => date('H:i', strtotime($row['delivery_time_setting_time_pickup_slot_end_time'])));
						$days_result[$dayval][] = $slot_array;
					}
				}
			}

			$defalut_values['slot'] = $days_result;
			$output[] = $defalut_values;
			$return_array = array('result_set' => $output, 'holidayresult' => $holidayRestArr);
			return $return_array;
		} else {
			return $output;
		}
	}


	private function set_timewithTAT($tat_time, $slottime, $strtxt)
	{
		$slottime_arr = explode(':', $slottime);
		$datetime = new DateTime();
		$datetime->setTime($slottime_arr[0], $slottime_arr[1], '00');
		$befor_tat = $datetime->format('H:i');
		$incr_txt = 'P0DT0H' . $tat_time . 'M0S';
		$datetime->add(new DateInterval($incr_txt));
		$hr_minds = $datetime->format('H:i');

		if (($strtxt == 'end') && (str_replace(':', '', $befor_tat) > str_replace(':', '', $hr_minds))) {
			$hr_minds = '24:00';
		}

		return $hr_minds;
	}


	public function timeslot_checking()
	{
		$timeslot = ($this->post('timeslot') != "" ? json_decode($this->post('timeslot')) : array());
		$timeslot = (!empty($timeslot)) ? $this->object_to_array($timeslot) : array();
		$notavailslot = 0;
		if (!empty($timeslot)) {
			foreach ($timeslot as $val) {
				if (empty($val['day']) || empty($val['from']) || empty($val['to'])) {
					$notavailslot++;
				}
			}
		}
		if ($notavailslot > 0) {
			$this->form_validation->set_message('timeslot_checking', get_label('timeslot_checking'));
			return false;
		} else {
			return true;
		}
	}


	public function avilablity_outlet_timeslot_exists()
	{
		$availability_id = $this->input->post('assign_availability');
		$outlet_id = $this->input->post('assign_outlet');
		$company_id = decode_value($this->input->post('company_id'));
		$edit_id = $this->input->post('edit_id');

		$where = array(
			'delivery_time_setting_availability_id' => trim($availability_id),
			'delivery_time_setting_company_id' => $company_id
		);

		if (!empty($outlet_id)) {
			$where = array_merge($where, array(
				'delivery_time_setting_outlet_id' => trim($outlet_id)
			));
		}

		if (!empty($edit_id)) {
			$edit_id = decode_value($edit_id);
			$where = array_merge($where, array(
				$this->primary_key . " !=" => $edit_id,
			));
		}

		$result = $this->Mydb->get_record($this->primary_key, $this->table, $where);
		if (!empty($result)) {
			$this->form_validation->set_message('avilablity_outlet_timeslot', get_label('avilablity_outlet_timeslot'));
			return false;
		} else {
			return true;
		}
	}

	private function object_to_array($data)
	{
		if (is_array($data) || is_object($data)) {
			$result = array();
			foreach ($data as $key => $value) {
				$result[$key] = $this->object_to_array($value);
			}
			return $result;
		}
		return $data;
	}



	public function action_post()
	{
		$ids = ($this->input->post('multiaction') == 'Yes' ? $this->input->post('id') : decode_value($this->input->post('changeId')));
		$postaction = $this->input->post('postaction');
		$company_id = decode_value($this->input->post('company_id'));
		$company_admin_id = decode_value($this->input->post('company_admin_id'));
		$get_company_details = $this->Mydb->get_record('company_unquie_id', 'company', array('company_id' => $company_id));
		$company_unquie_id =  $get_company_details['company_unquie_id'];
		$response = array(
			'status' => 'error',
			'msg' => get_label('something_wrong'),
			'action' => '',
			'form_error' => '',
			'multiaction' => $this->input->post('multiaction')
		);

		/* Delete */
		$wherearray = array('menu_company_id' => $company_id, 'menu_unquie_id' => $company_unquie_id);
		if ($postaction == 'Delete' && !empty($ids)) {
			$this->audit_action($ids, $postaction);
			// $this->Mydb->delete_where_in($this->table,'client_id',$ids,'');
			if (is_array($ids)) {
				$this->Mydb->delete_where_in($this->table, $this->primary_key, $ids, $wherearray);
				$response['msg'] = sprintf(get_label('success_message_delete'), $this->module_label);
			} else {
				$this->Mydb->delete($this->table, array($this->primary_key => $ids, 'menu_company_id' => $company_id, 'menu_unquie_id' => $company_unquie_id));
				$response['msg'] = sprintf(get_label('success_message_delete'), $this->module_label);
			}
			$response['status'] = 'success';
			$response['action'] = $postaction;
		}

		$where_array = array('menu_company_id' => $company_id, 'menu_unquie_id' => $company_unquie_id);
		/* Activation */
		if ($postaction == 'Activate' && !empty($ids)) {
			$update_values = array(
				"menu_status" => 'A',
				"menu_updated_on" => current_date(),
				'menu_updated_by' => $company_admin_id,
				'menu_updated_ip' => get_ip()
			);

			if (is_array($ids)) {
				$this->Mydb->update_where_in($this->table, $this->primary_key, $ids, $update_values, $where_array);
				$response['msg'] = sprintf(get_label('success_message_activate'), $this->module_labels);
			} else {

				$this->Mydb->update_where_in($this->table, $this->primary_key, array(
					$ids
				), $update_values, $where_array);
				$response['msg'] = sprintf(get_label('success_message_activate'), $this->module_label);
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
				"menu_status" => 'I',
				"menu_updated_on" => current_date(),
				'menu_updated_by' => $company_admin_id,
				'menu_updated_ip' => get_ip()
			);

			if (is_array($ids)) {
				$this->Mydb->update_where_in($this->table, $this->primary_key, $ids, $update_values, $where_array);
				$response['msg'] = sprintf(get_label('success_message_deactivate'), $this->module_labels);
			} else {
				$this->Mydb->update_where_in($this->table, $this->primary_key, array(
					$ids
				), $update_values, $where_array);
				$response['msg'] = sprintf(get_label('success_message_deactivate'), $this->module_label);
			}
			$response['status'] = 'success';
			$response['action'] = $postaction;
		}

		$this->set_response($response, success_response()); /* success message */
	}
} /* end of files */
