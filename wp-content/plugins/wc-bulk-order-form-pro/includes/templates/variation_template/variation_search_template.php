<?php
/*
 * Variation Search Template
 */
?>
<?php
class WCBulkOrderForm_Variation_Template{
	private static $add_script;
	/**
	 * Construct.
	 */
	public function __construct() {
		global $woocommerce;
		$this->includes();
		$this->options = get_option('wcbulkorderform_variation_template');
		//print_r($this->options);
		if(empty($this->options)) {
			register_activation_hook( __FILE__, array( 'WCBulkOrderForm_Settings_Variation_Template', 'default_settings' ) );
			$this->options = get_option('wcbulkorderform_variation_template');
		}
		$variation_template_settings = new WCBulkOrderForm_Settings_Variation_Template();
		
		add_shortcode('wcbulkorder', array( $this, 'wc_bulk_order_form' ) );
		
		// Functions to deal with the AJAX request - one for logged in users, the other for non-logged in users.
		add_action( 'wp_ajax_bulk_order_product_search', array( $this, 'bulk_order_product_search' ));
		add_action( 'wp_ajax_nopriv_bulk_order_product_search', array( $this, 'bulk_order_product_search' ));
		add_action( 'wp_ajax_bulk_order_variation_search', array( $this, 'bulk_order_variation_search' ));
		add_action( 'wp_ajax_nopriv_bulk_order_variation_search', array( $this, 'bulk_order_variation_search' ));
		add_action( 'wp_ajax_ajax_product_add_to_cart', array( $this, 'ajax_product_add_to_cart' ));
		add_action( 'wp_ajax_nopriv_ajax_product_add_to_cart', array( $this, 'ajax_product_add_to_cart' ));
		add_action( 'wp_ajax_ajax_multiproduct_add_to_cart', array( $this, 'ajax_multiproduct_add_to_cart' ));
		add_action( 'wp_ajax_nopriv_ajax_multiproduct_add_to_cart', array( $this, 'ajax_multiproduct_add_to_cart' ));
		add_action( 'wp_ajax_ajax_item_price', array( $this, 'ajax_item_price' ));
		add_action( 'wp_ajax_nopriv_ajax_item_price', array( $this, 'ajax_item_price' ));
		add_action( 'wp_print_styles', array( $this, 'load_styles' ), 0 );
		add_action( 'wp', array($this,'process_bulk_order_form') );
		add_action( 'init', array( $this, 'register_script'));
		add_action( 'wp_footer', array( $this, 'print_script'));
		add_action( 'wp_enqueue_scripts', array($this,'localize_script'));
	}
	
	/**
	 * Load additional classes and functions
	 */
	public function includes() {
		include_once( 'variation_template_options.php' );
	}
	
	/**
	 * Load CSS
	 */
	public function load_styles() {
		
		if (empty($this->options['no_load_css'])) {
			$autocomplete = file_exists( get_stylesheet_directory() . '/jquery-ui.css' )
			? get_stylesheet_directory_uri() . '/jquery-ui.css'
			: plugins_url( '/css/jquery-ui.css', __FILE__ );
			wp_register_style( 'wcbulkorder-jquery-ui', $autocomplete, array(), '', 'all' );
		}
		
		$css = file_exists( get_stylesheet_directory() . '/wcbulkorderform.css' )
			? get_stylesheet_directory_uri() . '/wcbulkorderform.css'
			: plugins_url( '/css/wcbulkorderform.css', __FILE__ );
			
		wp_register_style( 'wcbulkorderform', $css, array(), '', 'all' );
		/*
		$fontawesome = file_exists( get_stylesheet_directory() . '/font-awesome.min.css' )
			? get_stylesheet_directory_uri() . '/font-awesome.min.css'
			: plugins_url( '/css/font-awesome.min.css', __FILE__ );
		wp_register_style( 'wcbulkorderfontawesome', $fontawesome, array(), '', 'all' );
		*/
	}
	
	/**
	 * Load JS
	 */
	function register_script() {
		wp_register_script('wcbulkorder_acsearch', plugins_url( '/js/wcbulkorder_acsearch.js' , __FILE__ ), array('jquery','jquery-ui-autocomplete'),null,true);
	}
	function localize_script() {
		global $woocommerce;
		$options = get_option('wcbulkorderform_variation_template');
		$display_images = isset($options['display_images']) ? $options['display_images'] : '';
		$noproductsfound = __( 'No Products Were Found', 'wcbulkorderform' );
		$variation_noproductsfound = __( 'No Variations', 'wcbulkorderform' );
		$selectaproduct = __( 'Please Select a Product', 'wcbulkorderform' );
		$enterquantity = __( 'Enter Quantity', 'wcbulkorderform' );
		$decimal_sep = wp_specialchars_decode( stripslashes( get_option( 'woocommerce_price_decimal_sep' ) ), ENT_QUOTES );
  		$thousands_sep = wp_specialchars_decode( stripslashes( get_option( 'woocommerce_price_thousand_sep' ) ), ENT_QUOTES );
  		$num_decimals = absint( get_option( 'woocommerce_price_num_decimals' ) );
		$minLength = 0;
		$Delay = 500;
		$single_buttons = $options['variation_display_format'];
		if($options['send_to_cart_or_checkout'] == 'checkout'){
			$checkouttext = __( 'Checkout' , 'woocommerce' );
			$checkouturl = $woocommerce->cart->get_checkout_url();
		} else {
			$checkouttext = __( 'Cart' , 'woocommerce' );
			$checkouturl = $woocommerce->cart->get_cart_url();
		}
		wp_localize_script( 'wcbulkorder_acsearch', 'WCBulkOrder', array('url' => admin_url( 'admin-ajax.php' ), 'search_products_nonce' => wp_create_nonce('wcbulkorder-search-products'), 'display_images' => $display_images, 'noproductsfound' => $noproductsfound, 'selectaproduct' => $selectaproduct, 'enterquantity' => $enterquantity, 'variation_noproductsfound' => $variation_noproductsfound,'variation_noproductsfound' => $variation_noproductsfound, 'decimal_sep' => $decimal_sep, 'thousands_sep' => $thousands_sep, 'num_decimals' => $num_decimals, 'Delay' => $Delay, 'minLength' => $minLength, 'single_buttons' => $single_buttons, 'checkouttext' => $checkouttext, 'checkouturl' => $checkouturl ));
	}

