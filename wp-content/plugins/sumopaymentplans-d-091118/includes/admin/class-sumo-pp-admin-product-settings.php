<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit ; // Exit if accessed directly
}

/**
 * Handle SUMO Payment Plans admin settings in product backend. 
 * 
 * @class SUMO_PP_Admin_Product_Settings
 * @category Class
 */
class SUMO_PP_Admin_Product_Settings {

    protected static $fields = array (
        'enable_sumopaymentplans'        => 'checkbox' ,
        'payment_type'                   => 'select' ,
        'apply_global_settings'          => 'checkbox' ,
        'force_deposit'                  => 'checkbox' ,
        'deposit_type'                   => 'select' ,
        'deposit_price_type'             => 'select' ,
        'pay_balance_type'               => 'select' ,
        'pay_balance_after'              => 'number' ,
        'pay_balance_before'             => 'datepicker' ,
        'pay_balance_before_booked_date' => 'number' ,
        'set_expired_deposit_payment_as' => 'select' ,
        'fixed_deposit_price'            => 'price' ,
        'fixed_deposit_percent'          => 'text' ,
        'min_deposit'                    => 'text' ,
        'max_deposit'                    => 'text' ,
        'selected_plans'                 => 'multiselect' ,
            ) ;

    /**
     * Init Payment Plans Product Settings.
     */
    public static function init() {
        add_action( 'woocommerce_product_options_general_product_data' , __CLASS__ . '::get_product_settings' ) ;
        add_action( 'woocommerce_product_after_variable_attributes' , __CLASS__ . '::get_variation_product_settings' , 10 , 3 ) ;
        add_action( 'woocommerce_process_product_meta' , __CLASS__ . '::save_payment_plans_product_data' ) ;
        add_action( 'woocommerce_save_product_variation' , __CLASS__ . '::save_payment_plans_variation_data' , 10 , 2 ) ;
    }

    public static function get_fields() {
        return self::$fields ;
    }

