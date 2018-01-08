<?php
/**
 * WooCommerce Memberships
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Memberships to newer
 * versions in the future. If you wish to customize WooCommerce Memberships for your
 * needs please refer to https://docs.woocommerce.com/document/woocommerce-memberships/ for more information.
 *
 * @package   WC-Memberships/Admin
 * @author    SkyVerge
 * @category  Admin
 * @copyright Copyright (c) 2014-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Handle memberships order data in admin screens.
 *
 * @since 1.9.0
 */
class WC_Memberships_Admin_Orders {


	/**
	 * Handler constructor.
	 *
	 * @since 1.9.0
	 */
	public function __construct() {

		// list user memberships on individual "edit order" screen
		add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'render_order_data' ) );
	}


	/**
	 * Adds User Memberships information to "Edit Order" screen.
	 *
	 * @internal
	 *
	 * @since 1.9.0
	 *
	 * @param \WC_Order|\WC_Order_Refund $order the WooCommerce order
	 */
	public function render_memberships_order_data( $order ) {

		if ( $customer_user = $order instanceof WC_Order || $order instanceof WC_Order_Refund ? get_user_by( 'id', $order->get_user_id() ) : null ) :

			?>
			<p class="form-field form-field-wide wc-customer-memberships">
				<label for="customer_memberships"><?php esc_html_e( 'Active Memberships:', 'woocommerce-memberships' ); ?></label>
				<?php

				$user_id = $order->get_user_id();

				// get all active memberships
				$memberships = wc_memberships()->get_user_memberships_instance()->get_user_memberships( $user_id );

				// count the memberships displayed
				$count = 0;

				if ( ! empty( $memberships ) ) {
					foreach ( $memberships as $membership ) {

						$plan = $membership->get_plan();

						if ( $plan && wc_memberships_is_user_active_member( $user_id, $plan ) ) {
							edit_post_link( esc_html( $plan->name ), '', '<br />', $membership->id );
							$count++;
						}
					}
				}

				if ( 0 === $count ) {
					esc_html_e( 'none', 'woocommerce-memberships' );
				}

				?>
			</p>
			<?php

		endif;
	}


}
