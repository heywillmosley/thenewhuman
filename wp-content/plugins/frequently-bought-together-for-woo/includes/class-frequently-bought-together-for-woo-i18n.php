<?php

class Frequently_Bought_Together_For_Woo_i18n {

	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'frequently-bought-together-for-woo',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
