<?php

class WF_Shipping_UPS_Admin
{
	private $ups_services = array(
		// Domestic
		"12" => "3 Day Select",
		"03" => "Ground",
		"02" => "2nd Day Air",
		"59" => "2nd Day Air AM",
		"01" => "Next Day Air",
		"13" => "Next Day Air Saver",
		"14" => "Next Day Air Early AM",

		// International
		"11" => "Standard",
		"07" => "Worldwide Express",
		"54" => "Worldwide Express Plus",
		"08" => "Worldwide Expedited",
		"65" => "Worldwide Saver",
		
		// SurePost
		"92" =>	"SurePost Less than 1 lb",
		"93" =>	"SurePost 1 lb or Greater",
		"94" =>	"SurePost BPM",
		"95" =>	"SurePost Media",
		
		//New Services
		"M2" => "First Class Mail",
		"M3" => "Priority Mail",
		"M4" => "Expedited Mail Innovations ",
		"M5" => "Priority Mail Innovations ",
		"M6" => "EconomyMail Innovations ",
		"70" => "Access Point Economy ",
		"96" => "Worldwide Express Freight",
	);
	
	public function __construct(){
		$this->wf_init();

		//Print Shipping Label.
		if ( is_admin() ) { 
			add_action( 'add_meta_boxes', array( $this, 'wf_add_ups_metabox' ), 15 );
			add_action('admin_notices', array( $this, 'wf_admin_notice'), 15);
		}
		
		if ( isset( $_GET['wf_ups_shipment_confirm'] ) ) {
			add_action( 'init', array( $this, 'wf_ups_shipment_confirm' ), 15 );
		}
		else if ( isset( $_GET['wf_ups_shipment_accept'] ) ) {
			add_action( 'init', array( $this, 'wf_ups_shipment_accept' ), 15 );
		}
		else if ( isset( $_GET['wf_ups_print_label'] ) ) {
			add_action( 'init', array( $this, 'wf_ups_print_label' ), 15 );
		}	
		else if ( isset( $_GET['wf_ups_void_shipment'] ) ) {
			add_action( 'init', array( $this, 'wf_ups_void_shipment' ), 15 );
		}
	}

	function wf_admin_notice(){
		global $pagenow;
		global $post;
		
		if(!isset($_GET["wfupsmsg"]) && empty($_GET["wfupsmsg"]) ) {
			return;
		}
	
		$wfupsmsg = $_GET["wfupsmsg"];
		
		switch ($wfupsmsg) {
			case "0":
				echo '<div class="error"><p>UPS: Sorry, An unexpected error occurred.</p></div>';
				break;
			case "1":
				echo '<div class="updated"><p>UPS: Shipment initiated successfully. Please proceed to Step 2, Accept Shipment.</p></div>';
				break;
			case "2":
				$wfupsmsg = get_post_meta( $post->ID, 'wfupsmsg', true);
				echo '<div class="error"><p>UPS: '.$wfupsmsg.'</p></div>';
				break;
			case "3":
				echo '<div class="updated"><p>UPS: Shipment accepted successfully. Labels are ready for printing. </p></div>';
				break;
			case "4":
				echo '<div class="updated"><p>UPS: Cancellation of shipment completed successfully. You can re-initiate shipment.</p></div>';
				break;
			case "5":
				echo '<div class="updated"><p>UPS: Client side reset of labels and shipment completed. You can re-initiate shipment now.</p></div>';
				break;
			default:
				break;
		}
	}

	private function wf_init() {
		global $post;
		
		$shipmentconfirm_requests 			= array();
		// Load UPS Settings.
		$this->settings 					= get_option( 'woocommerce_'.WF_UPS_ID.'_settings', null ); 
		//Print Label Settings.
		$this->disble_ups_print_label		= isset( $this->settings['disble_ups_print_label'] ) ? $this->settings['disble_ups_print_label'] : '';
		$this->packing_method  				= isset( $this->settings['packing_method'] ) ? $this->settings['packing_method'] : 'per_item';
		$this->disble_shipment_tracking		= isset( $this->settings['disble_shipment_tracking'] ) ? $this->settings['disble_shipment_tracking'] : 'TrueForCustomer';
		$this->manual_weight_dimensions		= isset( $this->settings['manual_weight_dimensions'] ) ? $this->settings['manual_weight_dimensions'] : 'no';
        $this->show_label_in_browser	    = isset( $this->settings['show_label_in_browser'] ) ? $this->settings['show_label_in_browser'] : 'no';
		$this->box_max_weight			=	isset($this->settings[ 'box_max_weight']) ?  $this->settings[ 'box_max_weight'] : '';
		$this->weight_packing_process	=	isset($this->settings[ 'weight_packing_process']) ? $this->settings[ 'weight_packing_process'] : '';
		// Units
		$this->units			= isset( $this->settings['units'] ) ? $this->settings['units'] : 'imperial';

		if ( $this->units == 'metric' ) {
			$this->weight_unit = 'KGS';
			$this->dim_unit    = 'CM';
		} else {
			$this->weight_unit = 'LBS';
			$this->dim_unit    = 'IN';
		}

		include_once( 'class-wf-shipping-ups-tracking.php' );
	}

	function wf_add_ups_metabox(){
		global $post;
		
		if( $this->disble_ups_print_label == 'yes' ) {
			return;
		}

		if ( !$post ) return;

		$order = $this->wf_load_order( $post->ID );
		if ( !$order ) return; 
		
		add_meta_box( 'CyDUPS_metabox', __( 'UPS Shipment Label', 'ups-woocommerce-shipping' ), array( $this, 'wf_ups_metabox_content' ), 'shop_order', 'side', 'default' );
	}

