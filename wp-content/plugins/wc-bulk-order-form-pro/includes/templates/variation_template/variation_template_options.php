<?php

class WCBulkOrderForm_Settings_Variation_Template {
	
	public function __construct() {
		add_action( 'admin_init', array( &$this, 'init_settings' ) ); // Registers settings
		add_action('wcbulkorderform_settings',array(&$this,'print_settings'));
	}

	/**
	 * Print Settings
	 */
	public function print_settings(){
		settings_fields( 'wcbulkorderform_variation_template' );
		do_settings_sections( 'wcbulkorderform_variation_template' );
		$option = get_option('wcbulkorderform_variation_template');
		//print_r($option);
	}

	/**
	 * User settings.
	 */
	public function init_settings() {
		$option = 'wcbulkorderform_variation_template';
	
		// Create option in wp_options.
		if ( false == get_option( $option ) ) {
			add_option( $option );
		}
	
		// Main plugin options section.
		add_settings_section(
			'plugin_settings',
			__( 'Plugin Settings', 'wcbulkorderform' ),
			array( &$this, 'section_options_callback' ),
			$option
		);
		
		// Search by field
		add_settings_field(
			'search_by',
			__( 'When searching for products search by:', 'wcbulkorderform' ),
			array( &$this, 'radio_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'search_by',
				'options' 		=> array(
					'1'			=> __( 'SKU' , 'wcbulkorderform' ),
					'2'			=> __( 'ID' , 'wcbulkorderform' ),
					'3'			=> __( 'Title' , 'wcbulkorderform' ),
					'4'			=> __( 'All' , 'wcbulkorderform' )
				),
				'default'		=> '4'
			)
		);
		
		// How should we display the product search results?
		add_settings_field(
			'search_format',
			__( 'Choose your product search results format', 'wcbulkorderform' ),
			array( &$this, 'radio_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'search_format',
				'options' 		=> array(
					'1'			=> __( 'SKU - Title - Price' , 'wcbulkorderform' ),
					'2'			=> __( 'Title - Price - SKU' , 'wcbulkorderform' ),
					'3'			=> __( 'Title - Price' , 'wcbulkorderform' ),
					'4'			=> __( 'Title - SKU' , 'wcbulkorderform' ),
					'5'			=> __( 'Title' , 'wcbulkorderform' )
				),
				'default'		=> '2'
			)
		);

		// How should we display the variation search results?
		add_settings_field(
			'variation_search_format',
			__( 'Choose your variation display format', 'wcbulkorderform' ),
			array( &$this, 'radio_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'variation_search_format',
				'options' 		=> array(
					'1'			=> __( 'SKU - Title - Price' , 'wcbulkorderform' ),
					'2'			=> __( 'Title - Price - SKU' , 'wcbulkorderform' ),
					'3'			=> __( 'Title - Price' , 'wcbulkorderform' ),
					'4'			=> __( 'Title - SKU' , 'wcbulkorderform' ),
					'5'			=> __( 'Title' , 'wcbulkorderform' )
				),
				'default'		=> '2'
			)
		);

		// How should we display variations?
		add_settings_field(
			'variation_display_format',
			__( 'Display variations & attributes?', 'wcbulkorderform' ),
			array( &$this, 'radio_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'variation_display_format',
				'options' 		=> array(
					'1'			=> __( 'Variations only' , 'wcbulkorderform' ),
					'2'			=> __( 'Variations & Attributes' , 'wcbulkorderform' )
				),
				'default'		=> '2'
			)
		);

		// How should we display attributes?
		add_settings_field(
			'attribute_style',
			__( 'Display attribute title or just attribute value? Ex. (Color: Red) or (Red)', 'wcbulkorderform' ),
			array( &$this, 'radio_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'attribute_style',
				'options' 		=> array(
					'true'		=> __( 'Attribute value only (recommended)' , 'wcbulkorderform' ),
					'false'		=> __( 'Attribute title and value' , 'wcbulkorderform' )
				),
				'default'		=> 'false'
			)
		);

		// display an add to cart button next to each product field?
		add_settings_field(
			'single_add_to_cart',
			__( 'Display an add to cart button next to each product', 'wcbulkorderform' ),
			array( &$this, 'radio_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'single_add_to_cart',
				'options' 		=> array(
					'true'		=> __( 'Yes' , 'wcbulkorderform' ),
					'false'		=> __( 'No' , 'wcbulkorderform' )
				),
				'default'		=> 'false'
			)
		);
		
		// display new row buttton? Yes/no
		add_settings_field(
			'new_row_button',
			__( 'Display "Add New Row" Button?', 'wcbulkorderform' ),
			array( &$this, 'radio_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'new_row_button',
				'options' 		=> array(
					'true'		=> __( 'Yes' , 'wcbulkorderform' ),
					'false'		=> __( 'No' , 'wcbulkorderform' )
				),
				'default'		=> 'true'
			)
		);

		// display images in search? Yes/no
		add_settings_field(
			'display_images',
			__( 'Display product images in autocomplete search?', 'wcbulkorderform' ),
			array( &$this, 'radio_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'display_images',
				'options' 		=> array(
					'true'		=> __( 'Yes' , 'wcbulkorderform' ),
					'false'		=> __( 'No' , 'wcbulkorderform' )
				),
				'default'		=> 'false'
			)
		);
		
		// Advanced settings Section.
		add_settings_section(
			'advanced_settings',
			__( 'Default Shortcode Options', 'wcbulkorderform' ),
			array( &$this, 'section_options_callback' ),
			$option
		);
		
		// number of rows to display?
		add_settings_field(
			'bulkorder_row_number',
			__( 'Number of rows to display on the bulk order form', 'wcbulkorderform' ),
			array( &$this, 'text_element_callback' ),
			$option,
			'advanced_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'bulkorder_row_number'
			)
		);

