<?php
function woo_ce_export_template_banner( $post ) {

	// Check the Post object exists
	if( isset( $post->post_type ) == false )
		return;

	// Limit to the Export Template Post Type
	$post_type = 'export_template';
	if( $post->post_type <> $post_type )
		return;

	if( apply_filters( 'woo_ce_export_template_banner_save_prompt', true ) )
		echo '<a href="' . esc_url( add_query_arg( array( 'page' => 'woo_ce', 'tab' => 'export_template' ), 'admin.php' ) ) . '" class="button confirm-button" data-confirm="' . __( 'The changes you made will be lost if you navigate away from this page before saving.', 'woocommerce-exporter' ) . '">' . __( 'Return to Export Templates', 'woocommerce-exporter' ) . '</a>';
	else
		echo '<a href="' . esc_url( add_query_arg( array( 'page' => 'woo_ce', 'tab' => 'export_template' ), 'admin.php' ) ) . '" class="button">' . __( 'Return to Export Templates', 'woocommerce-exporter' ) . '</a>';

}

function woo_ce_export_template_options_meta_box() {

	global $post;

	$post_ID = ( $post ? $post->ID : 0 );

	woo_ce_load_export_types();

	// General
	add_action( 'woo_ce_before_export_template_general_options', 'woo_ce_export_template_general_export_type' );

	// Filters
	add_action( 'woo_ce_before_export_template_fields_options', 'woo_ce_export_template_fields_product' );
	add_action( 'woo_ce_before_export_template_fields_options', 'woo_ce_export_template_fields_category' );
	add_action( 'woo_ce_before_export_template_fields_options', 'woo_ce_export_template_fields_tag' );
	add_action( 'woo_ce_before_export_template_fields_options', 'woo_ce_export_template_fields_brand' );
	add_action( 'woo_ce_before_export_template_fields_options', 'woo_ce_export_template_fields_order' );
	add_action( 'woo_ce_before_export_template_fields_options', 'woo_ce_export_template_fields_customer' );
	add_action( 'woo_ce_before_export_template_fields_options', 'woo_ce_export_template_fields_user' );
	add_action( 'woo_ce_before_export_template_fields_options', 'woo_ce_export_template_fields_review' );
	add_action( 'woo_ce_before_export_template_fields_options', 'woo_ce_export_template_fields_coupon' );
	add_action( 'woo_ce_before_export_template_fields_options', 'woo_ce_export_template_fields_subscription' );
	add_action( 'woo_ce_before_export_template_fields_options', 'woo_ce_export_template_fields_product_vendor' );
	add_action( 'woo_ce_before_export_template_fields_options', 'woo_ce_export_template_fields_commission' );
	add_action( 'woo_ce_before_export_template_fields_options', 'woo_ce_export_template_fields_shipping_class' );
	add_action( 'woo_ce_before_export_template_fields_options', 'woo_ce_export_template_fields_ticket' );

	$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';
?>
<div id="export_template_options" class="panel-wrap export_template_data">
	<div class="wc-tabs-back"></div>
	<ul class="coupon_data_tabs wc-tabs" style="display:none;">
<?php
	$coupon_data_tabs = apply_filters( 'woo_ce_export_template_data_tabs', array(
		'general' => array(
			'label'  => __( 'General', 'woocommerce' ),
			'target' => 'general_coupon_data',
			'class'  => 'general_coupon_data',
		),
		'fields' => array(
			'label'  => __( 'Fields', 'woocommerce' ),
			'target' => 'fields_coupon_data',
			'class'  => ''
		)
	) );

	foreach ( $coupon_data_tabs as $key => $tab ) { ?>
		<li class="<?php echo $key; ?>_options <?php echo $key; ?>_tab <?php echo implode( ' ' , (array) $tab['class'] ); ?>">
			<a href="#<?php echo $tab['target']; ?>"><?php echo esc_html( $tab['label'] ); ?></a>
		</li><?php
	} ?>
	</ul>
	<?php do_action( 'woo_ce_before_export_template_options', $post_ID ); ?>
	<div id="general_coupon_data" class="panel woocommerce_options_panel export_general_options">
		<?php do_action( 'woo_ce_before_export_template_general_options', $post_ID ); ?>
		<?php do_action( 'woo_ce_after_export_template_general_options', $post_ID ); ?>
	</div>
	<!-- #general_coupon_data -->

	<div id="fields_coupon_data" class="panel woocommerce_options_panel export_type_options ">
		<?php do_action( 'woo_ce_before_export_template_fields_options', $post_ID ); ?>
		<?php do_action( 'woo_ce_after_export_template_fields_options', $post_ID ); ?>
	</div>
	<!-- #fields_coupon_data -->

	<?php do_action( 'woo_ce_after_export_template_options', $post_ID ); ?>
	<div class="clear"></div>
</div>
<!-- #export_template_options -->
<?php
	wp_nonce_field( 'export_template', 'woo_ce_export' );

}

