<?php
Class wf_freight_ups{
	Private $parent;
	
	function __construct($parent)
	{
		$this->parent=$parent;
	}
	
	function get_rate_request($package,$code)
	{
	$json_req=array();

	$json_req['UPSSecurity']=array(
                               "UsernameToken"=>array(
                                                            "Username"=>$this->parent->user_id,
                                                            "Password"=>str_replace( '&', '&amp;', $this->parent->password )
                                                        ),
                               "ServiceAccessToken"=>array(
                                                            "AccessLicenseNumber"=>$this->parent->access_key
                                                        ),
                               
                               );
    $address = '';
    if( !empty($package['destination']['address_1']) ){
        $address = $package['destination']['address_1'];
    }elseif( !empty($package['destination']['address']) ){
        $address = $package['destination']['address'];
    }
    $destination_city = strtoupper( $package['destination']['city'] );
    $destination_country = "";
    if ( ( "PR" == $package['destination']['state'] ) && ( "US" == $package['destination']['country'] ) ) {		
            $destination_country = "PR";
    } else {
            $destination_country = $package['destination']['country'];
    }
                               
	$json_req['FreightRateRequest']=array(
                               "Request"=>array(
                                                            "RequestOption"=>"1",
                                                            "TransactionReference"=>array("TransactionIdentifier"=>"Freight Rate Request")
                                                        ),
                               "ShipFrom"=>array(
                                                            "Name"=>$this->parent->ups_display_name,
                                                            "Address"=>array(
                                                                                "AddressLine"=>$this->parent->origin_addressline,
                                                                                "City"=>$this->parent->origin_city,
                                                                                "StateProvinceCode"=>$this->parent->origin_state,
                                                                                "PostalCode"=>$this->parent->origin_postcode,
                                                                                "CountryCode"=>$this->parent->origin_country,
                                                                             ),                                                            
                                                           // "EMailAddress"=>"",                                                          
                                                      
                                                        ),
                              "ShipperNumber"=>$this->parent->shipper_number,
                              "ShipTo"=>array(
                                                            "Name"=>"Receipient",
                                                            "Address"=>array(
                                                                                "AddressLine"=>$address,
                                                                                "City"=>$destination_city,
                                                                                "StateProvinceCode"=>$package['destination']['state'],
                                                                                "PostalCode"=>$package['destination']['postcode'],
                                                                                "CountryCode"=>$destination_country,
                                                                             ),         
                                              ),
                              "PaymentInformation"=>array(
                                                          "Payer"=>array(
                                                                         "Name"=>!empty($this->parent->ups_display_name)?$this->parent->ups_display_name:"Shipper",
                                                                         "Address"=>array(
                                                                                "AddressLine"=>$this->parent->origin_addressline,
                                                                                "City"=>$this->parent->origin_city,
                                                                                "StateProvinceCode"=>$this->parent->origin_state,
                                                                                "PostalCode"=>$this->parent->origin_postcode,
                                                                                "CountryCode"=>$this->parent->origin_country,
                                                                             ),
                                                                         "ShipperNumber"=>$this->parent->shipper_number,
                                                                         //"AccountType"=>"",
                                                                         //"EMailAddress"=>"",
                                                                         
                                                                         ),
                                                          "ShipmentBillingOption"=>array(
                                                                                         "Code"=>(string)$this->parent->freight_billing_option_code
                                                                                         )
                                                          
                                                          ),
                              "Service"=>array(
                                                         "Code"=>"$code"
                                               ),
                              "ShipmentRatingOptions"=>array("NegotiatedRatesIndicator"=>"0"),		// currently this is not working with freight
                              "HandlingUnitOne"=>array(
                                                         "Quantity"=>"1",
                                                         "Type"=>array(
                                                                             "Code"=>(string)$this->parent->freight_handling_unit_one_type_code
                                                                             )
                                                       ),
                                "Commodity"=>$this->freight_per_item_shipping($package),
                               );
	return json_encode($json_req);		
	//$rate_requests[$code]=json_encode($json_req);		
	}
	
	 /**
     * freight_per_item_shipping function.
     *
     * @access private
     * @param mixed $package
     * @return mixed $requests - an array of XML strings
     */
    function freight_per_item_shipping( $package) {
	    global $woocommerce;

	    $requests = array();

		$ctr=0;
		$this->parent->cod=sizeof($package['contents'])>1?false:$this->parent->cod; // For multiple packages COD is turned off
    	foreach ( $package['contents'] as $item_id => $values ) {
    		$values['data'] = $this->parent->wf_load_product( $values['data'] );
    		$ctr++;
			
			$skip_product = apply_filters('wf_shipping_skip_product',false, $values, $package['contents']);
			if($skip_product){
				continue;
			}

    		if ( !( $values['quantity'] > 0 && $values['data']->needs_shipping() ) ) {
    			$this->parent->debug( sprintf( __( 'Product #%d is virtual. Skipping.', 'ups-woocommerce-shipping' ), $ctr ) );
    			continue;
    		}

    		if ( ! $values['data']->get_weight() ) {
	    		$this->parent->debug( sprintf( __( 'Product #%d is missing weight. Aborting.', 'ups-woocommerce-shipping' ), $ctr ), 'error' );
	    		return;
    		}

			// get package weight
    		$weight = wc_get_weight( $values['data']->get_weight(), $this->parent->weight_unit );
            //$weight = apply_filters('wf_ups_filter_product_weight', $weight, $package, $item_id );

			// get package dimensions
    		if ( $values['data']->length && $values['data']->height && $values['data']->width ) {

				$dimensions = array( number_format( wc_get_dimension( $values['data']->length, $this->parent->dim_unit ), 2, '.', ''),
									 number_format( wc_get_dimension( $values['data']->height, $this->parent->dim_unit ), 2, '.', ''),
									 number_format( wc_get_dimension( $values['data']->width, $this->parent->dim_unit ), 2, '.', '') );
				sort( $dimensions );

			}

			// get quantity in cart
			$cart_item_qty = $values['quantity'];
			// get weight, or 1 if less than 1 lbs.
			// $_weight = ( floor( $weight ) < 1 ) ? 1 : $weight;
			
			$commodity	=	array(
									'Description'	=>get_the_title( $values['data'] ->id ),
									'Weight'	=>array(
														'Value'=>"$weight",
														'UnitOfMeasurement'=>array('Code'=>(string)$this->parent->weight_unit)
														),				
									);
			
			if ( $values['data']->length && $values['data']->height && $values['data']->width ) {
				
				$commodity['Dimensions']	=	array(
														'UnitOfMeasurement'	=>	array('Code'	=>	(string)$this->parent->dim_unit  ),
														'Length'	=>	$dimensions[2],
														'Width'		=>	$dimensions[1],
														'Height'	=>	$dimensions[0]
													);
			}
			$commodity['NumberOfPieces']	= "1";
			
			$commodity['PackagingType']	=array('Code'=>(string)$this->parent->freight_package_type_code);
			$commodity['FreightClass']	=(string)$this->parent->freight_class;
			
			for ( $i=0; $i < $cart_item_qty ; $i++)
				$commodities[] = $commodity;
    	}
		return $commodities;
    }
	
	
	function create_shipment_request($vars,$package_data)
	{
		extract($vars);
	$json_req=array();

	$json_req['UPSSecurity']=array(
                               "UsernameToken"=>array(
                                                            "Username"=>$ups_user_id,
                                                            "Password"=>str_replace( '&', '&amp;', $ups_password )
                                                        ),
                               "ServiceAccessToken"=>array(
                                                            "AccessLicenseNumber"=>$ups_access_key
                                                        ),
                               
                               );
                        
	$json_req['FreightShipRequest']=array(
                               "Request"=>array(
                                                            "RequestOption"=>"1",
                                                            "TransactionReference"=>array("TransactionIdentifier"=>"Freight Rate Request")
                                                        ),
							   "Shipment"=>array(
											"ShipFrom"=>array(
																		 "Name"=>htmlspecialchars( $ups_user_name ),
																		 'Address'		=>	array(
																			 'AddressLine'		=>	htmlspecialchars( $ups_origin_addressline ),
																			 'City'				=>	$ups_origin_city,
																			 'StateProvinceCode'	=>	$origin_state,
																			 'PostalCode'		=>	$ups_origin_postcode,
																			 'CountryCode'		=>	$origin_country,
																		 ),
																		 'AttentionName'	=>	htmlspecialchars( $ups_display_name ),
																		 'Phone'=>array(
																						'Number'	=>	(strlen($shipper_phone_number) < 10) ? '0000000000' :  htmlspecialchars( $shipper_phone_number )
																					   ),
																		 
																		// "EMailAddress"=>"",                                                          
																   
																	 ),
										   "ShipperNumber"=>$ups_shipper_number,
										   "ShipTo"=>array(
															 'Name'	=>	htmlspecialchars( $shipping_full_name ),
															 'Address'		=>	array(
																 'AddressLine'		=>	htmlspecialchars( $shipping_address_1 ). " , " .htmlspecialchars( $shipping_address_2 ),
																 'City'				=>	$shipping_city,
																 'StateProvinceCode'=>	$shipping_state,
																 'PostalCode'		=>	$shipping_postcode,
																 'CountryCode'		=>	$shipping_country,
															 ),
															 'AttentionName'	=>	htmlspecialchars( $shipping_full_name ),												
															 'Phone'=>array(
																			'Number'	=>	(strlen( $billing_phone ) < 10) ? '0000000000' : htmlspecialchars( $billing_phone )
																		   ),
															 
														   ),
										   "PaymentInformation"=>array(
																	   "Payer"=>array(
																						 'Name'	=>	htmlspecialchars( $ups_user_name ),
																						 'Address'		=>	array(
																							 'AddressLine'		=>	htmlspecialchars( $ups_origin_addressline ),
																							 'City'				=>	$ups_origin_city,
																							 'StateProvinceCode'	=>	$origin_state,
																							 'PostalCode'		=>	$ups_origin_postcode,
																							 'CountryCode'		=>	$origin_country,
																						 ),
																						 'ShipperNumber'	=>	$ups_shipper_number,
																						 'AttentionName'	=>	htmlspecialchars( $ups_display_name ),
																						 'Phone'=>array(
																										'Number'	=>	(strlen($shipper_phone_number) < 10) ? '0000000000' :  htmlspecialchars( $shipper_phone_number )
																										),
																										
																					  ),
																	 "ShipmentBillingOption"=>array(
																								 "Code"=>"10"
																								 )
																	   
																	   ),
										   "Service"=>array(
																 'Code'			=>	$shipping_service,
																 'Description'	=>	htmlspecialchars( $shipping_service_name ),	
															),
										   "ShipmentRatingOptions"=>array("NegotiatedRatesIndicator"=>"0"),		// currently this is not working with freight
										   "HandlingUnitOne"=>array(
																	  "Quantity"=>"1",
																	  "Type"=>array(
																						  "Code"=>"PLT"
																						  )
																	),
											 "Commodity"=>array(),
											 "Documents"=>array(
																 "Image"=>array("Type"=>array("Code"=>"20"),
																				"Format"=>array("Code"=>"01")
																				)
															 )
												 ),

                               );
					$commodities=array();
					$index=0;
					foreach( $package_data as $package )
					{
							$package=$package['Package'];
							$commodity	=	array(
							'Description'	=>'Package '.($index +1),
							'Weight'	=>array(
												'Value'=>(string)$package['PackageWeight']['Weight'],
												'UnitOfMeasurement'=>array('Code'=>(string)$package['PackageWeight']['UnitOfMeasurement']['Code'])
												),				
										);
							try{
								if(isset($package['Dimensions']['UnitOfMeasurement']) && isset($package['Dimensions']['UnitOfMeasurement']['Code']) )
								{
									$unit=$package['Dimensions']['UnitOfMeasurement']['Code'];
								}else
								{
									$unit="IN";
								}
								
							$commodity['Dimensions']	=	array(
																	'UnitOfMeasurement'	=>	array('Code'	=>	(string)$unit ),
																	'Length'	=>	$package['Dimensions']['Length'],
																	'Width'		=>	$package['Dimensions']['Width'],
																	'Height'	=>	$package['Dimensions']['Height']
																);															
							}catch(Exception $ex){	}					
							
							$commodity['NumberOfPieces']	= "1";
					
							$commodity['PackagingType']	=array('Code'=>"PLT");
							if(isset($_GET['FreightPackagingType']) && !empty($_GET['FreightPackagingType']))
							{
								$commodity['PackagingType']	=array('Code'=>$_GET['FreightPackagingType']);
							}
							$commodity['FreightClass']	="50";
							if(isset($_GET['FreightClass']) && !empty($_GET['FreightClass']))
							{
								$commodity['FreightClass']	=$_GET['FreightClass'];
							}
							$commodities[]=$commodity;			
						
					}
					
	$json_req['FreightShipRequest']['Shipment']['Commodity']=$commodities;
	$json_req['FreightShipRequest']['Shipment']['TimeInTransitIndicator']="";
	
	if(isset($_GET['PickupInstructions']) && !empty($_GET['PickupInstructions']))
	{
		$json_req['FreightShipRequest']['Shipment']['PickupInstructions']=$_GET['PickupInstructions'];
	}
	
	if(	(isset($_GET['HolidayPickupIndicator']) && $_GET['HolidayPickupIndicator']===true) ||
		(isset($_GET['InsidePickupIndicator']) && $_GET['HolidayPickupIndicator']===true)  ||
		(isset($_GET['ResidentialPickupIndicator']) && $_GET['HolidayPickupIndicator']===true)  ||
		(isset($_GET['WeekendPickupIndicator']) && $_GET['HolidayPickupIndicator']===true)  ||
		(isset($_GET['LiftGateRequiredIndicator']) && $_GET['HolidayPickupIndicator']===true)  ||
		(isset($_GET['LimitedAccessPickupIndicator'])  && $_GET['HolidayPickupIndicator']===true) )
	{
		$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']=array();
		$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['PickupOptions']=array();
		
		if(isset($_GET['HolidayPickupIndicator']) )
		{
			$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['PickupOptions']['HolidayPickupIndicator']="";
		}
		if(isset($_GET['InsidePickupIndicator']) )
		{
			$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['PickupOptions']['InsidePickupIndicator']="";
		}
		if(isset($_GET['ResidentialPickupIndicator']) )
		{
			$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['PickupOptions']['ResidentialPickupIndicator']="";
		}
		if(isset($_GET['WeekendPickupIndicator']) )
		{
			$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['PickupOptions']['WeekendPickupIndicator']="";
		}
		if(isset($_GET['LiftGateRequiredIndicator']) )
		{
			$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['PickupOptions']['LiftGateRequiredIndicator']="";
		}
		if(isset($_GET['LimitedAccessPickupIndicator']) )
		{
			$json_req['FreightShipRequest']['Shipment']['ShipmentServiceOptions']['PickupOptions']['LimitedAccessPickupIndicator']="";
		}
	}
	
	
	return json_encode($json_req);	
	}
}