		// max number of items to return in search result?
		add_settings_field(
			'max_items',
			__( 'Maximum Items to Display in a Search', 'wcbulkorderform' ),
			array( &$this, 'text_element_callback' ),
			$option,
			'advanced_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'max_items'
			)
		);
		
		// show the price column? Yes/no
		add_settings_field(
			'display_price',
			__( 'Display price on bulk order form?', 'wcbulkorderform' ),
			array( &$this, 'radio_element_callback' ),
			$option,
			'advanced_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'display_price',
				'options' 		=> array(
					'true'			=> __( 'Yes' , 'wcbulkorderform' ),
					'false'			=> __( 'No' , 'wcbulkorderform' )
				),
			)
		);
		
		// product column title
		add_settings_field(
			'product_field_title',
			__( 'Title for product fields', 'wcbulkorderform' ),
			array( &$this, 'text_element_callback' ),
			$option,
			'advanced_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'product_field_title'
			)
		);
		
		// variation column title
		add_settings_field(
			'variation_field_title',
			__( 'Title for variation fields', 'wcbulkorderform' ),
			array( &$this, 'text_element_callback' ),
			$option,
			'advanced_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'variation_field_title'
			)
		);

		// quantity column title
		add_settings_field(
			'quantity_field_title',
			__( 'Title for quantity fields', 'wcbulkorderform' ),
			array( &$this, 'text_element_callback' ),
			$option,
			'advanced_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'quantity_field_title'
			)
		);
		
		// price column title
		add_settings_field(
			'price_field_title',
			__( 'Title for price fields', 'wcbulkorderform' ),
			array( &$this, 'text_element_callback' ),
			$option,
			'advanced_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'price_field_title'
			)
		);

		// disable jquery ui css? Not recommended in most cases
		add_settings_field(
			'no_load_css',
			__( "Don't load jquery ui styles. (Don't check this unless you know your site is loading jquery ui styles from another source)", 'wcbulkorderform' ),
			array( &$this, 'checkbox_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'no_load_css',
			)
		);

		// add to cart success message
		add_settings_field(
			'add_to_cart_success_message',
			__( 'Add to Cart Success Message: Use {wcbo_pn} for product name', 'wcbulkorderform' ),
			array( &$this, 'text_element_callback' ),
			$option,
			'advanced_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'add_to_cart_success_message'
			)
		);

		// add to cart failure message
		add_settings_field(
			'add_to_cart_failure_message',
			__( 'Add to Cart Failure Message: Use {wcbo_pn} for product name', 'wcbulkorderform' ),
			array( &$this, 'text_element_callback' ),
			$option,
			'advanced_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'add_to_cart_failure_message'
			)
		);

		// pick between cart/checkout
		add_settings_field(
			'send_to_cart_or_checkout',
			__( 'Set button to cart or checkout?', 'wcbulkorderform' ),
			array( &$this, 'radio_element_callback' ),
			$option,
			'advanced_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'send_to_cart_or_checkout',
				'options' 		=> array(
					'cart'			=> __( 'Cart' , 'woocommerce' ),
					'checkout'			=> __( 'Checkout' , 'woocommerce' )
				),
			)
		);

		// Register settings.
		register_setting( $option, $option, array( &$this, 'wcbulkorderform_options_validate' ) );

		// Register defaults if settings empty (might not work in case there's only checkboxes and they're all disabled)
		$option_values = get_option($option);

		if ( empty( $option_values ) ) {
			$this->default_settings();
		}
	}
	 
	/**
	 * Default settings.
	 */
	public function default_settings() {
		global $options;
		$default = array(
			'search_by'						=> '4',
			'search_format'					=> '2',
			'variation_search_format' 		=> '2',
			'variation_display_format'		=> '2',
			'new_row_button'				=> 'true',
			'bulkorder_row_number'			=> '5',
			'max_items'						=> '20',
			'display_price'					=> 'true',
			'product_field_title'			=> 'Product',
			'variation_field_title'			=> 'Variation',
			'quantity_field_title'			=> 'Quantity',
			'price_field_title'				=> 'Price',
			'no_load_css'					=> '',
			'display_images'				=> 'false',
			'attribute_style'				=> 'true',
			'single_add_to_cart'			=> 'false',
			'add_to_cart_success_message'	=> '{wcbo_pn} successfully added to cart.',
			'add_to_cart_failure_message'	=> 'There was an error adding {wcbo_pn} to your cart.',
			'send_to_cart_or_checkout'		=> 'cart'
		);
		
		update_option( 'wcbulkorderform_variation_template', $default );
	}

	/**
	 * Text field callback.
	 *
	 * @param  array $args Field arguments.
	 *
	 * @return string	  Text field.
	 */
	public function text_element_callback( $args ) {
		$menu = $args['menu'];
		$id = $args['id'];
		$size = isset( $args['size'] ) ? $args['size'] : '25';
	
		$options = get_option( $menu );
	
		if ( isset( $options[$id] ) ) {
			$current = $options[$id];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '';
		}
		$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
		$html = sprintf( '<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s" size="%4$s"%5$s/>', $id, $menu, $current, $size, $disabled );
	
		// Displays option description.
		if ( isset( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
		}
	
		echo $html;
	}
	
	/**
	 * Displays a selectbox for a settings field
	 *
	 * @param array   $args settings field args
	 */
	public function select_element_callback( $args ) {
		$menu = $args['menu'];
		$id = $args['id'];
		
		$options = get_option( $menu );
		
		if ( isset( $options[$id] ) ) {
			$current = $options[$id];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '';
		}
		$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
		
		$html = sprintf( '<select name="%1$s[%2$s]" id="%1$s[%2$s]"%3$s>', $menu, $id, $disabled );
		$html .= sprintf( '<option value="%s"%s>%s</option>', '0', selected( $current, '0', false ), '' );
		
		foreach ( $args['options'] as $key => $label ) {
			$html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $current, $key, false ), $label );
		}
		$html .= sprintf( '</select>' );
		if ( isset( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
		}
		
		echo $html;
	}

	/**
	 * Displays a multiple selectbox for a settings field
	 *
	 * @param array   $args settings field args
	 */
	public function multiple_select_element_callback( $args ) {
		$html = '';
		foreach ($args as $id => $boxes) {
			$menu = $boxes['menu'];
			
			$options = get_option( $menu );
			
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $boxes['default'] ) ? $boxes['default'] : '';
			}
			
			$disabled = (isset( $boxes['disabled'] )) ? ' disabled' : '';
			
			$html .= sprintf( '<select name="%1$s[%2$s]" id="%1$s[%2$s]"%3$s>', $menu, $id, $disabled);
			$html .= sprintf( '<option value="%s"%s>%s</option>', '0', selected( $current, '0', false ), '' );
			
			foreach ( (array) $boxes['options'] as $key => $label ) {
				$html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $current, $key, false ), $label );
			}
			$html .= '</select>';
	
			if ( isset( $boxes['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', $boxes['description'] );
			}
			$html .= '<br />';
		}
		
		
		echo $html;
	}

	/**
	 * Checkbox field callback.
	 *
	 * @param  array $args Field arguments.
	 *
	 * @return string	  Checkbox field.
	 */
	public function checkbox_element_callback( $args ) {
		$menu = $args['menu'];
		$id = $args['id'];
	
		$options = get_option( $menu );
	
		if ( isset( $options[$id] ) ) {
			$current = $options[$id];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '';
		}
	
		$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
		$html = sprintf( '<input type="checkbox" id="%1$s" name="%2$s[%1$s]" value="1"%3$s %4$s/>', $id, $menu, checked( 1, $current, false ), $disabled );
	
		// Displays option description.
		if ( isset( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
		}
	
		echo $html;
	}

	/**
	 * Displays a multicheckbox a settings field
	 *
	 * @param array   $args settings field args
	 */
	public function radio_element_callback( $args ) {
		$menu = $args['menu'];
		$id = $args['id'];
	
		$options = get_option( $menu );
	
		if ( isset( $options[$id] ) ) {
			$current = $options[$id];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '';
		}
		$html = '';
		foreach ( $args['options'] as $key => $label ) {
			$html .= sprintf( '<input type="radio" class="radio" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s"%4$s />', $menu, $id, $key, checked( $current, $key, false ) );
			$html .= sprintf( '<label for="%1$s[%2$s][%3$s]"> %4$s</label><br>', $menu, $id, $key, $label);
		}
		
		// Displays option description.
		if ( isset( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
		}
		echo $html;
	}

	/**
	 * Displays a multicheckbox a settings field
	 *
	 * @param array   $args settings field args
	 */
	public function icons_radio_element_callback( $args ) {
		$menu = $args['menu'];
		$id = $args['id'];
	
		$options = get_option( $menu );
	
		if ( isset( $options[$id] ) ) {
			$current = $options[$id];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '';
		}
		$icons = '';
		$radios = '';
		
		foreach ( $args['options'] as $key => $iconnumber ) {
			$icons .= sprintf( '<td style="padding-bottom:0;font-size:16pt;" align="center"><label for="%1$s[%2$s][%3$s]"><i class="wcbulkorderform-icon-shopping-cart-%4$s"></i></label></td>', $menu, $id, $key, $iconnumber);
			$radios .= sprintf( '<td style="padding-top:0" align="center"><input type="radio" class="radio" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s"%4$s /></td>', $menu, $id, $key, checked( $current, $key, false ) );
		}
		$html = '<table><tr>'.$icons.'</tr><tr>'.$radios.'</tr></table>';
		$html .= '<p class="description"><i>'. __('<strong>Please note:</strong> you need to open your website in a new tab/browser window after updating the cart icon for the change to be visible!','wcbulkorderform').'</p>';
		
		echo $html;
	}

	/**
	 * Section null callback.
	 *
	 * @return void.
	 */
	public function section_options_callback() {
	
	}
	
	/**
	 * Validate/sanitize options input
	 */
	public function wcbulkorderform_options_validate( $input ) {
		// Create our array for storing the validated options.
		$output = array();
		// Loop through each of the incoming options.
		foreach ( $input as $key => $value ) {
			// Check to see if the current option has a value. If so, process it.
			if ( isset( $input[$key] ) ) {
				// Strip all HTML and PHP tags and properly handle quoted strings.
				$output[$key] = strip_tags( stripslashes( $input[$key] ) );
			}
		}
		// Return the array processing any additional functions filtered by this action.
		return apply_filters( 'wcbulkorderform_validate_input', $output, $input );
	}
}