    /**
     * Get payment plans product setting fields.
     */
    public static function get_product_settings() {
        global $post ;

        $product = wc_get_product( $post ) ;

        if ( ! $product || in_array( $product->get_type() , array ( 'variable' ) ) ) {
            return ;
        }

        woocommerce_wp_checkbox( array (
            'label'    => __( 'Enable SUMO Payment Plans' , _sumo_pp()->text_domain ) ,
            'id'       => _sumo_pp()->prefix . 'enable_sumopaymentplans' ,
            'desc_tip' => __( 'Enabling this option allows you to configure the product to accept product booking by paying a deposit amount / purchase the product by choosing from the available payment plans' , _sumo_pp()->text_domain )
        ) ) ;
        woocommerce_wp_select( array (
            'label'         => __( 'Payment Type' , _sumo_pp()->text_domain ) ,
            'id'            => _sumo_pp()->prefix . 'payment_type' ,
            'wrapper_class' => _sumo_pp()->prefix . 'fields' ,
            'options'       => array (
                'pay-in-deposit' => __( 'Pay a Deposit Amount' , _sumo_pp()->text_domain ) ,
                'payment-plans'  => __( 'Pay with Payment Plans' , _sumo_pp()->text_domain ) ,
            ) ,
        ) ) ;
        woocommerce_wp_checkbox( array (
            'label'         => __( 'Apply Global Level Settings' , _sumo_pp()->text_domain ) ,
            'id'            => _sumo_pp()->prefix . 'apply_global_settings' ,
            'wrapper_class' => _sumo_pp()->prefix . 'fields' ,
            'desc_tip'      => __( 'When enabled, the settings for SUMO Payment Plans will apply from global level' , _sumo_pp()->text_domain ) ,
        ) ) ;
        woocommerce_wp_checkbox( array (
            'label'         => __( 'Force Deposit/Payment Plans' , _sumo_pp()->text_domain ) ,
            'id'            => _sumo_pp()->prefix . 'force_deposit' ,
            'wrapper_class' => _sumo_pp()->prefix . 'fields' ,
            'desc_tip'      => __( 'When enabled, the user will be forced to pay a deposit amount' , _sumo_pp()->text_domain ) ,
        ) ) ;
        woocommerce_wp_select( array (
            'label'         => __( 'Deposit Type' , _sumo_pp()->text_domain ) ,
            'id'            => _sumo_pp()->prefix . 'deposit_type' ,
            'wrapper_class' => _sumo_pp()->prefix . 'fields' ,
            'options'       => array (
                'pre-defined'  => __( 'Predefined Deposit Amount' , _sumo_pp()->text_domain ) ,
                'user-defined' => __( 'User Defined Deposit Amount' , _sumo_pp()->text_domain ) ,
            ) ,
        ) ) ;
        woocommerce_wp_select( array (
            'label'         => __( 'Deposit Price Type' , _sumo_pp()->text_domain ) ,
            'id'            => _sumo_pp()->prefix . 'deposit_price_type' ,
            'wrapper_class' => _sumo_pp()->prefix . 'fields' ,
            'options'       => array (
                'fixed-price'              => __( 'Fixed Price' , _sumo_pp()->text_domain ) ,
                'percent-of-product-price' => __( 'Percentage of Product Price' , _sumo_pp()->text_domain ) ,
            ) ,
        ) ) ;
        woocommerce_wp_text_input( array (
            'label'         => __( 'Deposit Amount' , _sumo_pp()->text_domain ) ,
            'id'            => _sumo_pp()->prefix . 'fixed_deposit_price' ,
            'wrapper_class' => _sumo_pp()->prefix . 'fields' ,
            'style'         => 'width:20%;' ,
            'data_type'     => 'price' ,
        ) ) ;
        woocommerce_wp_text_input( array (
            'label'             => __( 'Deposit Percentage' , _sumo_pp()->text_domain ) ,
            'id'                => _sumo_pp()->prefix . 'fixed_deposit_percent' ,
            'wrapper_class'     => _sumo_pp()->prefix . 'fields' ,
            'style'             => 'width:20%;' ,
            'type'              => 'number' ,
            'custom_attributes' => array (
                'step' => '0.01' ,
            ) ,
        ) ) ;
        woocommerce_wp_text_input( array (
            'label'             => __( 'Minimum Deposit(%)' , _sumo_pp()->text_domain ) ,
            'id'                => _sumo_pp()->prefix . 'min_deposit' ,
            'wrapper_class'     => _sumo_pp()->prefix . 'fields' ,
            'style'             => 'width:20%;' ,
            'type'              => 'number' ,
            'custom_attributes' => array (
                'step' => '0.01' ,
            ) ,
        ) ) ;
        woocommerce_wp_text_input( array (
            'label'             => __( 'Maximum Deposit(%)' , _sumo_pp()->text_domain ) ,
            'id'                => _sumo_pp()->prefix . 'max_deposit' ,
            'wrapper_class'     => _sumo_pp()->prefix . 'fields' ,
            'style'             => 'width:20%;' ,
            'type'              => 'number' ,
            'custom_attributes' => array (
                'step' => '0.01' ,
            ) ,
        ) ) ;
        ?>        
        <p class="form-field <?php echo _sumo_pp()->prefix . 'pay_balance_type_field' . ' ' . _sumo_pp()->prefix . 'fields' ; ?>">
            <label for="<?php echo _sumo_pp()->prefix . 'pay_balance_type' ; ?>"><?php _e( 'Deposit Balance Payment Due Date' , _sumo_pp()->text_domain ) ; ?></label>
            <select id="<?php echo _sumo_pp()->prefix . "pay_balance_type" ; ?>" name="<?php echo _sumo_pp()->prefix . "pay_balance_type" ; ?>">
                <option value="after" <?php selected( true , 'after' === get_post_meta( $post->ID , _sumo_pp()->prefix . 'pay_balance_type' , true ) ) ?>><?php _e( 'After' , _sumo_pp()->text_domain ) ?></option> 
                <option value="before" <?php selected( true , 'before' === get_post_meta( $post->ID , _sumo_pp()->prefix . 'pay_balance_type' , true ) ) ?>><?php _e( 'Before' , _sumo_pp()->text_domain ) ?></option>
            </select>
            <span>
                <input type="number" id="<?php echo _sumo_pp()->prefix . "pay_balance_after" ; ?>" name="<?php echo _sumo_pp()->prefix . "pay_balance_after" ; ?>" value="<?php echo '' === get_post_meta( $post->ID , _sumo_pp()->prefix . 'balance_payment_due' , true ) ? get_post_meta( $post->ID , _sumo_pp()->prefix . 'pay_balance_after' , true ) : get_post_meta( $post->ID , _sumo_pp()->prefix . 'balance_payment_due' , true ) ; ?>" style="width:20%;">
                <span class="description"><?php _e( 'day(s) from the date of deposit payment' , _sumo_pp()->text_domain ) ?></span>
            </span>
            <span>
                <input type="text" placeholder="<?php esc_attr_e( 'YYYY-MM-DD' , _sumo_pp()->text_domain ) ?>" id="<?php echo _sumo_pp()->prefix . "pay_balance_before" ; ?>" name="<?php echo _sumo_pp()->prefix . "pay_balance_before" ; ?>" value="<?php echo get_post_meta( $post->ID , _sumo_pp()->prefix . 'pay_balance_before' , true ) ; ?>" style="width:20%;">
            </span>
            <?php if ( class_exists( 'SUMO_Bookings' ) ) { ?>
                <span>
                    <input type="number" min="0" id="<?php echo _sumo_pp()->prefix . "pay_balance_before_booked_date" ; ?>" name="<?php echo _sumo_pp()->prefix . "pay_balance_before_booked_date" ; ?>" value="<?php echo get_post_meta( $post->ID , _sumo_pp()->prefix . 'pay_balance_before_booked_date' , true ) ; ?>" style="width:20%;display: none;">
                    <span class="description"><?php _e( 'day(s) of booking start date' , _sumo_pp()->text_domain ) ?></span>
                </span>
            <?php } ?>
        </p>
        <p class="form-field <?php echo _sumo_pp()->prefix . 'set_expired_deposit_payment_as_field' . ' ' . _sumo_pp()->prefix . 'fields' ; ?>">
            <label for="<?php echo _sumo_pp()->prefix . 'set_expired_deposit_payment_as' ; ?>"><?php _e( 'After Balance Payment Due Date' , _sumo_pp()->text_domain ) ; ?></label>
            <select id="<?php echo _sumo_pp()->prefix . "set_expired_deposit_payment_as" ; ?>" name="<?php echo _sumo_pp()->prefix . "set_expired_deposit_payment_as" ; ?>">
                <option value="normal" <?php selected( true , 'normal' === get_post_meta( $post->ID , _sumo_pp()->prefix . 'set_expired_deposit_payment_as' , true ) ) ?>><?php _e( 'Disable SUMO Payment Plans' , _sumo_pp()->text_domain ) ?></option> 
                <option value="out-of-stock" <?php selected( true , 'out-of-stock' === get_post_meta( $post->ID , _sumo_pp()->prefix . 'set_expired_deposit_payment_as' , true ) ) ?>><?php _e( 'Set Product as Out of Stock' , _sumo_pp()->text_domain ) ?></option>
            </select>
        </p>
        <p class="form-field <?php echo _sumo_pp()->prefix . 'selected_plans_field' . ' ' . _sumo_pp()->prefix . 'fields' ; ?>">
            <label for="<?php echo _sumo_pp()->prefix . 'selected_plans' ; ?>"><?php _e( 'Select Plans' , _sumo_pp()->text_domain ) ; ?></label>
            <select multiple="multiple" id="<?php echo _sumo_pp()->prefix . 'selected_plans' ; ?>" name="<?php echo _sumo_pp()->prefix . 'selected_plans[]' ; ?>" style="width:50%">
                <?php
                $option_value = ( array ) get_post_meta( $post->ID , _sumo_pp()->prefix . 'selected_plans' , true ) ;

                foreach ( _sumo_pp_get_payment_plan_names() as $key => $val ) {
                    ?>
                    <option value="<?php echo esc_attr( $key ) ; ?>" <?php selected( in_array( $key , $option_value ) , true ) ; ?>><?php echo $val ?></option>
                <?php } ?>
            </select>
        </p>
        <?php
    }