	static function print_script() {
		if ( ! self::$add_script )
			return;
		wp_print_scripts('wcbulkorder_acsearch');
		wp_enqueue_style( 'wcbulkorder-jquery-ui' );
		wp_enqueue_style( 'wcbulkorderform' );
	}
	function process_bulk_order_form() {
        $prod_name = '';
		if(isset($_POST['submit'])) {
			$prod_name = $_POST['wcbulkorderproduct'];
			$prod_quantity = $_POST['wcbulkorderquantity'];
			$prod_id = $_POST['wcbulkorderid'];
			print_r($_POST);
			$i = 0;
			foreach($prod_id as $key => $value) {
				$ancestors = '';
				$ancestors = get_post_ancestors( $prod_id );
				$quantity = $_POST['wcbulkorderquantity'][$i];
				$product_id = $value;
				$variation_id = '';
				$attributes = '';
				if ( 'product_variation' == get_post_type( $product_id ) ) {
                    $variation_id = $product_id;
                    $product_id = wp_get_post_parent_id( $variation_id );
                    $product = new WC_Product_Variation($variation_id);
                    $attributes = $product->get_variation_attributes();
					$attributes = isset($attributes) ? $attributes : '';
            	}
				
				if (!empty($ancestors)) {
					global $woocommerce;
					$woocommerce->cart->add_to_cart($ancestors[0], $quantity, $variation_id,$attributes,null);
					++$i;
				}
				else {
					global $woocommerce;
					$woocommerce->cart->add_to_cart($_POST['wcbulkorderid'][$i], $quantity, $variation_id,$attributes,null);
					++$i;
				}
			}
			
		}
    }

     function ajax_multiproduct_add_to_cart(){
    	global $post, $woocommerce, $product;

		foreach($_POST as $item){
			$success_message = isset($this->options['add_to_cart_success_message']) ? $this->options['add_to_cart_success_message'] : '{wcbo_pn} successfully added to cart.';
			$failure_message = isset($this->options['add_to_cart_failure_message']) ? $this->options['add_to_cart_failure_message'] : 'There was an error adding {wcbo_pn} to your cart.';
			/* Set arrays and variables */
	    	$prod_id = isset($item['id']) ? $item['id'] : '';
	    	$var_id = '';
	    	$quantity = isset($item['quantity']) ? $item['quantity'] : '';
	    	$attributes = isset($item['atts']) ? $item['atts'] : '';

	    	/* Get Product by id*/
	    	$product = wc_get_product($prod_id);

			/* Set attribute array for this cart item and modify it to fit  */
			$att_query = array();
			if($attributes){
				foreach($attributes as $name => $value){
					$new_att_name = 'attribute_'.$name;
					$value = str_replace(" ","-",$value);
					$att_query[$new_att_name] = preg_replace('/[.,]/', '', strtolower($value));
				}
			}

			/* If has children get variations and check the array against the data passed before */
			if($product->has_child()) {
				$available_variations = $product->get_available_variations();
				$count = count($available_variations);
				/* if only one variation, use only one id */
				if($count == '1'){
					foreach($available_variations as $avar){
						$var_id = $avar['variation_id'];
						$attributes = $att_query;
						$result = $woocommerce->cart->add_to_cart($prod_id, $quantity, $var_id, $attributes, null);
						if(!empty($result)){
							$message = $success_message;
							$message = html_entity_decode(str_replace('{wcbo_pn}', $product->get_formatted_name(), $message));
							wc_print_notice( $message, $notice_type = 'success' );
						} else {
							$message = $failure_message;
							$message = html_entity_decode(str_replace('{wcbo_pn}', $product->get_formatted_name(), $message));
							wc_print_notice( $message, $notice_type = 'error' );
						}
					}
				/* if more than one variation, do this fancy pants logic */	
				} elseif($count > '1'){
					$var_id = '';
					$count_difference = '999';
					foreach($available_variations as $avar){
						$attributes = $avar['attributes'];
						$att_count = count($attributes);
						/* check for exact match. If exact, add to cart and exit */
						if($att_query == $attributes){
							$var_id = $avar['variation_id'];
							break;
						} else {
							$difference = array_diff($att_query, $attributes);
							$cdif = count($difference);
							if($cdif < $count_difference){
								$count_difference = $cdif;
								$var_id = $avar['variation_id'];
								$check_dif = $difference;
							}

						}
					}
					$attributes = $att_query;
					$result = $woocommerce->cart->add_to_cart($prod_id, $quantity, $var_id, $attributes, null);
					if(!empty($result)){
						$message = $success_message;
						$message = html_entity_decode(str_replace('{wcbo_pn}', $product->get_formatted_name(), $message));
						wc_print_notice( $message, $notice_type = 'success' );
					} else {
						$message = $failure_message;
						$message = html_entity_decode(str_replace('{wcbo_pn}', $product->get_formatted_name(), $message));
						wc_print_notice( $message, $notice_type = 'error' );
					}
				}
			/* If no children, just add to cart */	
			} else {
				$result = $woocommerce->cart->add_to_cart($prod_id, $quantity);
				if(!empty($result)){
					$message = $success_message;
					$message = html_entity_decode(str_replace('{wcbo_pn}', $product->get_formatted_name(), $message));
					wc_print_notice( $message, $notice_type = 'success' );
				} else {
					$message = $failure_message;
					$message = html_entity_decode(str_replace('{wcbo_pn}', $product->get_formatted_name(), $message));
					wc_print_notice( $message, $notice_type = 'error' );
				}
			}
		}
		exit;
    }

