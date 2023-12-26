<?php
/**************************
 Project Name	: POS
Created on		: 28  Sep, 2016
Last Modified 	: 28 Sep, 2016
Description		:  this file contains review count and ratings.
***************************/

/* Revert back loyality points */
if (! function_exists ( 'review_rating_count' )) {

	function review_rating_count($app_id,$product_id) {

		$CI = & get_instance ();

		$ret_arr = array();

		/*----------------------------------*/
		$table = 'product_review';
		$table_details = 'product_review_details';

		$join  = array();

		$where = array (
			'review_application_id' => $app_id, 
			'review_product_id' => addslashes ( $product_id ),
			'review_product_status' => '1'
		);

		$join [0] ['select'] = "$table.review_id";
		$join [0] ['table'] = $table;
		$join [0] ['condition'] = "$table_details.review_id=$table.review_id";
		$join [0] ['type'] = "LEFT";

		$result = $CI->Mydb->get_all_records ( 'SUM(review_rating) as total_rating,COUNT(*) as total_reviews', $table_details, $where,'','', '','','',$join);

		if(!empty($result)) {

			$ret_arr['total_reviews'] = floatval($result[0]['total_reviews']);
			if($ret_arr['total_reviews']!=0) /*Division by zero*/
			$ret_arr['total_rating'] = round((floatval( $result[0]['total_rating']) / $ret_arr['total_reviews']),1);
		}

		return $ret_arr;
	}
}