    /**
     * Get payment plans variation product setting fields.
     * @param int $loop
     * @param mixed $variation_data
     * @param object $variation The Variation post ID
     */
    public static function get_variation_product_settings( $loop , $variation_data , $variation ) {

        woocommerce_wp_checkbox( array (
            'label'    => __( 'Enable SUMO Payment Plans' , _sumo_pp()->text_domain ) ,
            'id'       => _sumo_pp()->prefix . "enable_sumopaymentplans{$loop}" ,
            'name'     => _sumo_pp()->prefix . "enable_sumopaymentplans[{$loop}]" ,
            'value'    => get_post_meta( $variation->ID , _sumo_pp()->prefix . 'enable_sumopaymentplans' , true ) ,
            'desc_tip' => __( 'Enabling this option allows you to configure the product to accept product booking by paying a deposit amount / purchase the product by choosing from the available payment plans' , _sumo_pp()->text_domain )
        ) ) ;
        woocommerce_wp_select( array (
            'label'         => __( 'Payment Type' , _sumo_pp()->text_domain ) ,
            'id'            => _sumo_pp()->prefix . "payment_type{$loop}" ,
            'name'          => _sumo_pp()->prefix . "payment_type[{$loop}]" ,
            'wrapper_class' => _sumo_pp()->prefix . "fields{$loop}" ,
            'options'       => array (
                'pay-in-deposit' => __( 'Pay a Deposit Amount' , _sumo_pp()->text_domain ) ,
                'payment-plans'  => __( 'Pay with Payment Plans' , _sumo_pp()->text_domain ) ,
            ) ,
            'value'         => get_post_meta( $variation->ID , _sumo_pp()->prefix . 'payment_type' , true ) ,
        ) ) ;
        woocommerce_wp_checkbox( array (
            'label'         => __( 'Apply Global Level Settings' , _sumo_pp()->text_domain ) ,
            'id'            => _sumo_pp()->prefix . "apply_global_settings{$loop}" ,
            'name'          => _sumo_pp()->prefix . "apply_global_settings[{$loop}]" ,
            'wrapper_class' => _sumo_pp()->prefix . "fields{$loop}" ,
            'value'         => get_post_meta( $variation->ID , _sumo_pp()->prefix . 'apply_global_settings' , true ) ,
            'desc_tip'      => __( 'When enabled, the settings for SUMO Payment Plans will apply from global level' , _sumo_pp()->text_domain ) ,
        ) ) ;
        woocommerce_wp_checkbox( array (
            'label'         => __( 'Force Deposit/Payment Plans' , _sumo_pp()->text_domain ) ,
            'id'            => _sumo_pp()->prefix . "force_deposit{$loop}" ,
            'name'          => _sumo_pp()->prefix . "force_deposit[{$loop}]" ,
            'wrapper_class' => _sumo_pp()->prefix . "fields{$loop}" ,
            'value'         => get_post_meta( $variation->ID , _sumo_pp()->prefix . 'force_deposit' , true ) ,
            'desc_tip'      => __( 'When enabled, the user will be forced to pay a deposit amount' , _sumo_pp()->text_domain ) ,
        ) ) ;
        woocommerce_wp_select( array (
            'label'         => __( 'Deposit Type' , _sumo_pp()->text_domain ) ,
            'id'            => _sumo_pp()->prefix . "deposit_type{$loop}" ,
            'name'          => _sumo_pp()->prefix . "deposit_type[{$loop}]" ,
            'wrapper_class' => _sumo_pp()->prefix . "fields{$loop}" ,
            'options'       => array (
                'pre-defined'  => __( 'Predefined Deposit Amount' , _sumo_pp()->text_domain ) ,
                'user-defined' => __( 'User Defined Deposit Amount' , _sumo_pp()->text_domain ) ,
            ) ,
            'value'         => get_post_meta( $variation->ID , _sumo_pp()->prefix . 'deposit_type' , true ) ,
        ) ) ;
        woocommerce_wp_select( array (
            'label'         => __( 'Deposit Price Type' , _sumo_pp()->text_domain ) ,
            'id'            => _sumo_pp()->prefix . "deposit_price_type{$loop}" ,
            'name'          => _sumo_pp()->prefix . "deposit_price_type[{$loop}]" ,
            'wrapper_class' => _sumo_pp()->prefix . "fields{$loop}" ,
            'options'       => array (
                'fixed-price'              => __( 'Fixed Price' , _sumo_pp()->text_domain ) ,
                'percent-of-product-price' => __( 'Percentage of Product Price' , _sumo_pp()->text_domain ) ,
            ) ,
            'value'         => get_post_meta( $variation->ID , _sumo_pp()->prefix . 'deposit_price_type' , true ) ,
        ) ) ;
        woocommerce_wp_text_input( array (
            'label'         => __( 'Deposit Amount' , _sumo_pp()->text_domain ) ,
            'id'            => _sumo_pp()->prefix . "fixed_deposit_price{$loop}" ,
            'name'          => _sumo_pp()->prefix . "fixed_deposit_price[{$loop}]" ,
            'wrapper_class' => _sumo_pp()->prefix . "fields{$loop}" ,
            'style'         => 'width:20%;' ,
            'data_type'     => 'price' ,
            'value'         => get_post_meta( $variation->ID , _sumo_pp()->prefix . 'fixed_deposit_price' , true ) ,
        ) ) ;
        woocommerce_wp_text_input( array (
            'label'             => __( 'Deposit Percentage' , _sumo_pp()->text_domain ) ,
            'id'                => _sumo_pp()->prefix . "fixed_deposit_percent{$loop}" ,
            'name'              => _sumo_pp()->prefix . "fixed_deposit_percent[{$loop}]" ,
            'wrapper_class'     => _sumo_pp()->prefix . "fields{$loop}" ,
            'style'             => 'width:20%;' ,
            'type'              => 'number' ,
            'custom_attributes' => array (
                'step' => '0.01' ,
            ) ,
            'value'             => get_post_meta( $variation->ID , _sumo_pp()->prefix . 'fixed_deposit_percent' , true ) ,
        ) ) ;
        woocommerce_wp_text_input( array (
            'label'             => __( 'Minimum Deposit(%)' , _sumo_pp()->text_domain ) ,
            'id'                => _sumo_pp()->prefix . "min_deposit{$loop}" ,
            'name'              => _sumo_pp()->prefix . "min_deposit[{$loop}]" ,
            'wrapper_class'     => _sumo_pp()->prefix . "fields{$loop}" ,
            'style'             => 'width:20%;' ,
            'type'              => 'number' ,
            'custom_attributes' => array (
                'step' => '0.01' ,
            ) ,
            'value'             => get_post_meta( $variation->ID , _sumo_pp()->prefix . 'min_deposit' , true ) ,
        ) ) ;
        woocommerce_wp_text_input( array (
            'label'             => __( 'Maximum Deposit(%)' , _sumo_pp()->text_domain ) ,
            'id'                => _sumo_pp()->prefix . "max_deposit{$loop}" ,
            'name'              => _sumo_pp()->prefix . "max_deposit[{$loop}]" ,
            'wrapper_class'     => _sumo_pp()->prefix . "fields{$loop}" ,
            'style'             => 'width:20%;' ,
            'type'              => 'number' ,
            'custom_attributes' => array (
                'step' => '0.01' ,
            ) ,
            'value'             => get_post_meta( $variation->ID , _sumo_pp()->prefix . 'max_deposit' , true ) ,
        ) ) ;
        ?>       
        <p class="form-field <?php echo _sumo_pp()->prefix . 'pay_balance_type_field' . ' ' . _sumo_pp()->prefix . "fields{$loop}" ; ?>">
            <label for="<?php echo _sumo_pp()->prefix . 'pay_balance_type' ; ?>"><?php _e( 'Deposit Balance Payment Due Date' , _sumo_pp()->text_domain ) ; ?></label>
            <select id="<?php echo _sumo_pp()->prefix . "pay_balance_type{$loop}" ; ?>" name="<?php echo _sumo_pp()->prefix . "pay_balance_type[{$loop}]" ; ?>">
                <option value="after" <?php selected( true , 'after' === get_post_meta( $variation->ID , _sumo_pp()->prefix . 'pay_balance_type' , true ) ) ?>><?php _e( 'After' , _sumo_pp()->text_domain ) ?></option> 
                <option value="before" <?php selected( true , 'before' === get_post_meta( $variation->ID , _sumo_pp()->prefix . 'pay_balance_type' , true ) ) ?>><?php _e( 'Before' , _sumo_pp()->text_domain ) ?></option>
            </select>
            <span>
                <input type="number" id="<?php echo _sumo_pp()->prefix . "pay_balance_after{$loop}" ; ?>" name="<?php echo _sumo_pp()->prefix . "pay_balance_after[{$loop}]" ; ?>" value="<?php echo '' === get_post_meta( $variation->ID , _sumo_pp()->prefix . 'balance_payment_due' , true ) ? get_post_meta( $variation->ID , _sumo_pp()->prefix . 'pay_balance_after' , true ) : get_post_meta( $variation->ID , _sumo_pp()->prefix . 'balance_payment_due' , true ) ; ?>" style="width:20%;">
                <span class="description"><?php _e( 'day(s) from the date of deposit payment' , _sumo_pp()->text_domain ) ?></span>
            </span>
            <span>
                <input type="text" placeholder="<?php esc_attr_e( 'YYYY-MM-DD' , _sumo_pp()->text_domain ) ?>" id="<?php echo _sumo_pp()->prefix . "pay_balance_before{$loop}" ; ?>" name="<?php echo _sumo_pp()->prefix . "pay_balance_before[{$loop}]" ; ?>" value="<?php echo get_post_meta( $variation->ID , _sumo_pp()->prefix . 'pay_balance_before' , true ) ; ?>" style="width:20%;">
            </span>
        </p>
        <p class="form-field <?php echo _sumo_pp()->prefix . 'set_expired_deposit_payment_as_field' . ' ' . _sumo_pp()->prefix . "fields{$loop}" ; ?>">
            <label for="<?php echo _sumo_pp()->prefix . 'set_expired_deposit_payment_as' ; ?>"><?php _e( 'After Balance Payment Due Date' , _sumo_pp()->text_domain ) ; ?></label>
            <select id="<?php echo _sumo_pp()->prefix . "set_expired_deposit_payment_as{$loop}" ; ?>" name="<?php echo _sumo_pp()->prefix . "set_expired_deposit_payment_as[{$loop}]" ; ?>">
                <option value="normal" <?php selected( true , 'normal' === get_post_meta( $variation->ID , _sumo_pp()->prefix . 'set_expired_deposit_payment_as' , true ) ) ?>><?php _e( 'Disable SUMO Payment Plans' , _sumo_pp()->text_domain ) ?></option> 
                <option value="out-of-stock" <?php selected( true , 'out-of-stock' === get_post_meta( $variation->ID , _sumo_pp()->prefix . 'set_expired_deposit_payment_as' , true ) ) ?>><?php _e( 'Set Product as Out of Stock' , _sumo_pp()->text_domain ) ?></option>
            </select>
        </p>
        <p class="form-field <?php echo _sumo_pp()->prefix . 'selected_plans_field' . ' ' . _sumo_pp()->prefix . "fields{$loop}" ; ?>">
            <label for="<?php echo _sumo_pp()->prefix . 'selected_plans' ; ?>"><?php _e( 'Select Plans' , _sumo_pp()->text_domain ) ; ?></label>
            <select multiple="multiple" id="<?php echo _sumo_pp()->prefix . "selected_plans{$loop}" ; ?>" name="<?php echo _sumo_pp()->prefix . "selected_plans[{$loop}][]" ; ?>" style="width: 50%;">
                <?php
                $option_value = ( array ) get_post_meta( $variation->ID , _sumo_pp()->prefix . 'selected_plans' , true ) ;

                foreach ( _sumo_pp_get_payment_plan_names() as $key => $val ) {
                    ?>
                    <option value="<?php echo esc_attr( $key ) ; ?>" <?php selected( in_array( $key , $option_value ) , true ) ; ?>><?php echo $val ?></option>
                <?php } ?>
            </select>
        </p>
        <?php
    }

