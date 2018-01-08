<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	if( !function_exists( 'woo_ce_get_export_type_coupon_count' ) ) {
		function woo_ce_get_export_type_coupon_count() {

			$count = 0;
			$post_type = apply_filters( 'woo_ce_coupon_term_taxonomy', 'shop_coupon' );

			// Override for WordPress MultiSite
			if( woo_ce_is_network_admin() ) {
				$sites = wp_get_sites();
				foreach( $sites as $site ) {
					switch_to_blog( $site['blog_id'] );
					if( post_type_exists( $post_type ) )
						$count += woo_ce_count_object( wp_count_posts( $post_type ) );
					restore_current_blog();
				}
				return $count;
			}

			// Check if the existing Transient exists
			$cached = get_transient( WOO_CD_PREFIX . '_coupon_count' );
			if( $cached == false ) {
				if( post_type_exists( $post_type ) )
					$count = woo_ce_count_object( wp_count_posts( $post_type ) );
				set_transient( WOO_CD_PREFIX . '_coupon_count', $count, HOUR_IN_SECONDS );
			} else {
				$count = $cached;
			}
			return $count;

		}
	}

	// HTML template for Filter Coupons by Discount Type on Store Exporter screen
	function woo_ce_coupons_filter_by_discount_type() {

		$discount_types = woo_ce_get_coupon_discount_types();

		ob_start(); ?>
<p><label><input type="checkbox" id="coupons-filters-discount_types" /> <?php _e( 'Filter Coupons by Discount Type', 'woocommerce-exporter' ); ?></label></p>
<div id="export-coupons-filters-discount_types" class="separator">
	<ul>
		<li>
<?php if( !empty( $discount_types ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Discount Type...', 'woocommerce-exporter' ); ?>" name="coupon_filter_discount_type[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $discount_types as $key => $discount_type ) { ?>
				<option value="<?php echo $key; ?>"><?php echo $discount_type; ?> (<?php printf( __( 'Post meta key: %s', 'woocommerce-exporter' ), $key ); ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Discount Types were found.', 'woocommerce-exporter' ); ?></li>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Discount Types you want to filter exported Coupons by. Default is to include all Coupons.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-products-filters-discount_types -->

<?php
		ob_end_flush();

	}

	// HTML template for Coupon Sorting widget on Store Exporter screen
	function woo_ce_coupon_sorting() {

		$orderby = woo_ce_get_option( 'coupon_orderby', 'ID' );
		$order = woo_ce_get_option( 'coupon_order', 'ASC' );

		ob_start(); ?>
<p><label><?php _e( 'Coupon Sorting', 'woocommerce-exporter' ); ?></label></p>
<div>
	<select name="coupon_orderby">
		<option value="ID"<?php selected( 'ID', $orderby ); ?>><?php _e( 'Coupon ID', 'woocommerce-exporter' ); ?></option>
		<option value="title"<?php selected( 'title', $orderby ); ?>><?php _e( 'Coupon Code', 'woocommerce-exporter' ); ?></option>
		<option value="date"<?php selected( 'date', $orderby ); ?>><?php _e( 'Date Created', 'woocommerce-exporter' ); ?></option>
		<option value="modified"<?php selected( 'modified', $orderby ); ?>><?php _e( 'Date Modified', 'woocommerce-exporter' ); ?></option>
		<option value="rand"<?php selected( 'rand', $orderby ); ?>><?php _e( 'Random', 'woocommerce-exporter' ); ?></option>
	</select>
	<select name="coupon_order">
		<option value="ASC"<?php selected( 'ASC', $order ); ?>><?php _e( 'Ascending', 'woocommerce-exporter' ); ?></option>
		<option value="DESC"<?php selected( 'DESC', $order ); ?>><?php _e( 'Descending', 'woocommerce-exporter' ); ?></option>
	</select>
	<p class="description"><?php _e( 'Select the sorting of Coupons within the exported file. By default this is set to export Coupons by Coupon ID in Desending order.', 'woocommerce-exporter' ); ?></p>
</div>
<?php
		ob_end_flush();

	}

	function woo_ce_coupon_dataset_args( $args, $export_type = '' ) {

		// Check if we're dealing with the Coupon Export Type
		if( $export_type <> 'coupon' )
			return $args;

		// Merge in the form data for this dataset
		$defaults = array(
			'coupon_discount_types' => ( isset( $_POST['coupon_filter_discount_type'] ) ? woo_ce_format_product_filters( array_map( 'sanitize_text_field', $_POST['coupon_filter_discount_type'] ) ) : false ),
			'coupon_orderby' => ( isset( $_POST['coupon_orderby'] ) ? sanitize_text_field( $_POST['coupon_orderby'] ) : false ),
			'coupon_order' => ( isset( $_POST['coupon_order'] ) ? sanitize_text_field( $_POST['coupon_order'] ) : false ),
		);
		$args = wp_parse_args( $args, $defaults );

		// Save dataset export specific options
		// Discount Type
		if( $args['coupon_orderby'] <> woo_ce_get_option( 'coupon_orderby' ) )
			woo_ce_update_option( 'coupon_orderby', $args['coupon_orderby'] );
		if( $args['coupon_order'] <> woo_ce_get_option( 'coupon_order' ) )
			woo_ce_update_option( 'coupon_order', $args['coupon_order'] );

		return $args;

	}
	add_filter( 'woo_ce_extend_dataset_args', 'woo_ce_coupon_dataset_args', 10, 2 );

	/* End of: WordPress Administration */

}

// Returns a list of Coupon export columns
function woo_ce_get_coupon_fields( $format = 'full', $post_ID = 0 ) {

	$export_type = 'coupon';

	$fields = array();
	$fields[] = array(
		'name' => 'coupon_code',
		'label' => __( 'Coupon Code', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'coupon_description',
		'label' => __( 'Coupon Description', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'discount_type',
		'label' => __( 'Discount Type', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'coupon_amount',
		'label' => __( 'Coupon Amount', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'individual_use',
		'label' => __( 'Individual Use', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'apply_before_tax',
		'label' => __( 'Apply before tax', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'exclude_sale_items',
		'label' => __( 'Exclude sale items', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'minimum_amount',
		'label' => __( 'Minimum Amount', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'maximum_amount',
		'label' => __( 'Maximum Amount', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'product_ids',
		'label' => __( 'Products', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'exclude_product_ids',
		'label' => __( 'Exclude Products', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'product_categories',
		'label' => __( 'Product Categories', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'exclude_product_categories',
		'label' => __( 'Exclude Product Categories', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'customer_email',
		'label' => __( 'Customer e-mails', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'usage_limit',
		'label' => __( 'Usage Limit', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'expiry_date',
		'label' => __( 'Expiry Date', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'usage_count',
		'label' => __( 'Usage Count', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'usage_cost',
		'label' => __( 'Usage Cost', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'used_by',
		'label' => __( 'Used By', 'woocommerce-exporter' )
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
function woo_ce_override_coupon_field_labels( $fields = array() ) {

	$labels = woo_ce_get_option( 'coupon_labels', array() );
	if( !empty( $labels ) ) {
		foreach( $fields as $key => $field ) {
			if( isset( $labels[$field['name']] ) )
				$fields[$key]['label'] = $labels[$field['name']];
		}
	}
	return $fields;

}
add_filter( 'woo_ce_coupon_fields', 'woo_ce_override_coupon_field_labels', 11 );

// Returns the export column header label based on an export column slug
function woo_ce_get_coupon_field( $name = null, $format = 'name' ) {

	$output = '';
	if( $name ) {
		$fields = woo_ce_get_coupon_fields();
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

// Returns a list of Coupon IDs
function woo_ce_get_coupons( $args = array() ) {

	global $export;

	$limit_volume = -1;
	$offset = 0;
	$discount_types = false;

	if( $args ) {
		$limit_volume = ( isset( $args['limit_volume'] ) ? $args['limit_volume'] : false );
		$offset = ( isset( $args['offset'] ) ? $args['offset'] : false );
		$orderby = ( isset( $args['coupon_orderby'] ) ? $args['coupon_orderby'] : 'ID' );
		$order = ( isset( $args['coupon_order'] ) ? $args['coupon_order'] : 'ASC' );
		if( !empty( $args['coupon_discount_types'] ) )
			$discount_types = $args['coupon_discount_types'];
	}

	$post_type = 'shop_coupon';
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
	if( $discount_types ) {
		$args['meta_query'] = array();
		$args['meta_query'][] = array(
			'key' => 'discount_type',
			'value' => $discount_types
		);
	}
	$coupons = array();

	// Allow other developers to bake in their own filters
	$args = apply_filters( 'woo_ce_get_coupons_args', $args );

	$coupon_ids = new WP_Query( $args );
	if( $coupon_ids->posts ) {
		foreach( $coupon_ids->posts as $coupon_id )
			$coupons[] = $coupon_id;
		unset( $coupon_ids, $coupon_id );
	}
	return $coupons;

}

function woo_ce_get_coupon_data( $coupon_id = 0, $args = array() ) {

	global $export;

	$coupon = get_post( $coupon_id );

	$coupon->coupon_code = $coupon->post_title;
	$coupon->discount_type = woo_ce_format_discount_type( get_post_meta( $coupon->ID, 'discount_type', true ) );
	$coupon->coupon_description = $coupon->post_excerpt;
	$coupon->coupon_amount = get_post_meta( $coupon->ID, 'coupon_amount', true );
	$coupon->individual_use = woo_ce_format_switch( get_post_meta( $coupon->ID, 'individual_use', true ) );
	$coupon->apply_before_tax = woo_ce_format_switch( get_post_meta( $coupon->ID, 'apply_before_tax', true ) );
	$coupon->exclude_sale_items = woo_ce_format_switch( get_post_meta( $coupon->ID, 'exclude_sale_items', true ) );
	$coupon->minimum_amount = get_post_meta( $coupon->ID, 'minimum_amount', true );
	$coupon->maximum_amount = get_post_meta( $coupon->ID, 'maximum_amount', true );
	$coupon->product_ids = woo_ce_convert_product_ids( get_post_meta( $coupon->ID, 'product_ids', true ) );
	$coupon->exclude_product_ids = woo_ce_convert_product_ids( get_post_meta( $coupon->ID, 'exclude_product_ids', true ) );
	$coupon->product_categories = woo_ce_convert_product_ids( get_post_meta( $coupon->ID, 'product_categories', true ) );
	$coupon->exclude_product_categories = woo_ce_convert_product_ids( get_post_meta( $coupon->ID, 'exclude_product_categories', true ) );
	$coupon->customer_email = woo_ce_convert_product_ids( get_post_meta( $coupon->ID, 'customer_email', true ) );
	$coupon->usage_limit = get_post_meta( $coupon->ID, 'usage_limit', true );
	$coupon->expiry_date = woo_ce_format_date( get_post_meta( $coupon->ID, 'expiry_date', true ) );
	$coupon->usage_count = get_post_meta( $coupon->ID, 'usage_count', true );
	$coupon->usage_cost = woo_ce_get_coupon_usage_cost( $coupon->coupon_code );
	$coupon->used_by = woo_ce_convert_product_ids( get_post_meta( $coupon->ID, '_used_by', false ) );

	// Allow Plugin/Theme authors to add support for additional Coupon columns
	$coupon = apply_filters( 'woo_ce_coupon_item', $coupon, $coupon_id );

	return $coupon;

}

function woo_ce_export_dataset_override_coupon( $output = null, $export_type = null ) {

	global $export;
	if( $coupons = woo_ce_get_coupons( $export->args ) ) {
		$export->total_rows = count( $coupons );
		// XML, RSS export
		if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
			if( !empty( $export->fields ) ) {
				foreach( $coupons as $coupon ) {
					if( $export->export_format == 'xml' )
						$child = $output->addChild( apply_filters( 'woo_ce_export_xml_coupon_node', sanitize_key( $export_type ) ) );
					else if( $export->export_format == 'rss' )
						$child = $output->addChild( 'item' );
					$child->addAttribute( 'id', ( isset( $coupon ) ? $coupon : '' ) );
					$coupon = woo_ce_get_coupon_data( $coupon, $export->args, array_keys( $export->fields ) );
					foreach( array_keys( $export->fields ) as $key => $field ) {
						if( isset( $coupon->$field ) ) {
							if( !is_array( $field ) ) {
								if( woo_ce_is_xml_cdata( $coupon->$field ) )
									$child->addChild( sanitize_key( $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $coupon->$field ) ) );
								else
									$child->addChild( sanitize_key( $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $coupon->$field ) ) );
							}
						}
					}
				}
			}
		} else {
			// PHPExcel export
			foreach( $coupons as $key => $coupon )
				$coupons[$key] = woo_ce_get_coupon_data( $coupon, $export->args, array_keys( $export->fields ) );
			$output = $coupons;
		}
		unset( $coupons, $coupon );
	}
	return $output;

}

function woo_ce_export_dataset_multisite_override_coupon( $output = null, $export_type = null ) {

	global $export;

	$sites = wp_get_sites();
	if( !empty( $sites ) ) {
		foreach( $sites as $site ) {
			switch_to_blog( $site['blog_id'] );
			if( $coupons = woo_ce_get_coupons( $export->args ) ) {
				$export->total_rows = count( $coupons );
				// XML, RSS export
				if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
					if( !empty( $export->fields ) ) {
						foreach( $coupons as $coupon ) {
							if( $export->export_format == 'xml' )
								$child = $output->addChild( apply_filters( 'woo_ce_export_xml_coupon_node', sanitize_key( $export_type ) ) );
							else if( $export->export_format == 'rss' )
								$child = $output->addChild( 'item' );
							$child->addAttribute( 'id', ( isset( $coupon ) ? $coupon : '' ) );
							$coupon = woo_ce_get_coupon_data( $coupon, $export->args, array_keys( $export->fields ) );
							foreach( array_keys( $export->fields ) as $key => $field ) {
								if( isset( $coupon->$field ) ) {
									if( !is_array( $field ) ) {
										if( woo_ce_is_xml_cdata( $coupon->$field ) )
											$child->addChild( sanitize_key( $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $coupon->$field ) ) );
										else
											$child->addChild( sanitize_key( $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $coupon->$field ) ) );
									}
								}
							}
						}
					}
				} else {
					// PHPExcel export
					foreach( $coupons as $key => $coupon )
						$coupons[$key] = woo_ce_get_coupon_data( $coupon, $export->args, array_keys( $export->fields ) );
					if( is_null( $output ) )
						$output = $coupons;
					else
					$output = array_merge( $output, $coupons );
				}
				unset( $coupons, $coupon );
			}
			restore_current_blog();
		}
	}
	return $output;

}


function woo_ce_get_coupon_usage_cost( $coupon_code = '' ) {

	global $wpdb;

	$count = 0;
	if( $coupon_code ) {
		$order_item_type = 'coupon';
		$meta_key = 'discount_amount';
		$count_sql = $wpdb->prepare( "SELECT SUM(order_itemmeta.meta_value) FROM `" . $wpdb->prefix . "woocommerce_order_items` as order_items, `" . $wpdb->prefix . "woocommerce_order_itemmeta` as order_itemmeta WHERE order_items.order_item_id = order_itemmeta.order_item_id AND order_items.order_item_type = %s AND order_items.order_item_name = %s AND order_itemmeta.meta_key = %s LIMIT 1", $order_item_type, $coupon_code, $meta_key );
		$count = $wpdb->get_var( $count_sql );
	}
	return $count;

}

function woo_ce_get_coupon_code_usage( $coupon_code = '' ) {

	global $wpdb;

	$count = 0;
	if( $coupon_code ) {
		$order_item_type = 'coupon';
		$count_sql = $wpdb->prepare( "SELECT COUNT('order_item_id') FROM `" . $wpdb->prefix . "woocommerce_order_items` WHERE `order_item_type` = %s AND `order_item_name` = %s", $order_item_type, $coupon_code );
		$count = $wpdb->get_var( $count_sql );
	}
	return $count;

}

function woo_ce_get_coupon_discount_types() {

	// Check if wc_get_coupon_types() is available
	if( function_exists( 'wc_get_coupon_types' ) ) {
		$discount_types = wc_get_coupon_types();
	} else {
		$discount_types = apply_filters( 'woocommerce_coupon_discount_types', array(
			'fixed_cart' => __( 'Cart Discount', 'woocommerce' ),
			'percent' => __( 'Cart % Discount', 'woocommerce' ),
			'fixed_product' => __( 'Product Discount', 'woocommerce' ),
			'percent_product' => __( 'Product % Discount', 'woocommerce' )
		) );
	}
	return $discount_types;

}

// Format the discount type, defaults to Cart Discount
function woo_ce_format_discount_type( $discount_type = '' ) {

	$output = $discount_type;
	switch( $discount_type ) {

		default:
		case 'fixed_cart':
			$output = __( 'Cart Discount', 'woocommerce-exporter' );
			break;

		case 'percent':
			$output = __( 'Cart % Discount', 'woocommerce-exporter' );
			break;

		case 'fixed_product':
			$output = __( 'Product Discount', 'woocommerce-exporter' );
			break;

		case 'percent_product':
			$output = __( 'Product % Discount', 'woocommerce-exporter' );
			break;

	}
	return $output;

}
?>