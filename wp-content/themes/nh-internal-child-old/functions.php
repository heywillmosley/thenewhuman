<?php

// Defines
define( 'FL_CHILD_THEME_DIR', get_stylesheet_directory() );
define( 'FL_CHILD_THEME_URL', get_stylesheet_directory_uri() );

// Classes
require_once 'classes/class-fl-child-theme.php';

// Actions
add_action( 'fl_head', 'FLChildTheme::stylesheet' );



function nh_jscss() {
	wp_enqueue_script('nh-custom-script', '//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.0/js/bootstrap.min.js',array( 'jquery' ));
	wp_enqueue_style('nh-bootstrapstyles', '//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.0/css/bootstrap-combined.min.css');
}

add_action( 'wp_enqueue_scripts', 'nh_jscss' );
add_action( 'wp_enqueue_style', 'nh_jscss' );




/**

  Hide shipping rates when free shipping is available

  @param array $rates Array of rates found for the package
  @param array $package The package array/object being shipped
  @return array of modified rates

*/
function nh_hide_shipping_when_free_available($rates, $package) {
  if (isset($rates['free_shipping'])) {
    unset($rates['flat_rate']);
  }
  return $rates;
}
add_filter('woocommerce_package_rates', 'nh_hide_shipping_when_free_available', 10, 2);




/**

 Apply custom discounts based on cart totals

 The built-in WC discount system is flawed in that it's either all or nothing.
 If your cart contains a product from an excluded category, then the entire discount is nulled.
 This is counter-intuitive if you wish to apply a discount to the cart total (less any restricted categories)

*/
function nh_custom_coupon_filter() {
  global $woocommerce;
  $excluded_amount = $discount_percent = 0;
  $working_total   = $woocommerce->cart->cart_contents_total;
  $excluded_categories = array(
    187, # Dr. Recommends Brand
    203, # SpectraVision Technology
    217, # Training
    # 223, # Starter Kits
    228, # Dynamica Induction Lasers
    # 479, # Neutraceuticals
    480  # Custom Pricing (CP)
  );

  # Only apply manual discount if no coupons are applied
  if (!$woocommerce->cart->applied_coupons) {

    # Find any items in cart that belong to the restricted categories
    foreach ($woocommerce->cart->cart_contents as $item) {
      $product_categories = get_the_terms($item['product_id'], 'product_cat');
      if (empty($product_categories) || is_wp_error($product_categories) || !$product_categories) {
        if (is_wp_error($product_categories)) {
          wp_die($product_categories->get_error_message());
        }
        else {
          $product_categories = new WP_Error('no_product_categories', "The product \"".$item->post_title."\" doesn't have any categories attached, thus no discounts can be calculated.", "Fatal Error");
          wp_die($product_categories);
        }
      }
      foreach ($excluded_categories as $excluded_category) {
        foreach ($product_categories as $category) {
          if ($excluded_category == $category->term_id) {
            $excluded_amount += $item['line_subtotal']; # Increase our exclusion amount
            $working_total -= $item['line_subtotal'];   # Decrease our discountable amount
          }
        }
      }
    }

    # Logic to determine WHICH discount to apply based on subtotal
    if ($working_total >= 600 && $working_total < 1000) {
      $discount_percent = 5;
    }
    elseif ($working_total >= 1000) {
      $discount_percent = 10;
    }
    else {
      $discount_percent = 0;
    }

    # Make sure cart total is eligible for discount
    if ($discount_percent > 0) {
      $discount_amount  = ( ( ($discount_percent/100) * $working_total ) * -1 );
      $woocommerce->cart->add_fee('Bulk Discount', $discount_amount);
    }
  }
}
add_action('woocommerce_cart_calculate_fees', 'nh_custom_coupon_filter');


/* add moment.js 
============================================== */
 function nh_add_momentjs() {
  if (is_page(13541)) {
    wp_enqueue_script( 'nh_add_momentjs', get_stylesheet_directory_uri() . '/js/moment.js', array( 'jquery' ), '1.0', true );
  }
}

add_action('wp_enqueue_scripts', 'nh_add_momentjs');


// /**

//  Compare manual discount and coupon discount(s) (if applicable) and apply highest

//  Since we don't want our manual discount to stack on top of any possible coupons
//  being applied to the order, we'll need to first check if coupons are being used,
//  then compare the total amount of our manual discount to the total amount of any
//  coupons being used. Once we know which is a greater discount, we can apply that
//  and remove the other(s).

// */
// function nh_apply_highest_discount() {
//   global $woocommerce;
//   $cart = $woocommerce->cart;
//   $has_manual_discount = false;
//   $manual_discount = null;

//   # Check for the existence of a manual discount
//   if (is_array($cart->fees) && count($cart->fees) > 0) {
//     foreach ($cart->fees as $key => $obj) {
//       # This name MUST be the same as the name of the fee
//       # we set in the nh_custom_coupon_filter() function
//       if ($obj->name === 'Bulk Discount') {
//         $has_manual_discount = true;
//         $manual_discount = $cart->fees[$key];
//       }
//     }
//   }

//   # Turn our negative fee back into a positive number if exists
//   $manual_discount_total = ($has_manual_discount === true) ? ($manual_discount->amount * -1) : 0;

//   # Make sure coupons are enabled and a discount exists before wasting resources
//   if ((isset($cart->applied_coupons) && is_array($cart->applied_coupons)) && (count($cart->applied_coupons > 0)) && $has_manual_discount) {
//     $coupon_total = $cart->discount_total;
//     if ($manual_discount_total >= $coupon_total) {
//       $cart->applied_coupons = array();
//       $cart->coupon_discount_amounts = array();
//       $cart->coupon_applied_count = array();
//       $cart->discount_total = 0;
//     }
//     elseif ($coupon_total > $manual_discount_total) {
//       $cart->fees = array();
//       $cart->fee_total = 0;
//     }
//   }
// }
// add_action('woocommerce_calculate_totals', 'nh_apply_highest_discount');


add_filter( 'woocommerce_product_tabs', 'sb_woo_remove_reviews_tab', 98);
function sb_woo_remove_reviews_tab($tabs) {

 unset($tabs['reviews']);

 return $tabs;
}

// Allow exe or dmg for digital downloads
add_filter('upload_mimes', function($mimetypes, $user)
{
    // Only allow these mimetypes for admins or shop managers
    $manager = $user ? user_can($user, 'manage_woocommerce') : current_user_can('manage_woocommerce');

    if ($manager)
    {
        $mimetypes = array_merge($mimetypes, array (
            'exe' => 'application/octet-stream',
            'dmg' => 'application/octet-stream'
        ));
    }

    return $mimetypes;
}, 10, 2);


// adds notice at single product page above add to cart
 
add_action( 'woocommerce_single_product_summary', 'return_policy', 20 );
function return_policy() {
    echo '<a href="javascript:window.print()" class="printpage">Print Page Flyer</a>';
}

// Coursepress Pro, course description length
add_filter('course_admin_excerpt_length', 'increase_excerpt_length');

function increase_excerpt_length($length){
  return 100;
}