function woo_ce_export_template_general_export_type( $post_ID = 0 ) {

	$export_type = get_post_meta( $post_ID, '_export_type', true );
	$export_types = woo_ce_get_export_types();

	ob_start(); ?>
<div class="options_group">
	<p class="form-field">Select an Export type then switch to the Fields tab to select your export field preferences. You can save export field preferences for multiple Export Types. Click Publish or Update to save changes.</p>
	<p class="form-field discount_type_field ">
		<label for="export_type"><?php _e( 'Export type', 'woocommerce-exporter' ); ?> </label>
<?php if( !empty( $export_types ) ) { ?>
		<select id="export_type" name="export_type" class="select short">
	<?php foreach( $export_types as $key => $type ) { ?>
			<option value="<?php echo $key; ?>"<?php selected( $export_type, $key ); ?>><?php echo $type; ?></option>
	<?php } ?>
		</select>
		<img class="help_tip" data-tip="<?php _e( 'Select the export type fields you want to manage.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
<?php } else { ?>
		<?php _e( 'No export types were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
	</p>
</div>
<?php
	ob_end_flush();

}

function woo_ce_export_template_fields_product( $post_ID = 0 ) {

	$fields = woo_ce_get_product_fields( 'full', $post_ID );

	ob_start(); ?>
<div class="export-options product-options">
	<div class="options_group">
		<div class="form-field discount_type_field ">
			<p class="form-field discount_type_field ">
				<label><?php _e( 'Product fields', 'woocommerce-exporter' ); ?></label>
			</p>
<?php if( !empty( $fields ) ) { ?>
			<table id="product-fields" class="ui-sortable">
	<?php foreach( $fields as $field ) { ?>
				<tr id="product-<?php echo $field['reset']; ?>">
					<td>
						<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>><input type="checkbox" name="product_fields[<?php echo $field['name']; ?>]" class="product_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?> /> <?php echo $field['label']; ?></label>
						<input type="hidden" name="product_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
					</td>
				</tr>
	<?php } ?>
			</table>
			<!-- #product-fields -->
<?php } else { ?>
			<p><?php _e( 'No Product fields were found.', 'woocommerce-exporter' ); ?></p>
<?php } ?>
		</div>
		<!-- .form-field -->
	</div>
	<!-- .options_group -->
</div>
<!-- .export-options -->
<?php
	ob_end_flush();

}

function woo_ce_export_template_fields_category( $post_ID = 0 ) {

	$fields = woo_ce_get_category_fields( 'full', $post_ID );

	ob_start(); ?>
<div class="export-options category-options">
	<div class="options_group">
		<div class="form-field discount_type_field ">
			<p class="form-field discount_type_field ">
				<label><?php _e( 'Category fields', 'woocommerce-exporter' ); ?></label>
			</p>
<?php if( !empty( $fields ) ) { ?>
			<table id="category-fields" class="ui-sortable">
	<?php foreach( $fields as $field ) { ?>
				<tr id="category-<?php echo $field['reset']; ?>">
					<td>
						<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>><input type="checkbox" name="category_fields[<?php echo $field['name']; ?>]" class="category_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?> /> <?php echo $field['label']; ?></label>
						<input type="hidden" name="category_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
					</td>
				</tr>
	<?php } ?>
			</table>
			<!-- #category-fields -->
<?php } else { ?>
			<p><?php _e( 'No Category fields were found.', 'woocommerce-exporter' ); ?></p>
<?php } ?>
		</div>
		<!-- .form-field -->
	</div>
	<!-- .options_group -->
</div>
<!-- .export-options -->
<?php
	ob_end_flush();

}

function woo_ce_export_template_fields_tag( $post_ID = 0 ) {

	$fields = woo_ce_get_tag_fields( 'full', $post_ID );

	ob_start(); ?>
<div class="export-options tag-options">
	<div class="options_group">
		<div class="form-field discount_type_field ">
			<p class="form-field discount_type_field ">
				<label><?php _e( 'Tag fields', 'woocommerce-exporter' ); ?></label>
			</p>
<?php if( !empty( $fields ) ) { ?>
			<table id="tag-fields" class="ui-sortable">
	<?php foreach( $fields as $field ) { ?>
				<tr id="tag-<?php echo $field['reset']; ?>">
					<td>
						<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>><input type="checkbox" name="tag_fields[<?php echo $field['name']; ?>]" class="tag_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?> /> <?php echo $field['label']; ?></label>
						<input type="hidden" name="tag_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
					</td>
				</tr>
	<?php } ?>
			</table>
			<!-- #tag-fields -->
<?php } else { ?>
			<p><?php _e( 'No Tag fields were found.', 'woocommerce-exporter' ); ?></p>
<?php } ?>
		</div>
		<!-- .form-field -->
	</div>
	<!-- .options_group -->
</div>
<!-- .export-options -->
<?php
	ob_end_flush();

}

function woo_ce_export_template_fields_brand( $post_ID = 0 ) {

	$fields = woo_ce_get_brand_fields( 'full', $post_ID );

	ob_start(); ?>
<div class="export-options brand-options">
	<div class="options_group">
		<div class="form-field discount_type_field ">
			<p class="form-field discount_type_field ">
				<label><?php _e( 'Brand fields', 'woocommerce-exporter' ); ?></label>
			</p>
<?php if( !empty( $fields ) ) { ?>
			<table id="brand-fields" class="ui-sortable">
	<?php foreach( $fields as $field ) { ?>
				<tr id="brand-<?php echo $field['reset']; ?>">
					<td>
						<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>><input type="checkbox" name="brand_fields[<?php echo $field['name']; ?>]" class="brand_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?> /> <?php echo $field['label']; ?></label>
						<input type="hidden" name="brand_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
					</td>
				</tr>
	<?php } ?>
			</table>
			<!-- #brand-fields -->
<?php } else { ?>
			<p><?php _e( 'No Brand fields were found.', 'woocommerce-exporter' ); ?></p>
<?php } ?>
		</div>
		<!-- .form-field -->
	</div>
	<!-- .options_group -->
</div>
<!-- .export-options -->
<?php
	ob_end_flush();

}

function woo_ce_export_template_fields_order( $post_ID = 0 ) {

	$fields = woo_ce_get_order_fields( 'full', $post_ID );

	ob_start(); ?>
<div class="export-options order-options">
	<div class="options_group">
		<div class="form-field discount_type_field">
<?php if( !empty( $fields ) ) { ?>
			<table id="order-fields" class="ui-sortable">
				<tbody>
	<?php foreach( $fields as $field ) { ?>
					<tr id="order-<?php echo $field['reset']; ?>">
						<td>
							<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>><input type="checkbox" name="order_fields[<?php echo $field['name']; ?>]" class="order_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?> /> <?php echo $field['label']; ?></label>
							<input type="hidden" name="order_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
						</td>
					</tr>
	<?php } ?>
				</tbody>
			</table>
			<!-- #order-fields -->
<?php } else { ?>
			<p><?php _e( 'No Order fields were found.', 'woocommerce-exporter' ); ?></p>
<?php } ?>
		</div>
		<!-- .form-field -->
	</div>
	<!-- .options_group -->
</div>
<!-- .export-options -->
<?php
	ob_end_flush();

}

function woo_ce_export_template_fields_customer( $post_ID = 0 ) {

	$fields = woo_ce_get_customer_fields( 'full', $post_ID );

	ob_start(); ?>
<div class="export-options customer-options">
	<div class="options_group">
		<div class="form-field discount_type_field ">
			<p class="form-field discount_type_field ">
				<label><?php _e( 'Customer fields', 'woocommerce-exporter' ); ?></label>
			</p>
<?php if( !empty( $fields ) ) { ?>
			<table id="customer-fields" class="ui-sortable">
	<?php foreach( $fields as $field ) { ?>
				<tr id="customer-<?php echo $field['reset']; ?>">
					<td>
						<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>><input type="checkbox" name="customer_fields[<?php echo $field['name']; ?>]" class="customer_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?> /> <?php echo $field['label']; ?></label>
						<input type="hidden" name="customer_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
					</td>
				</tr>
	<?php } ?>
			</table>
			<!-- #customer-fields -->
<?php } else { ?>
			<p><?php _e( 'No Customer fields were found.', 'woocommerce-exporter' ); ?></p>
<?php } ?>
		</div>
		<!-- .form-field -->
	</div>
	<!-- .options_group -->
</div>
<!-- .export-options -->
<?php
	ob_end_flush();

}

function woo_ce_export_template_fields_user( $post_ID = 0 ) {

	$fields = woo_ce_get_user_fields( 'full', $post_ID );

	ob_start(); ?>
<div class="export-options user-options">
	<div class="options_group">
		<div class="form-field discount_type_field ">
			<p class="form-field discount_type_field ">
				<label><?php _e( 'User fields', 'woocommerce-exporter' ); ?></label>
			</p>
<?php if( !empty( $fields ) ) { ?>
			<table id="user-fields" class="ui-sortable">
	<?php foreach( $fields as $field ) { ?>
				<tr id="user-<?php echo $field['reset']; ?>">
					<td>
						<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>><input type="checkbox" name="user_fields[<?php echo $field['name']; ?>]" class="user_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?> /> <?php echo $field['label']; ?></label>
						<input type="hidden" name="user_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
					</td>
				</tr>
	<?php } ?>
			</table>
			<!-- #user-fields -->
<?php } else { ?>
			<p><?php _e( 'No User fields were found.', 'woocommerce-exporter' ); ?></p>
<?php } ?>
		</div>
		<!-- .form-field -->
	</div>
	<!-- .options_group -->
</div>
<!-- .export-options -->
<?php
	ob_end_flush();

}

function woo_ce_export_template_fields_review( $post_ID = 0 ) {

	$fields = woo_ce_get_review_fields( 'full', $post_ID );

	ob_start(); ?>
<div class="export-options review-options">
	<div class="options_group">
		<div class="form-field discount_type_field ">
			<p class="form-field discount_type_field ">
				<label><?php _e( 'Review fields', 'woocommerce-exporter' ); ?></label>
			</p>
<?php if( !empty( $fields ) ) { ?>
			<table id="review-fields" class="ui-sortable">
	<?php foreach( $fields as $field ) { ?>
				<tr id="review-<?php echo $field['reset']; ?>">
					<td>
						<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>><input type="checkbox" name="review_fields[<?php echo $field['name']; ?>]" class="review_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?> /> <?php echo $field['label']; ?></label>
						<input type="hidden" name="review_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
					</td>
				</tr>
	<?php } ?>
			</table>
			<!-- #review-fields -->
<?php } else { ?>
			<p><?php _e( 'No Review fields were found.', 'woocommerce-exporter' ); ?></p>
<?php } ?>
		</div>
		<!-- .form-field -->
	</div>
	<!-- .options_group -->
</div>
<!-- .export-options -->
<?php
	ob_end_flush();

}

function woo_ce_export_template_fields_coupon( $post_ID = 0 ) {

	$fields = woo_ce_get_coupon_fields( 'full', $post_ID );

	ob_start(); ?>
<div class="export-options coupon-options">
	<div class="options_group">
		<div class="form-field discount_type_field ">
			<p class="form-field discount_type_field ">
				<label><?php _e( 'Coupon fields', 'woocommerce-exporter' ); ?></label>
			</p>
<?php if( !empty( $fields ) ) { ?>
			<table id="coupon-fields" class="ui-sortable">
	<?php foreach( $fields as $field ) { ?>
				<tr id="coupon-<?php echo $field['reset']; ?>">
					<td>
						<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>><input type="checkbox" name="coupon_fields[<?php echo $field['name']; ?>]" class="coupon_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?> /> <?php echo $field['label']; ?></label>
						<input type="hidden" name="coupon_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
					</td>
				</tr>
	<?php } ?>
			</table>
			<!-- #coupon-fields -->
<?php } else { ?>
			<p><?php _e( 'No Coupon fields were found.', 'woocommerce-exporter' ); ?></p>
<?php } ?>
		</div>
		<!-- .form-field -->
	</div>
	<!-- .options_group -->
</div>
<!-- .export-options -->
<?php
	ob_end_flush();

}

function woo_ce_export_template_fields_subscription( $post_ID = 0 ) {

	$fields = woo_ce_get_subscription_fields( 'full', $post_ID );

	ob_start(); ?>
<div class="export-options subscription-options">
	<div class="options_group">
		<div class="form-field discount_type_field ">
			<p class="form-field discount_type_field ">
				<label><?php _e( 'Subscription fields', 'woocommerce-exporter' ); ?></label>
			</p>
<?php if( !empty( $fields ) ) { ?>
			<table id="subscription-fields" class="ui-sortable">
	<?php foreach( $fields as $field ) { ?>
				<tr id="subscription-<?php echo $field['reset']; ?>">
					<td>
						<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>><input type="checkbox" name="subscription_fields[<?php echo $field['name']; ?>]" class="subscription_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?> /> <?php echo $field['label']; ?></label>
						<input type="hidden" name="subscription_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
					</td>
				</tr>
	<?php } ?>
			</table>
			<!-- #subscription-fields -->
<?php } else { ?>
			<p><?php _e( 'No Subscription fields were found.', 'woocommerce-exporter' ); ?></p>
<?php } ?>
		</div>
		<!-- .form-field -->
	</div>
	<!-- .options_group -->
</div>
<!-- .export-options -->
<?php
	ob_end_flush();

}

function woo_ce_export_template_fields_product_vendor( $post_ID = 0 ) {

	$fields = woo_ce_get_product_vendor_fields( 'full', $post_ID );

	ob_start(); ?>
<div class="export-options product_vendor-options">
	<div class="options_group">
		<div class="form-field discount_type_field ">
			<p class="form-field discount_type_field ">
				<label><?php _e( 'Product Vendor fields', 'woocommerce-exporter' ); ?></label>
			</p>
<?php if( !empty( $fields ) ) { ?>
			<table id="product_vendor-fields" class="ui-sortable">
	<?php foreach( $fields as $field ) { ?>
				<tr id="product_vendor-<?php echo $field['reset']; ?>">
					<td>
						<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>><input type="checkbox" name="product_vendor_fields[<?php echo $field['name']; ?>]" class="product_vendor_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?> /> <?php echo $field['label']; ?></label>
						<input type="hidden" name="product_vendor_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
					</td>
				</tr>
	<?php } ?>
			</table>
			<!-- #product_vendor-fields -->
<?php } else { ?>
			<p><?php _e( 'No Product Vendor fields were found.', 'woocommerce-exporter' ); ?></p>
<?php } ?>
		</div>
		<!-- .form-field -->
	</div>
	<!-- .options_group -->
</div>
<!-- .export-options -->
<?php
	ob_end_flush();

}

function woo_ce_export_template_fields_commission( $post_ID = 0 ) {

	$fields = woo_ce_get_commission_fields( 'full', $post_ID );

	ob_start(); ?>
<div class="export-options commission-options">
	<div class="options_group">
		<div class="form-field discount_type_field ">
			<p class="form-field discount_type_field ">
				<label><?php _e( 'Commission fields', 'woocommerce-exporter' ); ?></label>
			</p>
<?php if( !empty( $fields ) ) { ?>
			<table id="commission-fields" class="ui-sortable">
	<?php foreach( $fields as $field ) { ?>
				<tr id="commission-<?php echo $field['reset']; ?>">
					<td>
						<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>><input type="checkbox" name="commission_fields[<?php echo $field['name']; ?>]" class="commission_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?> /> <?php echo $field['label']; ?></label>
						<input type="hidden" name="commission_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
					</td>
				</tr>
	<?php } ?>
			</table>
			<!-- #commission-fields -->
<?php } else { ?>
			<p><?php _e( 'No Commission fields were found.', 'woocommerce-exporter' ); ?></p>
<?php } ?>
		</div>
		<!-- .form-field -->
	</div>
	<!-- .options_group -->
</div>
<!-- .export-options -->
<?php
	ob_end_flush();

}

function woo_ce_export_template_fields_shipping_class( $post_ID = 0 ) {

	$fields = woo_ce_get_shipping_class_fields( 'full', $post_ID );

	ob_start(); ?>
<div class="export-options shipping_class-options">
	<div class="options_group">
		<div class="form-field discount_type_field ">
			<p class="form-field discount_type_field ">
				<label><?php _e( 'Shipping Class fields', 'woocommerce-exporter' ); ?></label>
			</p>
<?php if( !empty( $fields ) ) { ?>
			<table id="shipping_class-fields" class="ui-sortable">
	<?php foreach( $fields as $field ) { ?>
				<tr id="shipping_class-<?php echo $field['reset']; ?>">
					<td>
						<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>><input type="checkbox" name="shipping_class_fields[<?php echo $field['name']; ?>]" class="shipping_class_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?> /> <?php echo $field['label']; ?></label>
						<input type="hidden" name="shipping_class_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
					</td>
				</tr>
	<?php } ?>
			</table>
			<!-- #shipping_class-fields -->
<?php } else { ?>
			<p><?php _e( 'No Shipping Class fields were found.', 'woocommerce-exporter' ); ?></p>
<?php } ?>
		</div>
		<!-- .form-field -->
	</div>
	<!-- .options_group -->
</div>
<!-- .export-options -->
<?php
	ob_end_flush();

}

function woo_ce_export_template_fields_ticket( $post_ID = 0 ) {

	$fields = woo_ce_get_ticket_fields( 'full', $post_ID );

	ob_start(); ?>
<div class="export-options ticket-options">
	<div class="options_group">
		<div class="form-field discount_type_field ">
			<p class="form-field discount_type_field ">
				<label><?php _e( 'Ticket fields', 'woocommerce-exporter' ); ?></label>
			</p>
<?php if( !empty( $fields ) ) { ?>
			<table id="ticket-fields" class="ui-sortable">
	<?php foreach( $fields as $field ) { ?>
				<tr id="ticket-<?php echo $field['reset']; ?>">
					<td>
						<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>><input type="checkbox" name="ticket_fields[<?php echo $field['name']; ?>]" class="ticket_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?> /> <?php echo $field['label']; ?></label>
						<input type="hidden" name="ticket_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
					</td>
				</tr>
	<?php } ?>
			</table>
			<!-- #ticket-fields -->
<?php } else { ?>
			<p><?php _e( 'No Ticket fields were found.', 'woocommerce-exporter' ); ?></p>
<?php } ?>
		</div>
		<!-- .form-field -->
	</div>
	<!-- .options_group -->
</div>
<!-- .export-options -->
<?php
	ob_end_flush();

}

function woo_ce_export_template_save( $post_ID = 0 ) {

	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;

	// Make sure we play nice with other WooCommerce and WordPress exporters
	if( !isset( $_POST['woo_ce_export'] ) )
		return;

	$post_type = 'export_template';
	check_admin_referer( $post_type, 'woo_ce_export' );

	// General
	$export_type = sanitize_text_field( $_POST['export_type'] );
	update_post_meta( $post_ID, '_export_type', $export_type );

	// Fields
	$export_types = woo_ce_get_export_types();
	if( !empty( $export_types ) ) {
		$export_types = array_keys( $export_types );
		foreach( $export_types as $export_type ) {
			$fields = ( isset( $_POST[sprintf( '%s_fields', $export_type )] ) ? array_map( 'sanitize_text_field', $_POST[sprintf( '%s_fields', $export_type )] ) : false );
			$sorting = ( isset( $_POST[sprintf( '%s_fields_order', $export_type )] ) ? array_map( 'absint', $_POST[sprintf( '%s_fields_order', $export_type )] ) : false );
			if( !empty( $fields ) ) {
				update_post_meta( $post_ID, sprintf( '_%s_fields', $export_type ), $fields );
				update_post_meta( $post_ID, sprintf( '_%s_sorting', $export_type ), $sorting );
			} else {
				delete_post_meta( $post_ID, sprintf( '_%s_fields', $export_type ) );
				delete_post_meta( $post_ID, sprintf( '_%s_sorting', $export_type ) );
			}
		}
	}

}
?>