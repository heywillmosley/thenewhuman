<?php

/**
 * EDD support for the theme builder.
 *
 * @since 1.1
 */
final class FLThemeBuilderEDD {

	/**
	 * @since 1.1
	 * @return void
	 */
	static public function init() {
		// Actions
		add_action( 'wp',  __CLASS__ . '::load_modules', 1 );
	}

	/**
	 * Loads the EDD modules.
	 *
	 * @since 1.1
	 * @return void
	 */
	static public function load_modules() {
		FLThemeBuilderLoader::load_modules( FL_THEME_BUILDER_EDD_DIR . 'modules' );
	}
}

FLThemeBuilderEDD::init();
