<?php

// Defines
define( 'FL_CHILD_THEME_DIR', get_stylesheet_directory() );
define( 'FL_CHILD_THEME_URL', get_stylesheet_directory_uri() );

/* Load custom login page styles */
function my_custom_login() {
echo '<link rel="stylesheet" type="text/css" href="' . get_bloginfo('stylesheet_directory') . '/login/custom-login-styles.css" />';
}
add_action('login_head', 'my_custom_login');

// Classes
require_once 'classes/class-fl-child-theme.php';

// Actions
add_action( 'wp_enqueue_scripts', 'FLChildTheme::enqueue_scripts', 1000 );

/**
*  Hide shipping rates when free shipping is available*
*
*  @param array $rates Array of rates found for the package
*  @param array $package The package array/object being shipped
*  @return array of modified rates
*
*/
function nh_hide_shipping_when_free_available($rates, $package) {
  if (isset($rates['free_shipping'])) {
    unset($rates['flat_rate']);
  }
  return $rates;
}
add_filter('woocommerce_package_rates', 'nh_hide_shipping_when_free_available', 10, 2);



// Allow exe or dmg for digital downloads
add_filter('upload_mimes', function($mimetypes, $user)
{
    // Only allow these mimetypes for admins or shop managers
    $manager = $user ? user_can($user, 'manage_woocommerce') : current_user_can('manage_woocommerce');

    if ($manager)
    {
        $mimetypes = array_merge($mimetypes, array (
            'exe' => 'application/octet-stream',
            'dmg' => 'application/octet-stream'
        ));
    }

    return $mimetypes;
}, 10, 2);


//Send all emails from info@thenewhuman.com
add_filter( 'wp_mail_from', 'your_email' );
function your_email( $original_email_address )
{
  return 'info@thenewhuman.com';
}
add_filter( 'wp_mail_from_name', 'custom_wp_mail_from_name' );
function custom_wp_mail_from_name( $original_email_from )
{
  return 'The New Human';
}


// Skill Certificate Verification for Network Update
//add_filter( 'https_local_ssl_verify', '__return_false' );

// Don't require website url w/ AWP
/**
 * Plugin Name: AffiliateWP - Make URL Field Not Required
 * Plugin URI: http://affiliatewp.com
 * Description: Makes the URL field on the affiliate registration form not required
 * Author: Andrew Munro, Sumobi
 * Author URI: http://sumobi.com
 * Version: 1.0
 */
function affwp_custom_make_url_not_required( $required_fields ) {
	unset( $required_fields['affwp_user_url'] );
	return $required_fields;
}
add_filter( 'affwp_register_required_fields', 'affwp_custom_make_url_not_required' );


// Activate WordPress Maintenance Mode
function wp_maintenance_mode(){
    if(!current_user_can('edit_themes') || !is_user_logged_in()){
        wp_die('<h1 style="color:red">New Human is currently under scheduled maintenance</h1><br />We are performing scheduled maintenance. We will be back online shortly!');
    }
}
//add_action('get_header', 'wp_maintenance_mode');


/**
 * Hide shipping rates when free shipping is available.
 * Updated to support WooCommerce 2.6 Shipping Zones.
 *
 * @param array $rates Array of rates found for the package.
 * @return array
 */
function my_hide_shipping_when_free_is_available( $rates ) {
	$free = array();
	foreach ( $rates as $rate_id => $rate ) {
		if ( 'free_shipping' === $rate->method_id ) {
			$free[ $rate_id ] = $rate;
			break;
		}
	}
	return ! empty( $free ) ? $free : $rates;
}
add_filter( 'woocommerce_package_rates', 'my_hide_shipping_when_free_is_available', 100 );


/** Sort A - Z Packing Slips **/
add_filter( 'wpo_wcpdf_order_items_data', 'wpo_wcpdf_sort_items_by_name', 10, 2 );
function wpo_wcpdf_sort_items_by_name ( $items, $order ) {
    usort($items, 'wpo_wcpdf_sort_by_name');
    return $items;
}

function wpo_wcpdf_sort_by_name($a, $b) {
    if (!isset($a['name'])) $a['name'] = '';
    if (!isset($b['name'])) $b['name'] = '';
    if ($a['name']==$b['name']) return 0;
    return ($a['name']<$b['name'])?-1:1;
}

/**
 * Filter Force Login to allow exceptions for specific URLs.
 *
 * @return array An array of URLs. Must be absolute.
 */
function my_forcelogin_whitelist( $whitelist ) {
  $whitelist[] = site_url( '/' );
  $whitelist[] = site_url( '/terms/' );
  $whitelist[] = site_url( '/my-account/' );
  $whitelist[] = site_url( '/my-account/lost-password/' );
  $whitelist[] = site_url( '/my-account/lost-password/?' . $_SERVER['QUERY_STRING'] );
  $whitelist[] = site_url( '/my-account/customer-logout/' );
  $whitelist[] = site_url( '/my-account/customer-logout/?' . $_SERVER['QUERY_STRING'] );
  $whitelist[] = site_url( '/sv2-insight/' );
  $whitelist[] = site_url( '/bionetics/' );
  $whitelist[] = site_url( '/testimonials/' );
  $whitelist[] = site_url( '/iabc/' );
  return $whitelist;
}
add_filter('v_forcelogin_whitelist', 'my_forcelogin_whitelist', 10, 1);