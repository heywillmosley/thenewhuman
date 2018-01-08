<?php

class WP_E_Model{

	public $table_prefix;

	public function __construct(){
		global $wpdb;
		$this->wpdb = $wpdb;

		$this->table_prefix = $wpdb->prefix . "esign_";
		$this->prefix = $this->table_prefix; // table_prefix alias
	}
}