	function wf_ups_metabox_content(){
		global $post;
		$shipmentId = '';
		
		$order 								= $this->wf_load_order( $post->ID );
		$shipping_service_data				= $this->wf_get_shipping_service_data( $order ); 
		$default_service_type 				= $shipping_service_data['shipping_service'];

		$created_shipments_details_array 	= get_post_meta( $post->ID, 'ups_created_shipments_details_array', true );
		if( empty( $created_shipments_details_array ) ) {

			$download_url = admin_url( '/?wf_ups_shipment_confirm='.base64_encode( $shipmentId.'|'.$post->ID ) );
			?>

			<strong><?php _e( 'Step 1: Initiate your shipment.', 'ups-woocommerce-shipping' ); ?></strong></br>
			
			Select Preferred Service:
			<img class="help_tip" style="float:none;" data-tip="<?php _e( 'Contact UPS for more info on this services.', 'ups-woocommerce-shipping' ); ?>" src="<?php echo WC()->plugin_url();?>/assets/images/help.png" height="16" width="16" />
			<?php
				echo '<ul><li class="wide"><select class="select" id="ups_manual_service">';
				foreach($this->ups_services as $service_code => $service_name){
					echo '<option value="'.$service_code.'" ' . selected($default_service_type, $service_code) . ' >'.$service_name.'</option>';
				}
				echo '</select></li>';
				
				echo '<li><label for="ups_cod"><input type="checkbox" style="" id="ups_cod" name="ups_cod" class="">' . __('Collect On Delivery', 'ups-woocommerce-shipping') . '</label><img class="help_tip" style="float:none;" data-tip="'.__( 'Collect On Delivery would be applicable only for single package which may contain single or multiple product(s).', 'ups-woocommerce-shipping' ).'" src="'.WC()->plugin_url().'/assets/images/help.png" height="16" width="16" /></li>';
				
				echo '<li><label for="ups_return"><input type="checkbox" style="" id="ups_return" name="ups_return" class="">' . __('Include Return Label', 'ups-woocommerce-shipping') . '</label><img class="help_tip" style="float:none;" data-tip="'.__( 'You can generate the return label only for single package order.', 'ups-woocommerce-shipping' ).'" src="'.WC()->plugin_url().'/assets/images/help.png" height="16" width="16" /></li>';
				
				echo '<li><label for="ups_sat_delivery"><input type="checkbox" style="" id="ups_sat_delivery" name="ups_sat_delivery" class="">' . __('Saturday Delivery', 'ups-woocommerce-shipping') . '</label><img class="help_tip" style="float:none;" data-tip="'.__( 'Saturday Delivery from UPS allows you to stretch your business week to Saturday', 'ups-woocommerce-shipping' ).'" src="'.WC()->plugin_url().'/assets/images/help.png" height="16" width="16" /></li>';
				
				if($this->manual_weight_dimensions == 'yes'){
			?>
					<li><strong>Weight:&nbsp;</strong><input type="text" id="manual_weight" size="3" />&nbsp;<?=$this->weight_unit;?><br>     
					<strong>&nbsp;Height:&nbsp;</strong><input type="text" id="manual_height" size="3" />&nbsp;<?=$this->dim_unit;?><br>
					<strong>&nbsp;&nbsp;Width:&nbsp;</strong><input type="text" id="manual_width" size="3" />&nbsp;<?=$this->dim_unit;?><br>
					<strong>Length:&nbsp;</strong><input type="text" id="manual_length" size="3" />&nbsp;<?=$this->dim_unit;?>
					</li>                                                      
			<?php
				}
			?>
		
			<a class="button button-primary tips ups_create_shipment" href="<?php echo $download_url; ?>" data-tip="<?php _e( 'Confirm Shipment', 'ups-woocommerce-shipping' ); ?>"><?php _e( 'Confirm Shipment', 'ups-woocommerce-shipping' ); ?></a><hr style="border-color:#0074a2">
			
			<script type="text/javascript">
				jQuery("a.ups_create_shipment").on("click", function() {
				   location.href = this.href + '&weight=' + jQuery('#manual_weight').val() +
					'&length=' + jQuery('#manual_length').val()
					+ '&width=' + jQuery('#manual_width').val()
					+ '&height=' + jQuery('#manual_height').val()
					+ '&cod=' + jQuery('#ups_cod').is(':checked')
					+ '&sat_delivery=' + jQuery('#ups_sat_delivery').is(':checked')
					+ '&is_return_label=' + jQuery('#ups_return').is(':checked')
					+ '&wf_ups_selected_service=' + jQuery('#ups_manual_service').val();
				   return false;
				});
			</script>
			
			<?php 
		}
		else {
			foreach ( $created_shipments_details_array as $shipmentId => $created_shipments_details ){
				$ups_label_details_array = get_post_meta( $post->ID, 'ups_label_details_array', true );
				?>
				<strong><?php _e( 'Shipment ID: ', 'ups-woocommerce-shipping' ); ?></strong><?php echo $shipmentId ?><hr style="border-color:#0074a2">
				<?php
				
				if( empty($ups_label_details_array) ) {
					$download_url = admin_url( '/?wf_ups_shipment_accept='.base64_encode( $shipmentId.'|'.$post->ID ) );
				?>
					<strong><?php _e( 'Step 2: Accept your shipment.', 'ups-woocommerce-shipping' ); ?></strong></br>
					<a class="button button-primary tips" href="<?php echo $download_url; ?>" data-tip="<?php _e('Accept Shipment', 'ups-woocommerce-shipping'); ?>"><?php _e( 'Accept Shipment', 'ups-woocommerce-shipping' ); ?></a><hr style="border-color:#0074a2">
				<?php
				}
				else {
					$ups_label_details_array = get_post_meta( $post->ID, 'ups_label_details_array', true );					
					if( !empty($ups_label_details_array) ) { 
						// Multiple labels for each package.
						$index = 0;
						foreach ( $ups_label_details_array[$shipmentId] as $ups_label_details ) {
							$label_extn_code 	= $ups_label_details["Code"];
                            $tracking_number 	= isset( $ups_label_details["TrackingNumber"] ) ? $ups_label_details["TrackingNumber"] : '';
							$download_url 		= admin_url( '/?wf_ups_print_label='.base64_encode( $shipmentId.'|'.$post->ID.'|'.$label_extn_code.'|'.$index.'|'.$tracking_number ) );
							$post_fix_label		= '';
							
							if( count($ups_label_details_array) > 1 ) {
								$post_fix_label = '#'.( $index + 1 );
							}
                            
                            if( "yes" == $this->show_label_in_browser ) {
                                $target_val = "_blank";
                            }
                            else {
                                $target_val = "_self";
                            }							
						?>
							<strong><?php _e( 'Tracking No: ', 'ups-woocommerce-shipping' ); ?></strong><a href="http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums=<?php echo $ups_label_details["TrackingNumber"] ?>" target="_blank"><?php echo $ups_label_details["TrackingNumber"] ?></a><br/>
							<a class="button button-primary tips" href="<?php echo $download_url; ?>" data-tip="<?php _e( 'Print Label ', 'ups-woocommerce-shipping' );echo $post_fix_label; ?>" target="<?php echo $target_val; ?>"><?php _e( 'Print Label ', 'ups-woocommerce-shipping' );echo $post_fix_label ?></a><hr style="border-color:#0074a2">
						<?php
							// Return Label Link
							if(isset($created_shipments_details['return'])&&!empty($created_shipments_details['return'])){
								$return_shipment_id=current(array_keys($created_shipments_details['return'])); // only one return label is considered now
								$ups_return_label_details_array = get_post_meta( $post->ID, 'ups_return_label_details_array', true );
								if(is_array($ups_return_label_details_array)&&isset($ups_return_label_details_array[$return_shipment_id])){// check for return label accepted data
									$ups_return_label_details=$ups_return_label_details_array[$return_shipment_id];
									if(is_array($ups_return_label_details)){
										$ups_return_label_detail=current($ups_return_label_details);
										$label_index=0;// as we took only one label so index is zero
										$return_download_url = admin_url( '/?wf_ups_print_label='.base64_encode( $return_shipment_id.'|'.$post->ID.'|'.$label_extn_code.'|'.$label_index.'|return' ) );
										?>
										<strong><?php _e( 'Tracking No: ', 'ups-woocommerce-shipping' ); ?></strong><a href="http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums=<?php echo $ups_return_label_detail["TrackingNumber"] ?>" target="_blank"><?php echo $ups_return_label_detail["TrackingNumber"] ?></a><br/>
										<a class="button button-primary tips" href="<?php echo $return_download_url; ?>" data-tip="<?php _e( 'Print Return Label ', 'ups-woocommerce-shipping' );echo $post_fix_label; ?>" target="<?php echo $target_val; ?>"><?php _e( 'Print Return Label ', 'ups-woocommerce-shipping' );echo $post_fix_label ?></a><hr style="border-color:#0074a2">
										<?php
									}
								}
							}
							// EOF Return Label Link
							
							$index = $index + 1;
						}
					}
					$download_url = admin_url( '/?wf_ups_void_shipment='.base64_encode( $shipmentId.'|'.$post->ID ) );
				?>
					<strong><?php _e( 'Cancel the Shipment', 'ups-woocommerce-shipping' ); ?></strong></br>
					<a class="button tips" href="<?php echo $download_url; ?>" data-tip="<?php _e( 'Void Shipment', 'ups-woocommerce-shipping' ); ?>"><?php _e( 'Void Shipment', 'ups-woocommerce-shipping' ); ?></a><hr style="border-color:#0074a2">
				<?php
				}
				// Not expecting an array with multiple items at this point of time.
				break;
			}
		}
	}

