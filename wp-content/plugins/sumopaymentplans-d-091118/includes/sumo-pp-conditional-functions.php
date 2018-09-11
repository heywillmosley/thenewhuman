<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit ; // Exit if accessed directly
}

/**
 * Check the currently installed WC version
 * @param string $comparison_opr The possible operators are: <, lt, <=, le, >, gt, >=, ge, ==, =, eq, !=, <>, ne respectively.
  This parameter is case-sensitive, values should be lowercase
 * @param string $version
 * @return boolean
 */
function _sumo_pp_is_wc_version( $comparison_opr , $version ) {

    if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION , $version , $comparison_opr ) ) {
        return true ;
    }
    return false ;
}

function _sumo_pp_cart_has_payment_items() {
    if ( is_admin() ) {
        return false ;
    }

    $cart_data     = SUMO_PP_Frontend::get_cart_session() ;
    $payment_items = array_keys( $cart_data ) ;

    if ( empty( $payment_items ) ) {
        return false ;
    }

    foreach ( ( array ) WC()->cart->cart_contents as $item_key => $item ) {
        if ( ! isset( $item[ 'product_id' ] ) ) {
            continue ;
        }
        $item = $item[ 'variation_id' ] > 0 ? $item[ 'variation_id' ] : $item[ 'product_id' ] ;

        if ( in_array( $item , $payment_items ) ) {
            return true ;
        }
    }
    return false ;
}

function _sumo_pp_is_payment_order( $order ) {
    if (
            $order instanceof SUMO_PP_Order ||
            ($order = _sumo_pp_get_order( $order ))
    ) {
        $prefix = _sumo_pp()->prefix ;

        return 'yes' === get_post_meta( $order->order_id , "is{$prefix}order" , true ) ;
    }
    return false ;
}
