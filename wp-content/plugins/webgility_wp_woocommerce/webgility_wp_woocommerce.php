<?php
/**
 * @package Webgility
 */
/*
Plugin Name: Webgility
Plugin URI: http://webgility.com/
Description: eCommerce Connector connects online store module with Webgility desktop. To get started: 1) Click the "Activate" link to the left of this description, 2) Copy and past the url into your Webgility http://example.com/wp-content/plugins/webgility_wp_woocommerce/woocommerce.php .
Version: v366
Author: Webgility
Author URI: http://webgility.com/
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

define('eCC_VERSION', '3.6');
define('eCC_PLUGIN_URL', plugin_dir_url( __FILE__ ));

/** If you hardcode a WP.com API key here, all key config screens will be hidden */
if ( defined('WPCOM_API_KEY') )
	$wpcom_api_key = constant('WPCOM_API_KEY');
else
	$wpcom_api_key = '';

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit;
}


function webgility_init() {

}