    function ajax_product_add_to_cart(){
    	global $post, $woocommerce, $product;

		/* Set arrays and variables */
    	$prod_id = isset($_POST['id']) ? $_POST['id'] : '';
    	$var_id = '';
    	$quantity = isset($_POST['quantity']) ? $_POST['quantity'] : '';
    	$attributes = isset($_POST['atts']) ? $_POST['atts'] : '';

    	/* Get Product by id*/
    	$product = wc_get_product($prod_id);

		/* Set attribute array for this cart item and modify it to fit  */
		$att_query = array();
		if($attributes){
			foreach($attributes as $name => $value){
				$new_att_name = 'attribute_'.$name;
				$value = str_replace(" ","-",$value);
				$att_query[$new_att_name] = preg_replace('/[.,]/', '', strtolower($value));
			}
		}

		/* If has children get variations and check the array against the data passed before */
		if($product->has_child()) {
			$available_variations = $product->get_available_variations();
			$count = count($available_variations);
			/* if only one variation, use only one id */
			if($count == '1'){
				foreach($available_variations as $avar){
					$var_id = $avar['variation_id'];
					$attributes = $att_query;
					$result = $woocommerce->cart->add_to_cart($prod_id, $quantity, $var_id, $attributes, null);
				}
			/* if more than one variation, do this fancy pants logic */	
			} elseif($count > '1'){
				$var_id = '';
				$count_difference = '999';
				foreach($available_variations as $avar){
					$attributes = $avar['attributes'];
					$att_count = count($attributes);
					/* check for exact match. If exact, add to cart and exit */
					if($att_query == $attributes){
						$var_id = $avar['variation_id'];
						break;
					} else {
						$difference = array_diff($att_query, $attributes);
						$cdif = count($difference);
						if($cdif < $count_difference){
							$count_difference = $cdif;
							$var_id = $avar['variation_id'];
							$check_dif = $difference;
						}

					}
				}
				$attributes = $att_query;
				$result = $woocommerce->cart->add_to_cart($prod_id, $quantity, $var_id, $attributes, null);
			}
		/* If no children, just add to cart */	
		} else {
			$result = $woocommerce->cart->add_to_cart($prod_id, $quantity);
		}
		/* Call WC ajax after add to cart. Not yet found a way to make this work. */
		//add_filter('add_to_cart_fragments', WC_AJAX::get_refreshed_fragments());
		//add_action('woocommerce_add_to_cart','');

		/* cart notices */
		$notices = wc_get_notices();
		if(!empty($notices)){
			wc_print_notices();
			exit;
		}
		if(!empty($result) && empty($notices)){
			echo '<ul class="woocommerce-success"><li>Product successfully added to your cart.</li></ul>';
			exit;
		}
		if(empty($result) && empty($notices)){
			echo '<ul class="woocommerce-error"><li>Product could not be added to your cart.</li></ul>';
			exit;
		} else {
			exit;
		}
    }

    function ajax_item_price(){
    	global $post, $woocommerce, $product;

    	/* Set arrays and variables */
    	$prod_id = isset($_POST['id']) ? $_POST['id'] : '';
    	$var_id = '';
    	$quantity = isset($_POST['quantity']) ? $_POST['quantity'] : '';
    	$attributes = isset($_POST['atts']) ? $_POST['atts'] : '';

    	/* Get Product by id*/
    	$product = get_product($prod_id);

		/* Set attribute array for this cart item and modify it to fit  */
		$att_query = array();
		if($attributes){
			foreach($attributes as $name => $value){
				$new_att_name = 'attribute_'.$name;
				$value = str_replace(" ","-",$value);
				$att_query[$new_att_name] = preg_replace('/[.,]/', '', strtolower($value));
			}
		}

		/* If has children get variations and check the array against the data passed before */
		if($product->has_child()) {
			$available_variations = $product->get_available_variations();
			//print_r($available_variations);
			$count = count($available_variations);
			/* if only one variation, use only one id */
			if($count == '1'){
				foreach($available_variations as $avar){
					$var_id = $avar['variation_id'];
				}
				$product = new WC_Product_Variation($var_id);
				$price_html = $product->get_price_html();
                if(preg_match('/<ins>(.*?)<\/ins>/', $price_html)){ 
				    preg_match('/<ins>(.*?)<\/ins>/', $price_html, $matches);
				    $price_html = $matches[1];
				}
				echo html_entity_decode($price_html);
				exit;
			/* if more than one variation, do this fancy pants logic */	
			} elseif($count > '1'){
				$var_id = '';
				$count_difference = '999';
				foreach($available_variations as $avar){
					$attributes = $avar['attributes'];
					$att_count = count($attributes);
					/* check for exact match. If exact, add to cart and exit */
					if($att_query == $attributes){
						$var_id = $avar['variation_id'];
						break;
					} else {
						$difference = array_diff($att_query, $attributes);
						$cdif = count($difference);
						if($cdif < $count_difference){
							$count_difference = $cdif;
							$var_id = $avar['variation_id'];
						}
					}
				}
				$product = new WC_Product_Variation($var_id);
				$price_html = $product->get_price_html();
                if(preg_match('/<ins>(.*?)<\/ins>/', $price_html)){ 
				    preg_match('/<ins>(.*?)<\/ins>/', $price_html, $matches);
				    $price_html = $matches[1];
				}
				echo html_entity_decode($price_html);
				exit;
			}
		/* If no children, just add to cart */	
		} else {
			$price_html = $product->get_price_html();
            if(preg_match('/<ins>(.*?)<\/ins>/', $price_html)){ 
			    preg_match('/<ins>(.*?)<\/ins>/', $price_html, $matches);
			    $price_html = $matches[1];
			}
			echo html_entity_decode($price_html);
			exit;
		}
    }
	
