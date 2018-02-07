<?php
/**
Plugin Name: WooCommerce Advance Sales Report Golden
Plugin URI: http://plugins.infosofttech.com/
Author: Infosoft Consultants
Description: Woocommerce Sales Reporter shows you all key sales information in one main Dashboard in very intuitive, easy to understand format which gives a quick overview of your business and helps make smart decisions. Key features are Responsive Layout, More Summary Details on Dashboard, Advance Filters, Export to Excel, Online PDF Generation, Customize Columns, Stock Summary, Theme Color, Crosstab Reports, Product Variation Reports, Reports linked with Google Analytics, Auto Email Reporting*.
Version: 4.0
Author URI: http://www.infosofttech.com

Copyright: © 2017 - www.infosofttech.com - All Rights Reserved

Tested WooCommerce Version: 3.2.4
Tested Wordpress Version: 4.9

Last Update Date: Nov 17, 2017
**/ 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!function_exists('init_icwoocommercegolden')){
		
	function init_icwoocommercegolden() {
		global $ic_woocommerce_advance_sales_report_golden, $ic_woocommerce_advance_sales_report_golden_constant;		
		$constants = array(
					"version" 				=> "4.0"
					,"product_id" 			=> "666"
					,"plugin_key" 			=> "icwoocommercegolden"
					,"plugin_name" 			=> "WooCommerce Advance Sales Report Golden"
					,"plugin_api_url" 		=> "http://plugins.infosofttech.com/api-woo-golden.php"
					,"plugin_main_class" 	=> "IC_Woocommerce_Advance_Sales_Report_Golden"
					,"plugin_instance" 		=> "ic_woocommerce_advance_sales_report_golden"
					,"plugin_dir" 			=> 'ic-woocommerce-advance-sales-report-golden'
					,"plugin_file" 			=> __FILE__
					,"plugin_role" 			=> apply_filters('ic_commerce_golden_plugin_role','manage_woocommerce')/*'read',manage_woocommerce*/
					,"per_page_default"		=> 5
					,"plugin_parent_active" => false
					,"color_code" 			=> '#77aedb'
					,"plugin_parent" 		=> array(
						"plugin_name"		=>"WooCommerce"
						,"plugin_slug"		=>"woocommerce/woocommerce.php"
						,"plugin_file_name"	=>"woocommerce.php"
						,"plugin_folder"	=>"woocommerce"
						,"order_detail_url"	=>"post.php?&action=edit&post="
					)			
		);
		
		require_once('includes/ic_commerce_golden_functions.php');
		
		require_once('includes/ic_commerce_golden_add_actions.php');
		
		load_plugin_textdomain('icwoocommerce_textdomains', WP_PLUGIN_DIR.'/'.$constants['plugin_dir'].'/languages',$constants['plugin_dir'].'/languages');
		$constants['plugin_name'] 		= __('WooCommerce Advance Sales Report Golden', 	'icwoocommerce_textdomains');
		$constants['plugin_menu_name'] 	= __('WooCommerce Report Golden',					'icwoocommerce_textdomains');
		$constants['admin_page'] 		= isset($_REQUEST['page']) ? $_REQUEST['page'] : "";
		$constants['is_admin'] 			= is_admin();
		
		$constants = apply_filters('ic_commerce_golden_init_constants', $constants, $constants['plugin_key']);
		do_action('ic_commerce_golden_textdomain_loaded',$constants, $constants['plugin_key']);
		
		$ic_woocommerce_advance_sales_report_golden_constant = $constants;
		
		require_once('includes/ic_commerce_golden_schedule_mailing.php');
		$IC_Commerce_Golden_Schedule_Mailing = new IC_Commerce_Golden_Schedule_Mailing(__FILE__,'icwoocommercegolden');
			
		if ($constants['is_admin']) {
			require_once('includes/ic_commerce_golden_init.php');
			
			if(!class_exists('IC_Woocommerce_Advance_Sales_Report_Golden')){class IC_Woocommerce_Advance_Sales_Report_Golden extends IC_Commerce_Golden_Init{}}	
					
			$ic_woocommerce_advance_sales_report_golden 			= new IC_Woocommerce_Advance_Sales_Report_Golden( __FILE__, $ic_woocommerce_advance_sales_report_golden_constant);
		}//End Is Admin
	}//End Function
}
add_action('init','init_icwoocommercegolden', 100);


require_once('includes/ic_commerce_product_thumb.php');
$ic_commerce_product_thumb = new ic_commerce_product_thumb($constants = array());
