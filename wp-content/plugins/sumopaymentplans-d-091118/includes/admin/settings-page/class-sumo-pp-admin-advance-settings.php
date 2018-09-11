<?php

/**
 * Advanced Tab.
 * 
 * @class SUMO_PP_Advanced_Settings
 * @category Class
 */
class SUMO_PP_Advanced_Settings extends SUMO_PP_Abstract_Admin_Settings {

    /**
     * SUMO_PP_Advanced_Settings constructor.
     */
    public function __construct() {

        $this->id       = 'advanced' ;
        $this->label    = __( 'Advanced' , $this->text_domain ) ;
        $this->settings = $this->get_settings() ;
        $this->init() ;
    }

    /**
     * Get settings array.
     * @return array
     */
    public function get_settings() {
        global $current_section ;

        return apply_filters( 'sumopaymentplans_get_' . $this->id . '_settings' , array (
            array (
                'name' => __( 'Advanced Settings' , $this->text_domain ) ,
                'type' => 'title' ,
                'id'   => $this->prefix . 'advanced_settings' ,
            ) ,
            array (
                'name'     => __( 'Disable WooCommerce Emails for Payment Plan Orders' , $this->text_domain ) ,
                'id'       => $this->prefix . 'disabled_wc_order_emails' ,
                'newids'   => $this->prefix . 'disabled_wc_order_emails' ,
                'type'     => 'multiselect' ,
                'options'  => array (
                    'processing' => __( 'Processing order' , $this->text_domain ) ,
                    'completed'  => __( 'Completed order' , $this->text_domain ) ,
                ) ,
                'std'      => array () ,
                'default'  => array () ,
                'desc'     => __( 'This option will be applicable only for balance payable orders' , $this->text_domain ) ,
                'desc_tip' => true ,
            ) ,
            array ( 'type' => 'sectionend' , 'id' => $this->prefix . 'advanced_settings' ) ,
            array (
                'name' => __( 'Experimental Settings' , $this->text_domain ) ,
                'type' => 'title' ,
                'id'   => $this->prefix . 'experimental_settings' ,
            ) ,
            array (
                'name'     => __( 'Display Payment Plans as Hyperlink in Single Product Page' , $this->text_domain ) ,
                'id'       => $this->prefix . 'payment_plan_add_to_cart_via_href' ,
                'newids'   => $this->prefix . 'payment_plan_add_to_cart_via_href' ,
                'type'     => 'checkbox' ,
                'std'      => 'no' ,
                'default'  => 'no' ,
                'desc'     => __( 'If enabled, payment plans will be displayed as hyperlink which when clicked, the payment plan will be directly added to cart' , $this->text_domain ) ,
                'desc_tip' => true ,
            ) ,
            array ( 'type' => 'sectionend' , 'id' => $this->prefix . 'experimental_settings' ) ,
                ) ) ;
    }

}

return new SUMO_PP_Advanced_Settings() ;
