<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WF_OrderImpExpXML_AJAX_Handler {

	public function __construct() {
		add_action( 'wp_ajax_woocommerce_xml_order_import_request', array( $this, 'wf_xml_order_import_request' ) );
	}
	
	public function wf_xml_order_import_request() {
		define( 'WP_LOAD_IMPORTERS', true );
                OrderImpExpXML_Importer::order_importer();
	}

	public function regenerate_thumbnail() {
		@error_reporting( 0 );

		header( 'Content-type: application/json' );

		$id    = (int) $_REQUEST['id'];
		$image = get_post( $id );

		if ( ! $image || 'attachment' != $image->post_type || 'image/' != substr( $image->post_mime_type, 0, 6 ) )
			die( json_encode( array( 'error' => sprintf( __( 'Failed resize: %s is an invalid image ID.', 'wf_order_import_export_xml' ), esc_html( $_REQUEST['id'] ) ) ) ) );

		if ( ! current_user_can( 'manage_woocommerce' ) )
			$this->die_json_error_msg( $image->ID, __( "Your user account doesn't have permission to resize images", 'wf_order_import_export_xml' ) );

		$fullsizepath = get_attached_file( $image->ID );

		if ( false === $fullsizepath || ! file_exists( $fullsizepath ) )
			$this->die_json_error_msg( $image->ID, sprintf( __( 'The originally uploaded image file cannot be found at %s', 'wf_order_import_export_xml' ), '<code>' . esc_html( $fullsizepath ) . '</code>' ) );

		@set_time_limit( 900 ); 

		$metadata = wp_generate_attachment_metadata( $image->ID, $fullsizepath );

		if ( is_wp_error( $metadata ) )
			$this->die_json_error_msg( $image->ID, $metadata->get_error_message() );
		if ( empty( $metadata ) )
			$this->die_json_error_msg( $image->ID, __( 'Unknown failure reason.', 'wf_order_import_export_xml' ) );

		wp_update_attachment_metadata( $image->ID, $metadata );

		die( json_encode( array( 'success' => sprintf( __( '&quot;%1$s&quot; (ID %2$s) was successfully resized in %3$s seconds.', 'wf_order_import_export_xml' ), esc_html( get_the_title( $image->ID ) ), $image->ID, timer_stop() ) ) ) );
	}	

	public function die_json_error_msg( $id, $message ) {
        die( json_encode( array( 'error' => sprintf( __( '&quot;%1$s&quot; (ID %2$s) failed to resize. The error message was: %3$s', 'regenerate-thumbnails' ), esc_html( get_the_title( $id ) ), $id, $message ) ) ) );
    }	
}

new WF_OrderImpExpXML_AJAX_Handler();