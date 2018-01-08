<?php

$product_name = 'ordercsvimportexport'; // name should match with 'Software Title' configured in server, and it should not contains white space
$product_version = '2.2.0';
$product_slug = 'order-import-export-for-woocommerce/order-import-export.php'; //product base_path/file_name
$serve_url = 'https://www.xadapter.com/';
$plugin_settings_url = admin_url( 'admin.php?page=wf_woocommerce_order_im_ex' );

//include api manager
include_once ( 'wf_api_manager.php' );
new WF_API_Manager($product_name, $product_version, $product_slug, $serve_url, $plugin_settings_url);
?>
