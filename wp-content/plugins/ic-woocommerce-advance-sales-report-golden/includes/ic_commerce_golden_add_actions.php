<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!function_exists('get_ic_commerce_golden_customization')){
	function get_ic_commerce_golden_customization($constants = array(), $admin_page = ""){
			
			if($admin_page == "icwoocommercegolden_details_page"){
				
				global $IC_Commerce_Golden_Customization;
				$path = WP_PLUGIN_DIR."/ic-woocommerce-advance-sales-report-golden/includes/";
				
				if(file_exists($path.'ic_commerce_golden_customization.php')){
					require_once($path.'ic_commerce_golden_customization.php');
					$IC_Commerce_Golden_Customization = new IC_Commerce_Golden_Customization($constants, $admin_page);
				}				
			}
			
			
			if($admin_page == "icwoocommercegolden_report_page"){
				
				global $IC_Commerce_Golden_Customization;
				$path = WP_PLUGIN_DIR."/ic-woocommerce-advance-sales-report-golden/includes/";
				
				if(file_exists($path.'ic_commerce_golden_refund_summary.php')){
					require_once($path.'ic_commerce_golden_refund_summary.php');
					$IC_Commerce_Golden_Refund_Summary = new IC_Commerce_Golden_Refund_Summary($constants, $admin_page);
				}				
			}
	}	
	add_action("ic_commerce_golden_page_init","get_ic_commerce_golden_customization", 10, 2);	
}