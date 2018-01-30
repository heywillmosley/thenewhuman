<?php

class USIN_BuddyPress_Numeric_Loader extends USIN_Numeric_Field_Loader{

	public function get_default_data(){
		return USIN_BuddyPress_Query::get_field_counts($this->report->get_field_id(), $this->total_col, $this->label_col);
	}

	public function get_data_in_ranges($chunk_size){
		global $wpdb;

		$prefix = USIN_BuddyPress_Query::get_prefix();

		$select = $this->get_select('`value`', $chunk_size);
		$group_by = $this->get_group_by('`value`', $chunk_size);

		$query = $wpdb->prepare("$select FROM ".$prefix."bp_xprofile_data WHERE field_id = %d $group_by",
			$this->report->get_field_id());

		return $wpdb->get_results( $query );

	}

}