	function wf_ups_shipment_confirmrequest($order,$return_label=false) {
		global $post;
		
		$ups_settings 					= get_option( 'woocommerce_'.WF_UPS_ID.'_settings', null ); 
		// Define user set variables
		$ups_enabled					= isset( $ups_settings['enabled'] ) ? $ups_settings['enabled'] : '';
		$ups_title						= isset( $ups_settings['title'] ) ? $ups_settings['title'] : 'UPS';
		$ups_availability    			= isset( $ups_settings['availability'] ) ? $ups_settings['availability'] : 'all';
		$ups_countries       			= isset( $ups_settings['countries'] ) ? $ups_settings['countries'] : array();
		// WF: Print Label Settings.
		$print_label_type     			= isset( $ups_settings['print_label_type'] ) ? $ups_settings['print_label_type'] : 'gif';
		$ship_from_address      		= isset( $ups_settings['ship_from_address'] ) ? $ups_settings['ship_from_address'] : 'origin_address';
		$phone_number 					= isset( $ups_settings['phone_number'] ) ? $ups_settings['phone_number'] : '';
		$ups_manual_weight_dimensions	= isset( $ups_settings['manual_weight_dimensions'] ) ? $ups_settings['manual_weight_dimensions'] : 'no';
		// API Settings
		$ups_user_name        			= isset( $ups_settings['ups_user_name'] ) ? $ups_settings['ups_user_name'] : '';
		$ups_display_name        		= isset( $ups_settings['ups_display_name'] ) ? $ups_settings['ups_display_name'] : '';
		$ups_user_id         			= isset( $ups_settings['user_id'] ) ? $ups_settings['user_id'] : '';
		$ups_password        			= isset( $ups_settings['password'] ) ? $ups_settings['password'] : '';
		$ups_access_key      			= isset( $ups_settings['access_key'] ) ? $ups_settings['access_key'] : '';
		$ups_shipper_number  			= isset( $ups_settings['shipper_number'] ) ? $ups_settings['shipper_number'] : '';
		$ups_negotiated      			= isset( $ups_settings['negotiated'] ) && $ups_settings['negotiated'] == 'yes' ? true : false;
        $ups_residential		        = isset( $ups_settings['residential'] ) && $ups_settings['residential'] == 'yes' ? true : false;
		
		$shipping_first_name 		= $order->shipping_first_name;
		$shipping_last_name 		= $order->shipping_last_name;
		$shipping_full_name			= $shipping_first_name.' '.$shipping_last_name;
		$shipping_company 			= $order->shipping_company;
		$shipping_address_1 		= $order->shipping_address_1;
		$shipping_address_2 		= $order->shipping_address_2;
		$shipping_city 				= $order->shipping_city;
		$shipping_postcode 			= $order->shipping_postcode;
		$shipping_country 			= $order->shipping_country;
		$shipping_state 			= $order->shipping_state;
		$billing_email 				= $order->billing_email;
		$billing_phone 				= $order->billing_phone;

		$ups_origin_addressline 	= $order->billing_address_1.', '.$order->billing_address_2;
		$ups_origin_city 			= $order->billing_city;
		$ups_origin_postcode 		= $order->billing_postcode;
		$origin_country				= $order->billing_country;
		$origin_state 				= $order->billing_state;
		
		$cod						= get_post_meta($order->id,'_wf_ups_cod',true);
		$sat_delivery				= get_post_meta($order->id,'_wf_ups_sat_delivery',true);
		$order_total				= $order->get_total();
		$oder_currency				= $order->get_order_currency();
		
		$ship_options=array('return_label'=>$return_label); // Array to pass options like return label on the fly.
		
		if( 'billing_address' == $ship_from_address ) { 
			$ups_display_name	= $order->billing_company;
			$phone_number		= $billing_phone;
			$billing_full_name	= $order->billing_first_name.' '.$order->billing_last_name;
			$ups_user_name		= $billing_full_name;
		}
		else {
			$ups_origin_addressline 		= isset( $ups_settings['origin_addressline'] ) ? $ups_settings['origin_addressline'] : '';
			$ups_origin_city 				= isset( $ups_settings['origin_city'] ) ? $ups_settings['origin_city'] : '';
			$ups_origin_postcode 			= isset( $ups_settings['origin_postcode'] ) ? $ups_settings['origin_postcode'] : '';
			$ups_origin_country_state 		= isset( $ups_settings['origin_country_state'] ) ? $ups_settings['origin_country_state'] : '';
			
			if ( strstr( $ups_origin_country_state, ':' ) ) :
				// WF: Following strict php standards.
				$origin_country_state_array 	= explode(':',$ups_origin_country_state);
				$origin_country 				= current($origin_country_state_array);
				$origin_country_state_array 	= explode(':',$ups_origin_country_state);
				$origin_state   				= end($origin_country_state_array);
			else :
				$origin_country = $ups_origin_country_state;
				$origin_state   = '';
			endif;
			
                        $origin_state = ( isset( $origin_state ) && !empty( $origin_state ) ) ? $origin_state : $ups_settings['origin_custom_state'];
                        
			if( '' == $ups_display_name ) {
				$ups_display_name = $ups_user_name;
			}
		}

		$shipping_service_data	= $this->wf_get_shipping_service_data( $order ); 
		$shipping_method		= $shipping_service_data['shipping_method'];
		$shipping_service		= $shipping_service_data['shipping_service'];
		$shipping_service_name	= $shipping_service_data['shipping_service_name'];

		if( $ups_manual_weight_dimensions == 'yes') {
			$package_data = $this->wf_get_package_data_manual( $order,$ship_options);
		}
		else {
			$package_data = $this->wf_get_package_data( $order,$ship_options);
		}

		if( empty( $package_data ) ) {
			return false;
		}
		
		$shipment_description = $this->wf_get_shipment_description( $order );
		$xml_request = '<?xml version="1.0" ?>';
		$xml_request .= '<AccessRequest xml:lang="en-US">';
		$xml_request .= '<AccessLicenseNumber>'.$ups_access_key.'</AccessLicenseNumber>';
		$xml_request .= '<UserId>'.$ups_user_id.'</UserId>';
		$xml_request .= '<Password>'.$ups_password.'</Password>';
		$xml_request .= '</AccessRequest>';
		$xml_request .= '<?xml version="1.0" ?>';
		$xml_request .= '<ShipmentConfirmRequest>';
		$xml_request .= '<Request>';
		$xml_request .= '<TransactionReference>';
		$xml_request .= '<CustomerContext>'.$order->id.'</CustomerContext>';
		$xml_request .= '<XpciVersion>1.0001</XpciVersion>';
		$xml_request .= '</TransactionReference>';
		$xml_request .= '<RequestAction>ShipConfirm</RequestAction>';
		$xml_request .= '<RequestOption>nonvalidate</RequestOption>';
		$xml_request .= '</Request>';
		$xml_request .= '<Shipment>';
		$xml_request .= '<Description>'.htmlspecialchars( $shipment_description ).'</Description>';
		if($return_label){
			$xml_request .= '<ReturnService><Code>9</Code></ReturnService>';
		}
		$xml_request .= '<Shipper>';
		$xml_request .= '<Name>'.htmlspecialchars( $ups_user_name ).'</Name>';
		$xml_request .= '<AttentionName>'.htmlspecialchars( $ups_display_name ).'</AttentionName>';
		if( strlen($phone_number) < 10) {
			$xml_request .= '<PhoneNumber>0000000000</PhoneNumber>';
		}
		else {
			$xml_request .= '<PhoneNumber>'.htmlspecialchars( $phone_number ).'</PhoneNumber>';
		}
		$xml_request .= '<ShipperNumber>'.$ups_shipper_number.'</ShipperNumber>';
		$xml_request .= '<Address>';
		$xml_request .= '<AddressLine1>'.htmlspecialchars( $ups_origin_addressline ).'</AddressLine1>';
		$xml_request .= '<City>'.$ups_origin_city.'</City>';
		$xml_request .= '<StateProvinceCode>'.$origin_state.'</StateProvinceCode>';
		$xml_request .= '<CountryCode>'.$origin_country.'</CountryCode>';
		$xml_request .= '<PostalCode>'.$ups_origin_postcode.'</PostalCode>';
		$xml_request .= '</Address>';
		$xml_request .= '</Shipper>';
                if($return_label){
                $xml_request .= '<ShipTo>';
		$xml_request .= '<CompanyName>'.htmlspecialchars( $ups_user_name ).'</CompanyName>';
		$xml_request .= '<AttentionName>'.htmlspecialchars( $ups_display_name ).'</AttentionName>';
		if( strlen( $phone_number ) < 10) {
			$xml_request .= '<PhoneNumber>0000000000</PhoneNumber>';
		}
		else {
			$xml_request .= '<PhoneNumber>'.htmlspecialchars( $phone_number ).'</PhoneNumber>';
		}
		$xml_request .= '<Address>';
		$xml_request .= '<AddressLine1>'.htmlspecialchars( $ups_origin_addressline ).'</AddressLine1>';
		//$xml_request .= '<AddressLine2>'.htmlspecialchars( $ups_origin_addressline ).'</AddressLine2>';
		$xml_request .= '<City>'.$ups_origin_city.'</City>';
		$xml_request .= '<StateProvinceCode>'.$origin_state.'</StateProvinceCode>';
		$xml_request .= '<CountryCode>'.$origin_country.'</CountryCode>';
		$xml_request .= '<PostalCode>'.$ups_origin_postcode.'</PostalCode>';
                }else{
		$xml_request .= '<ShipTo>';
		if( '' == trim( $shipping_company ) ) {
			$shipping_company = '-';
		}		
		
		$xml_request .= '<CompanyName>'.htmlspecialchars( $shipping_company ).'</CompanyName>';
		$xml_request .= '<AttentionName>'.htmlspecialchars( $shipping_full_name ).'</AttentionName>';
		if( strlen( $billing_phone ) < 10) {
			$xml_request .= '<PhoneNumber>0000000000</PhoneNumber>';
		}
		else {
			$xml_request .= '<PhoneNumber>'.htmlspecialchars( $billing_phone ).'</PhoneNumber>';
		}
		$xml_request .= '<Address>';
		$xml_request .= '<AddressLine1>'.htmlspecialchars( $shipping_address_1 ).'</AddressLine1>';
		$xml_request .= '<AddressLine2>'.htmlspecialchars( $shipping_address_2 ).'</AddressLine2>';
		$xml_request .= '<City>'.$shipping_city.'</City>';
		$xml_request .= '<StateProvinceCode>'.$shipping_state.'</StateProvinceCode>';
		$xml_request .= '<CountryCode>'.$shipping_country.'</CountryCode>';
		$xml_request .= '<PostalCode>'.$shipping_postcode.'</PostalCode>';
                }
        if( $ups_residential ) {
            $xml_request .= '<ResidentialAddress />';
        }

		$xml_request .= '</Address>';
		$xml_request .= '</ShipTo>';
		$xml_request .= '<Service>';
		$xml_request .= '<Code>'.$shipping_service.'</Code>';
		$xml_request .= '<Description>'.htmlspecialchars( $shipping_service_name ).'</Description>';
		$xml_request .= '</Service>';
		$xml_request .= '<PaymentInformation>';
		$xml_request .= '<Prepaid>';
		$xml_request .= '<BillShipper>';
		$xml_request .= '<AccountNumber>'.$ups_shipper_number.'</AccountNumber>';
		$xml_request .= '</BillShipper>';
		$xml_request .= '</Prepaid>';
		$xml_request .= '</PaymentInformation>';
		foreach ( $package_data as $package ) {
			$xml_request .= $package;
		}
		// Negotiated Rates Flag
        if ( $ups_negotiated ) {
            $xml_request .= '<RateInformation>';
            $xml_request .= '<NegotiatedRatesIndicator />';
            $xml_request .= '</RateInformation>';
        }
		
		// Ship From Address is required for Return Label.
        // For return label, Ship From address will be set as Shipping Address of order.
		if($return_label){
			$xml_request .= '<ShipFrom>'."\n"; 
				$xml_request .= '<CompanyName>'.htmlspecialchars( $shipping_full_name ).'</CompanyName>'."\n";
				$xml_request .= '<AttentionName>'.htmlspecialchars( $shipping_company ).'</AttentionName>'."\n";
				$xml_request .= '<Address>'."\n";
					$xml_request .= '<AddressLine1>'.htmlspecialchars( $shipping_address_1 ).'</AddressLine1>'."\n";
					$xml_request .= '<City>'.$shipping_city.'</City>'."\n";
					$xml_request .= '<StateProvinceCode>'.$shipping_state.'</StateProvinceCode>'."\n";
					$xml_request .= '<PostalCode>'.$shipping_postcode.'</PostalCode>'."\n";
					$xml_request .= '<CountryCode>'.$shipping_country.'</CountryCode>'."\n";
				$xml_request .= '</Address>'."\n";
			$xml_request .= '</ShipFrom>';
		}
		
		$shipmentServiceOptions = array();
		if($sat_delivery){
			$shipmentServiceOptions[]='<SaturdayDelivery></SaturdayDelivery>'."\n";
		}
		if(sizeof($shipmentServiceOptions)){
			$xml_request .= '<ShipmentServiceOptions>'."\n";
			foreach ($shipmentServiceOptions as $key => $value) {
				$xml_request .= $value;
			}
			$xml_request .= '</ShipmentServiceOptions>'."\n";
		}

		$xml_request .= '</Shipment>';
		$xml_request .= '<LabelSpecification>';
		$xml_request .= '<LabelPrintMethod>';
		$xml_request .= $this->get_code_from_label_type( $print_label_type );
		$xml_request .= '</LabelPrintMethod>';
		$xml_request .= '<HTTPUserAgent>Mozilla/4.5</HTTPUserAgent>';

		if( 'zpl' == $print_label_type || 'epl' == $print_label_type || 'png' == $print_label_type ) {
			$xml_request .= '<LabelStockSize>';
			$xml_request .= '<Height>4</Height>';
			$xml_request .= '<Width>6</Width>';
			$xml_request .= '</LabelStockSize>';
		}
		$xml_request .= '<LabelImageFormat>';
		$xml_request .= $this->get_code_from_label_type( $print_label_type );
		$xml_request .= '</LabelImageFormat>';
		$xml_request .= '</LabelSpecification>';
		$xml_request .= '</ShipmentConfirmRequest>';
		
		$xml_request	=	apply_filters('wf_ups_shipment_confirm_request', $xml_request, $order);
		return $xml_request;
	}
	private function get_code_from_label_type( $label_type ){
		switch ($label_type) {
			case 'zpl':
				$code_val = 'ZPL';
				break;
			case 'epl':
				$code_val = 'EPL';
				break;
			case 'png':
				$code_val = 'ZPL';
				break;
			default:
				$code_val = 'GIF';
				break;
		}
		return '<Code>'.$code_val.'</Code>';
	}
	