	/**
	 * Create Bulk Order Form Shortcode
	 * Source: http://wordpress.stackexchange.com/questions/53280/woocommerce-add-a-product-to-cart-programmatically-via-js-or-php
	 * http://stackoverflow.com/questions/10802172/wordpress-woocommerce-add-a-variable-product-to-cart-using-the-wc-cart-class
	*/ 
	public function wc_bulk_order_form ($atts){
		global $woocommerce;
		self::$add_script = true;
		extract( shortcode_atts( array(
		'rows' => isset($this->options['bulkorder_row_number']) ? $this->options['bulkorder_row_number'] : '',
		'price' => isset($this->options['display_price']) ? $this->options['display_price'] : '',
		'price_label' => isset($this->options['price_field_title']) ? $this->options['price_field_title'] : '',
		'product_label' => isset($this->options['product_field_title']) ? $this->options['product_field_title'] : '',
		'variation_label' => isset($this->options['variation_field_title']) ? $this->options['variation_field_title'] : '',
		'quantity_label' => isset($this->options['quantity_field_title']) ? $this->options['quantity_field_title'] : '',
		'add_rows' => isset($this->options['new_row_button']) ? $this->options['new_row_button'] : '',
		'category' => '',
		'exclude' => '',
		'include' => ''
		), $atts ) );
		$i = 0;
		$html = '';
		$product_help_text = apply_filters('bulkorder_product_help_text','Type to search for a product');
		$variation_help_text = apply_filters('bulkorder_variation_help_text','');
		$quantity_help_text = apply_filters('bulkorder_quantity_help_text','');
		$cart_url = $woocommerce->cart->get_cart_url();
		if (!empty($_POST['wcbulkorderid'])) {
			$quantity_check = array_filter($_POST['wcbulkorderquantity']);
			if (empty($quantity_check)){
				$message = __("Make sure to set a quantity! Please try again.", "wcbulkorderform");
				wc_add_notice( $message, 'error' );
			} else {
				if (($_POST['wcbulkorderid'][0] > 0) && ($_POST['wcbulkorderid'][1] > 0)){
					$items = 2;
				} else if($_POST['wcbulkorderid'][0] > 0){
					$items = 1;
				} else if((isset($_POST['submit'])) && ($_POST['wcbulkorderid'][0] <= 0)){
					$items = 0;
				}
				switch($items){
					case 0:
						$message = __("Looks like there was an error. Please try again.", "wcbulkorderform");
						wc_add_notice( $message, 'error' );
						break;
					case 1:
						$message = '<a class="button wc-forward" href="'.$cart_url.'">View Cart</a>'.__("Your product was successfully added to your cart.", "wcbulkorderform");
						wc_add_notice( $message, 'success' );
						break;
					case 2:
						$message = '<a class="button wc-forward" href="'.$cart_url.'">'.__("View Cart</a> Your products were successfully added to your cart.", "wcbulkorderform");
						wc_add_notice( $message, 'success' );
						break;
				}
			}
			wc_print_notices();
		}
		$html = <<<HTML
		<form action="" method="post" id="BulkOrderForm" category="$category" included="$include" excluded="$exclude">
		<table class="wcbulkorderformtable">
			<tbody class="wcbulkorderformtbody">
				<tr>
					<th class="wcbulkorder-title">$product_label</th>
					<th class="wcbulkorder-variation-title">$variation_label</th>
					<th class="wcbulkorder-quantity">$quantity_label</th>
HTML;
					if ($price == 'true'){
						$html .= '<th class="wcbulkorderprice">'.$price_label.'</th>';
					}
					if($this->options['single_add_to_cart'] == 'true'){
						$html .= '<th class="wcbulkorderaddtocart"></th>';
					}
				$html .= '</tr>';
			while($i < $rows) {
				++$i;
				//$html .= <<<HTML2
				$html .=' <tr class="wcbulkorderformtr">';
					$html .= '<td class="wcbulkorder-title">';
						$html .= '<i class="bulkorder_spinner"></i>';
						$html .= '<input type="text" name="wcbulkorderproduct[]" class="wcbulkorderproduct" placeholder="'.$product_help_text.'" />';
					$html .= '</td>';
					$html .= '<td class="wcbulkorder-variation-title">';
						$html .= '<i class="bulkorder_spinner"></i>';
						$html .= '<input type="text" name="wcbulkordervariation[]" class="wcbulkordervariation" placeholder="'.$variation_help_text.'" />';
					$html .= '</td>';
					$html .= '<td class="wcbulkorder-quantity">';
						$html .= '<input type="number" name="wcbulkorderquantity[]" class="wcbulkorderquantity" placeholder="'.$quantity_help_text.'" />';
					$html .= '</td>';
//HTML2;
					if ($price == 'true'){
					$html .= '<td class="wcbulkorderprice"></td>';
					}
					$html .= '<input type="hidden" name="wcbulkorderid[]" class="wcbulkorderid" value="" />';
					if($this->options['single_add_to_cart'] == 'true'){
						$html .= '<td class="wcbulkorderaddtocart"><input type="button" value="Add to Cart" data-link="" /><span class="add_to_cart_message"></span></td>';
					}
				$html .= '</tr>';
			}
		$html .= <<<HTML3
			</tbody>
		</table>	
		<table class="wcbulkorderformtable">
			<tbody>
HTML3;
				if ($price == 'true'){
				$html .= <<<HTML4
				<tr class="wcbulkorderformtr">
					<td class="wcbulkorder-title"></td>
					<td class="wcbulkorder-quantity"></td>
					<td class="wcbulkorder-quantity">
HTML4;
						$html .= __( 'Total Price:' , 'wcbulkorderform' );
					$html .= <<<HTML6
					</td>
					
					<td class="wcbulkorderpricetotal"></td>
					
				</tr>
HTML6;
				}
				$html .= '<tr>';
					$html .= '<td class="wcbulkorder-title"></td>';
					$html .= '<td class="wcbulkorder-quantity"></td>';
					$html .='<td class="wcbulkorder-quantity">';
						if (($add_rows == 'true') && ($price == 'true')){
						$html .='<button class="wcbulkordernewrowprice">'.__( 'Add Row' , 'wcbulkorderform' ).'</button>';
						}
						elseif (($add_rows == 'true') && ($price != 'true')) {
						$html .='<button class="wcbulkordernewrow">'.__( 'Add Row' , 'wcbulkorderform' ).'</button>';
						}
					
					$html .='</td>';
					$html .='<td class="wcbulkorder-quantity"><input type="submit" value="'.__( 'Add To Cart' , 'wcbulkorderform' ).'" name="submit" class="add_to_cart_button single_add_to_cart_button bulkorderform_submit" /></td>';
					$html .= <<<HTML5
				</tr>
			</tbody>
		</table>
		</form>
HTML5;
		$html .= '<div class="multi_add_to_cart_message woocommerce"></div>';
		return $html;
		
	}
	function bulk_order_product_search(){
		// Query for suggestions
		$term = $_REQUEST['term'];
		$category = !empty($_REQUEST['category']) ? explode(',', $_REQUEST['category']) : array();
		$excluded_products = !empty($_REQUEST['excluded']) ? explode(',', $_REQUEST['excluded']) : array();
		$included_products = !empty($_REQUEST['included']) ? explode(',', $_REQUEST['included']) : array();
		$search_by = isset($this->options['search_by']) ? $this->options['search_by'] : '4';
		$max_items = isset($this->options['max_items']) ? $this->options['max_items'] : '-1';
		$excluded_products = apply_filters('wc_bulk_order_excluded_products', $excluded_products);
		$included_products = apply_filters('wc_bulk_order_included_products', $included_products);
		if (empty($term)) {
			$term = '';
		}
		if(!empty($category)){
			if ( is_numeric( $term ) ) {
			
				if (($search_by == 2) || ($search_by == 4)){
					$products1 = array(
						'post_type'     	=> array ('product', 'product_variation'),
						'post_status'     	=> array('publish'),
						'posts_per_page'    => $max_items,
						'fields'            => 'ids',
						'post__not_in'		=> $excluded_products,
						'post__in'			=> $included_products,
						'tax_query' => array(
							array(
								'taxonomy' 	=> 'product_cat',
								'field'    	=> 'id',
								'terms'    	=> $category,
							),
						),
						'suppress_filters' => false,
						'no_found_rows'          => true,
						'update_post_term_cache' => false,
						'update_post_meta_cache' => false,
						'cache_results'          => false
					);
					
					$products2 = array(
						'post_type'        	=> array ('product', 'product_variation'),
						'post_status'       => array('publish'),
						'posts_per_page'    => $max_items,
						'post_parent'       => $term,
						'fields'            => 'ids',
						'post__not_in'		=> $excluded_products,
						'post__in'			=> $included_products,
						'tax_query' => array(
							array(
								'taxonomy' 	=> 'product_cat',
								'field'    	=> 'id',
								'terms'    	=> $category,
							),
						),
						'suppress_filters' => false,
						'no_found_rows'          => true,
						'update_post_term_cache' => false,
						'update_post_meta_cache' => false,
						'cache_results'          => false
					);
				}
				if (($search_by == 1) || ($search_by == 4)){
					$products3 = array(
						'post_type'       	=> array ('product', 'product_variation'),
						'post_status'       => array('publish'),
						'posts_per_page'    => $max_items,
						'meta_query'        => array(
							array(
							'key'	 		=> '_sku',
							'value' 		=> '^'.$term,
							'compare' 		=> 'REGEXP'
							)
						),
						'fields'         	=> 'ids',
						'post__not_in'		=> $excluded_products,
						'post__in'			=> $included_products,
						'tax_query' => array(
							array(
								'taxonomy' 	=> 'product_cat',
								'field'    	=> 'id',
								'terms'    	=> $category,
							),
						),
						'suppress_filters' => false,
						'no_found_rows'          => true,
						'update_post_term_cache' => false,
						'update_post_meta_cache' => false,
						'cache_results'          => false
					);
				}
				if($search_by == 1) {
					$products = array_unique(array_merge(get_posts( $products3 ) ));
				} elseif ($search_by == 2){
					$products = array_unique(array_merge( get_posts( $products1 ), get_posts( $products2 ) ));
				} else {
					$products = array_unique(array_merge( get_posts( $products1 ), get_posts( $products2 ), get_posts( $products3 ) ));
				}
			} else {
			
				if (($search_by == 1) || ($search_by == 4)){
					$products1 = array(
						'post_type'       	=> array ('product', 'product_variation'),
						'post_status'    	=> array('publish'),
						'posts_per_page'    => $max_items,
						'meta_query'        => array(
							array(
							'key'	 		=> '_sku',
							'value' 		=> '^'.$term,
							'compare' 		=> 'REGEXP'
							)
						),
						'fields'            => 'ids',
						'post__not_in'		=> $excluded_products,
						'post__in'			=> $included_products,
						'tax_query' => array(
							array(
								'taxonomy' 	=> 'product_cat',
								'field'    	=> 'id',
								'terms'    	=> $category,
							),
						),
						'suppress_filters' => false,
						'no_found_rows'          => true,
						'update_post_term_cache' => false,
						'update_post_meta_cache' => false,
						'cache_results'          => false
					);
				}
				if (($search_by == 3) || ($search_by == 4)){
					$products2 = array(
						'post_type' 		=> array ('product', 'product_variation'),
						'post_status'       => array('publish'),
						'posts_per_page'    => $max_items,
						's'                 => $term,
						'fields'            => 'ids',
						'post__not_in'		=> $excluded_products,
						'post__in'			=> $included_products,
						'tax_query' => array(
							array(
								'taxonomy' 	=> 'product_cat',
								'field'    	=> 'id',
								'terms'    	=> $category,
							),
						),
						'suppress_filters' => false,
						'no_found_rows'          => true,
						'update_post_term_cache' => false,
						'update_post_meta_cache' => false,
						'cache_results'          => false
					);
				}
				if($search_by == 1) {
					$products = array_unique(array_merge(get_posts( $products1 ) ));
				} elseif($search_by == 3) {
					$products = array_unique(array_merge(get_posts( $products2 ) ));
				} else {
					$products = array_unique(array_merge( get_posts( $products1 ), get_posts( $products2 ) ));
				}
			
			}
		} else {
			if ( is_numeric( $term ) ) {
			
				if (($search_by == 2) || ($search_by == 4)){
					$products1 = array(
						'post_type'     	=> array ('product', 'product_variation'),
						'post_status'     	=> array('publish'),
						'posts_per_page'    => $max_items,
						'fields'            => 'ids',
						'post__not_in'		=> $excluded_products,
						'post__in'			=> $included_products,
						'suppress_filters' => false,
						'no_found_rows'          => true,
						'update_post_term_cache' => false,
						'update_post_meta_cache' => false,
						'cache_results'          => false
					);
					
					$products2 = array(
						'post_type'        	=> array ('product', 'product_variation'),
						'post_status'       => array('publish'),
						'posts_per_page'    => $max_items,
						'post_parent'       => $term,
						'fields'            => 'ids',
						'post__not_in'		=> $excluded_products,
						'post__in'			=> $included_products,
						'suppress_filters' => false,
						'no_found_rows'          => true,
						'update_post_term_cache' => false,
						'update_post_meta_cache' => false,
						'cache_results'          => false
					);
				}
				if (($search_by == 1) || ($search_by == 4)){
					$products3 = array(
						'post_type'       	=> array ('product', 'product_variation'),
						'post_status'       => array('publish'),
						'posts_per_page'    => $max_items,
						'meta_query'        => array(
							array(
							'key'	 		=> '_sku',
							'value' 		=> '^'.$term,
							'compare' 		=> 'REGEXP'
							)
						),
						'fields'         	=> 'ids',
						'post__not_in'		=> $excluded_products,
						'post__in'			=> $included_products,
						'suppress_filters' => false,
						'no_found_rows'          => true,
						'update_post_term_cache' => false,
						'update_post_meta_cache' => false,
						'cache_results'          => false
					);
				}
				if($search_by == 1) {
					$products = array_unique(array_merge(get_posts( $products3 ) ));
				} elseif ($search_by == 2){
					$products = array_unique(array_merge( get_posts( $products1 ), get_posts( $products2 ) ));
				} else {
					$products = array_unique(array_merge( get_posts( $products1 ), get_posts( $products2 ), get_posts( $products3 ) ));
				}
			} else {
			
				if (($search_by == 1) || ($search_by == 4)){
					$products1 = array(
						'post_type'       	=> array ('product', 'product_variation'),
						'post_status'    	=> array('publish'),
						'posts_per_page'    => $max_items,
						'meta_query'        => array(
							array(
							'key'	 		=> '_sku',
							'value' 		=> '^'.$term,
							'compare' 		=> 'REGEXP'
							)
						),
						'fields'            => 'ids',
						'post__not_in'		=> $excluded_products,
						'post__in'			=> $included_products,
						'suppress_filters' => false,
						'no_found_rows'          => true,
						'update_post_term_cache' => false,
						'update_post_meta_cache' => false,
						'cache_results'          => false
					);
				}
				if (($search_by == 3) || ($search_by == 4)){
					$products2 = array(
						'post_type' 		=> array ('product', 'product_variation'),
						'post_status'       => array('publish'),
						'posts_per_page'    => $max_items,
						's'                 => $term,
						'fields'            => 'ids',
						'post__not_in'		=> $excluded_products,
						'post__in'			=> $included_products,
						'suppress_filters' => false,
						'no_found_rows'          => true,
						'update_post_term_cache' => false,
						'update_post_meta_cache' => false,
						'cache_results'          => false
					);
				}
				if($search_by == 1) {
					$products = array_unique(array_merge(get_posts( $products1 ) ));
				} elseif($search_by == 3) {
					$products = array_unique(array_merge(get_posts( $products2 ) ));
				} else {
					$products = array_unique(array_merge( get_posts( $products1 ), get_posts( $products2 ) ));
				}
			
			}
		}
		
		// JSON encode and echo
		// Initialise suggestions array
		global $post, $woocommerce, $product;
		$suggestions = '';
		foreach ($products as $prod){
			$post_type = get_post_type($prod);
			if ( 'product' == $post_type ) {
				$attribute_html = '';
				$variation_template_type = '1';
				$product = wc_get_product($prod);
				if($product->visibility == 'hidden'){
					continue;
				}
				$product_has_variation = 'no';
				if( $product->has_child() ) {
					$product_has_variation = 'yes';
				}
				$id = $prod;
				$price = number_format((float)$product->get_price(), 2, '.', '');
				$price_html = $product->get_price_html();
                if(preg_match('/<ins>(.*?)<\/ins>/', $price_html)){ 
				    preg_match('/<ins>(.*?)<\/ins>/', $price_html, $matches);
				    $price_html = $matches[1];
				}
				/* Different Variation Setup for Attribute Option */
				if($this->options['variation_display_format'] == '2'){
					$variation_template_type = '2';
					$att_list = array();
					$add_to_cart = $product->add_to_cart_url();

					/* Display the attribute selection */
					$product_attributes = $product->get_attributes();
	                $p_atts = array();
	                foreach($product_attributes as $attribute){
	                	$att_name = preg_replace('/pa_/','',$attribute['name']);
	                	$att_name = preg_replace('/_/',' ',$att_name);
	                	$attribute_html .= '<select id="attribute-selection-'.$attribute['name'].'" class="'.$attribute['name'].' attribute-selection" data-attribute="'.$attribute['name'].'">';
	                		$attribute_html .= '<option selected="selected">Select '.$att_name.'</option>';
	                		$terms = get_the_terms( $id, $attribute['name']);
					      	foreach ( $terms as $term ) {
			      				$value = $term->name;
			      				$name = $term->name;
			      				$attribute_html .= '<option value="'.$value.'">'.$name.'</option>';
					        }
	                	$attribute_html .= '</select>';
	                	$p_atts[] = $attribute['name'];
	                }
	                if(empty($attribute_html)){
	                	$attribute_html = apply_filters('wcbulkorder_no_variations_found','<span></span>');
	                }
				}
				$price_html = strip_tags($price_html);
				$price = $price_html;
				$sku = $product->get_sku();
				$title = get_the_title($product->id);
				$title = html_entity_decode($title, ENT_COMPAT, 'UTF-8');
				$img = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'thumbnail');
	        	$img = $img[0];
				if (!empty($img)) {
					$img = $img;
				} else {
					$img = apply_filters( 'woocommerce_placeholder_img_src', WC_Bulk_Order_Form_Compatibility::WC()->plugin_url() . '/assets/images/placeholder.png' );
				}
			} else {
				continue;
			}
			if(!empty($id)) {
				$symbol = get_woocommerce_currency_symbol();
				$symbol = html_entity_decode($symbol, ENT_COMPAT, 'UTF-8');
				$price = html_entity_decode($price, ENT_COMPAT, 'UTF-8');
				// Initialise suggestion array
				$suggestion = array();
				$switch_data = isset($this->options['search_format']) ? $this->options['search_format'] : '1';
				$price = apply_filters('wc_bulk_order_form_price' , $price, $product);
				switch ($switch_data) {
					case 1:
						if (!empty($sku)) {
							$label = $sku.' - '.$title. ' - '.$price;
						} else {
							$label = $title. ' - '.$price;
						}
						break;
					case 2:
						if (!empty($sku)) {
							$label = $title. ' - '.$price.' - '.$sku;
						} else {
							$label = $title. ' - '.$price;
						}
						break;
					case 3:
						$label = $title .' - '.$price;
						break;
					case 4:
						if (!empty($sku)) {
							$label = $title. ' - '.$sku;
						} else {
							$label = $title;
						}
						break;
					case 5:
						$label = $title;
						break;
				}
				$suggestion['label'] = apply_filters('wc_bulk_order_form_label', $label, $price, $title, $sku, $symbol);
				$suggestion['price'] = apply_filters('wc_bulk_order_form_price' , $price, $product);
				$suggestion['symbol'] = $symbol;
				$suggestion['id'] = $id;
				$suggestion['imgsrc'] = $img;
				$suggestion['has_variation'] = $product_has_variation;
				$suggestion['attribute_html'] = $attribute_html;
				$suggestion['add_to_cart_url'] = $add_to_cart;
				$suggestion['variation_template'] = $variation_template_type;
				if (!empty($variation_id)) {
					$suggestion['variation_id'] = $variation_id;
				}
				// Add suggestion to suggestions array
				$suggestions[]= $suggestion;
			}
		}
		// JSON encode and echo
		$response = $_GET["callback"] . "(" . json_encode($suggestions) . ")";
		//print_r($response);
		echo $response;
		//echo 'var WCBulkOrder.product_has_variation = '.$this->product_has_variation.';';
		// Don't forget to exit!
		exit;
	}
	
		function bulk_order_variation_search(){
		// Query for suggestions
		$term = $_REQUEST['term'];
		$excluded_products = array();
		$excluded_products = apply_filters('wc_bulk_order_excluded_products', $excluded_products);
		$included_products = array();
		$included_products = apply_filters('wc_bulk_order_included_products', $included_products);
		if (empty($term)) die();
		$products1 = array(
			'post_type'					=> array('product_variation'),
			'post_status'       		=> array('publish'),
			'post_parent'       		=> $term,
			'fields'            		=> 'ids',
			'post__not_in'				=> $excluded_products,
			'post__in'					=> $included_products,
			'suppress_filters' 			=> false,
			'no_found_rows'          	=> true,
			'update_post_term_cache' 	=> false,
			'update_post_meta_cache' 	=> false,
			'cache_results'          	=> false,
			'posts_per_page'			=> -1
		);
		$products = get_posts( $products1 );
		
		// JSON encode and echo
		// Initialise suggestions array
		
		global $post, $woocommerce, $product;
		$suggestions = '';
		
		foreach ($products as $prod){
			$post_type = get_post_type($prod);
						
			if ( 'product_variation' == $post_type ) {
                    $product = new WC_Product_Variation($prod);
                    $parent = wc_get_product($prod);
                    $id = $product->variation_id;
                    $price = number_format((float)$product->price, 2, '.', '');
                    $price_html = $product->get_price_html();
                    if(preg_match('/<ins>(.*?)<\/ins>/', $price_html)){ 
					    preg_match('/<ins>(.*?)<\/ins>/', $price_html, $matches);
					    $price_html = $matches[1];
					}
					$price_html = strip_tags($price_html);
					$price = $price_html;
                    $price = apply_filters('wc_bulk_order_form_price' , $price, $product);
                    $sku = $product->get_sku();
                    $title = '';
                    $attributes = $product->get_variation_attributes();
                    $img = apply_filters( 'woocommerce_placeholder_img_src', WC_Bulk_Order_Form_Compatibility::WC()->plugin_url() . '/assets/images/placeholder.png' );
                    foreach ( $attributes as $name => $value) {
                    	$name = str_ireplace("attribute_", "", $name);
                    	$terms = get_the_terms( $product->id, $name);
				      	foreach ( $terms as $term ) {
			      			if(strtolower($term->name) == $value){
			      				$value = $term->name;
			      			}
				        }
						$attr_name = $name;
						$attr_value = str_replace('-', ' ', $value);
						if($this->options['attribute_style'] === 'true'){
							$title .= $attr_value.' ';
						} else{
							if (strstr($attr_name, 'pa_')){
		                        $atts = get_the_terms($parent->id ,$attr_name);
		                        $attr_name_clean = WC_Bulk_Order_Form_Compatibility::wc_attribute_label($attr_name);
		                    }
		                    else {
		                        $np = explode("-",str_replace("attribute_","",$attr_name));
		                        $attr_name_clean = ucwords(implode(" ",$np));
		                    }
		                    $attr_name_clean = str_replace("attribute_pa_","",$attr_name_clean);
		                    $attr_name_clean = str_replace("Attribute_pa_","",$attr_name_clean);
							$title .= ' '.$attr_name_clean . ": " . $attr_value;
							}
						$title = html_entity_decode($title, ENT_COMPAT, 'UTF-8');
                    }
    				$parent_image = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'thumbnail');
    				$parent_image = $parent_image[0];
    				$img = wp_get_attachment_image_src( get_post_thumbnail_id( $parent->id ), 'thumbnail');
    				$img = $img[0];
					if (!empty($img)) {
						$img = $img;
					} elseif (!empty($parent_image)) {
						$img = $parent_image;
					} else {
						$img = apply_filters( 'woocommerce_placeholder_img_src', WC_Bulk_Order_Form_Compatibility::WC()->plugin_url() . '/assets/images/placeholder.png' );
					}
			}
			if(!empty($id)) {	
				$symbol = get_woocommerce_currency_symbol();
				$symbol = html_entity_decode($symbol, ENT_COMPAT, 'UTF-8');
				$price = html_entity_decode($price, ENT_COMPAT, 'UTF-8');
				// Initialise suggestion array
				$suggestion = array();
				$variation_switch_data = isset($this->options['variation_search_format']) ? $this->options['variation_search_format'] : '1';
				switch ($variation_switch_data) {
					case 1:
						if (!empty($sku)) {
							$label = $sku.' - '.$title. ' - '.$price;
						} else {
							$label = $title. ' - '.$price;
						}
						break;
					case 2:
						if (!empty($sku)) {
							$label = $title. ' - '.$price.' - '.$sku;
						} else {
							$label = $title. ' - '.$price;
						}
						break;
					case 3:
						$label = $title .' - '.$price;
						break;
					case 4:
						if (!empty($sku)) {
							$label = $title. ' - '.$sku;
						} else {
							$label = $title;
						}
						break;
					case 5:
						$label = $title;
						break;
				}
				$suggestion['label'] = $label;
				$suggestion['price'] = $price;
				$suggestion['symbol'] = $symbol;
				$suggestion['id'] = $id;
				$suggestion['imgsrc'] = $img;
				$suggestion['attributes'] = $product_attributes;
				if (!empty($variation_id)) {
					$suggestion['variation_id'] = $variation_id;
				}
				// Add suggestion to suggestions array
				$suggestions[]= $suggestion;
			}
		}
		// JSON encode and echo
		$response = $_GET["callback"] . "(" . json_encode($suggestions) . ")";
		//print_r($response);
		echo $response;
		// Don't forget to exit!
		exit;
	}
}