    /**
     * Save payment plans product data.
     * @param int $product_id The Product post ID
     */
    public static function save_payment_plans_product_data( $product_id ) {
        self::save_meta( $product_id ) ;
    }

    /**
     * Save payment plans variation product data.
     * @param int $variation_id The Variation post ID
     * @param int $i
     */
    public static function save_payment_plans_variation_data( $variation_id , $i ) {
        self::save_meta( '' , $variation_id , $i ) ;
    }

    /**
     * Save payment plans product meta's.
     * @param int $product_id The Product post ID
     * @param int $variation_id The Variation post ID
     * @param int $i The Variation loop
     */
    public static function save_meta( $product_id , $variation_id = '' , $i = '' ) {

        foreach ( self::$fields as $field_name => $type ) {
            $meta_key         = _sumo_pp()->prefix . $field_name ;
            $posted_meta_data = isset( $_POST[ "$meta_key" ] ) ? $_POST[ "$meta_key" ] : '' ;

            if ( $variation_id > 0 ) {
                if ( 'checkbox' === $type ) {
                    delete_post_meta( $variation_id , "$meta_key" ) ;
                }
                if ( isset( $posted_meta_data[ $i ] ) ) {
                    if ( 'price' === $type ) {
                        $posted_meta_data[ $i ] = wc_format_decimal( $posted_meta_data[ $i ] ) ;
                    }

                    update_post_meta( $variation_id , "$meta_key" , wc_clean( $posted_meta_data[ $i ] ) ) ;

                    //backward compatible
                    if ( $posted_meta_data[ $i ] && 'pay_balance_after' === $field_name ) {
                        delete_post_meta( $variation_id , _sumo_pp()->prefix . 'balance_payment_due' ) ;
                    }
                }
            } else {
                if ( 'price' === $type ) {
                    $posted_meta_data = wc_format_decimal( $posted_meta_data ) ;
                }
                update_post_meta( $product_id , "$meta_key" , wc_clean( $posted_meta_data ) ) ;

                //backward compatible
                if ( $posted_meta_data && 'pay_balance_after' === $field_name ) {
                    delete_post_meta( $product_id , _sumo_pp()->prefix . 'balance_payment_due' ) ;
                }
            }
        }
    }

}

SUMO_PP_Admin_Product_Settings::init() ;