	private function wf_get_shipment_description( $order ){
		$shipment_description	= '';
		$order_items	= $order->get_items();

		foreach( $order_items as $order_item ) {
			$product_data	= wc_get_product( $order_item['variation_id'] ? $order_item['variation_id'] : $order_item['product_id'] );
			$title 	= $product_data->get_title();
			$shipment_description 	.= $title.', ';
		}

		if ('' == $shipment_description ) {
			$shipment_description = 'Package/customer supplied.';
		}

		$shipment_description = ( strlen( $shipment_description ) >= 50 ) ? substr( $shipment_description, 0, 45 ).'...' : $shipment_description;
		
		return $shipment_description;
	}

	function wf_get_package_data( $order, $ship_options=array()) {
		$package				= $this->wf_create_package( $order );
		$shipping_service_data	= $this->wf_get_shipping_service_data( $order );
		
		if( !$shipping_service_data ) {
			return false;
		}
		
		if ( ! class_exists( 'WF_Shipping_UPS' ) ) {
	  		include_once 'class-wf-shipping-ups.php';
	  	}
		$wcsups 			= new WF_Shipping_UPS();
		$package_data_array	= array();		
		
		if(!$ship_options['return_label']){ // If return label is printing, cod can't be applied
			$wcsups->wf_set_cod_details($order);
		}
		
		$service_code=get_post_meta($order->id,'wf_ups_selected_service',1);
		if($service_code)
		{
			$wcsups->wf_set_service_code($service_code);
		}
		
		$package_data 		= $wcsups->wf_get_api_rate_box_data( $package, $this->packing_method );
		
		return $package_data;
	}
	
	function wf_get_package_data_manual( $order,$ship_options=array()) {
	    global $woocommerce;
		
		$ups_settings 		= get_option( 'woocommerce_'.WF_UPS_ID.'_settings', null ); 
		$ups_units			= isset( $ups_settings['units'] ) ? $ups_settings['units'] : 'imperial';

		if ( $ups_units == 'metric' ) {
			$weight_unit = 'KGS';
			$dim_unit    = 'CM';
		} else {
			$weight_unit = 'LBS';
			$dim_unit    = 'IN';
		}

	    $requests 	= array();    
		$weight		= isset( $_GET['weight'] )	? $_GET['weight'] 	: false;
		$height		= isset( $_GET['height'] )	? $_GET['height'] 	: false;
		$width		= isset( $_GET['width'] )	? $_GET['width'] 	: false;
		$length		= isset( $_GET['length'] )	? $_GET['length']	: false;
		
		$dimensions = array( $height, $width, $length );
		
		// Getting COD Data
		$cod=get_post_meta($order->id,'_wf_ups_cod',true);
		if(isset($ship_options['return_label'])&&$ship_options['return_label']){ // COD not applicable for return labels
			$cod=false;
		}
		$cod_value=$order->get_total();
		
		$request  = '<Package>'."\n";
		$request .= '	<PackagingType>' . "\n";
		$request .= '		<Code>02</Code>' . "\n";
		$request .= '		<Description>Package/customer supplied</Description>' . "\n";
		$request .= '	</PackagingType>' . "\n";
		$request .= '	<Description>Rate</Description>' . "\n";

		$request .= '	<Dimensions>' . "\n";
		$request .= '		<UnitOfMeasurement>' . "\n";
		$request .= '	 		<Code>' . $dim_unit. '</Code>' . "\n";
		$request .= '		</UnitOfMeasurement>' . "\n";
		$request .= '		<Length>' . $dimensions[2] . '</Length>' . "\n";
		$request .= '		<Width>' . $dimensions[1] . '</Width>' . "\n";
		$request .= '		<Height>' . $dimensions[0] . '</Height>' . "\n";
		$request .= '	</Dimensions>' . "\n";
		
		if((isset($params['service_code'])&&$params['service_code']==92)||($this->service_code==92)){// Surepost Less Than 1LBS
			if($this->weight_unit=='LBS'){ // make sure weight in pounds
				$weight_ozs=$weight*16;
			}else{
				$weight_ozs=$weight*35.274; // From KG
			}
		}else{
			$request .= '	<PackageWeight>' . "\n";
			$request .= '		<UnitOfMeasurement>' . "\n";
			$request .= '			<Code>' . $weight_unit . '</Code>' . "\n";
			$request .= '		</UnitOfMeasurement>' . "\n";
			$request .= '		<Weight>' . $weight . '</Weight>' . "\n";
			$request .= '	</PackageWeight>' . "\n";
		}
		
		if($cod){
			$request .= '	<PackageServiceOptions>' . "\n";
			if($cod){
				$request.='<COD>'."\n";
				$request.=	'<CODCode>3</CODCode>'."\n";
				$request.=	'<CODFundsCode>0</CODFundsCode>'."\n";
				$request.=	'<CODAmount>'."\n";
				$request.=		'<CurrencyCode>'.get_woocommerce_currency().'</CurrencyCode>'."\n";
				$request.=		'<MonetaryValue>'.$cod_value.'</MonetaryValue>'."\n";
				$request.=	'</CODAmount>'."\n";
				$request.='</COD>'."\n";
			}
			$request .= '	</PackageServiceOptions>' . "\n";
		}
		$request .= '</Package>' . "\n";

		$requests[] = $request;

		return $requests;
    }
	
	function wf_create_package( $order ){
		$orderItems = $order->get_items();
		
		foreach( $orderItems as $orderItem )
		{
			$item_id 			= $orderItem['variation_id'] ? $orderItem['variation_id'] : $orderItem['product_id'];
			$product_data 		= wc_get_product( $item_id );
			$items[$item_id] 	= array('data' => $product_data , 'quantity' => $orderItem['qty']);
		}
		
		$package['contents'] = $items;
		$package['destination'] = array (
        'country' 	=> $order->shipping_country,
        'state' 	=> $order->shipping_state,
        'postcode' 	=> $order->shipping_postcode,
        'city' 		=> $order->shipping_city,
        'address' 	=> $order->shipping_address_1,
        'address_2'	=> $order->shipping_address_2);
		
		return $package;
	}
	
