<?php

class USIN_Woocommerce_Ajax extends USIN_Ajax{

	public function __construct(){
		$this->user_capability = USIN_Capabilities::LIST_USERS;
		$this->nonce_key = USIN_List_Page::$nonce_key;
	}

	public function add_actions(){
		add_action('wp_ajax_usin_wc_product_search', array($this, 'product_search'));
	}

	public function product_search(){
		$this->verify_request();
		$this->validate_required_get_params(array('search'));

		$res = USIN_Woocommerce::get_product_options($_GET['search']);
	
		$this->respond($res);
	}
}