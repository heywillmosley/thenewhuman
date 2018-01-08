<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	if( !function_exists( 'woo_ce_get_export_type_order_count' ) ) {
		function woo_ce_get_export_type_order_count() {

			$count = 0;
			$post_type = 'shop_order';
			$woocommerce_version = woo_get_woo_version();
			// Check if this is a WooCommerce 2.2+ instance (new Post Status)
			if( version_compare( $woocommerce_version, '2.2' ) >= 0 )
				$post_status = ( function_exists( 'wc_get_order_statuses' ) ? apply_filters( 'woo_ce_order_post_status', array_keys( wc_get_order_statuses() ) ) : 'any' );
			else
				$post_status = apply_filters( 'woo_ce_order_post_status', woo_ce_post_statuses() );

			// Override for WordPress MultiSite
			if( woo_ce_is_network_admin() ) {
				$sites = wp_get_sites();
				foreach( $sites as $site ) {
					switch_to_blog( $site['blog_id'] );
					$args = array(
						'post_type' => $post_type,
						'posts_per_page' => 1,
						'post_status' => $post_status,
						'fields' => 'ids'
					);
					$count_query = new WP_Query( $args );
					$count += $count_query->found_posts;
					restore_current_blog();
				}
				return $count;
			}

			// Check if the existing Transient exists
			$cached = get_transient( WOO_CD_PREFIX . '_order_count' );
			if( $cached == false ) {
				$args = array(
					'post_type' => $post_type,
					'posts_per_page' => 1,
					'post_status' => $post_status,
					'fields' => 'ids'
				);
				$count_query = new WP_Query( $args );
				$count = $count_query->found_posts;
				set_transient( WOO_CD_PREFIX . '_order_count', $count, HOUR_IN_SECONDS );
			} else {
				$count = $cached;
			}
			return $count;

		}
	}

	// HTML template for Filter Orders by Order Date widget on Store Exporter screen
	function woo_ce_orders_filter_by_date() {

		$tomorrow = date( 'l', strtotime( 'tomorrow', current_time( 'timestamp' ) ) );
		$today = date( 'l', current_time( 'timestamp' ) );
		$yesterday = date( 'l', strtotime( '-1 days', current_time( 'timestamp' ) ) );
		$current_month = date( 'F', current_time( 'timestamp' ) );
		$last_month = date( 'F', mktime( 0, 0, 0, date( 'n', current_time( 'timestamp' ) )-1, 1, date( 'Y', current_time( 'timestamp' ) ) ) );
		$order_dates_variable = woo_ce_get_option( 'order_dates_filter_variable', '' );
		$order_dates_variable_length = woo_ce_get_option( 'order_dates_filter_variable_length', '' );
		$date_format = woo_ce_get_option( 'date_format', 'd/m/Y' );
		$order_dates_first_order = woo_ce_get_order_first_date( $date_format );
		$order_dates_last_order = woo_ce_get_order_date_filter( 'today', 'from', $date_format );
		$types = woo_ce_get_option( 'order_dates_filter' );
		$order_dates_from = woo_ce_get_option( 'order_dates_from' );
		$order_dates_to = woo_ce_get_option( 'order_dates_to' );
		// Check if the Order Date To/From have been saved
		if( empty( $order_dates_from ) || empty( $order_dates_to ) ) {
			if( empty( $order_dates_from ) )
				$order_dates_from = $order_dates_first_order;
			if( empty( $order_dates_to ) )
				$order_dates_to = $order_dates_last_order;
		}

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-date"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Order Date', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-date" class="separator">
	<ul>
		<li>
			<label><input type="radio" name="order_dates_filter" value=""<?php checked( $types, false ); ?> /> <?php _e( 'All dates', 'woocommerce-exporter' ); ?> (<?php echo $order_dates_from; ?> - <?php echo $order_dates_to; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_dates_filter" value="tomorrow"<?php checked( $types, 'tomorrow' ); ?> /> <?php _e( 'Tomorrow', 'woocommerce-exporter' ); ?> (<?php echo $tomorrow; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_dates_filter" value="today"<?php checked( $types, 'today' ); ?> /> <?php _e( 'Today', 'woocommerce-exporter' ); ?> (<?php echo $today; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_dates_filter" value="yesterday"<?php checked( $types, 'yesterday' ); ?> /> <?php _e( 'Yesterday', 'woocommerce-exporter' ); ?> (<?php echo $yesterday; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_dates_filter" value="current_week"<?php checked( $types, 'current_week' ); ?> /> <?php _e( 'Current week', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_dates_filter" value="last_week"<?php checked( $types, 'last_week' ); ?> /> <?php _e( 'Last week', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_dates_filter" value="current_month"<?php checked( $types, 'current_month' ); ?> /> <?php _e( 'Current month', 'woocommerce-exporter' ); ?> (<?php echo $current_month; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_dates_filter" value="last_month"<?php checked( $types, 'last_month' ); ?> /> <?php _e( 'Last month', 'woocommerce-exporter' ); ?> (<?php echo $last_month; ?>)</label>
		</li>
<!--
		<li>
			<label><input type="radio" name="order_dates_filter" value="last_quarter" /> <?php _e( 'Last quarter', 'woocommerce-exporter' ); ?> (Nov. - Jan.)</label>
		</li>
-->
		<li>
			<label><input type="radio" name="order_dates_filter" value="variable"<?php checked( $types, 'variable' ); ?> /> <?php _e( 'Variable date', 'woocommerce-exporter' ); ?></label>
			<div style="margin-top:0.2em;">
				<?php _e( 'Last', 'woocommerce-exporter' ); ?>
				<input type="text" name="order_dates_filter_variable" class="text code" size="4" maxlength="4" value="<?php echo $order_dates_variable; ?>" />
				<select name="order_dates_filter_variable_length" style="vertical-align:top;">
					<option value=""<?php selected( $order_dates_variable_length, '' ); ?>>&nbsp;</option>
					<option value="second"<?php selected( $order_dates_variable_length, 'second' ); ?>><?php _e( 'second(s)', 'woocommerce-exporter' ); ?></option>
					<option value="minute"<?php selected( $order_dates_variable_length, 'minute' ); ?>><?php _e( 'minute(s)', 'woocommerce-exporter' ); ?></option>
					<option value="hour"<?php selected( $order_dates_variable_length, 'hour' ); ?>><?php _e( 'hour(s)', 'woocommerce-exporter' ); ?></option>
					<option value="day"<?php selected( $order_dates_variable_length, 'day' ); ?>><?php _e( 'day(s)', 'woocommerce-exporter' ); ?></option>
					<option value="week"<?php selected( $order_dates_variable_length, 'week' ); ?>><?php _e( 'week(s)', 'woocommerce-exporter' ); ?></option>
					<option value="month"<?php selected( $order_dates_variable_length, 'month' ); ?>><?php _e( 'month(s)', 'woocommerce-exporter' ); ?></option>
					<option value="year"<?php selected( $order_dates_variable_length, 'year' ); ?>><?php _e( 'year(s)', 'woocommerce-exporter' ); ?></option>
				</select>
			</div>
		</li>
		<li>
			<label><input type="radio" name="order_dates_filter" value="manual"<?php checked( $types, 'manual' ); ?> /> <?php _e( 'Fixed date', 'woocommerce-exporter' ); ?></label>
			<div style="margin-top:0.2em;">
				<input type="text" size="10" maxlength="10" id="order_dates_from" name="order_dates_from" value="<?php echo ( $types == 'manual' ? esc_attr( $order_dates_from ) : esc_attr( $order_dates_first_order ) ); ?>" class="text code datepicker order_export" /> <?php _e( 'to', 'woocommerce-exporter' ); ?> <input type="text" size="10" maxlength="10" id="order_dates_to" name="order_dates_to" value="<?php echo ( $types == 'manual' ? esc_attr( $order_dates_to ) : esc_attr( $order_dates_last_order ) ); ?>" class="text code datepicker order_export" />
				<p class="description"><?php _e( 'Filter the dates of Orders to be included in the export. Default is the date of the first Order to today.', 'woocommerce-exporter' ); ?></p>
			</div>
		</li>
		<li>
			<label><input type="radio" name="order_dates_filter" value="last_export"<?php checked( $types, 'last_export' ); ?>s /> <?php _e( 'Since last export', 'woocommerce-exporter' ); ?></label>
			<p class="description"><?php _e( 'Export Orders which have not previously been included in an export. Decided by whether the <code>_woo_cd_exported</code> custom Post meta key has not been assigned to an Order.', 'woocommerce-exporter' ); ?></p>
		</li>
	</ul>
</div>
<!-- #export-orders-filters-date -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Orders by Customer widget on Store Exporter screen
	function woo_ce_orders_filter_by_customer() {

		$user_count = woo_ce_get_export_type_count( 'user' );
		$list_limit = apply_filters( 'woo_ce_order_filter_customer_list_limit', 100, $user_count );
		if( $user_count < $list_limit )
			$customers = woo_ce_get_customers_list();

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-customer" /> <?php _e( 'Filter Orders by Customer', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-customer" class="separator">
	<ul>
		<li>
<?php if( $user_count < $list_limit ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Customer...', 'woocommerce-exporter' ); ?>" id="order_customer" name="order_filter_customer[]" multiple class="chzn-select" style="width:95%;">
				<option value=""><?php _e( 'Show all customers', 'woocommerce-exporter' ); ?></option>
	<?php if( !empty( $customers ) ) { ?>
		<?php foreach( $customers as $customer ) { ?>
				<option value="<?php echo $customer->ID; ?>"><?php printf( '%s (#%s - %s)', $customer->display_name, $customer->ID, $customer->user_email ); ?></option>
		<?php } ?>
	<?php } ?>
			</select>
<?php } else { ?>
			<input type="text" id="order_customer" name="order_filter_customer" size="20" class="text" />
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Filter Orders by Customer (unique e-mail address) to be included in the export.', 'woocommerce-exporter' ); ?><?php if( $user_count > $list_limit ) { echo ' ' . __( 'Enter a list of User ID\'s separated by a comma character.', 'woocommerce-exporter' ); } ?> <?php _e( 'Default is to include all Orders.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-customer -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Orders by Billing Country widget on Store Exporter screen
	function woo_ce_orders_filter_by_billing_country() {

		$countries = woo_ce_allowed_countries();
		$types = woo_ce_get_option( 'order_billing_country', array() );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-billing_country"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Billing Country', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-billing_country" class="separator">
	<ul>
		<li>
<?php if( !empty( $countries ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Billing Country...', 'woocommerce-exporter' ); ?>" id="order_billing_country" name="order_filter_billing_country[]" multiple class="chzn-select" style="width:95%;">
				<option value=""><?php _e( 'Show all Countries', 'woocommerce-exporter' ); ?></option>
	<?php if( $countries ) { ?>
		<?php foreach( $countries as $country_prefix => $country ) { ?>
				<option value="<?php echo $country_prefix; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $country_prefix, $types, false ), true ) : '' ); ?>><?php printf( '%s (%s)', $country, $country_prefix ); ?></option>
		<?php } ?>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Countries were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Filter Orders by Billing Country to be included in the export. Default is to include all Countries.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-customer -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Orders by Shipping Country widget on Store Exporter screen
	function woo_ce_orders_filter_by_shipping_country() {

		$countries = woo_ce_allowed_countries();
		$types = woo_ce_get_option( 'order_shipping_country', array() );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-shipping_country"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Shipping Country', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-shipping_country" class="separator">
	<ul>
		<li>
<?php if( !empty( $countries ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Shipping Country...', 'woocommerce-exporter' ); ?>" id="order_shipping_country" name="order_filter_shipping_country[]" multiple class="chzn-select" style="width:95%;">
				<option value=""><?php _e( 'Show all Countries', 'woocommerce-exporter' ); ?></option>
	<?php foreach( $countries as $country_prefix => $country ) { ?>
				<option value="<?php echo $country_prefix; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $country_prefix, $types, false ), true ) : '' ); ?>><?php printf( '%s (%s)', $country, $country_prefix ); ?></option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Countries were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Filter Orders by Shipping Country to be included in the export. Default is to include all Countries.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-customer -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Orders by User Role widget on Store Exporter screen
	function woo_ce_orders_filter_by_user_role() {

		$user_roles = woo_ce_get_user_roles();
		// Add Guest Role to the User Roles list
		if( !empty( $user_roles ) ) {
			$user_roles['guest'] = array(
				'name' => __( 'Guest', 'woocommerce-exporter' ),
				'count' => 1
			);
		}
		$types = woo_ce_get_option( 'order_user_roles', array() );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-user_role"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by User Role', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-user_role" class="separator">
	<ul>
		<li>
<?php if( !empty( $user_roles ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a User Role...', 'woocommerce-exporter' ); ?>" name="order_filter_user_role[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $user_roles as $key => $user_role ) { ?>
				<option value="<?php echo $key; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $key, $types, false ), true ) : '' ); ?>><?php echo ucfirst( $user_role['name'] ); ?></option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No User Roles were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the User Roles you want to filter exported Orders by. Default is to include all User Role options.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-user_role -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Orders by Order ID widget on Store Exporter screen
	function woo_ce_orders_filter_by_order_id() {

		$types = woo_ce_get_option( 'order_order_ids' );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-id"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Order ID', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-id" class="separator">
	<ul>
		<li>
			<label for="order_filter_id"><?php _e( 'Order ID', 'woocommerce-exporter' ); ?></label>:<br />
			<input type="text" id="order_filter_id" name="order_filter_id" placeholder="1000,1001,1002" value="<?php echo ( !empty( $types ) ? $types : '' ); ?>" class="text code" style="width:95%;" />
		</li>
	</ul>
	<p class="description"><?php _e( 'Enter the Order ID\'s you want to filter exported Orders by. Multiple Order ID\'s can be entered separated by the \',\' (comma) character, Order ID ranges can be entered separated by the \'-\' (dash) character. Default is to include all Orders.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-user_role -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Orders by Coupon Code widget on Store Exporter screen
	function woo_ce_orders_filter_by_coupon() {

		$args = array(
			'coupon_orderby' => 'ID',
			'coupon_order' => 'DESC'
		);
		$coupons = woo_ce_get_coupons( $args );
		$types = woo_ce_get_option( 'order_coupons', array() );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-coupon"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Coupon Code', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-coupon" class="separator">
	<ul>
		<li>
<?php if( !empty( $coupons ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Coupon...', 'woocommerce-exporter' ); ?>" name="order_filter_coupon[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $coupons as $coupon ) { ?>
				<option value="<?php echo $coupon; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $coupon, $types, false ), true ) : '' ); ?><?php disabled( 0, woo_ce_get_coupon_code_usage( get_the_title( $coupon ) ) ); ?>><?php echo get_the_title( $coupon ); ?> (<?php echo woo_ce_get_coupon_code_usage( get_the_title( $coupon ) ); ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Coupons were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Coupon Codes you want to filter exported Orders by. Default is to include all Orders with and without assigned Coupon Codes.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-coupon -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Orders by Payment Gateway widget on Store Exporter screen
	function woo_ce_orders_filter_by_payment_gateway() {

		$payment_gateways = woo_ce_get_order_payment_gateways();
		$types = woo_ce_get_option( 'order_payment_method', array() );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-payment_gateway"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Payment Gateway', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-payment_gateway" class="separator">
	<ul>
		<li>
<?php if( !empty( $payment_gateways ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Payment Gateway...', 'woocommerce-exporter' ); ?>" name="order_filter_payment_gateway[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $payment_gateways as $payment_gateway ) { ?>
				<option value="<?php echo $payment_gateway->id; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $payment_gateway->id, $types, false ), true ) : '' ); ?><?php disabled( 0, woo_ce_get_order_payment_gateway_usage( $payment_gateway->id ) ); ?>><?php echo ucfirst( woo_ce_format_order_payment_gateway( $payment_gateway->id ) ); ?> (<?php echo woo_ce_get_order_payment_gateway_usage( $payment_gateway->id ); ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Payment Gateways were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Payment Gateways you want to filter exported Orders by. Default is to include all Orders.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-payment_gateway -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Orders by Shipping Gateway widget on Store Exporter screen
	function woo_ce_orders_filter_by_shipping_method() {

		$shipping_methods = woo_ce_get_order_shipping_methods();
		$types = woo_ce_get_option( 'order_shipping_method', array() );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-shipping_method"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Shipping Method', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-shipping_method" class="separator">
	<ul>
		<li>
<?php if( !empty( $shipping_methods ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Shipping Method...', 'woocommerce-exporter' ); ?>" name="order_filter_shipping_method[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $shipping_methods as $shipping_method ) { ?>
				<option value="<?php echo $shipping_method->id; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $shipping_method->id, $types, false ), true ) : '' ); ?>><?php echo woo_ce_format_order_shipping_method( $shipping_method->id ); ?></option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Shipping Methods were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Shipping Methods you want to filter exported Orders by. Default is to include all Orders.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-shipping_method -->
<?php
		ob_end_flush();

	}

	// HTML template for Digital Products on Store Exporter screen
	function woo_ce_orders_filter_by_digital_products() {

		$order_digital_products = woo_ce_get_option( 'order_digital_products', false );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-digital_products"<?php checked( !empty( $order_digital_products ), true ); ?> /> <?php _e( 'Filter Orders by Digital Products', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-digital_products" class="separator">
	<ul>
		<li>
			<label><input type="radio" name="order_filter_digital_products" value=""<?php checked( $order_digital_products, false ); ?> /> <?php _e( 'Export Orders containing both Digital and Physical Products', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_filter_digital_products" value="include_digital"<?php checked( $order_digital_products, 'include_digital' ); ?> /> <?php _e( 'Export Orders containing only Digital Products', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_filter_digital_products" value="exclude_digital"<?php checked( $order_digital_products, 'exclude_digital' ); ?> /> <?php _e( 'Exclude Orders containing any Digital Products', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_filter_digital_products" value="exclude_digital_only"<?php checked( $order_digital_products, 'exclude_digital_only' ); ?> /> <?php _e( 'Exclude Orders containing only Digital Products', 'woocommerce-exporter' ); ?></label>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Digital Products you want to filter exported Orders by. Default is to include all Orders.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-date -->

<?php
		ob_end_flush();

	}

	// HTML template for Order Items Formatting on Store Exporter screen
	function woo_ce_orders_items_formatting() {

		$order_items_formatting = woo_ce_get_option( 'order_items_formatting', 'unique' );

		ob_start(); ?>
<tr class="export-options order-options">
	<th><label for="order_items"><?php _e( 'Order items formatting', 'woocommerce-exporter' ); ?></label></th>
	<td>
		<ul>
			<li>
				<label><input type="radio" name="order_items" value="combined"<?php checked( $order_items_formatting, 'combined' ); ?> />&nbsp;<?php _e( 'Place Order Items within a grouped single Order row', 'woocommerce-exporter' ); ?></label>
				<p class="description"><?php _e( 'For example: <code>Order Items: SKU</code> cell might contain <code>SPECK-IPHONE|INCASE-NANO|-</code> for 3 Order items within an Order', 'woocommerce-exporter' ); ?></p>
			</li>
			<li>
				<label><input type="radio" name="order_items" value="unique"<?php checked( $order_items_formatting, 'unique' ); ?> />&nbsp;<?php _e( 'Place Order Items on individual cells within a single Order row', 'woocommerce-exporter' ); ?></label>
				<p class="description"><?php _e( 'For example: <code>Order Items: SKU</code> would become <code>Order Item #1: SKU</code> with <codeSPECK-IPHONE</code> for the first Order item within an Order', 'woocommerce-exporter' ); ?></p>
			</li>
			<li>
				<label><input type="radio" name="order_items" value="individual"<?php checked( $order_items_formatting, 'individual' ); ?> />&nbsp;<?php _e( 'Place each Order Item within their own Order row', 'woocommerce-exporter' ); ?></label>
				<p class="description"><?php _e( 'For example: An Order with 3 Order items will display a single Order item on each row', 'woocommerce-exporter' ); ?></p>
			</li>
		</ul>
		<p class="description"><?php _e( 'Choose how you would like Order Items to be presented within Orders.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<?php
		ob_end_flush();

	}

	// HTML template for Max Order Items widget on Store Exporter screen
	function woo_ce_orders_max_order_items() {

		$max_order_items = woo_ce_get_option( 'max_order_items', 10 );
		// Default to 10 if empty
		if( empty( $max_order_items ) )
			$max_order_items = 10;

		ob_start(); ?>
<tr id="max_order_items_option" class="export-options order-options">
	<th>
		<label for="max_order_items"><?php _e( 'Max unique Order items', 'woocommerce-exporter' ); ?>: </label>
	</th>
	<td>
		<input type="text" id="max_order_items" name="max_order_items" size="3" class="text" value="<?php echo esc_attr( $max_order_items ); ?>" />
		<p class="description"><?php _e( 'Manage the number of Order Item colums displayed when the \'Place Order Items on individual cells within a single Order row\' Order items formatting option is selected.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<?php
		ob_end_flush();

	}

	// HTML template for Order Items Types on Store Exporter screen
	function woo_ce_orders_items_types() {

		$types = woo_ce_get_order_items_types();
		$order_items_types = woo_ce_get_option( 'order_items_types', array() );

		// Default to Line Item if not set
		if( empty( $order_items_types ) ) {
			$order_items_types = array( 'line_item' );
			// Check if WooCommerce Checkout Add-ons is activated
			if( woo_ce_detect_export_plugin( 'checkout_addons' ) )
				$order_items_types = array( 'line_item', 'fee' );
		}

		ob_start(); ?>
<tr class="export-options order-options">
	<th><label><?php _e( 'Order item types', 'woocommerce-exporter' ); ?></label></th>
	<td>
		<ul>
<?php foreach( $types as $key => $type ) { ?>
			<li><label><input type="checkbox" name="order_items_types[<?php echo $key; ?>]" value="<?php echo $key; ?>"<?php checked( in_array( $key, $order_items_types ), true ); ?> /> <?php echo ucfirst( $type ); ?></label></li>
<?php } ?>
		</ul>
		<p class="description"><?php _e( 'Choose what Order Item types are included within the Orders export. Default is to include all Order Item types.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<?php
		ob_end_flush();

	}

	// HTML template for Add note for exported Order flag widget on Store Exporter screen
	function woo_ce_orders_flag_notes() {

		$order_flag_notes = woo_ce_get_option( 'order_flag_notes', 0 );

		ob_start(); ?>
<tr class="export-options order-options">
	<th><label><?php _e( 'Exported Order notes', 'woocommerce-exporter' ); ?></label></th>
	<td>
		<label><input type="radio" name="order_flag_notes" value="0"<?php checked( $order_flag_notes, 0 ); ?>>&nbsp;<?php _e( 'Do not add private Order notes', 'woocommerce-exporter' ); ?></label><br />
		<label><input type="radio" name="order_flag_notes" value="1"<?php checked( $order_flag_notes, 1 ); ?>>&nbsp;<?php _e( 'Add private Order notes', 'woocommerce-exporter' ); ?></label>
		<p class="description"><?php _e( 'Choose whether Order notes - e.g. Order was exported successfully or Order export flag was cleared - are assigned to exported Orders when using the Since last export Order Filter. Default is not to add Order notes.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<?php
		ob_end_flush();

	}

	// HTML template for Filter Orders by Order Status widget on Store Exporter screen
	function woo_ce_orders_filter_by_status() {

		$order_statuses = woo_ce_get_order_statuses();
		$types = woo_ce_get_option( 'order_status', array() );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-status"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Order Status', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-status" class="separator">
	<ul>
		<li>
<?php if( !empty( $order_statuses ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Order Status...', 'woocommerce-exporter' ); ?>" name="order_filter_status[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $order_statuses as $order_status ) { ?>
				<option value="<?php echo $order_status->slug; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $order_status->slug, $types, false ), true ) : '' ); ?><?php disabled( 0, $order_status->count ); ?>><?php echo ucfirst( $order_status->name ); ?> (<?php echo $order_status->count; ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Order Status\'s were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Order Status you want to filter exported Orders by. Default is to include all Order Status options.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-status -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Orders by Product widget on Store Exporter screen
	function woo_ce_orders_filter_by_product() {

		if( apply_filters( 'woo_ce_override_orders_filter_by_product', true ) == false )
			return;

/*
		// @mod - Removed as the meta_query args are returning empty results
		$product_types = woo_ce_get_product_types();
		// Remove the Product Variation type
		unset( $product_types['variation'] );
		$args = array(
			'product_type' => array_keys( $product_types )
		);
*/
		$args = array();
		$products = woo_ce_get_products( $args );
		add_filter( 'the_title', 'woo_ce_get_product_title_sku', 10, 2 );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-product" /> <?php _e( 'Filter Orders by Product', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-product" class="separator">
	<ul>
		<li>
<?php if( wp_script_is( 'wc-enhanced-select', 'enqueued' ) ) { ?>
			<p><input data-placeholder="<?php _e( 'Search for a Product&hellip;', 'woocommerce-exporter' ); ?>" type="hidden" id="order_filter_product" name="order_filter_product[]" class="multiselect wc-product-search" data-multiple="true" style="width:95;" data-action="woocommerce_json_search_products_and_variations" /></p>
<?php } else { ?>
	<?php if( !empty( $products ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Product...', 'woocommerce-exporter' ); ?>" name="order_filter_product[]" multiple class="chzn-select" style="width:95%;">
		<?php foreach( $products as $product ) { ?>
				<option value="<?php echo $product; ?>"><?php echo woo_ce_format_post_title( get_the_title( $product ) ); ?></option>
		<?php } ?>
			</select>
	<?php } else { ?>
			<?php _e( 'No Products were found.', 'woocommerce-exporter' ); ?></li>
	<?php } ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Products you want to filter exported Orders by. Default is to include all Products.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-product -->
<?php
		ob_end_flush();
		remove_filter( 'the_title', 'woo_ce_get_product_title_sku' );

	}

	// HTML template for Filter Orders by Product Category widget on Store Exporter screen
	function woo_ce_orders_filter_by_product_category() {

		$args = array(
			'hide_empty' => 1
		);
		$product_categories = woo_ce_get_product_categories( $args );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-category" /> <?php _e( 'Filter Orders by Product Category', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-category" class="separator">
	<ul>
		<li>
<?php if( !empty( $product_categories ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Product Category...', 'woocommerce-exporter' ); ?>" name="order_filter_category[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $product_categories as $product_category ) { ?>
				<option value="<?php echo $product_category->term_id; ?>"><?php echo woo_ce_format_product_category_label( $product_category->name, $product_category->parent_name ); ?> (<?php printf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_category->term_id ); ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Product Categories were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Product Categories you want to filter exported Orders by. Product Categories not assigned to Products are hidden from view. Default is to include all Product Categories.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-category -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Orders by Product Tag widget on Store Exporter screen
	function woo_ce_orders_filter_by_product_tag() {

		$args = array(
			'hide_empty' => 1
		);
		$product_tags = woo_ce_get_product_tags( $args );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-tag" /> <?php _e( 'Filter Orders by Product Tag', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-tag" class="separator">
	<ul>
		<li>
<?php if( !empty( $product_tags ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Product Tag...', 'woocommerce-exporter' ); ?>" name="order_filter_tag[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $product_tags as $product_tag ) { ?>
				<option value="<?php echo $product_tag->term_id; ?>"><?php echo $product_tag->name; ?> (<?php printf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_tag->term_id ); ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Product Tags were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Product Tags you want to filter exported Orders by. Product Tags not assigned to Products are hidden from view. Default is to include all Product Tags.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-tag -->
<?php
		ob_end_flush();

	}

	// HTML template for Order Sorting widget on Store Exporter screen
	function woo_ce_order_sorting() {

		$orderby = woo_ce_get_option( 'order_orderby', 'ID' );
		$order = woo_ce_get_option( 'order_order', 'ASC' );

		ob_start(); ?>
<p><label><?php _e( 'Order Sorting', 'woocommerce-exporter' ); ?></label></p>
<div>
	<select name="order_orderby">
		<option value="ID"<?php selected( 'ID', $orderby ); ?>><?php _e( 'Order ID', 'woocommerce-exporter' ); ?></option>
		<option value="title"<?php selected( 'title', $orderby ); ?>><?php _e( 'Order Name', 'woocommerce-exporter' ); ?></option>
		<option value="date"<?php selected( 'date', $orderby ); ?>><?php _e( 'Date Created', 'woocommerce-exporter' ); ?></option>
		<option value="modified"<?php selected( 'modified', $orderby ); ?>><?php _e( 'Date Modified', 'woocommerce-exporter' ); ?></option>
		<option value="rand"<?php selected( 'rand', $orderby ); ?>><?php _e( 'Random', 'woocommerce-exporter' ); ?></option>
	</select>
	<select name="order_order">
		<option value="ASC"<?php selected( 'ASC', $order ); ?>><?php _e( 'Ascending', 'woocommerce-exporter' ); ?></option>
		<option value="DESC"<?php selected( 'DESC', $order ); ?>><?php _e( 'Descending', 'woocommerce-exporter' ); ?></option>
	</select>
	<p class="description"><?php _e( 'Select the sorting of Orders within the exported file. By default this is set to export Orders by Product ID in Desending order.', 'woocommerce-exporter' ); ?></p>
</div>
<?php
		ob_end_flush();

	}

	// HTML template for jump link to Custom Order Fields within Order Options on Store Exporter screen
	function woo_ce_orders_custom_fields_link() {

		ob_start(); ?>
<div id="export-orders-custom-fields-link">
	<p><a href="#export-orders-custom-fields"><?php _e( 'Manage Custom Order Fields', 'woocommerce-exporter' ); ?></a></p>
</div>
<!-- #export-orders-custom-fields-link -->
<?php
		ob_end_flush();

	}

	// HTML template for Custom Orders widget on Store Exporter screen
	function woo_ce_orders_custom_fields() {

		if( $custom_orders = woo_ce_get_option( 'custom_orders', '' ) )
			$custom_orders = implode( "\n", $custom_orders );
		if( $custom_order_items = woo_ce_get_option( 'custom_order_items', '' ) )
			$custom_order_items = implode( "\n", $custom_order_items );
		if( $custom_order_products = woo_ce_get_option( 'custom_order_products', '' ) )
			$custom_order_products = implode( "\n", $custom_order_products );

		$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

		ob_start(); ?>
<form method="post" id="export-orders-custom-fields" class="export-options order-options">
	<div id="poststuff">

		<div class="postbox" id="export-options">
			<h3 class="hndle"><?php _e( 'Custom Order Fields', 'woocommerce-exporter' ); ?></h3>
			<div class="inside">
				<p class="description"><?php _e( 'To include additional custom Order, Order Item or Product meta associated to Order Items in the Export Orders table above fill the appropriate text box then click <em>Save Custom Fields</em>. The saved meta will appear as new export fields to be selected from the Order Fields list.', 'woocommerce-exporter' ); ?></p>
				<p class="description"><?php printf( __( 'For more information on exporting custom Order and Order Item meta consult our <a href="%s" target="_blank">online documentation</a>.', 'woocommerce-exporter' ), $troubleshooting_url ); ?></p>
				<table class="form-table">

					<tr>
						<th>
							<label><?php _e( 'Order meta', 'woocommerce-exporter' ); ?></label>
						</th>
						<td>
							<textarea name="custom_orders" rows="5" cols="70"><?php echo esc_textarea( $custom_orders ); ?></textarea>
							<p class="description"><?php _e( 'Include additional custom Order meta in your export file by adding each custom Order meta name to a new line above. This is case sensitive.<br />For example: <code>Customer UA</code> (new line) <code>Customer IP Address</code>', 'woocommerce-exporter' ); ?></p>
						</td>
					</tr>

					<tr>
						<th>
							<label><?php _e( 'Order Item meta', 'woocommerce-exporter' ); ?></label>
						</th>
						<td>
							<textarea name="custom_order_items" rows="5" cols="70"><?php echo esc_textarea( $custom_order_items ); ?></textarea>
							<p class="description"><?php _e( 'Include additional custom Order Item meta in your export file by adding each custom Order Item meta name to a new line above. This is case sensitive.<br />For example: <code>Personalized Message</code> (new line) <code>_line_total</code>', 'woocommerce-exporter' ); ?></p>
						</td>
					</tr>

					<tr>
						<th>
							<label><?php _e( 'Order Item Product meta', 'woocommerce-exporter' ); ?></label>
						</th>
						<td>
							<textarea name="custom_order_products" rows="5" cols="70"><?php echo esc_textarea( $custom_order_products ); ?></textarea>
							<p class="description"><?php _e( 'Include additional custom Order Item Product meta in your export file by adding each custom Product meta name associated to Order Items to a new line above. This is case sensitive.<br />For example: <code>_sold_individually</code> (new line) <code>_manage_stock</code>', 'woocommerce-exporter' ); ?></p>
						</td>
					</tr>

					<?php do_action( 'woo_ce_orders_custom_fields' ); ?>

				</table>
				<p class="submit">
					<input type="submit" value="<?php _e( 'Save Custom Fields', 'woocommerce-exporter' ); ?>" class="button" />
				</p>
			</div>
			<!-- .inside -->
		</div>
		<!-- .postbox -->

	</div>
	<!-- #poststuff -->
	<input type="hidden" name="action" value="update" />
</form>
<!-- #export-orders-custom-fields -->
<?php
		ob_end_flush();

	}

	function woo_ce_order_dataset_args( $args, $export_type = '' ) {

		// Check if we're dealing with the Order Export Type
		if( $export_type <> 'order' )
			return $args;

		// Merge in the form data for this dataset
		$defaults = array(
			'order_status' => ( isset( $_POST['order_filter_status'] ) ? woo_ce_format_product_filters( array_map( 'sanitize_text_field', $_POST['order_filter_status'] ) ) : false ),
			'order_dates_filter' => ( isset( $_POST['order_dates_filter'] ) ? sanitize_text_field( $_POST['order_dates_filter'] ) : false ),
			'order_dates_from' => ( isset( $_POST['order_dates_from'] ) ? woo_ce_format_order_date( sanitize_text_field( $_POST['order_dates_from'] ) ) : '' ),
			'order_dates_to' => ( isset( $_POST['order_dates_to'] ) ? woo_ce_format_order_date( sanitize_text_field( $_POST['order_dates_to'] ) ) : '' ),
			'order_dates_filter_variable' => ( isset( $_POST['order_dates_filter_variable'] ) ? absint( $_POST['order_dates_filter_variable'] ) : false ),
			'order_dates_filter_variable_length' => ( isset( $_POST['order_dates_filter_variable_length'] ) ? sanitize_text_field( $_POST['order_dates_filter_variable_length'] ) : false ),
			'order_billing_country' => ( isset( $_POST['order_filter_billing_country'] ) ? array_map( 'sanitize_text_field', $_POST['order_filter_billing_country'] ) : false ),
			'order_shipping_country' => ( isset( $_POST['order_filter_shipping_country'] ) ? array_map( 'sanitize_text_field', $_POST['order_filter_shipping_country'] ) : false ),
			'order_user_roles' => ( isset( $_POST['order_filter_user_role'] ) ? woo_ce_format_user_role_filters( array_map( 'sanitize_text_field', $_POST['order_filter_user_role'] ) ) : false ),
			'order_coupons' => ( isset( $_POST['order_filter_coupon'] ) ? woo_ce_format_product_filters( array_map( 'absint', $_POST['order_filter_coupon'] ) ) : false ),
			'order_product' => ( isset( $_POST['order_filter_product'] ) ? woo_ce_format_product_filters( array_map( 'sanitize_text_field', $_POST['order_filter_product'] ) ) : false ),
			'order_category' => ( isset( $_POST['order_filter_category'] ) ? woo_ce_format_product_filters( array_map( 'absint', $_POST['order_filter_category'] ) ) : false ),
			'order_tag' => ( isset( $_POST['order_filter_tag'] ) ? woo_ce_format_product_filters( array_map( 'absint', $_POST['order_filter_tag'] ) ) : false ),
			'order_ids' => ( isset( $_POST['order_filter_id'] ) ? sanitize_text_field( $_POST['order_filter_id'] ) : false ),
			'order_payment' => ( isset( $_POST['order_filter_payment_gateway'] ) ? array_map( 'sanitize_text_field', $_POST['order_filter_payment_gateway'] ) : false ),
			'order_shipping' => ( isset( $_POST['order_filter_shipping_method'] ) ? array_map( 'sanitize_text_field', $_POST['order_filter_shipping_method'] ) : false ),
			'order_items_digital' => ( isset( $_POST['order_filter_digital_products'] ) ? sanitize_text_field( $_POST['order_filter_digital_products'] ) : false ),
			'order_items' => ( isset( $_POST['order_items'] ) ? sanitize_text_field( $_POST['order_items'] ) : false ),
			'order_items_types' => ( isset( $_POST['order_items_types'] ) ? array_map( 'sanitize_text_field', $_POST['order_items_types'] ) : false ),
			'order_flag_notes' => ( isset( $_POST['order_flag_notes'] ) ? absint( $_POST['order_flag_notes'] ) : false ),
			'max_order_items' => ( isset( $_POST['max_order_items'] ) ? absint( $_POST['max_order_items'] ) : 10 ),
			'order_orderby' => ( isset( $_POST['order_orderby'] ) ? sanitize_text_field( $_POST['order_orderby'] ) : false ),
			'order_order' => ( isset( $_POST['order_order'] ) ? sanitize_text_field( $_POST['order_order'] ) : false ),
			'product_image_formatting' => woo_ce_get_option( 'product_image_formatting', 1 ),
			'gallery_formatting' => woo_ce_get_option( 'gallery_formatting', 1 )
		);
		$args = wp_parse_args( $args, $defaults );

		// Default empty values
		if( empty( $args['max_order_items'] ) )
			$args['max_order_items'] = 10;

		// Save dataset export specific options
		if( $args['order_status'] <> woo_ce_get_option( 'order_status' ) )
			woo_ce_update_option( 'order_status', $args['order_status'] );
		if( $args['order_dates_filter'] <> woo_ce_get_option( 'order_dates_filter' ) )
			woo_ce_update_option( 'order_dates_filter', $args['order_dates_filter'] );
		if( $args['order_dates_from'] <> woo_ce_get_option( 'order_dates_from' ) )
			woo_ce_update_option( 'order_dates_from', woo_ce_format_order_date( $args['order_dates_from'], 'save' ) );
		if( $args['order_dates_to'] <> woo_ce_get_option( 'order_dates_to' ) )
			woo_ce_update_option( 'order_dates_to', woo_ce_format_order_date( $args['order_dates_to'], 'save' ) );
		if( $args['order_dates_filter_variable'] <> woo_ce_get_option( 'order_dates_filter_variable' ) )
			woo_ce_update_option( 'order_dates_filter_variable', $args['order_dates_filter_variable'] );
		if( $args['order_dates_filter_variable_length'] <> woo_ce_get_option( 'order_dates_filter_variable_length' ) )
			woo_ce_update_option( 'order_dates_filter_variable_length', $args['order_dates_filter_variable_length'] );
		if( $args['order_billing_country'] <> woo_ce_get_option( 'order_billing_country' ) )
			woo_ce_update_option( 'order_billing_country', $args['order_billing_country'] );
		if( $args['order_shipping_country'] <> woo_ce_get_option( 'order_shipping_country' ) )
			woo_ce_update_option( 'order_shipping_country', $args['order_shipping_country'] );
		if( $args['order_user_roles'] <> woo_ce_get_option( 'order_user_roles' ) )
			woo_ce_update_option( 'order_user_roles', $args['order_user_roles'] );
		if( $args['order_coupons'] <> woo_ce_get_option( 'order_coupons' ) )
			woo_ce_update_option( 'order_coupons', $args['order_coupons'] );
		// Product
		// Category
		// Tag
		if( $args['order_ids'] <> woo_ce_get_option( 'order_order_ids' ) )
			woo_ce_update_option( 'order_order_ids', $args['order_ids'] );
		if( $args['order_payment'] <> woo_ce_get_option( 'order_payment_method' ) )
			woo_ce_update_option( 'order_payment_method', $args['order_payment'] );
		if( $args['order_shipping'] <> woo_ce_get_option( 'order_shipping_method' ) )
			woo_ce_update_option( 'order_shipping_method', $args['order_shipping'] );
		if( $args['order_items_digital'] <> woo_ce_get_option( 'order_digital_products' ) )
			woo_ce_update_option( 'order_digital_products', $args['order_items_digital'] );
		if( $args['order_items'] <> woo_ce_get_option( 'order_items_formatting' ) )
			woo_ce_update_option( 'order_items_formatting', $args['order_items'] );
		if( $args['order_items_types'] <> woo_ce_get_option( 'order_items_types' ) )
			woo_ce_update_option( 'order_items_types', $args['order_items_types'] );
		if( $args['order_flag_notes'] <> woo_ce_get_option( 'order_flag_notes' ) )
			woo_ce_update_option( 'order_flag_notes', $args['order_flag_notes'] );
		if( $args['max_order_items'] <> woo_ce_get_option( 'max_order_items' ) )
			woo_ce_update_option( 'max_order_items', $args['max_order_items'] );
		if( $args['order_orderby'] <> woo_ce_get_option( 'order_orderby' ) )
			woo_ce_update_option( 'order_orderby', $args['order_orderby'] );
		if( $args['order_order'] <> woo_ce_get_option( 'order_order' ) )
			woo_ce_update_option( 'order_order', $args['order_order'] );

		return $args;

	}
	add_filter( 'woo_ce_extend_dataset_args', 'woo_ce_order_dataset_args', 10, 2 );

	/* End of: WordPress Administration */

}

function woo_ce_cron_order_dataset_args( $args, $export_type = '', $is_scheduled = 0 ) {

	// Check if we're dealing with the Order Export Type
	if( $export_type <> 'order' )
		return $args;

	$order_filter_status = false;
	$order_filter_customer = false;
	$order_filter_product = false;
	$order_filter_billing_country = false;
	$order_filter_shipping_country = false;
	$order_filter_payment = false;
	$order_filter_shipping = false;
	$order_filter_user_role = false;
	$order_filter_coupon = false;
	$order_filter_digital = false;
	$order_dates_filter = false;
	$order_filter_date_variable = false;
	$order_filter_date_variable_length = false;
	$order_filter_dates_from = false;
	$order_filter_dates_to = false;
	$max_order_items = woo_ce_get_option( 'max_order_items', 10 );

	if( $is_scheduled ) {
		$scheduled_export = ( $is_scheduled ? absint( get_transient( WOO_CD_PREFIX . '_scheduled_export_id' ) ) : 0 );
		$order_filter_status = get_post_meta( $scheduled_export, '_filter_order_status', true );
		$order_filter_customer = get_post_meta( $scheduled_export, '_filter_order_customer', true );
		$order_filter_product = get_post_meta( $scheduled_export, '_filter_order_product', true );
		$order_filter_billing_country = get_post_meta( $scheduled_export, '_filter_order_billing_country', true );
		$order_filter_shipping_country = get_post_meta( $scheduled_export, '_filter_order_shipping_country', true );
		$order_filter_payment = get_post_meta( $scheduled_export, '_filter_order_payment', true );
		$order_filter_shipping = get_post_meta( $scheduled_export, '_filter_order_shipping', true );
		$order_filter_user_role = get_post_meta( $scheduled_export, '_filter_order_user_role', true );
		$order_filter_coupon = get_post_meta( $scheduled_export, '_filter_order_coupon', true );
		$order_filter_digital = get_post_meta( $scheduled_export, '_filter_order_items_digital', true );
		$order_dates_filter = get_post_meta( $scheduled_export, '_filter_order_date', true );
		if( $order_dates_filter ) {
			switch( $order_dates_filter ) {

				case 'manual':
					$order_filter_dates_from = get_post_meta( $scheduled_export, '_filter_order_dates_from', true );
					$order_filter_dates_to = get_post_meta( $scheduled_export, '_filter_order_dates_to', true );
					break;

				case 'variable':
					$order_filter_date_variable = get_post_meta( $scheduled_export, '_filter_order_date_variable', true );
					$order_filter_date_variable_length = get_post_meta( $scheduled_export, '_filter_order_date_variable_length', true );
					break;

			}
		}
		$max_order_items = get_post_meta( $scheduled_export, '_filter_order_max_order_items', true );
	} else {
		if( isset( $_GET['order_status'] ) ) {
			$order_filter_status = sanitize_text_field( $_GET['order_status'] );
			$order_filter_status = explode( ',', $order_filter_status );
		}
		// Customer
		if( isset( $_GET['order_product'] ) ) {
			$order_filter_product = sanitize_text_field( $_GET['order_product'] );
			$order_filter_product = explode( ',', $order_filter_product );
		}
		if( isset( $_GET['billing_country'] ) ) {
			$order_filter_billing_country = sanitize_text_field( $_GET['billing_country'] );
			$order_filter_billing_country = explode( ',', $order_filter_billing_country );
		}
		if( isset( $_GET['shipping_country'] ) ) {
			$order_filter_shipping_country = sanitize_text_field( $_GET['shipping_country'] );
			$order_filter_shipping_country = explode( ',', $order_filter_shipping_country );
		}
		if( isset( $_GET['payment_gateway'] ) ) {
			$order_filter_payment = sanitize_text_field( $_GET['order_payment'] );
			$order_filter_payment = explode( ',', $order_filter_payment );
		}
		if( isset( $_GET['shipping_method'] ) ) {
			$order_filter_shipping = sanitize_text_field( $_GET['shipping_method'] );
			$order_filter_shipping = explode( ',', $order_filter_shipping );
		}
		// User Role
		// Coupon Code
		if( isset( $_GET['order_items_digital'] ) ) {
			$order_filter_digital = sanitize_text_field( $_GET['order_items_digital'] );
		}
		if( isset( $_GET['order_date_from'] ) || isset( $_GET['order_date_to'] ) ) {
			// @mod - The CRON export engine does not support variable date filtering, yet.
			$order_dates_filter = 'manual';
			$order_filter_dates_from = ( isset( $_GET['order_date_from'] ) ? sanitize_text_field( $_GET['order_date_from'] ) : false );
			$order_filter_dates_to = ( isset( $_GET['order_date_to'] ) ?  sanitize_text_field( $_GET['order_date_to'] ) : false );
		}
		if( isset( $_GET['max_order_items'] ) ) {
			$max_order_items = absint( $_GET['max_order_items'] );
		}
	}

	// Merge in the form data for this dataset
	$defaults = array(
		'order_status' => ( !empty( $order_filter_status ) ? $order_filter_status : false ),
		'order_customer' => ( !empty( $order_filter_customer ) ? (array)$order_filter_customer : array() ),
		'order_product' => ( !empty( $order_filter_product ) ? (array)$order_filter_product : array() ),
		'order_billing_country' => ( !empty( $order_filter_billing_country ) ? array_map( 'sanitize_text_field', $order_filter_billing_country ) : false ),
		'order_shipping_country' => ( !empty( $order_filter_shipping_country ) ? $order_filter_shipping_country : false ),
		'order_payment' => ( !empty( $order_filter_payment ) ? $order_filter_payment : false ),
		'order_shipping' => ( !empty( $order_filter_shipping ) ? array_map( 'sanitize_text_field', $order_filter_shipping ) : false ),
		'order_user_roles' => ( !empty( $order_filter_user_role ) ? array_map( 'sanitize_text_field', $order_filter_user_role ) : false ),
		'order_coupons' => ( !empty( $order_filter_coupon ) ? array_map( 'sanitize_text_field', $order_filter_coupon ) : false ),
		'order_items_digital' => ( !empty( $order_filter_digital ) ? $order_filter_digital : false ),
		'order_dates_filter' => $order_dates_filter,
		'order_dates_filter_variable' => ( !empty( $order_filter_date_variable ) ? absint( $order_filter_date_variable ) : false ),
		'order_dates_filter_variable_length' => ( !empty( $order_filter_date_variable_length ) ? sanitize_text_field( $order_filter_date_variable_length ) : false ),
		'order_dates_from' => ( !empty( $order_filter_dates_from ) ? $order_filter_dates_from : false ),
		'order_dates_to' => ( !empty( $order_filter_dates_to ) ? $order_filter_dates_to : false ),
		'max_order_items' => ( !empty( $max_order_items ) ? $max_order_items : false )
	);
	$args = wp_parse_args( $args, $defaults );

	return $args;

}
add_filter( 'woo_ce_extend_cron_dataset_args', 'woo_ce_cron_order_dataset_args', 10, 3 );

// Returns a list of Order export columns
function woo_ce_get_order_fields( $format = 'full', $post_ID = 0 ) {

	$export_type = 'order';

	$fields = array();
	$fields[] = array(
		'name' => 'purchase_id',
		'label' => __( 'Order ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'post_id',
		'label' => __( 'Post ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'purchase_total',
		'label' => __( 'Order Total', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'purchase_subtotal',
		'label' => __( 'Order Subtotal', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_currency',
		'label' => __( 'Order Currency', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_discount',
		'label' => __( 'Order Discount', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'coupon_code',
		'label' => __( 'Coupon Code', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'coupon_expiry_date',
		'label' => __( 'Coupon Expiry Date', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'coupon_description',
		'label' => __( 'Coupon Description', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'purchase_total_tax',
		'label' => __( 'Order Total Tax', 'woocommerce-exporter' )
	);
/*
	$fields[] = array(
		'name' => 'order_incl_tax',
		'label' => __( 'Order Incl. Tax', 'woocommerce-exporter' )
	);
*/
	$fields[] = array(
		'name' => 'order_subtotal_excl_tax',
		'label' => __( 'Order Subtotal Excl. Tax', 'woocommerce-exporter' )
	);
/*
	$fields[] = array(
		'name' => 'order_tax_rate',
		'label' => __( 'Order Tax Rate', 'woocommerce-exporter' )
	);
*/
	$fields[] = array(
		'name' => 'order_sales_tax',
		'label' => __( 'Sales Tax Total', 'woocommerce-exporter' )
	);
	// Tax Rates
	$tax_rates = woo_ce_get_order_tax_rates();
	if( !empty( $tax_rates ) ) {
		foreach( $tax_rates as $tax_rate ) {
			$fields[] = array(
				'name' => sprintf( 'purchase_total_tax_rate_%d', $tax_rate['rate_id'] ),
				'label' => sprintf( __( 'Order Total Tax: %s', 'woocommerce-exporter' ), $tax_rate['label'] )
			);
		}
	}
	$fields[] = array(
		'name' => 'order_shipping_tax',
		'label' => __( 'Shipping Tax Total', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_incl_tax',
		'label' => __( 'Shipping Incl. Tax', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_excl_tax',
		'label' => __( 'Shipping Excl. Tax', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'refund_total',
		'label' => __( 'Refund Total', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'refund_date',
		'label' => __( 'Refund Date', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_tax_percentage',
		'label' => __( 'Order Tax Percentage', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'payment_gateway_id',
		'label' => __( 'Payment Gateway ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'payment_gateway',
		'label' => __( 'Payment Gateway', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_method_id',
		'label' => __( 'Shipping Method ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_method',
		'label' => __( 'Shipping Method', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_cost',
		'label' => __( 'Shipping Cost', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_weight',
		'label' => __( 'Shipping Weight', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'payment_status',
		'label' => __( 'Order Status', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'post_status',
		'label' => __( 'Post Status', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_key',
		'label' => __( 'Order Key', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'purchase_date',
		'label' => __( 'Order Date', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'purchase_time',
		'label' => __( 'Order Time', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'customer_message',
		'label' => __( 'Customer Message', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'customer_notes',
		'label' => __( 'Customer Notes', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_notes',
		'label' => __( 'Order Notes', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'total_quantity',
		'label' => __( 'Total Quantity', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'total_order_items',
		'label' => __( 'Total Order Items', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'user_id',
		'label' => __( 'User ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'user_name',
		'label' => __( 'Username', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'user_role',
		'label' => __( 'User Role', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'ip_address',
		'label' => __( 'Checkout IP Address', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'browser_agent',
		'label' => __( 'Checkout Browser Agent', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'has_downloads',
		'label' => __( 'Has Downloads', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'has_downloaded',
		'label' => __( 'Has Downloaded', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_full_name',
		'label' => __( 'Billing: Full Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_first_name',
		'label' => __( 'Billing: First Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_last_name',
		'label' => __( 'Billing: Last Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_company',
		'label' => __( 'Billing: Company', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_address',
		'label' => __( 'Billing: Street Address (Full)', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_address_1',
		'label' => __( 'Billing: Street Address 1', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_address_2',
		'label' => __( 'Billing: Street Address 2', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_city',
		'label' => __( 'Billing: City', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_postcode',
		'label' => __( 'Billing: ZIP Code', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_state',
		'label' => __( 'Billing: State (prefix)', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_state_full',
		'label' => __( 'Billing: State', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_country',
		'label' => __( 'Billing: Country (prefix)', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_country_full',
		'label' => __( 'Billing: Country', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_phone',
		'label' => __( 'Billing: Phone Number', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_email',
		'label' => __( 'Billing: E-mail Address', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_full_name',
		'label' => __( 'Shipping: Full Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_first_name',
		'label' => __( 'Shipping: First Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_last_name',
		'label' => __( 'Shipping: Last Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_company',
		'label' => __( 'Shipping: Company', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_address',
		'label' => __( 'Shipping: Street Address (Full)', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_address_1',
		'label' => __( 'Shipping: Street Address 1', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_address_2',
		'label' => __( 'Shipping: Street Address 2', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_city',
		'label' => __( 'Shipping: City', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_postcode',
		'label' => __( 'Shipping: ZIP Code', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_state',
		'label' => __( 'Shipping: State (prefix)', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_state_full',
		'label' => __( 'Shipping: State', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_country',
		'label' => __( 'Shipping: Country (prefix)', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_country_full',
		'label' => __( 'Shipping: Country', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_id',
		'label' => __( 'Order Items: ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_product_id',
		'label' => __( 'Order Items: Product ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_variation_id',
		'label' => __( 'Order Items: Variation ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_sku',
		'label' => __( 'Order Items: SKU', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_name',
		'label' => __( 'Order Items: Product Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_variation',
		'label' => __( 'Order Items: Product Variation', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_image_embed',
		'label' => __( 'Order Items: Featured Image (Embed)', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_description',
		'label' => __( 'Order Items: Product Description', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_excerpt',
		'label' => __( 'Order Items: Product Excerpt', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_publish_date',
		'label' => __( 'Order Items: Publish Date', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_modified_date',
		'label' => __( 'Order Items: Modified Date', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_tax_class',
		'label' => __( 'Order Items: Tax Class', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_quantity',
		'label' => __( 'Order Items: Quantity', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_total',
		'label' => __( 'Order Items: Total', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_subtotal',
		'label' => __( 'Order Items: Subtotal', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_rrp',
		'label' => __( 'Order Items: RRP', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_stock',
		'label' => __( 'Order Items: Stock', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_tax',
		'label' => __( 'Order Items: Tax', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_tax_subtotal',
		'label' => __( 'Order Items: Tax Subtotal', 'woocommerce-exporter' )
	);
	$tax_rates = woo_ce_get_order_tax_rates();
	if( !empty( $tax_rates ) ) {
		foreach( $tax_rates as $tax_rate ) {
			$fields[] = array(
				'name' => sprintf( 'order_items_tax_rate_%d', $tax_rate['rate_id'] ),
				'label' => sprintf( __( 'Order Items: Tax Rate - %s', 'woocommerce-exporter' ), $tax_rate['label'] )
			);
		}
	}
	unset( $tax_rates, $tax_rate );
	$fields[] = array(
		'name' => 'order_items_refund_subtotal',
		'label' => __( 'Order Items: Refund Subtotal', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_refund_quantity',
		'label' => __( 'Order Items: Refund Quantity', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_type',
		'label' => __( 'Order Items: Type', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_type_id',
		'label' => __( 'Order Items: Type ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_category',
		'label' => __( 'Order Items: Category', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_tag',
		'label' => __( 'Order Items: Tag', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_total_sales',
		'label' => __( 'Order Items: Total Sales', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_weight',
		'label' => __( 'Order Items: Weight', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_height',
		'label' => __( 'Order Items: Height', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_width',
		'label' => __( 'Order Items: Width', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_length',
		'label' => __( 'Order Items: Length', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_total_weight',
		'label' => __( 'Order Items: Total Weight', 'woocommerce-exporter' )
	);

	// Drop in our content filters here
	add_filter( 'sanitize_key', 'woo_ce_sanitize_key' );

	// Allow Plugin/Theme authors to add support for additional Order Item fields
	$fields = apply_filters( sprintf( WOO_CD_PREFIX . '_%s_fields', 'order_items' ), $fields, $export_type );

	// Remove our content filters here to play nice with other Plugins
	remove_filter( 'sanitize_key', 'woo_ce_sanitize_key' );

/*
	$fields[] = array(
		'name' => '',
		'label' => __( '', 'woocommerce-exporter' )
	);
*/

	// Drop in our content filters here
	add_filter( 'sanitize_key', 'woo_ce_sanitize_key' );

	// Allow Plugin/Theme authors to add support for additional columns
	$fields = apply_filters( sprintf( WOO_CD_PREFIX . '_%s_fields', $export_type ), $fields, $export_type );

	// Remove our content filters here to play nice with other Plugins
	remove_filter( 'sanitize_key', 'woo_ce_sanitize_key' );

	// Check if we're dealing with an Export Template
	$sorting = false;
	if( !empty( $post_ID ) ) {
		$remember = get_post_meta( $post_ID, sprintf( '_%s_fields', $export_type ), true );
		$hidden = get_post_meta( $post_ID, sprintf( '_%s_hidden', $export_type ), false );
		$sorting = get_post_meta( $post_ID, sprintf( '_%s_sorting', $export_type ), true );
	} else {
		$remember = woo_ce_get_option( $export_type . '_fields', array() );
		$hidden = woo_ce_get_option( $export_type . '_hidden', array() );
	}
	if( !empty( $remember ) ) {
		$remember = maybe_unserialize( $remember );
		$hidden = maybe_unserialize( $hidden );
		$size = count( $fields );
		for( $i = 0; $i < $size; $i++ ) {
			$fields[$i]['disabled'] = ( isset( $fields[$i]['disabled'] ) ? $fields[$i]['disabled'] : 0 );
			$fields[$i]['hidden'] = ( isset( $fields[$i]['hidden'] ) ? $fields[$i]['hidden'] : 0 );
			$fields[$i]['default'] = 1;
			if( isset( $fields[$i]['name'] ) ) {
				// If not found turn off default
				if( !array_key_exists( $fields[$i]['name'], $remember ) )
					$fields[$i]['default'] = 0;
				// Remove the field from exports if found
				if( array_key_exists( $fields[$i]['name'], $hidden ) )
					$fields[$i]['hidden'] = 1;
			}
		}
	}

	switch( $format ) {

		case 'summary':
			$output = array();
			$size = count( $fields );
			for( $i = 0; $i < $size; $i++ ) {
				if( isset( $fields[$i] ) )
					$output[$fields[$i]['name']] = 'on';
			}
			return $output;
			break;

		case 'full':
		default:
			// Load the default sorting
			if( empty( $sorting ) )
				$sorting = woo_ce_get_option( sprintf( '%s_sorting', $export_type ), array() );
			$size = count( $fields );
			for( $i = 0; $i < $size; $i++ ) {
				if( !isset( $fields[$i]['name'] ) ) {
					unset( $fields[$i] );
					continue;
				}
				$fields[$i]['reset'] = $i;
				$fields[$i]['order'] = ( isset( $sorting[$fields[$i]['name']] ) ? $sorting[$fields[$i]['name']] : $i );
			}
			// Check if we are using PHP 5.3 and above
			if( version_compare( phpversion(), '5.3' ) >= 0 )
				usort( $fields, woo_ce_sort_fields( 'order' ) );
			return $fields;
			break;

	}

}

// Check if we should override field labels from the Field Editor
function woo_ce_override_order_field_labels( $fields = array() ) {

	$labels = woo_ce_get_option( 'order_labels', array() );
	if( !empty( $labels ) ) {
		foreach( $fields as $key => $field ) {
			if( isset( $labels[$field['name']] ) )
				$fields[$key]['label'] = $labels[$field['name']];
		}
	}
	return $fields;

}
add_filter( 'woo_ce_order_fields', 'woo_ce_override_order_field_labels', 11 );
add_filter( 'woo_ce_order_items_fields', 'woo_ce_override_order_field_labels', 11 );

// Returns the export column header label based on an export column slug
function woo_ce_get_order_field( $name = null, $format = 'name', $order_items = false ) {

	global $export;

	$output = '';
	if( $name ) {
		$fields = woo_ce_get_order_fields();
		if( WOO_CD_DEBUG )
			error_log( 'woo_ce_get_order_field() > woo_ce_get_order_fields(): ' . ( time() - $export->start_time ) );
		$size = count( $fields );
		for( $i = 0; $i < $size; $i++ ) {
			if( $fields[$i]['name'] == $name ) {
				switch( $format ) {

					case 'name':
						$output = $fields[$i]['label'];
						if( $order_items == 'unique' )
							$output = str_replace( __( 'Order Items: ', 'woocommerce-exporter' ), '', $output );
						break;

					case 'full':
						$output = $fields[$i];
						break;

				}
				$i = $size;
			}
		}
		if( WOO_CD_DEBUG )
			error_log( 'for $fields...: ' . ( time() - $export->start_time ) );
	}
	return $output;

}

// Returns a list of Order IDs
function woo_ce_get_orders( $export_type = 'order', $args = array() ) {

	global $export;

	$limit_volume = -1;
	$offset = 0;

	if( $args ) {
		$order_ids = ( isset( $args['order_ids'] ) ? $args['order_ids'] : false );
		$payment = ( isset( $args['order_payment'] ) ? $args['order_payment'] : false );
		$shipping = ( isset( $args['order_shipping'] ) ? $args['order_shipping'] : false );
		$user_roles = ( isset( $args['order_user_roles'] ) ? $args['order_user_roles'] : false );
		$coupons = ( isset( $args['order_coupons'] ) ? $args['order_coupons'] : false );
		$product = ( isset( $args['order_product'] ) ? $args['order_product'] : false );
		$product_category = ( isset( $args['order_category'] ) ? $args['order_category'] : false );
		$product_tag = ( isset( $args['order_tag'] ) ? $args['order_tag'] : false );
		$product_brand = ( isset( $args['order_brand'] ) ? $args['order_brand'] : false );
		$product_vendor = ( isset( $args['order_product_vendor'] ) ? $args['order_product_vendor'] : false );
		$limit_volume = ( isset( $args['limit_volume'] ) ? $args['limit_volume'] : false );
		$offset = $args['offset'];
		$orderby = ( isset( $args['order_orderby'] ) ? $args['order_orderby'] : 'ID' );
		$order = ( isset( $args['order_order'] ) ? $args['order_order'] : 'ASC' );
		$order_dates_filter = ( isset( $args['order_dates_filter'] ) ? $args['order_dates_filter'] : false );
		switch( $order_dates_filter ) {

			case 'tomorrow':
				$order_dates_from = woo_ce_get_order_date_filter( 'tomorrow', 'from' );
				$order_dates_to = woo_ce_get_order_date_filter( 'tomorrow', 'to' );
				break;

			case 'today':
				$order_dates_from = woo_ce_get_order_date_filter( 'today', 'from' );
				$order_dates_to = woo_ce_get_order_date_filter( 'today', 'to' );
				break;

			case 'yesterday':
				$order_dates_from = woo_ce_get_order_date_filter( 'yesterday', 'from' );
				$order_dates_to = woo_ce_get_order_date_filter( 'yesterday', 'to' );
				break;

			case 'current_week':
				$order_dates_from = woo_ce_get_order_date_filter( 'current_week', 'from' );
				$order_dates_to = woo_ce_get_order_date_filter( 'current_week', 'to' );
				break;

			case 'last_week':
				$order_dates_from = woo_ce_get_order_date_filter( 'last_week', 'from' );
				$order_dates_to = woo_ce_get_order_date_filter( 'last_week', 'to' );
				break;

			case 'current_month':
				$order_dates_from = woo_ce_get_order_date_filter( 'current_month', 'from' );
				$order_dates_to = woo_ce_get_order_date_filter( 'current_month', 'to' );
				break;

			case 'last_month':
				$order_dates_from = woo_ce_get_order_date_filter( 'last_month', 'from' );
				$order_dates_to = woo_ce_get_order_date_filter( 'last_month', 'to' );
				break;

			case 'manual':
				$date_format = woo_ce_get_option( 'date_format', 'd/m/Y' );

				// Populate empty from or to dates
				if( !empty( $args['order_dates_from'] ) ) {
					$order_dates_from = woo_ce_format_order_date( $args['order_dates_from'] );
				} else {
					// Default From date to the first Order
					$order_dates_from = woo_ce_get_order_first_date( $date_format );
				}
				if( !empty( $args['order_dates_to'] ) ) {
					$order_dates_to = woo_ce_format_order_date( $args['order_dates_to'] );
				} else {
					// Default To date to tomorrow
					$order_dates_to = woo_ce_format_order_date( woo_ce_get_order_date_filter( 'today', 'to', $date_format ) );
				}

				// Check if the same date has been provided for both order_dates_from and order_dates_to
				if( $order_dates_from == $order_dates_to ) {
					// Add a day to order_dates_to
					$order_dates_to = woo_ce_format_order_date( date( $date_format, date( strtotime( "+1 day", strtotime( $order_dates_to ) ) ) ) );
				}

				// WP_Query only accepts D-m-Y so we must format dates to that
				if( $date_format <> 'd/m/Y' ) {
					$date_format = woo_ce_format_order_date( $date_format );
					if( function_exists( 'date_create_from_format' ) && function_exists( 'date_format' ) ) {
						if( $order_dates_from = date_create_from_format( $date_format, $order_dates_from ) )
							$order_dates_from = date_format( $order_dates_from, 'd-m-Y' );
						if( $order_dates_to = date_create_from_format( $date_format, $order_dates_to ) )
							$order_dates_to = date_format( $order_dates_to, 'd-m-Y' );
					}
				}
				break;

			case 'variable':
				$order_filter_date_variable = $args['order_dates_filter_variable'];
				$order_filter_date_variable_length = $args['order_dates_filter_variable_length'];
				if( $order_filter_date_variable !== false && $order_filter_date_variable_length !== false ) {
					$timestamp = strtotime( sprintf( '-%d %s', $order_filter_date_variable, $order_filter_date_variable_length ), current_time( 'timestamp', 0 ) );
					$order_dates_from = date( 'd-m-Y', mktime( date( 'H', $timestamp ), date( 'i', $timestamp ), date( 's', $timestamp ), date( 'n', $timestamp ), date( 'd', $timestamp ), date( 'Y', $timestamp ) ) );
					$order_dates_to = woo_ce_get_order_date_filter( 'today', 'to' );
					unset( $order_filter_date_variable, $order_filter_date_variable_length, $timestamp );
				}
				break;

			default:
				$order_dates_from = false;
				$order_dates_to = false;
				break;

		}
		if( !empty( $order_dates_from ) && !empty( $order_dates_to ) ) {
			$order_dates_from = explode( '-', $order_dates_from );
			// Check that a valid date was provided
			if( isset( $order_dates_from[0] ) && isset( $order_dates_from[1] ) && isset( $order_dates_from[2] ) ) {
				$order_dates_from = array(
					'year' => absint( $order_dates_from[2] ),
					'month' => absint( $order_dates_from[1] ),
					'day' => absint( $order_dates_from[0] ),
					'hour' => 0,
					'minute' => 0,
					'second' => 0
				);
			} else {
				$order_dates_from = false;
			}
			$order_dates_to = explode( '-', $order_dates_to );
			// Check that a valid date was provided
			if( isset( $order_dates_to[0] ) && isset( $order_dates_to[1] ) && isset( $order_dates_to[2] ) ) {
				$order_dates_to = array(
					'year' => absint( $order_dates_to[2] ),
					'month' => absint( $order_dates_to[1] ),
					'day' => absint( $order_dates_to[0] ),
					'hour' => 0,
					'minute' => 0,
					'second' => 0
				);
			} else {
				$order_dates_to = false;
			}
		}
		$order_status = ( isset( $args['order_status'] ) ? $args['order_status'] : array() );
		$user_ids = ( isset( $args['order_customer'] ) ? $args['order_customer'] : false );
		$billing_country = ( isset( $args['order_billing_country'] ) ? $args['order_billing_country'] : false );
		$shipping_country = ( isset( $args['order_shipping_country'] ) ? $args['order_shipping_country'] : false );
		$order_items = $args['order_items'];
		$order_items_digital = ( isset( $args['order_items_digital'] ) ? $args['order_items_digital'] : false );
	}
	$post_type = 'shop_order';
	$args = array(
		'post_type' => $post_type,
		'orderby' => $orderby,
		'order' => $order,
		'offset' => $offset,
		'posts_per_page' => $limit_volume,
		'fields' => 'ids',
		'suppress_filters' => false
	);
	$woocommerce_version = woo_get_woo_version();
	// Check if this is a pre-WooCommerce 2.2 instance
	if( version_compare( $woocommerce_version, '2.2' ) >= 0 )
		$args['post_status'] = ( function_exists( 'wc_get_order_statuses' ) ? apply_filters( 'woo_ce_order_post_status', array_keys( wc_get_order_statuses() ) ) : 'any' );
	else
		$args['post_status'] = apply_filters( 'woo_ce_order_post_status', 'publish' );
	// Filter Orders by Post ID
	if( !empty( $order_ids ) ) {
		$has_post_id_ranges = false;
		// Check for Post ID ranges (e.g. 100-199)
		if( strpos( $order_ids, '-' ) !== false )
			$has_post_id_ranges = true;
		// Explode the Post IDs
		$order_ids = explode( ',', $order_ids );
		if( $has_post_id_ranges ) {
			foreach( $order_ids as $key => $order_id ) {
				if( strpos( $order_id, '-' ) !== false ) {
					$order_id_ranges = explode( '-', $order_id );
					if( $order_id_ranges !== false ) {
						$order_id_ranges = range( $order_id_ranges[0], $order_id_ranges[1] );
						unset( $order_ids[$key] );
						$order_ids = array_merge( $order_ids, $order_id_ranges );
					}
					unset( $order_id_ranges );
				}
			}
		}
		unset( $has_post_id_ranges );
		// Check if we're looking up a Sequential Order Number
		if( woo_ce_detect_export_plugin( 'seq' ) || woo_ce_detect_export_plugin( 'seq_pro' ) ) {
			$args['meta_query'][] = array(
				'key' => ( woo_ce_detect_export_plugin( 'seq_pro' ) ? '_order_number_formatted' : '_order_number' ),
				'value' => $order_ids
			);
		} else {
			$size = count( $order_ids );
			if( $size > 1 )
				$args['post__in'] = array_map( 'absint', $order_ids );
			else
				$args['p'] = absint( $order_ids[0] );
		}
	}
	// Filter Orders by Product
	if( $product ) {
		$order_ids = woo_ce_get_product_assoc_order_ids( $product );
		if( $order_ids ) {
			$size = count( $order_ids );
			if( $size > 1 )
				$args['post__in'] = array_map( 'absint', $order_ids );
			else
				$args['p'] = absint( $order_ids[0] );
		}
	}
	// Filter Orders by Payment Method
	if( !empty( $payment ) ) {
		$args['meta_query'][] = array(
			'key' => '_payment_method',
			'value' => $payment
		);
	}
	// Filter Orders by Order Status
	if( !empty( $order_status ) ) {
		// Check if this is a WooCommerce 2.2+ instance (new Post Status)
		if( version_compare( $woocommerce_version, '2.2' ) >= 0 ) {
			$args['post_status'] = $order_status;
			if( $export->cron ) {
				// Something weird is going on so we'll override WordPress on this one
				$args['post_status'] = implode( ',', $order_status );
				$args['suppress_filters'] = false;
				add_filter( 'posts_where' , 'woo_ce_wp_query_order_where_override' );
			}
		} else {
			$term_taxonomy = 'shop_order_status';
			$args['tax_query'] = array(
				array(
					'taxonomy' => $term_taxonomy,
					'field' => 'slug',
					'terms' => $order_status
				)
			);
		}
	}
	if( !empty( $user_ids ) ) {
		// Check if we're dealing with a string or list of users
		if( is_string( $user_ids ) )
			$user_ids = explode( ',', $user_ids );
		$user_emails = array();
		foreach( $user_ids as $user_id ) {
			if( $user = get_userdata( $user_id ) )
				$user_emails[] = $user->user_email;
		}
		if( !empty( $user_emails ) ) {
			$args['meta_query'][] = array(
				'key' => '_billing_email',
				'value' => $user_emails
			);
		}
		unset( $user_id, $user_emails );
	}
	// Filter Orders by Billing Country
	if( !empty( $billing_country ) ) {
		$args['meta_query'][] = array(
			'key' => '_billing_country',
			'value' => $billing_country
		);
	}
	// Filter Orders by Shipping Country
	if( !empty( $shipping_country ) ) {
		$args['meta_query'][] = array(
			'key' => '_shipping_country',
			'value' => $shipping_country
		);
	}
	// Filter Order dates
	if( !empty( $order_dates_from ) && !empty( $order_dates_to ) ) {
		$args['date_query'] = array(
			array(
				'column' => 'post_date',
				'before' => $order_dates_to,
				'after' => $order_dates_from,
				'inclusive' => true
			)
		);
	}
	// Check if we are filtering Orders by Last Export
	if( $order_dates_filter == 'last_export' ) {
		$args['meta_query'][] = array(
			'key' => '_woo_cd_exported',
			'value' => 1,
			'compare' => 'NOT EXISTS'
		);
	}

	// Check if we are only filtering Orders by the Guest User Role
	if( !empty( $user_roles ) ) {
		// Check if we are only filtering Orders by the Guest User Role
		$size = count( $export->args['order_user_roles'] );
		if( $size == 1 && $export->args['order_user_roles'][0] == 'guest' ) {
			$args['meta_query'][] = array(
				'key' => '_customer_user',
				'value' => 0
			);
		}
	}

	$orders = array();

	// Allow other developers to bake in their own filters
	$args = apply_filters( 'woo_ce_get_orders_args', $args );

	$order_ids = new WP_Query( $args );
	// Something weird is going on so we'll override WordPress on this one
	if( !empty( $order_status ) && $export->cron && version_compare( $woocommerce_version, '2.2' ) >= 0 )
		remove_filter( 'posts_where' , 'woo_ce_wp_query_order_where_override' );
	if( $order_ids->posts ) {
		foreach( $order_ids->posts as $order_id ) {

			// Get WooCommerce Order details
			$order = woo_ce_get_order_wc_data( $order_id );

			// Filter Orders by User Roles
			$order->user_id = get_post_meta( $order->id, '_customer_user', true );
			if( !empty( $user_roles ) ) {
				$user_ids = array();
				$size = count( $export->args['order_user_roles'] );
				// Check if we are only filtering Orders by the Guest User Role
				if( ( $size == 1 && $export->args['order_user_roles'][0] == 'guest' ) == false ) {
					for( $i = 0; $i < $size; $i++ ) {
						$args = array(
							'role' => $export->args['order_user_roles'][$i],
							'fields' => 'ID'
						);
						$user_id = get_users( $args );
						$user_ids = array_merge( $user_ids, $user_id );
					}
					if( !in_array( $order->user_id, $user_ids ) ) {
						unset( $order );
						continue;
					}
				}
			}

			// Filter Orders by Coupons
			$order->coupon_code = woo_ce_get_order_assoc_coupon( $order->id );
			if( $coupons ) {
				$coupon_ids = array();
				$size = count( $export->args['order_coupons'] );
				for( $i = 0; $i < $size; $i++ )
					$coupon_ids[] = get_the_title( $coupons[$i] );
				if( !in_array( $order->coupon_code, $coupon_ids ) ) {
					unset( $order );
					continue;
				}
			}

			// Filter Orders by Product Category
			if( $product_category ) {
				if( $order_items = woo_ce_get_order_item_ids( $order->id ) ) {
					$term_taxonomy = 'product_cat';
					$args = array(
						'fields' => 'ids'
					);
					$category_ids = array();
					foreach( $order_items as $order_item ) {
						if( $product_categories = wp_get_post_terms( $order_item->product_id, $term_taxonomy, $args ) ) {
							$category_ids = array_merge( $category_ids, $product_categories );
							unset( $product_categories );
						}
					}
					if( count( array_intersect( $product_category, $category_ids ) ) == 0 ) {
						unset( $order );
						continue;
					}
					unset( $category_ids );
				} else {
					// If the Order has no Order Items assigned to it we can safely remove it from the export
					unset( $order );
					continue;
				}
				unset( $order_items );
			}

			// Filter Orders by Product Tag
			if( $product_tag ) {
				if( $order_items = woo_ce_get_order_item_ids( $order->id ) ) {
					$term_taxonomy = 'product_tag';
					$args = array(
						'fields' => 'ids'
					);
					$tag_ids = array();
					foreach( $order_items as $order_item ) {
						if( $product_tags = wp_get_post_terms( $order_item->product_id, $term_taxonomy, $args ) ) {
							$tag_ids = array_merge( $tag_ids, $product_tags );
							unset( $product_tags );
						}
					}
					if( empty( $tag_ids ) || count( array_intersect( $product_tag, $tag_ids ) ) == 0 ) {
						unset( $order );
						continue;
					}
					unset( $tag_ids );
				} else {
					// If the Order has no Order Items assigned to it we can safely remove it from the export
					unset( $order );
					continue;
				}
				unset( $order_items );
			}

			// Filter Orders by Product Brand
			if( $product_brand ) {
				if( $order_items = woo_ce_get_order_item_ids( $order->id ) ) {
					$term_taxonomy = apply_filters( 'woo_ce_brand_term_taxonomy', 'product_brand' );
					$args = array(
						'fields' => 'ids'
					);
					$brand_ids = array();
					foreach( $order_items as $order_item ) {
						if( $product_brands = wp_get_post_terms( $order_item->product_id, $term_taxonomy, $args ) ) {
							$brand_ids = array_merge( $brand_ids, $product_brands );
							unset( $product_brands );
						}
					}
					if( empty( $brand_ids ) || count( array_intersect( $product_brand, $brand_ids ) ) == 0 ) {
						unset( $order );
						continue;
					}
					unset( $brand_ids );
				} else {
					// If the Order has no Order Items assigned to it we can safely remove it from the export
					unset( $order );
					continue;
				}
				unset( $order_items );
			}

			// Filter Orders by Shipping Method
			if( $shipping ) {
				$shipping_id = woo_ce_get_order_assoc_shipping_method_id( $order->id );
				if( !in_array( $shipping_id, $shipping ) ) {
					unset( $order );
					continue;
				}
				unset( $shipping_id );
			}

			// Filter Orders by Product Vendor
			if( $product_vendor ) {
				// Get a list of Orders by the selected Product Vendors
				$vendor_ids = woo_ce_get_product_vendor_assoc_orders( $product_vendor );
				if( !empty( $vendor_ids ) ) {
					if( !in_array( $order->id, $vendor_ids ) ) {
						unset( $order );
						continue;
					}
				}
				unset( $vendor_ids );
			}

			// Filter Orders by Digital Products
			if( $order_items_digital ) {
				if( $order_items = woo_ce_get_order_item_ids( $order->id ) ) {
					switch( $order_items_digital ) {

						// Filter Orders by Digital-only Orders
						case 'include_digital':
							$exclude = false;
							foreach( $order_items as $order_item ) {
								$downloadable = get_post_meta( $order_item->product_id, '_downloadable', true );
								if( $downloadable == false ||  $downloadable == 'no' ) {
									$exclude = true;
									// Stop scanning Order Items
									break;
								}
							}
							if( $exclude ) {
								// Do not include this Order ID in the export
								unset( $order );
								continue 2;
							}
							break;

						// Exclude Orders with Digital Products from Orders export
						case 'exclude_digital':
							$exclude = false;
							foreach( $order_items as $order_item ) {
								$downloadable = get_post_meta( $order_item->product_id, '_downloadable', true );
								if( $downloadable == 'yes' ) {
									$exclude = true;
									// Stop scanning Order Items
									break;
								}
							}
							if( $exclude ) {
								// Do not include this Order ID in the export
								unset( $order );
								continue 2;
							}
							break;

						// Exclude Digital-only Orders from Orders export
						case 'exclude_digital_only':
							$exclude = array();
							foreach( $order_items as $order_item ) {
								$downloadable = get_post_meta( $order_item->product_id, '_downloadable', true );
								$exclude[] = ( $downloadable == 'yes' ? 1 : 0 );
							}
							$exclude = array_values( $exclude );
							// Check if the Order contains Digital Products but no Physical Products
							if( in_array( 1, array_values( $exclude ) ) && in_array( 0, array_values( $exclude ) ) == false ) {
								// Remove if there are no physical Products in that Order
								unset( $order );
								continue 2;
							}
							break;

					}
					unset( $exclude );
				}
				unset( $order_items );
			}

			// Filter Orders by Booking Start Date

			$order->id = apply_filters( 'woo_ce_get_order_id', $order->id );
			if( $order->id )
				$orders[] = $order->id;

			// Mark this Order as exported if Since last export Date filter is used
			if( $order_dates_filter == 'last_export' && !empty( $order->id ) ) {
				update_post_meta( $order->id, '_woo_cd_exported', 1 );
				$order_flag_notes = woo_ce_get_option( 'order_flag_notes', 0 );

				// Override if this is a Scheduled Export
				$scheduled_export = ( $export->scheduled_export ? absint( get_transient( WOO_CD_PREFIX . '_scheduled_export_id' ) ) : 0 );
				if( $scheduled_export ) {
					$single_export_order_flag_notes = get_post_meta( $scheduled_export, '_filter_order_flag_notes', true );
					if( $single_export_order_flag_notes != false )
						$order_flag_notes = $single_export_order_flag_notes;
					unset( $single_export_order_flag_notes );
				}

				if( $order_flag_notes ) {
					// Add an Order Note
					$note = __( 'Order was exported successfully.', 'woocommerce-exporter' );
					if( method_exists( $order, 'add_order_note' ) )
						$order->add_order_note( $note );
					unset( $note );
				}
			}

		}
		// Only populate the $export Global if it is an export
		if( isset( $export ) ) {
			$export->total_rows = count( $orders );
			if( !empty( $order_ids ) ) {
				// Check if we're looking up a Sequential Order Number
				if( woo_ce_detect_export_plugin( 'seq' ) || woo_ce_detect_export_plugin( 'seq_pro' ) )
					$export->order_ids_raw = $orders;
			}
		}
		unset( $order_ids, $order_id );
	}
	switch( $export_type ) {

		case 'order':
			if( WOO_CD_DEBUG !== true ) {
				if( $order_dates_filter == 'last_export' ) {
					// Save the Order ID's list to a WordPress Transient incase the export fails
					woo_ce_update_option( 'exported', $orders );
				}
			}
			return $orders;
			break;

		case 'customer':
			$customers = array();
			if( !empty( $orders ) ) {
				foreach( $orders as $order_id ) {
					$order = woo_ce_get_order_data( $order_id, 'customer', $export->args );
					if( $duplicate_key = woo_ce_is_duplicate_customer( $customers, $order ) ) {
						$customers[$duplicate_key]->total_spent = $customers[$duplicate_key]->total_spent + woo_ce_format_price( get_post_meta( $order_id, '_order_total', true ) );
						$customers[$duplicate_key]->total_orders++;
						if( strtolower( $order->payment_status ) == 'completed' )
							$customers[$duplicate_key]->completed_orders++;
					} else {
						$customers[$order_id] = $order;
						$customers[$order_id]->total_spent = woo_ce_format_price( get_post_meta( $order_id, '_order_total', true ) );
						$customers[$order_id]->completed_orders = 0;
						if( strtolower( $order->payment_status ) == 'completed' )
							$customers[$order_id]->completed_orders = 1;
						$customers[$order_id]->total_orders = 1;
					}
				}
			}
			return $customers;
			break;

	}

}

function woo_ce_wp_query_order_where_override( $where ) {

	global $export, $wpdb;

	$order_status = ( isset( $export->args['order_status'] ) ? $export->args['order_status'] : false );

	// Skip this if we're dealing with stock WordPress Post Status
	if( count( array_intersect( array( 'trash', 'publish' ), $order_status ) ) )
		return $where;

	// Let's add in our custom Post Status parameters
	if( !empty( $order_status ) ) {
		foreach( $order_status as $key => $status ) {
			if( empty( $status ) ) {
				unset( $order_status[$key] );
				continue;
			}
			$order_status[$key] = " " . $wpdb->posts . ".post_status = '$status'";
		}
		if( !empty( $order_status ) )
			$where .= " AND (" . join( ' OR ', $order_status ) . ")";
	}

	return $where;

}

// Returns WooCommerce Order data associated to a specific Order
function woo_ce_get_order_wc_data( $order_id = 0 ) {

	if( !empty( $order_id ) ) {
		$order = ( class_exists( 'WC_Order' ) ? new WC_Order( $order_id ) : get_post( $order_id ) );
		return $order;
	}

}

function woo_ce_get_order_data( $order_id = 0, $export_type = 'order', $args = array(), $fields = array() ) {

	global $export;

	// Check if this is a pre-WooCommerce 2.2 instance
	$woocommerce_version = woo_get_woo_version();

	$defaults = array(
		'order_items' => 'combined',
		'order_items_types' => array_keys( woo_ce_get_order_items_types() )
	);
	$args = wp_parse_args( $args, $defaults );

	// Get WooCommerce Order details
	$order = woo_ce_get_order_wc_data( $order_id );

	$order->ID = ( isset( $order->id ) ? $order->id : $order_id );
	$order->payment_status = $order->status;

	$order->post_status = woo_ce_format_post_status( $order->post_status );
	$order->user_id = get_post_meta( $order_id, '_customer_user', true );
	if( $order->user_id == 0 ) {
		$order->user_id = '';
	} else {
		$order->user_name = woo_ce_get_username( $order->user_id );
		$order->user_role = woo_ce_format_user_role_label( woo_ce_get_user_role( $order->user_id ) );
	}
	$order->purchase_total = get_post_meta( $order_id, '_order_total', true );
	$order->refund_total = ( method_exists( $order, 'get_total_refunded' ) ? $order->get_total_refunded() : '' );
	$order->refund_date = ( !empty( $order->refund_total ) ? woo_ce_get_order_assoc_refund_date( $order_id ) : '' );
	$order->order_currency = get_post_meta( $order_id, '_order_currency', true );

	$order->billing_first_name = get_post_meta( $order_id, '_billing_first_name', true );
	$order->billing_last_name = get_post_meta( $order_id, '_billing_last_name', true );
	if( empty( $order->billing_first_name ) && empty( $order->billing_first_name ) )
		$order->billing_full_name = '';
	else
		$order->billing_full_name = $order->billing_first_name . ' ' . $order->billing_last_name;
	$order->billing_company = get_post_meta( $order_id, '_billing_company', true );
	$order->billing_address = '';
	$order->billing_address_1 = get_post_meta( $order_id, '_billing_address_1', true );
	$order->billing_address_2 = get_post_meta( $order_id, '_billing_address_2', true );
	if( !empty( $order->billing_address_2 ) )
		$order->billing_address = sprintf( apply_filters( 'woo_ce_get_order_data_billing_address', '%s %s' ), $order->billing_address_1, $order->billing_address_2 );
	else
		$order->billing_address = $order->billing_address_1;
	$order->billing_city = get_post_meta( $order_id, '_billing_city', true );
	$order->billing_postcode = get_post_meta( $order_id, '_billing_postcode', true );
	$order->billing_state = get_post_meta( $order_id, '_billing_state', true );
	$order->billing_country = get_post_meta( $order_id, '_billing_country', true );
	$order->billing_state_full = woo_ce_expand_state_name( $order->billing_country, $order->billing_state );
	$order->billing_country_full = woo_ce_expand_country_name( $order->billing_country );
	$order->billing_phone = get_post_meta( $order_id, '_billing_phone', true );
	$order->billing_email = get_post_meta( $order_id, '_billing_email', true );
	// If the e-mail address is empty check if the Order has a User assigned to it
	if( empty( $order->billing_email ) ) {
		// Check if a User ID has been assigned
		if( !empty( $order->user_id ) ) {
			$user = woo_ce_get_user_data( $order->user_id );
			// Check if the User is valid and e-mail assigned to User
			if( isset( $user->email ) )
				$order->billing_email = $user->email;
			unset( $user );
		}
	}
	$order->shipping_first_name = get_post_meta( $order_id, '_shipping_first_name', true );
	$order->shipping_last_name = get_post_meta( $order_id, '_shipping_last_name', true );
	if( empty( $order->shipping_first_name ) && empty( $order->shipping_last_name ) )
		$order->shipping_full_name = '';
	else
		$order->shipping_full_name = $order->shipping_first_name . ' ' . $order->shipping_last_name;
	$order->shipping_company = get_post_meta( $order_id, '_shipping_company', true );
	$order->shipping_address = '';
	$order->shipping_address_1 = get_post_meta( $order_id, '_shipping_address_1', true );
	$order->shipping_address_2 = get_post_meta( $order_id, '_shipping_address_2', true );
	if( !empty( $order->billing_address_2 ) )
		$order->shipping_address = sprintf( apply_filters( 'woo_ce_get_order_data_shipping_address', '%s %s' ), $order->shipping_address_1, $order->shipping_address_2 );
	else
		$order->shipping_address = $order->shipping_address_1;
	$order->shipping_city = get_post_meta( $order_id, '_shipping_city', true );
	$order->shipping_postcode = get_post_meta( $order_id, '_shipping_postcode', true );
	$order->shipping_state = get_post_meta( $order_id, '_shipping_state', true );
	$order->shipping_country = get_post_meta( $order_id, '_shipping_country', true );
	$order->shipping_state_full = woo_ce_expand_state_name( $order->shipping_country, $order->shipping_state );
	$order->shipping_country_full = woo_ce_expand_country_name( $order->shipping_country );
	$order->shipping_phone = get_post_meta( $order_id, '_shipping_phone', true );

	if( $export_type == 'order' ) {

		$order->post_id = $order->purchase_id = $order_id;
		$order->order_discount = get_post_meta( $order_id, '_cart_discount', true );
		$order->coupon_code = woo_ce_get_order_assoc_coupon( $order_id );
		if( !empty( $order->coupon_code ) ) {
			$coupon = get_page_by_title( $order->coupon_code, OBJECT, 'shop_coupon' );
			if( $coupon !== null ) {
				$order->coupon_description = $coupon->post_excerpt;
				$order->coupon_expiry_date = woo_ce_format_date( get_post_meta( $coupon->ID, 'expiry_date', true ) );
			}
			unset( $coupon );
		}
		$order->order_sales_tax = get_post_meta( $order_id, '_order_tax', true );
		$order->order_shipping_tax = get_post_meta( $order_id, '_order_shipping_tax', true );
		$order->shipping_cost = get_post_meta( $order_id, '_order_shipping', true );
		$order->shipping_incl_tax = ( $order->shipping_cost + $order->order_shipping_tax );
		$order->shipping_excl_tax = ( $order->shipping_cost - $order->order_shipping_tax );
		$order->purchase_total_tax = ( $order->order_sales_tax + $order->order_shipping_tax );
		if( !empty( $order->purchase_total_tax ) ) {
			// Tax Rates
			$tax_rates = woo_ce_get_order_tax_rates( $order_id );
			if( !empty( $tax_rates ) ) {
				foreach( $tax_rates as $tax_rate ) {
					$order->{sprintf( 'purchase_total_tax_rate_%d', $tax_rate['rate_id'] )} = woo_ce_format_price( woo_ce_get_order_assoc_tax_rate_total( $order_id, $tax_rate['rate_id'] ), $order->order_currency );
				}
			}
			unset( $tax_rates, $tax_rate );
		}
		$order->purchase_total = $order->purchase_total - $order->refund_total;
		$order->order_subtotal_excl_tax = ( $order->purchase_total - $order->purchase_total_tax );
		$order->purchase_subtotal = $order->order_subtotal_excl_tax - $order->shipping_cost;
		// Order Tax Percentage - Order Total - Total Tax / Total Tax
		$order->order_tax_percentage = 0;
		if( !empty( $order->purchase_total_tax ) && !empty( $order->purchase_total ) ) {
			$order_tax_percentage = apply_filters( 'woo_ce_override_order_tax_percentage_format', '%d%%' );
			// Fetch the tax rates assigned to this Order
			$tax_rates = woo_ce_get_order_assoc_tax_rates( $order_id );
			if( !empty( $tax_rates ) ) {
				foreach( $tax_rates as $tax_rate ) {
					// Take the Rate ID and fetch the Rate % from the WooCommerce Tax Rates table
					$order->order_tax_percentage = sprintf( $order_tax_percentage, floatval( woo_ce_get_order_tax_percentage( $tax_rate['rate_id'] ) ) );
					break;
				}
			}
			unset( $tax_rates, $tax_rate, $order_tax_percentage );
		}
		$order->purchase_total = woo_ce_format_price( $order->purchase_total, $order->order_currency );
		$order->order_sales_tax = woo_ce_format_price( $order->order_sales_tax, $order->order_currency );
		$order->order_shipping_tax = woo_ce_format_price( $order->order_shipping_tax, $order->order_currency );
		$order->purchase_subtotal = woo_ce_format_price( $order->purchase_subtotal, $order->order_currency );
		$order->order_discount = woo_ce_format_price( $order->order_discount, $order->order_currency );
		$order->order_subtotal_excl_tax = woo_ce_format_price( $order->order_subtotal_excl_tax, $order->order_currency );
		$order->refund_total = woo_ce_format_price( $order->refund_total, $order->order_currency );
		$order->payment_status = woo_ce_format_order_status( $order->payment_status );
		$order->payment_gateway_id = get_post_meta( $order_id, '_payment_method', true );
		$order->payment_gateway = woo_ce_format_order_payment_gateway( $order->payment_gateway_id );
		// WooCommerce 2.1 stores the shipping method in Order Items, includes fallback support
		if( method_exists( $order, 'get_shipping_method' ) ) {
			$order->shipping_method_id = woo_ce_get_order_assoc_shipping_method_id( $order_id );
			$order->shipping_method = $order->get_shipping_method();
		} else {
			$order->shipping_method_id = get_post_meta( $order_id, '_shipping_method', true );
			$order->shipping_method = '';
		}
		$order->shipping_cost = woo_ce_format_price( $order->shipping_cost, $order->order_currency );
		$order->shipping_excl_tax = woo_ce_format_price( $order->shipping_excl_tax, $order->order_currency );
		$order->purchase_total_tax = woo_ce_format_price( $order->purchase_total_tax, $order->order_currency );
		$order->shipping_weight = '';
		$order->order_key = get_post_meta( $order_id, '_order_key', true );
		$order->purchase_date = woo_ce_format_date( $order->order_date );
		$order->purchase_time = mysql2date( 'H:i:s', $order->order_date );
		$order->ip_address = woo_ce_format_ip_address( get_post_meta( $order_id, '_customer_ip_address', true ) );
		$order->browser_agent = get_post_meta( $order_id, '_customer_user_agent', true );
		$order->has_downloads = 0;
		$order->has_downloaded = 0;
		// Order Downloads
		if( $order_downloads = woo_ce_get_order_assoc_downloads( $order_id ) ) {
			$order->has_downloads = 1;
			foreach( $order_downloads as $order_download ) {
				// Check if any download permissions have counts against them
				if( $order_download->download_count > 0 ) {
					$order->has_downloaded = 1;
					break;
				}
			}
		}
		unset( $order_downloads, $order_download );
		$order->has_downloads = woo_ce_format_switch( $order->has_downloads );
		$order->has_downloaded = woo_ce_format_switch( $order->has_downloaded );
		$order->customer_notes = '';
		$order->order_notes = '';
		$order->total_quantity = 0;
		$order->total_order_items = 0;
		// Order Notes
		if( $order_notes = woo_ce_get_order_assoc_notes( $order_id ) ) {
			if( WOO_CD_DEBUG )
				$order->order_notes = implode( $export->category_separator, $order_notes );
			else
				$order->order_notes = implode( "\n", $order_notes );
			unset( $order_notes );
		}
		// Customer Notes
		if( $order_notes = woo_ce_get_order_assoc_notes( $order_id, 'customer_note' ) ) {
			if( WOO_CD_DEBUG )
				$order->customer_notes = implode( $export->category_separator, $order_notes );
			else
				$order->customer_notes = implode( "\n", $order_notes );
			unset( $order_notes );
		}
		if( $order->order_items = woo_ce_get_order_items( $order_id, $args['order_items_types'] ) ) {
			$order->total_order_items = count( $order->order_items );
			if( $args['order_items'] == 'combined' ) {
				$order->order_items_id = '';
				$order->order_items_product_id = '';
				$order->order_items_variation_id = '';
				$order->order_items_sku = '';
				$order->order_items_name = '';
				$order->order_items_variation = '';
				$order->order_items_image_embed = '';
				$order->order_items_description = '';
				$order->order_items_excerpt = '';
				$order->order_items_publish_date = '';
				$order->order_items_modified_date = '';
				$order->order_items_tax_class = '';
				$order->order_items_quantity = '';
				$order->order_items_total = '';
				$order->order_items_subtotal = '';
				$order->order_items_rrp = '';
				$order->order_items_stock = '';
				$order->order_items_tax = '';
				$order->order_items_tax_subtotal = '';
				$order->order_items_refund_subtotal = '';
				$order->order_items_refund_quantity = '';
				$order->order_items_type = '';
				$order->order_items_type_id = '';
				$order->order_items_category = '';
				$order->order_items_tag = '';
				$order->order_items_total_sales = '';
				$order->order_items_weight = '';
				$order->order_items_height = '';
				$order->order_items_width = '';
				$order->order_items_length = '';
				$order->order_items_total_weight = '';
				if( !empty( $order->order_items ) ) {
					foreach( $order->order_items as $order_item ) {
						if( empty( $order_item->sku ) )
							$order_item->sku = '';
						$order->order_items_id .= $order_item->id . $export->category_separator;
						$order->order_items_product_id .= $order_item->product_id . $export->category_separator;
						$order->order_items_variation_id .= $order_item->variation_id . $export->category_separator;
						$order->order_items_sku .= $order_item->sku . $export->category_separator;
						$order->order_items_name .= $order_item->name . $export->category_separator;
						$order->order_items_variation .= $order_item->variation . $export->category_separator;
						$order->order_items_image_embed .= $order_item->image_embed . $export->category_separator;
						$order->order_items_description .= woo_ce_format_description_excerpt( $order_item->description ) . $export->category_separator;
						$order->order_items_excerpt .= woo_ce_format_description_excerpt( $order_item->excerpt ) . $export->category_separator;
						$order->order_items_publish_date .= $order_item->publish_date . $export->category_separator;
						$order->order_items_modified_date .= $order_item->modified_date . $export->category_separator;
						$order->order_items_tax_class .= $order_item->tax_class . $export->category_separator;
						$order->total_quantity += $order_item->quantity;
						if( empty( $order_item->quantity ) && '0' != $order_item->quantity )
							$order_item->quantity = '';
						$order->order_items_quantity .= $order_item->quantity . $export->category_separator;
						$order->order_items_total .= $order_item->total . $export->category_separator;
						$order->order_items_subtotal .= $order_item->subtotal . $export->category_separator;
						$order->order_items_rrp .= $order_item->rrp . $export->category_separator;
						$order->order_items_stock .= $order_item->stock . $export->category_separator;
						$order->order_items_tax .= $order_item->tax . $export->category_separator;
						$order->order_items_tax_subtotal .= $order_item->tax_subtotal . $export->category_separator;
						$order->order_items_refund_subtotal .= $order_item->refund_subtotal . $export->category_separator;
						$order->order_items_refund_quantity .= $order_item->refund_quantity . $export->category_separator;
						$order->order_items_type .= $order_item->type . $export->category_separator;
						$order->order_items_type_id .= $order_item->type_id . $export->category_separator;
						$order->order_items_category .= $order_item->category . $export->category_separator;
						$order->order_items_tag .= $order_item->tag . $export->category_separator;
						$order->order_items_total_sales .= $order_item->total_sales . $export->category_separator;
						$order->order_items_weight .= $order_item->weight . $export->category_separator;
						$order->order_items_height .= $order_item->height . $export->category_separator;
						$order->order_items_width .= $order_item->width . $export->category_separator;
						$order->order_items_length .= $order_item->length . $export->category_separator;
						$order->order_items_total_weight .= $order_item->total_weight . $export->category_separator;
						// Add Order Item weight to Shipping Weight
						if( $order_item->total_weight != '' )
							$order->shipping_weight += $order_item->total_weight;
					}
					$order->order_items_id = substr( $order->order_items_id, 0, -1 );
					$order->order_items_product_id = substr( $order->order_items_product_id, 0, -1 );
					$order->order_items_variation_id = substr( $order->order_items_variation_id, 0, -1 );
					$order->order_items_sku = substr( $order->order_items_sku, 0, -1 );
					$order->order_items_name = substr( $order->order_items_name, 0, -1 );
					$order->order_items_variation = substr( $order->order_items_variation, 0, -1 );
					$order->order_items_image_embed = substr( $order->order_items_image_embed, 0, -1 );
					$order->order_items_description = substr( $order->order_items_description, 0, -1 );
					$order->order_items_excerpt = substr( $order->order_items_excerpt, 0, -1 );
					$order->order_items_publish_date = substr( $order->order_items_publish_date, 0, -1 );
					$order->order_items_modified_date = substr( $order->order_items_modified_date, 0, -1 );
					$order->order_items_tax_class = substr( $order->order_items_tax_class, 0, -1 );
					$order->order_items_quantity = substr( $order->order_items_quantity, 0, -1 );
					$order->order_items_total = substr( $order->order_items_total, 0, -1 );
					$order->order_items_subtotal = substr( $order->order_items_subtotal, 0, -1 );
					$order->order_items_rrp = substr( $order->order_items_rrp, 0, -1 );
					$order->order_items_stock = substr( $order->order_items_stock, 0, -1 );
					$order->order_items_tax = substr( $order_item->tax, 0, -1 );
					$order->order_items_tax_subtotal = substr( $order_item->tax_subtotal, 0, -1 );
					$order->order_items_refund_subtotal = substr( $order_item->refund_subtotal, 0, -1 );
					$order->order_items_refund_quantity = substr( $order_item->refund_quantity, 0, -1 );
					$order->order_items_type = substr( $order->order_items_type, 0, -1 );
					$order->order_items_type_id = substr( $order->order_items_type_id, 0, -1 );
					$order->order_items_category = substr( $order->order_items_category, 0, -1 );
					$order->order_items_tag = substr( $order->order_items_tag, 0, -1 );
					$order->order_items_total_sales = substr( $order->order_items_total_sales, 0, -1 );
					$order->order_items_weight = substr( $order->order_items_weight, 0, -1 );
					$order->order_items_height = substr( $order->order_items_height, 0, -1 );
					$order->order_items_width = substr( $order->order_items_width, 0, -1 );
					$order->order_items_length = substr( $order->order_items_length, 0, -1 );
					$order->order_items_total_weight = substr( $order->order_items_total_weight, 0, -1 );
				}
				$order = apply_filters( 'woo_ce_order_items_combined', $order );
			} else if( $args['order_items'] == 'unique' ) {
				if( !empty( $order->order_items ) ) {
					$i = 1;
					foreach( $order->order_items as $order_item ) {
						$order->{sprintf( 'order_item_%d_id', $i )} = $order_item->id;
						$order->{sprintf( 'order_item_%d_product_id', $i )} = $order_item->product_id;
						$order->{sprintf( 'order_item_%d_variation_id', $i )} = $order_item->variation_id;
						$order->{sprintf( 'order_item_%d_sku', $i )} = ( empty( $order_item->sku ) == false ? $order_item->sku : '' );
						$order->{sprintf( 'order_item_%d_name', $i )} = $order_item->name;
						$order->{sprintf( 'order_item_%d_variation', $i )} = $order_item->variation;
						$order->{sprintf( 'order_item_%d_image_embed', $i )} = $order_item->image_embed;
						$order->{sprintf( 'order_item_%d_description', $i )} = $order_item->description;
						$order->{sprintf( 'order_item_%d_excerpt', $i )} = $order_item->excerpt;
						$order->{sprintf( 'order_item_%d_publish_date', $i )} = $order_item->publish_date;
						$order->{sprintf( 'order_item_%d_modified_date', $i )} = $order_item->modified_date;
						$order->{sprintf( 'order_item_%d_tax_class', $i )} = $order_item->tax_class;
						$order->total_quantity += $order_item->quantity;
						if( empty( $order_item->quantity ) && '0' != $order_item->quantity )
							$order_item->quantity = '';
						$order->{sprintf( 'order_item_%d_quantity', $i )} = $order_item->quantity;
						$order->{sprintf( 'order_item_%d_total', $i )} = $order_item->total;
						$order->{sprintf( 'order_item_%d_subtotal', $i )} = $order_item->subtotal;
						$order->{sprintf( 'order_item_%d_rrp', $i )} = $order_item->rrp;
						$order->{sprintf( 'order_item_%d_stock', $i )} = $order_item->stock;
						$order->{sprintf( 'order_item_%d_tax', $i )} = $order_item->tax;
						$order->{sprintf( 'order_item_%d_tax_subtotal', $i )} = $order_item->tax_subtotal;
						$order->{sprintf( 'order_item_%d_refund_subtotal', $i )} = $order_item->refund_subtotal;
						$order->{sprintf( 'order_item_%d_refund_quantity', $i )} = $order_item->refund_quantity;
						$order->{sprintf( 'order_item_%d_type', $i )} = $order_item->type;
						$order->{sprintf( 'order_item_%d_type_id', $i )} = $order_item->type_id;
						$order->{sprintf( 'order_item_%d_category', $i )} = $order_item->category;
						$order->{sprintf( 'order_item_%d_tag', $i )} = $order_item->tag;
						$order->{sprintf( 'order_item_%d_total_sales', $i )} = $order_item->total_sales;
						$order->{sprintf( 'order_item_%d_weight', $i )} = $order_item->weight;
						$order->{sprintf( 'order_item_%d_height', $i )} = $order_item->height;
						$order->{sprintf( 'order_item_%d_width', $i )} = $order_item->width;
						$order->{sprintf( 'order_item_%d_length', $i )} = $order_item->length;
						$order->{sprintf( 'order_item_%d_total_weight', $i )} = $order_item->total_weight;
						// Add Order Item weight to Shipping Weight
						if( $order_item->total_weight != '' )
							$order->shipping_weight += $order_item->total_weight;
						$order = apply_filters( 'woo_ce_order_items_unique', $order, $i, $order_item );
						$i++;
					}
				}
			}
		}

		// Custom Order fields
		$custom_orders = woo_ce_get_option( 'custom_orders', '' );
		if( !empty( $custom_orders ) ) {
			foreach( $custom_orders as $custom_order ) {
				if( !empty( $custom_order ) ) {
					$order->{$custom_order} = woo_ce_format_custom_meta( get_post_meta( $order_id, $custom_order, true ) );
				}
			}
		}

		// Check if the Order has a User assigned to it
		if( !empty( $order->user_id ) ) {
			// Custom User fields
			$custom_users = woo_ce_get_option( 'custom_users', '' );
			if( !empty( $custom_users ) ) {
				foreach( $custom_users as $custom_user ) {
					if( !empty( $custom_user ) && !isset( $order->{$custom_user} ) ) {
						$order->{$custom_user} = woo_ce_format_custom_meta( get_user_meta( $order->user_id, $custom_user, true ) );
					}
				}
			}
			unset( $custom_users, $custom_user );
		}

	} else if( $export_type = 'customer' ) {

		// Check if the Order has a User assigned to it
		if( !empty( $order->user_id ) ) {

			// Load up the User data as other Plugins will use it too
			$user = woo_ce_get_user_data( $order->user_id );

			// WooCommerce Follow-Up Emails - http://www.woothemes.com/products/follow-up-emails/
			if( woo_ce_detect_export_plugin( 'wc_followupemails' ) ) {

				global $wpdb;

				if( isset( $user->email ) ) {
					$followup_optout_sql = $wpdb->prepare( "SELECT `id` FROM `" . $wpdb->prefix . "followup_email_excludes` WHERE `email` = %s LIMIT 1", $user->email );
					$order->followup_optout = $wpdb->get_var( $followup_optout_sql );
				}

			}

			// Custom User fields
			$custom_users = woo_ce_get_option( 'custom_users', '' );
			if( !empty( $custom_users ) ) {
				foreach( $custom_users as $custom_user ) {
					if( !empty( $custom_user ) && !isset( $order->{$custom_user} ) ) {
						$order->{$custom_user} = woo_ce_format_custom_meta( get_user_meta( $order->user_id, $custom_user, true ) );
					}
				}
			}
			unset( $custom_users, $custom_user );

			// Clean up
			unset( $user );

		}

		// Custom Customer fields
		$custom_customers = woo_ce_get_option( 'custom_customers', '' );
		if( !empty( $custom_customers ) ) {
			foreach( $custom_customers as $custom_customer ) {
				if( !empty( $custom_customer ) )
					$order->{$custom_customer} = esc_attr( get_user_meta( $order->user_id, $custom_customer, true ) );
			}
		}

	}

	// Allow Plugin/Theme authors to add support for additional Order columns
	$order = apply_filters( 'woo_ce_order', $order, $order_id );

	// Trim back the Order just to requested export fields
	if( !empty( $fields ) ) {
		$fields[] = 'id';
		if( $args['order_items'] == 'individual' )
			$fields[] = 'order_items';
		if( !empty( $order ) ) {
			foreach( $order as $key => $data ) {
				if( !in_array( $key, $fields ) )
					unset( $order->$key );
			}
		}
	}

	return $order;

}

function woo_ce_export_dataset_override_order( $output = null, $export_type = null ) {

	global $export;

	if( $orders = woo_ce_get_orders( 'order', $export->args ) ) {
		if( WOO_CD_DEBUG )
			error_log( 'woo_ce_get_orders(): ' . ( time() - $export->start_time ) );
		$export->total_columns = $size = count( $export->columns );
		// XML, RSS export
		if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
			if( !empty( $export->fields ) ) {
				foreach( $orders as $order ) {
					if( $export->export_format == 'xml' )
						$child = $output->addChild( apply_filters( 'woo_ce_export_xml_order_node', sanitize_key( $export_type ) ) );
					else if( $export->export_format == 'rss' )
						$child = $output->addChild( 'item' );
					$child->addAttribute( 'id', $order );
					$order = woo_ce_get_order_data( $order, 'order', $export->args, array_keys( $export->fields ) );
					if( in_array( $export->args['order_items'], array( 'combined', 'unique' ) ) ) {
						// Order items formatting: SPECK-IPHONE|INCASE-NANO|-
						foreach( array_keys( $export->fields ) as $key => $field ) {
							if( isset( $order->$field ) && isset( $export->columns[$key] ) ) {
								if( !is_array( $field ) ) {
									if( woo_ce_is_xml_cdata( $order->$field ) )
										$child->addChild( sanitize_key( $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $order->$field ) ) );
									else
										$child->addChild( sanitize_key( $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $order->$field ) ) );
								}
							}
						}
					} else if( $export->args['order_items'] == 'individual' ) {
						// Order items formatting: SPECK-IPHONE<br />INCASE-NANO<br />-
						if( !empty( $order->order_items ) ) {
							$order->order_items_id = '';
							$order->order_items_product_id = '';
							$order->order_items_variation_id = '';
							$order->order_items_sku = '';
							$order->order_items_name = '';
							$order->order_items_variation = '';
							$order->order_items_image_embed = '';
							$order->order_items_description = '';
							$order->order_items_excerpt = '';
							$order->order_items_publish_date = '';
							$order->order_items_modified_date = '';
							$order->order_items_tax_class = '';
							$order->order_items_quantity = '';
							$order->order_items_total = '';
							$order->order_items_subtotal = '';
							$order->order_items_rrp = '';
							$order->order_items_stock = '';
							$order->order_items_tax = '';
							$order->order_items_tax_subtotal = '';
							$order->order_items_refund_subtotal = '';
							$order->order_items_refund_quantity = '';
							$order->order_items_type = '';
							$order->order_items_type_id = '';
							$order->order_items_category = '';
							$order->order_items_tag = '';
							$order->order_items_total_sales = '';
							$order->order_items_weight = '';
							$order->order_items_height = '';
							$order->order_items_width = '';
							$order->order_items_length = '';
							$order->order_items_total_weight = '';
							foreach( $order->order_items as $order_item ) {
								// Add Order Item weight to Shipping Weight
								if( $order_item->total_weight != '' )
									$order->shipping_weight += $order_item->total_weight;
								$order->order_items_id = $order_item->id;
								$order->order_items_product_id = $order_item->product_id;
								$order->order_items_variation_id = $order_item->variation_id;
								if( empty( $order_item->sku ) )
									$order_item->sku = '';
								$order->order_items_sku = $order_item->sku;
								$order->order_items_name = $order_item->name;
								$order->order_items_variation = $order_item->variation;
								$order->order_items_image_embed = $order_item->image_embed;
								$order->order_items_description = woo_ce_format_description_excerpt( $order_item->description );
								$order->order_items_excerpt = woo_ce_format_description_excerpt( $order_item->excerpt );
								$order->order_items_publish_date = $order_item->publish_date;
								$order->order_items_modified_date = $order_item->modified_date;
								$order->order_items_tax_class = $order_item->tax_class;
								$order->total_quantity += $order_item->quantity;
								$order->order_items_quantity = $order_item->quantity;
								$order->order_items_total = $order_item->total;
								$order->order_items_subtotal = $order_item->subtotal;
								$order->order_items_rrp = $order_item->rrp;
								$order->order_items_stock = $order_item->stock;
								$order->order_items_tax = $order_item->tax;
								$order->order_items_tax_subtotal = $order_item->tax_subtotal;
								$order->order_items_refund_subtotal = $order_item->refund_subtotal;
								$order->order_items_refund_quantity = $order_item->refund_quantity;
								$order->order_items_type = $order_item->type;
								$order->order_items_type_id = $order_item->type_id;
								$order->order_items_category = $order_item->category;
								$order->order_items_tag = $order_item->tag;
								$order->order_items_total_sales = $order_item->total_sales;
								$order->order_items_weight = $order_item->weight;
								$order->order_items_height = $order_item->height;
								$order->order_items_width = $order_item->width;
								$order->order_items_length = $order_item->length;
								$order->order_items_total_weight = $order_item->total_weight;
								$order = apply_filters( 'woo_ce_order_items_individual', $order, $order_item );
								foreach( array_keys( $export->fields ) as $key => $field ) {
									if( isset( $order->$field ) && isset( $export->columns[$key] ) ) {
										if( !is_array( $field ) ) {
											if( woo_ce_is_xml_cdata( $order->$field ) )
												$child->addChild( sanitize_key( $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $order->$field ) ) );
											else
												$child->addChild( sanitize_key( $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $order->$field ) ) );
										}
									}
								}
							}
							unset( $order->order_items );
						}
					}
				}
			}
		} else {
			// PHPExcel export
			if( $export->args['order_items'] == 'individual' )
				$output = array();
			if( WOO_CD_DEBUG )
				error_log( 'foreach $orders...: ' . ( time() - $export->start_time ) );
			foreach( $orders as $order ) {
				if( in_array( $export->args['order_items'], array( 'combined', 'unique' ) ) ) {
					// Order items formatting: SPECK-IPHONE|INCASE-NANO|-
					$output[] = woo_ce_get_order_data( $order, 'order', $export->args, array_keys( $export->fields ) );
					if( WOO_CD_DEBUG )
						error_log( 'woo_ce_get_order_data(): ' . ( time() - $export->start_time ) );
				} else if( $export->args['order_items'] == 'individual' ) {
					// Order items formatting: SPECK-IPHONE<br />INCASE-NANO<br />-
					$order = woo_ce_get_order_data( $order, 'order', $export->args, array_keys( $export->fields ) );
					if( WOO_CD_DEBUG )
						error_log( 'woo_ce_get_order_data(): ' . ( time() - $export->start_time ) );
					if( !empty( $order->order_items ) ) {
						foreach( $order->order_items as $order_item ) {
							// Add Order Item weight to Shipping Weight
							if( $order_item->total_weight != '' )
								$order->shipping_weight += $order_item->total_weight;
							$order->order_items_id = $order_item->id;
							$order->order_items_product_id = $order_item->product_id;
							$order->order_items_variation_id = $order_item->variation_id;
							if( empty( $order_item->sku ) )
								$order_item->sku = '';
							$order->order_items_sku = $order_item->sku;
							$order->order_items_name = $order_item->name;
							$order->order_items_image_embed = $order_item->image_embed;
							$order->order_items_variation = $order_item->variation;
							$order->order_items_description = $order_item->description;
							$order->order_items_excerpt = $order_item->excerpt;
							$order->order_items_publish_date = $order_item->publish_date;
							$order->order_items_modified_date = $order_item->modified_date;
							$order->order_items_tax_class = $order_item->tax_class;
							$order->total_quantity += $order_item->quantity;
							$order->order_items_quantity = $order_item->quantity;
							$order->order_items_total = $order_item->total;
							$order->order_items_subtotal = $order_item->subtotal;
							$order->order_items_rrp = $order_item->rrp;
							$order->order_items_stock = $order_item->stock;
							$order->order_items_tax = $order_item->tax;
							$order->order_items_tax_subtotal = $order_item->tax_subtotal;
							$order->order_items_refund_subtotal = $order_item->refund_subtotal;
							$order->order_items_refund_quantity = $order_item->refund_quantity;
							$order->order_items_type = $order_item->type;
							$order->order_items_type_id = $order_item->type_id;
							$order->order_items_category = $order_item->category;
							$order->order_items_tag = $order_item->tag;
							$order->order_items_total_sales = $order_item->total_sales;
							$order->order_items_weight = $order_item->weight;
							$order->order_items_width = $order_item->width;
							$order->order_items_length = $order_item->length;
							$order->order_items_height = $order_item->height;
							$order->order_items_total_weight = $order_item->total_weight;
							$order = apply_filters( 'woo_ce_order_items_individual', $order, $order_item );
							// This fixes the Order Items for this Order Items Formatting rule
							$output[] = (object)(array)$order;
							$output = apply_filters( 'woo_ce_order_items_individual_output', $output, $order, $order_item );
						}
					} else {
						$output[] = $order;
					}
				}
			}
		}
		unset( $orders, $order );
	}

	return $output;

}

function woo_ce_export_dataset_multisite_override_order( $output = null, $export_type = null ) {

	global $export;

	$sites = wp_get_sites();
	if( !empty( $sites ) ) {
		foreach( $sites as $site ) {
			switch_to_blog( $site['blog_id'] );
			if( $orders = woo_ce_get_orders( 'order', $export->args ) ) {
				$export->total_columns = $size = count( $export->columns );
				// XML, RSS export
				if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
					if( !empty( $export->fields ) ) {
						foreach( $orders as $order ) {
							if( $export->export_format == 'xml' )
								$child = $output->addChild( apply_filters( 'woo_ce_export_xml_order_node', sanitize_key( $export_type ) ) );
							else if( $export->export_format == 'rss' )
								$child = $output->addChild( 'item' );
							$child->addAttribute( 'id', $order );
							$order = woo_ce_get_order_data( $order, 'order', $export->args, array_keys( $export->fields ) );
							if( in_array( $export->args['order_items'], array( 'combined', 'unique' ) ) ) {
								// Order items formatting: SPECK-IPHONE|INCASE-NANO|-
								foreach( array_keys( $export->fields ) as $key => $field ) {
									if( isset( $order->$field ) && isset( $export->columns[$key] ) ) {
										if( !is_array( $field ) ) {
											if( woo_ce_is_xml_cdata( $order->$field ) )
												$child->addChild( sanitize_key( $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $order->$field ) ) );
											else
												$child->addChild( sanitize_key( $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $order->$field ) ) );
										}
									}
								}
							} else if( $export->args['order_items'] == 'individual' ) {
								// Order items formatting: SPECK-IPHONE<br />INCASE-NANO<br />-
								if( !empty( $order->order_items ) ) {
									$order->order_items_id = '';
									$order->order_items_product_id = '';
									$order->order_items_variation_id = '';
									$order->order_items_sku = '';
									$order->order_items_name = '';
									$order->order_items_variation = '';
									$order->order_items_image_embed = '';
									$order->order_items_description = '';
									$order->order_items_excerpt = '';
									$order->order_items_publish_date = '';
									$order->order_items_modified_date = '';
									$order->order_items_tax_class = '';
									$order->order_items_quantity = '';
									$order->order_items_total = '';
									$order->order_items_subtotal = '';
									$order->order_items_rrp = '';
									$order->order_items_stock = '';
									$order->order_items_tax = '';
									$order->order_items_tax_subtotal = '';
									$order->order_items_refund_subtotal = '';
									$order->order_items_refund_quantity = '';
									$order->order_items_type = '';
									$order->order_items_type_id = '';
									$order->order_items_category = '';
									$order->order_items_tag = '';
									$order->order_items_total_sales = '';
									$order->order_items_weight = '';
									$order->order_items_height = '';
									$order->order_items_width = '';
									$order->order_items_length = '';
									$order->order_items_total_weight = '';
									foreach( $order->order_items as $order_item ) {
										// Add Order Item weight to Shipping Weight
										if( $order_item->total_weight != '' )
											$order->shipping_weight += $order_item->total_weight;
										$order->order_items_id = $order_item->id;
										$order->order_items_product_id = $order_item->product_id;
										$order->order_items_variation_id = $order_item->variation_id;
										if( empty( $order_item->sku ) )
											$order_item->sku = '';
										$order->order_items_sku = $order_item->sku;
										$order->order_items_name = $order_item->name;
										$order->order_items_variation = $order_item->variation;
										$order->order_items_image_embed = $order_item->image_embed;
										$order->order_items_description = woo_ce_format_description_excerpt( $order_item->description );
										$order->order_items_excerpt = woo_ce_format_description_excerpt( $order_item->excerpt );
										$order->order_items_publish_date = $order_item->publish_date;
										$order->order_items_modified_date = $order_item->modified_date;
										$order->order_items_tax_class = $order_item->tax_class;
										$order->total_quantity += $order_item->quantity;
										$order->order_items_quantity = $order_item->quantity;
										$order->order_items_total = $order_item->total;
										$order->order_items_subtotal = $order_item->subtotal;
										$order->order_items_rrp = $order_item->rrp;
										$order->order_items_stock = $order_item->stock;
										$order->order_items_tax = $order_item->tax;
										$order->order_items_tax_subtotal = $order_item->tax_subtotal;
										$order->order_items_refund_subtotal = $order_item->refund_subtotal;
										$order->order_items_refund_quantity = $order_item->refund_quantity;
										$order->order_items_type = $order_item->type;
										$order->order_items_type_id = $order_item->type_id;
										$order->order_items_category = $order_item->category;
										$order->order_items_tag = $order_item->tag;
										$order->order_items_total_sales = $order_item->total_sales;
										$order->order_items_weight = $order_item->weight;
										$order->order_items_height = $order_item->height;
										$order->order_items_width = $order_item->width;
										$order->order_items_length = $order_item->length;
										$order->order_items_total_weight = $order_item->total_weight;
										$order = apply_filters( 'woo_ce_order_items_individual', $order, $order_item );
										foreach( array_keys( $export->fields ) as $key => $field ) {
											if( isset( $order->$field ) && isset( $export->columns[$key] ) ) {
												if( !is_array( $field ) ) {
													if( woo_ce_is_xml_cdata( $order->$field ) )
														$child->addChild( sanitize_key( $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $order->$field ) ) );
													else
														$child->addChild( sanitize_key( $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $order->$field ) ) );
												}
											}
										}
									}
									unset( $order->order_items );
								}
							}
						}
					}
				} else {
					// PHPExcel export
					if( $export->args['order_items'] == 'individual' && isset( $output ) == false )
						$output = array();
					foreach( $orders as $order ) {
						if( in_array( $export->args['order_items'], array( 'combined', 'unique' ) ) ) {
							// Order items formatting: SPECK-IPHONE|INCASE-NANO|-
							$output[] = woo_ce_get_order_data( $order, 'order', $export->args, array_keys( $export->fields ) );
						} else if( $export->args['order_items'] == 'individual' ) {
							// Order items formatting: SPECK-IPHONE<br />INCASE-NANO<br />-
							$order = woo_ce_get_order_data( $order, 'order', $export->args, array_keys( $export->fields ) );
							if( !empty( $order->order_items ) ) {
								foreach( $order->order_items as $order_item ) {
									// Add Order Item weight to Shipping Weight
									if( $order_item->total_weight != '' )
										$order->shipping_weight += $order_item->total_weight;
									$order->order_items_id = $order_item->id;
									$order->order_items_product_id = $order_item->product_id;
									$order->order_items_variation_id = $order_item->variation_id;
									if( empty( $order_item->sku ) )
										$order_item->sku = '';
									$order->order_items_sku = $order_item->sku;
									$order->order_items_name = $order_item->name;
									$order->order_items_image_embed = $order_item->image_embed;
									$order->order_items_variation = $order_item->variation;
									$order->order_items_description = $order_item->description;
									$order->order_items_excerpt = $order_item->excerpt;
									$order->order_items_publish_date = $order_item->publish_date;
									$order->order_items_modified_date = $order_item->modified_date;
									$order->order_items_tax_class = $order_item->tax_class;
									$order->total_quantity += $order_item->quantity;
									$order->order_items_quantity = $order_item->quantity;
									$order->order_items_total = $order_item->total;
									$order->order_items_subtotal = $order_item->subtotal;
									$order->order_items_rrp = $order_item->rrp;
									$order->order_items_stock = $order_item->stock;
									$order->order_items_tax = $order_item->tax;
									$order->order_items_tax_subtotal = $order_item->tax_subtotal;
									$order->order_items_refund_subtotal = $order_item->refund_subtotal;
									$order->order_items_refund_quantity = $order_item->refund_quantity;
									$order->order_items_type = $order_item->type;
									$order->order_items_type_id = $order_item->type_id;
									$order->order_items_category = $order_item->category;
									$order->order_items_tag = $order_item->tag;
									$order->order_items_total_sales = $order_item->total_sales;
									$order->order_items_weight = $order_item->weight;
									$order->order_items_width = $order_item->width;
									$order->order_items_length = $order_item->length;
									$order->order_items_height = $order_item->height;
									$order->order_items_total_weight = $order_item->total_weight;
									$order = apply_filters( 'woo_ce_order_items_individual', $order, $order_item );
									// This fixes the Order Items for this Order Items Formatting rule
									$output[] = (object)(array)$order;
									$output = apply_filters( 'woo_ce_order_items_individual_output', $output, $order, $order_item );
								}
							} else {
								$output[] = (object)(array)$order;
							}
						}
					}
				}
				unset( $orders, $order );
			}
			restore_current_blog();
		}
	}
	return $output;

}

// Returns a list of WooCommerce Tax Rates based on existing Orders
function woo_ce_get_order_tax_rates( $order_id = 0 ) {

	global $wpdb;

	$order_item_type = 'tax';
	$tax_rates_sql = $wpdb->prepare( "SELECT order_items.order_item_id as item_id FROM " . $wpdb->prefix . "woocommerce_order_items as order_items WHERE order_items.order_item_type = %s", $order_item_type );
	if( !empty( $order_id ) ) {
		$tax_rates_sql .= $wpdb->prepare( " AND order_items.order_id = %d", $order_id );
	}
	$tax_rates_sql .= " GROUP BY order_items.order_item_name";
	$tax_rates = $wpdb->get_results( $tax_rates_sql, 'ARRAY_A' );
	if( !empty( $tax_rates ) ) {
		$meta_type = 'order_item';
		foreach( $tax_rates as $key => $tax_rate ) {
			$tax_rates[$key]['rate_id'] = get_metadata( $meta_type, $tax_rate['item_id'], 'rate_id', true );
			$tax_rates[$key]['label'] = get_metadata( $meta_type, $tax_rate['item_id'], 'label', true );
			if( !empty( $tax_rates[$key]['rate_id'] ) ) {
				$meta_sql = $wpdb->prepare( "SELECT `tax_rate_class` FROM `" . $wpdb->prefix . "woocommerce_tax_rates` WHERE `tax_rate_id` = %d LIMIT 1", $tax_rates[$key]['rate_id'] );
				$meta = $wpdb->get_var( $meta_sql );
				if( empty( $meta ) )
					$meta = 'Standard';
				$tax_rates[$key]['class'] = $meta;
			}
		}
		return $tax_rates;
	}

}

// Get the Order Item ID and tax rate ID of tax Order Items
function woo_ce_get_order_assoc_tax_rates( $order_id = 0 ) {

	global $wpdb;

	if( !empty( $order_id ) ) {
		$order_item_type = 'tax';
		$meta_key = 'rate_id';
		$order_items_sql = $wpdb->prepare( "SELECT order_items.order_item_id, order_itemmeta.meta_value as rate_id FROM " . $wpdb->prefix . "woocommerce_order_items as order_items, " . $wpdb->prefix . "woocommerce_order_itemmeta as order_itemmeta WHERE order_items.order_item_id = order_itemmeta.order_item_id AND order_items.order_item_type = %s AND order_items.order_id = %d AND order_itemmeta.meta_key = %s", $order_item_type, $order_id, $meta_key );
		$order_items = $wpdb->get_results( $order_items_sql, 'ARRAY_A' );
		if( !empty( $order_items ) )
			return $order_items;
	}

}

// Get the Tax Rate assigned to a given tax rate ID
function woo_ce_get_order_tax_percentage( $tax_rate_id = 0 ) {

	global $wpdb;

	if( !empty( $tax_rate_id ) ) {
		$tax_rate_sql = $wpdb->prepare( "SELECT tax_rates.tax_rate FROM " . $wpdb->prefix . "woocommerce_tax_rates as tax_rates WHERE tax_rates.tax_rate_id = %d LIMIT 1", $tax_rate_id );
		$tax_rate = $wpdb->get_var( $tax_rate_sql );
		if( !empty( $tax_rate ) )
			return $tax_rate;
	}

}

// Return the total tax applied to a specific Tax Rate for a given Order
function woo_ce_get_order_assoc_tax_rate_total( $order_id = 0, $tax_rate = 0 ) {

	global $wpdb;

	$order_item_type = 'tax';
	$meta_key = 'rate_id';
	$order_item_id_sql = $wpdb->prepare( "SELECT order_items.order_item_id FROM " . $wpdb->prefix . "woocommerce_order_items as order_items, " . $wpdb->prefix . "woocommerce_order_itemmeta as order_itemmeta WHERE order_items.order_item_id = order_itemmeta.order_item_id AND order_items.order_item_type = %s AND order_items.order_id = %d AND order_itemmeta.meta_key = %s AND order_itemmeta.meta_value = %d", $order_item_type, $order_id, $meta_key, $tax_rate );
	$order_item_id = $wpdb->get_var( $order_item_id_sql );
	if( !empty( $order_item_id ) ) {
		$amounts_sql = $wpdb->prepare( "SELECT SUM( meta_value ) FROM " . $wpdb->prefix . "woocommerce_order_itemmeta WHERE order_item_id = %d AND meta_key IN ( 'tax_amount', 'shipping_tax_amount' )", $order_item_id );
		$amounts = $wpdb->get_var( $amounts_sql );
		if( !empty( $amounts ) ) {
			return $amounts;
		}
	}

}

// Get the Order Item ID of refunded Order Items
function woo_ce_get_order_line_item_assoc_refunds( $line_item_id = 0 ) {

	global $wpdb;

	$order_item_type = 'line_item';
	$meta_key = '_refunded_item_id';
	$refund_items_sql = $wpdb->prepare( "SELECT order_itemmeta.`order_item_id` FROM `" . $wpdb->prefix . "woocommerce_order_items` as order_items, `" . $wpdb->prefix . "woocommerce_order_itemmeta` as order_itemmeta WHERE order_items.`order_item_id` = order_itemmeta.`order_item_id` AND order_items.`order_item_type` = %s AND order_itemmeta.`meta_key` = %s AND order_itemmeta.`meta_value` = %d", $order_item_type, $meta_key, $line_item_id );
	$refund_items = $wpdb->get_col( $refund_items_sql );
	return $refund_items;

}

// Return the PHP date format for the requested Order Date filter
function woo_ce_get_order_date_filter( $filter = '', $format = '', $date_format = 'd-m-Y' ) {

	$output = false;
	if( !empty( $filter ) && !empty( $format ) ) {
		switch( $filter ) {

			case 'tomorrow':
				if( $format == 'from' )
					$output = date( $date_format, strtotime( 'tomorrow' ) );
				else
					$output = date( $date_format, strtotime( 'tomorrow' ) );
				break;

			case 'today':
				if( $format == 'from' )
					$output = date( $date_format, strtotime( 'today' ) );
				else
					$output = date( $date_format, strtotime( 'tomorrow' ) );
				break;

			case 'yesterday':
				if( $format == 'from' )
					$output = date( $date_format, strtotime( 'yesterday' ) );
				else
					$output = date( $date_format, strtotime( 'today' ) );
				break;

			case 'current_week':
				if( $format == 'from' )
					$output = date( $date_format, strtotime( 'last Monday' ) );
				else
					$output = date( $date_format, strtotime( 'next Monday' ) );
				break;

			case 'last_week':
				if( $format == 'from' )
					$output = date( $date_format, strtotime( '-2 weeks Monday' ) );
				else
					$output = date( $date_format, strtotime( '-1 weeks Monday' ) );
				break;

			case 'current_month':
				if( $format == 'from' )
					$output = date( $date_format, mktime( 0, 0, 0, date( 'n' ), 1 ) );
				else
					$output = date( $date_format, mktime( 0, 0, 0, ( date( 'n' ) + 1 ), 0 ) );
				break;

			case 'last_month':
				if( $format == 'from' )
					$output = date( $date_format, mktime( 0, 0, 0, date( 'n', strtotime( '-1 month' ) ), 1, date( 'Y', strtotime( '-1 month' ) ) ) );
				else
					$output = date( $date_format, mktime( 0, 0, 0, date( 'n' ), 1 ) );
				break;

/*
			case '':
				if( $format == 'from' )
					$output = ;
				else
					$output = ;
					break;
*/

			default:
				woo_ce_error_log( sprintf( 'Warning: %s', sprintf( __( 'Unknown Order Date filter %s provided, defaulted to none', 'woocommerce-exporter' ), $filter ) ) );
				break;

		}
	}
	return $output;

}

// Returns date of first Order received, any status
function woo_ce_get_order_first_date( $date_format = 'd/m/Y' ) {

	$output = date( $date_format, mktime( 0, 0, 0, date( 'n' ), 1 ) );

	$post_type = 'shop_order';
	$args = array(
		'post_type' => $post_type,
		'orderby' => 'post_date',
		'order' => 'ASC',
		'numberposts' => 1,
		'post_status' => 'any'
	);
	$orders = get_posts( $args );
	if( !empty( $orders ) ) {
		$output = date( $date_format, strtotime( $orders[0]->post_date ) );
		unset( $orders );
	}
	return $output;

}

// Returns a list of WooCommerce Order statuses
function woo_ce_get_order_statuses() {

	$terms = false;
	// Check if this is a WooCommerce 2.2+ instance (new Post Status)
	$woocommerce_version = woo_get_woo_version();
	if( version_compare( $woocommerce_version, '2.2' ) >= 0 ) {
		// Convert Order Status array into our magic sauce
		$order_statuses = ( function_exists( 'wc_get_order_statuses' ) ? wc_get_order_statuses() : false );
		if( !empty( $order_statuses ) ) {
			$terms = array();
			$post_type = 'shop_order';
			$posts_count = wp_count_posts( $post_type );
			foreach( $order_statuses as $key => $order_status ) {
				$terms[] = (object)array(
					'name' => $order_status,
					'slug' => $key,
					'count' => ( isset( $posts_count->$key ) ? $posts_count->$key : 0 )
				);
			}
		}
	} else {
		$args = array(
			'hide_empty' => false
		);
		$terms = get_terms( 'shop_order_status', $args );
		if( empty( $terms ) || ( is_wp_error( $terms ) == true ) )
			$terms = false;
	}
	return $terms;

}

// Returns the Shipping Method ID associated to a specific Order
function woo_ce_get_order_assoc_shipping_method_id( $order_id = 0 ) {

	global $export;

	if( class_exists( 'WC_Order' ) && !empty( $order_id ) ) {
		$output = '';
		$order = new WC_Order( $order_id );
		if( method_exists( 'WC_Order', 'get_shipping_methods' ) ) {
			if( $shipping_methods = $order->get_shipping_methods() ) {
				foreach( $shipping_methods as $shipping_item_id => $shipping_item ) {
					if( isset( $shipping_item['item_meta'] ) ) {
						$output = $shipping_item['item_meta']['method_id'];
						if( is_array( $output ) )
							$output = $output[0];
						break;
					}
				}
			}
			unset( $shipping_methods );
		}
		unset( $order );
		return apply_filters( 'woo_ce_get_order_assoc_shipping_method_id', $output );
	}

}

// Returns Download keys associated to a specified Order
function woo_ce_get_order_assoc_downloads( $order_id = 0 ) {

	global $wpdb;

	if( !empty( $order_id ) ) {
		$order_downloads_sql = $wpdb->prepare( "SELECT `download_id`, `download_count` FROM `" . $wpdb->prefix . "woocommerce_downloadable_product_permissions` WHERE `order_id` = %d", $order_id );
		$order_downloads = $wpdb->get_results( $order_downloads_sql );
		$output = array();
		if( !empty( $order_downloads ) ) {
			$output = $order_downloads;
		}
		unset( $order_downloads );
		return $output;
	}

}

// Returns Order Notes associated to a specific Order
function woo_ce_get_order_assoc_notes( $order_id = 0, $note_type = 'order_note' ) {

	global $wpdb;

	if( !empty( $order_id ) ) {
		$term_taxonomy = 'order_note';
		// @mod - The default get_comments() call is not working for returning Order Notes or Customer Notes, using database query
		$order_notes_sql = $wpdb->prepare( "SELECT `comment_ID`, `comment_date`, `comment_content` FROM `" . $wpdb->comments . "` WHERE `comment_type` = %s AND `comment_post_ID` = %d AND `comment_agent` = 'WooCommerce' AND `comment_approved` = 1", $term_taxonomy, $order_id );
		$order_notes = $wpdb->get_results( $order_notes_sql );
		$wpdb->flush();
		$output = array();
		if( !empty( $order_notes ) ) {
			foreach( $order_notes as $order_note ) {
				// Check if we are returning an order or customer note
				$order_note->comment_date = sprintf( apply_filters( 'woo_ce_get_order_assoc_notes_date', '%s %s' ), woo_ce_format_date( $order_note->comment_date ), mysql2date( 'H:i:s', $order_note->comment_date ) );
				if( $note_type == 'customer_note' ) {
					// Check if the order note is a customer one
					if( absint( get_comment_meta( $order_note->comment_ID, 'is_customer_note', true ) ) == 1 )
						$output[] = sprintf( apply_filters( 'woo_ce_get_order_assoc_notes_customer', '%s: %s' ), $order_note->comment_date, $order_note->comment_content );
				} else {
					// Check if the order note is a customer one
					if( absint( get_comment_meta( $order_note->comment_ID, 'is_customer_note', true ) ) == 0 )
						$output[] = sprintf( apply_filters( 'woo_ce_get_order_assoc_notes_order', '%s: %s' ), $order_note->comment_date, $order_note->comment_content );
				}
			}
		}
		return $output;
	}

}

function woo_ce_get_order_assoc_refund_date( $order_id = 0 ) {

	if( !empty( $order_id ) ) {

		$output = '';
		$post_type = 'shop_order_refund';
		$args = array(
			'post_type' => $post_type,
			'post_status' => 'wc-completed',
			'post_parent' => $order_id,
			'posts_per_page' => -1
		);
		$args = apply_filters( 'woo_ce_get_order_assoc_refund_date', $args, $order_id );
		$refunds = new WP_Query( $args );
		if( !empty( $refunds->posts ) ) {
			foreach( $refunds->posts as $refund ) {
				if( apply_filters( 'woo_ce_override_get_order_assoc_refund_date_filter', false ) ) {
					// This will return the latest partial refund regardless of whether it is fully refunded or not
					$output = woo_ce_format_date( $refund->post_date );
				} else {
					// This will limit the refund date to only Orders fully refunded
					if( $refund->post_excerpt == __( 'Order Fully Refunded', 'woocommerce' ) ) {
						$output = woo_ce_format_date( $refund->post_date );
						break;
					}
				}
			}
		}
		return $output;
	}

}

// Returns the Coupon Code associated to a specific Order
function woo_ce_get_order_assoc_coupon( $order_id = 0 ) {

	global $export;

	if( !empty( $order_id ) ) {
		$output = '';
		$order_item_type = 'coupon';
		if( class_exists( 'WC_Order' ) ) {
			$order = new WC_Order( $order_id );
			if( method_exists( $order, 'get_used_coupons' ) ) {
				if( $coupons = $order->get_used_coupons() ) {
					$size = count( $coupons );
					// If more than a single Coupon is assigned to this order then separate them
					if( $size > 1 )
						$output = implode( $export->category_separator, $coupons );
					else
						$output = $coupons[0];
				}
			}
		}
		return $output;
	}

}

function woo_ce_max_order_items( $orders = array() ) {

	$output = 0;
	if( $orders ) {
		foreach( $orders as $order ) {
			if( $order->order_items )
				$output = count( $order->order_items[0]->name );
		}
	}
	return $output;

}

// Returns a list of Order Item ID's with the order_item_type of 'line item' for a specified Order
function woo_ce_get_order_item_ids( $order_id = 0 ) {

	global $wpdb;

	if( !empty( $order_id ) ) {
		$order_item_type = 'line_item';
		$order_items_sql = $wpdb->prepare( "SELECT order_items.`order_item_id` as id, order_itemmeta.`meta_value` as product_id FROM `" . $wpdb->prefix . "woocommerce_order_items` as order_items, `" . $wpdb->prefix . "woocommerce_order_itemmeta` as order_itemmeta WHERE order_items.`order_item_id` = order_itemmeta.`order_item_id` AND order_items.`order_id` = %d AND order_items.`order_item_type` = %s AND order_itemmeta.`meta_key` IN ('_product_id')", $order_id, $order_item_type );
		if( $order_items = $wpdb->get_results( $order_items_sql ) )
			return $order_items;
	}

}

// Returns a list of Order Items for a specified Order
function woo_ce_get_order_items( $order_id = 0, $order_items_types = array() ) {

	global $export, $wpdb;

	$upload_dir = wp_upload_dir();

	if( !empty( $order_id ) ) {
		$order_items_sql = $wpdb->prepare( "SELECT `order_item_id` as id, `order_item_name` as name, `order_item_type` as type FROM `" . $wpdb->prefix . "woocommerce_order_items` WHERE `order_id` = %d", $order_id );

		// Allow Plugin/Theme authors to extend the Order Items query
		$order_items_sql = apply_filters( 'woo_ce_get_order_items_sql', $order_items_sql, $order_id );

		if( $order_items = $wpdb->get_results( $order_items_sql ) ) {
			$wpdb->flush();

			// Default to Line Item for empty Order Item types
			if( empty( $order_items_types ) )
				$order_items_types = array( 'line_item' );

			// Allow Plugin/Theme authors to add support for sorting Order Items
			$order_items = apply_filters( 'woo_ce_get_order_items_pre', $order_items, $order_id );

			$attributes = array();
			if( apply_filters( 'woo_ce_enable_product_attributes', true ) )
				$attributes = woo_ce_get_product_attributes();

			foreach( $order_items as $key => $order_item ) {

				// Filter Order Item types from Orders export
				if( !in_array( $order_item->type, $order_items_types ) ) {
					unset( $order_items[$key] );
					continue;
				}

				$order_item_meta_sql = $wpdb->prepare( "SELECT `meta_key`, `meta_value` FROM `" . $wpdb->prefix . "woocommerce_order_itemmeta` WHERE `order_item_id` = %d ORDER BY meta_key ASC", $order_item->id );
				if( $order_item_meta = $wpdb->get_results( $order_item_meta_sql ) ) {
					$order_items[$key]->product_id = '';
					$order_items[$key]->variation_id = '';
					$order_items[$key]->sku = '';
					$order_items[$key]->image_embed = '';
					$order_items[$key]->description = '';
					$order_items[$key]->excerpt = '';
					$order_items[$key]->publish_date = '';
					$order_items[$key]->modified_date = '';
					$order_items[$key]->variation = '';
					$order_items[$key]->quantity = '';
					$order_items[$key]->total = '';
					$order_items[$key]->subtotal = '';
					$order_items[$key]->rrp = '';
					$order_items[$key]->stock = '';
					$order_items[$key]->tax = '';
					$order_items[$key]->tax_subtotal = '';
					$order_items[$key]->tax_class = '';
					$order_items[$key]->category = '';
					$order_items[$key]->tag = '';
					$order_items[$key]->total_sales = '';
					$order_items[$key]->weight = '';
					$order_items[$key]->height = '';
					$order_items[$key]->width = '';
					$order_items[$key]->length = '';
					$order_items[$key]->total_weight = '';
					$size = count( $order_item_meta );
					for( $i = 0; $i < $size; $i++ ) {

						// Go through each Order Item meta found
						switch( $order_item_meta[$i]->meta_key ) {

							case '_qty':
								$order_items[$key]->quantity = $order_item_meta[$i]->meta_value;
								break;

							case '_product_id':
								if( $order_items[$key]->product_id = $order_item_meta[$i]->meta_value ) {
									$product = get_post( $order_items[$key]->product_id );
									if( $product !== null ) {
										$order_items[$key]->description = woo_ce_format_description_excerpt( $product->post_content );
										$order_items[$key]->excerpt = woo_ce_format_description_excerpt( $product->post_excerpt );
										$order_items[$key]->publish_date = woo_ce_format_date( $product->post_date );
										$order_items[$key]->modified_date = woo_ce_format_date( $product->post_modified );
									}
									unset( $product );
									if( isset( $export->export_format ) && $export->export_format == 'xlsx' ) {
										$image_id = woo_ce_get_product_assoc_featured_image( $order_items[$key]->product_id, false, 'image_id' );
										if( $metadata = wp_get_attachment_metadata( $image_id ) ) {
											$thumbnail_size = apply_filters( 'woo_ce_override_embed_thumbnail_size', 'shop_thumbnail' );
											if( isset( $metadata['sizes'][$thumbnail_size] ) && $metadata['sizes'][$thumbnail_size]['file'] ) {
												$image_path = pathinfo( $metadata['file'] );
												$order_items[$key]->image_embed = trailingslashit( $upload_dir['basedir'] ) . trailingslashit( $image_path['dirname'] ) . $metadata['sizes'][$thumbnail_size]['file'];
											}
										}
										unset( $image_id, $metadata, $thumbnail_size, $image_path );
									}
									$order_items[$key]->sku = get_post_meta( $order_items[$key]->product_id, '_sku', true );
									$order_items[$key]->category = woo_ce_get_product_assoc_categories( $order_items[$key]->product_id );
									$order_items[$key]->tag = woo_ce_get_product_assoc_tags( $order_items[$key]->product_id );
									$order_items[$key]->total_sales = get_post_meta( $order_items[$key]->product_id, 'total_sales', true );
									$order_items[$key]->weight = get_post_meta( $order_items[$key]->product_id, '_weight', true );
									$order_items[$key]->height = get_post_meta( $order_items[$key]->product_id, '_height', true );
									$order_items[$key]->width = get_post_meta( $order_items[$key]->product_id, '_width', true );
									$order_items[$key]->length = get_post_meta( $order_items[$key]->product_id, '_length', true );
									$order_items[$key]->rrp = get_post_meta( $order_items[$key]->product_id, '_price', true );
									if( isset( $order_items[$key]->rrp ) && $order_items[$key]->rrp != '' )
										$order_items[$key]->rrp = woo_ce_format_price( $order_items[$key]->rrp );
									$order_items[$key]->stock = get_post_meta( $order_items[$key]->product_id, '_stock', true );
									$order_items[$key]->stock = ( function_exists( 'wc_stock_amount' ) ? wc_stock_amount( $order_items[$key]->stock ) : $order_items[$key]->stock );
									// Populate the Order Items: %Attribute% Attribute fields
									if( !empty( $attributes ) ) {
										$product_attributes = maybe_unserialize( get_post_meta( $order_items[$key]->product_id, '_product_attributes', true ) );
										if( !empty( $product_attributes ) ) {
											// Check for Taxonomy-based attributes
											foreach( $attributes as $attribute ) {
												if( isset( $product_attributes[sprintf( 'pa_%s', sanitize_key( $attribute->attribute_name ) )] ) ) {
													$args = array(
														'attribute' => $product_attributes[sprintf( 'pa_%s', sanitize_key( $attribute->attribute_name ) )],
														'type' => 'product'
													);
													$order_items[$key]->{sprintf( 'product_attribute_%s', sanitize_key( $attribute->attribute_name ) )} = woo_ce_get_product_assoc_attributes( $order_items[$key]->product_id, $args );
												} else {
													$args = array(
														'attribute' => $attribute,
														'type' => 'global'
													);
													$order_items[$key]->{sprintf( 'product_attribute_%s', sanitize_key( $attribute->attribute_name ) )} = woo_ce_get_product_assoc_attributes( $order_items[$key]->product_id, $args );
												}
											}
											// Check for per-Product attributes (custom)
											foreach( $product_attributes as $attribute_key => $attribute ) {
												if( $attribute['is_taxonomy'] == 0 ) {
													if( !isset( $order_items[$key]->{sprintf( 'product_attribute_%s', sanitize_key( $attribute_key ) )} ) )
														$order_items[$key]->{sprintf( 'product_attribute_%s', sanitize_key( $attribute_key ) )} = $attribute['value'];
												}
											}
										}
									}
									// Override Variable Product Type with total stock quantity of all Variations
									$term_taxonomy = 'product_type';
									if( has_term( 'variable', $term_taxonomy, $order_items[$key]->product_id ) ) {
										$_product = ( function_exists( 'wc_get_product' ) ? wc_get_product( $order_items[$key]->product_id ) : false );
										$order_items[$key]->stock = ( method_exists( $_product, 'get_total_stock' ) ? $_product->get_total_stock() : $order_items[$key]->stock );
										unset( $_product );
									}
								}
								break;

							case '_variation_id':
								$order_items[$key]->variation = '';
								if( $order_items[$key]->variation_id = $order_item_meta[$i]->meta_value ) {
									if( isset( $export->export_format ) && $export->export_format == 'xlsx' ) {
										$image_id = woo_ce_get_product_assoc_featured_image( $order_items[$key]->variation_id, false, 'image_id' );
										if( $metadata = wp_get_attachment_metadata( $image_id ) ) {
											$thumbnail_size = apply_filters( 'woo_ce_override_embed_thumbnail_size', 'shop_thumbnail' );
											if( isset( $metadata['sizes'][$thumbnail_size] ) && $metadata['sizes'][$thumbnail_size]['file'] ) {
												$image_path = pathinfo( $metadata['file'] );
												$order_items[$key]->image_embed = trailingslashit( $upload_dir['basedir'] ) . trailingslashit( $image_path['dirname'] ) . $metadata['sizes'][$thumbnail_size]['file'];
											}
										}
										unset( $image_id, $metadata, $thumbnail_size, $image_path );
									}
									// Check if the Variation SKU is set and default to the Product SKU if it is empty
									$variation_sku = get_post_meta( $order_items[$key]->variation_id, '_sku', true );
									if( !empty( $variation_sku ) )
										$order_items[$key]->sku = $variation_sku;
									unset( $variation_sku );
									$order_items[$key]->weight = get_post_meta( $order_items[$key]->variation_id, '_weight', true );
									$order_items[$key]->height = get_post_meta( $order_items[$key]->variation_id, '_height', true );
									$order_items[$key]->width = get_post_meta( $order_items[$key]->variation_id, '_width', true );
									$order_items[$key]->length = get_post_meta( $order_items[$key]->variation_id, '_length', true );
									$order_items[$key]->rrp = get_post_meta( $order_items[$key]->variation_id, '_price', true );
									if( isset( $order_items[$key]->rrp ) && $order_items[$key]->rrp != '' )
										$order_items[$key]->rrp = woo_ce_format_price( $order_items[$key]->rrp );
									$variations_sql = "SELECT `meta_key` FROM `" . $wpdb->postmeta . "` WHERE `post_id` = " . $order_items[$key]->variation_id . " AND `meta_key` LIKE 'attribute_pa_%' ORDER BY `meta_key` ASC";
									// Check if the Variation has a Term Taxonomy
									if( $variations = $wpdb->get_col( $variations_sql ) ) {
										foreach( $variations as $variation ) {

											$variation = str_replace( 'attribute_pa_', '', $variation );
											$variation_label = '';
											if( !empty( $variation ) ) {
												if( !empty( $attributes ) ) {
													foreach( $attributes as $attribute ) {
														if( $attribute->attribute_name == $variation ) {
															if( empty( $attribute->attribute_label ) )
																$attribute->attribute_label = $attribute->attribute_name;
															$variation_label = $attribute->attribute_label;
															break;
														}
													}
												}
											}
											$slug = get_post_meta( $order_items[$key]->variation_id, sprintf( 'attribute_pa_%s', $variation ), true );
											$term_taxonomy = sprintf( 'pa_%s', $variation );
											if( taxonomy_exists( $term_taxonomy ) ) {
												$term = get_term_by( 'slug', $slug, $term_taxonomy );
												if( $term && !is_wp_error( $term ) )
													$order_items[$key]->variation .= sprintf( apply_filters( 'woo_ce_get_order_items_variation_taxonomy', '%s: %s' ), $variation_label, $term->name ) . "|";
											}

										}
										$order_items[$key]->variation = substr( $order_items[$key]->variation, 0, -1 );
										unset( $variations, $variation, $variation_label, $slug, $term_taxonomy, $term );
									} else {
										// Check for per-Product variations that are not linked to a Taxonomy
										$variations_sql = "SELECT `meta_key` FROM `" . $wpdb->postmeta . "` WHERE `post_id` = " . $order_items[$key]->variation_id . " AND `meta_key` LIKE 'attribute_%' ORDER BY `meta_key` ASC";
										if( $variations = $wpdb->get_col( $variations_sql ) ) {
											foreach( $variations as $variation ) {
												$variation = str_replace( 'attribute_', '', $variation );
												$attribute = get_post_meta( $order_items[$key]->product_id, '_product_attributes', true );
												$variation_label = '';
												if( !empty( $attribute ) ) {
													if( isset( $attribute[$variation] ) )
														$variation_label = $attribute[$variation]['name'];
												}
												$slug = get_post_meta( $order_items[$key]->variation_id, sprintf( 'attribute_%s', $variation ), true );
												if( !empty( $slug ) && !empty( $variation_label ) )
													$order_items[$key]->variation .= sprintf( apply_filters( 'woo_ce_get_order_items_variation_custom', '%s: %s' ), $variation_label, ucwords( $slug ) ) . "\n";
											}
											$order_items[$key]->variation = substr( $order_items[$key]->variation, 0, -1 );
											unset( $variations, $variation, $variation_label, $attribute, $slug );
										}
									}
								}
								break;

							case '_tax_class':
								$order_items[$key]->tax_class = $order_item_meta[$i]->meta_value;
								// $order_items[$key]->tax_class = woo_ce_format_order_item_tax_class( $order_item_meta[$i]->meta_value );
								break;

							case '_line_subtotal':
								$order_items[$key]->subtotal = woo_ce_format_price( $order_item_meta[$i]->meta_value );
								break;

							case '_line_subtotal_tax':
								$order_items[$key]->tax_subtotal = woo_ce_format_price( $order_item_meta[$i]->meta_value );
								break;

							case '_line_total':
								$order_items[$key]->total = woo_ce_format_price( $order_item_meta[$i]->meta_value );
								break;

							case '_line_tax':
								$order_items[$key]->tax = woo_ce_format_price( $order_item_meta[$i]->meta_value );
								break;

							// This is for Order Item tax meta, we can safely ignore
							case '_line_tax_data':
								continue;
								break;

							// This is for any custom Order Item meta
							default:
								$order_items[$key] = apply_filters( 'woo_ce_order_item_custom_meta', $order_items[$key], $order_item_meta[$i]->meta_key, $order_item_meta[$i]->meta_value );
								break;

						}
					}
				}
				unset( $order_item_meta );

				if( !empty( $order_items[$key]->tax_class ) || ( empty( $order_items[$key]->tax_class ) && !empty( $order_items[$key]->tax ) ) ) {
					// Tax Rates
					if( !empty( $order_items[$key]->tax_class ) ) {
						$tax_rates = woo_ce_get_order_tax_rates( $order_id );
					} else {
						$tax_rates = woo_ce_get_order_tax_rates();
						$order_items[$key]->tax_class = 'Standard';
					}
					if( !empty( $tax_rates ) ) {
						foreach( $tax_rates as $tax_rate ) {
							$tax_rate['class'] = ( isset( $tax_rate['class'] ) ? $tax_rate['class'] : 'Standard' );
							if( sanitize_title_with_dashes( $tax_rate['class'] ) == sanitize_title_with_dashes( $order_items[$key]->tax_class ) ) {
								$order_items[$key]->{sprintf( 'tax_rate_%d', $tax_rate['rate_id'] )} = $order_items[$key]->tax_subtotal;
								break;
							}
						}
					}
					unset( $tax_rates );
				}

				// Default the quantity to 1 for the Fee Order Item Type
				if( $order_items[$key]->type == 'fee' )
					$order_items[$key]->quantity = 1;

				$order_items[$key]->type_id = $order_items[$key]->type;

				// Check for the Refund Line Item
				$order_items[$key]->refund_subtotal = 0;
				$order_items[$key]->refund_quantity = 0;
				if( $refunds = woo_ce_get_order_line_item_assoc_refunds( $order_items[$key]->id ) ) {
					$refund_subtotal = 0;
					$refund_quantity = 0;
					foreach( $refunds as $refund ) {
						switch( $order_items[$key]->type_id ) {

							case 'shipping':
								$refund_subtotal += wc_get_order_item_meta( $refund, '_cost' );
								break;

							default:
								$refund_subtotal += wc_get_order_item_meta( $refund, '_line_total' );
								break;

						}
						$refund_quantity += wc_get_order_item_meta( $refund, '_qty' );
					}
					$order_items[$key]->refund_subtotal = woo_ce_format_price( $refund_subtotal );
					$order_items[$key]->refund_quantity = $refund_quantity;
					unset( $refund_subtotal, $refund_quantity, $refunds, $refund );
				}

				$order_items[$key] = apply_filters( 'woo_ce_order_item', $order_items[$key], $order_id );
				$order_items[$key]->type = woo_ce_format_order_item_type( $order_items[$key]->type );
				$order_items[$key]->total_weight = ( $order_items[$key]->weight <> '' ? $order_items[$key]->weight * $order_items[$key]->quantity : '' );

			}

			// Allow Plugin/Theme authors to add support for filtering Order Items
			$order_items = apply_filters( 'woo_ce_get_order_items', $order_items, $order_id );

			return $order_items;

		}
	}

}

// Returns a list of WooCommerce Order Item Types
function woo_ce_get_order_items_types() {

	$order_item_types = array(
		'line_item' => __( 'Line Item', 'woocommerce-exporter' ),
		'coupon' => __( 'Coupon', 'woocommerce-exporter' ),
		'fee' => __( 'Fee', 'woocommerce-exporter' ),
		'tax' => __( 'Tax', 'woocommerce-exporter' ),
		'shipping' => __( 'Shipping', 'woocommerce-exporter' )
	);

	// Allow Plugin/Theme authors to add support for additional Order Item types
	$order_item_types = apply_filters( 'woo_ce_order_item_types', $order_item_types );

	return $order_item_types;

}

// Return the Order Status for a specified Order
function woo_ce_get_order_status( $order_id = 0 ) {

	global $export;

	$output = '';
	// Check if this is a WooCommerce 2.2+ instance (new Post Status)
	$woocommerce_version = woo_get_woo_version();
	if( version_compare( $woocommerce_version, '2.2' ) >= 0 ) {
		$output = get_post_status( $order_id );
		$terms = ( function_exists( 'wc_get_order_statuses' ) ? wc_get_order_statuses() : array() );
		if( isset( $terms[$output] ) )
			$output = $terms[$output];
	} else {
		$term_taxonomy = 'shop_order_status';
		$status = wp_get_object_terms( $order_id, $term_taxonomy );
		if( !empty( $status ) && is_wp_error( $status ) == false ) {
			$size = count( $status );
			for( $i = 0; $i < $size; $i++ ) {
				if( $term = get_term( $status[$i]->term_id, $term_taxonomy ) ) {
					$output .= $term->name . $export->category_separator;
					unset( $term );
				}
			}
			$output = substr( $output, 0, -1 );
		}
	}
	return $output;

}

function woo_ce_get_order_payment_gateways() {

	global $woocommerce;

	$output = false;

	// Test that payment gateways exist with WooCommerce 1.6 compatibility
	if( version_compare( $woocommerce->version, '2.0.0', '<' ) ) {
		if( $woocommerce->payment_gateways )
			$output = $woocommerce->payment_gateways->payment_gateways;
	} else {
		if( $woocommerce->payment_gateways() )
			$output = $woocommerce->payment_gateways()->payment_gateways();
	}
	// Add Other to list of payment gateways
	$output['other'] = (object)array(
		'id' => 'other',
		'title' => __( 'Other', 'woocommerce-exporter' ),
		'method_title' => __( 'Other', 'woocommerce-exporter' )
	);
	return $output;

}

function woo_ce_format_order_payment_gateway( $payment_id = '' ) {

	$output = $payment_id;
	$payment_gateways = woo_ce_get_order_payment_gateways();
	if( !empty( $payment_gateways ) ) {
		foreach( $payment_gateways as $payment_gateway ) {
			if( $payment_gateway->id == $payment_id ) {
				if( method_exists( $payment_gateway, 'get_title' ) )
					$output = $payment_gateway->get_title();
				else
					$output = $payment_id;
				break;
			}
		}
		unset( $payment_gateways, $payment_gateway );
	}
	if( empty( $payment_id ) )
		$output = __( 'N/A', 'woocommerce-exporter' );
	return $output;

}

function woo_ce_get_order_payment_gateway_usage( $payment_id = '' ) {

	$output = 0;
	if( !empty( $payment_id ) ) {
		$post_type = 'shop_order';
		$args = array(
			'post_type' => $post_type,
			'numberposts' => 1,
			'post_status' => 'any',
			'meta_query' => array(
				array(
					'key' => '_payment_method',
					'value' => $payment_id
				)
			),
			'fields' => 'ids'
		);
		$order_ids = new WP_Query( $args );
		$output = absint( $order_ids->found_posts );
		unset( $order_ids );
	}
	return $output;

}

function woo_ce_get_order_shipping_methods() {

	global $woocommerce;

	$output = false;

	// Test that payment gateways exist with WooCommerce 1.6 compatibility
	if( version_compare( $woocommerce->version, '2.0.0', '<' ) ) {
		if( $woocommerce->shipping )
			$output = $woocommerce->shipping->shipping_methods;
	} else {
		if( $woocommerce->shipping() )
			$output = $woocommerce->shipping->load_shipping_methods();
	}

	// Allow Plugin/Theme authors to add support for additional Shipping Methods
	$output = apply_filters( 'woo_ce_get_order_shipping_methods', $output );

	return $output;

}

function woo_ce_format_order_shipping_method( $shipping_id = '' ) {

	global $woocommerce;

	$output = $shipping_id;
	$shipping_methods = woo_ce_get_order_shipping_methods();
	if( !empty( $shipping_methods ) ) {
		foreach( $shipping_methods as $shipping_method ) {
			if( $shipping_method->id == $shipping_id ) {
				if( method_exists( $shipping_method, 'get_title' ) )
					$output = $shipping_method->get_title();
				else if( isset( $shipping_method->title ) )
					$output = $shipping_method->title;
				else
					$output = $shipping_id;
				break;
			}
		}
		unset( $shipping_methods );
	}
	if( empty( $shipping_id ) )
		$output = __( 'N/A', 'woocommerce-exporter' );
	return $output;

}

function woo_ce_format_order_item_type( $line_type = '' ) {

	$output = $line_type;
	switch( $line_type ) {

		case 'line_item':
			$output = __( 'Product', 'woocommerce-exporter' );
			break;

		case 'fee':
			$output = __( 'Fee', 'woocommerce-exporter' );
			break;

		case 'shipping':
			$output = __( 'Shipping', 'woocommerce-exporter' );
			break;

		case 'tax':
			$output = __( 'Tax', 'woocommerce-exporter' );
			break;

		case 'coupon':
			$output = __( 'Coupon', 'woocommerce-exporter' );
			break;

	}
	return $output;

}

function woo_ce_format_order_item_tax_class( $tax_class = '' ) {

	$output = $tax_class;
	switch( $tax_class ) {

		case 'zero-rate':
			$output = __( 'Zero Rate', 'woocommerce-exporter' );
			break;

		case 'reduced-rate':
			$output = __( 'Reduced Rate', 'woocommerce-exporter' );
			break;

		case '':
			$output = __( 'Standard', 'woocommerce-exporter' );
			break;

		case '0':
			$output = __( 'N/A', 'woocommerce-exporter' );
			break;

	}
	return $output;

}

function woo_ce_format_order_status( $status_id = '' ) {

	$output = $status_id;
	// Check if an empty Order Status has been provided
	if( empty( $status_id ) )
		return $output;

	$order_statuses = woo_ce_get_order_statuses();
	if( !empty( $order_statuses ) ) {
		foreach( $order_statuses as $order_status ) {
			if( $order_status->slug == $status_id || strtolower( $order_status->name ) == $status_id || strpos( $order_status->slug, $status_id ) !== false ) {
				$output = ucfirst( $order_status->name );
				break;
			}
		}
	}
	return $output;

}
?>