	function wf_ups_shipment_confirm(){
		if( !$this->wf_user_check() ) {
			echo "You don't have admin privileges to view this page.";
			exit;
		}
		
		$wfupsmsg = '';
		// Load UPS Settings.
		$ups_settings 		= get_option( 'woocommerce_'.WF_UPS_ID.'_settings', null ); 
		// API Settings
		$api_mode      		= isset( $ups_settings['api_mode'] ) ? $ups_settings['api_mode'] : 'Test';
        $debug_mode      	= isset( $ups_settings['debug'] ) && $ups_settings['debug'] == 'yes' ? true : false;
		
		$query_string 		= explode('|', base64_decode($_GET['wf_ups_shipment_confirm']));
		$post_id 			= $query_string[1];
		$wf_ups_selected_service	= isset( $_GET['wf_ups_selected_service'] ) ? $_GET['wf_ups_selected_service'] : '';
		update_post_meta( $post_id, 'wf_ups_selected_service', $wf_ups_selected_service );
		
		$cod	= isset( $_GET['cod'] ) ? $_GET['cod'] : '';
		if($cod=='true'){
			update_post_meta( $post_id, '_wf_ups_cod', true );
		}else{
			delete_post_meta( $post_id, '_wf_ups_cod');
		}

		$sat_delivery	= isset( $_GET['sat_delivery'] ) ? $_GET['sat_delivery'] : '';
		if($sat_delivery=='true'){
			update_post_meta( $post_id, '_wf_ups_sat_delivery', true );
		}else{
			delete_post_meta( $post_id, '_wf_ups_sat_delivery');
		}

		$is_return_label	= isset( $_GET['is_return_label'] ) ? $_GET['is_return_label'] : '';
		if($is_return_label=='true'){
			$ups_return=true;
		}
		else{
			$ups_return=false;
		}
		$order				= $this->wf_load_order( $post_id );
        
		$request = $this->wf_ups_shipment_confirmrequest( $order );
		
        if( $debug_mode ) {
            echo 'SHIPMENT CONFIRM REQUEST: ';
            var_dump( $request ); 
        }
		
		if( !$request ) {
			// Due to some error and request not available, But the error is not catched
			$wfupsmsg = 2;
			update_post_meta( $post_id, 'wfupsmsg', 'Sorry. Something went wrong: please turn on debug mode to investigate more.');
			wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfupsmsg='.$wfupsmsg ) );
			exit;//return;
		}
		if( "Live" == $api_mode ) {
			$endpoint = 'https://www.ups.com/ups.app/xml/ShipConfirm';
		}
		else {
			$endpoint = 'https://wwwcie.ups.com/ups.app/xml/ShipConfirm';
		}

		$xml_request = str_replace( array( "\n", "\r" ), '', $request );
		
		$response = wp_remote_post( $endpoint,
			array(
				'timeout'   => 70,
				'sslverify' => 0,
				'body'      => $xml_request
			)
		);
        if( $debug_mode ) {
            echo 'SHIPMENT CONFIRM RESPONSE: ';
            var_dump( $response );   
        }
		
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			$wfupsmsg = 2;
			update_post_meta( $post_id, 'wfupsmsg', 'Sorry. Something went wrong: '.$error_message );
			wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfupsmsg='.$wfupsmsg ) );
			exit;
		}
		
		$response_obj = simplexml_load_string( $response['body'] );
		
		$response_code = (string)$response_obj->Response->ResponseStatusCode;
		if( '0' == $response_code ) {
			$error_code = (string)$response_obj->Response->Error->ErrorCode;
			$error_desc = (string)$response_obj->Response->Error->ErrorDescription;
			
			$wfupsmsg = 2;
			update_post_meta( $post_id, 'wfupsmsg', $error_desc.' [Error Code: '.$error_code.']' );
			wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfupsmsg='.$wfupsmsg ) );
			exit;
		}
		
		$created_shipments_details_array = array();
		$created_shipments_details = array();
		$shipment_id = (string)$response_obj->ShipmentIdentificationNumber;
		
		$created_shipments_details["ShipmentDigest"] 			= (string)$response_obj->ShipmentDigest;

		$created_shipments_details_array[$shipment_id] = $created_shipments_details;
		
		update_post_meta( $post_id, 'ups_created_shipments_details_array', $created_shipments_details_array );
		
		// Creating Return Label 		
		if($ups_return){
			$this->wf_ups_return_shipment_confirm($shipment_id);
		}
		
