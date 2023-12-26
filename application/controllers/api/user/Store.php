<?php

/**************************
Project Name	: White Label
Created on		: 04 April, 2023
Last Modified 	: 27 April, 2016
Description		: Store related functions

 ***************************/
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Store extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->outlet_tags = "outlet_tags";
		$this->outlet_management = "outlet_management";
		$this->site_location = "site_location";
		$this->zone = "outlet_zone_management";
		$this->favourite = "outlet_favourite";
		$this->load->library('form_validation');
	}
	public function tagList_get()
	{

		$company = app_validation($this->get('unquieid')); /* validate app */
		$company_id = $company['company_id'];
		$tagList = $this->Mydb->get_all_records('tag_primary_id AS value, tag_name AS label', $this->outlet_tags, array(
			'tag_company_id' => $company_id,
			'tag_status' => 'A'
		));
		if (!empty($tagList)) {
			$return_array = array(
				'status' => "ok",
				'result' => $tagList
			);
			$this->set_response($return_array, success_response());
		} else {
			echo json_encode(array(
				'status' => 'error',
				'message' => get_label('no_records_found')
			));
			exit();
		}
	}
	public function listStore_get()
	{

		$company = app_validation($this->get('unquieid')); /* validate app */
		$company_id = $company['company_id'];
		$LocationLID = decode_value($this->get('dellocation'));
		$latitude = $this->get('latitude');
		$longitude = $this->get('longitude');

		$favourite = post_value('favourite');
		$where = array(
			'outlet_company_id' => $company_id,
			'outlet_availability' => '1'
		);
		if (!empty($LocationLID)) {
			$where = array_merge($where, array('outlet_location_id' => $LocationLID));
		}
		$join = array();
		if (!empty($favourite) && !empty(post_value('customerID'))) {
			$customerID = decode_value(post_value('customerID'));
			$join = array();
			$i = 0;
			$join[$i]['select']    = "";
			$join[$i]['condition'] = "outlet_id = fav_outlet_id";
			$join[$i]['table']     = $this->favourite;
			$join[$i]['type']      = "INNER";
			$where = array_merge($where, array('fav_customer_id' => $customerID));
		}
		$outlet = $this->Mydb->get_all_records('outlet_id AS storeID, outlet_name As storeName, outlet_slug AS storeSlug, outlet_image AS storeImage, outlet_time_info AS storeTimeInfo, outlet_tag_id AS tagID, outlet_offer_info AS offerInfo, 111.111 *
		DEGREES(ACOS(LEAST(1.0, COS(RADIANS(' . $latitude . '))
			 * COS(RADIANS(outlet_marker_latitude))
			 * COS(RADIANS(' . $longitude . ' - outlet_marker_longitude))
			 + SIN(RADIANS(' . $latitude . '))
			 * SIN(RADIANS(outlet_marker_latitude))))) AS distance, outlet_rating AS Rating, outlet_total_rating AS totalRating', $this->outlet_management, $where, '', '', '', '', '', $join);
		if (!empty($outlet)) {
			$return_array = array(
				'status' => "ok",
				'result' => $outlet
			);
			$this->set_response($return_array, success_response());
		} else {
			echo json_encode(array(
				'status' => 'error',
				'message' => get_label('no_records_found')
			));
			exit();
		}
	}
	public function storeDetails_get()
	{
		$unquieid = $this->get('unquieid');
		$company = app_validation($unquieid); /* validate app */
		$company_id = $company['company_id'];
		$LocationLID = $this->get('dellocation');
		$storeID = $this->get('storeID');
		$storeSlug = $this->get('storeSlug');
		$latitude = $this->get('latitude');
		$longitude = $this->get('longitude');
		$where = array(
			'outlet_company_id' => $company_id,
			'outlet_availability' => '1'
		);
		if (!empty($storeID)) {
			$where = array_merge($where, array('outlet_id' => $storeID));
		}
		if (!empty($storeSlug)) {
			$where = array_merge($where, array('outlet_slug' => $storeSlug));
		}
		if (!empty($LocationLID)) {
			$where = array_merge($where, array('outlet_location_id' => $LocationLID));
		}
		$outlet = $this->Mydb->get_record('outlet_id AS storeID, outlet_name As storeName, outlet_slug AS storeSlug, outlet_image AS storeImage, outlet_banner_image AS bannerImage, outlet_time_info AS storeTimeInfo, , outlet_tag_id AS tagID, outlet_offer_info AS offerInfo, 111.111 *
		DEGREES(ACOS(LEAST(1.0, COS(RADIANS(' . $latitude . '))
			 * COS(RADIANS(outlet_marker_latitude))
			 * COS(RADIANS(' . $longitude . ' - outlet_marker_longitude))
			 + SIN(RADIANS(' . $latitude . '))
			 * SIN(RADIANS(outlet_marker_latitude))))) AS distance, outlet_rating AS Rating, outlet_total_rating AS totalRating', $this->outlet_management, $where);
		if (!empty($outlet)) {
			$outlet['favourite'] = 'No';
			if (post_value('customerID') !== "") {
				$customerID = decode_value(post_value('customerID'));
				$favourite = $this->Mydb->get_record('', $this->favourite, array(
					'fav_customer_id' => $customerID,
					'fav_company_unquie_id' => $unquieid,
					'fav_outlet_id' => $outlet['storeID']
				));
				$outlet['favourite'] = (!empty($favourite)) ? 'Yes' : 'No';
			}


			$outlet['tagName'] = "";
			if (!empty($outlet['tagID'])) {
				$tagWhere = "tag_primary_id IN (" . $outlet['tagID'] . ") AND tag_status='A'";
				$tagList = $this->Mydb->get_all_records('tag_name AS tagName', $this->outlet_tags, $tagWhere);
				$outlet['tagName'] = (!empty($tagList)) ? implode(', ', array_column($tagList, 'tagName')) : '';
			}
			$return_array = array(
				'status' => "ok",
				'result' => $outlet
			);
			$this->set_response($return_array, success_response());
		} else {
			echo json_encode(array(
				'status' => 'error',
				'message' => get_label('no_records_found')
			));
			exit();
		}
	}
	public function listDelLocation_get()
	{

		$company = app_validation($this->get('unquieid')); /* validate app */
		$company_id = $company['company_id'];
		$latitude = $this->get('latitude');
		$longitude = $this->get('longitude');
		$where = array(
			'sl_company_id' => $company_id,
			'sl_status' => 'A'
		);
		$outlet = $this->Mydb->get_all_records('sl_location_id AS locationID, sl_name AS locationName, sl_slug AS slug, sl_image AS image, sl_pickup_address_line1 AS address, 111.111 *
		DEGREES(ACOS(LEAST(1.0, COS(RADIANS(' . $latitude . '))
			 * COS(RADIANS(sl_latitude))
			 * COS(RADIANS(' . $longitude . ' - sl_longitude))
			 + SIN(RADIANS(' . $latitude . '))
			 * SIN(RADIANS(sl_latitude))))) AS distance', $this->site_location, $where);
		if (!empty($outlet)) {
			$return_array = array(
				'status' => "ok",
				'result' => $outlet
			);
			$this->set_response($return_array, success_response());
		} else {
			echo json_encode(array(
				'status' => 'error',
				'message' => get_label('no_records_found')
			));
			exit();
		}
	}
	public function findDeliveryZone_get()
	{
		$company = app_validation($this->get('unquieid')); /* validate app */
		$company_id = $company['company_id'];
		$latitude = $this->get('latitude');
		$longitude = $this->get('longitude');
		$locationID = $this->get('locationID');
		$avilability = $this->get('avilability');
		$join = array();
		$i = 0;
		$join[$i]['select']    = "outlet_zone_availability.oza_outlet_zone_id,outlet_zone_availability.oza_outlet_id,outlet_zone_availability.oza_availability_id";
		$join[$i]['condition'] = "outlet_zone_availability.oza_outlet_zone_id = outlet_zone_management.zone_id";
		$join[$i]['table']     = "outlet_zone_availability";
		$join[$i]['type']      = "INNER";
		$i++;
		$join[$i]['select']    = " coverage.oa_region_marker_location, coverage.oa_outlet_id, coverage.oa_region_type_primary, 
								coverage.oa_region_points_primary,  coverage.oa_region_radius_primary , coverage.oa_region_marker_location";
		$join[$i]['condition'] = "coverage.oa_outlet_zone_id = outlet_zone_management.zone_id";
		$join[$i]['table']     = "outlet_zone_area_coverage as coverage";
		$join[$i]['type']      = "INNER";

		$zoneWhere =  array(
			'zone_company_id' => $company_id,
			'zone_site_location_id' => $locationID,
			'oza_availability_id' => $avilability
		);


		$zoneDetails = $this->Mydb->get_all_records('outlet_zone_management.zone_id AS delZoneID,outlet_zone_management.zone_outlet_id,outlet_zone_management.zone_name AS delZoneName', $this->zone, $zoneWhere, '', '', '', '', '', $join);
		if (!empty($zoneDetails)) {
			foreach ($zoneDetails as $out) {
				$region_points = $out['oa_region_points_primary'];
				$region_type   = $out['oa_region_type_primary'];
				$region_radius = $out['oa_region_radius_primary'];
				if ($region_points != '') {
					$reg_points = explode('|', $region_points);
					/* for circle */
					if ($region_type == 'circle') {
						$distance = $this->getDistance($reg_points[0], $reg_points[1], $latitude, $longitude);
						if ($distance <= $region_radius) {
							$output[$out['delZoneID']] = array(
								'delZoneID' => $out['delZoneID'],
								'delZoneName' => $out['delZoneName'],
							);
						}
					} else if ($region_type == 'rectangle') {
						$points_lngs = $points_lats = array();
						foreach ($reg_points as $reg_pts) {
							$rects         = explode(',', $reg_pts);
							$points_lats[] = $rects[0];
							$points_lngs[] = $rects[1];
						}
						if ($this->is_rectangle($points_lats, $points_lngs, $latitude, $longitude)) {
							$output[$out['delZoneID']] = array(
								'delZoneID' => $out['delZoneID'],
								'delZoneName' => $out['delZoneName'],
							);
						}
					} elseif ($region_type == 'polygon') {
						require_once 'PointLocation.php';
						$pointLocation      = new PointLocation();
						$region_str_explode = explode('|', $region_points);

						$polygon            = array();
						if (!empty($region_str_explode)) {
							$lat_values  = explode(",", $region_str_explode[0]);
							$lang_values = explode(",", $region_str_explode[1]);
							$m           = 0;
							foreach ($lat_values as $lat) {
								$lat     = $lat_values[$m];
								$lang    = $lang_values[$m];
								$cmp_str = $lat . "" . $lang;
								if ($m == 0) {
									$first_lat = $cmp_str;
								}
								array_push($polygon, $cmp_str);
								$lat_count = count($lat_values) - 1;
								if ($lat_count == $m) {
									array_push($polygon, $first_lat);
								}
								$m++;
							}
						}
						$points = array(
							$latitude . " " . $longitude
						);
						foreach ($points as $key => $point) {
							$result_text = $pointLocation->pointInPolygon($point, $polygon);

							// exit;
							if ($result_text == 'inside') {
								$output[$out['delZoneID']] = array(
									'delZoneID' => $out['delZoneID'],
									'delZoneName' => $out['delZoneName'],
								);
							}
						}
					}
				}
			}

			/* if get mutiple records filter by near one */
			if (empty($output)) {
				// rest_no_outlets_found ****response
			} else {
				$lat_val  = $lang_val = "";
				$distance = array();
				if (count($output) > 1) {
					foreach ($output as $outval) {
						if ($outval['posatl_code_lat_lang'] != "") {
							$marker_val = $outval['posatl_code_lat_lang'];
							$replaced   = str_replace(array(
								'(',
								')'
							), array(
								'',
								''
							), $marker_val);
							list($lat_val, $lang_val) = explode(',', $replaced);
							$distance[$outval['delZoneID']] = $this->getDistance($lat_val, $lang_val, $latitude, $longitude);
						} else {
							// rest_no_outlets_found ****response
						}
					}
					$minimum_distance = array_keys($distance, min($distance));
				} else {
					$minimum_distance = array_column($output, 'delZoneID');
				}
			}
		} else {
			// rest_no_outlets_found ****response
		}
		/* if set final result */
		if (!empty($minimum_distance)) {
			/* get postal code details */
			$outlet_id = $minimum_distance[0];
			/*--------------------------------------------*/
			$outlet_deatlls = $output[$outlet_id];
			$return_array                              = array(
				'status' => "ok",
				'result' => $outlet_deatlls
			);
			$this->set_response($return_array, success_response());
		} else {
			if ($outlet_shop_close_check == 1) {
				$this->set_response(array(
					'status' => "error",
					'shop_close' => "Yes",
					'message' => get_label('rest_no_outlets_found')
				), notfound_response());
			} else {
				$this->set_response(array(
					'status' => "error",
					'shop_close' => "No",
					'message' => get_label('rest_no_outlets_found')
				), notfound_response());
			}
		}
	}
	public function getTotalFavourite_get()
	{
		$unquieid = $this->get('unquieid');
		app_validation($this->get('unquieid')); /* validate app */
		$totalFav = 0;
		if (!empty(post_value('customerID'))) {
			$customerID = decode_value(post_value('customerID'));
			$totalFav = $this->Mydb->get_num_rows('fav_id', $this->favourite, array('fav_customer_id' => $customerID, 'fav_company_unquie_id' => $unquieid));
		}
		$this->set_response(array('totalfavourite' => $totalFav), success_response());
	}
	public function addFavourite_post()
	{
		$unquieid = post_value('unquieid');
		app_validation(post_value('unquieid'));
		$this->form_validation->set_rules('shopID', 'lang:bp_rider_outlet_req', 'required');

		$this->form_validation->set_rules('customerID', 'lang:rest_customer_id', 'required');
		if ($this->form_validation->run() == TRUE) {
			$shopID = decode_value(post_value('shopID'));
			$customerID = decode_value(post_value('customerID'));
			$data = array(
				'fav_outlet_id' => $shopID,
				'fav_customer_id' => $customerID,
				'fav_company_unquie_id' => $unquieid,
				'fav_created_on' => current_date(),
			);
			$this->Mydb->insert($this->favourite, $data);
			$this->set_response(array(
				'status' => 'success',
				'message' => get_label('rest_fav_product_success'),
				'form_error' => '',
			), success_response()); /* success message */
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => get_label('rest_form_error'),
				'form_error' => validation_errors()
			), something_wrong()); /* error message */
		}
	}
	public function removeFavourite_post()
	{
		$unquieid = post_value('unquieid');
		$this->form_validation->set_rules('shopID', 'lang:bp_rider_outlet_req', 'required');

		$this->form_validation->set_rules('customerID', 'lang:rest_customer_id', 'required');
		if ($this->form_validation->run() == TRUE) {
			$shopID = decode_value(post_value('shopID'));
			$customerID = decode_value(post_value('customerID'));
			$data = array(
				'fav_outlet_id' => $shopID,
				'fav_customer_id' => $customerID,
				'fav_company_unquie_id' => $unquieid
			);
			$this->Mydb->delete($this->favourite, $data);
			$this->set_response(array(
				'status' => 'success',
				'message' => get_label('rest_fav_remove_product_success'),
				'form_error' => '',
			), success_response()); /* success message */
		} else {
			$this->set_response(array(
				'status' => 'error',
				'message' => get_label('rest_form_error'),
				'form_error' => validation_errors()
			), something_wrong()); /* error message */
		}
	}


	/* To get the distance of a point from the center of the circle in the outlet */
	private function getDistance($latitude1, $longitude1, $latitude2, $longitude2)
	{
		$earth_radius = 6371;
		$dLat         = deg2rad($latitude2 - $latitude1);
		$dLon         = deg2rad($longitude2 - $longitude1);
		$a            = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLon / 2) * sin($dLon / 2);
		$c            = 2 * asin(sqrt($a));
		$d            = $earth_radius * $c;
		return $d * 1000;
	}
	/* point present inside the rectangle starts here */
	private function is_rectangle($points_lats, $points_lngs, $lat, $lng)
	{
		$position_lat         = $lat;
		$position_long        = $lng;
		$rectanglepoints_lat1 = $points_lats[0];
		$rectanglepoints_lat2 = $points_lats[1];
		$rectanglepoints_lng1 = $points_lngs[0];
		$rectanglepoints_lng2 = $points_lngs[1];
		$availabel            = false;
		if (($position_lat >= $rectanglepoints_lat1 && $position_lat <= $rectanglepoints_lat2) && ($position_long >= $rectanglepoints_lng1 && $position_long <= $rectanglepoints_lng2)) {
			$availabel = true;
		}
		return $availabel;
	}
	private function show_errror_response()
	{
		echo json_encode(array(
			'status' => 'error',
			'message' => get_label('rest_no_outlets_found')
		));
		exit();
	}
}
