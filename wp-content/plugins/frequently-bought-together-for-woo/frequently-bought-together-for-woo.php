<?php
/**
 * Plugin Name:       Frequently Bought Together For Woo
 * Plugin URI:        http://www.storeapps.org/product/frequently-bought-together-woocommerce/
 * Description:       Shows products which are frequently bought together
 * Version:           1.2.4
 * Author:            StoreApps
 * Author URI:        http://www.storeapps.org
 * Requires at least: 3.3
 * Tested up to: 	  4.7.2
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       frequently-bought-together-for-woo
 * Domain Path:       /languages/
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$active_plugins = (array) get_option( 'active_plugins', array() );

if ( is_multisite() ) {
    $active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
}

if ( ! ( in_array( 'woocommerce/woocommerce.php', $active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins ) ) ) return;

function activate_frequently_bought_together_for_woo() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-frequently-bought-together-for-woo-activator.php';
	Frequently_Bought_Together_For_Woo_Activator::activate();
}

function deactivate_frequently_bought_together_for_woo() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-frequently-bought-together-for-woo-deactivator.php';
	Frequently_Bought_Together_For_Woo_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_frequently_bought_together_for_woo' );
register_deactivation_hook( __FILE__, 'deactivate_frequently_bought_together_for_woo' );

require plugin_dir_path( __FILE__ ) . 'includes/class-frequently-bought-together-for-woo.php';

function run_frequently_bought_together_for_woo() {

	define ('FREQUENTLY_BOUGHT_TOGETHER_FOR_WOO_PLUGIN_FILE' , __FILE__);

	$plugin = new Frequently_Bought_Together_For_Woo();
	$plugin->run();

}

run_frequently_bought_together_for_woo();