		$wfupsmsg = 1;
		wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfupsmsg='.$wfupsmsg ) );
		exit;
	}
	
	function wf_ups_return_shipment_confirm($parent_shipment_id){
		if( !$this->wf_user_check() ) {
			echo "You don't have admin privileges to view this page.";
			exit;
		}
		
		$wfupsmsg = '';
		// Load UPS Settings.
		$ups_settings 		= get_option( 'woocommerce_'.WF_UPS_ID.'_settings', null ); 
		// API Settings
		$api_mode      		= isset( $ups_settings['api_mode'] ) ? $ups_settings['api_mode'] : 'Test';
        $debug_mode      	= isset( $ups_settings['debug'] ) && $ups_settings['debug'] == 'yes' ? true : false;
		
		$query_string 		= explode('|', base64_decode($_GET['wf_ups_shipment_confirm']));
		$post_id 			= $query_string[1];
		$wf_ups_selected_service	= isset( $_GET['wf_ups_selected_service'] ) ? $_GET['wf_ups_selected_service'] : '';	
			
		$order				= $this->wf_load_order( $post_id );        
		$request = $this->wf_ups_shipment_confirmrequest( $order,true);//true for return label, false for general shipment, default is false	
                
        
        if( $debug_mode ) {
            echo 'SHIPMENT CONFIRM REQUEST: ';
            var_dump( $request ); 
        }
        
		if( !$request ) return;

		if( "Live" == $api_mode ) {
			$endpoint = 'https://www.ups.com/ups.app/xml/ShipConfirm';
		}
		else {
			$endpoint = 'https://wwwcie.ups.com/ups.app/xml/ShipConfirm';
		}

		$xml_request = str_replace( array( "\n", "\r" ), '', $request );
		
		$response = wp_remote_post( $endpoint,
			array(
				'timeout'   => 70,
				'sslverify' => 0,
				'body'      => $xml_request
			)
		);
        if( $debug_mode ) {
            echo 'SHIPMENT CONFIRM RESPONSE: ';
            var_dump( $response );   
        }
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			$error_message='Return Label - '.$error_message;
			$wfupsmsg = 2;
			update_post_meta( $post_id, 'wfupsmsg', 'Sorry. Something went wrong: '.$error_message );
			wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfupsmsg='.$wfupsmsg ) );
			exit;
		}
		
		$response_obj = simplexml_load_string( $response['body'] );
		
		$response_code = (string)$response_obj->Response->ResponseStatusCode;
		if( '0' == $response_code ) {
			$error_code = (string)$response_obj->Response->Error->ErrorCode;
			$error_desc = (string)$response_obj->Response->Error->ErrorDescription;
			$error_desc='Return Label - '.$error_desc;
			$wfupsmsg = 2;
			update_post_meta( $post_id, 'wfupsmsg', $error_desc.' [Error Code: '.$error_code.']' );
			wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfupsmsg='.$wfupsmsg ) );
			exit;
		}
		$created_shipments_details_array=get_post_meta($post_id, 'ups_created_shipments_details_array', 1);		
		$created_shipments_details = array();
		$shipment_id = (string)$response_obj->ShipmentIdentificationNumber;
		
		$created_shipments_details["ShipmentDigest"] 			= (string)$response_obj->ShipmentDigest;

		$created_shipments_details_array[$parent_shipment_id]['return'][$shipment_id] = $created_shipments_details;
		update_post_meta( $post_id, 'ups_created_shipments_details_array', $created_shipments_details_array );
		return true;
	}
	
	function wf_ups_shipment_accept(){
		if( !$this->wf_user_check() ) {
			echo "You don't have admin privileges to view this page.";
			exit;
		}

		$query_string		= explode('|', base64_decode($_GET['wf_ups_shipment_accept']));
		$shipmentId 		= $query_string[0];
		$post_id 			= $query_string[1];
		$wfupsmsg 			= '';
		
		$created_shipments_details_array	= get_post_meta($post_id, 'ups_created_shipments_details_array', true);
		$created_shipments_details 			= $created_shipments_details_array[$shipmentId];
		
		// Load UPS Settings.
		$ups_settings 				= get_option( 'woocommerce_'.WF_UPS_ID.'_settings', null ); 
		// API Settings
		$api_mode      				= isset( $ups_settings['api_mode'] ) ? $ups_settings['api_mode'] : 'Test';
		$ups_user_id         		= isset( $ups_settings['user_id'] ) ? $ups_settings['user_id'] : '';
		$ups_password        		= isset( $ups_settings['password'] ) ? $ups_settings['password'] : '';
		$ups_access_key      		= isset( $ups_settings['access_key'] ) ? $ups_settings['access_key'] : '';
		$ups_shipper_number  		= isset( $ups_settings['shipper_number'] ) ? $ups_settings['shipper_number'] : '';
		$disble_shipment_tracking	= isset( $ups_settings['disble_shipment_tracking'] ) ? $ups_settings['disble_shipment_tracking'] : 'TrueForCustomer';
        $debug_mode      	        = isset( $ups_settings['debug'] ) && $ups_settings['debug'] == 'yes' ? true : false;
		
		if( "Live" == $api_mode ) {
			$endpoint = 'https://www.ups.com/ups.app/xml/ShipAccept';
		}
		else {
			$endpoint = 'https://wwwcie.ups.com/ups.app/xml/ShipAccept';
		}
		
		$xml_request = '<?xml version="1.0" ?>';
		$xml_request .= '<AccessRequest xml:lang="en-US">';
		$xml_request .= '<AccessLicenseNumber>'.$ups_access_key.'</AccessLicenseNumber>';
		$xml_request .= '<UserId>'.$ups_user_id.'</UserId>';
		$xml_request .= '<Password>'.$ups_password.'</Password>';
		$xml_request .= '</AccessRequest>'; 
		$xml_request .= '<?xml version="1.0" ?>';
		$xml_request .= '<ShipmentAcceptRequest>';
		$xml_request .= '<Request>';
		$xml_request .= '<TransactionReference>';
		$xml_request .= '<CustomerContext>'.$post_id.'</CustomerContext>';
		$xml_request .= '<XpciVersion>1.0001</XpciVersion>';
		$xml_request .= '</TransactionReference>';
		$xml_request .= '<RequestAction>ShipAccept</RequestAction>';
		$xml_request .= '</Request>';
		$xml_request .= '<ShipmentDigest>'.$created_shipments_details["ShipmentDigest"].'</ShipmentDigest>';
		$xml_request .= '</ShipmentAcceptRequest>';
		
		
        if( $debug_mode ) {
            echo 'SHIPMENT ACCEPT REQUEST: ';
            var_dump( $xml_request );   
        }
        
		$response = wp_remote_post( $endpoint,
			array(
				'timeout'   => 70,
				'sslverify' => 0,
				'body'      => $xml_request
			)
		);
		
        if( $debug_mode ) {
            echo 'SHIPMENT ACCEPT RESPONSE: ';
            var_dump( $response );   
        }
        
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			$wfupsmsg = 2;
			update_post_meta( $post_id, 'wfupsmsg', 'Sorry. Something went wrong: '.$error_message );
			wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfupsmsg='.$wfupsmsg ) );
			exit;
		}

		$response_obj = simplexml_load_string( '<root>' . preg_replace('/<\?xml.*\?>/','', $response['body'] ) . '</root>' );	

		$response_code = (string)$response_obj->ShipmentAcceptResponse->Response->ResponseStatusCode;
		if('0' == $response_code) {
			$error_code = (string)$response_obj->ShipmentAcceptResponse->Response->Error->ErrorCode;
			$error_desc = (string)$response_obj->ShipmentAcceptResponse->Response->Error->ErrorDescription;
			
			$wfupsmsg = 2;
			update_post_meta( $post_id, 'wfupsmsg', $error_desc.' [Error Code: '.$error_code.']' );
			wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfupsmsg='.$wfupsmsg ) );
			exit;
		}
		
		$package_results 			= $response_obj->ShipmentAcceptResponse->ShipmentResults->PackageResults;
		$ups_label_details			= array();
		$shipment_id 				= $shipmentId;
		$ups_label_details_array	= array();
		$shipment_id_cs 			= '';

		// Labels for each package.
		foreach ( $package_results as $package_result ) {
			$ups_label_details["TrackingNumber"]		= (string)$package_result->TrackingNumber;
			$ups_label_details["Code"] 					= (string)$package_result->LabelImage->LabelImageFormat->Code;
			$ups_label_details["GraphicImage"] 			= (string)$package_result->LabelImage->GraphicImage;
			$ups_label_details_array[$shipment_id][]	= $ups_label_details;
			$shipment_id_cs 							.= $ups_label_details["TrackingNumber"].',';
		}
		
		$shipment_id_cs = rtrim( $shipment_id_cs, ',' );

		if( empty($ups_label_details_array) ) {
			$wfupsmsg = 0;
			wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfupsmsg='.$wfupsmsg ) );
			exit;
		}
		else {
			update_post_meta( $post_id, 'ups_label_details_array', $ups_label_details_array );
			
			if( isset($created_shipments_details['return']) && $created_shipments_details['return'] ){// creating return label
				$return_label_ids=$this->wf_ups_return_shipment_accept($post_id,$created_shipments_details['return']);
				if($return_label_ids&&$shipment_id_cs){
					$shipment_id_cs=$shipment_id_cs.','.$return_label_ids;
				}
			}
		}
		
		if( 'True' != $disble_shipment_tracking) {
			wp_redirect( admin_url( '/post.php?post='.$post_id.'&wf_ups_track_shipment='.$shipment_id_cs ) );
			exit;
		}
		
		$wfupsmsg = 3;
		wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfupsmsg='.$wfupsmsg ) );
		exit;
	}
	
	function wf_ups_return_shipment_accept($post_id,$shipment_data){
		if( !$this->wf_user_check() ) {
			echo "You don't have admin privileges to view this page.";
			exit;
		}
		
		// Load UPS Settings.
		$ups_settings 				= get_option( 'woocommerce_'.WF_UPS_ID.'_settings', null ); 
		// API Settings
		$api_mode      				= isset( $ups_settings['api_mode'] ) ? $ups_settings['api_mode'] : 'Test';
		$ups_user_id         		= isset( $ups_settings['user_id'] ) ? $ups_settings['user_id'] : '';
		$ups_password        		= isset( $ups_settings['password'] ) ? $ups_settings['password'] : '';
		$ups_access_key      		= isset( $ups_settings['access_key'] ) ? $ups_settings['access_key'] : '';
		$ups_shipper_number  		= isset( $ups_settings['shipper_number'] ) ? $ups_settings['shipper_number'] : '';
		$disble_shipment_tracking	= isset( $ups_settings['disble_shipment_tracking'] ) ? $ups_settings['disble_shipment_tracking'] : 'TrueForCustomer';
        $debug_mode      	        = isset( $ups_settings['debug'] ) && $ups_settings['debug'] == 'yes' ? true : false;
		
		if( "Live" == $api_mode ) {
			$endpoint = 'https://www.ups.com/ups.app/xml/ShipAccept';
		}
		else {
			$endpoint = 'https://wwwcie.ups.com/ups.app/xml/ShipAccept';
		}
		
		foreach($shipment_data as $shipment_id=>$created_shipments_details){	
			$created_shipments_details=current($shipment_data);// only one shipment is allowed
			$xml_request = '<?xml version="1.0" ?>';
			$xml_request .= '<AccessRequest xml:lang="en-US">';
			$xml_request .= '<AccessLicenseNumber>'.$ups_access_key.'</AccessLicenseNumber>';
			$xml_request .= '<UserId>'.$ups_user_id.'</UserId>';
			$xml_request .= '<Password>'.$ups_password.'</Password>';
			$xml_request .= '</AccessRequest>'; 
			$xml_request .= '<?xml version="1.0" ?>';
			$xml_request .= '<ShipmentAcceptRequest>';
			$xml_request .= '<Request>';
			$xml_request .= '<TransactionReference>';
			$xml_request .= '<CustomerContext>'.$post_id.'</CustomerContext>';
			$xml_request .= '<XpciVersion>1.0001</XpciVersion>';
			$xml_request .= '</TransactionReference>';
			$xml_request .= '<RequestAction>ShipAccept</RequestAction>';
			$xml_request .= '</Request>';
			$xml_request .= '<ShipmentDigest>'.$created_shipments_details["ShipmentDigest"].'</ShipmentDigest>';
			$xml_request .= '</ShipmentAcceptRequest>';
			
			if( $debug_mode ) {
				echo 'RETURN SHIPMENT ACCEPT REQUEST: ';
				var_dump( $xml_request );   
			}
			
			$response = wp_remote_post( $endpoint,
				array(
					'timeout'   => 70,
					'sslverify' => 0,
					'body'      => $xml_request
				)
			);
			
			if( $debug_mode ) {
				echo 'RETURN SHIPMENT ACCEPT RESPONSE: ';
				var_dump( $response );   
			}	
			$response_obj = simplexml_load_string( '<root>' . preg_replace('/<\?xml.*\?>/','', $response['body'] ) . '</root>' );	
			$response_code = (string)$response_obj->ShipmentAcceptResponse->Response->ResponseStatusCode;
			if('0' == $response_code) {
				$error_code = (string)$response_obj->ShipmentAcceptResponse->Response->Error->ErrorCode;
				$error_desc = (string)$response_obj->ShipmentAcceptResponse->Response->Error->ErrorDescription;
				
				$wfupsmsg = 2;
				update_post_meta( $post_id, 'wfupsmsg', $error_desc.' [Error Code: '.$error_code.']' );
				return false;
			}
			$package_results 			= $response_obj->ShipmentAcceptResponse->ShipmentResults->PackageResults;		
			
			$shipment_id_cs = '';
			// Labels for each package.
			foreach ( $package_results as $package_result ) {
				$ups_label_details["TrackingNumber"]		= (string)$package_result->TrackingNumber;
				$ups_label_details["Code"] 					= (string)$package_result->LabelImage->LabelImageFormat->Code;
				$ups_label_details["GraphicImage"] 			= (string)$package_result->LabelImage->GraphicImage;
				$ups_label_details_array[$shipment_id][]	= $ups_label_details;
				$shipment_id_cs 							.= $ups_label_details["TrackingNumber"].',';
			}
			$shipment_id_cs = rtrim( $shipment_id_cs, ',' );			
			if( empty($ups_label_details_array) ) {
				$wfupsmsg = 0;
				return false;
			}
			else {
				update_post_meta( $post_id, 'ups_return_label_details_array', $ups_label_details_array );
				return $shipment_id_cs;
			}
			break; // Only one return shipment is allowed
			return false;
		}
	}

	function wf_ups_print_label(){
		if( !$this->wf_user_check() ) {
			echo "You don't have admin privileges to view this page.";
			exit;
		}
		
		$ups_settings 				= get_option( 'woocommerce_'.WF_UPS_ID.'_settings', null ); 
		$print_label_type	= isset( $ups_settings['print_label_type'] ) ? $ups_settings['print_label_type'] : 'gif';

		$query_string		= explode('|', base64_decode($_GET['wf_ups_print_label']));
		$shipmentId 		= $query_string[0];
		$post_id 			= $query_string[1];
		$label_extn_code 	= $query_string[2];
		$index			 	= $query_string[3];
        $tracking_number    = $query_string[4];
		
		$label_meta_name='ups_label_details_array';
		if(isset($query_string[4])){
			$return			= $query_string[4];
			if($return=='return'){
				$label_meta_name='ups_return_label_details_array';
			}
		}
		$wfupsmsg 			= '';
		
		$ups_label_details_array = get_post_meta( $post_id, $label_meta_name, true );
        
        $ups_settings 				  = get_option( 'woocommerce_'.WF_UPS_ID.'_settings', null ); 
        $show_label_in_browser        = isset( $ups_settings['show_label_in_browser'] ) ? $ups_settings['show_label_in_browser'] : 'no';

		if( empty($ups_label_details_array) ) {
			$wfupsmsg = 0;
			wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfupsmsg='.$wfupsmsg ) );
			exit;
		}

		$graphic_image = $ups_label_details_array[$shipmentId][$index]["GraphicImage"];
		
        if("GIF" == $label_extn_code) {
            if( "yes" == $show_label_in_browser ) {
                echo '<img src="data:image/gif;base64,' . $graphic_image. '" />';
                exit;
            }
            
            //$binary_label = base64_decode($graphic_image);
            $binary_label = base64_decode(chunk_split($graphic_image));
            
			$final_image 	= $binary_label;
			$extn_code		= 'gif';
		}
        // ZPL which will be converted to PNG.
		elseif("ZPL" == $label_extn_code && $print_label_type == 'zpl') {
            $binary_label = base64_decode(chunk_split($graphic_image));
            
            // By default zpl code returned by UPS has ^POI command, which will invert the label because
            // of some reason. Removing it so that label will not be inverted.
            $zpl_label_inverted = str_replace( "^POI", "", $binary_label);
			
			$file_name = 'UPS-ShippingLabel-Label-'.$post_id.'-'.$tracking_number.'.zpl.txt';
			$this->wf_generate_document_file($zpl_label_inverted, $file_name);
			exit;
		}
		elseif("EPL" == $label_extn_code && $print_label_type == 'epl') {
            $binary_label = base64_decode(chunk_split($graphic_image));
            
			$file_name = 'UPS-ShippingLabel-Label-'.$post_id.'-'.$tracking_number.'.epl';
			$this->wf_generate_document_file($binary_label, $file_name);
			exit;
		}

        else {
            //$zpl_label = base64_decode($graphic_image);
            $zpl_label = base64_decode(chunk_split($graphic_image));
            // By default zpl code returned by UPS has ^POI command, which will invert the label because
            // of some reason. Removing it so that label will not be inverted.
            $zpl_label_inverted = str_replace( "^POI", "", $zpl_label);

			$response 		= wp_remote_post( "http://api.labelary.com/v1/printers/8dpmm/labels/4x6/0/",
				array(
					'timeout'   => 70,
					'sslverify' => 0,
					'body'      => $zpl_label_inverted
				)
			);
            
            //var_dump( $response ); die();
            
			$final_image 	= $response["body"];
			$extn_code		= 'png';
            
            if( "yes" == $show_label_in_browser ) {
                $final_image_base64_encoded = base64_encode( $final_image );
                echo '<img src="data:image/png;base64,' . $final_image_base64_encoded. '" />';
                exit;
            }
        
		}

        header('Content-Description: File Transfer');
        header('Content-Type: image/'.$extn_code.'');
        header('Content-disposition: attachment; filename="UPS-ShippingLabel-' . 'Label-'.$post_id.'-'.$tracking_number.'.'.$extn_code.'"');
		echo $final_image;
		exit;
	}

	private function wf_generate_document_file($content, $file_name){
		$handle = fopen($file_name, "w");
		fwrite($handle, $content);
		fclose($handle);

		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.basename($file_name));
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file_name));
		readfile($file_name);
		return;
	}

	function wf_ups_void_shipment(){
		if( !$this->wf_user_check() ) {
			echo "You don't have admin privileges to view this page.";
			exit;
		}
	
		$query_string		= explode( '|', base64_decode( $_GET['wf_ups_void_shipment'] ) );
		$shipmentId 		= $query_string[0];
		$post_id 			= $query_string[1];
		$wfupsmsg 			= '';
		
		// Load UPS Settings.
		$ups_settings 				= get_option( 'woocommerce_'.WF_UPS_ID.'_settings', null ); 
		// API Settings
		$api_mode		      		= isset( $ups_settings['api_mode'] ) ? $ups_settings['api_mode'] : 'Test';
		$ups_user_id         		= isset( $ups_settings['user_id'] ) ? $ups_settings['user_id'] : '';
		$ups_password        		= isset( $ups_settings['password'] ) ? $ups_settings['password'] : '';
		$ups_access_key      		= isset( $ups_settings['access_key'] ) ? $ups_settings['access_key'] : '';
		$ups_shipper_number  		= isset( $ups_settings['shipper_number'] ) ? $ups_settings['shipper_number'] : '';
		
		$ups_label_details_array 	= get_post_meta( $post_id, 'ups_label_details_array', true );
		
		$client_side_reset = false;
		if( isset( $_GET['client_reset'] ) ) {
			$client_side_reset = true;
		}
		
		if( "Live" == $api_mode ) {
			$endpoint = 'https://www.ups.com/ups.app/xml/Void';
		}
		else {
			$endpoint = 'https://wwwcie.ups.com/ups.app/xml/Void';
		}	
		
		if( !empty( $ups_label_details_array ) && $shipmentId && !$client_side_reset ) {
			$xml_request = '<?xml version="1.0" ?>';
			$xml_request .= '<AccessRequest xml:lang="en-US">';
			$xml_request .= '<AccessLicenseNumber>'.$ups_access_key.'</AccessLicenseNumber>';
			$xml_request .= '<UserId>'.$ups_user_id.'</UserId>';
			$xml_request .= '<Password>'.$ups_password.'</Password>';
			$xml_request .= '</AccessRequest>';
			$xml_request .= '<?xml version="1.0" encoding="UTF-8" ?>';
			$xml_request .= '<VoidShipmentRequest>';
			$xml_request .= '<Request>';
			$xml_request .= '<TransactionReference>';
			$xml_request .= '<CustomerContext>'.$post_id.'</CustomerContext>';
			$xml_request .= '<XpciVersion>1.0001</XpciVersion>';
			$xml_request .= '</TransactionReference>';
			$xml_request .= '<RequestAction>Void</RequestAction>';
			$xml_request .= '<RequestOption />';
			$xml_request .= '</Request>';
			$xml_request .= '<ExpandedVoidShipment>';
			$xml_request .= '<ShipmentIdentificationNumber>'.$shipmentId.'</ShipmentIdentificationNumber>';
			foreach ( $ups_label_details_array[$shipmentId] as $ups_label_details ) {
				$xml_request .= '<TrackingNumber>'.$ups_label_details["TrackingNumber"].'</TrackingNumber>';
			}
			$xml_request .= '</ExpandedVoidShipment>';
			$xml_request .= '</VoidShipmentRequest>';
			
			$response = wp_remote_post( $endpoint,
				array(
					'timeout'   => 70,
					'sslverify' => 0,
					'body'      => $xml_request
				)
			);
			
			// In case of any issues with remote post.
			if ( is_wp_error( $response ) ) {
				$wfupsmsg = 2;
				update_post_meta( $post_id, 'wfupsmsg', 'Sorry. Something went wrong: '.$error_message );
				wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfupsmsg='.$wfupsmsg ) );
				exit;
			}
			
			$response_obj 	= simplexml_load_string( '<root>' . preg_replace('/<\?xml.*\?>/','', $response['body'] ) . '</root>' );
			$response_code 	= (string)$response_obj->VoidShipmentResponse->Response->ResponseStatusCode;

			// It is an error response.
			if( '0' == $response_code ) {
				$error_code = (string)$response_obj->VoidShipmentResponse->Response->Error->ErrorCode;
				$error_desc = (string)$response_obj->VoidShipmentResponse->Response->Error->ErrorDescription;
				
				$message = '<strong>'.$error_desc.' [Error Code: '.$error_code.']'.'. </strong>';

				$current_page_uri	= $_SERVER['REQUEST_URI'];
				$href_url 			= $current_page_uri.'&client_reset';
				
				$message .= 'Please contact UPS to void/cancel this shipment. <br/>';
				$message .= 'If you have already cancelled this shipment by calling UPS customer care, and you would like to create shipment again then click <a class="button button-primary tips" href="'.$href_url.'" data-tip="Client Side Reset">Client Side Reset</a>';
				$message .= '<p style="color:red"><strong>Note: </strong>Previous shipment details and label will be removed from Order page.</p>';
				
				if( "Test" == $api_mode ) {
					$message .= "<strong>Also, noticed that you have enabled 'Test' mode.<br/>Please note that void is not possible in 'Test' mode, as there is no real shipment is created with UPS. </strong><br/>";
				}
				
				$wfupsmsg = 2;
				update_post_meta( $post_id, 'wfupsmsg', $message );
				wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfupsmsg='.$wfupsmsg ) );
				exit;
			}
		}
		$this->wf_ups_void_return_shipment($post_id,$shipmentId);		
		$empty_array = array();
		
		update_post_meta( $post_id, 'ups_created_shipments_details_array', $empty_array );
		update_post_meta( $post_id, 'ups_label_details_array', $empty_array );
		update_post_meta( $post_id, 'wf_ups_selected_service', '' );
		
		// Reset of stored meta elements done. Back to admin order page. 
		if( $client_side_reset ){
			$wfupsmsg = 5;
			wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfupsmsg='.$wfupsmsg ) );
			exit;
		}
		
		$wfupsmsg = 4;
		wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&wfupsmsg='.$wfupsmsg ) );
		exit;
	}
	
	function wf_ups_void_return_shipment($post_id,$shipmentId){
		$ups_created_shipments_details_array=get_post_meta($post_id,'ups_created_shipments_details_array',1);
		if(is_array($ups_created_shipments_details_array)&&isset($ups_created_shipments_details_array[$shipmentId]['return'])){
			$return_shipment_id=current(array_keys($ups_created_shipments_details_array[$shipmentId]['return']));
			if($return_shipment_id){
				// Load UPS Settings.
				$ups_settings 				= get_option( 'woocommerce_'.WF_UPS_ID.'_settings', null ); 
				// API Settings
				$api_mode		      		= isset( $ups_settings['api_mode'] ) ? $ups_settings['api_mode'] : 'Test';
				$ups_user_id         		= isset( $ups_settings['user_id'] ) ? $ups_settings['user_id'] : '';
				$ups_password        		= isset( $ups_settings['password'] ) ? $ups_settings['password'] : '';
				$ups_access_key      		= isset( $ups_settings['access_key'] ) ? $ups_settings['access_key'] : '';
				$ups_shipper_number  		= isset( $ups_settings['shipper_number'] ) ? $ups_settings['shipper_number'] : '';
				
				$ups_return_label_details_array 	= get_post_meta( $post_id, 'ups_return_label_details_array', true );
				
				$client_side_reset = false;
				if( isset( $_GET['client_reset'] ) ) {
					$client_side_reset = true;
				}
				
				if( "Live" == $api_mode ) {
					$endpoint = 'https://www.ups.com/ups.app/xml/Void';
				}
				else {
					$endpoint = 'https://wwwcie.ups.com/ups.app/xml/Void';
				}
				
				if( !empty( $ups_return_label_details_array ) && $return_shipment_id) {
					$xml_request = '<?xml version="1.0" ?>';
					$xml_request .= '<AccessRequest xml:lang="en-US">';
					$xml_request .= '<AccessLicenseNumber>'.$ups_access_key.'</AccessLicenseNumber>';
					$xml_request .= '<UserId>'.$ups_user_id.'</UserId>';
					$xml_request .= '<Password>'.$ups_password.'</Password>';
					$xml_request .= '</AccessRequest>';
					$xml_request .= '<?xml version="1.0" encoding="UTF-8" ?>';
					$xml_request .= '<VoidShipmentRequest>';
					$xml_request .= '<Request>';
					$xml_request .= '<TransactionReference>';
					$xml_request .= '<CustomerContext>'.$post_id.'</CustomerContext>';
					$xml_request .= '<XpciVersion>1.0001</XpciVersion>';
					$xml_request .= '</TransactionReference>';
					$xml_request .= '<RequestAction>Void</RequestAction>';
					$xml_request .= '<RequestOption />';
					$xml_request .= '</Request>';
					$xml_request .= '<ExpandedVoidShipment>';
					$xml_request .= '<ShipmentIdentificationNumber>'.$return_shipment_id.'</ShipmentIdentificationNumber>';
					foreach ( $ups_return_label_details_array[$return_shipment_id] as $ups_return_label_details ) {
						$xml_request .= '<TrackingNumber>'.$ups_return_label_details["TrackingNumber"].'</TrackingNumber>';
					}
					$xml_request .= '</ExpandedVoidShipment>';
					$xml_request .= '</VoidShipmentRequest>';
					$response = wp_remote_post( $endpoint,
						array(
							'timeout'   => 70,
							'sslverify' => 0,
							'body'      => $xml_request
						)
					);
					
					// In case of any issues with remote post.
					if ( is_wp_error( $response ) ) {
						$wfupsmsg = 2;
						update_post_meta( $post_id, 'wfupsmsg', 'Sorry. Something went wrong: '.$error_message );
						return;
					}
					
					$response_obj 	= simplexml_load_string( '<root>' . preg_replace('/<\?xml.*\?>/','', $response['body'] ) . '</root>' );
					$response_code 	= (string)$response_obj->VoidShipmentResponse->Response->ResponseStatusCode;

					// It is an error response.
					if( '0' == $response_code ) {
						$error_code = (string)$response_obj->VoidShipmentResponse->Response->Error->ErrorCode;
						$error_desc = (string)$response_obj->VoidShipmentResponse->Response->Error->ErrorDescription;
						
						$message = '<strong>'.$error_desc.' [Error Code: '.$error_code.']'.'. </strong>';

						$current_page_uri	= $_SERVER['REQUEST_URI'];
						$href_url 			= $current_page_uri.'&client_reset';
						
						$message .= 'Please contact UPS to void/cancel this shipment. <br/>';
						$message .= 'If you have already cancelled this shipment by calling UPS customer care, and you would like to create shipment again then click <a class="button button-primary tips" href="'.$href_url.'" data-tip="Client Side Reset">Client Side Reset</a>';
						$message .= '<p style="color:red"><strong>Note: </strong>Previous shipment details and label will be removed from Order page.</p>';
						
						if( "Test" == $api_mode ) {
							$message .= "<strong>Also, noticed that you have enabled 'Test' mode.<br/>Please note that void is not possible in 'Test' mode, as there is no real shipment is created with UPS. </strong><br/>";
						}
						
						$wfupsmsg = 2;
						update_post_meta( $post_id, 'wfupsmsg', $message );
						return;
					}
				}
				$empty_array = array();
				update_post_meta( $post_id, 'ups_return_label_details_array', $empty_array );
			}
		}
		
	}

	function wf_load_order( $orderId ){
		if ( !class_exists( 'WC_Order' ) ) {
			return false;
		}
		return new WC_Order( $orderId );      
	}
	
	function wf_user_check() {
		if ( is_admin() ) {
			return true;
		}
		return false;
	}
	
	function wf_get_shipping_service_data($order){
		//TODO: Take the first shipping method. The use case of multiple shipping method for single order is not handled.
		
		$shipping_methods = $order->get_shipping_methods();
		if ( ! $shipping_methods ) {
			return false;
		}

		$shipping_method			= array_shift( $shipping_methods );
		$shipping_service_tmp_data	= explode( ':',$shipping_method['method_id'] );
		$wf_ups_selected_service	= '';

		$wf_ups_selected_service 	= get_post_meta( $order->id, 'wf_ups_selected_service', true );

		if( '' != $wf_ups_selected_service ) {
			$shipping_service_data['shipping_method'] 		= WF_UPS_ID;
			$shipping_service_data['shipping_service'] 		= $wf_ups_selected_service;
			$shipping_service_data['shipping_service_name']	= isset( $ups_services[$wf_ups_selected_service] ) ? $ups_services[$wf_ups_selected_service] : '';
		}
		else if( !isset( $shipping_service_tmp_data[0] ) || 
			( isset( $shipping_service_tmp_data[0] ) && $shipping_service_tmp_data[0] != WF_UPS_ID ) ){
			$shipping_service_data['shipping_method'] 		= WF_UPS_ID;
			$shipping_service_data['shipping_service'] 		= '';
			$shipping_service_data['shipping_service_name']	= '';
		}
		else {
			$shipping_service_data['shipping_method'] 		= $shipping_service_tmp_data[0];
			$shipping_service_data['shipping_service'] 		= $shipping_service_tmp_data[1];
			$shipping_service_data['shipping_service_name']	= $shipping_method['name'];	
		}
		
		return $shipping_service_data;
	}
}
new WF_Shipping_UPS_Admin();
