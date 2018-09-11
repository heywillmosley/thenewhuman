<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit ; // Exit if accessed directly
}

/**
 * Handle SUMO Payment Plans in My Account page.
 * 
 * @class SUMO_PP_My_Account
 * @category Class
 */
class SUMO_PP_My_Account {

    public static $template_base = SUMO_PP_PLUGIN_TEMPLATE_PATH ;

    /**
     * Init SUMO_PP_My_Account.
     */
    public static function init() {

        //Compatible with Woocommerce v2.6.x and above
        add_filter( 'woocommerce_account_menu_items' , __CLASS__ . '::set_my_account_menu_items' ) ;
        add_action( 'woocommerce_account_sumo-pp-my-payments_endpoint' , __CLASS__ . '::my_payments' ) ;
        add_action( 'woocommerce_account_sumo-pp-view-payment_endpoint' , __CLASS__ . '::view_payment' ) ;

        //Compatible up to Woocommerce v2.5.x
        add_action( 'woocommerce_before_my_account' , array ( __CLASS__ , 'bkd_cmptble_my_payments' ) ) ;
        add_filter( 'wc_get_template' , array ( __CLASS__ , 'bkd_cmptble_view_payment' ) , 10 , 5 ) ;
    }

    /**
     * Get my payments.
     */
    public static function get_payments() {
        global $wp ;

        try {
            $payments = _sumo_pp()->query->get( array (
                'type'       => 'sumo_pp_payments' ,
                'status'     => array_keys( _sumo_pp_get_payment_statuses() ) ,
                'meta_key'   => '_customer_id' ,
                'meta_value' => get_current_user_id() ,
                    ) ) ;

            if ( empty( $payments ) ) {
                throw new Exception( __( "You don't have any payment." , _sumo_pp()->text_domain ) ) ;
            }
            ?>
            <p style="display:inline-table">
                <?php _e( 'Search:' , _sumo_pp()->text_domain ) ?>
                <input id="filter" type="text" style="width: 40%"/>&nbsp;
                <?php _e( 'Page Size:' , _sumo_pp()->text_domain ) ?>
                <input id="change-page-size" type="number" min="5" step="5" value="5" style="width: 25%"/>
            </p>
            <table class="shop_table shop_table_responsive my_account_orders <?php echo _sumo_pp()->prefix . 'footable' ; ?>" data-filter="#filter" data-page-size="5" data-page-previous-text="prev" data-filter-text-only="true" data-page-next-text="next" style="width:100%">
                <thead>
                    <tr>
                        <th class="<?php echo _sumo_pp()->prefix . '-payment-number' ; ?>"><span class="nobr"><?php _e( 'Payment Number' , _sumo_pp()->text_domain ) ; ?></span></th>
                        <th class="<?php echo _sumo_pp()->prefix . '-product-title' ; ?>"><span class="nobr"><?php _e( 'Product Title' , _sumo_pp()->text_domain ) ; ?></span></th>
                        <th class="<?php echo _sumo_pp()->prefix . '-payment-plan' ; ?>"><span class="nobr"><?php _e( 'Payment Plan' , _sumo_pp()->text_domain ) ; ?></span></th>
                        <th class="<?php echo _sumo_pp()->prefix . '-payment-status' ; ?>"><span class="nobr"><?php _e( 'Payment Status' , _sumo_pp()->text_domain ) ; ?></span></th>
                        <th data-sort-ignore="true">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ( $payments as $payment_id ) :
                        $wp->query_vars[ 'sumo-pp-view-payment' ] = $payment_id ;
                        ?>
                        <tr class="<?php echo _sumo_pp()->prefix . '-data' ; ?>">
                            <td class="<?php echo _sumo_pp()->prefix . '-payment-number' ; ?>" data-title="<?php _e( 'Payment Number' , _sumo_pp()->text_domain ) ; ?>">
                                <?php
                                echo '<a href="' . _sumo_pp_get_payment_endpoint_url( $payment_id ) . '">#' . get_post_meta( $payment_id , '_payment_number' , true ) . '</a>' ;
                                ?>
                            </td>
                            <td class="<?php echo _sumo_pp()->prefix . '-product-title' ; ?>" data-title="<?php _e( 'Product Title' , _sumo_pp()->text_domain ) ; ?>">
                                <?php
                                echo _sumo_pp_get_formatted_payment_product_title( $payment_id ) ;
                                ?>
                            </td>
                            <td class="<?php echo _sumo_pp()->prefix . '-payment-plan' ; ?>" data-title="<?php _e( 'Payment Plan' , _sumo_pp()->text_domain ) ; ?>">
                                <?php
                                $payment_type                             = get_post_meta( $payment_id , '_payment_type' , true ) ;

                                if ( 'payment-plans' === $payment_type ) {
                                    echo get_post( get_post_meta( $payment_id , '_plan_id' , true ) )->post_title ;
                                } else {
                                    echo 'N/A' ;
                                }
                                ?>
                            </td>
                            <td class="<?php echo _sumo_pp()->prefix . '-payment-status' ; ?>" data-title="<?php _e( 'Payment Status' , _sumo_pp()->text_domain ) ; ?>">
                                <?php
                                $payment_status = _sumo_pp_get_payment_status( $payment_id ) ;
                                printf( '<mark class="%s"/>%s</mark>' , $payment_status[ 'name' ] , esc_attr( $payment_status[ 'label' ] ) ) ;
                                ?>
                            </td>
                            <td class="<?php echo _sumo_pp()->prefix . '-view-payment' ; ?>">
                                <a href="<?php echo _sumo_pp_get_payment_endpoint_url( $payment_id ) ; ?>" class="button view" data-action="view"><?php _e( 'View' , _sumo_pp()->text_domain ) ; ?></a>
                            </td>
                        </tr>
                    <?php endforeach ; ?>
                </tbody>
            </table>
            <div class="pagination pagination-centered"></div>
            <?php
        } catch ( Exception $e ) {
            ?>
            <div class="<?php echo _sumo_pp()->prefix . '-payment-not-found' ; ?> woocommerce-Message woocommerce-Message--info woocommerce-info">
                <p>
                    <?php echo $e->getMessage() ; ?>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Set our menus under My account menu items
     * @param array $items
     * @return array
     */
    public static function set_my_account_menu_items( $items ) {
        $menu     = array (
            'sumo-pp-my-payments' => apply_filters( 'sumopaymentplans_my_payments_title' , __( 'My Payments' , _sumo_pp()->text_domain ) ) ,
                ) ;
        $position = 2 ;

        $items = array_slice( $items , 0 , $position ) + $menu + array_slice( $items , $position , count( $items ) - 1 ) ;

        return $items ;
    }

    /**
     * Output my payments table.
     */
    public static function my_payments() {
        echo self::get_payments() ;
    }

    /**
     * Output Payment content.
     * @param int $payment_id
     */
    public static function view_payment( $payment_id ) {

        if ( _sumo_pp_payment_exists( $payment_id ) ) {

            _sumo_pp_get_template( 'view-payment.php' , array (
                'payment_id' => absint( $payment_id ) ,
            ) ) ;
        } else {
            // No endpoint found? Default to dashboard.
            wc_get_template( 'myaccount/dashboard.php' , array (
                'current_user' => get_user_by( 'id' , get_current_user_id() ) ,
            ) ) ;
        }
    }

    /**
     * Output my payments table up to Woocommerce v2.5.x
     */
    public static function bkd_cmptble_my_payments() {

        if ( _sumo_pp_is_wc_version( '<' , '2.6' ) ) {
            echo '<h2>' . apply_filters( 'sumopaymentplans_my_payments_title' , __( 'My Payments' , _sumo_pp()->text_domain ) ) . '</h2>' ;
            echo self::get_payments() ;
        }
    }

    /**
     * Output payment content up to Woocommerce v2.5.x
     * @global object $wp
     * @param string $located
     * @param string $template_name
     * @param array $args
     * @param string $template_path
     * @param string $default_path
     * @return string
     */
    public static function bkd_cmptble_view_payment( $located , $template_name , $args , $template_path , $default_path ) {
        global $wp ;

        if ( _sumo_pp_is_wc_version( '<' , '2.6' ) && isset( $_GET[ 'payment-id' ] ) && _sumo_pp_payment_exists( $_GET[ 'payment-id' ] ) ) {

            $wp->query_vars[ 'sumo-pp-view-payment' ] = absint( $_GET[ 'payment-id' ] ) ;

            return self::$template_base . 'view-payment.php' ;
        }
        return $located ;
    }

}

SUMO_PP_My_Account::init() ;
