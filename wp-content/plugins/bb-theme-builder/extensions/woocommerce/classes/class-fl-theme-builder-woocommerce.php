<?php

/**
 * WooCommerce support for the theme builder.
 *
 * @since 1.0
 */
final class FLThemeBuilderWooCommerce {

	/**
	 * @since 1.0
	 * @return void
	 */
	static public function init() {
		// As of WooCommerce 3.3.3, if we don't have this, things break.
		add_theme_support( 'woocommerce' );

		// Actions
		add_action( 'wp',  __CLASS__ . '::load_modules', 1 );
	}

	/**
	 * Loads the WooCommerce modules.
	 *
	 * @since 1.0
	 * @return void
	 */
	static public function load_modules() {
		FLThemeBuilderLoader::load_modules( FL_THEME_BUILDER_WOOCOMMERCE_DIR . 'modules' );
	}
}

FLThemeBuilderWooCommerce::init();
