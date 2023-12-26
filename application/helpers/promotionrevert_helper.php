<?php

/**************************
 Project Name    :  White Label
 Created on        : 18 Apr, 2-18
 Last Modified     : 18 Apr, 2-18
 Description        :   Cancel order promotion revert option
 ***************************/
/* Remove reward points */
if (!function_exists(' removeRewardPoints')) {
    function removeRewardPoints($order_primary_id, $customer_id)
    {
        $CI = &get_instance();

        $loyality = $CI->Mydb->get_record('lh_id,lh_order_primaryid,lh_redeem_history', 'loyality_history', array(
            'lh_order_primaryid' => $order_primary_id,
            "lh_customer_id" => $customer_id, "lh_redeem_status" => "Redeemed"
        ));
        if (!empty($loyality) && (isset($loyality['lh_redeem_history']) && $loyality['lh_redeem_history'] != "")) {
            $histryData = json_decode($loyality['lh_redeem_history']);

            if (!empty($histryData)) {
                foreach ($histryData as $key => $val) {
                    $points = array();
                    if ($key != "" && $val != "") {
                        $points = $CI->Mydb->get_record('lh_id as pointId,lh_credit_points,lh_debit_points', 'loyality_points', array(
                            'lh_id' => $key,
                            "lh_customer_id" => $customer_id
                        ));

                        if (!empty($points)) {
                            $currentPoints = $points['lh_debit_points'];
                            $spentPoints = $val;
                            $totalDebited = ($currentPoints - $spentPoints);
                            $dbtotalPoints = $points['lh_credit_points'];
                            if ($totalDebited >= 0) {
                                $CI->Mydb->update('loyality_points', array(
                                    'lh_customer_id' => $customer_id,
                                    'lh_id' => $points['pointId']
                                ), array(
                                    'lh_debit_points' => $totalDebited

                                ));
                            }
                        }
                    }
                }
            }

            $CI->Mydb->update('loyality_history', array(
                'lh_customer_id' => $customer_id,
                'lh_id' => $loyality['lh_id'],
                'lh_order_primaryid' => $order_primary_id
            ), array(
                'lh_redeem_status' => 'Refunded'
            ));
        }

        /*  revert cacnel order flow */
        /* Reset Customer points */

        $findRecord = $CI->Mydb->get_record("lh_expiry_flag", "pos_loyality_points", array("lh_customer_id" => $customer_id, "lh_ref_id" => $order_primary_id, "lh_expiry_flag" => "Yes"));
        if (!empty($findRecord)) {
            $CI->Mydb->update('loyality_points', array(
                'lh_customer_id' => $customer_id,
                'lh_from' => 'order',
                'lh_ref_id' => $order_primary_id
            ), array(
                'lh_expiry_flag' => 'no',
                'lh_cancel_status' => '1',
                'lh_cancel_source' => 'Admin',
                'lh_status' => 'Cancelled'
            ));
        }
    }
}
