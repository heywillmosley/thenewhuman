<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmRegUpdate extends FrmAddon {
	public $plugin_file;
	public $plugin_name = 'Registration Lite';
	public $version = '1.11.06';

	public function __construct() {
		$this->plugin_file = dirname( dirname( __FILE__ ) ) . '/formidable-registration.php';
		parent::__construct();
	}

	public static function load_hooks() {
		add_filter( 'frm_include_addon_page', '__return_true' );
		new FrmRegUpdate();
	}
}
