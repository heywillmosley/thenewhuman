<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SA_Save_For_Later' ) ) {

	class SA_Save_For_Later {

		static $text_domain = 'save-for-later-for-woocommerce';

		private $saved_items;

		public function __construct() {

			if ( ! $this->is_wc_gte_25() ) {
				add_action( 'admin_notices', array( $this, 'admin_notice_sa_needs_wc_25_above' ) );
			}

			add_action( 'init', array( $this, 'localize' ) );

			add_action( 'wp_loaded', array( $this, 'save_for_later_action_handler' ) );
			add_action( 'wp_loaded', array( $this, 'move_to_cart_action_handler' ) );
			add_action( 'wp_loaded', array( $this, 'move_saved_items_from_cookies_to_account' ) );

			add_action( 'woocommerce_after_cart_table', array( $this, 'show_saved_items_list' ) );
			add_action( 'woocommerce_cart_is_empty', array( $this, 'show_saved_items_list' ) );

			add_filter( 'sa_saved_items_list_template', array( $this, 'saved_items_list_template_path' ) );

			add_filter( 'woocommerce_cart_item_remove_link', array( $this, 'cart_item_save_for_later_link' ), 10, 2 );

			add_action( 'wp_ajax_get_save_for_later_actions', array( $this, 'get_save_for_later_actions' ) );
			add_action( 'wp_ajax_nopriv_get_save_for_later_actions', array( $this, 'get_save_for_later_actions' ) );

			add_action( 'wp_ajax_sa_delete_saved_item', array( $this, 'sa_delete_saved_item' ) );
			add_action( 'wp_ajax_nopriv_sa_delete_saved_item', array( $this, 'sa_delete_saved_item' ) );

			add_action( 'wp_footer', array( $this, 'styles_and_scripts' ) );

		}

		/**
		 * to handle WC compatibility related function call from appropriate class
		 * 
		 * @param $function_name string
		 * @param $arguments array of arguments passed while calling $function_name
		 * @return result of function call
		 * 
		 */
		public function __call( $function_name, $arguments = array() ) {

			if ( ! is_callable( 'SA_WC_Compatibility_3_0', $function_name ) ) return;

			if ( ! empty( $arguments ) ) {
				return call_user_func_array( 'SA_WC_Compatibility_3_0::'.$function_name, $arguments );
			} else {
				return call_user_func( 'SA_WC_Compatibility_3_0::'.$function_name );
			}

		}

		/**
		 * Language loader
		 */
		public function localize() {

			$text_domains = array( self::$text_domain );

			$plugin_dirname = dirname( plugin_basename( __FILE__ ) );

			foreach ( $text_domains as $text_domain ) {

				self::$text_domain = $text_domain;

				$locale = apply_filters( 'plugin_locale', get_locale(), self::$text_domain );

				$loaded = load_textdomain( self::$text_domain, WP_LANG_DIR . '/' . $plugin_dirname . '/' . self::$text_domain . '-' . $locale . '.mo' );

				if ( ! $loaded ) {
					$loaded = load_plugin_textdomain( self::$text_domain, false, $plugin_dirname . '/languages' );
				}

				if ( $loaded ) {
					break;
				}
			}
		}

		/**
		 * Function to show admin notice that Save For Later works with WC 2.5+
		 */
		public function admin_notice_sa_needs_wc_25_above() { 
			?>
			<div class="updated error">
				<p><?php
					echo sprintf(__( '%s Save For Later is active but it will only work with WooCommerce 2.5+. %s.', self::$text_domain ), '<strong>' . __( 'Important', self::$text_domain ) . ':</strong>', '<a href="'.admin_url('plugins.php?plugin_status=upgrade').'" target="_blank" >' . __( 'Please update WooCommerce to the latest version', self::$text_domain ) . '</a>' );
				?></p>
			</div>
			<?php
		}

		/**
		 * To get saved items 
		 */
		public function get_saved_items() {

			$user_id = get_current_user_id();

			if ( $user_id == 0 ) {
				$saved_items = $this->get_saved_item_from_cookie();
			} else {
				$saved_items = $this->get_saved_item_from_user_account();
			}

			$cart_items = $this->create_cart_items( $saved_items );

			$this->saved_items = $cart_items;

			return $this->saved_items;

		}

		/**
		 * To handle Save For Later action, when save for later link is clicked
		 */
		public function save_for_later_action_handler() {
			
			if ( ! empty( $_GET['sa_save_for_later'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'sa-save-for-later-cart' ) ) {

				$cart_item_key = sanitize_text_field( $_GET['sa_save_for_later'] );

				WC()->cart->get_cart_from_session();

				if ( $cart_item = WC()->cart->get_cart_item( $cart_item_key ) ) {

					$this->save_for_later( $cart_item_key );

					$product = wc_get_product( $cart_item['product_id'] );

					$item_removed_title = apply_filters( 'sa_sfl_cart_item_removed_title', $product ? $product->get_title() : __( 'Item', self::$text_domain ), $cart_item );

					// Don't show undo link if saved item is out of stock.
					if ( $product->is_in_stock() && $product->has_enough_stock( $cart_item['quantity'] ) ) {
						$undo = $this->get_move_to_cart_url( $cart_item_key );
						wc_add_notice( sprintf( __( '%s saved for later. %sUndo?%s', self::$text_domain ), $item_removed_title, '<a href="' . esc_url( $undo ) . '">', '</a>' ) );
					} else {
						wc_add_notice( sprintf( __( '%s saved for later.', self::$text_domain ), $item_removed_title ) );
					}
				}

				$referer  = wp_get_referer() ? remove_query_arg( array( 'remove_item', 'removed_item', 'add-to-cart', 'added-to-cart', 'sa_save_for_later' ), add_query_arg( 'sa_save_for_later', '1', wp_get_referer() ) ) : wc_get_cart_url();
				wp_safe_redirect( $referer );
				exit;

			}

		}

		/**
		 * To save cart item 
		 * 
		 * @param string $cart_item_key
		 */
		public function save_for_later( $cart_item_key = null ) {

			if ( empty( $cart_item_key ) ) return;

			$user_id = get_current_user_id();

			if ( $user_id == 0 ) {
				$saved = $this->save_cart_item_in_cookie( $cart_item_key );
			} else {
				$saved = $this->save_cart_item_in_user_account( $cart_item_key );
			}

			if ( $saved ) {
				WC()->cart->set_quantity( $cart_item_key, 0 );
			}

		}

		/**
		 * To save cart item in cookie
		 * 
		 * @param string $cart_item_key
		 * @return bool saved or not
		 */
		public function save_cart_item_in_cookie( $cart_item_key = null ) {

			if ( empty( $cart_item_key ) ) return false;

			global $sa_save_for_later;

			WC()->cart->get_cart_from_session();

			$cart_item = WC()->cart->get_cart_item( $cart_item_key );

			if ( empty( $cart_item ) ) {
				return false;
			}

			if ( empty( $_COOKIE['sa_saved_for_later_profile_id'] ) ) {
				$unique_id = $this->generate_unique_id();
			} else {
				$unique_id = $_COOKIE['sa_saved_for_later_profile_id'];
			}

			$saved_for_later_products = get_option( 'sa_saved_for_later_profile_' . $unique_id, array() );

			$product_id = ( ! empty( $cart_item['variation_id'] ) ) ? $cart_item['variation_id'] : $cart_item['product_id'];

			$update = false;

			if ( $sa_save_for_later->is_wc_gte_30() ) {
				$cart_item_price = $cart_item['data']->get_price();
			} else {
				$cart_item_price = $cart_item['data']->price;
			}

			if ( ! array_key_exists( $product_id, $saved_for_later_products ) ) {
				$saved_for_later_products[ $product_id ] = array(
																	'product_id' => $product_id,
																	'price' => $cart_item_price
																);
				$update = true;
			} else {
				return true;
			}

			if ( $update ) {

				update_option( 'sa_saved_for_later_profile_' . $unique_id, $saved_for_later_products );

				// Save saved for later profile id
				wc_setcookie( 'sa_saved_for_later_profile_id', $unique_id, $this->get_cookie_life() );

				return true;

			}

			return false;
		}

		/**
		 * To save cart item in user account
		 * 
		 * @param string $cart_item_key
		 * @return bool saved or not
		 */
		public function save_cart_item_in_user_account( $cart_item_key = null ) {

			global $sa_save_for_later;

			if ( empty( $cart_item_key ) ) return false;

			$user_id = get_current_user_id();

			WC()->cart->get_cart_from_session();

			$cart_item = WC()->cart->get_cart_item( $cart_item_key );

			if ( empty( $cart_item ) ) {
				return false;
			}

			$saved_items = get_user_meta( $user_id, '_sa_sfl_saved_items', true );

			if ( empty( $saved_items ) || ! is_array( $saved_items ) ) {
				$saved_items = array();
			}

			if ( $sa_save_for_later->is_wc_gte_30() ) {
				$cart_item_price = $cart_item['data']->get_price();
			} else {
				$cart_item_price = $cart_item['data']->price;
			}

			$saved_item = array(
								'product_id' => ( ! empty( $cart_item['variation_id'] ) ) ? $cart_item['variation_id'] : $cart_item['product_id'],
								'price' => $cart_item_price
							);

			if ( ! empty( $saved_item ) ) {
				if ( ! in_array( $saved_item, $saved_items, true ) ) {
					$saved_items[] = $saved_item;
					update_user_meta( $user_id, '_sa_sfl_saved_items', $saved_items );
				}
				return true;
			}

			return false;
		}

		/**
		 * To handle Move To Cart action, when move to cart link is clicked
		 */
		public function move_to_cart_action_handler() {

			if ( ! empty( $_GET['sa_move_to_cart'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'sa-move-to-cart' ) ) {

				$cart_item_key = sanitize_text_field( $_GET['sa_move_to_cart'] );

				$moved = $this->move_to_cart( $cart_item_key );

				if ( ! empty( $moved ) ) {
					$_product = $moved['data'];
					wc_add_notice( sprintf( __( 'Moved %s to cart', self::$text_domain ), $_product->get_title() ) );
				}

				$referer  = wp_get_referer() ? remove_query_arg( array( 'undo_item', '_wpnonce', 'sa_move_to_cart' ), wp_get_referer() ) : wc_get_cart_url();
				wp_safe_redirect( $referer );
				exit;

			}

		}

		/**
		 * To move saved item back to cart
		 * 
		 * @param string $cart_item_key
		 * 
		 * @return mixed Will return cart_item, if moved successfully, false otherwise
		 */
		public function move_to_cart( $cart_item_key = null ) {

			global $sa_save_for_later;

			if ( empty( $cart_item_key ) ) return;

			WC()->cart->get_cart_from_session();

			$cart_items = $this->get_saved_items();

			if ( ! empty( $cart_items[ $cart_item_key ] ) ) {
				WC()->cart->cart_contents[ $cart_item_key ] = $cart_items[ $cart_item_key ];
				WC()->cart->set_session();

				if ( $sa_save_for_later->is_wc_gte_30() ) {
					if ( ( ! empty( $cart_items[ $cart_item_key ]['data']->get_id() ) ) ) {
						$product_id = $cart_items[ $cart_item_key ]['data']->get_id();
					}
				} else {
					$product_id = ( ! empty( $cart_items[ $cart_item_key ]['data']->variation_id ) ) ? $cart_items[ $cart_item_key ]['data']->variation_id : $cart_items[ $cart_item_key ]['data']->id;
				}

				$deleted = $this->delete_saved_item( $product_id );
				if ( $deleted ) {
					return WC()->cart->cart_contents[ $cart_item_key ];
				}
			} 

			return false;

		}

		/**
		 * To delete saved item
		 * 
		 * @param int $product_id
		 * 
		 * @return bool $deleted whether deleted or not
		 */
		public function delete_saved_item( $product_id = null ) {

			if ( empty( $product_id ) ) return;

			$user_id = get_current_user_id();

			if ( $user_id == 0 ) {
				$deleted = $this->delete_saved_item_from_cookie( $product_id );
			} else {
				$deleted = $this->delete_saved_item_from_user_account( $product_id );
			}

			return $deleted;

		}

		/**
		 * To delete saved items from cookies
		 * 
		 * @param int $product_id
		 * 
		 * @return bool $deleted
		 */
		public function delete_saved_item_from_cookie( $product_id = null ) {

			if ( empty( $product_id ) ) return false;

			if ( ! empty( $_COOKIE['sa_saved_for_later_profile_id'] ) ) {

				$unique_id = $_COOKIE['sa_saved_for_later_profile_id'];

				$saved_for_later_products = get_option( 'sa_saved_for_later_profile_' . $unique_id );

				$update = false;

				if ( $saved_for_later_products !== false && is_array( $saved_for_later_products ) ) {

					if ( array_key_exists( $product_id, $saved_for_later_products ) ) {

						unset( $saved_for_later_products[ $product_id ] );
						$update = true;

					}

				}

				if ( $update ) {

					update_option( 'sa_saved_for_later_profile_' . $unique_id, $saved_for_later_products );

					return true;

				}

			}

			return false;

		}

		/**
		 * To delete saved items from user account
		 * 
		 * @param int $product_id
		 * 
		 * @return bool $deleted
		 */
		public function delete_saved_item_from_user_account( $product_id = null ) {

			if ( empty( $product_id ) ) return false;

			$user_id = get_current_user_id();

			$saved_items = get_user_meta( $user_id, '_sa_sfl_saved_items', true );

			if ( ! empty( $saved_items ) ) {

				$update = false;

				foreach ( $saved_items as $index => $saved_item ) {

					if ( $product_id == $saved_item['product_id'] ) {
						unset( $saved_items[ $index ] );
						$update = true;
					}

				}

				if ( $update ) {
					update_user_meta( $user_id, '_sa_sfl_saved_items', $saved_items );
					return true;
				}

			}

			return false;

		}

		/**
		 * To move saved items from cookies to user account as soon as they logged in
		 */
		public function move_saved_items_from_cookies_to_account() {

			$user_id = get_current_user_id();

			if ( $user_id > 0 && ! empty( $_COOKIE['sa_saved_for_later_profile_id'] ) ) {

				$unique_id = $_COOKIE['sa_saved_for_later_profile_id'];

				$saved_for_later_products = get_option( 'sa_saved_for_later_profile_' . $unique_id );

				if ( $saved_for_later_products !== false && is_array( $saved_for_later_products ) && ! empty( $saved_for_later_products ) ) {

					$saved_items = get_user_meta( $user_id, '_sa_sfl_saved_items', true );
					if ( empty( $saved_items ) || ! is_array( $saved_items ) ) {
						$saved_items = array();
					}
					$saved_items = array_merge( $saved_items, $saved_for_later_products );
					update_user_meta( $user_id, '_sa_sfl_saved_items', $saved_items );
					wc_setcookie( 'sa_saved_for_later_profile_id', '' );
					delete_option( 'sa_saved_for_later_profile_' . $unique_id );

				}

			}

		}

		/**
		 * To display saved items
		 */
		public function show_saved_items_list() {

			$user_id = get_current_user_id();

			if ( $user_id == 0 ) {
				$saved_items = $this->get_saved_item_from_cookie();
			} else {
				$saved_items = $this->get_saved_item_from_user_account();
			}

			$cart_items = $this->create_cart_items( $saved_items );

			if ( count( $cart_items ) > 0 ) {

				$js = "
						jQuery('.sa_saved_items_list_wrapper').on('click', '.sa_saved_item_actions a.sa_delete_saved_item', function(){
							var element = jQuery(this);
							element.closest('tr').css('opacity', '0.3');
							var saved_item_count = jQuery('.sa_saved_items_list_container span.sa_saved_item_count').text();
							saved_item_count = parseInt( saved_item_count );
							jQuery.ajax({
								url: '" . admin_url( 'admin-ajax.php' ) . "',
								type: 'post',
								dataType: 'json',
								data: {
									action: 'sa_delete_saved_item',
									product_id: element.data('product_id'),
									security: '" . wp_create_nonce( 'sa-saved-item-list-action' ) . "'
								},
								success: function( response ){
									if ( response.success == 'true' ) {
										element.parent().parent().parent().hide('slow', function(){
											element.parent().parent().parent().remove();
										});
										saved_item_count--;
										if ( saved_item_count == 0 ) {
											jQuery('.sa_saved_items_list_wrapper').remove();
										} else if ( saved_item_count == 1 ) {
											jQuery('.sa_saved_items_list_container h2:first').html('" . sprintf(__( 'Saved for later (%s item)', self::$text_domain ), '<span class="sa_saved_item_count">1</span>' ) . "');
										} else {
											jQuery('.sa_saved_items_list_container h2:first').html('" . __( 'Saved for later', self::$text_domain ) . " (<span class=\"sa_saved_item_count\">' + saved_item_count + '</span> " . __( 'items', self::$text_domain ) . ")');
										}
									} else {
										console.log('" . __( 'Error', self::$text_domain ) . "');
									}
								}
							});
						});
						";

				wc_enqueue_js( $js );

				include( apply_filters( 'sa_saved_items_list_template', 'templates/saved-items-list.php' ) );

			}

		}

		/**
		 * Allow overridding of Saved Item's List template
		 *
		 * @param string $template
		 * @return mixed $template
		 */
		public function saved_items_list_template_path( $template ) {

			$template_name  = 'saved-items-list.php';

			$template = $this->locate_template( $template_name, $template );

			// Return what we found
			return $template;

		}

		/**
		 * Locate template for displaying saved items
		 *
		 * @param string $template_name
		 * @param mixed $template
		 * @return mixed $template
		 */
		public function locate_template( $template_name = '', $template = '' ) {

			$default_path   = untrailingslashit( dirname( dirname( __FILE__ ) ) ) . '/templates/';

			$plugin_base_dir = trailingslashit( dirname( dirname( __FILE__ ) ) );

			// Look within passed path within the theme - this is priority
			$template = locate_template(
				array(
					'woocommerce/' . $plugin_base_dir . $template_name,
					$plugin_base_dir . $template_name,
					$template_name
				)
			);

			// Get default template
			if ( ! $template )
				$template = $default_path . $template_name;

			return $template;
		}

		/**
		 * To create cart items in same format in which it will be added to WooCommerce Cart
		 * 
		 * @param array $saved_items
		 * 
		 * @return array $cart_items
		 */
		public function create_cart_items( $saved_items = null ) {

			global $sa_save_for_later;

			if ( empty( $saved_items ) ) return array();

			$cart_items = array();

			foreach ( $saved_items as $saved_item ) {

				$product = wc_get_product( $saved_item['product_id'] );

				if ( $sa_save_for_later->is_wc_gte_30() ) {
					$product_id = $product->get_id();
				} else {
					$product_id = $product->id;
				}

				if ( empty( $product_id ) ) continue;

				if ( $sa_save_for_later->is_wc_gte_30() ) {
					$variation_parent_id = $product->get_parent_id();
					$variation_id = $product->get_id();
					$variation = ( ! empty( $variation_parent_id ) && ( $variation_parent_id != 0 ) ) ? $product->get_variation_attributes() : array();
				} else {
					$variation_id = ( ! empty( $product->variation_id ) ) ? $product->variation_id : null;
					$variation = ( ! empty( $variation_id ) ) ? $product->variation_data : array();
				}

				$cart_item_data = array();

				// Load cart item data when adding to cart
				$cart_item_data = (array) apply_filters( 'woocommerce_add_cart_item_data', $cart_item_data, $product_id, $variation_id );

				// Generate a ID based on product ID, variation ID, variation data, and other cart item data
				$cart_id = WC()->cart->generate_cart_id( $product_id, $variation_id, $variation, $cart_item_data );

				// See if this product and its options is already in the cart
				$cart_item_key = WC()->cart->find_product_in_cart( $cart_id );

				// If cart_item_key is set, the item is already in the cart.
				if ( ! $cart_item_key ) {

					$cart_item_key = $cart_id;

					// Get the product
					$product_data = wc_get_product( $variation_id ? $variation_id : $product_id );

					$cart_item = apply_filters( 'woocommerce_add_cart_item', array_merge( $cart_item_data, array(
									'product_id'   => $product_id,
									'variation_id' => $variation_id,
									'variation'    => $variation,
									'quantity'     => 1,
									'data'         => $product_data
								) ), $cart_item_key );

					$cart_items[ $cart_item_key ] = $cart_item;

				}

			}

			return $cart_items;

		}

		/**
		 * To get saved items list from cookie
		 */
		public function get_saved_item_from_cookie() {

			$saved_items = array();

			if ( ! empty( $_COOKIE['sa_saved_for_later_profile_id'] ) ) {

				$unique_id = $_COOKIE['sa_saved_for_later_profile_id'];

				$saved_items = get_option( 'sa_saved_for_later_profile_' . $unique_id );

			}

			return $saved_items;

		}

		/**
		 * To get saved items list from user account
		 */
		public function get_saved_item_from_user_account() {

			$user_id = get_current_user_id();

			$saved_items = get_user_meta( $user_id, '_sa_sfl_saved_items', true );

			if ( empty( $saved_items ) ) {
				$saved_items = array(); 
			}

			return $saved_items;
			
		}

		/**
		 * To get Save For Later actions html
		 * 
		 * @param string $cart_item_key
		 * 
		 * @return string HTML code for Save For Later actions
		 */
		public function get_save_for_later_actions_html( $cart_item_key = null ) {

			if ( empty( $cart_item_key ) ) return null;

			WC()->cart->get_cart_from_session();

			$cart_item = WC()->cart->get_cart_item( $cart_item_key );

			$_product = $cart_item['data'];

			$product_id = ( ! empty( $cart_item['variation_id'] ) ) ? $cart_item['variation_id'] : $cart_item['product_id'];

			$remove_link_html = sprintf(
									'<a href="%s" title="%s" data-product_id="%s" data-product_sku="%s">%s</a>',
									esc_url( WC()->cart->get_remove_url( $cart_item_key ) ),
									__( 'Delete from cart', self::$text_domain ),
									esc_attr( $product_id ),
									esc_attr( $_product->get_sku() ),
									__( 'Delete from cart', self::$text_domain )
								);

			$save_for_later_link_html = sprintf(
											'<a href="%s" class="button sa_save_for_later" title="%s" data-product_id="%s">%s</a>',
											esc_url( $this->get_save_for_later_url( $cart_item_key ) ),
											__( 'Save for later', self::$text_domain ),
											esc_attr( $product_id ),
											__( 'Save for later', self::$text_domain )
										);

			if ( ! empty( $save_for_later_link_html ) ) {
				$link_html = $save_for_later_link_html . '&nbsp;or&nbsp;&nbsp;' . $remove_link_html;
			} else {
				$link_html = null;
			}

			return $link_html;

		}

		/**
		 * To display Save For Later link in cart with cart item remove link
		 * 
		 * @param string $remove_link HTML code for cart item remove link
		 * @param string $cart_item_key
		 * 
		 * @return string $remove_link including Save For Later link
		 */
		public function cart_item_save_for_later_link( $remove_link = null, $cart_item_key = null ) {

			if ( empty( $cart_item_key ) ) return $remove_link;
			if ( did_action( 'woocommerce_before_cart' ) < 1 ) return $remove_link;

			$suffix       = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			if ( ! wp_script_is( 'jquery-tiptip', 'registered' ) ) {
				wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip' . $suffix . '.js', array( 'jquery' ), WC_VERSION, true );
			}

			if ( ! wp_script_is( 'jquery-tiptip' ) ) {
				wp_enqueue_script( 'jquery-tiptip' );
			}

			if ( ! wp_style_is( 'woocommerce_admin_styles', 'registered' ) ) {
				wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
			}

			if ( ! wp_style_is( 'woocommerce_admin_styles' ) ) {
				wp_enqueue_style( 'woocommerce_admin_styles' );
			}

			WC()->cart->get_cart_from_session();

			$cart_item = WC()->cart->get_cart_item( $cart_item_key );

			$_product = $cart_item['data'];

			$product_id = ( ! empty( $cart_item['variation_id'] ) ) ? $cart_item['variation_id'] : $cart_item['product_id'];

			$link_html = $this->get_save_for_later_actions_html( $cart_item_key );

			if ( ! empty( $link_html ) ) {

				$remove_link = sprintf(
									'<a href="%s" class="remove" title="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
									esc_url( WC()->cart->get_remove_url( $cart_item_key ) ),
									esc_attr( $link_html ),
									esc_attr( $product_id ),
									esc_attr( $_product->get_sku() )
								);

			}

			return $remove_link;
		}

		/**
		 * To generate html code for Svae For Later actions
		 */
		public function get_save_for_later_actions() {

			check_ajax_referer( 'sa-save-for-later-actions', 'security' );

			$cart_item_key = ( ! empty( $_POST['cart_item_key'] ) ) ? $_POST['cart_item_key'] : null;

			$link_html = $this->get_save_for_later_actions_html( $cart_item_key );

			echo $link_html;

			die();

		}

		/**
		 * To delete saved item via AJAX
		 */
		public function sa_delete_saved_item() {

			check_ajax_referer( 'sa-saved-item-list-action', 'security' );

			$product_id = ( ! empty( $_POST['product_id'] ) ) ? $_POST['product_id'] : null;

			if ( empty( $product_id ) ) {
				echo json_encode( array( 'success' => 'false' ) );
				die();
			}

			$deleted = $this->delete_saved_item( $product_id );

			if ( $deleted ) {
				$return = array( 'success' => 'true' );
			} else {
				$return = array( 'success' => 'false' );
			}

			echo json_encode( $return );
			die();

		}

		/**
		 * To get cookie life
		 */
		public function get_cookie_life() {

			$life = get_option( 'sa_saved_for_later_profile_life', 180 );

			return apply_filters( 'sa_saved_for_later_profile_life', time()+(60*60*24*$life) );

		}

		/**
		 * To generate unique id
		 * 
		 * Credit: WooCommerce
		 */
		public function generate_unique_id() {

			require_once( ABSPATH . 'wp-includes/class-phpass.php');
			$hasher = new PasswordHash( 8, false );
			return md5( $hasher->get_random_bytes( 32 ) );

		}

		/**
		 * To add styles & scripts 
		 */
		public function styles_and_scripts() {

			if ( ! is_cart() ) return;

			?>
			<style type="text/css">
				#tiptip_content a.button.sa_save_for_later {
					margin-bottom: 1em;
					display: inline-block;
				}
			</style>
			<?php

			$js = "<!-- Save For Later JavaScript Start -->\n
						var window_width = jQuery(window).width();
						var half_window_width = window_width / 2;
						var window_height = jQuery(window).height();
						var half_window_height = window_height / 2;
						var target_position = jQuery('a.remove').offset();
						var target_left_position = 0;
						var target_top_position = 0;
						var tip_position = 'right';
						var activation_method = 'hover';

						if ( target_position != undefined ) {
							target_left_position = target_position.left;
							target_top_position = target_position.top;
						}

						if ( target_left_position > half_window_width ) {
							tip_position = 'left';
						}

						if ( !!( 'ontouchstart' in window ) ) {
							activation_method = 'click';
						}

						if ( jQuery('a.remove').length > 0 ) {

							jQuery('a.remove').tipTip({
								keepAlive: true,
								activation: activation_method,
								defaultPosition: tip_position,
								edgeOffset: 0,
								delay: 100,
								enter: function(){
									jQuery('#tiptip_content').css('background', '#fff');
									jQuery('#tiptip_content').css('color', '#000');
									jQuery('#tiptip_content').css('border', '1px solid rgba( 128, 128, 128, 0.4 )');
									jQuery('#tiptip_holder').css('z-index', '100');
									jQuery('#tiptip_holder').css('width', '300');
									jQuery('#tiptip_arrow_inner').css('cssText', 'border-'+tip_position+'-color: rgba( 128, 128, 128, 0.6 ) !important');
								}
							});

						}
					<!-- Save For Later JavaScript End -->";

			wc_enqueue_js( $js );

		}

		/**
		 * Get Save For Later url
		 * 
		 * @param string $cart_item_key
		 * 
		 * @return string save for later url
		 */
		public function get_save_for_later_url( $cart_item_key = null ) {

			$cart_page_url = wc_get_page_permalink( 'cart' );

			return apply_filters( 'sa_get_save_for_later_url', $cart_page_url ? wp_nonce_url( add_query_arg( 'sa_save_for_later', $cart_item_key, $cart_page_url ), 'sa-save-for-later-cart' ) : '' );

		}

		/**
		 * Get Move To Cart url
		 * 
		 * @param string $cart_item_key
		 * 
		 * @return string move to cart url
		 */
		public function get_move_to_cart_url( $cart_item_key = null ) {

			$cart_page_url = wc_get_page_permalink( 'cart' );

			return apply_filters( 'sa_get_move_to_cart_url', $cart_page_url ? wp_nonce_url( add_query_arg( 'sa_move_to_cart', $cart_item_key, $cart_page_url ), 'sa-move-to-cart' ) : '' );

		}
	
	} // End class

} // End class exists condition