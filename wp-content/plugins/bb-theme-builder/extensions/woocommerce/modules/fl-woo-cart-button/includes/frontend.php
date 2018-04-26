<?php

$button = FLPageDataWooCommerce::get_add_to_cart_button();

if ( function_exists( 'YITH_YWRAQ_Frontend' ) ) {
	if ( 'yes' !== get_option( 'ywraq_hide_add_to_cart' ) ) {
		echo $button;
	}
	YITH_YWRAQ_Frontend()->add_button_single_page();
} else {
	echo $button;
}
