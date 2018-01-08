<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WF_OrderImpExpXML_System_Status_Tools {

	public function __construct() {
		add_filter( 'woocommerce_debug_tools', array( $this, 'tools' ) );
	}

	public function tools( $tools ) {
		$tools['delete_trashed_orders'] = array(
			'name'		=> __( 'Delete Trashed Orders','wf_order_import_export_xml'),
			'button'	=> __( 'Delete  Trashed Orders','wf_order_import_export_xml' ),
			'desc'		=> __( 'This tool will delete all  Trashed Orders.', 'wf_order_import_export_xml' ),
			'callback'  => array( $this, 'delete_trashed_orders' )
		);
		$tools['delete_all_orders'] = array(
			'name'		=> __( 'Delete Orders','wf_order_import_export_xml'),
			'button'	=> __( 'Delete ALL Orders','wf_order_import_export_xml' ),
			'desc'		=> __( 'This tool will delete all orders allowing you to start fresh.', 'wf_order_import_export_xml' ),
			'callback'  => array( $this, 'delete_all_orders' )
		);
		return $tools;
	}

	public function delete_trashed_orders() {
		global $wpdb;
		$result  = absint( $wpdb->delete( $wpdb->posts, array( 'post_type' => 'shop_order' , 'post_status' => 'trash') ) );
		echo '<div class="updated"><p>' . sprintf( __( '%d Orders Deleted', 'wf_order_import_export_xml' ), ( $result ) ) . '</p></div>';
	}

	public function delete_all_orders() {
		global $wpdb;

		$result = absint( $wpdb->delete( $wpdb->posts, array( 'post_type' => 'shop_order' ) ) );

		$wpdb->query( "DELETE pm
			FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} wp ON wp.ID = pm.post_id
			WHERE wp.ID IS NULL" );
		echo '<div class="updated"><p>' . sprintf( __( '%d Orders Deleted', 'wf_order_import_export_xml' ), $result ) . '</p></div>';
	}	
}

new WF_OrderImpExpXML_System_Status_Tools();