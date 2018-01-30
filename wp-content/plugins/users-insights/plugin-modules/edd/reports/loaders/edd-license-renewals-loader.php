<?php

class USIN_Edd_License_Renewals_Loader extends USIN_Period_Report_Loader {


	protected function load_data(){
		global $wpdb;
		
		$group_by = $this->get_period_group_by($this->label_col);

		$query = $wpdb->prepare("SELECT COUNT(*) AS $this->total_col, post_date AS $this->label_col FROM $wpdb->posts AS posts".
			" INNER JOIN $wpdb->postmeta AS meta on posts.ID = meta.post_id AND meta.meta_key = '_edd_payment_meta' AND meta.meta_value like %s".
			" WHERE post_type = %s AND post_date >= %s AND post_date <= %s AND post_status = 'publish'".
			" GROUP BY $group_by",
			'%"is_renewal";b:1%', USIN_EDD::ORDER_POST_TYPE, $this->get_period_start(), $this->get_period_end());

		return $wpdb->get_results( $query );
	}

}