<?php
if( !class_exists('WF_UPS_Admin_Options') ){
    class WF_UPS_Admin_Options{
        function __construct(){
			$this->init();
        }

        function init(){
            //add a custome field in product page
            add_action( 'woocommerce_product_options_shipping', array($this,'wf_add_deliveryconfirmation_field')  );

            //Saving the values
            add_action( 'woocommerce_process_product_meta', array( $this, 'wf_save_deliveryconfirmation_field' ) );
        }

        function wf_add_deliveryconfirmation_field() {
			 
		// Print a custom select field
		woocommerce_wp_select( array(
			'id' => '_wf_ups_deliveryconfirmation',
			'label' => __('Delivery Confirmation'),
					'options'	=> array(
						0	=> __( 'Confirmation Not Required', 'ups-woocommerce-shipping' ),
						1	=> __( 'Confirmation Required', 'ups-woocommerce-shipping' ),
						2	=> __( 'Confirmation With Signature', 'ups-woocommerce-shipping' ),
						3	=> __( 'Confirmation With Adult Signature', 'ups-woocommerce-shipping' )
					),
			'desc_tip' => false,
		) );

		woocommerce_wp_text_input( array(
			'id'		=> '_wf_ups_custom_declared_value',
			'label'		=> __( 'Custom Declared Value (UPS)', 'ups-woocommerce-shipping' ),
			'description'	=> __('This amount will be reimbursed from UPS if products get damaged and you have opt for Insurance.','ups-woocommerce-shipping'),
			'desc_tip'	=> 'true',
			'placeholder'	=> __( 'Insurance amount UPS', 'ups-woocommerce-shipping' )
		) );
        }
		

        function wf_save_deliveryconfirmation_field( $post_id ) {

		if ( isset( $_POST['_wf_ups_deliveryconfirmation'] ) ) {
			update_post_meta( $post_id, '_wf_ups_deliveryconfirmation', esc_attr( $_POST['_wf_ups_deliveryconfirmation'] ) );
		}

		// Update the Insurance amount on individual product page
		if( isset($_POST['_wf_ups_custom_declared_value'] ) ) {
			update_post_meta( $post_id, '_wf_ups_custom_declared_value', esc_attr( $_POST['_wf_ups_custom_declared_value'] ) );
		}
	}
    }
    new WF_UPS_Admin_Options();
}
