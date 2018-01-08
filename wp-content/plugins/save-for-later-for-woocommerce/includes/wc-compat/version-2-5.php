<?php
if ( !defined( 'ABSPATH' ) ) exit;
if ( !class_exists( 'SA_WC_Compatibility_2_5' ) ) {

/**
 * Class to check for WooCommerce versions & return variables accordingly
 * Compatibility class for WooCommerce 2.5+
 * 
 * @version 1.0.0
 * @since 2.7 7-January-2017
 *
 */
	class SA_WC_Compatibility_2_5 {

		/** 
		 * to check if WooCommerce version is less than 2.5.0
		 */
		public static function is_wc_gte_25() {
			return self::is_wc_greater_than( '2.4.13' );
		}

		public static function get_wc_version() {
			if (defined('WC_VERSION') && WC_VERSION)
				return WC_VERSION;
			if (defined('WOOCOMMERCE_VERSION') && WOOCOMMERCE_VERSION)
				return WOOCOMMERCE_VERSION;
			return null;
		}

		public static function is_wc_greater_than( $version ) {
			return version_compare( self::get_wc_version(), $version, '>' );
		}
	}
}