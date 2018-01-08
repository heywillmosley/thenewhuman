<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	if( !function_exists( 'woo_ce_get_export_type_product_count' ) ) {
		function woo_ce_get_export_type_product_count() {

			$count = 0;
			$post_type = apply_filters( 'woo_ce_get_export_type_product_count_post_types', array( 'product', 'product_variation' ) );

			// Override for WordPress MultiSite
			if( woo_ce_is_network_admin() ) {
				$sites = wp_get_sites();
				foreach( $sites as $site ) {
					switch_to_blog( $site['blog_id'] );
					$args = array(
						'post_type' => $post_type,
						'posts_per_page' => 1,
						'fields' => 'ids',
						'suppress_filters' => true
					);
					$count_query = new WP_Query( $args );
					$count += $count_query->found_posts;
					restore_current_blog();
				}
				return $count;
			}

			// Check if the existing Transient exists
			$cached = get_transient( WOO_CD_PREFIX . '_product_count' );
			if( $cached == false ) {
				$args = array(
					'post_type' => $post_type,
					'posts_per_page' => 1,
					'fields' => 'ids',
					'suppress_filters' => true
				);
				$count_query = new WP_Query( $args );
				$count = $count_query->found_posts;
				set_transient( WOO_CD_PREFIX . '_product_count', $count, HOUR_IN_SECONDS );
			} else {
				$count = $cached;
			}
			return $count;

		}
	}

	// HTML template for Filter Products by Product Category widget on Store Exporter screen
	function woo_ce_products_filter_by_product_category() {

		$args = array(
			'hide_empty' => 1
		);
		$product_categories = woo_ce_get_product_categories( $args );

		ob_start(); ?>
<p><label><input type="checkbox" id="products-filters-categories" /> <?php _e( 'Filter Products by Product Category', 'woocommerce-exporter' ); ?></label></p>
<div id="export-products-filters-categories" class="separator">
	<ul>
		<li>
<?php if( !empty( $product_categories ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Product Category...', 'woocommerce-exporter' ); ?>" name="product_filter_category[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $product_categories as $product_category ) { ?>
				<option value="<?php echo $product_category->term_id; ?>"<?php disabled( $product_category->count, 0 ); ?>><?php echo woo_ce_format_product_category_label( $product_category->name, $product_category->parent_name ); ?> (<?php printf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_category->term_id ); ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Product Categories were found.', 'woocommerce-exporter' ); ?></li>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Product Categories you want to filter exported Products by. Product Categories not assigned to Products are hidden from view. Default is to include all Product Categories.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-products-filters-categories -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Products by Product Tag widget on Store Exporter screen
	function woo_ce_products_filter_by_product_tag() {

		$args = array(
			'hide_empty' => 1
		);
		$product_tags = woo_ce_get_product_tags( $args );

		ob_start(); ?>
<p><label><input type="checkbox" id="products-filters-tags" /> <?php _e( 'Filter Products by Product Tag', 'woocommerce-exporter' ); ?></label></p>
<div id="export-products-filters-tags" class="separator">
	<ul>
		<li>
<?php if( !empty( $product_tags ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Product Tag...', 'woocommerce-exporter' ); ?>" name="product_filter_tag[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $product_tags as $product_tag ) { ?>
				<option value="<?php echo $product_tag->term_id; ?>"<?php disabled( $product_tag->count, 0 ); ?>><?php echo $product_tag->name; ?> (<?php printf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_tag->term_id ); ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Product Tags were found.', 'woocommerce-exporter' ); ?></li>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Product Tags you want to filter exported Products by. Product Tags not assigned to Products are hidden from view. Default is to include all Product Tags.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-products-filters-tags -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Products by Product Status widget on Store Exporter screen
	function woo_ce_products_filter_by_product_status() {

		$product_stati = get_post_statuses();
		if( !isset( $product_stati['trash'] ) )
			$product_stati['trash'] = __( 'Trash', 'woocommerce-exporter' );
/*
		$post_type = 'product';
		$args = array(
			'post_type' => $post_type
		);
		$product_stati = get_post_stati( $args );
*/
		// Allow Plugin/Theme authors to add support for custom Product Post Stati
		$product_stati = apply_filters( 'woo_ce_products_filter_post_stati', $product_stati );

		$types = woo_ce_get_option( 'product_status', array() );

		ob_start(); ?>
<p><label><input type="checkbox" id="products-filters-status"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Products by Product Status', 'woocommerce-exporter' ); ?></label></p>
<div id="export-products-filters-status" class="separator">
	<ul>
		<li>
<?php if( !empty( $product_stati ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Product Status...', 'woocommerce-exporter' ); ?>" name="product_filter_status[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $product_stati as $key => $product_status ) { ?>
				<option value="<?php echo $key; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $key, $types, false ), true ) : '' ); ?>><?php echo $product_status; ?></option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Product Status were found.', 'woocommerce-exporter' ); ?></li>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Product Status options you want to filter exported Products by. Default is to include all Product Status options.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-products-filters-status -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Products by Product Type widget on Store Exporter screen
	function woo_ce_products_filter_by_product_type() {

		$product_types = woo_ce_get_product_types();
		$types = woo_ce_get_option( 'product_type', array() );

		ob_start(); ?>
<p><label><input type="checkbox" id="products-filters-type"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Products by Product Type', 'woocommerce-exporter' ); ?></label></p>
<div id="export-products-filters-type" class="separator">
	<ul>
		<li>
<?php if( !empty( $product_types ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Product Type...', 'woocommerce-exporter' ); ?>" name="product_filter_type[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $product_types as $key => $product_type ) { ?>
				<option value="<?php echo $key; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $key, $types, false ), true ) : '' ); ?><?php disabled( $product_type['count'], 0 ); ?>><?php echo woo_ce_format_product_type( $product_type['name'] ); ?> (<?php echo $product_type['count']; ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Product Types were found.', 'woocommerce-exporter' ); ?></li>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Product Type\'s you want to filter exported Products by. Default is to include all Product Types except Variations.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-products-filters-type -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Products by Product widget on Store Exporter screen
	function woo_ce_products_filter_by_sku() {

		if( apply_filters( 'woo_ce_override_products_filter_by_sku', true ) == false )
			return;

		$args = array();
		$products = woo_ce_get_products( $args );
		add_filter( 'the_title', 'woo_ce_get_product_title_sku', 10, 2 );

		ob_start(); ?>
<p><label><input type="checkbox" id="products-filters-sku" /> <?php _e( 'Filter Products by Product', 'woocommerce-exporter' ); ?></label></p>
<div id="export-products-filters-sku" class="separator">
	<ul>
		<li>
<?php if( wp_script_is( 'wc-enhanced-select', 'enqueued' ) ) { ?>
			<p><input data-placeholder="<?php _e( 'Search for a Product&hellip;', 'woocommerce-exporter' ); ?>" type="hidden" id="product_filter_sku" name="product_filter_sku[]" class="multiselect wc-product-search" data-multiple="true" style="width:95;" data-action="woocommerce_json_search_products_and_variations" /></p>
<?php } else { ?>
	<?php if( !empty( $products ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Product...', 'woocommerce-exporter' ); ?>" name="product_filter_sku[]" multiple class="chzn-select" style="width:95%;">
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
	<p class="description"><?php _e( 'Select the Product\'s you want to filter exported Products by. Multiple Products can be selected. Default is to include all Products.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-products-filters-sku -->
<?php
		ob_end_flush();
		remove_filter( 'the_title', 'woo_ce_get_product_title_sku' );

	}

	// HTML template for Filter Products by Stock Status widget on Store Exporter screen
	function woo_ce_products_filter_by_stock_status() {

		$types = woo_ce_get_option( 'product_stock', false );

		ob_start(); ?>
<p><label><input type="checkbox" id="products-filters-stock"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Products by Stock Status', 'woocommerce-exporter' ); ?></label></p>
<div id="export-products-filters-stock" class="separator">
	<ul>
		<li value=""><label><input type="radio" name="product_filter_stock" value=""<?php checked( $types, false ); ?> /><?php _e( 'Include both', 'woocommerce-exporter' ); ?></label></li>
		<li value="instock"><label><input type="radio" name="product_filter_stock" value="instock"<?php checked( $types, 'instock' ); ?> /><?php _e( 'In stock', 'woocommerce-exporter' ); ?></label></li>
		<li value="outofstock"><label><input type="radio" name="product_filter_stock" value="outofstock"<?php checked( $types, 'outofstock' ); ?> /><?php _e( 'Out of stock', 'woocommerce-exporter' ); ?></label></li>
	</ul>
	<p class="description"><?php _e( 'Select the Stock Status\'s you want to filter exported Products by. Default is to include all Stock Status\'s.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-products-filters-stock -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Products by Featured widget on Store Exporter screen
	function woo_ce_products_filter_by_featured() {

		$types = woo_ce_get_option( 'product_featured', false );

		ob_start(); ?>
<p><label><input type="checkbox" id="products-filters-featured"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Products by Featured', 'woocommerce-exporter' ); ?></label></p>
<div id="export-products-filters-featured" class="separator">
	<ul>
		<li value=""><label><input type="radio" name="product_filter_featured" value=""<?php checked( $types, false ); ?> /><?php _e( 'Include both', 'woocommerce-exporter' ); ?></label></li>
		<li value="yes"><label><input type="radio" name="product_filter_featured" value="yes"<?php checked( $types, 'yes' ); ?> /><?php _e( 'Featured', 'woocommerce-exporter' ); ?></label></li>
		<li value="no"><label><input type="radio" name="product_filter_featured" value="no"<?php checked( $types, 'no' ); ?> /><?php _e( 'Un-featured', 'woocommerce-exporter' ); ?></label></li>
	</ul>
	<p class="description"><?php _e( 'Select the Featured state you want to filter exported Products by. Default is to include all Products.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-products-filters-featured -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Products by Shipping Classes widget on Store Exporter screen
	function woo_ce_products_filter_by_shipping_class() {

		$shipping_classes = woo_ce_get_shipping_classes();

		ob_start(); ?>
<p><label><input type="checkbox" id="products-filters-shipping_class" /> <?php _e( 'Filter Products by Shipping Class', 'woocommerce-exporter' ); ?></label></p>
<div id="export-products-filters-shipping_class" class="separator">
	<ul>
		<li>
<?php if( !empty( $shipping_classes ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Shipping Class...', 'woocommerce-exporter' ); ?>" name="product_filter_shipping_class[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $shipping_classes as $shipping_class ) { ?>
				<option value="<?php echo $shipping_class->term_id; ?>"<?php disabled( $shipping_class->count, 0 ); ?>><?php echo $shipping_class->name; ?> (<?php echo $shipping_class->count; ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Shipping Classes were found.', 'woocommerce-exporter' ); ?></li>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Shipping Class you want to filter exported Products by. Default is to include all Products.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-products-filters-shipping_class -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Products by Date Modified widget on Store Exporter screen
	function woo_ce_products_filter_by_date_modified() {

		$today = date( 'l', current_time( 'timestamp' ) );
		$yesterday = date( 'l', strtotime( '-1 days', current_time( 'timestamp' ) ) );
		$date_format = 'd/m/Y';
		$types = woo_ce_get_option( 'product_dates_filter' );
		$product_dates_from = woo_ce_get_option( 'product_dates_from' );
		$product_dates_to = woo_ce_get_option( 'product_dates_to' );
		// Check if the Product Modified Date To/From have been saved
		if( empty( $product_dates_from ) || empty( $product_dates_to ) ) {
			if( empty( $product_dates_from ) )
				$product_dates_from = woo_ce_get_product_first_date();
			if( empty( $product_dates_to ) )
				$product_dates_to = date( $date_format );
		}

		ob_start(); ?>
<p><label><input type="checkbox" id="products-filters-date_modified"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Products by Date Modified', 'woocommerce-exporter' ); ?></label></p>
<div id="export-products-filters-date_modified" class="separator">
	<ul>
		<li>
			<label><input type="radio" name="product_dates_filter" value=""<?php checked( $types, false ); ?> /> <?php _e( 'All dates', 'woocommerce-exporter' ); ?> (<?php echo $product_dates_from; ?> - <?php echo $product_dates_to; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="product_dates_filter" value="today"<?php checked( $types, 'today' ); ?> /> <?php _e( 'Today', 'woocommerce-exporter' ); ?> (<?php echo $today; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="product_dates_filter" value="yesterday"<?php checked( $types, 'yesterday' ); ?> /> <?php _e( 'Yesterday', 'woocommerce-exporter' ); ?> (<?php echo $yesterday; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="product_dates_filter" value="manual"<?php checked( $types, 'manual' ); ?> /> <?php _e( 'Fixed date', 'woocommerce-exporter' ); ?></label>
			<div style="margin-top:0.2em;">
				<input type="text" size="10" maxlength="10" id="product_dates_from" name="product_dates_from" value="<?php echo esc_attr( $product_dates_from ); ?>" class="text code datepicker product_export" /> to <input type="text" size="10" maxlength="10" id="product_dates_to" name="product_dates_to" value="<?php echo esc_attr( $product_dates_to ); ?>" class="text code datepicker product_export" />
				<p class="description"><?php _e( 'Filter the dates of Products to be included in the export. Default is the date of the first Product Modified to today in the date format <code>DD/MM/YYYY</code>.', 'woocommerce-exporter' ); ?></p>
			</div>
		</li>
	</ul>
</div>
<!-- #export-products-filters-date_modified -->
<?php
		ob_end_flush();

	}

	// HTML template for jump link to Custom Product Fields within Order Options on Store Exporter screen
	function woo_ce_products_custom_fields_link() {

		ob_start(); ?>
<div id="export-products-custom-fields-link">
	<p><a href="#export-products-custom-fields"><?php _e( 'Manage Custom Product Fields', 'woocommerce-exporter' ); ?></a></p>
</div>
<!-- #export-products-custom-fields-link -->
<?php
		ob_end_flush();

	}

	// HTML template for Product Sorting widget on Store Exporter screen
	function woo_ce_product_sorting() {

		$product_orderby = woo_ce_get_option( 'product_orderby', 'ID' );
		$product_order = woo_ce_get_option( 'product_order', 'ASC' );

		ob_start(); ?>
<p><label><?php _e( 'Product Sorting', 'woocommerce-exporter' ); ?></label></p>
<div>
	<select name="product_orderby">
		<option value="ID"<?php selected( 'ID', $product_orderby ); ?>><?php _e( 'Product ID', 'woocommerce-exporter' ); ?></option>
		<option value="title"<?php selected( 'title', $product_orderby ); ?>><?php _e( 'Product Name', 'woocommerce-exporter' ); ?></option>
		<option value="sku"<?php selected( 'sku', $product_orderby ); ?>><?php _e( 'Product SKU', 'woocommerce-exporter' ); ?></option>
		<option value="date"<?php selected( 'date', $product_orderby ); ?>><?php _e( 'Date Created', 'woocommerce-exporter' ); ?></option>
		<option value="modified"<?php selected( 'modified', $product_orderby ); ?>><?php _e( 'Date Modified', 'woocommerce-exporter' ); ?></option>
		<option value="rand"<?php selected( 'rand', $product_orderby ); ?>><?php _e( 'Random', 'woocommerce-exporter' ); ?></option>
		<option value="menu_order"<?php selected( 'menu_order', $product_orderby ); ?>><?php _e( 'Sort Order', 'woocommerce-exporter' ); ?></option>
	</select>
	<select name="product_order">
		<option value="ASC"<?php selected( 'ASC', $product_order ); ?>><?php _e( 'Ascending', 'woocommerce-exporter' ); ?></option>
		<option value="DESC"<?php selected( 'DESC', $product_order ); ?>><?php _e( 'Descending', 'woocommerce-exporter' ); ?></option>
	</select>
	<p class="description"><?php _e( 'Select the sorting of Products within the exported file. By default this is set to export Products by Product ID in Desending order.', 'woocommerce-exporter' ); ?></p>
</div>
<?php
		ob_end_flush();

	}

	// HTML template for Up-sells formatting on Store Exporter screen
	function woo_ce_products_upsells_formatting() {

		$upsell_formatting = woo_ce_get_option( 'upsell_formatting', 1 );

		ob_start(); ?>
<tr class="export-options product-options">
	<th><label for=""><?php _e( 'Up-sells formatting', 'woocommerce-exporter' ); ?></label></th>
	<td>
		<label><input type="radio" name="product_upsell_formatting" value="0"<?php checked( $upsell_formatting, 0 ); ?> />&nbsp;<?php _e( 'Export Up-Sells as Product ID', 'woocommerce-exporter' ); ?></label><br />
		<label><input type="radio" name="product_upsell_formatting" value="1"<?php checked( $upsell_formatting, 1 ); ?> />&nbsp;<?php _e( 'Export Up-Sells as Product SKU', 'woocommerce-exporter' ); ?></label>
		<p class="description"><?php _e( 'Choose the up-sell formatting that is accepted by your WooCommerce import Plugin (e.g. Product Importer Deluxe, Product Import Suite, etc.).', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>

<?php
		ob_end_flush();

	}

	// HTML template for Cross-sells formatting on Store Exporter screen
	function woo_ce_products_crosssells_formatting() {

		$crosssell_formatting = woo_ce_get_option( 'crosssell_formatting', 1 );

		ob_start(); ?>
<tr class="export-options product-options">
	<th><label for=""><?php _e( 'Cross-sells formatting', 'woocommerce-exporter' ); ?></label></th>
	<td>
		<label><input type="radio" name="product_crosssell_formatting" value="0"<?php checked( $crosssell_formatting, 0 ); ?> />&nbsp;<?php _e( 'Export Cross-Sells as Product ID', 'woocommerce-exporter' ); ?></label><br />
		<label><input type="radio" name="product_crosssell_formatting" value="1"<?php checked( $crosssell_formatting, 1 ); ?> />&nbsp;<?php _e( 'Export Cross-Sells as Product SKU', 'woocommerce-exporter' ); ?></label>
		<p class="description"><?php _e( 'Choose the cross-sell formatting that is accepted by your WooCommerce import Plugin (e.g. Product Importer Deluxe, Product Import Suite, etc.).', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>

<?php
		ob_end_flush();

	}

	// HTML template for Variation formatting on Store Exporter screen
	function woo_ce_products_variation_formatting() {

		$variation_formatting = woo_ce_get_option( 'variation_formatting', 0 );

		ob_start(); ?>
<tr class="export-options product-options">
	<th><label for=""><?php _e( 'Variation formatting', 'woocommerce-exporter' ); ?></label></th>
	<td>
		<label><input type="radio" name="variation_formatting" value="0"<?php checked( $variation_formatting, 0 ); ?> />&nbsp;<?php _e( 'Leave empty Variant details intact', 'woocommerce-exporter' ); ?></label><br />
		<label><input type="radio" name="variation_formatting" value="1"<?php checked( $variation_formatting, 1 ); ?> />&nbsp;<?php _e( 'Default Variant details to Parent Product', 'woocommerce-exporter' ); ?></label>
		<p class="description"><?php _e( 'Choose the default formatting rule that is applied to Product Variations.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>

<?php
		ob_end_flush();

	}

	function woo_ce_products_description_excerpt_formatting() {

		$description_excerpt_formatting = woo_ce_get_option( 'description_excerpt_formatting', 0 );

		ob_start(); ?>
<tr class="export-options product-options category-options tag-options order-options">
	<th><label for=""><?php _e( 'Description/Excerpt formatting', 'woocommerce-exporter' ); ?></label></th>
	<td>
		<label><input type="radio" name="description_excerpt_formatting" value="0"<?php checked( $description_excerpt_formatting, 0 ); ?> />&nbsp;<?php _e( 'Leave HTML tags from Description/Excerpt intact', 'woocommerce-exporter' ); ?></label><br />
		<label><input type="radio" name="description_excerpt_formatting" value="1"<?php checked( $description_excerpt_formatting, 1 ); ?> />&nbsp;<?php _e( 'Strip HTML tags from Description/Excerpt', 'woocommerce-exporter' ); ?></label>
		<p class="description"><?php _e( 'Choose the HTML tag formatting rule that is applied to the Description/Excerpt within the Product, Category, Tag, Brand and Order export.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<?php
		ob_end_flush();

	}

	// HTML template for Custom Products widget on Store Exporter screen
	function woo_ce_products_custom_fields() {

		if( $custom_products = woo_ce_get_option( 'custom_products', '' ) )
			$custom_products = implode( "\n", $custom_products );
		if( $custom_attributes = woo_ce_get_option( 'custom_attributes', '' ) )
			$custom_attributes = implode( "\n", $custom_attributes );

		$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

		ob_start(); ?>
<form method="post" id="export-products-custom-fields" class="export-options product-options">
	<div id="poststuff">

		<div class="postbox" id="export-options product-options">
			<h3 class="hndle"><?php _e( 'Custom Product Fields', 'woocommerce-exporter' ); ?></h3>
			<div class="inside">
				<p class="description"><?php _e( 'To include additional custom Product meta or custom Attributes in the Export Products table above fill the meta text box then click Save Custom Fields. The saved meta will appear as new export fields to be selected from the Product Fields list.', 'woocommerce-exporter' ); ?></p>
				<p class="description"><?php printf( __( 'For more information on exporting custom Product meta and Attributes consult our <a href="%s" target="_blank">online documentation</a>.', 'woocommerce-exporter' ), $troubleshooting_url ); ?></p>
				<table class="form-table">

					<tr>
						<th>
							<label><?php _e( 'Product meta', 'woocommerce-exporter' ); ?></label>
						</th>
						<td>
							<textarea name="custom_products" rows="5" cols="70"><?php echo esc_textarea( $custom_products ); ?></textarea>
							<p class="description"><?php _e( 'Include additional custom Product meta in your export file by adding each custom Product meta name to a new line above.<br />For example: <code>Customer UA</code> (new line) <code>Customer IP Address</code>', 'woocommerce-exporter' ); ?></p>
						</td>
					</tr>

					<tr>
						<th>
							<label><?php _e( 'Custom attribute', 'woocommerce-exporter' ); ?></label>
						</th>
						<td>
							<textarea name="custom_attributes" rows="5" cols="70"><?php echo esc_textarea( $custom_attributes ); ?></textarea>
							<p class="description"><?php _e( 'Include custom Attributes in your export file by adding each custom Attribute name - typically in lowercase and replacing spaces with dashes, e.g. Size becomes size or Sample Attribute becomes sample-attribute - to a new line above.<br />For example: <code>condition</code> (new line) <code>colour</code>', 'woocommerce-exporter' ); ?></p>
						</td>
					</tr>

					<?php do_action( 'woo_ce_products_custom_fields' ); ?>

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
<!-- #export-products-custom-fields -->
<?php
		ob_end_flush();

	}

	function woo_ce_export_options_featured_image_formatting() {

		$product_image_formatting = woo_ce_get_option( 'product_image_formatting', 1 );

		ob_start(); ?>
<tr class="export-options product-options">
	<th><label for=""><?php _e( 'Product image formatting', 'woocommerce-exporter' ); ?></label></th>
	<td>
		<label><input type="radio" name="product_image_formatting" value="0"<?php checked( $product_image_formatting, 0 ); ?> />&nbsp;<?php _e( 'Export Product Image as Attachment ID', 'woocommerce-exporter' ); ?></label><br />
		<label><input type="radio" name="product_image_formatting" value="1"<?php checked( $product_image_formatting, 1 ); ?> />&nbsp;<?php _e( 'Export Product Image as Image URL', 'woocommerce-exporter' ); ?></label><br />
		<label><input type="radio" name="product_image_formatting" value="2"<?php checked( $product_image_formatting, 2 ); ?> />&nbsp;<?php _e( 'Export Product Image as Image filepath', 'woocommerce-exporter' ); ?></label>
		<p class="description"><?php _e( 'Choose the featured image formatting that is accepted by your WooCommerce import Plugin (e.g. Product Importer Deluxe, Product Import Suite, etc.).', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<?php
		ob_end_flush();

	}

	function woo_ce_export_options_product_gallery_formatting() {

		$gallery_formatting = woo_ce_get_option( 'gallery_formatting', 1 );
		$gallery_unique = woo_ce_get_option( 'gallery_unique', 0 );
		$max_size = woo_ce_get_option( 'max_product_gallery', 3 );

		ob_start(); ?>
<tr class="export-options product-options">
	<th><label for=""><?php _e( 'Product gallery formatting', 'woocommerce-exporter' ); ?></label></th>
	<td>
		<label><input type="radio" name="product_gallery_formatting" value="0"<?php checked( $gallery_formatting, 0 ); ?> />&nbsp;<?php _e( 'Export Product Gallery as Attachment ID', 'woocommerce-exporter' ); ?></label><br />
		<label><input type="radio" name="product_gallery_formatting" value="1"<?php checked( $gallery_formatting, 1 ); ?> />&nbsp;<?php _e( 'Export Product Gallery as Image URL', 'woocommerce-exporter' ); ?></label><br />
		<label><input type="radio" name="product_gallery_formatting" value="2"<?php checked( $gallery_formatting, 2 ); ?> />&nbsp;<?php _e( 'Export Product Gallery as Image filepath', 'woocommerce-exporter' ); ?></label>
		<hr />
		<label><input type="radio" name="product_gallery_unique" value="0"<?php checked( $gallery_unique, 0 ); ?> />&nbsp;<?php _e( 'Export Product Gallery as a single combined image cell', 'woocommerce-exporter' ); ?></label><br />
		<label><input type="radio" name="product_gallery_unique" value="1"<?php checked( $gallery_unique, 1 ); ?> />&nbsp;<?php _e( 'Export Product Gallery as individual image cells', 'woocommerce-exporter' ); ?></label>
		<p class="description"><?php _e( 'Choose the product gallery formatting that is accepted by your WooCommerce import Plugin (e.g. Product Importer Deluxe, Product Import Suite, etc.).', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<tr id="max_product_gallery_option" class="export-options product-options">
	<th><label for=""><?php _e( 'Max unique Product Gallery images', 'woocommerce-exporter' ); ?></label></th>
	<td>
		<input type="text" id="max_product_gallery" name="max_product_gallery" size="3" class="text" value="<?php echo esc_attr( $max_size ); ?>" />
		<p class="description"><?php _e( 'Manage the number of Product Gallery colums displayed when the \'Export Product Gallery as individual image cells\' Product gallery formatting option is selected.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<?php
		ob_end_flush();

	}

	// Returns date of first Product Date Modified, any status
	function woo_ce_get_product_first_date( $date_format = 'd/m/Y' ) {

		$output = date( $date_format, mktime( 0, 0, 0, date( 'n' ), 1 ) );

		$post_type = 'product';
		$args = array(
			'post_type' => $post_type,
			'orderby' => 'post_date',
			'order' => 'ASC',
			'numberposts' => 1,
			'post_status' => 'any'
		);
		$products = get_posts( $args );
		if( !empty( $products ) ) {
			$output = date( $date_format, strtotime( $products[0]->post_date ) );
			unset( $products );
		}
		return $output;

	}

	function woo_ce_product_dataset_args( $args, $export_type = '' ) {

		// Check if we're dealing with the Product Export Type
		if( $export_type <> 'product' )
			return $args;

		// Merge in the form data for this dataset
		$defaults = array(
			'product_categories' => ( isset( $_POST['product_filter_category'] ) ? woo_ce_format_product_filters( array_map( 'absint', $_POST['product_filter_category'] ) ) : false ),
			'product_tags' => ( isset( $_POST['product_filter_tag'] ) ? woo_ce_format_product_filters( array_map( 'absint', $_POST['product_filter_tag'] ) ) : false ),
			'product_brands' => ( isset( $_POST['product_filter_brand'] ) ? woo_ce_format_product_filters( array_map( 'absint', $_POST['product_filter_brand'] ) ) : false ),
			'product_vendors' => ( isset( $_POST['product_filter_vendor'] ) ? woo_ce_format_product_filters( array_map( 'absint', $_POST['product_filter_vendor'] ) ) : false ),
			'product_status' => ( isset( $_POST['product_filter_status'] ) ? woo_ce_format_product_filters( array_map( 'sanitize_text_field', $_POST['product_filter_status'] ) ) : false ),
			'product_type' => ( isset( $_POST['product_filter_type'] ) ? woo_ce_format_product_filters( array_map( 'sanitize_text_field', $_POST['product_filter_type'] ) ) : false ),
			'product_sku' => ( isset( $_POST['product_filter_sku'] ) ? woo_ce_format_product_filters( array_map( 'sanitize_text_field', $_POST['product_filter_sku'] ) ) : false ),
			'product_stock' => ( isset( $_POST['product_filter_stock'] ) ? sanitize_text_field( $_POST['product_filter_stock'] ) : false ),
			'product_featured' => ( isset( $_POST['product_filter_featured'] ) ? sanitize_text_field( $_POST['product_filter_featured'] ) : false ),
			'product_shipping_class' => ( isset( $_POST['product_filter_shipping_class'] ) ? woo_ce_format_product_filters( $_POST['product_filter_shipping_class'] ) : false ),
			'product_language' => ( isset( $_POST['product_filter_language'] ) ? array_map( 'sanitize_text_field', $_POST['product_filter_language'] ) : false ),
			'product_dates_filter' => ( isset( $_POST['product_dates_filter'] ) ? sanitize_text_field( $_POST['product_dates_filter'] ) : false ),
			'product_dates_from' => ( isset( $_POST['product_dates_from'] ) ? woo_ce_format_order_date( sanitize_text_field( $_POST['product_dates_from'] ) ) : '' ),
			'product_dates_to' => ( isset( $_POST['product_dates_to'] ) ? woo_ce_format_order_date( sanitize_text_field( $_POST['product_dates_to'] ) ) : '' ),
			'upsell_formatting' => ( isset( $_POST['product_upsell_formatting'] ) ? absint( $_POST['product_upsell_formatting'] ) : false ),
			'crosssell_formatting' => ( isset( $_POST['product_crosssell_formatting'] ) ? absint( $_POST['product_crosssell_formatting'] ) : false ),
			'variation_formatting' => ( isset( $_POST['variation_formatting'] ) ? absint( $_POST['variation_formatting'] ) : false ),
			'product_image_formatting' => ( isset( $_POST['product_image_formatting'] ) ? absint( $_POST['product_image_formatting'] ) : false ),
			'gallery_formatting' => ( isset( $_POST['product_gallery_formatting'] ) ? absint( $_POST['product_gallery_formatting'] ) : false ),
			'gallery_unique' => ( isset( $_POST['product_gallery_unique'] ) ? absint( $_POST['product_gallery_unique'] ) : false ),
			'max_product_gallery' => ( isset( $_POST['max_product_gallery'] ) ? absint( $_POST['max_product_gallery'] ) : false ),
			'product_orderby' => ( isset( $_POST['product_orderby'] ) ? sanitize_text_field( $_POST['product_orderby'] ) : false ),
			'product_order' => ( isset( $_POST['product_order'] ) ? sanitize_text_field( $_POST['product_order'] ) : false )
		);
		$args = wp_parse_args( $args, $defaults );

		// Save dataset export specific options
		// Category
		// Tag
		if( $args['product_brands'] <> woo_ce_get_option( 'product_brands' ) )
			woo_ce_update_option( 'product_brands', $args['product_brands'] );
		// Vendor
		if( $args['product_status'] <> woo_ce_get_option( 'product_status' ) )
			woo_ce_update_option( 'product_status', $args['product_status'] );
		if( $args['product_type'] <> woo_ce_get_option( 'product_type' ) )
			woo_ce_update_option( 'product_type', $args['product_type'] );
		// SKU
		if( $args['product_stock'] <> woo_ce_get_option( 'product_stock' ) )
			woo_ce_update_option( 'product_stock', $args['product_stock'] );
		if( $args['product_featured'] <> woo_ce_get_option( 'product_featured' ) )
			woo_ce_update_option( 'product_featured', $args['product_featured'] );
		// Shipping Class
		// Language
		if( $args['product_dates_filter'] <> woo_ce_get_option( 'product_dates_filter' ) )
			woo_ce_update_option( 'product_dates_filter', $args['product_dates_filter'] );
		if( $args['product_dates_from'] <> woo_ce_get_option( 'product_dates_from' ) )
			woo_ce_update_option( 'product_dates_from', woo_ce_format_order_date( $args['product_dates_from'], 'save' ) );
		if( $args['product_dates_to'] <> woo_ce_get_option( 'product_dates_to' ) )
			woo_ce_update_option( 'product_dates_to', woo_ce_format_order_date( $args['product_dates_to'], 'save' ) );
		if( $args['upsell_formatting'] <> woo_ce_get_option( 'upsell_formatting' ) )
			woo_ce_update_option( 'upsell_formatting', $args['upsell_formatting'] );
		if( $args['crosssell_formatting'] <> woo_ce_get_option( 'crosssell_formatting' ) )
			woo_ce_update_option( 'crosssell_formatting', $args['crosssell_formatting'] );
		if( $args['variation_formatting'] <> woo_ce_get_option( 'variation_formatting' ) )
			woo_ce_update_option( 'variation_formatting', $args['variation_formatting'] );
		if( $args['product_image_formatting'] <> woo_ce_get_option( 'product_image_formatting' ) )
			woo_ce_update_option( 'product_image_formatting', $args['product_image_formatting'] );
		if( $args['gallery_formatting'] <> woo_ce_get_option( 'gallery_formatting' ) )
			woo_ce_update_option( 'gallery_formatting', $args['gallery_formatting'] );
		if( $args['gallery_unique'] <> woo_ce_get_option( 'gallery_unique' ) )
			woo_ce_update_option( 'gallery_unique', $args['gallery_unique'] );
		if( $args['max_product_gallery'] <> woo_ce_get_option( 'max_product_gallery' ) )
			woo_ce_update_option( 'max_product_gallery', $args['max_product_gallery'] );
		if( $args['product_orderby'] <> woo_ce_get_option( 'product_orderby' ) )
			woo_ce_update_option( 'product_orderby', $args['product_orderby'] );
		if( $args['product_order'] <> woo_ce_get_option( 'product_order' ) )
			woo_ce_update_option( 'product_order', $args['product_order'] );

		return $args;

	}
	add_filter( 'woo_ce_extend_dataset_args', 'woo_ce_product_dataset_args', 10, 2 );

	/* End of: WordPress Administration */

}

function woo_ce_cron_product_dataset_args( $args, $export_type = '', $is_scheduled = 0 ) {

	// Check if we're dealing with the Product Export Type
	if( $export_type <> 'product' )
		return $args;

	$product_filter_category = false;
	$product_filter_tag = false;
	$product_filter_status = false;
	$product_filter_type = false;
	$product_filter_sku = false;
	$product_filter_stock = false;
	$product_filter_featured = false;
	$product_filter_shipping_class = false;
	$product_filter_date = false;
	$product_filter_date_from = false;
	$product_filter_date_to = false;
	$product_format_upsell_formatting = woo_ce_get_option( 'upsell_formatting', 1 );
	$product_format_crosssell_formatting = woo_ce_get_option( 'crosssell_formatting', 1 );
	$product_format_image_formatting = woo_ce_get_option( 'product_image_formatting', 1 );
	$product_format_gallery_formatting = woo_ce_get_option( 'gallery_formatting', 1 );
	$product_format_max_product_gallery = woo_ce_get_option( 'max_product_gallery', 3 );

	if( $is_scheduled ) {
		$scheduled_export = ( $is_scheduled ? absint( get_transient( WOO_CD_PREFIX . '_scheduled_export_id' ) ) : 0 );
		$product_filter_category = get_post_meta( $scheduled_export, '_filter_product_category', true );
		$product_filter_tag = get_post_meta( $scheduled_export, '_filter_product_tag', true );
		$product_filter_status = get_post_meta( $scheduled_export, '_filter_product_status', true );
		$product_filter_type = get_post_meta( $scheduled_export, '_filter_product_type', true );
		$product_filter_sku = get_post_meta( $scheduled_export, '_filter_product_sku', true );
		$product_filter_stock = get_post_meta( $scheduled_export, '_filter_product_stock', true );
		$product_filter_featured = get_post_meta( $scheduled_export, '_filter_product_featured', true );
		$product_filter_shipping_class = get_post_meta( $scheduled_export, '_filter_product_shipping_class', true );
		$product_filter_date = get_post_meta( $scheduled_export, '_filter_product_date', true );
		if( $product_filter_date ) {
			$export->args['product_dates_filter'] = $product_filter_date;
			switch( $product_filter_date ) {

				case 'manual':
					$product_filter_dates_from = get_post_meta( $scheduled_export, '_filter_product_dates_from', true );
					$product_filter_dates_to = get_post_meta( $scheduled_export, '_filter_product_dates_to', true );
					$product_filter_dates_from = ( !empty( $product_filter_dates_from ) ? sanitize_text_field( $product_filter_dates_from ) : false );
					$product_filter_dates_to = ( !empty( $product_filter_dates_to ) ? sanitize_text_field( $product_filter_dates_to ) : false );
					break;

			}
		}
	} else {
		if( isset( $_GET['product_category'] ) ) {
			$product_filter_category = sanitize_text_field( $_GET['product_category'] );
			if( !empty( $product_filter_category ) ) {
				$product_filter_category = explode( ',', $product_filter_category );
				$product_filter_category = array_map( 'absint', (array)$product_filter_category );
			}
		}
		if( isset( $_GET['product_tag'] ) ) {
			$product_filter_tag = sanitize_text_field( $_GET['product_tag'] );
			if( !empty( $product_filter_tag ) ) {
				$product_filter_tag = explode( ',', $product_filter_tag );
				$product_filter_tag = array_map( 'absint', (array)$product_filter_tag );
			}
		}
		$args['product_status'] = ( isset( $_GET['product_status'] ) ? sanitize_text_field( $_GET['product_status'] ) : null );
		if( isset( $_GET['product_type'] ) ) {
			$product_filter_type = sanitize_text_field( $_GET['product_type'] );
			$product_filter_type = explode( ',', $product_filter_type );
		}
		$product_filter_stock = ( isset( $_GET['stock_status'] ) ? sanitize_text_field( $_GET['stock_status'] ) : null );
		$product_filter_featured = ( isset( $_GET['product_featured'] ) ? sanitize_text_field( $_GET['product_featured'] ) : null );
		if( isset( $_GET['shipping_class'] ) ) {
			$product_filter_shipping_class = sanitize_text_field( $_GET['shipping_class'] );
			if( !empty( $product_filter_shipping_class ) ) {
				$product_filter_shipping_class = explode( ',', $product_filter_shipping_class );
			}
		}
		if( isset( $_GET['upsell_formatting'] ) ) {
			$product_format_upsell_formatting = absint( $_GET['upsell_formatting'] );
		}
		if( isset( $_GET['crosssell_formatting'] ) ) {
			$product_format_crosssell_formatting = absint( $_GET['crosssell_formatting'] );
		}
		if( isset( $_GET['product_image_formatting'] ) ) {
			$product_format_image_formatting = absint( $_GET['product_image_formatting'] );
		}
		if( isset( $_GET['gallery_formatting'] ) ) {
			$product_format_gallery_formatting = absint( $_GET['gallery_formatting'] );
		}
		if( isset( $_GET['max_product_gallery'] ) ) {
			$product_format_max_product_gallery = absint( $_GET['max_product_gallery'] );
		}
		$product_filter_post_ids = ( isset( $_GET['product_ids'] ) ? sanitize_text_field( $_GET['product_ids'] ) : null );
		// Override Filter Products by Product ID if a single Product transient is set
		$single_export_product_ids = get_transient( WOO_CD_PREFIX . '_single_export_post_ids' );
		if( $single_export_product_ids != false )
			$product_filter_post_ids = sanitize_text_field( $single_export_product_ids );
		unset( $single_export_product_ids );
	}

	// Merge in the form data for this dataset
	$defaults = array(
		'product_categories' => ( !empty( $product_filter_category ) ? $product_filter_category : false ),
		'product_tags' => ( !empty( $product_filter_tag ) ? $product_filter_tag : false ),
		'product_vendors' => ( !empty( $product_filter_vendor ) ? $product_filter_vendor : false ),
		'product_status' => ( !empty( $product_filter_status ) ? $product_filter_status : false ),
		'product_type' => ( !empty( $product_filter_type ) ? $product_filter_type : false ),
		'product_sku' => ( !empty( $product_filter_sku ) ? (array)$product_filter_sku : array() ),
		'product_stock' => ( !empty( $product_filter_stock ) ? $product_filter_stock : false ),
		'product_featured' => ( !empty( $product_filter_featured ) ? $product_filter_featured : false ),
		'product_ids' => ( !empty( $product_filter_post_ids ) ? $product_filter_post_ids : false ),
		'product_shipping_class' => ( !empty( $product_filter_shipping_class ) ? $product_filter_shipping_class : false ),
		'product_dates_filter' => ( !empty( $product_filter_date ) ? $product_filter_date : false ),
		'product_dates_from' => ( !empty( $product_filter_dates_from ) ? $product_filter_dates_from : false ),
		'product_dates_to' => ( !empty( $product_filter_dates_to ) ? $product_filter_dates_to : false ),
		'product_image_formatting' => $product_format_image_formatting,
		'gallery_formatting' => $product_format_gallery_formatting,
		'gallery_unique' => woo_ce_get_option( 'gallery_unique', 0 ),
		'upsell_formatting' => $product_format_upsell_formatting,
		'crosssell_formatting' => $product_format_crosssell_formatting,
		'max_product_gallery' => $product_format_max_product_gallery
	);
	$args = wp_parse_args( $args, $defaults );

	return $args;

}
add_filter( 'woo_ce_extend_cron_dataset_args', 'woo_ce_cron_product_dataset_args', 10, 3 );

// Returns a list of Product export columns
function woo_ce_get_product_fields( $format = 'full', $post_ID = 0 ) {

	$export_type = 'product';

	$fields = array();
	$fields[] = array(
		'name' => 'parent_id',
		'label' => __( 'Parent ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'parent_sku',
		'label' => __( 'Parent SKU', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'product_id',
		'label' => __( 'Product ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'sku',
		'label' => __( 'Product SKU', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'name',
		'label' => __( 'Product Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'post_title',
		'label' => __( 'Post Title', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'slug',
		'label' => __( 'Slug', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'permalink',
		'label' => __( 'Permalink', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'product_url',
		'label' => __( 'Product URI', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'description',
		'label' => __( 'Description', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'excerpt',
		'label' => __( 'Excerpt', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'post_date',
		'label' => __( 'Product Published', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'post_modified',
		'label' => __( 'Product Modified', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'type',
		'label' => __( 'Type', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'visibility',
		'label' => __( 'Visibility', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'featured',
		'label' => __( 'Featured', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'virtual',
		'label' => __( 'Virtual', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'downloadable',
		'label' => __( 'Downloadable', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'price',
		'label' => __( 'Price', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'sale_price',
		'label' => __( 'Sale Price', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'sale_price_dates_from',
		'label' => __( 'Sale Price Dates From', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'sale_price_dates_to',
		'label' => __( 'Sale Price Dates To', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'weight',
		'label' => __( 'Weight', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'weight_unit',
		'label' => __( 'Weight Unit', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'height',
		'label' => __( 'Height', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'height_unit',
		'label' => __( 'Height Unit', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'width',
		'label' => __( 'Width', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'width_unit',
		'label' => __( 'Width Unit', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'length',
		'label' => __( 'Length', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'length_unit',
		'label' => __( 'Length Unit', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'category',
		'label' => __( 'Category', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'category_level_1',
		'label' => __( 'Category: Level 1', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'category_level_2',
		'label' => __( 'Category: Level 2', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'category_level_3',
		'label' => __( 'Category: Level 3', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'tag',
		'label' => __( 'Tag', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'image',
		'label' => __( 'Featured Image', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'image_thumbnail',
		'label' => __( 'Featured Image Thumbnail', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'image_embed',
		'label' => __( 'Featured Image (Embed)', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'product_gallery',
		'label' => __( 'Product Gallery', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'product_gallery_thumbnail',
		'label' => __( 'Product Gallery Thumbnail', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'product_gallery_embed',
		'label' => __( 'Product Gallery (Embed)', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'tax_status',
		'label' => __( 'Tax Status', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'tax_class',
		'label' => __( 'Tax Class', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_class',
		'label' => __( 'Shipping Class', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'download_file_name',
		'label' => __( 'Download File Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'download_file_path',
		'label' => __( 'Download File URL Path', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'download_limit',
		'label' => __( 'Download Limit', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'download_expiry',
		'label' => __( 'Download Expiry', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'download_type',
		'label' => __( 'Download Type', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'manage_stock',
		'label' => __( 'Manage Stock', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'quantity',
		'label' => __( 'Quantity', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'stock_status',
		'label' => __( 'Stock Status', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'allow_backorders',
		'label' => __( 'Allow Backorders', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'sold_individually',
		'label' => __( 'Sold Individually', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'total_sales',
		'label' => __( 'Total Sales', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'upsell_ids',
		'label' => __( 'Up-Sells', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'crosssell_ids',
		'label' => __( 'Cross-Sells', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'external_url',
		'label' => __( 'External URL', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'button_text',
		'label' => __( 'Button Text', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'purchase_note',
		'label' => __( 'Purchase Note', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'product_status',
		'label' => __( 'Product Status', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'enable_reviews',
		'label' => __( 'Enable Reviews', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'review_count',
		'label' => __( 'Review Count', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'rating_count',
		'label' => __( 'Rating Count', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'average_rating',
		'label' => __( 'Average Rating', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'menu_order',
		'label' => __( 'Sort Order', 'woocommerce-exporter' )
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
function woo_ce_override_product_field_labels( $fields = array() ) {

	$labels = woo_ce_get_option( 'product_labels', array() );
	if( !empty( $labels ) ) {
		foreach( $fields as $key => $field ) {
			if( isset( $labels[$field['name']] ) )
				$fields[$key]['label'] = $labels[$field['name']];
		}
	}
	return $fields;

}
add_filter( 'woo_ce_product_fields', 'woo_ce_override_product_field_labels', 11 );

// Returns the export column header label based on an export column slug
function woo_ce_get_product_field( $name = null, $format = 'name' ) {

	$output = '';
	if( $name ) {
		$fields = woo_ce_get_product_fields();
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

// Returns a list of WooCommerce Products
function woo_ce_get_products( $args = array() ) {

	global $export;

	$limit_volume = -1;
	$offset = 0;

	$product_ids = false;
	$product_categories = false;
	$product_tags = false;
	$product_brands = false;
	$product_vendors = false;
	$product_status = false;
	$product_type = false;
	$product_sku = false;
	$product_stock = false;
	$product_featured = false;
	$product_status = false;
	$product_shipping_class = false;
	$product_language = false;
	$orderby = 'ID';
	$order = 'ASC';
	if( $args ) {
		$product_ids = ( !empty( $args['product_ids'] ) ? $args['product_ids'] : false );
		$limit_volume = ( isset( $args['limit_volume'] ) ? $args['limit_volume'] : false );
		$offset = ( isset( $args['offset'] ) ? $args['offset'] : false );
		if( !empty( $args['product_categories'] ) )
			$product_categories = $args['product_categories'];
		if( !empty( $args['product_tags'] ) )
			$product_tags = $args['product_tags'];
		$product_brands = ( !empty( $args['product_brands'] ) ? $args['product_brands'] : false );
		if( !empty( $args['product_vendors'] ) )
			$product_vendors = $args['product_vendors'];
		if( !empty( $args['product_status'] ) )
			$product_status = $args['product_status'];
		if( !empty( $args['product_type'] ) )
			$product_type = $args['product_type'];
		if( !empty( $args['product_sku'] ) )
			$product_sku = $args['product_sku'];
		if( !empty( $args['product_stock'] ) )
			$product_stock = $args['product_stock'];
		if( !empty( $args['product_featured'] ) )
			$product_featured = $args['product_featured'];
		if( !empty( $args['product_shipping_class'] ) )
			$product_shipping_class = $args['product_shipping_class'];
		if( !empty( $args['product_language'] ) )
			$product_language = $args['product_language'];
		if( isset( $args['product_orderby'] ) )
			$orderby = $args['product_orderby'];
		if( isset( $args['product_order'] ) )
			$order = $args['product_order'];
		$product_dates_filter = ( isset( $args['product_dates_filter'] ) ? $args['product_dates_filter'] : false );
		switch( $product_dates_filter ) {

			case 'today':
				$product_dates_from = woo_ce_get_order_date_filter( 'today', 'from' );
				$product_dates_to = woo_ce_get_order_date_filter( 'today', 'to' );
				break;

			case 'yesterday':
				$product_dates_from = woo_ce_get_order_date_filter( 'yesterday', 'from' );
				$product_dates_to = woo_ce_get_order_date_filter( 'yesterday', 'to' );
				break;

			case 'manual':
				$date_format = woo_ce_get_option( 'date_format', 'd/m/Y' );

				// Populate empty from or to dates
				if( !empty( $args['product_dates_from'] ) )
					$product_dates_from = woo_ce_format_order_date( $args['product_dates_from'] );
				else
					$product_dates_from = woo_ce_get_product_first_date( $date_format );
				if( !empty( $args['product_dates_to'] ) )
					$product_dates_to = woo_ce_format_order_date( $args['product_dates_to'] );
				else
					$product_dates_to = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n' ), date( 'd' ) ) );

				// WP_Query only accepts D-m-Y so we must format dates to that
				if( $date_format <> 'd/m/Y' ) {
					$date_format = woo_ce_format_order_date( $date_format );
					if( function_exists( 'date_create_from_format' ) && function_exists( 'date_format' ) ) {
						if( $product_dates_from = date_create_from_format( $date_format, $product_dates_from ) )
							$product_dates_from = date_format( $product_dates_from, 'd-m-Y' );
						if( $product_dates_to = date_create_from_format( $date_format, $product_dates_to ) )
							$product_dates_to = date_format( $product_dates_to, 'd-m-Y' );
					}
				}
				break;

			default:
				$product_dates_from = false;
				$product_dates_to = false;
				break;

		}
		if( !empty( $product_dates_from ) && !empty( $product_dates_to ) ) {
			$product_dates_from = explode( '-', $product_dates_from );
			// Check that a valid date was provided
			if( isset( $product_dates_from[0] ) && isset( $product_dates_from[1] ) && isset( $product_dates_from[2] ) ) {
				$product_dates_from = array(
					'year' => $product_dates_from[2],
					'month' => $product_dates_from[1],
					'day' => $product_dates_from[0],
					'hour' => 0,
					'minute' => 0,
					'second' => 0
				);
			} else {
				$product_dates_from = false;
			}
			$product_dates_to = explode( '-', $product_dates_to );
			// Check that a valid date was provided
			if( isset( $product_dates_to[0] ) && isset( $product_dates_to[1] ) && isset( $product_dates_to[2] ) ) {
				$product_dates_to = array(
					'year' => $product_dates_to[2],
					'month' => $product_dates_to[1],
					'day' => $product_dates_to[0],
					'hour' => 23,
					'minute' => 59,
					'second' => 59
				);
			} else {
				$product_dates_to = false;
			}
		}
	}
	$post_type = apply_filters( 'woo_ce_get_products_post_type', array( 'product' ) );
	$post_status = apply_filters( 'woo_ce_get_products_status', array( 'publish', 'pending', 'draft', 'future', 'private' ) );

	$args = array(
		'post_type' => $post_type,
		'orderby' => $orderby,
		'order' => $order,
		'offset' => $offset,
		'posts_per_page' => $limit_volume,
		'post_status' => woo_ce_post_statuses( $post_status, true ),
		'fields' => 'ids',
		'suppress_filters' => false
	);
	// Filter Products by Product Category
	if( $product_categories ) {
		$term_taxonomy = 'product_cat';
		// Check if tax_query has been created
		if( !isset( $args['tax_query'] ) )
			$args['tax_query'] = array();
		$args['tax_query'][] = array(
			array(
				'taxonomy' => $term_taxonomy,
				'field' => 'id',
				'terms' => $product_categories
			)
		);
	}
	// Filter Products by Product Tag
	if( $product_tags ) {
		$term_taxonomy = 'product_tag';
		// Check if tax_query has been created
		if( !isset( $args['tax_query'] ) )
			$args['tax_query'] = array();
		$args['tax_query'][] = array(
			array(
				'taxonomy' => $term_taxonomy,
				'field' => 'id',
				'terms' => $product_tags
			)
		);
	}
	// WooCommerce Brands Addon - http://woothemes.com/woocommerce/
	if( $product_brands ) {
		$term_taxonomy = apply_filters( 'woo_ce_brand_term_taxonomy', 'product_brand' );
		// Check if tax_query has been created
		if( !isset( $args['tax_query'] ) )
			$args['tax_query'] = array();
		$args['tax_query'][] = array(
			array(
				'taxonomy' => $term_taxonomy,
				'field' => 'id',
				'terms' => $product_brands
			)
		);
	}
	// Product Vendors - http://www.woothemes.com/products/product-vendors/
	// YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/
	if( $product_vendors ) {
		$term_taxonomy = apply_filters( 'woo_ce_product_vendor_term_taxonomy', 'wcpv_product_vendors' );
		// Check if tax_query has been created
		if( !isset( $args['tax_query'] ) )
			$args['tax_query'] = array();
		$args['tax_query'][] = array(
			array(
				'taxonomy' => $term_taxonomy,
				'field' => 'id',
				'terms' => $product_vendors
			)
		);
	}
	// Filter Products by Shipping Class
	if( $product_shipping_class ) {
		$term_taxonomy = 'product_shipping_class';
		// Check if tax_query has been created
		if( !isset( $args['tax_query'] ) )
			$args['tax_query'] = array();
		$args['tax_query'][] = array(
			array(
				'taxonomy' => $term_taxonomy,
				'field' => 'id',
				'terms' => $product_shipping_class
			)
		);
	}
	// Filter Products by Language
	if( $product_language ) {

		global $sitepress;

		// See if our WPML integration magic sticks
		remove_filter( 'posts_where' , array( $sitepress, 'posts_where_filter' ), 10 );
		add_filter( 'posts_where' , 'woo_ce_wp_query_product_where_override_language' );

	}
	// Filter Products by Post Status
	if( $product_status ) {
		$args['post_status'] = woo_ce_post_statuses( $product_status, true );
	}
	// Filter Products by Product Type
	if( is_array( $product_type ) && !empty( $product_type ) ) {
		// Check if we are just exporting variations
		if( in_array( 'variation', $product_type ) && count( $product_type ) == 1 ) {
			$args['post_type'] = array( 'product_variation' );
		}
		$args['meta_query'] = array(
			'relation' => 'OR'
		);
		if( in_array( 'downloadable', $product_type ) ) {
			$args['meta_query'][] = array(
				'key' => '_downloadable',
				'value' => 'yes',
				'compare' => 'EXISTS'
			);
		}
		if( in_array( 'virtual', $product_type ) ) {
			$args['meta_query'][] = array(
				'key' => '_virtual',
				'value' => 'yes'
			);
		}
		// Remove non-Term based Product Types before we tack on our tax_query
		$term_product_type = $product_type;
		foreach( $term_product_type as $key => $type ) {
			if( in_array( $type, array( 'downloadable', 'virtual', 'variation' ) ) )
				unset( $term_product_type[$key] );
		}
		if( !empty( $term_product_type ) ) {
			$term_taxonomy = 'product_type';
			// Check if it's an empty array that has snuck through
			if( ( count( $term_product_type ) == 1 && $term_product_type[0] == '' ) == false ) {
				$args['tax_query'][] = array(
					array(
						'taxonomy' => $term_taxonomy,
						'field' => 'slug',
						'terms' => $term_product_type
					)
				);
			}
		} else {
			unset( $args['meta_query'] );
		}
		unset( $term_product_type );
	}
	// Filter Products by Featured
	if( $product_featured ) {
		$args['meta_query'][] = array(
			'key' => '_featured',
			'value' => $product_featured
		);
	}
	// Filter Products by SKU
	if( $product_sku ) {
		$args['post__in'] = array_map( 'absint', $product_sku );
	}
	// Filter Products by Post ID
	if( !empty( $product_ids ) ) {
		$product_ids = explode( ',', $product_ids );
		$size = count( $product_ids );
		if( $size > 1 )
			$args['post__in'] = array_map( 'absint', $product_ids );
		else
			$args['p'] = absint( $product_ids[0] );
	}
	// Filter Product dates
	if( !empty( $product_dates_from ) && !empty( $product_dates_to ) ) {
		$args['date_query'] = array(
			array(
				'column' => 'post_modified_date',
				'before' => $product_dates_to,
				'after' => $product_dates_from,
				'inclusive' => true
			)
		);
	}
	// Sort Products by SKU
	if( $orderby == 'sku' ) {
		$args['orderby'] = 'meta_value';
		$args['meta_key'] = '_sku';
	}
	$products = array();

	// Allow other developers to bake in their own filters
	$args = apply_filters( 'woo_ce_get_products_args', $args );

	$product_ids = new WP_Query( $args );
	if( $product_ids->posts ) {
		foreach( $product_ids->posts as $product_id ) {

			// Get Product details
			$product = get_post( $product_id );

			// Filter out Variations that don't have a Parent Product that exists
			if( isset( $product->post_type ) && $product->post_type == 'product_variation' ) {
				// Check if Parent exists
				if( $product->post_parent ) {
					if( get_post( $product->post_parent ) == false ) {
						unset( $product_id, $product );
						continue;
					}
				}
			}

			// Filter out Products based on the Stock Status and Quantity
			$term_taxonomy = 'product_type';
			if( $product_stock && has_term( 'variable', $term_taxonomy, $product_id ) !== true ) {
				$manage_stock = get_post_meta( $product_id, '_manage_stock', true );
				$stock_status = get_post_meta( $product_id, '_stock_status', true );
				$quantity = get_post_meta( $product_id, '_stock', true );
				$quantity = ( function_exists( 'wc_stock_amount' ) ? wc_stock_amount( $quantity ) : absint( $quantity ) );
				switch( $product_stock ) {

					case 'outofstock':
						if( ( $manage_stock == 'yes' && $quantity > 0 ) || $stock_status <> 'outofstock' ) {
							unset( $product_id, $product );
							continue;
						}
						break;

					case 'instock':
						if( ( $manage_stock == 'yes' && $quantity == 0 ) || $stock_status <> 'instock' ) {
							unset( $product_id, $product );
							continue;
						}
						break;

				}
				unset( $stock_status, $quantity );
			}

			if( isset( $product_id ) )
				$products[] = $product_id;

			// Include Variables in a new WP_Query if a tax_query filter is used or WPML exists
			if( ( isset( $args['tax_query'] ) || woo_ce_detect_wpml() ) && isset( $product_id ) ) {
				$term_taxonomy = 'product_type';
				if( has_term( 'variable', $term_taxonomy, $product_id ) && ( $product_type !== false && in_array( 'variation', $product_type ) ) ) {
					// @mod - Limit Volume and Volume Offset are iffy for this add-on query, needs truncation love
					$variable_args = array(
						'post_type' => 'product_variation',
						'orderby' => $orderby,
						'order' => $order,
						'offset' => $offset,
						'posts_per_page' => $limit_volume,
						'post_parent' => $product_id,
						'post_status' => array( 'publish' ),
						'fields' => 'ids',
					);
					// Filter Products by Post Status
					if( $product_status )
						$variable_args['post_status'] = woo_ce_post_statuses( $product_status, true );
					$variables = array();
					$variable_ids = new WP_Query( $variable_args );
					if( $variable_ids->posts ) {
						foreach( $variable_ids->posts as $variable_id ) {

							// Filter out Products based on the Stock Status and Quantity
							if( $product_stock ) {
								$manage_stock = get_post_meta( $variable_id, '_manage_stock', true );
								$stock_status = get_post_meta( $variable_id, '_stock_status', true );
								$quantity = get_post_meta( $variable_id, '_stock', true );
								$quantity = ( function_exists( 'wc_stock_amount' ) ? wc_stock_amount( $quantity ) : absint( $quantity ) );
								switch( $product_stock ) {

									case 'outofstock':
										if( ( $manage_stock == 'yes' && $quantity > 0 ) || $stock_status <> 'outofstock' ) {
											unset( $variable_id );
											continue;
										}
										break;

									case 'instock':
										if( ( $manage_stock == 'yes' && $quantity == 0 ) || $stock_status <> 'instock' ) {
											unset( $variable_id );
											continue;
										}
										break;

								}
								unset( $stock_status, $quantity );
							}

							if( isset( $variable_id ) ) {
								// Check we're not including a duplicate Product ID
								if( !in_array( $variable_id, $product_ids->posts ) )
									$products[] = $variable_id;
							}
						}
					}
					unset( $variables, $variable_ids, $variable_args, $variable_id );
				}
			}

			// Override for exporting Variations without Variables
			if( is_array( $product_type ) && !empty( $product_type ) ) {
				if( in_array( 'variation', $product_type ) && in_array( 'variable', $product_type ) == false ) {
					$term_taxonomy = 'product_type';
					if( has_term( 'variable', $term_taxonomy, $product_id ) ) {
						// Remove the Variable Product ID
						$key = array_search( $product_id, $products );
						if( $key !== false )
							unset( $products[$key] );
					}
				}
			}

		}
		// Only populate the $export Global if it is an export
		if( isset( $export ) )
			$export->total_rows = count( $products );
		unset( $product_ids, $product_id );
	}
	// Filter Products by Language
	if( $product_language ) {

		global $sitepress;

		add_filter( 'posts_where' , array( $sitepress, 'posts_where_filter' ), 10, 2 );
		remove_filter( 'posts_where' , 'woo_ce_wp_query_product_where_override_language' );
	}

	return $products;

}

function woo_ce_get_product_data( $product_id = 0, $args = array(), $fields = array() ) {

	global $export;

	$upload_dir = wp_upload_dir();

	// Get Product defaults
	$weight_unit = get_option( 'woocommerce_weight_unit' );
	$dimension_unit = get_option( 'woocommerce_dimension_unit' );
	$height_unit = $dimension_unit;
	$width_unit = $dimension_unit;
	$length_unit = $dimension_unit;

	$product = get_post( $product_id );
	$_product = ( function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : false );
	// Check for corrupt Products
	if( $_product == false )
		return false;

	$product->parent_id = '';
	$product->parent_sku = '';
	if( $product->post_type == 'product_variation' ) {
		// Assign Parent ID for Variants then check if Parent exists
		if( $product->parent_id = $product->post_parent )
			$product->parent_sku = get_post_meta( $product->post_parent, '_sku', true );
		else
			$product->parent_id = '';
	}
	$product->product_id = $product_id;
	$product->sku = get_post_meta( $product_id, '_sku', true );
	add_filter( 'the_title', 'woo_ce_get_product_title', 10, 2 );
	$product->name = woo_ce_format_post_title( get_the_title( $product_id ) );
	remove_filter( 'the_title', 'woo_ce_get_product_title' );
	if( $product->post_type <> 'product_variation' )
		$product->permalink = get_permalink( $product_id );
	$product->product_url = ( method_exists( $_product, 'get_permalink' ) ? $_product->get_permalink() : get_permalink( $product_id ) );
	$product->slug = $product->post_name;
	$product->description = woo_ce_format_description_excerpt( $product->post_content );
	$product->excerpt = woo_ce_format_description_excerpt( $product->post_excerpt );
	// Check if we're dealing with a Variable Product Type
	$term_taxonomy = 'product_type';
	if( has_term( 'variable', $term_taxonomy, $product_id ) ) {
		$product->price = get_post_meta( $product_id, '_price', true );
		if( method_exists( $_product, 'get_variation_regular_price' ) && method_exists( $_product, 'get_variation_sale_price' ) ) {
			// Control whether the back-end or storefront price (after taxes) is displayed
			$display = apply_filters( 'woo_ce_product_variable_price_display', false );
			$pricing_args = array(
				'min_price' => $_product->get_variation_regular_price( 'min', $display ),
				'max_price' => $_product->get_variation_regular_price( 'max', $display ),
				'min_sale_price' => $_product->get_variation_sale_price( 'min', $display ),
				'max_sale_price' => $_product->get_variation_sale_price( 'max', $display )
			);
			unset( $display );
			if( $pricing_args['min_price'] == $pricing_args['max_price'] ) {
				$product->price = woo_ce_format_price( $pricing_args['min_price'] );
				$product->sale_price = woo_ce_format_price( $pricing_args['min_sale_price'] );
			} else {
				$product->price = sprintf( apply_filters( 'woo_ce_product_variable_price', '%s-%s' ), woo_ce_format_price( $pricing_args['min_price'] ), woo_ce_format_price( $pricing_args['max_price'] ) );
				$product->sale_price = sprintf( apply_filters( 'woo_ce_product_variable_sale_price', '%s-%s' ), woo_ce_format_price( $pricing_args['min_sale_price'] ), woo_ce_format_price( $pricing_args['max_sale_price'] ) );
			}
			$product = apply_filters( 'woo_ce_product_variation_pricing', $product, $pricing_args );
			unset( $pricing_args );
		}
	} else {
		$product->price = get_post_meta( $product_id, '_regular_price', true );
		$product->sale_price = get_post_meta( $product_id, '_sale_price', true );
		if( $product->price != '' )
			$product->price = woo_ce_format_price( $product->price );
		if( $product->sale_price != '' )
			$product->sale_price = woo_ce_format_price( $product->sale_price );
	}
	$product->sale_price_dates_from = woo_ce_format_product_sale_price_dates( get_post_meta( $product_id, '_sale_price_dates_from', true ) );
	$product->sale_price_dates_to = woo_ce_format_product_sale_price_dates( get_post_meta( $product_id, '_sale_price_dates_to', true ) );
	$product->post_date = woo_ce_format_date( $product->post_date );
	$product->post_modified = woo_ce_format_date( $product->post_modified );
	$product->type = woo_ce_get_product_assoc_type( $product_id );
	if( $product->post_type == 'product_variation' ) {
		$product->description = woo_ce_format_description_excerpt( get_post_meta( $product_id, '_variation_description', true ) );
		// Override the Product Type for Variations
		$product->type = __( 'Variation', 'woocommerce-exporter' );
		// Override the Description and Excerpt if Variation Formatting is enabled
		if( woo_ce_get_option( 'variation_formatting', 0 ) ) {
			$parent = get_post( $product->parent_id );
			if( empty( $product->description ) )
				$product->description = $parent->post_content;
			if( empty( $product->excerpt ) )
				$product->excerpt = $parent->post_excerpt;
			unset( $parent );
		}
	}
	$product->visibility = woo_ce_format_product_visibility( get_post_meta( $product_id, '_visibility', true ) );
	$product->featured = woo_ce_format_switch( get_post_meta( $product_id, '_featured', true ) );
	$product->virtual = woo_ce_format_switch( get_post_meta( $product_id, '_virtual', true ) );
	$product->downloadable = woo_ce_format_switch( get_post_meta( $product_id, '_downloadable', true ) );
	$product->weight = get_post_meta( $product_id, '_weight', true );
	$product->weight_unit = ( $product->weight != '' ? $weight_unit : '' );
	$product->height = get_post_meta( $product_id, '_height', true );
	$product->height_unit = ( $product->height != '' ? $height_unit : '' );
	$product->width = get_post_meta( $product_id, '_width', true );
	$product->width_unit = ( $product->width != '' ? $width_unit : '' );
	$product->length = get_post_meta( $product_id, '_length', true );
	$product->length_unit = ( $product->length != '' ? $length_unit : '' );
	$product->category = woo_ce_get_product_assoc_categories( $product_id, $product->parent_id );
/*
	// @mod - This is being actively re-written for the 2.2+ release
	if( !empty( $product->category ) ) {
		if( $product_id == 2652 ) {
			$product->category_level_1 = '';
			$product->category_level_2 = '';
			// Separate the Category by Category separator
			$category_levels = explode( $export->category_separator, $product->category );
			echo $category_levels . ' - ' . print_r( $category_levels, true );
			echo '<hr />';
			if( !empty( $category_levels ) ) {
				$product->category_level_1 .= ( isset( $category_levels[0] ) ? $category_levels[0] : '' );
				if( !empty( $product->category_level_1 ) ) {
					$category_level_2 = explode( '>', $product->category_level_1 );
					if( !empty( $category_level_2 ) ) {
						$product->category_level_2 = ( isset( $category_level_2[1] ) ? $category_level_2[1] : '' );
					}
				}
				$product->category_level_1 .= substr( $product->category_level_1, 0, strpos( $product->category_level_1, '>' ) );
				unset( $category_levels, $category_level_2 );
			}
			echo 'All: ' . $product->category;
			echo '<hr />';
			echo 'Level 1: ' . $product->category_level_1;
			echo '<hr />';
			echo 'Level 2: ' . $product->category_level_2;
			echo '<hr />';
			echo 'Level 3: ' . $product->category_level_3;
			exit();
		}
		
	}
*/
	$product->tag = woo_ce_get_product_assoc_tags( $product_id );
	$product->manage_stock = get_post_meta( $product_id, '_manage_stock', true );
	$product->allow_backorders = woo_ce_format_product_allow_backorders( get_post_meta( $product_id, '_backorders', true ) );
	$product->sold_individually = woo_ce_format_switch( get_post_meta( $product_id, '_sold_individually', true ) );
	$product->total_sales = get_post_meta( $product_id, 'total_sales', true );
	$product->upsell_ids = woo_ce_get_product_assoc_upsell_ids( $product_id );
	$product->crosssell_ids = woo_ce_get_product_assoc_crosssell_ids( $product_id );
	$product->quantity = get_post_meta( $product_id, '_stock', true );
	// Override Variable with total stock quantity
	if( has_term( 'variable', $term_taxonomy, $product_id ) ) {
		$product->quantity = ( method_exists( $_product, 'get_total_stock' ) ? $_product->get_total_stock() : $product->quantity );
	}
	$product->quantity = ( function_exists( 'wc_stock_amount' ) ? wc_stock_amount( $product->quantity ) : $product->quantity );
	if( $product->manage_stock == 'no' && !$product->quantity )
		$product->quantity = '';
	$product->manage_stock = woo_ce_format_switch( $product->manage_stock );
	$product->stock_status = woo_ce_format_product_stock_status( get_post_meta( $product_id, '_stock_status', true ), $product->quantity );
	$product->image = woo_ce_get_product_assoc_featured_image( $product_id, $product->parent_id );
	$product->image_thumbnail = woo_ce_get_product_assoc_featured_image( $product_id, $product->parent_id, 'thumbnail' );
	$product->image_embed = '';
	if( !empty( $product->image ) ) {
		if( isset( $export->export_format ) && $export->export_format == 'xlsx' ) {
			$image_id = woo_ce_get_product_assoc_featured_image( $product_id, $product->parent_id, 'image_id' );
			if( $metadata = wp_get_attachment_metadata( $image_id ) ) {
				$thumbnail_size = apply_filters( 'woo_ce_override_embed_thumbnail_size', 'shop_thumbnail' );
				if( isset( $metadata['sizes'][$thumbnail_size] ) && $metadata['sizes'][$thumbnail_size]['file'] ) {
					$image_path = pathinfo( $metadata['file'] );
					$product->image_embed = trailingslashit( $image_path['dirname'] ) . $metadata['sizes'][$thumbnail_size]['file'];
					// $product->image_embed = trailingslashit( $upload_dir['basedir'] ) . trailingslashit( $image_path['dirname'] ) . $metadata['sizes'][$thumbnail_size]['file'];
				}
			}
			unset( $image_id, $metadata, $thumbnail_size, $image_path );
		}
	}
	$product->product_gallery = woo_ce_get_product_assoc_product_gallery( $product_id );
	$product->product_gallery_thumbnail = woo_ce_get_product_assoc_product_gallery( $product_id, 'thumbnail' );
	$product->product_gallery_embed = '';
	if( !empty( $product->product_gallery ) ) {
		if( isset( $export->export_format ) && $export->export_format == 'xlsx' ) {
			$image_ids = woo_ce_get_product_assoc_product_gallery( $product_id, 'image_id' );
			if( !empty( $image_ids ) ) {
				$image_ids = explode( $export->category_separator, $image_ids );
				$product->product_gallery_embed = array();
				foreach( $image_ids as $image_id ) {
					if( $metadata = wp_get_attachment_metadata( $image_id ) ) {
						$thumbnail_size = apply_filters( 'woo_ce_override_embed_thumbnail_size', 'shop_thumbnail' );
						if( isset( $metadata['sizes'][$thumbnail_size] ) && $metadata['sizes'][$thumbnail_size]['file'] ) {
							$image_path = pathinfo( $metadata['file'] );
							$product->product_gallery_embed[] = trailingslashit( $image_path['dirname'] ) . $metadata['sizes'][$thumbnail_size]['file'];
							// $product->product_gallery_embed[] = trailingslashit( $upload_dir['basedir'] ) . trailingslashit( $image_path['dirname'] ) . $metadata['sizes'][$thumbnail_size]['file'];
						}
					}
				}
				$product->product_gallery_embed = implode( $export->category_separator, $product->product_gallery_embed );
			}
			unset( $image_ids, $image_id, $metadata, $thumbnail_size, $image_path );
		}
	}
	$product->tax_status = woo_ce_format_product_tax_status( get_post_meta( $product_id, '_tax_status', true ) );
	$product->tax_class = woo_ce_format_product_tax_class( get_post_meta( $product_id, '_tax_class', true ) );
	$product->shipping_class = woo_ce_get_product_assoc_shipping_class( $product_id );
	$product->external_url = get_post_meta( $product_id, '_product_url', true );
	$product->button_text = get_post_meta( $product_id, '_button_text', true );
	$product->download_file_path = woo_ce_get_product_assoc_download_files( $product_id, 'url' );
	$product->download_file_name = woo_ce_get_product_assoc_download_files( $product_id, 'name' );
	$product->download_limit = get_post_meta( $product_id, '_download_limit', true );
	$product->download_expiry = get_post_meta( $product_id, '_download_expiry', true );
	$product->download_type = woo_ce_format_product_download_type( get_post_meta( $product_id, '_download_type', true ) );
	$product->purchase_note = get_post_meta( $product_id, '_purchase_note', true );
	$product->product_status = woo_ce_format_post_status( $product->post_status );
	$product->enable_reviews = woo_ce_format_comment_status( $product->comment_status );
	$product->review_count = get_post_meta( $product_id, '_wc_review_count', true );
	$rating_count = get_post_meta( $product_id, '_wc_rating_count', true );
	if( $product->post_type == 'product' ) {
		$product->rating_count = count( $rating_count );
	}
	$product->average_rating = get_post_meta( $product_id, '_wc_average_rating', true );
	unset( $_product );

	// Allow Plugin/Theme authors to add support for additional Product columns
	$product = apply_filters( 'woo_ce_product_item', $product, $product_id );

	// Trim back the Product just to requested export fields
	if( !empty( $fields ) ) {
		$fields = array_merge( $fields, array( 'id', 'ID', 'post_parent', 'filter' ) );
		if( !empty( $product ) ) {
			foreach( $product as $key => $data ) {
				if( !in_array( $key, $fields ) )
					unset( $product->$key );
			}
		}
	}

	return $product;

}

function woo_ce_wp_query_product_where_override_language( $where ) {

	global $export;

	$condition = '';
	if( !empty( $export->args ) ) {
		$languages = $export->args['product_language'];
		if( !empty( $languages ) ) {
			$where = " AND t.language_code IN ('" . implode( "', '", array_values( $languages ) ) . "')";
		}
	}
	return $where . $condition;

}

function woo_ce_export_dataset_override_product( $output = null, $export_type = null ) {

	global $export;

	if( $products = woo_ce_get_products( $export->args ) ) {
		$export->total_rows = count( $products );
		// XML export
		if( $export->export_format == 'xml' ) {
			if( !empty( $export->fields ) ) {
				foreach( $products as $product ) {
					$child = $output->addChild( apply_filters( 'woo_ce_export_xml_product_node', sanitize_key( $export_type ) ) );
					$product = woo_ce_get_product_data( $product, $export->args, array_keys( $export->fields ) );
					if( apply_filters( 'woo_ce_export_xml_product_node_attribute_id', true ) )
						$child->addAttribute( 'id', ( isset( $product->product_id ) ? $product->product_id : '' ) );
					foreach( array_keys( $export->fields ) as $key => $field ) {
						if( isset( $product->$field ) ) {
							if( !is_array( $field ) ) {
								if( woo_ce_is_xml_cdata( $product->$field, $export_type, $field ) )
									$child->addChild( sanitize_key( $export->columns[$key] ) )->addCData( woo_ce_sanitize_xml_string( $product->$field ) );
								else
									$child->addChild( sanitize_key( $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $product->$field ) ) );
							}
						}
					}
				}
			}
		} else if( $export->export_format == 'rss' ) {
			// RSS export
			if( !empty( $export->fields ) ) {
				foreach( $products as $product ) {
					$child = $output->addChild( 'item' );
					$product = woo_ce_get_product_data( $product, $export->args, array_keys( $export->fields ) );
					foreach( array_keys( $export->fields ) as $field ) {
						if( isset( $product->$field ) ) {
							if( !is_array( $field ) ) {
								if( woo_ce_is_xml_cdata( $product->$field ) )
									$child->addChild( sanitize_key( $field ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $product->$field ) ) );
								else
									$child->addChild( sanitize_key( $field ), esc_html( woo_ce_sanitize_xml_string( $product->$field ) ) );
							}
						}
					}
				}
			}
		} else {
			// PHPExcel export
			foreach( $products as $key => $product )
				$products[$key] = woo_ce_get_product_data( $product, $export->args, array_keys( $export->fields ) );
			$output = $products;
		}
		unset( $products, $product );
	}
	return $output;

}

function woo_ce_export_dataset_multisite_override_product( $output = null, $export_type = null ) {

	global $export;

	$sites = wp_get_sites();
	if( !empty( $sites ) ) {
		foreach( $sites as $site ) {
			switch_to_blog( $site['blog_id'] );
			if( $products = woo_ce_get_products( $export->args ) ) {
				$export->total_rows = count( $products );
				// XML export
				if( $export->export_format == 'xml' ) {
					if( !empty( $export->fields ) ) {
						foreach( $products as $product ) {
							$child = $output->addChild( apply_filters( 'woo_ce_export_xml_product_node', sanitize_key( $export_type ) ) );
							$product = woo_ce_get_product_data( $product, $export->args, array_keys( $export->fields ) );
							if( apply_filters( 'woo_ce_export_xml_product_node_attribute_id', true ) )
								$child->addAttribute( 'id', ( isset( $product->product_id ) ? $product->product_id : '' ) );
							foreach( array_keys( $export->fields ) as $key => $field ) {
								if( isset( $product->$field ) ) {
									if( !is_array( $field ) ) {
										if( woo_ce_is_xml_cdata( $product->$field, $export_type, $field ) )
											$child->addChild( sanitize_key( $export->columns[$key] ) )->addCData( woo_ce_sanitize_xml_string( $product->$field ) );
										else
											$child->addChild( sanitize_key( $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $product->$field ) ) );
									}
								}
							}
						}
					}
				} else if( $export->export_format == 'rss' ) {
					// RSS export
					if( !empty( $export->fields ) ) {
						foreach( $products as $product ) {
							$child = $output->addChild( 'item' );
							$product = woo_ce_get_product_data( $product, $export->args, array_keys( $export->fields ) );
							foreach( array_keys( $export->fields ) as $field ) {
								if( isset( $product->$field ) ) {
									if( !is_array( $field ) ) {
										if( woo_ce_is_xml_cdata( $product->$field ) )
											$child->addChild( sanitize_key( $field ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $product->$field ) ) );
										else
											$child->addChild( sanitize_key( $field ), esc_html( woo_ce_sanitize_xml_string( $product->$field ) ) );
									}
								}
							}
						}
					}
				} else {
					// PHPExcel export
					foreach( $products as $key => $product )
						$products[$key] = woo_ce_get_product_data( $product, $export->args, array_keys( $export->fields ) );
					if( is_null( $output ) )
						$output = $products;
					else
						$output = array_merge( $output, $products );
				}
				unset( $products, $product );
			}
			restore_current_blog();
		}
	}
	return $output;

}

// Filters the get_the_title() function and adds friendly Variation information
function woo_ce_get_product_title( $title = '', $post_ID = '' ) {

	if( !empty( $post_ID ) ) {

		$product = ( function_exists( 'wc_get_product' ) ? wc_get_product( $post_ID ) : false );
		if( !empty( $product ) ) {
			// Check if we're dealing with a Variation
			$title = $product->get_title();
			if ( $product->is_type( 'variation' ) ) {
				$list_attributes = array();
				$attributes = $product->get_variation_attributes();
				if( !empty( $attributes ) ) {
					$format = apply_filters( 'woo_ce_get_product_title_attribute_formatting', 'slug', $post_ID );
					foreach ( $attributes as $name => $attribute ) {
						switch( $format ) {

							case 'title':
								$list_attributes[] = wc_attribute_label( str_replace( 'attribute_', '', $name ) ) . ': ' . woo_ce_get_product_attribute_name_by_slug( $attribute, str_replace( 'attribute_', '', $name ) );
								break;

							case 'slug':
							default:
								$list_attributes[] = wc_attribute_label( str_replace( 'attribute_', '', $name ) ) . ': ' . $attribute;
								break;

						}
					}
					$title .= ' - ' . implode( ', ', $list_attributes );
				}
				unset( $attributes );
			}
		}

	}
	return $title;

}

// Filters the get_the_title() function and adds friendly Variation information suffixed with SKU
function woo_ce_get_product_title_sku( $title = '', $post_ID = '' ) {

	if( !empty( $post_ID ) ) {

		$product = ( function_exists( 'wc_get_product' ) ? wc_get_product( $post_ID ) : false );
		if( !empty( $product ) ) {
			// Check if we're dealing with a Variation
			$title = $product->get_title();
			if ( $product->is_type( 'variation' ) ) {
				$list_attributes = array();
				$attributes = $product->get_variation_attributes();
				if( !empty( $attributes ) ) {
					foreach ( $attributes as $name => $attribute ) {
						$list_attributes[] = wc_attribute_label( str_replace( 'attribute_', '', $name ) ) . ': ' . $attribute;
					}
					$title .= ' - ' . implode( ', ', $list_attributes );
				}
				unset( $attributes );
			}
			$sku = $product->get_sku();
			if( !empty( $sku ) )
				$title .= ' (' . sprintf( __( 'SKU: %s', 'woocommerce-exporter' ), $sku ) . ')';
			unset( $sku );
		}

	}
	return $title;

}

// Returns Product Categories associated to a specific Product
function woo_ce_get_product_assoc_categories( $product_id = 0, $parent_id = 0, $term_taxonomy = 'product_cat' ) {

	global $export;

	$category_separator = apply_filters( 'woo_ce_get_product_assoc_categories_separator', $export->category_separator );

	$output = '';
	// Return Product Categories of Parent if this is a Variation
	$categories = array();
	if( !empty( $parent_id ) )
		$product_id = $parent_id;
	if( !empty( $product_id ) )
		$categories = wp_get_object_terms( $product_id, $term_taxonomy );
	if( !empty( $categories ) && !is_wp_error( $categories ) ) {
		$size = apply_filters( 'woo_ce_get_product_assoc_categories_size', count( $categories ) );
		for( $i = 0; $i < $size; $i++ ) {
			if( $categories[$i]->parent == '0' ) {
				$output .= $categories[$i]->name . $category_separator;
			} else {
				// Check if Parent -> Child
				$category_1 = get_term( $categories[$i]->parent, $term_taxonomy );
				if( $category_1->parent == '0' ) {
					$output .= $category_1->name . '>' . $categories[$i]->name . $category_separator;
				} else {
					// Check if Parent -> Child -> Subchild
					$category_2 = get_term( $category_1->parent, $term_taxonomy );
					if( $category_2->parent == '0' ) {
						$output .= $category_2->name . '>' . $category_1->name . '>' . $categories[$i]->name . $category_separator;
					} else {
						// Check if Parent -> Child -> Child -> Subchild
						$category_3 = get_term( $category_2->parent, $term_taxonomy );
						$output .= $category_3->name . '>' . $category_2->name . '>' . $category_1->name . '>' . $categories[$i]->name . $category_separator;
					}
				}
				unset( $category_1, $category_2, $category_3 );
			}
		}
		$output = substr( $output, 0, -1 );
		// Sort Categories
		if( $size > 1 && apply_filters( 'woo_ce_get_product_assoc_categories_sort', true ) ) {
			$output = explode( $category_separator, $output );
			sort( $output );
			$output = implode( $category_separator, $output );
		}
	} else {
		$output .= __( 'Uncategorized', 'woocommerce-exporter' );
	}
	return $output;

}

// Returns Product Tags associated to a specific Product
function woo_ce_get_product_assoc_tags( $product_id = 0, $term_taxonomy = 'product_tag' ) {

	global $export;

	$category_separator = apply_filters( 'woo_ce_get_product_assoc_tags_separator', $export->category_separator );

	$output = '';
	$args = array(
		'fields' => 'names'
	);
	$terms = wp_get_object_terms( $product_id, $term_taxonomy, $args );
	if( !empty( $terms ) && is_wp_error( $terms ) == false ) {
		$output = implode( $category_separator, $terms );
		unset( $terms );
	}
	return $output;

}

// Returns the Featured Image associated to a specific Product
function woo_ce_get_product_assoc_featured_image( $product_id = 0, $parent_id = 0, $image_format = 'full' ) {

	global $export;

	$output = '';
	if( !empty( $product_id ) ) {
		$thumbnail_id = get_post_meta( $product_id, '_thumbnail_id', true );
		// Default empty value
		if( isset( $export->args['product_image_formatting'] ) == false )
			$export->args['product_image_formatting'] = woo_ce_get_option( 'product_image_formatting', 1 );
		if( !empty( $thumbnail_id ) ) {
			// Check if we're returning ID's or URL's
			if( $export->args['product_image_formatting'] == '0' || $image_format == 'image_id' ) {
				$output = $thumbnail_id;
			} else if( in_array( $export->args['product_image_formatting'], array( '1', '2' ) ) ) {
				switch( $export->args['product_image_formatting'] ) {

					case '1':
						// Media URL
						if( $image_format == 'full' )
							$output = wp_get_attachment_url( $thumbnail_id );
						else if( $image_format == 'thumbnail' )
							$output = wp_get_attachment_thumb_url( $thumbnail_id );
						break;

					case '2':
						// Media filename
						if( $image_format == 'full' ) {
							$output = get_attached_file( $thumbnail_id );
						} else if( $image_format == 'thumbnail' ) {
							$output = wp_get_attachment_thumb_file( $thumbnail_id );
							// Media don't have a 'thumb' size assigned
							if( $output == false ) {
								$file = get_attached_file( $thumbnail_id );
								$imagedata = wp_get_attachment_metadata( $thumbnail_id );
								$thumbnail_size = apply_filters( 'woo_ce_override_image_thumbnail_size', 'thumbnail' );
								if( !empty( $imagedata['sizes'][$thumbnail_size]['file'] ) && ( $thumbfile = str_replace( basename($file), $imagedata['sizes'][$thumbnail_size]['file'], $file ) ) && file_exists( $thumbfile ) )
									$output = $thumbfile;
								unset( $file, $imagedata, $thumbnail_size, $thumbfile );
							}
						}
						break;

				}
			}
		} else if( !empty( $parent_id ) && woo_ce_get_option( 'variation_formatting', 0 ) ) {
			// Return Feature Image of Parent if this is a Variation
			$thumbnail_id = get_post_meta( $parent_id, '_thumbnail_id', true );
			if( !empty( $thumbnail_id ) ) {
				if( $export->args['product_image_formatting'] == '0' || $image_format == 'image_id' ) {
					$output = $thumbnail_id;
				} else if( in_array( $export->args['product_image_formatting'], array( '1', '2' ) ) ) {
					switch( $export->args['product_image_formatting'] ) {

						case '1':
							// Media URL
							if( $image_format == 'full' )
								$output = wp_get_attachment_url( $thumbnail_id );
							else if( $image_format == 'thumbnail' )
								$output = wp_get_attachment_thumb_url( $thumbnail_id );
							break;

						case '2':
							// Media filename
							if( $image_format == 'full' ) {
								$output = get_attached_file( $thumbnail_id );
							} else if( $image_format == 'thumbnail' ) {
								$output = wp_get_attachment_thumb_file( $thumbnail_id );
								// Media don't have a 'thumb' size assigned
								if( $output == false ) {
									$file = get_attached_file( $thumbnail_id );
									$imagedata = wp_get_attachment_metadata( $thumbnail_id );
									$thumbnail_size = apply_filters( 'woo_ce_override_image_thumbnail_size', 'thumbnail' );
									if( !empty( $imagedata['sizes'][$thumbnail_size]['file'] ) && ( $thumbfile = str_replace( basename($file), $imagedata['sizes'][$thumbnail_size]['file'], $file ) ) && file_exists( $thumbfile ) )
										$output = $thumbfile;
									unset( $file, $imagedata, $thumbnail_size, $thumbfile );
								}
							}
							break;

					}
				}
			}
		}
	}
	return $output;

}

// Returns the Product Galleries associated to a specific Product
function woo_ce_get_product_assoc_product_gallery( $product_id = 0, $image_format = 'full' ) {

	global $export;

	if( !empty( $product_id ) ) {
		$images = get_post_meta( $product_id, '_product_image_gallery', true );
		if( !empty( $images ) ) {
			$output = '';
			// Default empty value
			if( isset( $export->args['gallery_formatting'] ) == false )
				$export->args['gallery_formatting'] = woo_ce_get_option( 'gallery_formatting', 1 );
			// Check if we're returning ID's or URL's
			if( $export->args['gallery_formatting'] == '0' || $image_format == 'image_id' ) {
				$images = explode( ',', $images );
				$output = implode( $export->category_separator, $images );
			} else if( in_array( $export->args['gallery_formatting'], array( '1', '2' ) ) ) {
				$images = explode( ',', $images );
				$size = count( $images );
				for( $i = 0; $i < $size; $i++ ) {
					switch( $export->args['gallery_formatting'] ) {

						case '1':
							// Media URL
							if( $image_format == 'full' )
								$images[$i] = wp_get_attachment_url( $images[$i] );
							else if( $image_format == 'thumbnail' )
								$images[$i] = wp_get_attachment_thumb_url( $images[$i] );
							break;

						case '2':
							// Media filename
							if( $image_format == 'full' ) {
								$images[$i] = get_attached_file( $images[$i] );
							} else if( $image_format == 'thumbnail' ) {
								$image_thumbnail = $images[$i];
								$images[$i] = wp_get_attachment_thumb_file( $images[$i] );
								// Media don't have a 'thumb' size assigned
								if( $images[$i] == false ) {
									$file = get_attached_file( $image_thumbnail );
									$imagedata = wp_get_attachment_metadata( $image_thumbnail );
									$thumbnail_size = apply_filters( 'woo_ce_override_image_thumbnail_size', 'thumbnail' );
									if( !empty( $imagedata['sizes'][$thumbnail_size]['file'] ) && ( $thumbfile = str_replace( basename($file), $imagedata['sizes'][$thumbnail_size]['file'], $file ) ) && file_exists( $thumbfile ) )
										$images[$i] = $thumbfile;
									unset( $file, $imagedata, $thumbnail_size, $thumbfile );
								}
								unset( $image_thumbnail );
							}
							break;

					}
				}
				$output = implode( $export->category_separator, $images );
			}
			return $output;
		}
	}

}

// Returns the Product Type of a specific Product
function woo_ce_get_product_assoc_type( $product_id = 0 ) {

	global $export;

	$output = '';
	$term_taxonomy = 'product_type';
	$types = wp_get_object_terms( $product_id, $term_taxonomy );
	if( empty( $types ) )
		$types = array( get_term_by( 'name', 'simple', $term_taxonomy ) );
	if( $types ) {
		$size = count( $types );
		for( $i = 0; $i < $size; $i++ ) {
			$type = get_term( $types[$i]->term_id, $term_taxonomy );
			$output .= woo_ce_format_product_type( $type->name ) . $export->category_separator;
		}
		$output = substr( $output, 0, -1 );
	}
	return $output;

}

// Returns the Shipping Class of a specific Product
function woo_ce_get_product_assoc_shipping_class( $product_id = 0 ) {

	global $export;

	$output = '';
	$term_taxonomy = 'product_shipping_class';
	$types = wp_get_object_terms( $product_id, $term_taxonomy );
	if( empty( $types ) )
		$types = get_term_by( 'name', 'simple', $term_taxonomy );
	if( !empty( $types ) ) {
		$size = count( $types );
		for( $i = 0; $i < $size; $i++ ) {
			$type = get_term( $types[$i]->term_id, $term_taxonomy );
			if( is_wp_error( $type ) !== true )
				$output .= $type->name . $export->category_separator;
		}
		$output = substr( $output, 0, -1 );
	}
	return $output;

}

// Returns the Up-Sell associated to a specific Product
function woo_ce_get_product_assoc_upsell_ids( $product_id = 0 ) {

	global $export;

	$output = '';
	if( $product_id ) {
		$upsell_ids = get_post_meta( $product_id, '_upsell_ids', true );
		// Convert Product ID to Product SKU as per Up-Sells Formatting
		if( $export->args['upsell_formatting'] == 1 && !empty( $upsell_ids ) ) {
			$size = count( $upsell_ids );
			for( $i = 0; $i < $size; $i++ ) {
				$upsell_ids[$i] = get_post_meta( $upsell_ids[$i], '_sku', true );
				if( empty( $upsell_ids[$i] ) )
					unset( $upsell_ids[$i] );
			}
			// 'reindex' array
			$upsell_ids = array_values( $upsell_ids );
		}
		$output = woo_ce_convert_product_ids( $upsell_ids );
	}
	return $output;

}

// Returns the Cross-Sell associated to a specific Product
function woo_ce_get_product_assoc_crosssell_ids( $product_id = 0 ) {

	global $export;

	$output = '';
	if( $product_id ) {
		$crosssell_ids = get_post_meta( $product_id, '_crosssell_ids', true );
		// Convert Product ID to Product SKU as per Cross-Sells Formatting
		if( $export->args['crosssell_formatting'] == 1 && !empty( $crosssell_ids ) ) {
			$size = count( $crosssell_ids );
			for( $i = 0; $i < $size; $i++ ) {
				$crosssell_ids[$i] = get_post_meta( $crosssell_ids[$i], '_sku', true );
				// Remove Cross-Sell if SKU is empty
				if( empty( $crosssell_ids[$i] ) )
					unset( $crosssell_ids[$i] );
			}
			// 'reindex' array
			$crosssell_ids = array_values( $crosssell_ids );
		}
		$output = woo_ce_convert_product_ids( $crosssell_ids );
	}
	return $output;
	
}

// Returns Product Attributes associated to a specific Product
function woo_ce_get_product_assoc_attributes( $product_id = 0, $args = array() ) {

	global $export;

	$defaults = array(
		'attribute' => array(),
		'type' => 'product',
		'fields' => 'names',
		'term_taxonomy' => false
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );

	$output = '';
	if( !empty( $product_id ) && !empty( $attribute ) ) {
		$terms = array();
		if( $type == 'product' ) {
			if( $attribute['is_taxonomy'] == 1 )
				$term_taxonomy = $attribute['name'];
		} else if( $type == 'global' ) {
			$term_taxonomy = sprintf( 'pa_%s', $attribute->attribute_name );
		}
		if( !empty( $term_taxonomy ) ) {
			$args = array(
				'fields' => $fields
			);
			$terms = wp_get_object_terms( $product_id, $term_taxonomy, $args );
			if( !empty( $terms ) && is_wp_error( $terms ) == false ) {
				$output = implode( $export->category_separator, $terms );
				unset( $terms );
			}
		}
	}
	return $output;

}

// Returns the Variation quantities linked to Attributes of a given Variable Product
function woo_ce_get_product_assoc_attribute_quantities( $product_id = 0, $args = array() ) {

	global $export;

	$defaults = array(
		'attribute' => array(),
		'type' => 'product',
		'fields' => 'ids'
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );

	$output = '';
	$post_type = 'product_variation';
	if( !empty( $product_id ) && !empty( $attribute ) ) {
		// Get the Term slugs linked to this Product
		$terms = woo_ce_get_product_assoc_attributes( $product_id, $args );
		if( !empty( $terms ) ) {
			$output = array();
			$terms = explode( $export->category_separator, $terms );
			foreach( $terms as $term ) {
				// Return the Post ID of the Product linked to each Attribute
				if( $type == 'product' ) {
					if( $attribute['is_taxonomy'] == 1 )
						$term_taxonomy = $attribute['name'];
				} else if( $type == 'global' ) {
					$term_taxonomy = sprintf( 'pa_%s', $attribute->attribute_name );
				}
				$args = array(
					'post_type' => $post_type,
					'post_parent' => $product_id,
					'tax_query' => array(
						array(
							'taxonomy' => $term_taxonomy,
							'field' => 'term_id',
							'terms' => $term
						)
					),
					'fields' => 'ids',
					'posts_per_page' => 1
				);
				$product_ids = new WP_Query( $args );
				if( $product_ids->posts ) {
					$quantity = get_post_meta( $product_ids->posts[0], '_stock', true );
					$quantity = ( function_exists( 'wc_stock_amount' ) ? wc_stock_amount( $quantity ) : absint( $quantity ) );
					$output[] = $quantity;
				}
			}
			$output = implode( $export->category_separator, $output );
		}
	}
	return $output;

}

// Returns the Attribute Name when passed a Attribute Slug
function woo_ce_get_product_attribute_name_by_slug( $slug = '', $term_taxonomy = '' ) {

	$output = $slug;
	if( !empty( $term_taxonomy ) ) {
		$term = get_term_by( 'slug', $slug, $term_taxonomy );
		if( !empty( $term ) )
			$output = $term->name;
		unset( $term );
	}
	return $output;

}

// Returns File Downloads associated to a specific Product
function woo_ce_get_product_assoc_download_files( $product_id = 0, $type = 'url' ) {

	global $export;

	$output = '';
	if( $product_id ) {
		if( version_compare( WOOCOMMERCE_VERSION, '2.0', '>=' ) ) {
			// If WooCommerce 2.0+ is installed then use new _downloadable_files Post meta key
			if( $file_downloads = maybe_unserialize( get_post_meta( $product_id, '_downloadable_files', true ) ) ) {
				foreach( $file_downloads as $file_download ) {
					if( $type == 'url' )
						$output .= $file_download['file'] . $export->category_separator;
					else if( $type == 'name' )
						$output .= $file_download['name'] . $export->category_separator;
				}
				unset( $file_download, $file_downloads );
			}
			$output = substr( $output, 0, -1 );
		} else {
			// If WooCommerce -2.0 is installed then use legacy _file_paths Post meta key
			if( $file_downloads = maybe_unserialize( get_post_meta( $product_id, '_file_paths', true ) ) ) {
				foreach( $file_downloads as $file_download ) {
					if( $type == 'url' )
						$output .= $file_download . $export->category_separator;
				}
				unset( $file_download, $file_downloads );
			}
			$output = substr( $output, 0, -1 );
		}
	}
	return $output;

}

function woo_ce_get_product_assoc_order_ids( $products = array() ) {

	// Save database processing
	if( count( $products ) == 0 )
		return;

	global $wpdb;

	$output = false;
	// $order_ids_sql = "SELECT `order_id` FROM `" . $wpdb->prefix . "woocommerce_order_items` as order_items, `" . $wpdb->prefix . "woocommerce_order_itemmeta` as order_itemmeta WHERE `order_items`.order_item_id = `order_itemmeta`.order_item_id AND `order_itemmeta`.meta_key IN ( '_product_id', '_variation_id' ) AND `order_itemmeta`.meta_value IN ( " . implode( ',', $products ) . " )";
	$order_ids_sql = "SELECT `order_id` FROM `" . $wpdb->prefix . "woocommerce_order_items` as order_items, `" . $wpdb->prefix . "woocommerce_order_itemmeta` as order_itemmeta WHERE `order_items`.order_item_id = `order_itemmeta`.order_item_id AND `order_itemmeta`.meta_key = '_product_id' AND `order_itemmeta`.meta_value IN ( " . implode( ',', $products ) . " )";
	$order_ids = $wpdb->get_col( $order_ids_sql );
	$wpdb->flush();
	if( !empty( $order_ids ) ) {
		$output = $order_ids;
		unset( $order_ids );
	}
	return $output;

}

function woo_ce_format_product_visibility( $visibility = '' ) {

	$output = '';
	if( !empty( $visibility ) ) {
		switch( $visibility ) {

			case 'visible':
				$output = __( 'Catalog & Search', 'woocommerce-exporter' );
				break;

			case 'catalog':
				$output = __( 'Catalog', 'woocommerce-exporter' );
				break;

			case 'search':
				$output = __( 'Search', 'woocommerce-exporter' );
				break;

			case 'hidden':
				$output = __( 'Hidden', 'woocommerce-exporter' );
				break;

		}
	}
	return $output;

}

function woo_ce_format_product_allow_backorders( $allow_backorders = '' ) {

	$output = '';
	if( !empty( $allow_backorders ) ) {
		switch( $allow_backorders ) {

			case 'yes':
			case 'no':
				$output = woo_ce_format_switch( $allow_backorders );
				break;

			case 'notify':
				$output = __( 'Notify', 'woocommerce-exporter' );
				break;

		}
	}
	return $output;

}

function woo_ce_format_product_download_type( $download_type = '' ) {

	$output = __( 'Standard', 'woocommerce-exporter' );
	if( !empty( $download_type ) ) {
		switch( $download_type ) {

			case 'application':
				$output = __( 'Application', 'woocommerce-exporter' );
				break;

			case 'music':
				$output = __( 'Music', 'woocommerce-exporter' );
				break;

		}
	}
	return $output;

}

function woo_ce_format_product_stock_status( $stock_status = '', $stock = '' ) {

	$output = '';
	if( empty( $stock_status ) && !empty( $stock ) ) {
		if( $stock )
			$stock_status = 'instock';
		else
			$stock_status = 'outofstock';
	}
	if( $stock_status ) {
		switch( $stock_status ) {

			case 'instock':
				$output = __( 'In Stock', 'woocommerce-exporter' );
				break;

			case 'outofstock':
				$output = __( 'Out of Stock', 'woocommerce-exporter' );
				break;

		}
	}
	return $output;

}

function woo_ce_format_product_tax_status( $tax_status = null ) {

	$output = '';
	if( !empty( $tax_status ) ) {
		switch( $tax_status ) {
	
			case 'taxable':
				$output = __( 'Taxable', 'woocommerce-exporter' );
				break;
	
			case 'shipping':
				$output = __( 'Shipping Only', 'woocommerce-exporter' );
				break;

			case 'none':
				$output = __( 'None', 'woocommerce-exporter' );
				break;

		}
	}
	return $output;

}

function woo_ce_format_product_tax_class( $tax_class = '' ) {

	global $export;

	$output = '';
	if( $tax_class ) {
		switch( $tax_class ) {

			case '*':
				$tax_class = __( 'Standard', 'woocommerce-exporter' );
				break;

			case 'reduced-rate':
				$tax_class = __( 'Reduced Rate', 'woocommerce-exporter' );
				break;

			case 'zero-rate':
				$tax_class = __( 'Zero Rate', 'woocommerce-exporter' );
				break;

		}
		$output = $tax_class;
	}
	return $output;

}

function woo_ce_format_product_type( $type_id = '' ) {

	$output = $type_id;
	if( $output ) {
		$product_types = apply_filters( 'woo_ce_format_product_types', array(
			'simple' => __( 'Simple Product', 'woocommerce' ),
			'downloadable' => __( 'Downloadable', 'woocommerce' ),
			'grouped' => __( 'Grouped Product', 'woocommerce' ),
			'virtual' => __( 'Virtual', 'woocommerce' ),
			'variable' => __( 'Variable', 'woocommerce' ),
			'external' => __( 'External/Affiliate Product', 'woocommerce' ),
			'variation' => __( 'Variation', 'woocommerce-exporter' ),
			'subscription' => __( 'Simple Subscription', 'woocommerce-exporter' ),
			'variable-subscription' => __( 'Variable Subscription', 'woocommerce-exporter' )
		) );
		if( isset( $product_types[$type_id] ) )
			$output = $product_types[$type_id];
	}
	return $output;

}

// Returns a list of WooCommerce Product Types to export process
function woo_ce_get_product_types() {

	$term_taxonomy = 'product_type';
	$args = array(
		'hide_empty' => 0
	);
	$types = get_terms( $term_taxonomy, $args );
	if( !empty( $types ) && is_wp_error( $types ) == false ) {
		$output = array();
		$size = count( $types );
		for( $i = 0; $i < $size; $i++ ) {
			$output[$types[$i]->slug] = array(
				'name' => ucfirst( $types[$i]->name ),
				'count' => $types[$i]->count
			);
			// Override the Product Type count for Downloadable and Virtual
			if( in_array( $types[$i]->slug, array( 'downloadable', 'virtual' ) ) ) {
				if( $types[$i]->slug == 'downloadable' ) {
					$args = array(
						'meta_key' => '_downloadable',
						'meta_value' => 'yes'
					);
				} else if( $types[$i]->slug == 'virtual' ) {
					$args = array(
						'meta_key' => '_virtual',
						'meta_value' => 'yes'
					);
				}
				$output[$types[$i]->slug]['count'] = woo_ce_get_product_type_count( 'product', $args );
			}
		}
		$output['variation'] = array(
			'name' => __( 'variation', 'woocommerce-exporter' ),
			'count' => woo_ce_get_product_type_count( 'product_variation' )
		);
		asort( $output );
		return $output;
	}

}

function woo_ce_get_product_type_count( $post_type = 'product', $args = array() ) {

	$defaults = array(
		'post_type' => $post_type,
		'posts_per_page' => 1,
		'fields' => 'ids'
	);
	$args = wp_parse_args( $args, $defaults );
	$product_ids = new WP_Query( $args );
	$size = $product_ids->found_posts;
	return $size;

}

// Returns a list of WooCommerce Product Attributes to export process
function woo_ce_get_product_attributes( $slice = '' ) {

	if( apply_filters( 'woo_ce_enable_product_attributes', true ) == false )
		return false; 

	global $export;

	if ( false === ( $output = get_transient( 'wc_attribute_taxonomies' ) ) ) {
		$output = ( function_exists( 'wc_get_attribute_taxonomies' ) ? wc_get_attribute_taxonomies() : array() );
		if( WOO_CD_DEBUG )
			error_log( 'wc_get_attribute_taxonomies(): ' . ( time() - $export->start_time ) );
	}

	// Fallback when wc_get_attribute_taxonomies() fails
	if( empty( $output ) ) {

		global $wpdb;

		$output = array();
		// Check if there are any records in wp_woocommerce_attribute_taxonomies
		if( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}woocommerce_attribute_taxonomies';" ) ) {
			$attributes_sql = "SELECT * FROM `" . $wpdb->prefix . "woocommerce_attribute_taxonomies`";
			$attributes = $wpdb->get_results( $attributes_sql );
			$wpdb->flush();
			if( WOO_CD_DEBUG ) {
				if( isset( $export->start_time ) )
					error_log( 'attributes_sql: ' . ( time() - $export->start_time ) );
			}
		}

	}

	if( !empty( $output ) ) {
		// Splice in our custom Attributes
		$custom_attributes = woo_ce_get_option( 'custom_attributes', '' );
		if( !empty( $custom_attributes ) ) {
			foreach( $custom_attributes as $custom_attribute ) {
				if( !empty( $custom_attribute ) ) {
					$output[] = (object)array(
						'attribute_id' => 0,
						'attribute_name' => remove_accents( $custom_attribute ),
						'attribute_label' => $custom_attribute,
						'attribute_type' => 'select',
						'attribute_orderby' => 'menu_order',
						'attribute_public' => 0
					);
				}
			}
			unset( $custom_attributes, $custom_attribute );
		}
		unset( $attributes );
	}

	// Trim back the response
	if( !empty( $output ) && !empty( $slice ) ) {
		$attributes = $output;
		$output = array();
		foreach( $attributes as $attribute ) {
			$output[] = ( isset( $attribute->$slice ) ? $attribute->$slice : '' );
		}
	}

	return $output;

}

function woo_ce_get_product_assoc_brands( $product_id = 0, $parent_id = 0 ) {

	global $export;

	$output = '';
	$term_taxonomy = apply_filters( 'woo_ce_brand_term_taxonomy', 'product_brand' );
	// Return Product Brands of Parent if this is a Variation
	if( $parent_id )
		$product_id = $parent_id;
	if( $product_id )
		$brands = wp_get_object_terms( $product_id, $term_taxonomy );
	if( !empty( $brands ) && is_wp_error( $brands ) == false ) {
		$size = count( $brands );
		for( $i = 0; $i < $size; $i++ ) {
			if( $brands[$i]->parent == '0' ) {
				$output .= $brands[$i]->name . $export->category_separator;
			} else {
				// Check if Parent -> Child
				$parent_brand = get_term( $brands[$i]->parent, $term_taxonomy );
				// Check if Parent -> Child -> Subchild
				if( $parent_brand->parent == '0' ) {
					$output .= $parent_brand->name . '>' . $brands[$i]->name . $export->category_separator;
					$output = str_replace( $parent_brand->name . $export->category_separator, '', $output );
				} else {
					$root_brand = get_term( $parent_brand->parent, $term_taxonomy );
					$output .= $root_brand->name . '>' . $parent_brand->name . '>' . $brands[$i]->name . $export->category_separator;
					$output = str_replace( array(
						$root_brand->name . '>' . $parent_brand->name . $export->category_separator,
						$parent_brand->name . $export->category_separator
					), '', $output );
				}
				unset( $root_brand, $parent_brand );
			}
		}
		$output = substr( $output, 0, -1 );
	}
	return $output;

}

function woo_ce_get_product_assoc_per_product_shipping_rules( $product_id ) {

	// Per-Product Shipping - http://www.woothemes.com/products/per-product-shipping/
	if( woo_ce_detect_export_plugin( 'per_product_shipping' ) ) {

		global $wpdb, $export;

		$output = array();
		$shipping_rules_sql = $wpdb->prepare( "SELECT rule_country as `country`, rule_state as `state`, rule_postcode as `postcode`, rule_cost as `cost`, rule_item_cost as `item_cost`, rule_order as `order` FROM `" . $wpdb->prefix . "woocommerce_per_product_shipping_rules` WHERE `product_id` = %d", $product_id );
		$shipping_rules = $wpdb->get_results( $shipping_rules_sql );
		$wpdb->flush();
		if( !empty( $shipping_rules ) ) {

			$output = array(
				'country' => '',
				'state' => '',
				'postcode' => '',
				'cost' => '',
				'item_cost' => '',
				'order' => ''
			);

			foreach( $shipping_rules as $shipping_rule ) {
				$output['country'] .= ( !empty( $shipping_rule->country ) ? $shipping_rule->country : '*' ) . $export->category_separator;
				$output['state'] .= ( !empty( $shipping_rule->state ) ? $shipping_rule->state : '*' ) . $export->category_separator;
				$output['postcode'] .= ( !empty( $shipping_rule->postcode ) ? $shipping_rule->postcode : '*' ) . $export->category_separator;
				$output['cost'] .= ( !empty( $shipping_rule->cost ) ? $shipping_rule->cost : '*' ) . $export->category_separator;
				$output['item_cost'] .= ( !empty( $shipping_rule->item_cost ) ? $shipping_rule->item_cost : '*' ) . $export->category_separator;
				$output['order'] .= ( !empty( $shipping_rule->order ) ? $shipping_rule->order : '0' ) . $export->category_separator;
			}

			$output['country'] = substr( $output['country'], 0, -1 );
			$output['state'] = substr( $output['state'], 0, -1 );
			$output['postcode'] = substr( $output['postcode'], 0, -1 );
			$output['cost'] = substr( $output['cost'], 0, -1 );
			$output['item_cost'] = substr( $output['item_cost'], 0, -1 );
			$output['order'] = substr( $output['order'], 0, -1 );

		}
		return $output;
	}

}

function woo_ce_format_product_sale_price_dates( $sale_date = '' ) {

	$output = $sale_date;
	if( $sale_date )
		$output = woo_ce_format_date( date( 'Y-m-d H:i:s', $sale_date ) );
	return $output;

}

function woo_ce_format_pre_orders_charge( $charge = '' ) {

	$output = $charge;
	if( !empty( $charge ) ) {
		switch( $charge ) {

			case 'upon_release':
				$output = __( 'Upon Release', 'woocommerce-exporter' );
				break;

			case 'upfront':
				$output = __( 'Upfront', 'woocommerce-exporter' );
				break;

		}
	}
	return $output;

}

function woo_ce_unique_product_gallery_fields( $fields = array() ) {

	$max_size = woo_ce_get_option( 'max_product_gallery', 3 );
	if( !empty( $fields ) ) {
		// Tack on a extra digit to max_size so we get the correct number of columns
		$max_size++;
		for( $i = 1; $i < $max_size; $i++ ) {
			if( isset( $fields['product_gallery'] ) )
				$fields[sprintf( 'product_gallery_%d', $i )] = 'on';
		}
	}
	return $fields;

}

function woo_ce_unique_product_gallery_columns( $columns = array(), $fields = array() ) {

	$max_size = woo_ce_get_option( 'max_product_gallery', 3 );
	if( !empty( $columns ) ) {
		// Tack on a extra digit to max_size so we get the correct number of columns
		$max_size++;
		for( $i = 1; $i < $max_size; $i++ ) {
			if( isset( $fields[sprintf( 'product_gallery_%d', $i )] ) )
				$columns[] = sprintf( apply_filters( 'woo_ce_unique_product_gallery_column', __( '%s #%d', 'woocommerce-exporter' ) ), woo_ce_get_product_field( 'product_gallery' ), $i );
		}
	}
	return $columns;

}
?>