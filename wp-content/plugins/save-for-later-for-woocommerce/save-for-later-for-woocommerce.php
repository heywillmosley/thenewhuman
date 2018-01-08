<?php
/**
 * Plugin Name: Save For Later For WooCommerce
 * Plugin URI: http://www.storeapps.org/product/save-for-later-for-woocommerce/
 * Description: Allow your customer to save products from cart for later use.
 * Version: 1.0.3
 * Author: StoreApps
 * Author URI: http://www.storeapps.org/
 * Requires at least: 4.0
 * Tested up to: 4.7.3
 * Requires WooCommerce: 2.5
 * Text Domain: save-for-later-for-woocommerce
 * Domain Path: /languages/
 * Copyright (c) 2016-2017 StoreApps. All rights reserved.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$active_plugins = (array) get_option( 'active_plugins', array() );

if ( is_multisite() ) {
	$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
}

if ( ! ( in_array( 'woocommerce/woocommerce.php', $active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins ) ) ) return;

function initialize_save_for_later_for_woocommerce() {

	require_once 'includes/class-sa-save-for-later.php';

	require_once 'includes/wc-compat/version-2-5.php';
	require_once 'includes/wc-compat/version-2-6.php';
	require_once 'includes/wc-compat/version-3-0.php';

	$GLOBALS['sa_save_for_later'] = new SA_Save_For_Later();

	if ( ! class_exists( 'StoreApps_Upgrade_1_4' ) ) {
		require_once 'sa-includes/class-storeapps-upgrade-v-1-4.php';
	}

	$sku                = 'sfl';
	$prefix             = 'save-for-later-for-woocommerce';
	$plugin_name        = 'Save For Later For WooCommerce';
	$text_domain        = SA_Save_For_Later::$text_domain;
	$documentation_link = 'http://www.storeapps.org/knowledgebase_category/save-for-later-for-woocommerce/';
	$bn_upgrader        = new StoreApps_Upgrade_1_4( __FILE__, $sku, $prefix, $plugin_name, $text_domain, $documentation_link );

}
add_action( 'plugins_loaded', 'initialize_save_for_later_for_woocommerce' );