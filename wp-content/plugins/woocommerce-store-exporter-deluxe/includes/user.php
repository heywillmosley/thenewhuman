<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	if( !function_exists( 'woo_ce_get_export_type_user_count' ) ) {
		function woo_ce_get_export_type_user_count() {

			$count = 0;

			// Override for WordPress MultiSite
			if( woo_ce_is_network_admin() ) {
				$sites = wp_get_sites();
				foreach( $sites as $site ) {
					switch_to_blog( $site['blog_id'] );
					if( $users = count_users() )
						$count += ( isset( $users['total_users'] ) ? $users['total_users'] : 0 );
					restore_current_blog();
				}
				return $count;
			}

			// Check if the existing Transient exists
			$cached = get_transient( WOO_CD_PREFIX . '_user_count' );
			if( $cached == false ) {
				if( $users = count_users() )
					$count = ( isset( $users['total_users'] ) ? $users['total_users'] : 0 );
				set_transient( WOO_CD_PREFIX . '_user_count', $count, HOUR_IN_SECONDS );
			} else {
				$count = $cached;
			}
			return $count;

		}
	}

	// HTML template for Filter Users by User Role widget on Store Exporter screen
	function woo_ce_users_filter_by_user_role() {

		$user_roles = woo_ce_get_user_roles();

		ob_start(); ?>
<p><label><input type="checkbox" id="users-filters-user_role" /> <?php _e( 'Filter Users by User Role', 'woocommerce-exporter' ); ?></label></p>
<div id="export-users-filters-user_role" class="separator">
	<ul>
		<li>
<?php if( !empty( $user_roles ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a User Role...', 'woocommerce-exporter' ); ?>" name="user_filter_user_role[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $user_roles as $key => $user_role ) { ?>
				<option value="<?php echo $key; ?>"><?php echo ucfirst( $user_role['name'] ); ?></option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No User Roles were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the User Roles you want to filter exported Users by. Default is to include all User Role options.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-users-filters-user_role -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Users by Date Registered widget on Store Exporter screen
	function woo_ce_users_filter_by_date_registered() {

		$date_format = 'd/m/Y';
		$user_dates_from = woo_ce_get_user_first_date( $date_format );
		$user_dates_to = date( $date_format );

		ob_start(); ?>
<p><label><input type="checkbox" id="users-filters-date_registered" /> <?php _e( 'Filter Users by Date Registered', 'woocommerce-exporter' ); ?></label></p>
<div id="export-users-filters-date_registered" class="separator">
	<ul>
		<li>
			<label><input type="radio" name="user_dates_filter" value="manual" /> <?php _e( 'Fixed date', 'woocommerce-exporter' ); ?></label>
			<div style="margin-top:0.2em;">
				<input type="text" size="10" maxlength="10" id="user_dates_from" name="user_dates_from" value="<?php echo esc_attr( $user_dates_from ); ?>" class="text code datepicker user_export" /> to <input type="text" size="10" maxlength="10" id="user_dates_to" name="user_dates_to" value="<?php echo esc_attr( $user_dates_to ); ?>" class="text code datepicker user_export" />
				<p class="description"><?php _e( 'Filter the dates of Users to be included in the export. Default is the date of the first User registered to today in the date format <code>DD/MM/YYYY</code>.', 'woocommerce-exporter' ); ?></p>
			</div>
		</li>
	</ul>
</div>
<!-- #export-users-filters-date_registered -->
<?php
		ob_end_flush();

	}

	// HTML template for jump link to Store Exporter screen
	function woo_ce_users_custom_fields_link() {

		ob_start(); ?>
<div id="export-users-custom-fields-link">
	<p><a href="#export-users-custom-fields"><?php _e( 'Manage Custom User Fields', 'woocommerce-exporter' ); ?></a></p>
</div>
<!-- #export-users-custom-fields-link -->
<?php
		ob_end_flush();

	}

	// HTML template for User Sorting widget on Store Exporter screen
	function woo_ce_user_sorting() {

		$orderby = woo_ce_get_option( 'user_orderby', 'ID' );
		$order = woo_ce_get_option( 'user_order', 'ASC' );

		ob_start(); ?>
<p><label><?php _e( 'User Sorting', 'woocommerce-exporter' ); ?></label></p>
<div>
	<select name="user_orderby">
		<option value="ID"<?php selected( 'ID', $orderby ); ?>><?php _e( 'User ID', 'woocommerce-exporter' ); ?></option>
		<option value="display_name"<?php selected( 'display_name', $orderby ); ?>><?php _e( 'Display Name', 'woocommerce-exporter' ); ?></option>
		<option value="user_name"<?php selected( 'user_name', $orderby ); ?>><?php _e( 'Name', 'woocommerce-exporter' ); ?></option>
		<option value="user_login"<?php selected( 'user_login', $orderby ); ?>><?php _e( 'Username', 'woocommerce-exporter' ); ?></option>
		<option value="nicename"<?php selected( 'nicename', $orderby ); ?>><?php _e( 'Nickname', 'woocommerce-exporter' ); ?></option>
		<option value="email"<?php selected( 'email', $orderby ); ?>><?php _e( 'E-mail', 'woocommerce-exporter' ); ?></option>
		<option value="url"<?php selected( 'url', $orderby ); ?>><?php _e( 'Website', 'woocommerce-exporter' ); ?></option>
		<option value="registered"<?php selected( 'registered', $orderby ); ?>><?php _e( 'Date Registered', 'woocommerce-exporter' ); ?></option>
		<option value="rand"<?php selected( 'rand', $orderby ); ?>><?php _e( 'Random', 'woocommerce-exporter' ); ?></option>
	</select>
	<select name="user_order">
		<option value="ASC"<?php selected( 'ASC', $order ); ?>><?php _e( 'Ascending', 'woocommerce-exporter' ); ?></option>
		<option value="DESC"<?php selected( 'DESC', $order ); ?>><?php _e( 'Descending', 'woocommerce-exporter' ); ?></option>
	</select>
	<p class="description"><?php _e( 'Select the sorting of Users within the exported file. By default this is set to export User by User ID in Desending order.', 'woocommerce-exporter' ); ?></p>
</div>
<?php
		ob_end_flush();

	}

	// HTML template for Custom Users widget on Store Exporter screen
	function woo_ce_users_custom_fields() {

		if( $custom_users = woo_ce_get_option( 'custom_users', '' ) )
			$custom_users = implode( "\n", $custom_users );

		$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

		ob_start(); ?>
<form method="post" id="export-users-custom-fields" class="export-options user-options">
	<div id="poststuff">

		<div class="postbox" id="export-options user-options">
			<h3 class="hndle"><?php _e( 'Custom User Fields', 'woocommerce-exporter' ); ?></h3>
			<div class="inside">
				<p class="description"><?php _e( 'To include additional custom User meta in the Export Users table above fill the Users text box then click Save Custom Fields. The saved meta will appear as new export fields to be selected from the User Fields list.', 'woocommerce-exporter' ); ?></p>
				<p class="description"><?php printf( __( 'For more information on exporting custom User meta consult our <a href="%s" target="_blank">online documentation</a>.', 'woocommerce-exporter' ), $troubleshooting_url ); ?></p>
				<table class="form-table">

					<tr>
						<th>
							<label><?php _e( 'User meta', 'woocommerce-exporter' ); ?></label>
						</th>
						<td>
							<textarea name="custom_users" rows="5" cols="70"><?php echo esc_textarea( $custom_users ); ?></textarea>
							<p class="description"><?php _e( 'Include additional custom User meta in your export file by adding each custom User meta name to a new line above.<br />For example: <code>Customer UA (new line) Customer IP Address</code>', 'woocommerce-exporter' ); ?></p>
						</td>
					</tr>

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
<!-- #export-users-custom-fields -->
<?php
		ob_end_flush();

	}

	function woo_ce_user_dataset_args( $args, $export_type = '' ) {

		// Check if we're dealing with the User Export Type
		if( $export_type <> 'user' )
			return $args;

		// Merge in the form data for this dataset
		$defaults = array(
			'user_user_roles' => ( isset( $_POST['user_filter_user_role'] ) ? woo_ce_format_user_role_filters( array_map( 'sanitize_text_field', $_POST['user_filter_user_role'] ) ) : false ),
			'user_dates_filter' => ( isset( $_POST['user_dates_filter'] ) ? sanitize_text_field( $_POST['user_dates_filter'] ) : false ),
			'user_dates_from' => ( isset( $_POST['user_dates_from'] ) ? woo_ce_format_order_date( sanitize_text_field( $_POST['user_dates_from'] ) ) : '' ),
			'user_dates_to' => ( isset( $_POST['user_dates_to'] ) ? woo_ce_format_order_date( sanitize_text_field( $_POST['user_dates_to'] ) ) : '' ),
			'user_orderby' => ( isset( $_POST['user_orderby'] ) ? sanitize_text_field( $_POST['user_orderby'] ) : false ),
			'user_order' => ( isset( $_POST['user_order'] ) ? sanitize_text_field( $_POST['user_order'] ) : false )
		);
		$args = wp_parse_args( $args, $defaults );

		// Save dataset export specific options
		// User Role
		// Date
		if( $args['user_orderby'] <> woo_ce_get_option( 'user_orderby' ) )
			woo_ce_update_option( 'user_orderby', $args['user_orderby'] );
		if( $args['user_order'] <> woo_ce_get_option( 'user_order' ) )
			woo_ce_update_option( 'user_order', $args['user_order'] );

		return $args;

	}
	add_filter( 'woo_ce_extend_dataset_args', 'woo_ce_user_dataset_args', 10, 2 );

	/* End of: WordPress Administration */

}

function woo_ce_cron_user_dataset_args( $args, $export_type = '', $is_scheduled = 0 ) {

	// Check if we're dealing with the User Export Type
	if( $export_type <> 'user' )
		return $args;

	$user_filter_role = false;

	if( $is_scheduled ) {
		$scheduled_export = ( $is_scheduled ? absint( get_transient( WOO_CD_PREFIX . '_scheduled_export_id' ) ) : 0 );
		// User Role
		$user_filter_role = get_post_meta( $scheduled_export, '_filter_user_role', true );
		$user_filter_role = ( !empty( $user_filter_role ) ? array_map( 'sanitize_text_field', $user_filter_role ) : false );
	} else {
		// User Role
		if( isset( $_GET['user_user_role'] ) ) {
			$user_filter_role = sanitize_text_field( $_GET['user_user_role'] );
			$user_filter_role = explode( ',', $user_filter_role );
		}
	}

	// Merge in the form data for this dataset
	$defaults = array(
		'user_user_roles' => $user_filter_role
	);
	$args = wp_parse_args( $args, $defaults );

	return $args;

}
add_filter( 'woo_ce_extend_cron_dataset_args', 'woo_ce_cron_user_dataset_args', 10, 3 );

// Returns a list of User export columns
function woo_ce_get_user_fields( $format = 'full', $post_ID = 0 ) {

	$export_type = 'user';

	$fields = array();
	$fields[] = array(
		'name' => 'user_id',
		'label' => __( 'User ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'user_name',
		'label' => __( 'Username', 'woocommerce-exporter' )
	);
	if( apply_filters( 'woo_ce_enable_user_password', false ) ) {
		$fields[] = array(
			'name' => 'password',
			'label' => __( 'Password (Encrypted)', 'woocommerce-exporter' )
		);
	}
	$fields[] = array(
		'name' => 'user_role',
		'label' => __( 'User Role', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'first_name',
		'label' => __( 'First Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'last_name',
		'label' => __( 'Last Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'full_name',
		'label' => __( 'Full Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'nick_name',
		'label' => __( 'Nickname', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'email',
		'label' => __( 'E-mail', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'orders',
		'label' => __( 'Orders', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'money_spent',
		'label' => __( 'Money Spent', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'url',
		'label' => __( 'Website', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'date_registered',
		'label' => __( 'Date Registered', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'description',
		'label' => __( 'Biographical Info', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'aim',
		'label' => __( 'AIM', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'yim',
		'label' => __( 'Yahoo IM', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'jabber',
		'label' => __( 'Jabber / Google Talk', 'woocommerce-exporter' )
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
function woo_ce_override_user_field_labels( $fields = array() ) {

	$labels = woo_ce_get_option( 'user_labels', array() );
	if( !empty( $labels ) ) {
		foreach( $fields as $key => $field ) {
			if( isset( $labels[$field['name']] ) )
				$fields[$key]['label'] = $labels[$field['name']];
		}
	}
	return $fields;

}
add_filter( 'woo_ce_user_fields', 'woo_ce_override_user_field_labels', 11 );

// Returns the export column header label based on an export column slug
function woo_ce_get_user_field( $name = null, $format = 'name' ) {

	$output = '';
	if( $name ) {
		$fields = woo_ce_get_user_fields();
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

// Returns a list of User IDs
function woo_ce_get_users( $export_args = array() ) {

	global $wpdb, $export;

	$limit_volume = 0;
	$offset = 0;
	$orderby = 'login';
	$order = 'ASC';
	$user_roles = false;

	if( $export_args ) {
		$user_roles = ( isset( $export_args['user_user_roles'] ) ? $export_args['user_user_roles'] : 0 );
		$limit_volume = ( isset( $export_args['limit_volume'] ) ? $export_args['limit_volume'] : 0 );
		if( $limit_volume == -1 )
			$limit_volume = 0;
		$offset = ( isset( $export_args['offset'] ) ? $export_args['offset'] : 0 );
		$orderby = ( isset( $export_args['user_orderby'] ) ? $export_args['user_orderby'] : 'login' );
		$order = ( isset( $export_args['user_order'] ) ? $export_args['user_order'] : 'ASC' );
		$user_dates_filter = ( isset( $export_args['user_dates_filter'] ) ? $export_args['user_dates_filter'] : false );
		switch( $user_dates_filter ) {

			case 'manual':
				$user_dates_from = woo_ce_format_order_date( $export_args['user_dates_from'] );
				$user_dates_to = woo_ce_format_order_date( $export_args['user_dates_to'] );
				$date_format = 'd/m/Y';
				// WP_User_Query only accepts YY-m-D so we must format dates to that
				if( $date_format <> 'Y/m/d' ) {
					$date_format = woo_ce_format_order_date( $date_format );
					if( function_exists( 'date_create_from_format' ) && function_exists( 'date_format' ) ) {
						if( $user_dates_from = date_create_from_format( $date_format, $user_dates_from ) )
							$user_dates_from = date_format( $user_dates_from, 'Y-m-d 00:00:00' );
						if( $user_dates_to = date_create_from_format( $date_format, $user_dates_to ) )
							$user_dates_to = date_format( $user_dates_to, 'Y-m-d 23:59:59' );
					}
				}
				break;

			default:
				$user_dates_from = false;
				$user_dates_to = false;
				break;

		}
	}
	$args = array(
		'offset' => $offset,
		'number' => $limit_volume,
		'order' => $order,
		'offset' => $offset,
		'fields' => 'ids'
	);

	// Filter Order dates
	if( !empty( $user_dates_from ) && !empty( $user_dates_to ) ) {
		$args['date_query'] = array(
			array(
				'before' => $user_dates_to,
				'after' => $user_dates_from,
				'inclusive' => true
			)
		);
	}

	// Allow other developers to bake in their own filters
	$args = apply_filters( 'woo_ce_get_users_args', $args, $export_args );

	if( $user_ids = new WP_User_Query( $args ) ) {
		$users = array();
		foreach( $user_ids->results as $user_id ) {

			$user = new WP_User( $user_id );

			if( !empty( $user_roles ) ) {
				if( count( array_intersect( $user_roles, $user->roles ) ) == 0 ) {
					unset( $user, $user_id );
					continue;
				}
			}

			if( isset( $user_id ) )
				$users[] = $user_id;

		}
		// Only populate the $export Global if it is an export
		if( isset( $export ) )
			$export->total_rows = count( $users );

		return $users;
	}

}

function woo_ce_get_user_data( $user_id = 0, $args = array() ) {

	$defaults = array();
	$args = wp_parse_args( $args, $defaults );

	// Get User details
	$user_data = get_userdata( $user_id );

	$user = new stdClass;
	if( $user_data !== false ) {
		$user->ID = $user_data->ID;
		$user->user_id = $user_data->ID;
		$user->user_name = $user_data->user_login;
		if( apply_filters( 'woo_ce_enable_user_password', false ) ) {
			// We must use WP_User to fetch the Password
			$wp_user_data = new WP_User( $user_id );
			if( $wp_user_data !== false )
				$user->password = $wp_user_data->data->user_pass;
			unset( $wp_user_data );
		}
		$user->user_role = ( isset( $user_data->roles[0] ) ? $user_data->roles[0] : false );
		$user->first_name = $user_data->first_name;
		$user->last_name = $user_data->last_name;
		$user->full_name = sprintf( apply_filters( 'woo_ce_get_user_data_full_name', '%s %s' ), $user->first_name, $user->last_name );
		$user->nick_name = $user_data->user_nicename;
		$user->email = $user_data->user_email;
		$user->orders = ( function_exists( 'wc_get_customer_order_count' ) ? wc_get_customer_order_count( $user->ID ) : 0 );
		$user->money_spent = ( function_exists( 'wc_get_customer_total_spent' ) ? woo_ce_format_price( wc_get_customer_total_spent( $user->ID ) ) : 0 );
		$user->url = $user_data->user_url;
		$user->date_registered = $user_data->user_registered;
		$user->description = $user_data->description;
		$user->aim = $user_data->aim;
		$user->yim = $user_data->yim;
		$user->jabber = $user_data->jabber;
	}

	// Allow Plugin/Theme authors to add support for additional User columns
	return apply_filters( 'woo_ce_user', $user );
	
}

function woo_ce_export_dataset_override_user( $output = null, $export_type = null ) {

	global $export;

	if( $users = woo_ce_get_users( $export->args ) ) {
		$export->total_rows = count( $users );
		// XML, RSS export
		if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
			if( !empty( $export->fields ) ) {
				foreach( $users as $user ) {
					if( $export->export_format == 'xml' )
						$child = $output->addChild( apply_filters( 'woo_ce_export_xml_user_node', sanitize_key( $export_type ) ) );
					else if( $export->export_format == 'rss' )
						$child = $output->addChild( 'item' );
					$child->addAttribute( 'id', ( isset( $user->user_id ) ? $user->user_id : '' ) );
					$user = woo_ce_get_user_data( $user, $export->args, array_keys( $export->fields ) );
					foreach( array_keys( $export->fields ) as $key => $field ) {
						if( isset( $user->$field ) ) {
							if( !is_array( $field ) ) {
								if( woo_ce_is_xml_cdata( $user->$field ) )
									$child->addChild( sanitize_key( $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $user->$field ) ) );
								else
									$child->addChild( sanitize_key( $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $user->$field ) ) );
							}
						}
					}
				}
			}
		} else {
			// PHPExcel export
			foreach( $users as $key => $user )
				$users[$key] = woo_ce_get_user_data( $user, $export->args, array_keys( $export->fields ) );
			$output = $users;
		}
		unset( $users, $user );
	}
	return $output;

}

function woo_ce_export_dataset_multisite_override_user( $output = null, $export_type = null ) {

	global $export;

	$sites = wp_get_sites();
	if( !empty( $sites ) ) {
		foreach( $sites as $site ) {
			switch_to_blog( $site['blog_id'] );
			if( $users = woo_ce_get_users( $export->args ) ) {
				$export->total_rows = count( $users );
				// XML, RSS export
				if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
					if( !empty( $export->fields ) ) {
						foreach( $users as $user ) {
							if( $export->export_format == 'xml' )
								$child = $output->addChild( apply_filters( 'woo_ce_export_xml_user_node', sanitize_key( $export_type ) ) );
							else if( $export->export_format == 'rss' )
								$child = $output->addChild( 'item' );
							$child->addAttribute( 'id', ( isset( $user->user_id ) ? $user->user_id : '' ) );
							$user = woo_ce_get_user_data( $user, $export->args, array_keys( $export->fields ) );
							foreach( array_keys( $export->fields ) as $key => $field ) {
								if( isset( $user->$field ) ) {
									if( !is_array( $field ) ) {
										if( woo_ce_is_xml_cdata( $user->$field ) )
											$child->addChild( sanitize_key( $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $user->$field ) ) );
										else
											$child->addChild( sanitize_key( $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $user->$field ) ) );
									}
								}
							}
						}
					}
				} else {
					// PHPExcel export
					foreach( $users as $key => $user )
						$users[$key] = woo_ce_get_user_data( $user, $export->args, array_keys( $export->fields ) );
					if( is_null( $output ) )
						$output = $users;
					else
						$output = array_merge( $output, $users );
				}
				unset( $users, $user );
			}
			restore_current_blog();
		}
	}
	return $output;

}

// Returns a list of WordPress User Roles
function woo_ce_get_user_roles() {

	global $wp_roles;

	$user_roles = $wp_roles->roles;
	if( !empty( $user_roles ) ) {
		if( $users = count_users() ) {
			foreach( $user_roles as $key => $user_role ) {
				$user_roles[$key]['count'] = ( isset( $users['avail_roles'][$key] ) ? $users['avail_roles'][$key] : 0 );
			}
			unset( $user_role, $users );
		}
		return $user_roles;
	}

}

// Returns the Username of a User
function woo_ce_get_username( $user_id = 0 ) {

	$output = '';
	if( $user_id ) {
		if( $user = get_userdata( $user_id ) )
			$output = $user->user_login;
		unset( $user );
	}
	return $output;

}

// Returns the User Role of a User
function woo_ce_get_user_role( $user_id = 0 ) {

	$output = '';
	if( $user_id ) {
		$user = get_userdata( $user_id );
		if( $user ) {
			$user_role = ( isset( $user->roles[0] ) ? $user->roles[0] : false );
			if( !empty( $user_role ) )
				$output = $user_role;
		}
		unset( $user );
	}
	return $output;

}

function woo_ce_format_user_role_label( $user_role = '' ) {

	global $wp_roles;

	$output = $user_role;
	if( !empty( $user_role ) ) {
		$user_roles = woo_ce_get_user_roles();
		if( isset( $user_roles[$user_role] ) )
			$output = ucfirst( $user_roles[$user_role]['name'] );
		unset( $user_roles );
	}
	return $output;

}

// Returns date of first User registered, any status
function woo_ce_get_user_first_date( $date_format = 'd/m/Y' ) {

	$output = date( $date_format, mktime( 0, 0, 0, date( 'n' ), 1 ) );

	$args = array(
		'limit_volume' => 1,
		'orderby' => 'registered',
		'order' => 'ASC'
	);
	if( $user_ids = woo_ce_get_users( $args ) ) {
		foreach( $user_ids as $user_id ) {
			$user = new WP_User( $user_id );
			if( !empty( $user ) )
				$output = date( $date_format, strtotime( $user->data->user_registered ) );
		}
	}
	return $output;

}
?>