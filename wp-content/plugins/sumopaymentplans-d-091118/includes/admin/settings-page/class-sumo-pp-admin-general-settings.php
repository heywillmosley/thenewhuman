<?php

/**
 * General Tab.
 * 
 * @class SUMO_PP_General_Settings
 * @category Class
 */
class SUMO_PP_General_Settings extends SUMO_PP_Abstract_Admin_Settings {

    /**
     * SUMO_PP_General_Settings constructor.
     */
    public function __construct() {

        $this->id       = 'general' ;
        $this->label    = __( 'General' , $this->text_domain ) ;
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
                'name' => __( 'Deposit Global Level Settings' , $this->text_domain ) ,
                'type' => 'title' ,
                'id'   => $this->prefix . 'deposit_global_settings'
            ) ,
            array (
                'name'     => __( 'Force Deposit' , $this->text_domain ) ,
                'id'       => $this->prefix . 'force_deposit' ,
                'newids'   => $this->prefix . 'force_deposit' ,
                'type'     => 'checkbox' ,
                'std'      => 'no' ,
                'default'  => 'no' ,
                'desc'     => __( 'When enabled, the user will be forced to pay a deposit amount' , $this->text_domain ) ,
                'desc_tip' => true ,
            ) ,
            array (
                'name'     => __( 'Deposit Type' , $this->text_domain ) ,
                'id'       => $this->prefix . 'deposit_type' ,
                'newids'   => $this->prefix . 'deposit_type' ,
                'type'     => 'select' ,
                'options'  => array (
                    'pre-defined'  => __( 'Predefined Deposit Amount' , $this->text_domain ) ,
                    'user-defined' => __( 'User Defined Deposit Amount' , $this->text_domain ) ,
                ) ,
                'std'      => 'pre-defined' ,
                'default'  => 'pre-defined' ,
                'desc'     => '' ,
                'desc_tip' => true ,
            ) ,
            array (
                'name'              => __( 'Deposit Percentage' , $this->text_domain ) ,
                'id'                => $this->prefix . 'fixed_deposit_percent' ,
                'newids'            => $this->prefix . 'fixed_deposit_percent' ,
                'type'              => 'number' ,
                'std'               => '50' ,
                'default'           => '50' ,
                'desc'              => '' ,
                'desc_tip'          => true ,
                'custom_attributes' => array (
                    'min'  => '0.01' ,
                    'max'  => '99.99' ,
                    'step' => '0.01' ,
                ) ,
            ) ,
            array (
                'name'              => __( 'Minimum Deposit (%)' , $this->text_domain ) ,
                'id'                => $this->prefix . 'min_deposit' ,
                'newids'            => $this->prefix . 'min_deposit' ,
                'type'              => 'number' ,
                'std'               => '0.01' ,
                'default'           => '0.01' ,
                'desc'              => '' ,
                'desc_tip'          => true ,
                'custom_attributes' => array (
                    'min'  => '0.01' ,
                    'max'  => '99.99' ,
                    'step' => '0.01' ,
                ) ,
            ) ,
            array (
                'name'              => __( 'Maximum Deposit (%)' , $this->text_domain ) ,
                'id'                => $this->prefix . 'max_deposit' ,
                'newids'            => $this->prefix . 'max_deposit' ,
                'type'              => 'number' ,
                'std'               => '99.99' ,
                'default'           => '99.99' ,
                'desc'              => '' ,
                'desc_tip'          => true ,
                'custom_attributes' => array (
                    'min'  => '0.01' ,
                    'max'  => '99.99' ,
                    'step' => '0.01' ,
                ) ,
            ) ,
            array (
                'name'              => __( 'Deposit Balance Payment Due Date' , $this->text_domain ) ,
                'id'                => $this->prefix . 'pay_balance_after' ,
                'newids'            => $this->prefix . 'pay_balance_after' ,
                'type'              => 'number' ,
                'std'               => '10' ,
                'default'           => '10' ,
                'desc'              => __( 'day(s) from the date of deposit payment' , $this->text_domain ) ,
                'custom_attributes' => array (
                    'min' => '1' ,
                ) ,
            ) ,
            array ( 'type' => 'sectionend' , 'id' => $this->prefix . 'deposit_global_settings' ) ,
            array (
                'name' => __( 'Payment Plan Global Level Settings' , $this->text_domain ) ,
                'type' => 'title' ,
                'id'   => $this->prefix . 'global_payment_plan_settings'
            ) ,
            array (
                'name'     => __( 'Force Payment Plan' , $this->text_domain ) ,
                'id'       => $this->prefix . 'force_payment_plan' ,
                'newids'   => $this->prefix . 'force_payment_plan' ,
                'type'     => 'checkbox' ,
                'std'      => 'no' ,
                'default'  => 'no' ,
                'desc'     => '' ,
                'desc_tip' => true ,
            ) ,
            array (
                'name'     => __( 'Select Plans' , $this->text_domain ) ,
                'id'       => $this->prefix . 'selected_plans' ,
                'newids'   => $this->prefix . 'selected_plans' ,
                'type'     => 'multiselect' ,
                'options'  => _sumo_pp_get_payment_plan_names() ,
                'std'      => array () ,
                'default'  => array () ,
                'desc'     => '' ,
                'desc_tip' => true ,
            ) ,
            array ( 'type' => 'sectionend' , 'id' => $this->prefix . 'global_payment_plan_settings' ) ,
            array (
                'name' => __( 'General Settings' , $this->text_domain ) ,
                'type' => 'title' ,
                'id'   => $this->prefix . 'general_settings'
            ) ,
            array (
                'name'              => __( 'Create Next Payable Order' , $this->text_domain ) ,
                'id'                => $this->prefix . 'create_next_payable_order_before' ,
                'newids'            => $this->prefix . 'create_next_payable_order_before' ,
                'type'              => 'number' ,
                'std'               => '1' ,
                'default'           => '1' ,
                'desc'              => __( 'day(s)' , $this->text_domain ) ,
                'desc_tip'          => __( 'Payable order will be created before specified days. If set as 1 then order will be created one day before payment date' , $this->text_domain ) ,
                'custom_attributes' => array (
                    'min' => '1' ,
                ) ,
            ) ,
            array (
                'name'              => __( 'Overdue Period' , $this->text_domain ) ,
                'id'                => $this->prefix . 'specified_overdue_days' ,
                'newids'            => $this->prefix . 'specified_overdue_days' ,
                'type'              => 'number' ,
                'std'               => '0' ,
                'default'           => '0' ,
                'desc'              => __( 'day(s)' , $this->text_domain ) ,
                'desc_tip'          => __( 'If the payment is not made within the payment date, payment will goto overdue status and it will be in that status for the specified number of days.' , $this->text_domain ) ,
                'custom_attributes' => array (
                    'min' => '0' ,
                ) ,
            ) ,
            array (
                'name'     => __( 'Invoice Reminder' , $this->text_domain ) ,
                'id'       => $this->prefix . 'notify_invoice_before' ,
                'newids'   => $this->prefix . 'notify_invoice_before' ,
                'type'     => 'text' ,
                'std'      => '3,2,1' ,
                'default'  => '3,2,1' ,
                'desc'     => __( 'day(s) before next payment date' , $this->text_domain ) ,
                'desc_tip' => false ,
            ) ,
            array (
                'name'     => __( 'Overdue Reminder' , $this->text_domain ) ,
                'id'       => $this->prefix . 'notify_overdue_before' ,
                'newids'   => $this->prefix . 'notify_overdue_before' ,
                'type'     => 'text' ,
                'std'      => '1' ,
                'default'  => '1' ,
                'desc'     => __( 'day(s) after payment due date' , $this->text_domain ) ,
                'desc_tip' => false ,
            ) ,
            array (
                'name'     => __( 'Charge Tax' , $this->text_domain ) ,
                'id'       => $this->prefix . 'charge_tax_during' ,
                'newids'   => $this->prefix . 'charge_tax_during' ,
                'type'     => 'select' ,
                'options'  => array (
                    'initial-payment' => __( 'During Initial Payment' , $this->text_domain ) ,
                    'each-payment'    => __( 'During Each Payment' , $this->text_domain ) ,
                ) ,
                'std'      => 'each-payment' ,
                'default'  => 'each-payment' ,
                'desc'     => '' ,
                'desc_tip' => true ,
            ) ,
            array (
                'name'     => __( 'Disable Payment Gateways' , $this->text_domain ) ,
                'id'       => $this->prefix . 'disabled_payment_gateways' ,
                'newids'   => $this->prefix . 'disabled_payment_gateways' ,
                'type'     => 'multiselect' ,
                'options'  => _sumo_pp_get_active_payment_gateways() ,
                'std'      => array () ,
                'default'  => array () ,
                'desc'     => '' ,
                'desc_tip' => true ,
            ) ,
            array (
                'name'     => __( 'Hide Payment Plans Option for Selected User Role(s)' , $this->text_domain ) ,
                'id'       => $this->prefix . 'hide_payment_plans_only_for' ,
                'newids'   => $this->prefix . 'hide_payment_plans_only_for' ,
                'type'     => 'multiselect' ,
                'options'  => _sumo_pp_get_user_roles( true ) ,
                'std'      => array () ,
                'default'  => array () ,
                'desc'     => '' ,
                'desc_tip' => true ,
            ) ,
            array ( 'type' => 'sectionend' , 'id' => $this->prefix . 'general_settings' ) ,
                ) ) ;
    }

    /**
     * Save the custom options once.
     */
    public function custom_types_add_options() {

        //Backward compatibility
        if ( false === get_option( $this->prefix . 'balance_payment_due' ) ) {
            add_option( $this->prefix . 'pay_balance_after' , get_option( $this->prefix . 'pay_balance_after' ) ) ;
        } else {
            if ( add_option( $this->prefix . 'pay_balance_after' , get_option( $this->prefix . 'balance_payment_due' ) ) ) {
                delete_option( $this->prefix . 'balance_payment_due' ) ;
            }
        }
    }

}

return new SUMO_PP_General_Settings() ;
