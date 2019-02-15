<?php
/*
Plugin Name: Ni WooCommerce Sales Report
Description: Enhance WooCommerce sales report beautifully and provide complete solutions for WooCommerce reporting.
Author: 	 anzia
Version: 	 3.2.7
Author URI:  http://naziinfotech.com/
Plugin URI:  https://wordpress.org/plugins/ni-woocommerce-sales-report/
License:	 GPLv3 or later
License URI: http://www.gnu.org/licenses/agpl-3.0.html
Text Domain: nisalesreport
Domain Path: /languages/
Tested up to:  5.0.1
WC requires at least: 3.0.0
WC tested up to:3.5.5
Last Updated Date: 17-December-2018
*/
if ( !class_exists( 'Ni_WooCommerce_Sales_Report' ) ) {
	class Ni_WooCommerce_Sales_Report {
		 function __construct() {
			if ( is_admin() ) {
				add_action( 'plugins_loaded',  array(&$this,'plugins_loaded') );
				include_once('include/base-sales-report.php'); 
				$obj = new BaseSalesReport();
			}
		 }
		 function plugins_loaded(){
			load_plugin_textdomain('nisalesreport', WP_PLUGIN_DIR.'/ni-woocommerce-sales-report/languages','ni-woocommerce-sales-report/languages');
			//load_plugin_textdomain('nisalesreport', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		 }	
		 
	}
	$obj  = new Ni_WooCommerce_Sales_Report();
}

?>
