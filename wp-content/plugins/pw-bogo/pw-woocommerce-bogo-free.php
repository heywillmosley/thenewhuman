<?php
/**
 * Plugin Name: PW WooCommerce BOGO
 * Plugin URI: https://pimwick.com/pw-bogo
 * Description: Makes Buy One, Get One promotions so easy!
 * Version: 2.60
 * Author: Pimwick, LLC
 * Author URI: https://pimwick.com/pw-bogo
 *
 * WC requires at least: 2.6.13
 * WC tested up to: 3.3.2
 *
 * Copyright: © Pimwick, LLC
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

// Verify this isn't called directly.
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

if ( ! class_exists( 'PW_BOGO' ) ) :

final class PW_BOGO {

    private $use_coupons = true;
    private $active_bogos_cache = null;

    function __construct() {
        add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
        add_action( 'woocommerce_init', array( $this, 'woocommerce_init' ) );
    }

    function plugins_loaded() {
        load_plugin_textdomain( 'pimwick', false, basename( dirname( __FILE__ ) ) . '/languages' );
    }

    function woocommerce_init() {

        defined( 'PW_BOGO_LICENSE_PRODUCT_NAME' ) or define( 'PW_BOGO_LICENSE_PRODUCT_NAME', 'PW BOGO' );
        defined( 'PW_BOGO_LICENSE_OPTION_NAME' ) or define( 'PW_BOGO_LICENSE_OPTION_NAME', 'pw-bogo-license' );

        $this->use_coupons = boolval( get_option( 'pw_bogo_use_coupons', true ) );

        // If WooCommerce does not have Coupons enabled, we can't utilize them.
        if ( 'no' === get_option( 'woocommerce_enable_coupons', 'no' ) ) {
            $this->use_coupons = false;
        }

        add_action( 'init', array( $this, 'register_post_types' ), 9 );

        if ( is_admin() ) {

            require( 'includes/class-pimwick-license.php' );

            add_action( 'admin_menu', array( $this, 'admin_menu' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
            add_filter( 'manage_edit-pw_bogo_columns', array( $this, 'edit_pw_bogo_columns' ) );
            add_action( 'manage_pw_bogo_posts_custom_column', array( $this, 'pw_bogo_posts_custom_column' ) );
            add_action( 'post_row_actions', array( $this, 'post_row_actions' ), 10, 2 );
            add_action( 'add_meta_boxes_pw_bogo', array( $this, 'meta_boxes' ) );
            add_action( 'save_post', array( $this, 'save_post' ), 1, 2 );
            add_action( 'wp_ajax_pw-bogo-activation', array( $this, 'ajax_activation' ) );
            add_action( 'pre_get_posts', array( $this, 'pre_get_posts') );
            add_filter( 'wp_count_posts', array( $this, 'wp_count_posts' ), 10, 3 );
            add_filter( 'woocommerce_order_get_items', array( $this, 'woocommerce_order_get_items' ), 10, 2 );
        }

        add_action( 'woocommerce_after_calculate_totals', array( $this, 'woocommerce_after_calculate_totals' ) );

        if ( true === $this->use_coupons ) {
            add_filter( 'woocommerce_get_shop_coupon_data', array( $this, 'woocommerce_get_shop_coupon_data' ), 10, 2 );
            add_action( 'woocommerce_add_to_cart', array( $this, 'maybe_apply_bogo_coupon' ) );
            add_action( 'woocommerce_check_cart_items', array( $this, 'maybe_apply_bogo_coupon' ) );
            add_filter( 'woocommerce_coupon_message', array( $this, 'woocommerce_coupon_message' ), 10, 3 );
            add_filter( 'woocommerce_coupon_is_valid', array( $this, 'woocommerce_coupon_is_valid' ), 99, 2 );
            add_filter( 'woocommerce_cart_totals_coupon_label', array( $this, 'woocommerce_cart_totals_coupon_label' ), 10, 2 );
            add_action( 'woocommerce_applied_coupon', array( $this, 'woocommerce_applied_coupon' ), 99, 1 );

            if ( $this->wc_min_version( '3.0' ) ) {
                add_action( 'woocommerce_new_order_item', array( $this, 'woocommerce_new_order_item' ), 10, 3 );
            } else {
                add_action( 'woocommerce_order_add_coupon', array( $this, 'woocommerce_order_add_coupon' ), 10, 5 );
            }
        } else {
            add_action( 'woocommerce_cart_calculate_fees' , array( $this, 'woocommerce_cart_calculate_fees' ) );
            add_action( 'woocommerce_cart_contents_total' , array( $this, 'woocommerce_cart_contents_total' ) );
        }
    }

    function register_post_types() {
        if ( post_type_exists('pw_bogo') ) {
            return;
        }

        $labels = array(
            'name'                  => _x( 'PW BOGO', 'Post Type General Name', 'pimwick' ),
            'singular_name'         => _x( 'PW BOGO', 'Post Type Singular Name', 'pimwick' ),
            'menu_name'             => __( 'PW BOGO', 'pimwick' ),
            'name_admin_bar'        => __( 'PW BOGO', 'pimwick' ),
            'archives'              => __( 'PW BOGO Archives', 'pimwick' ),
            'parent_item_colon'     => __( 'Parent PW BOGO:', 'pimwick' ),
            'all_items'             => __( 'PW BOGO', 'pimwick' ),
            'add_new_item'          => __( 'Add New PW BOGO', 'pimwick' ),
            'add_new'               => __( 'Create New PW BOGO', 'pimwick' ),
            'new_item'              => __( 'New PW BOGO', 'pimwick' ),
            'edit_item'             => __( 'Edit PW BOGO', 'pimwick' ),
            'update_item'           => __( 'Update PW BOGO', 'pimwick' ),
            'view_item'             => __( 'View PW BOGO', 'pimwick' ),
            'search_items'          => __( 'Search PW BOGO', 'pimwick' ),
            'not_found'             => __( 'Not found', 'pimwick' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'pimwick' ),
            'featured_image'        => __( 'PW BOGO Logo', 'pimwick' ),
            'set_featured_image'    => __( 'Set PW BOGO Logo', 'pimwick' ),
            'remove_featured_image' => __( 'Remove PW BOGO Logo', 'pimwick' ),
            'use_featured_image'    => __( 'Use as PW BOGO Logo', 'pimwick' ),
            'insert_into_item'      => __( 'Insert into item', 'pimwick' ),
            'uploaded_to_this_item' => __( 'Uploaded to this item', 'pimwick' ),
            'items_list'            => __( 'PW BOGO list', 'pimwick' ),
            'items_list_navigation' => __( 'PW BOGO list navigation', 'pimwick' ),
            'filter_items_list'     => __( 'Filter PW BOGO list', 'pimwick' ),
        );

        $args = array(
            'label'                 => __( 'PW BOGO', 'pimwick' ),
            'description'           => __( 'PW BOGO', 'pimwick' ),
            'labels'                => $labels,
            'supports'              => array( 'title' ),
            'show_ui'               => true,
            'show_in_menu'          => current_user_can( 'manage_woocommerce' ) ? 'pimwick' : false,
            'has_archive'           => true
        );

        register_post_type( 'pw_bogo', $args );
    }

    public static function version() {
        $data = get_plugin_data( __FILE__ );
        return $data['Version'];
    }

    public static function wc_min_version( $version ) {
        return version_compare( WC()->version, $version, ">=" );
    }

    function meta_boxes( $post ) {
        $pimwick_license = new Pimwick_License( PW_BOGO_LICENSE_PRODUCT_NAME, PW_BOGO_LICENSE_OPTION_NAME );

        require( 'ui/meta-boxes.php' );

        add_meta_box( 'pw-bogo-about', __( 'About', 'pimwick' ), 'PW_BOGO_Meta_Boxes::about', 'pw_bogo', 'side', 'default' );

        if ( $pimwick_license->premium() ) {
            add_meta_box( 'pw-bogo-discount', __( 'Discount', 'pimwick' ), 'PW_BOGO_Meta_Boxes::discount', 'pw_bogo', 'normal', 'default' );
            add_meta_box( 'pw-bogo-products', __( 'Eligible Products', 'pimwick' ), 'PW_BOGO_Meta_Boxes::products', 'pw_bogo', 'normal', 'default' );
            add_meta_box( 'pw-bogo-discounted-products', __( 'Discounted Products', 'pimwick' ), 'PW_BOGO_Meta_Boxes::discounted_products', 'pw_bogo', 'normal', 'default' );
            add_meta_box( 'pw-bogo-dates', __( 'Dates', 'pimwick' ), 'PW_BOGO_Meta_Boxes::dates', 'pw_bogo', 'normal', 'default' );
            add_meta_box( 'pw-bogo-restrictions', __( 'Restrictions', 'pimwick' ), 'PW_BOGO_Meta_Boxes::restrictions', 'pw_bogo', 'normal', 'default' );
        } else {
            add_meta_box( 'pw-bogo-activation', __( 'Activation Required', 'pimwick' ), 'PW_BOGO_Meta_Boxes::activation', 'pw_bogo', 'normal', 'default' );

        }
    }

    function admin_menu() {
        if ( empty ( $GLOBALS['admin_page_hooks']['pimwick'] ) ) {
            add_menu_page(
                __( 'PW BOGO', 'pimwick' ),
                __( 'Pimwick Plugins', 'pimwick' ),
                'manage_woocommerce',
                'pimwick',
                '',
                plugins_url( '/assets/images/pimwick-icon-120x120.png', __FILE__ ),
                6
            );

            add_submenu_page(
                'pimwick',
                __( 'PW BOGO', 'pimwick' ),
                __( 'Pimwick Plugins', 'pimwick' ),
                'manage_woocommerce',
                'pimwick',
                ''
            );

            remove_submenu_page('pimwick','pimwick');
        }

        remove_submenu_page( 'pimwick','pimwick-plugins' );
        add_submenu_page(
            'pimwick',
            __( 'Pimwick Plugins', 'pimwick' ),
            __( 'Our Plugins', 'pimwick' ),
            'manage_woocommerce',
            'pimwick-plugins',
            array( $this, 'other_plugins_page' )
        );
    }

    function other_plugins_page() {
        global $pimwick_more_handled;

        if ( !$pimwick_more_handled ) {
            $pimwick_more_handled = true;
            require( 'ui/more.php' );
        }
    }

    function admin_enqueue_scripts() {
        global $post_type;

        $data = get_plugin_data( __FILE__ );
        $version = $data['Version'];

        if ( 'pw_bogo' == $post_type ) {
            wp_register_style( 'jquery-ui-style', plugins_url( '/assets/css/jquery-ui-style.min.css', __FILE__ ), array(), $version );
            wp_enqueue_style( 'jquery-ui-style' );

            wp_enqueue_script( 'wc-admin-meta-boxes' );
            wp_enqueue_style( 'woocommerce_admin_styles' );

            wp_register_style( 'pwbogo-font-awesome', plugins_url( '/assets/css/font-awesome.min.css', __FILE__ ), array(), $version ); // 4.6.3
            wp_enqueue_style( 'pwbogo-font-awesome' );

            wp_register_style( 'pw-bogo', plugins_url( '/assets/css/style.css', __FILE__ ), array( 'woocommerce_admin_styles' ), $version );
            wp_enqueue_style( 'pw-bogo' );

            wp_enqueue_script( 'pw-bogo-admin-script', plugins_url( '/assets/js/script.js', __FILE__ ), array( 'wc-admin-meta-boxes' ), $version );
        }

        wp_register_style( 'pw-bogo-icon', plugins_url( '/assets/css/icon-style.css', __FILE__ ), array(), $version );
        wp_enqueue_style( 'pw-bogo-icon' );
    }

    function edit_pw_bogo_columns( $gallery_columns ) {
        $new_columns['cb'] = '<input type="checkbox" />';

        $new_columns['title'] = _x( 'Name', 'pimwick' );
        $new_columns['type'] = __( 'Buy One, Get One', 'pimwick' );
        $new_columns['begin_date'] = __( 'Begin Date', 'pimwick' );
        $new_columns['end_date'] = __( 'End Date', 'pimwick' );

        return $new_columns;
    }

    function pw_bogo_posts_custom_column( $column ) {
        global $post;

        switch ( $column ) {
            case 'type':
                $type = $post->type;
                if ( 'free' === $type || empty( $type )) {
                    echo __( 'Free', 'pimwick' );
                } else {
                    $percentage = $post->percentage;
                    echo $percentage . __( '% off', 'pimwick' );
                }
            break;

            case 'begin_date':
            case 'end_date':
                echo $post->{$column};
            break;
        }
    }

    function post_row_actions( $actions, $post ) {
        if ( $post->post_type == 'pw_bogo' ) {
            unset($actions['inline hide-if-no-js']);
        }

        return $actions;
    }

    function save_post( $post_id, $post ) {
        // $post_id and $post are required
        if ( empty( $post_id ) || empty( $post ) ) {
            return;
        }

        // Dont' save meta boxes for revisions or autosaves
        if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
            return;
        }

        // Check the nonce
        if ( empty( $_POST['pw_bogo_meta_nonce'] ) || ! wp_verify_nonce( $_POST['pw_bogo_meta_nonce'], 'pw_bogo_save_data' ) ) {
            return;
        }

        // Check the post being saved == the $post_id to prevent triggering this call for other save_post events
        if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
            return;
        }

        // Check user has permission to edit
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $type                                   = wc_clean( $_POST['type'] );
        $buy_type                               = wc_clean( $_POST['buy_type'] );
        $buy_limit                              = wc_clean( $_POST['buy_limit'] );
        $get_limit                              = wc_clean( $_POST['get_limit'] );
        $percentage                             = wc_format_decimal( $_POST['percentage'] );

        if ( PW_BOGO::wc_min_version( '3.0' ) ) {
            $product_ids                        = isset( $_POST['product_ids'] ) ? implode( ',', $_POST['product_ids'] ) : '';
            $exclude_product_ids                = isset( $_POST['exclude_product_ids'] ) ? implode( ',', $_POST['exclude_product_ids'] ) : '';
            $discounted_product_ids             = isset( $_POST['discounted_product_ids'] ) ? implode( ',', $_POST['discounted_product_ids'] ) : '';
            $discounted_exclude_product_ids     = isset( $_POST['discounted_exclude_product_ids'] ) ? implode( ',', $_POST['discounted_exclude_product_ids'] ) : '';
        } else {
            $product_ids                        = implode( ',', array_filter( array_map( 'intval', explode( ',', $_POST['product_ids'] ) ) ) );
            $exclude_product_ids                = implode( ',', array_filter( array_map( 'intval', explode( ',', $_POST['exclude_product_ids'] ) ) ) );
            $discounted_product_ids             = implode( ',', array_filter( array_map( 'intval', explode( ',', $_POST['discounted_product_ids'] ) ) ) );
            $discounted_exclude_product_ids     = implode( ',', array_filter( array_map( 'intval', explode( ',', $_POST['discounted_exclude_product_ids'] ) ) ) );
        }

        $product_categories                     = isset( $_POST['product_categories'] ) ? array_map( 'intval', $_POST['product_categories'] ) : array();
        $exclude_product_categories             = isset( $_POST['exclude_product_categories'] ) ? array_map( 'intval', $_POST['exclude_product_categories'] ) : array();

        $identical_products_only                = isset( $_POST['identical_products_only'] ) ? 'yes' : 'no';
        $identical_variations_only              = isset( $_POST['identical_variations_only'] ) ? 'yes' : 'no';
        $ignore_discounted_products             = isset( $_POST['ignore_discounted_products'] ) ? 'yes' : 'no';
        $auto_add_discounted_products           = isset( $_POST['auto_add_discounted_products'] ) ? 'yes' : 'no';
        $restrict_discount_quantity             = isset( $_POST['restrict_discount_quantity'] ) ? 'yes' : 'no';
        $discounted_product_categories          = isset( $_POST['discounted_product_categories'] ) ? array_map( 'intval', $_POST['discounted_product_categories'] ) : array();
        $discounted_exclude_product_categories  = isset( $_POST['discounted_exclude_product_categories'] ) ? array_map( 'intval', $_POST['discounted_exclude_product_categories'] ) : array();

        $begin_date                             = wc_clean( $_POST['begin_date'] );
        $end_date                               = wc_clean( $_POST['end_date'] );

        $discount_limit                         = isset( $_POST['discount_limit'] ) ? absint( $_POST['discount_limit'] ) : '';
        $coupon_code                            = isset( $_POST['coupon_code'] ) ? wc_clean( trim( $_POST['coupon_code'] ) ) : '';
        $individual_use                         = isset( $_POST['individual_use'] ) ? 'yes' : 'no';
        $exclude_sale_items                     = isset( $_POST['exclude_sale_items'] ) ? 'yes' : 'no';

        update_post_meta( $post_id, 'type', $type );
        update_post_meta( $post_id, 'buy_type', $buy_type );
        update_post_meta( $post_id, 'buy_limit', $buy_limit );
        update_post_meta( $post_id, 'get_limit', $get_limit );
        update_post_meta( $post_id, 'percentage', $percentage );

        update_post_meta( $post_id, 'product_ids', $product_ids );
        update_post_meta( $post_id, 'exclude_product_ids', $exclude_product_ids );
        update_post_meta( $post_id, 'product_categories', $product_categories );
        update_post_meta( $post_id, 'exclude_product_categories', $exclude_product_categories );

        update_post_meta( $post_id, 'identical_products_only', $identical_products_only );
        update_post_meta( $post_id, 'identical_variations_only', $identical_variations_only );
        update_post_meta( $post_id, 'ignore_discounted_products', $ignore_discounted_products );
        update_post_meta( $post_id, 'auto_add_discounted_products', $auto_add_discounted_products );
        update_post_meta( $post_id, 'restrict_discount_quantity', $restrict_discount_quantity );
        update_post_meta( $post_id, 'discounted_product_ids', $discounted_product_ids );
        update_post_meta( $post_id, 'discounted_exclude_product_ids', $discounted_exclude_product_ids );
        update_post_meta( $post_id, 'discounted_product_categories', $discounted_product_categories );
        update_post_meta( $post_id, 'discounted_exclude_product_categories', $discounted_exclude_product_categories );

        update_post_meta( $post_id, 'begin_date', $begin_date );
        update_post_meta( $post_id, 'end_date', $end_date );

        update_post_meta( $post_id, 'discount_limit', $discount_limit );
        update_post_meta( $post_id, 'coupon_code', $coupon_code );
        update_post_meta( $post_id, 'individual_use', $individual_use );
        update_post_meta( $post_id, 'exclude_sale_items', $exclude_sale_items );
    }

    function woocommerce_cart_calculate_fees( $cart ) {
        $discounts = $this->get_discounts( $cart );
        $active_bogos = $this->get_active_bogos();

        foreach ( $discounts as $bogo_id => $discount ) {

            // Get the coupon title.
            $bogo_title = 'BOGO';
            foreach ( $active_bogos as $bogo ) {
                if ( $bogo->ID == $bogo_id ) {
                    $bogo_title = $bogo->post_title;
                    break;
                }
            }

            $cart->add_fee( $bogo_title, ( $discount * -1 ) );
        }
    }

    function get_discounts( $cart, $subtotal = 0 ) {
        // Expand the list of cart items, one element per quantity.
        $cart_items = $this->flatten_cart( $cart );

        // Sort the cart by price from higest to lowest for the Eligible products, lowest to highest for the Discounted products.
        $cart_items_desc = $cart_items;
        $cart_items_asc = $cart_items;
        usort( $cart_items_desc, function( $a, $b ) { return ( floatval( $a['price'] ) > floatval( $b['price'] ) ) ? -1 : 1; } );
        usort( $cart_items_asc, function( $a, $b ) { return ( floatval( $a['price'] ) < floatval( $b['price'] ) ) ? -1 : 1; } );

        $discounts = array();
        $already_applied_cart_items = array();

        foreach ( $this->get_active_bogos() as $bogo ) {
            $bogo_percentage = !empty( $bogo->percentage ) ? $bogo->percentage : 100;
            $percentage = $bogo_percentage / 100;
            $discount_limit = absint( $bogo->discount_limit );
            $identical_products_only = ( 'yes' === $bogo->identical_products_only );
            $identical_variations_only = ( 'yes' === $bogo->identical_variations_only );
            $buy_type = !empty( $bogo->buy_type ) ? $bogo->buy_type : 'quantity';
            $buy_limit = !empty( $bogo->buy_limit ) ? $bogo->buy_limit : 1;
            $get_limit = !empty( $bogo->get_limit ) ? $bogo->get_limit : 1;

            if ( 'spend' == $buy_type ) {
                if ( empty( $subtotal ) ) {
                    if ( $this->wc_min_version( '3.2' ) ) {
                        $subtotal = $cart->get_cart_contents_total();
                    } else {
                        $subtotal = $cart->cart_contents_total;
                    }
                }
                if ( $subtotal < $buy_limit ) {
                    continue;
                }
            }

            $eligible_items = array();
            foreach ( $cart_items_desc as $ci ) {
                if ( $this->is_cart_item_valid_for_bogo( $ci['cart_item'], $bogo ) ) {
                    $eligible_items[ $ci['key'] ] = $ci['cart_item'];
                }
            }

            $discounted_items = array();
            foreach ( $cart_items_asc as $ci ) {
                if ( $this->is_cart_item_valid_for_bogo( $ci['cart_item'], $bogo, true ) ) {
                    $discounted_items[ $ci['key'] ] = $ci['cart_item'];
                }
            }

            $discount = 0;
            $id = '0';
            $item_index[ $id ] = 0;
            $discounted_item_count[ $id ] = 0;
            $discount_iterations = 0;

            foreach ( $eligible_items as $eligible_cart_item_key => $cart_item ) {
                if ( in_array( $eligible_cart_item_key, $already_applied_cart_items ) ) {
                    continue;
                }

                $already_applied_cart_items[] = $eligible_cart_item_key;

                if ( $identical_products_only === true ) {
                    if ( $identical_variations_only === true && $cart_item['variation_id'] != '0' ) {
                        $id = (string) $cart_item['variation_id'];
                    } else {
                        $id = (string) $cart_item['product_id'];
                    }
                }

                if ( !isset( $item_index[ $id ] ) ) { $item_index[ $id ] = 0; }
                if ( !isset( $discounted_item_count[ $id ] ) ) { $discounted_item_count[ $id ] = 0; }

                if ( $buy_type == 'quantity' ) {
                    $item_index[ $id ]++;
                    if ( $item_index[ $id ] < $buy_limit ) {
                        continue;
                    } else {
                        $discount_iterations++;
                        $item_index[ $id ] = 0;
                        $discounted_item_count[ $id ] = 0;
                    }
                }

                // Maximum number of times this deal can be used per order.
                if ( !empty( $discount_limit ) && $discount_iterations > $discount_limit ) {
                    break;
                }

                foreach ( $discounted_items as $discounted_cart_item_key => $discounted_cart_item ) {
                    if ( in_array( $discounted_cart_item_key, $already_applied_cart_items ) ) {
                        continue;
                    }

                    if ( $identical_products_only === true ) {
                        if ( $identical_variations_only === true && $cart_item['variation_id'] != '0'  ) {
                            if ( $discounted_cart_item['variation_id'] != $id ) {
                                continue;
                            }
                        } else {
                            if ( $discounted_cart_item['product_id'] != $id ) {
                                continue;
                            }
                        }
                    }

                    if ( $discounted_item_count[ $id ] >= $get_limit ) {
                        break;
                    }

                    $price = $discounted_cart_item['data']->get_price();
                    if ( !empty( $bogo->type ) && $bogo->type != 'free' ) {
                        $discount += $price * $percentage;
                    } else {
                        $discount += $price;
                    }

                    $discounted_item_count[ $id ]++;
                    $already_applied_cart_items[] = $discounted_cart_item_key;
                }
            }

            if ( !empty( $discount ) ) {
                $discounts[ $bogo->ID ] = $discount;
            }
        }

        return $discounts;
    }

    function woocommerce_cart_contents_total( $cart_contents_total ) {
        WC()->cart->calculate_fees();
        $fees = WC()->cart->get_fees();
        foreach ( $this->get_active_bogos() as $bogo ) {
            $fee_id = sanitize_title( $bogo->post_title );
            foreach ( $fees as $fee ) {
                if ( $fee->id == $fee_id ) {
                    return wc_price( WC()->cart->cart_contents_total + $fee->amount );
                }
            }
        }

        return $cart_contents_total;
    }

    function woocommerce_applied_coupon( $coupon_code ) {
        remove_action( 'woocommerce_applied_coupon', array( $this, 'woocommerce_applied_coupon' ) );

        $active_bogos = $this->get_active_bogos( true );
        foreach ( $active_bogos as $bogo ) {
            if ( strtolower( $coupon_code ) == strtolower( $bogo->coupon_code ) ) {
                $success = false;
                $this->maybe_apply_bogo_coupon();

                foreach ( WC()->cart->get_applied_coupons() as $applied_coupon ) {
                    if ( $this->is_bogo_coupon( $applied_coupon, $bogo ) ) {
                        $success = true;
                        break;
                    }
                }

                // Remove the original 'Coupon applied' message so we're just left with the BOGO Coupon Applied message.
                if ( !$success ) {
                    $notices = wc_get_notices();
                    unset($notices['success'][0]);
                    $notices['success'] = array_values( $notices['success'] );
                    $coupon_temp = new WC_Coupon();
                    $notices['error'][] = $coupon_temp->get_coupon_error( WC_Coupon::E_WC_COUPON_NOT_APPLICABLE );
                    wc_set_notices( $notices );
                }
                break;
            }
        }

        add_action( 'woocommerce_applied_coupon', array( $this, 'woocommerce_applied_coupon' ), 99, 1 );
    }

    function woocommerce_coupon_is_valid( $valid_for_cart, $coupon ) {
        // Fix for interfering plugins such as WooCommerce Coupon Schedule.
        if ( !$valid_for_cart ) {
            if ( $this->wc_min_version( '3.0' ) ) {
                $coupon_code = $coupon->get_code();
            } else {
                $coupon_code = $coupon->code;
            }

            if ( $this->is_bogo_coupon( $coupon_code ) ) {
                $valid_for_cart = true;
            }
        }

        return $valid_for_cart;
    }

    function ajax_activation() {
        $pimwick_license = new Pimwick_License( PW_BOGO_LICENSE_PRODUCT_NAME, PW_BOGO_LICENSE_OPTION_NAME );
        $pimwick_license->activate_license( wc_clean( $_POST['license-key'] ) );

        $registration['active'] = $pimwick_license->premium();
        $registration['error'] = $pimwick_license->error;

        wp_send_json( $registration );
    }

    function is_cart_item_valid_for_bogo( $cart_item, $bogo, $discounted = false ) {
        $prefix = '';
        if ( true === $discounted && 'yes' !== $bogo->ignore_discounted_products ) {
            $prefix = 'discounted_';
        }

        // Exclude any on-sale items, if we need to.
        if ( 'yes' === $bogo->exclude_sale_items ) {
            $product_ids_on_sale = wc_get_product_ids_on_sale();

            if ( ! empty( $cart_item['variation_id'] ) ) {
                if ( in_array( $cart_item['variation_id'], $product_ids_on_sale, true ) ) {
                    return false;
                }
            } else if ( in_array( $cart_item['product_id'], $product_ids_on_sale, true ) ) {
                return false;
            }
        }

        // If we have a coupon applied and that's not allowed, we can't use this BOGO deal.
        if ( 'yes' === $bogo->individual_use ) {
            foreach ( WC()->cart->get_applied_coupons() as $coupon ) {
                if ( !$this->is_bogo_coupon( $coupon, $bogo ) ) {
                    return false;
                }
            }
        }

        if ( PW_BOGO::wc_min_version( '3.0' ) ) {
            $parent_id = $cart_item['data']->get_parent_id();
        } else {
            $parent_id = $cart_item['data']->get_parent();
        }

        $included_products = array_filter( array_map( 'absint', explode( ',', get_post_meta( $bogo->ID, $prefix . 'product_ids', true ) ) ) );
        if ( count( $included_products ) > 0 ) {
            if ( !in_array( $cart_item['product_id'], $included_products ) && !in_array( $cart_item['variation_id'], $included_products ) && ( empty( $parent_id ) || !in_array( $parent_id, $included_products ) ) ) {
                return false;
            }
        }

        $excluded_products = array_filter( array_map( 'absint', explode( ',', get_post_meta( $bogo->ID, $prefix . 'exclude_product_ids', true ) ) ) );
        if ( count( $excluded_products ) > 0 ) {
            if ( in_array( $cart_item['product_id'], $excluded_products ) || in_array( $cart_item['variation_id'], $excluded_products ) || ( !empty( $parent_id ) && in_array( $parent_id, $excluded_products ) ) ) {
                return false;
            }
        }

        $product_cats = wc_get_product_cat_ids( $cart_item['product_id'] );
        $included_categories = get_post_meta( $bogo->ID, $prefix . 'product_categories', true );

        if ( count( $product_cats ) > 0 ) {
            if ( !empty( $included_categories ) ) {
                if ( count( array_intersect( $product_cats, $included_categories ) ) == 0 ) {
                    return false;
                }
            }

            $excluded_categories = get_post_meta( $bogo->ID, $prefix . 'exclude_product_categories', true );
            if ( !empty( $excluded_categories ) ) {
                if ( count( array_intersect( $product_cats, $excluded_categories ) ) > 0 ) {
                    return false;
                }
            }
        } else if ( count( $included_categories ) > 0 ) {
            return false;
        }

        return true;
    }

    function get_bogo( $bogo ) {
        if ( is_numeric( $bogo ) ) {
            $bogo = get_post( absint( $bogo ) );
            if ( !$bogo || $bogo->post_type != 'pw_bogo' ) {
                wp_die( __( 'Invalid bogo parameter for get_bogo().', 'pimwick' ) );
            }

        } elseif ( !$bogo instanceof WP_Post ) {
            wp_die( sprintf( __( '%s is not a valid type for get_bogo().', 'pimwick' ), gettype( $bogo ) ) );
        }

        return $bogo;
    }

    function get_active_bogos( $ignore_coupon_code = false ) {
        if ( is_null( $this->active_bogos_cache ) ) {
            $this->active_bogos_cache = array();

            $all_bogos = get_posts( array(
                'posts_per_page' => -1,
                'post_type' => 'pw_bogo',
                'post_status' => 'publish'
            ) );

            // Switch to their local timezone.
            $configured_timezone = wc_timezone_string();
            if ( !empty( $configured_timezone ) ) {
                $original_timezone = date_default_timezone_get();
                date_default_timezone_set( $configured_timezone );
            }

            foreach ( $all_bogos as $bogo ) {
                if ( !empty( $bogo->begin_date ) && strtotime( $bogo->begin_date ) > time() ) {
                    continue;
                }

                if ( !empty( $bogo->end_date ) && strtotime( $bogo->end_date ) < time() ) {
                    continue;
                }

                $this->active_bogos_cache[] = $bogo;
            }

            // Now that we're done formatting, switch it back.
            if ( isset( $original_timezone ) ) {
                date_default_timezone_set( $original_timezone );
            }

            if ( !empty( $this->active_bogos_cache ) ) {
                // Sort the bogos so when we go looking they're in order.
                $this->sort_bogos( $this->active_bogos_cache );
            }
        }

        $bogos = array();

        if ( true === $ignore_coupon_code ) {
            $bogos = $this->active_bogos_cache;
        } else {
            foreach ( $this->active_bogos_cache as $bogo ) {
                if ( !empty( $bogo->coupon_code ) ) {
                    foreach ( WC()->cart->get_applied_coupons() as $coupon_code ) {
                        if ( strtolower( $coupon_code ) == strtolower( $bogo->coupon_code ) || $this->is_bogo_coupon( $coupon_code, $bogo ) ) {
                            $bogos[] = $bogo;
                            break;
                        }
                    }
                } else {
                    $bogos[] = $bogo;
                }
            }
        }

        return $bogos;
    }

    function sort_bogos( &$bogos ) {
        usort( $bogos, function( $a, $b ) {
            $percentage_a = (int) ( $a->type == 'free' || empty( $a->type ) ) ? '100' : $a->percentage;
            $percentage_b = (int) ( $b->type == 'free' || empty( $b->type ) ) ? '100' : $b->percentage;

            if ( empty( $percentage_a ) ) { $percentage_a = 100; }
            if ( empty( $percentage_b ) ) { $percentage_b = 100; }

            // $scrooge means get the worst deal. If there are 2 active BOGOs, one is 50% off and the other
            // is 25% off, we'd return the 25% deal if $scrooge = true.
            $scrooge = false;
            if ( false === $scrooge ) {
                return ( $percentage_a > $percentage_b ) ? 1 : -1;
            } else {
                return ( $percentage_a < $percentage_b ) ? 1 : -1;
            }
        });
    }

    function validate_product_ids( $bogo, $cart ) {
        $product_ids = array_filter( array_map( 'absint', explode( ',', $bogo->product_ids ) ) );

        if ( count( $product_ids ) > 0 ) {
            $valid_for_cart = false;

            foreach( $cart->get_cart() as $cart_item_key => $cart_item ) {
                if ( in_array( $cart_item['product_id'], $product_ids ) || in_array( $cart_item['variation_id'], $product_ids ) || in_array( $cart_item['data']->get_parent(), $product_ids ) ) {
                    $valid_for_cart = true;
                    break;
                }
            }

            return $valid_for_cart;
        }
    }

    function validate_excluded_product_ids( $bogo, $cart ) {
        $exclude_product_ids = array_filter( array_map( 'absint', explode( ',', $bogo->exclude_product_ids ) ) );

        if ( count( $bogo->exclude_product_ids ) > 0 ) {
            $valid_for_cart = false;

            foreach( $cart->get_cart() as $cart_item_key => $cart_item ) {
                if ( in_array( $cart_item['product_id'], $bogo->product_ids ) || in_array( $cart_item['variation_id'], $bogo->product_ids ) || in_array( $cart_item['data']->get_parent(), $bogo->product_ids ) ) {
                    $valid_for_cart = true;
                    break;
                }
            }

            return $valid_for_cart;
        }
    }

    function is_bogo_coupon( $code, $bogo = '' ) {
        if ( !empty( $bogo ) ) {
            if ( strtolower( $code ) == strtolower( $bogo->coupon_code ) ) {
                return true;
            }

            if ( strtolower( $code ) == strtolower( $this->get_bogo_coupon_code( $bogo ) ) ) {
                return true;
            }

        } else {
            foreach ( $this->get_active_bogos() as $active_bogo ) {
                if ( $this->is_bogo_coupon( $code, $active_bogo ) ) {
                    return true;
                }
            }
        }

        return false;
    }

    function get_bogo_coupon_code( $bogo ) {
        return wc_sanitize_taxonomy_name( $bogo->post_title ) . '-' . $bogo->ID;
    }

    function pre_get_posts( $query ) {
        // Prevents our internal BOGO coupons from appearing in the admin area.
        if ( $query->is_main_query() || $query->is_search() ) {
            if ( $query->query['post_type'] == 'shop_coupon' ) {
                $query->set( 'meta_query', array(
                    'relation' => 'AND',
                     array(
                        'key' => '_pw_bogo_id',
                        'compare' => 'NOT EXISTS',
                     ),
                ) );
            }
        }
    }

    function wp_count_posts( $counts, $type, $perm ) {
        global $wpdb;

        // Subtract the total number of published Store Credit coupons from the counter.
        if ( 'shop_coupon' === $type && property_exists( $counts, 'publish' ) ) {
            $store_credit_coupons = $wpdb->get_var( "SELECT COUNT(*) AS total FROM {$wpdb->posts} AS p JOIN {$wpdb->postmeta} AS m ON (m.post_id = p.ID AND m.meta_key = '_pw_bogo_id') WHERE p.post_type = 'shop_coupon' AND p.post_status = 'publish'" );
            $counts->publish -= $store_credit_coupons;
        }

        return $counts;
    }

    function woocommerce_order_get_items( $items, $order ) {
        foreach ( $items as $order_item_id => &$order_item ) {
            if ( is_a( $order_item, 'WC_Order_Item_Coupon' ) && isset( $order_item['item_meta']['pw_bogo_id'] ) ) {
                $bogo_id = is_array( $order_item['item_meta']['pw_bogo_id'] ) ? $order_item['item_meta']['pw_bogo_id'][0] : $order_item['item_meta']['pw_bogo_id'];
                $bogo = get_post( absint( $bogo_id ) );
                $order_item['name'] = sprintf( __( 'PW BOGO: %s', 'pimwick' ), $bogo->post_title );
            }
        }
        return $items;
    }

    function woocommerce_after_calculate_totals( $cart ) {

        remove_action( 'woocommerce_after_calculate_totals', array( $this, 'woocommerce_after_calculate_totals' ) );

        foreach ( $this->get_active_bogos() as $bogo ) {

            $ignore_discounted_products = $bogo->ignore_discounted_products;
            $auto_add_discounted_products = $bogo->auto_add_discounted_products;

            if ( 'yes' !== $auto_add_discounted_products ) {
                continue;
            }

            $buy_type = !empty( $bogo->buy_type ) ? $bogo->buy_type : 'quantity';
            $buy_limit = !empty( $bogo->buy_limit ) ? $bogo->buy_limit : 1;
            $identical_products_only = ( 'yes' === $bogo->identical_products_only );
            $identical_variations_only = ( 'yes' === $bogo->identical_variations_only );

            if ( $buy_type == 'spend' ) {
                if ( $this->wc_min_version( '3.2' ) ) {
                    $subtotal = $cart->get_cart_contents_total();
                } else {
                    $subtotal = $cart->cart_contents_total;
                }

                if ( $subtotal < $buy_limit ) {
                    continue;
                }
            }

            if ( $identical_products_only === true ) {
                foreach ( $cart->get_cart() as $cart_item ) {
                    $product_id = $cart_item['product_id'];
                    $variation_id = ( $identical_variations_only === true ) ? $cart_item['variation_id'] : 0;

                    $this->maybe_add_discounted_item( $cart, $bogo, $product_id, $variation_id );
                }
            } else {
                $this->maybe_add_discounted_item( $cart, $bogo, 0, 0 );
            }
        }

        add_action( 'woocommerce_after_calculate_totals', array( $this, 'woocommerce_after_calculate_totals' ) );
    }

    function maybe_add_discounted_item( $cart, $bogo, $identical_product_id = 0, $identical_variation_id = 0 ) {

        $buy_type = !empty( $bogo->buy_type ) ? $bogo->buy_type : 'quantity';
        $buy_limit = !empty( $bogo->buy_limit ) ? $bogo->buy_limit : 1;
        $get_limit = !empty( $bogo->get_limit ) ? $bogo->get_limit : 1;
        $discount_limit = absint( $bogo->discount_limit );

        // Expand the list of cart items, one element per quantity.
        $cart_items = $this->flatten_cart( $cart, $identical_product_id, $identical_variation_id );

        // Sort the cart by price from higest to lowest for the Eligible products, lowest to highest for the Discounted products.
        $cart_items_desc = $cart_items;
        $cart_items_asc = $cart_items;
        usort( $cart_items_desc, function( $a, $b ) { return ( floatval( $a['price'] ) > floatval( $b['price'] ) ) ? -1 : 1; } );
        usort( $cart_items_asc, function( $a, $b ) { return ( floatval( $a['price'] ) < floatval( $b['price'] ) ) ? -1 : 1; } );

        $eligible_items = array();
        foreach ( $cart_items_desc as $ci ) {
            if ( $this->is_cart_item_valid_for_bogo( $ci['cart_item'], $bogo ) ) {
                $eligible_items[ $ci['key'] ] = $ci['cart_item'];
            }
        }

        $discounted_items = array();
        foreach ( $cart_items_asc as $ci ) {
            if ( $this->is_cart_item_valid_for_bogo( $ci['cart_item'], $bogo, true ) ) {
                $discounted_items[ $ci['key'] ] = $ci['cart_item'];
            }
        }

        $quantity_to_add = 0;
        $discount_iterations = 0;
        $processed_eligible_item_keys = array();

        $buy_amount = 0;

        while ( $buy_amount < $buy_limit && ( empty( $discount_limit ) || $discount_iterations < $discount_limit ) ) {

            // Get the next eligible item and remove it from the array.
            reset( $eligible_items );
            $eligible_item_key = key( $eligible_items );
            $eligible_item = array_shift( $eligible_items );
            if ( is_null( $eligible_item ) ) {
                break;
            }

            if ( 'quantity' === $buy_type ) {
                $buy_amount++;
            } else {
                if ( $eligible_item['quantity'] != 0 ) {
                    $buy_amount += ( $eligible_item['line_subtotal'] / $eligible_item['quantity'] );
                } else {
                    $buy_amount += 0;
                }
            }

            $processed_eligible_item_keys[] = $eligible_item_key;

            // Once we're at our buy limit, begin adding items as necessary.
            if ( $buy_amount >= $buy_limit ) {
                $discount_iterations++;

                // reset the outer loop.
                $buy_amount = 0;

                // Remove the appropriate number of discounted+eligible items from the discounted pool.
                $purge_counter = 0;
                foreach ( $discounted_items as $discounted_item_key => $discounted_item ) {
                    if ( in_array( $discounted_item_key, $processed_eligible_item_keys ) ) {
                        $purge_counter++;
                        unset( $discounted_items[ $discounted_item_key ] );

                        if ( $purge_counter == $buy_limit ) {
                            break;
                        }
                    }
                }

                // Automatically add discounted items.
                $get_counter = 0;
                $get_quantity = $get_limit;
                while ( $get_counter < $get_quantity ) {
                    $get_counter++;

                    if ( count( $discounted_items ) > 0 ) {
                        reset( $discounted_items );
                        $discounted_item_key = key( $discounted_items );
                        $discounted_item = array_shift( $discounted_items );

                        // If we're using an eligible item as the qualifier, then we need to
                        // take that into account for the quantity that are discounted.
                        if ( in_array( $discounted_item_key, $processed_eligible_item_keys ) ) {
                            $get_quantity = $get_limit + 1;
                        }

                        // Remove the Discounted Item from the Eligible Item array (if it exists)
                        foreach ( $eligible_items as $ei_key => $ei_item ) {
                            if ( $discounted_item_key == $ei_key ) {
                                unset( $eligible_items[ $ei_key ] );
                                $processed_eligible_item_keys[] = $ei_key;
                            }
                        }

                    } else {
                        $quantity_to_add++;
                    }
                }
            }
        }

        if ( $quantity_to_add > 0 ) {

            $auto_add_product_ids = array_filter( array_map( 'absint', explode( ',', $bogo->discounted_product_ids ) ) );
            if ( empty( $auto_add_product_ids ) ) {

                foreach ( $cart_items_asc as $ci ) {
                    $cart_item = $ci['cart_item'];
                    if ( $this->is_cart_item_valid_for_bogo( $cart_item, $bogo, true ) ) {
                        $auto_add_product_ids[] = !empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : $cart_item['product_id'];
                        break;
                    }
                }
            }

            if ( count( $auto_add_product_ids ) > 0 ) {
                $product_id = $auto_add_product_ids[0];
                $variation_id = 0;
                $variation_attributes = array();

                if ( 'product_variation' === get_post_type( $product_id ) ) {
                    $variation_id = $product_id;
                    $product_id   = wp_get_post_parent_id( $variation_id );

                    $product = wc_get_product( $variation_id );
                    $variation_attributes = $product->get_variation_attributes();

                } else {
                    $product = wc_get_product( $product_id );
                }

                $cart->add_to_cart( $product_id, $quantity_to_add, $variation_id, $variation_attributes );
            }
        } else {

            // We have more discounted items in the cart than what applies to this promo. Optionally limit that quantity.
            if ( count( $discounted_items ) > 0 ) {
                $restrict_discount_quantity = !empty( $bogo->restrict_discount_quantity ) ? ( 'yes' === $bogo->restrict_discount_quantity ) : false;

                if ( true === $restrict_discount_quantity ) {
                    $discounted_item = end( $discounted_items );
                    $new_quantity = $discounted_item['quantity'] - count( $discounted_items );

                    $cart->set_quantity( $discounted_item['key'], $new_quantity );
                }
            }
        }
    }

    function woocommerce_get_shop_coupon_data( $data, $code ) {
        if ( empty( $code ) || empty( WC()->cart ) ) {
            return $data;
        }

        if ( $this->wc_min_version( '3.2' ) ) {
            $subtotal = WC()->cart->get_subtotal();
        } else {
            $subtotal = WC()->cart->subtotal;
        }

        if ( empty( $subtotal ) && false === $this->wc_min_version( '3.0' ) ) {
            // For 2.6 and earlier, manually calculate the cart subtotal.
            foreach ( WC()->cart->get_cart() as $cart_item_index => $cart_item ) {
                $subtotal += $cart_item['line_subtotal'];
            }
        }

        $discounts = $this->get_discounts( WC()->cart, $subtotal );

        foreach ( $this->get_active_bogos( true ) as $bogo ) {
            if ( $this->is_bogo_coupon( $code, $bogo ) ) {
                $amount = isset( $discounts[ $bogo->ID ] ) ? $discounts[ $bogo->ID ] : 0;

                // Creates a virtual coupon
                $data = array(
                    'id' => -1,
                    'code' => $code,
                    'description' => $bogo->post_title,
                    'amount' => $amount,
                    'coupon_amount' => $amount
                );
                break;
            }
        }

        return $data;
    }

    function maybe_apply_bogo_coupon() {
        if ( false === $this->use_coupons ) {
            return;
        }

        $cart = WC()->cart;

        $discounts = $this->get_discounts( $cart );
        $active_bogos = $this->get_active_bogos();

        // Delete any invalid coupons.
        foreach ( $cart->get_applied_coupons() as $coupon_code ) {
            foreach ( $active_bogos as $active_bogo ) {
                if ( $this->is_bogo_coupon( $coupon_code, $active_bogo ) ) {
                    if ( !isset( $discounts[ $active_bogo->ID ] ) ) {
                        if ( empty( $active_bogo->coupon_code ) || strtolower( trim( $active_bogo->coupon_code ) ) != strtolower( trim( $coupon_code ) ) ) {
                            $cart->remove_coupon( $coupon_code );
                        }
                    }
                }
            }
        }

        asort( $discounts );
        foreach ( $discounts as $bogo_id => $bogo_discount ) {
            $bogo = $this->get_bogo( $bogo_id );
            $coupon_exists = false;

            // Use the existing bogo coupon if it exists.
            foreach ( $cart->get_applied_coupons() as $coupon_code ) {
                if ( $this->is_bogo_coupon( $coupon_code, $bogo ) ) {
                    $coupon_exists = true;
                    break;
                }
            }

            // If it doesn't exist, we need to create one for the BOGO.
            if ( !$coupon_exists ) {
                $bogo_coupon_code = $this->get_bogo_coupon_code( $bogo );
                $cart->add_discount( $bogo_coupon_code );
                WC()->session->set( 'refresh_totals', true );
            }
        }
    }

    function woocommerce_coupon_message( $msg, $msg_code, $coupon ) {
        if ( $msg_code == WC_Coupon::WC_COUPON_SUCCESS ) {
            if ( PW_BOGO::wc_min_version( '3.0' ) ) {
                $coupon_code = $coupon->get_code();
            } else {
                $coupon_code = $coupon->code;
            }

            foreach ( $this->get_active_bogos() as $bogo ) {
                if ( $this->is_bogo_coupon( $coupon_code, $bogo ) ) {
                    $msg = $bogo->post_title . ' ' . strtolower( $msg );
                }
            }
        }

        return $msg;
    }

    function woocommerce_cart_totals_coupon_label( $label, $coupon ) {
        if ( PW_BOGO::wc_min_version( '3.0' ) ) {
            if ( $this->is_bogo_coupon( $coupon->get_code() ) ) {
                $label = sprintf( __( 'Coupon: %s', 'woocommerce' ), $coupon->get_description() );
            }
        } else {
            foreach ( $this->get_active_bogos() as $bogo ) {
                if ( $this->is_bogo_coupon( $coupon->code, $bogo ) ) {
                    $label = sprintf( __( 'Coupon: %s', 'woocommerce' ), $bogo->post_title );
                    break;
                }
            }
        }

        return $label;
    }

    function woocommerce_new_order_item( $order_item_id, $item, $order_id ) {
        if ( is_a( $item, 'WC_Order_Item_Coupon' ) ) {
            if ( PW_BOGO::wc_min_version( '3.0' ) ) {
                $coupon_code = $item->get_code();
            } else {
                $coupon_code = $item->code;
            }

            $this->maybe_add_coupon_to_order_item( $order_item_id, $coupon_code );
        }
    }

    function woocommerce_order_add_coupon( $order_id, $order_item_id, $code, $discount_amount, $discount_amount_tax ) {
        $this->maybe_add_coupon_to_order_item( $order_item_id, $code );
    }

    function maybe_add_coupon_to_order_item( $order_item_id, $code ) {
        foreach ( $this->get_active_bogos() as $bogo ) {
            if ( $this->is_bogo_coupon( $code, $bogo ) ) {
                wc_add_order_item_meta( $order_item_id, 'pw_bogo_id', $bogo->ID );
                break;
            }
        }
    }

    function flatten_cart( $cart, $product_id = 0, $variation_id = 0 ) {
        // Expand the list of cart items, one element per quantity.
        $cart_items = array();
        foreach ( $cart->get_cart() as $cart_item_index => $cart_item ) {
            if ( !empty( $variation_id ) && $cart_item['variation_id'] != $variation_id ) {
                continue;
            }

            if ( !empty( $product_id ) && $cart_item['product_id'] != $product_id ) {
                continue;
            }

            for ( $i = 0; $i < $cart_item['quantity']; $i++ ) {

                if ( !empty( $cart_item['data'] ) ) {
                    $price = $cart_item['data']->get_price();
                } else {
                    $price = 0;
                }

                $cart_items[] = array(
                    'key'       => $cart_item_index . '_' . $i,
                    'cart_item' => $cart_item,
                    'price'     => $price
                );
            }
        }

        return $cart_items;
    }
}

new PW_BOGO();

endif;

require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://pimwick.com/wordpress-plugins/pw-bogo.json',
    __FILE__
);

if ( !function_exists( 'boolval' ) ) {
    function boolval( $val ) {
        return (bool) $val;
    }
}

?>