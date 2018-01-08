<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	if( !function_exists( 'woo_ce_get_export_type_commission_count' ) ) {
		function woo_ce_get_export_type_commission_count( $count = 0, $export_type = '', $args ) {

			if( $export_type <> 'commission' )
				return $count;

			$count = 0;
			$post_type = 'shop_commission';

			// Override for WordPress MultiSite
			if( woo_ce_is_network_admin() ) {
				$sites = wp_get_sites();
				foreach( $sites as $site ) {
					switch_to_blog( $site['blog_id'] );
					if( post_type_exists( $post_type ) ) {
						$count += woo_ce_count_object( wp_count_posts( $post_type ) );
					} else if( woo_ce_detect_export_plugin( 'wc_vendors' ) ) {
						// Check for WC-Vendors

						global $wpdb;

						$count += $wpdb->get_var( 'SELECT COUNT(id) FROM `' . $wpdb->prefix . 'pv_commission`' );
					}
					restore_current_blog();
				}
				return $count;
			}

			// Check if the existing Transient exists
			$cached = get_transient( WOO_CD_PREFIX . '_commission_count' );
			if( $cached == false ) {
				if( post_type_exists( $post_type ) ) {
					$count = woo_ce_count_object( wp_count_posts( $post_type ) );
				} else if( woo_ce_detect_export_plugin( 'wc_vendors' ) ) {
					// Check for WC-Vendors

					global $wpdb;

					$count = $wpdb->get_var( 'SELECT COUNT(id) FROM `' . $wpdb->prefix . 'pv_commission`' );
				}
				set_transient( WOO_CD_PREFIX . '_commission_count', $count, HOUR_IN_SECONDS );
			} else {
				$count = $cached;
			}
			return $count;

		}
		add_filter( 'woo_ce_get_export_type_count', 'woo_ce_get_export_type_commission_count', 10, 3 );
	}

	// HTML template for Filter Commissions by Commission Date widget on Store Exporter screen
	function woo_ce_commissions_filter_by_date() {

		$today = date( 'l' );
		$yesterday = date( 'l', strtotime( '-1 days' ) );
		$current_month = date( 'F' );
		$last_month = date( 'F', mktime( 0, 0, 0, date( 'n' )-1, 1, date( 'Y' ) ) );
		$commission_dates_variable = '';
		$commission_dates_variable_length = '';
		$date_format = 'd/m/Y';
		$commission_dates_from = woo_ce_get_commission_first_date( $date_format );
		$commission_dates_to = date( $date_format );

		ob_start(); ?>
<p><label><input type="checkbox" id="commissions-filters-date" /> <?php _e( 'Filter Commissions by Commission Date', 'woocommerce-exporter' ); ?></label></p>
<div id="export-commissions-filters-date" class="separator">
	<ul>
		<li>
			<label><input type="radio" name="commission_dates_filter" value="today" /> <?php _e( 'Today', 'woocommerce-exporter' ); ?> (<?php echo $today; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="commission_dates_filter" value="yesterday" /> <?php _e( 'Yesterday', 'woocommerce-exporter' ); ?> (<?php echo $yesterday; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="commission_dates_filter" value="current_week" /> <?php _e( 'Current week', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="commission_dates_filter" value="last_week" /> <?php _e( 'Last week', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="commission_dates_filter" value="current_month" /> <?php _e( 'Current month', 'woocommerce-exporter' ); ?> (<?php echo $current_month; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="commission_dates_filter" value="last_month" /> <?php _e( 'Last month', 'woocommerce-exporter' ); ?> (<?php echo $last_month; ?>)</label>
		</li>
<!--
		<li>
			<label><input type="radio" name="commission_dates_filter" value="last_quarter" /> <?php _e( 'Last quarter', 'woocommerce-exporter' ); ?> (Nov. - Jan.)</label>
		</li>
-->
		<li>
			<label><input type="radio" name="commission_dates_filter" value="variable" /> <?php _e( 'Variable date', 'woocommerce-exporter' ); ?></label>
			<div style="margin-top:0.2em;">
				<?php _e( 'Last', 'woocommerce-exporter' ); ?>
				<input type="text" name="commission_dates_filter_variable" class="text code" size="4" maxlength="4" value="<?php echo $commission_dates_variable; ?>" />
				<select name="commission_dates_filter_variable_length" style="vertical-align:top;">
					<option value=""<?php selected( $commission_dates_variable_length, '' ); ?>>&nbsp;</option>
					<option value="second"<?php selected( $commission_dates_variable_length, 'second' ); ?>><?php _e( 'second(s)', 'woocommerce-exporter' ); ?></option>
					<option value="minute"<?php selected( $commission_dates_variable_length, 'minute' ); ?>><?php _e( 'minute(s)', 'woocommerce-exporter' ); ?></option>
					<option value="hour"<?php selected( $commission_dates_variable_length, 'hour' ); ?>><?php _e( 'hour(s)', 'woocommerce-exporter' ); ?></option>
					<option value="day"<?php selected( $commission_dates_variable_length, 'day' ); ?>><?php _e( 'day(s)', 'woocommerce-exporter' ); ?></option>
					<option value="week"<?php selected( $commission_dates_variable_length, 'week' ); ?>><?php _e( 'week(s)', 'woocommerce-exporter' ); ?></option>
					<option value="month"<?php selected( $commission_dates_variable_length, 'month' ); ?>><?php _e( 'month(s)', 'woocommerce-exporter' ); ?></option>
					<option value="year"<?php selected( $commission_dates_variable_length, 'year' ); ?>><?php _e( 'year(s)', 'woocommerce-exporter' ); ?></option>
				</select>
			</div>
		</li>
		<li>
			<label><input type="radio" name="commission_dates_filter" value="manual" /> <?php _e( 'Fixed date', 'woocommerce-exporter' ); ?></label>
			<div style="margin-top:0.2em;">
				<input type="text" size="10" maxlength="10" id="commission_dates_from" name="commission_dates_from" value="<?php echo esc_attr( $commission_dates_from ); ?>" class="text code datepicker commission_export" /> to <input type="text" size="10" maxlength="10" id="commission_dates_to" name="commission_dates_to" value="<?php echo esc_attr( $commission_dates_to ); ?>" class="text code datepicker commission_export" />
				<p class="description"><?php _e( 'Filter the dates of Orders to be included in the export. Default is the date of the first Commission to today.', 'woocommerce-exporter' ); ?></p>
			</div>
		</li>
	</ul>
</div>
<!-- #export-commissions-filters-date -->
<?php
		ob_end_flush();

	}

	// Returns date of first Commission received, any status
	function woo_ce_get_commission_first_date( $date_format = 'd/m/Y' ) {

		$output = date( $date_format, mktime( 0, 0, 0, date( 'n' ), 1 ) );
		$post_type = 'shop_commission';
		$args = array(
			'post_type' => $post_type,
			'orderby' => 'post_date',
			'order' => 'ASC',
			'numberposts' => 1
		);
		$commissions = get_posts( $args );
		if( $commissions ) {
			$commission = strtotime( $commissions[0]->post_date );
			$output = date( $date_format, $commission );
			unset( $commissions, $commission );
		}
		return $output;

	}

	// HTML template for Commission Sorting widget on Store Exporter screen
	function woo_ce_commission_sorting() {

		$orderby = woo_ce_get_option( 'commission_orderby', 'ID' );
		$order = woo_ce_get_option( 'commission_order', 'ASC' );

		ob_start(); ?>
<p><label><?php _e( 'Commission Sorting', 'woocommerce-exporter' ); ?></label></p>
<div>
	<select name="commission_orderby">
		<option value="ID"<?php selected( 'ID', $orderby ); ?>><?php _e( 'Commission ID', 'woocommerce-exporter' ); ?></option>
		<option value="title"<?php selected( 'title', $orderby ); ?>><?php _e( 'Commission Title', 'woocommerce-exporter' ); ?></option>
		<option value="date"<?php selected( 'date', $orderby ); ?>><?php _e( 'Date Created', 'woocommerce-exporter' ); ?></option>
		<option value="modified"<?php selected( 'modified', $orderby ); ?>><?php _e( 'Date Modified', 'woocommerce-exporter' ); ?></option>
		<option value="rand"<?php selected( 'rand', $orderby ); ?>><?php _e( 'Random', 'woocommerce-exporter' ); ?></option>
	</select>
	<select name="commission_order">
		<option value="ASC"<?php selected( 'ASC', $order ); ?>><?php _e( 'Ascending', 'woocommerce-exporter' ); ?></option>
		<option value="DESC"<?php selected( 'DESC', $order ); ?>><?php _e( 'Descending', 'woocommerce-exporter' ); ?></option>
	</select>
	<p class="description"><?php _e( 'Select the sorting of Commissions within the exported file. By default this is set to export Commissions by Commission ID in Desending order.', 'woocommerce-exporter' ); ?></p>
</div>
<?php
		ob_end_flush();

	}

	// HTML template for Filter Commissions by Product Vendor widget on Store Exporter screen
	function woo_ce_commissions_filter_by_product_vendor() {

		// Product Vendors - http://www.woothemes.com/products/product-vendors/
		if( woo_ce_detect_export_plugin( 'vendors' ) == false )
			return;

		$product_vendors = woo_ce_get_product_vendors( array(), 'full' );

		ob_start(); ?>
<p><label><input type="checkbox" id="commissions-filters-product_vendor" /> <?php _e( 'Filter Commissions by Product Vendors', 'woocommerce-exporter' ); ?></label></p>
<div id="export-commissions-filters-product_vendor" class="separator">
<?php if( $product_vendors ) { ?>
	<ul>
	<?php foreach( $product_vendors as $product_vendor ) { ?>
		<li>
			<label><input type="checkbox" name="commission_filter_product_vendor[<?php echo $product_vendor->term_id; ?>]" value="<?php echo $product_vendor->term_id; ?>" title="<?php printf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_vendor->term_id ); ?>"<?php disabled( $product_vendor->count, 0 ); ?> /> <?php echo $product_vendor->name; ?></label>
			<span class="description">(<?php echo $product_vendor->count; ?>)</span>
		</li>
	<?php } ?>
	</ul>
	<p class="description"><?php _e( 'Select the Product Vendors you want to filter exported Commissions by. Default is to include all Product Vendors.', 'woocommerce-exporter' ); ?></p>
<?php } else { ?>
	<p><?php _e( 'No Product Vendors were found.', 'woocommerce-exporter' ); ?></p>
<?php } ?>
</div>
<!-- #export-commissions-filters-product_vendor -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Commissions by Commission Status widget on Store Exporter screen
	function woo_ce_commissions_filter_by_commission_status() {

		ob_start(); ?>
<p><label><input type="checkbox" id="commissions-filters-commission_status" /> <?php _e( 'Filter Commissions by Commission Status', 'woocommerce-exporter' ); ?></label></p>
<div id="export-commissions-filters-commission_status" class="separator">
	<ul>
		<li>
			<label><input type="checkbox" name="commission_filter_commission_status[]" value="unpaid"<?php disabled( woo_ce_commissions_stock_status_count( 'unpaid' ), 0 ); ?> /> <?php _e( 'Unpaid', 'woocommerce-exporter' ); ?></label>
			<span class="description">(<?php echo woo_ce_commissions_stock_status_count( 'unpaid' ); ?>)</span>
		</li>
		<li>
			<label><input type="checkbox" name="commission_filter_commission_status[]" value="paid"<?php disabled( woo_ce_commissions_stock_status_count( 'paid' ), 0 ); ?> /> <?php _e( 'Paid', 'woocommerce-exporter' ); ?></label>
			<span class="description">(<?php echo woo_ce_commissions_stock_status_count( 'paid' ); ?>)</span>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Commission Status you want to filter exported Commissions by. Default is to include all Commission Statuses.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-commissions-filters-commission_status -->
<?php
		ob_end_flush();

	}

	// HTML template for displaying the number of each export type filter on the Archives screen
	function woo_ce_commissions_stock_status_count( $type = '' ) {

		$output = 0;
		$post_type = 'shop_commission';
		$meta_key = '_paid_status';
		$args = array(
			'post_type' => $post_type,
			'meta_key' => $meta_key,
			'meta_value' => null,
			'numberposts' => -1,
			'fields' => 'ids'
		);
		if( $type )
			$args['meta_value'] = $type;
		$commission_ids = new WP_Query( $args );
		if( !empty( $commission_ids->posts ) )
			$output = count( $commission_ids->posts );
		return $output;

	}

	function woo_ce_commission_dataset_args( $args, $export_type = '' ) {

		// Check if we're dealing with the Commission Export Type
		if( $export_type <> 'commission' )
			return $args;

		// Merge in the form data for this dataset
		$defaults = array(
			'commission_dates_filter' => ( isset( $_POST['commission_dates_filter'] ) ? sanitize_text_field( $_POST['commission_dates_filter'] ) : false ),
			'commission_dates_from' => ( isset( $_POST['commission_dates_from'] ) ? woo_ce_format_order_date( sanitize_text_field( $_POST['commission_dates_from'] ) ) : '' ),
			'commission_dates_to' => ( isset( $_POST['commission_dates_to'] ) ? woo_ce_format_order_date( sanitize_text_field( $_POST['commission_dates_to'] ) ) : '' ),
			'commission_dates_filter_variable' => ( isset( $_POST['commission_dates_filter_variable'] ) ? absint( $_POST['commission_dates_filter_variable'] ) : false ),
			'commission_dates_filter_variable_length' => ( isset( $_POST['commission_dates_filter_variable_length'] ) ? sanitize_text_field( $_POST['commission_dates_filter_variable_length'] ) : false ),
			'commission_product_vendors' => ( isset( $_POST['commission_filter_product_vendor'] ) ? woo_ce_format_product_filters( array_map( 'absint', $_POST['commission_filter_product_vendor'] ) ) : false ),
			'commission_status' => ( isset( $_POST['commission_filter_commission_status'] ) ? woo_ce_format_product_filters( array_map( 'sanitize_text_field', $_POST['commission_filter_commission_status'] ) ) : false ),
			'commission_orderby' => ( isset( $_POST['commission_orderby'] ) ? sanitize_text_field( $_POST['commission_orderby'] ) : false ),
			'commission_order' => ( isset( $_POST['commission_order'] ) ? sanitize_text_field( $_POST['commission_order'] ) : false )
		);
		$args = wp_parse_args( $args, $defaults );

		// Save dataset export specific options
		if( $args['commission_orderby'] <> woo_ce_get_option( 'commission_orderby' ) )
			woo_ce_update_option( 'commission_orderby', $args['commission_orderby'] );
		if( $args['commission_order'] <> woo_ce_get_option( 'commission_order' ) )
			woo_ce_update_option( 'commission_order', $args['commission_order'] );

		return $args;

	}
	add_filter( 'woo_ce_extend_dataset_args', 'woo_ce_commission_dataset_args', 10, 2 );

	/* End of: WordPress Administration */

}

function woo_ce_cron_commission_dataset_args( $args, $export_type = '', $is_scheduled = 0 ) {

	// Check if we're dealing with the Commission Export Type
	if( $export_type <> 'commission' )
		return $args;

	$commission_dates_filter = false;
	$commission_filter_dates_from = false;
	$commission_filter_dates_to = false;
	$commission_filter_date_variable = false;
	$commission_filter_date_variable_length = false;

	if( $is_scheduled ) {
		$scheduled_export = ( $is_scheduled ? absint( get_transient( WOO_CD_PREFIX . '_scheduled_export_id' ) ) : 0 );
		// Commission Date
		$commission_dates_filter = get_post_meta( $scheduled_export, '_filter_commission_date', true );
		if( !empty( $commission_dates_filter ) ) {
			switch( $commission_dates_filter ) {

				case 'manual':
					$commission_filter_dates_from = get_post_meta( $scheduled_export, '_filter_commission_dates_from', true );
					$commission_filter_dates_to = get_post_meta( $scheduled_export, '_filter_commission_date_to', true );
					break;

				case 'variable':
					$commission_filter_date_variable = get_post_meta( $scheduled_export, '_filter_commission_date_variable', true );
					$commission_filter_date_variable_length = get_post_meta( $scheduled_export, '_filter_commission_date_variable_length', true );
					break;

			}
		}
	}

	// Merge in the form data for this dataset
	$defaults = array(
		'commission_dates_filter' => $commission_dates_filter,
		'commission_dates_from' => ( !empty( $commission_filter_dates_from ) ? sanitize_text_field( $commission_filter_dates_from ) : false ),
		'commission_dates_to' => ( !empty( $commission_filter_dates_to ) ? sanitize_text_field( $commission_filter_dates_to ) : false ),
		'commission_dates_filter_variable' => ( !empty( $commission_filter_date_variable ) ? absint( $commission_filter_date_variable ) : false ),
		'commission_dates_filter_variable_length' => ( !empty( $commission_filter_date_variable_length ) ? sanitize_text_field( $commission_filter_date_variable_length ) : false )
	);
	$args = wp_parse_args( $args, $defaults );

	return $args;

}
add_filter( 'woo_ce_extend_cron_dataset_args', 'woo_ce_cron_commission_dataset_args', 10, 3 );

function woo_ce_get_commission_fields( $format = 'full', $post_ID = 0 ) {

	$export_type = 'commission';

	$fields = array();
	$fields[] = array(
		'name' => 'ID',
		'label' => __( 'Commission ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'post_date',
		'label' => __( 'Commission Date', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'product_id',
		'label' => __( 'Product ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'product_name',
		'label' => __( 'Product Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'product_sku',
		'label' => __( 'Product SKU', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'product_vendor_id',
		'label' => __( 'Product Vendor ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'product_vendor_name',
		'label' => __( 'Product Vendor Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'commission_amount',
		'label' => __( 'Commission Amount', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'paid_status',
		'label' => __( 'Commission Status', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'post_status',
		'label' => __( 'Post Status', 'woocommerce-exporter' )
	);

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
function woo_ce_override_commission_field_labels( $fields = array() ) {

	$labels = woo_ce_get_option( 'commission_labels', array() );
	if( !empty( $labels ) ) {
		foreach( $fields as $key => $field ) {
			if( isset( $labels[$field['name']] ) )
				$fields[$key]['label'] = $labels[$field['name']];
		}
	}
	return $fields;

}
add_filter( 'woo_ce_commission_fields', 'woo_ce_override_commission_field_labels', 11 );

// Returns the export column header label based on an export column slug
function woo_ce_get_commission_field( $name = null, $format = 'name' ) {

	$output = '';
	if( $name ) {
		$fields = woo_ce_get_commission_fields();
		$size = count( $fields );
		for( $i = 0; $i < $size; $i++ ) {
			if( $fields[$i]['name'] == $name ) {
				switch( $format ) {

					case 'name':
						$output = $fields[$i]['label'];
						break;

					case 'full':
						$output = $fields[$i];
						break;

				}
				$i = $size;
			}
		}
	}
	return $output;

}

// Returns a list of Commission Post IDs
function woo_ce_get_commissions( $args = array() ) {

	global $export;

	$limit_volume = -1;
	$offset = 0;

	if( $args ) {
		$product_vendors = ( isset( $args['commission_product_vendors'] ) ? $args['commission_product_vendors'] : false );
		$status = ( isset( $args['commission_status'] ) ? $args['commission_status'] : false );
		$limit_volume = ( isset( $args['limit_volume'] ) ? $args['limit_volume'] : false );
		$offset = ( isset( $args['offset'] ) ? $args['offset'] : false );
		$orderby = ( isset( $args['commission_orderby'] ) ? $args['commission_orderby'] : 'ID' );
		$order = ( isset( $args['commission_order'] ) ? $args['commission_order'] : 'ASC' );
		$commission_dates_filter = ( isset( $args['commission_dates_filter'] ) ? $args['commission_dates_filter'] : false );
		switch( $commission_dates_filter ) {

			case 'today':
				$commission_dates_from = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n' ), date( 'd' ) ) );
				$commission_dates_to = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n' ), date( 'd' ) ) );
				break;

			case 'yesterday':
				$commission_dates_from = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n', strtotime( '-2 days' ) ), date( 'd', strtotime( '-2 days' ) ) ) );
				$commission_dates_to = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n', strtotime( '-1 days' ) ), date( 'd', strtotime( '-1 days' ) ) ) );
				break;

			case 'current_week':
				$commission_dates_from = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n', strtotime( 'this Monday' ) ), date( 'd', strtotime( 'this Monday' ) ) ) );
				$commission_dates_to = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n', strtotime( 'next Sunday' ) ), date( 'd', strtotime( 'next Sunday' ) ) ) );
				break;

			case 'last_week':
				$commission_dates_from = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n', strtotime( 'last Monday' ) ), date( 'd', strtotime( 'last Monday' ) ) ) );
				$commission_dates_to = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n', strtotime( 'last Sunday' ) ), date( 'd', strtotime( 'last Sunday' ) ) ) );
				break;

			case 'current_month':
				$commission_dates_from = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n' ), 1 ) );
				$commission_dates_to = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n', strtotime( '+1 month' ) ), 0 ) );
				break;

			case 'last_month':
				$commission_dates_from = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n', strtotime( '-1 month' ) ), 1 ) );
				$commission_dates_to = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n' ), 0 ) );
				break;

			case 'last_quarter':
				break;

			case 'manual':
				$commission_dates_from = woo_ce_format_order_date( $args['commission_dates_from'] );
				$commission_dates_to = woo_ce_format_order_date( $args['commission_dates_to'] );
				break;

			case 'variable':
				$commission_filter_date_variable = $args['commission_dates_filter_variable'];
				$commission_filter_date_variable_length = $args['commission_dates_filter_variable_length'];
				if( $commission_filter_date_variable !== false && $commission_filter_date_variable_length !== false ) {
					$commission_filter_date_strtotime = sprintf( '-%d %s', $commission_filter_date_variable, $commission_filter_date_variable_length );
					$commission_dates_from = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n', strtotime( $commission_filter_date_strtotime ) ), date( 'd', strtotime( $commission_filter_date_strtotime ) ) ) );
					$commission_dates_to = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n' ), date( 'd' ) ) );
					unset( $commission_filter_date_variable, $commission_filter_date_variable_length, $commission_filter_date_strtotime );
				}
				break;

			default:
				$commission_dates_from = false;
				$commission_dates_to = false;
				break;

		}
		if( $commission_dates_from && $commission_dates_to ) {
			$commission_dates_from = strtotime( $commission_dates_from );
			$commission_dates_to = explode( '-', $commission_dates_to );
			// Check that a valid date was provided
			if( isset( $commission_dates_to[0] ) && isset( $commission_dates_to[1] ) && isset( $commission_dates_to[2] ) )
				$commission_dates_to = strtotime( date( 'd-m-Y', mktime( 0, 0, 0, $commission_dates_to[1], $commission_dates_to[0]+1, $commission_dates_to[2] ) ) );
			else	
				$commission_dates_to = false;
		}
	}
	$post_type = 'shop_commission';
	$args = array(
		'post_type' => $post_type,
		'orderby' => $orderby,
		'order' => $order,
		'offset' => $offset,
		'posts_per_page' => $limit_volume,
		'post_status' => woo_ce_post_statuses(),
		'fields' => 'ids',
		'suppress_filters' => false
	);
	if( !empty( $product_vendors ) ) {
		$args['meta_query'][] = array(
			'key' => '_commission_vendor',
			'value' => $product_vendors,
			'compare' => 'IN'
		);
	}
	if( !empty( $status ) ) {
		$args['meta_query'][] = array(
			'key' => '_paid_status',
			'value' => $status,
			'compare' => 'IN'
		);
	}
	$commissions = array();

	// Allow other developers to bake in their own filters
	$args = apply_filters( 'woo_ce_get_commissions_args', $args );

	// Override for Plugins that use custom tables; naughty, naughty...
	if( apply_filters( 'woo_ce_override_commission_data', false ) ) {
		$commissions = apply_filters( 'woo_ce_override_get_commissions', false );
		return $commissions;
	}

	$commission_ids = new WP_Query( $args );
	if( $commission_ids->posts ) {
		foreach( $commission_ids->posts as $commission_id ) {

			// Get Commission details
			$commission = get_post( $commission_id );

			// Filter Commission dates by dropping those outside the date range
			if( $commission_dates_from && $commission_dates_to ) {
				if( ( strtotime( $commission->post_date ) > $commission_dates_from ) && ( strtotime( $commission->post_date ) < $commission_dates_to ) ) {
					// Do nothing
				} else {
					unset( $commission );
					continue;
				}
			}

			$commissions[] = $commission_id;
		}
		unset( $commission_ids, $commission_id );
	}
	return $commissions;

}

function woo_ce_export_dataset_override_commission( $output = null, $export_type = null ) {

	global $export;

	if( $commissions = woo_ce_get_commissions( $export->args ) ) {
		$export->total_rows = count( $commissions );
		// XML, RSS export
		if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
			if( !empty( $export->fields ) ) {
				foreach( $commissions as $commission ) {
					if( $export->export_format == 'xml' )
						$child = $output->addChild( apply_filters( 'woo_ce_export_xml_commission_node', sanitize_key( $export_type ) ) );
					else if( $export->export_format == 'rss' )
						$child = $output->addChild( 'item' );
					$child->addAttribute( 'id', ( isset( $commission ) ? $commission : '' ) );
					$commission = woo_ce_get_commission_data( $commission, $export->args, array_keys( $export->fields ) );
					foreach( array_keys( $export->fields ) as $key => $field ) {
						if( isset( $commission->$field ) ) {
							if( !is_array( $field ) ) {
								if( woo_ce_is_xml_cdata( $commission->$field ) )
									$child->addChild( sanitize_key( $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $commission->$field ) ) );
								else
									$child->addChild( sanitize_key( $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $commission->$field ) ) );
							}
						}
					}
				}
			}
		} else {
			// PHPExcel export
			foreach( $commissions as $key => $commission )
				$commissions[$key] = woo_ce_get_commission_data( $commission, $export->args, array_keys( $export->fields ) );
			$output = $commissions;
		}
		unset( $commissions, $commission );
	}
	return $output;

}

function woo_ce_export_dataset_multisite_override_commission( $output = null, $export_type = null ) {

	global $export;

	$sites = wp_get_sites();
	if( !empty( $sites ) ) {
		foreach( $sites as $site ) {
			switch_to_blog( $site['blog_id'] );
			if( $commissions = woo_ce_get_commissions( $export->args ) ) {
				$export->total_rows = count( $commissions );
				// XML, RSS export
				if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
					if( !empty( $export->fields ) ) {
						foreach( $commissions as $commission ) {
							if( $export->export_format == 'xml' )
								$child = $output->addChild( apply_filters( 'woo_ce_export_xml_commission_node', sanitize_key( $export_type ) ) );
							else if( $export->export_format == 'rss' )
								$child = $output->addChild( 'item' );
							$child->addAttribute( 'id', ( isset( $commission ) ? $commission : '' ) );
							$commission = woo_ce_get_commission_data( $commission, $export->args, array_keys( $export->fields ) );
							foreach( array_keys( $export->fields ) as $key => $field ) {
								if( isset( $commission->$field ) ) {
									if( !is_array( $field ) ) {
										if( woo_ce_is_xml_cdata( $commission->$field ) )
											$child->addChild( sanitize_key( $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $commission->$field ) ) );
										else
											$child->addChild( sanitize_key( $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $commission->$field ) ) );
									}
								}
							}
						}
					}
				} else {
					// PHPExcel export
					foreach( $commissions as $key => $commission )
						$commissions[$key] = woo_ce_get_commission_data( $commission, $export->args, array_keys( $export->fields ) );
					if( is_null( $output ) )
						$output = $commissions;
					else
						$output = array_merge( $output, $commissions );
				}
				unset( $commissions, $commission );
			}
			restore_current_blog();
		}
	}
	return $output;

}

function woo_ce_get_commission_data( $commission_id = 0, $args = array() ) {

	global $export;

	$commission = get_post( $commission_id );

	// Override for Plugins that use custom tables; naughty, naughty...
	if( apply_filters( 'woo_ce_override_commission_data', false ) ) {
		$commission = apply_filters( 'woo_ce_override_get_commission_data', false, $commission_id, $args );
		return $commission;
	}

	$commission->title = $commission->post_title;
	$commission->product_id = get_post_meta( $commission->ID, '_commission_product', true );
	$commission->product_name = woo_ce_format_post_title( get_the_title( $commission->product_id ) );
	$commission->product_sku = get_post_meta( $commission->product_id, '_sku', true );
	$commission->product_vendor_id = get_post_meta( $commission->ID, '_commission_vendor', true );
	$product_vendor = woo_ce_get_product_vendor_data( $commission->product_vendor_id );
	$commission->product_vendor_name = ( isset( $product_vendor->title ) ? $product_vendor->title : '' );
	unset( $product_vendor );

	$commission->commission_amount = get_post_meta( $commission->ID, '_commission_amount', true );
	// Check that a valid price has been provided
	if( isset( $commission->commission_amount ) && $commission->commission_amount != '' && function_exists( 'wc_format_localized_price' ) )
		$commission->commission_amount = woo_ce_format_price( $commission->commission_amount );
	$commission->paid_status = woo_ce_format_commission_paid_status( get_post_meta( $commission->ID, '_paid_status', true ) );
	$commission->post_date = woo_ce_format_date( $commission->post_date );
	$commission->post_status = woo_ce_format_post_status ( $commission->post_status );

	return $commission;

}

function woo_ce_extend_commission_fields( $fields = array() ) {

	// Product Vendors - http://www.woothemes.com/products/product-vendors/
	if( woo_ce_detect_export_plugin( 'vendors' ) ) {
		$fields[] = array(
			'name' => 'title',
			'label' => __( 'Commission Title', 'woocommerce-exporter' )
		);
	}

	return $fields;

}
add_filter( 'woo_ce_commission_fields', 'woo_ce_extend_commission_fields' );

function woo_ce_format_commission_paid_status( $paid_status = '' ) {

	$output = $paid_status;
	switch( $output ) {

		case 'paid':
			$output = __( 'Paid', 'woocommerce-exporter' );
			break;

		default:
		case 'unpaid':
			$output = __( 'Unpaid', 'woocommerce-exporter' );
			break;

	}
	return $output;

}
?>