<?php
/*
===================================
© Copyright Webgility LLC 2017
----------------------------------------
This file and the source code contained herein are the property of Webgility LLC
and are protected by United States copyright law. All usage is restricted as per 
the terms & conditions of Webgility License Agreement. You may not alter or remove 
any trademark, copyright or other notice from copies of the content.

The code contained herein may not be reproduced, copied, modified or redistributed in any form
without the express written consent by an officer of Webgility LLC.
WooCommerce plug in version 	:	1.6.5.2
*/

ini_set("display_errors","Off");
error_reporting(E_ALL && ~E_NOTICE && '~E_STRICT');
//error_reporting(E_ALL);
# Added for removing memory exhausted  problem of service file   
require_once('D.WgCommon.php'); 

if(((int)str_replace("M","",ini_get("memory_limit")))<128)
    ini_set("memory_limit","128M");
$dirpath=dirname(dirname(dirname(__FILE__)));

define( 'WPSC_FILE_PATH', $dirpath.'/wp-e-commerce' );
if ( defined('ABSPATH') )
{  
	//require_once(ABSPATH . 'wp-load.php');
	//require_once(ABSPATH . 'wp-settings');
	
//	if(file_exists($path.'/wp-e-commerce/wpsc-admin/includes/image.php'))
//	require_once($path.'/wp-e-commerce/wpsc-admin/includes/image.php');
}	
else
{	
	
	require_once('../../../wp-load.php');
	require_once('../../../wp-settings.php');
	
		if(file_exists('../../../wp-admin/includes/image.php'))
	require_once('../../../wp-admin/includes/image.php');
	require_once('../../../wp-admin/includes/post.php');
	
	
global $woocommerce ;

$version = $woocommerce->version;
global $version;


}

class Webgility_Ecc_WP extends WgCommon
{	
	 public function auth_user($username,$password)
	{
		global $sql_tbl;
		add_filter('authenticate', 'wp_authenticate_username_password', 20, 3);
		
		
		if (preg_match('/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/si', $username)){
			$email = wp_authenticate_email_password('WP_User', $username, $password);	
		}else{
			$user = wp_authenticate_username_password('WP_User', $username, $password);	
		}
		$WgBaseResponse = new WgBaseResponse();		
		
		$plugins = get_option('active_plugins');
		 $required_plugin = 'webgility_wp_woocommerce/webgility_wp_woocommerce.php';
		 if ( !in_array( $required_plugin , $plugins ) ) {
    		$WgBaseResponse->setStatusCode('1');
			$WgBaseResponse->setStatusMessage('Plugin not activated please activate plugin before using it.');
			return $this->response($WgBaseResponse->getBaseResponse());		   
			exit;
		 }	
		 
		try
		{
			if (isset($user->errors[invalid_username]))
			{
				if(is_array($user->errors[invalid_username]))
				{
					$WgBaseResponse->setStatusCode('1');
					$WgBaseResponse->setStatusMessage('Invalid login. Authorization failed');
					return $this->response($WgBaseResponse->getBaseResponse());		   
					exit;
				}
			}elseif (isset($user->errors['incorrect_password']))
			{
				if(is_array($user->errors['incorrect_password']))
				{
					$WgBaseResponse->setStatusCode('2');
					$WgBaseResponse->setStatusMessage('Invalid password. Authorization failed');
					return $this->response($WgBaseResponse->getBaseResponse());		   
					exit;
				}
				
			}elseif(isset($email->errors[invalid_email])){
				if(is_array($email->errors[invalid_email]))
				{
					$WgBaseResponse->setStatusCode('1');
					$WgBaseResponse->setStatusMessage('Invalid login. Authorization failed');
					return $this->response($WgBaseResponse->getBaseResponse());		   
					exit;
				}
		   	}elseif (isset($email->errors['incorrect_password']))
			{
				if(is_array($email->errors['incorrect_password']))
				{
					$WgBaseResponse->setStatusCode('2');
					$WgBaseResponse->setStatusMessage('Invalid password. Authorization failed');
					return $this->response($WgBaseResponse->getBaseResponse());		   
					exit;
				}
				
			}
			else
			{
				return 0;
			}   	
			   
		
		}catch (Exception $e)
		{
			$WgBaseResponse->setStatusCode('1');
			$WgBaseResponse->setStatusMessage('Invalid login. Authorization failed');
			return $this->response($WgBaseResponse->getBaseresponse());		   
			exit;
		}
	}
	
	#***********************************************************
	# Function to check the admin username and password and also the Webgility Version and Store Version
	function checkAccessInfo($username,$password)
	{ 
		global $version;

		$status=$this->auth_user($username,$password);
		
		if($status!='0')
		{
			return $status;
		}

		$WgBaseResponse = new WgBaseResponse();				
		$WgBaseResponse->setStatusCode('0');
	
		$code = "0";
		$message = "Successfully connected to your online store.";
		$responseArray['StatusCode'] = $code;

		if($version!="0")
		{
			if($version < "1.5.8" || $version > "3.4.5" )
			{
				$WgBaseResponse->setStatusMessage($message ." However, your store version is " . $version ." which hasn't been fully tested with webgility. If you'd still like to continue, click OK to continue or contact Webgility to confirm compatibility.");
			}
			else
			{ 
				$WgBaseResponse->setStatusMessage($message);		
			}
		}
		else
		{ 
			$WgBaseResponse->setStatusMessage($message." However, webgility is unable to detect your store version. If you'd still like to continue, click OK to continue or contact Webgility to confirm compatibility.");
		}
		
		return $this->response($WgBaseResponse->getBaseResponse());
	} // Check AccessInfo
	
	#
	# Returns the Company Info of the Store
	#
	function getCompanyInfo($username,$password)
	{
		#check for authorisation
		$status=$this->auth_user($username,$password);
		if($status!='0')
		{
			return $status;
		}
		$CompanyInfo = new WG_CompanyInfo();	
		$CompanyInfo->setStatusCode('0');
		$CompanyInfo->setStatusMessage('All Ok');	
		$CompanyInfo->setStoreName(esc_attr(get_option('blogname')));
		$CompanyInfo->setAddress(htmlspecialchars($config['Company']['location_address'], ENT_NOQUOTES));
		$CompanyInfo->setcity(htmlspecialchars($config['Company']['location_address'], ENT_NOQUOTES));
		$CompanyInfo->setState(htmlspecialchars($region, ENT_NOQUOTES)?htmlspecialchars($region, ENT_NOQUOTES):htmlspecialchars(get_option('base_city'), ENT_NOQUOTES));
		$CompanyInfo->setCountry(htmlspecialchars(get_option('base_country'), ENT_NOQUOTES));
		$CompanyInfo->setZipcode(htmlspecialchars(get_option('base_zipcode'), ENT_NOQUOTES));
		$CompanyInfo->setPhone(htmlspecialchars($config['Company']['company_phone'], ENT_NOQUOTES));
		$CompanyInfo->setFax(htmlspecialchars($config['Company']['company_fax'], ENT_NOQUOTES));
		$CompanyInfo->setEmail(htmlspecialchars(get_option('admin_email'), ENT_NOQUOTES));
		$CompanyInfo->setWebsite(htmlspecialchars(get_option('home'), ENT_NOQUOTES));
		
		return $this->response($CompanyInfo->getCompanyInfo());		
	} // GetCompanyInfo
	
	
	#
	# Function returns All the Payment Methods used by the store
	#
	function getPaymentMethods($username,$password)
	{
		global $woocommerce;
		#check for authorisation
		$status=$this->auth_user($username,$password);
		if($status!='0')
		{
			return $status;
		}
		$PaymentMethods = new WG_PaymentMethods();
		$PaymentMethods->setStatusCode('0');		 
		$PaymentMethods->setStatusMessage('All Ok');	
		
		$payment_gateways = $woocommerce->payment_gateways;
	
		if($payment_gateways) 
		{
			$i=1;
			$payment = $payment_gateways->payment_gateways;
			
			foreach($payment as $k=>$gateway) 
			{ 				
				$PaymentMethod = new WG_PaymentMethod();
				$PaymentMethod->setMethodId($i);
				$PaymentMethod->setMethod($gateway->title?$gateway->title:"");
				$PaymentMethod->setDetail($gateway->description?$gateway->description:"");
				$PaymentMethods->setPaymentMethods($PaymentMethod->getPaymentMethod());
				$i++;			  
			}	
		}
	
		return $this->response($PaymentMethods->getPaymentMethods());
	} // getPaymentMethods
	
	#
	# Returns all the shipping methods used by the store
	#
	function getShippingMethods($username,$password)
	{
		global $woocommerce;
		$WgBaseResponse=new WgBaseResponse();
		$ShippingMethods = new WG_ShippingMethods();
		$ShippingMethods->setStatusCode('0');
		$ShippingMethods->setStatusMessage('All Ok');

		#check for authorisation
		$status=$this->auth_user($username,$password);
		if($status!='0')
		{
			return $status;
		}
		$shipping_methods = $woocommerce->shipping->load_shipping_methods();
		
		if(is_array($shipping_methods)) 
		{
		
			foreach($shipping_methods as $key => $module) 
			{ 
				$ShippingMethod = new WG_ShippingMethod();
				$ShippingMethod->setCarrier($module->method_title);
				$ShippingMethod->setMethods($module->title);
				$ShippingMethods->setShippingMethods($ShippingMethod->getShippingMethod());	
			}
		}
		else 
		{
			//$ShippingMethods->setStatusCode('9997');
			//$ShippingMethods->setStatusMessage("There is no shipment mathod active \n ");
			//return $this->response($WgBaseResponse->getBaseresponse());		   
			
		}
		return $this->response($ShippingMethods->getShippingMethods());
	
	}	
	#
	# function to return the store Category list so synch with QB inventory
	#
	function getCategory($username,$password)
	{
		global $woocommerce;
		
		$status=$this->auth_user($username,$password);
		if($status!='0')
		{
			return $status;
		}
		$Categories = new WG_Categories();
		$Categories->setStatusCode('0');
		$Categories->setStatusMessage('All Ok');
		$categories_arr = get_terms( 'product_cat', 'orderby=name&hide_empty=0' );
		
		if($categories_arr)
		{
			//for($i=0;$i<$total;$i++)
			foreach($categories_arr as $categories)
			{ 
				$Category =new WG_Category();
				$Category->setCategoryID($categories->term_id);
				$Category->setCategoryName($categories->name);
				$Category->setParentID($categories->parent);
				$Categories->setCategories($Category->getCategory());
			}
		}
		return $this->response($Categories->getCategories());
	} // Category
	
	#
	# function to return the store tax list so synch with QB inventory
	#
	function getTaxes($username,$password)
	{
		global $woocommerce;
		
		$status=$this->auth_user($username,$password);
		if($status!='0')
		{
			return $status;
		}
		$Taxes = new WG_Taxes();
		$Taxes->setStatusCode('0');
		$Taxes->setStatusMessage('All Ok');	
		
		$tax_classes = array_filter(array_map('trim', explode("\n", get_option('woocommerce_tax_classes'))));
		$classes_options = array();
			$classes_options[''] = __('Standard', 'woocommerce');
    		if ($tax_classes) foreach ($tax_classes as $class) :
    			$classes_options[sanitize_title($class)] = $class;
    		endforeach;
	
		if($classes_options)
		{
			$i=0;
			foreach($classes_options as  $key =>$classes)
			{ 
				$Tax =new WG_Tax();
				$Tax->setTaxID($key);
				if($key == "")
					$Tax->setTaxName($classes.' Rate');
				else 
					$Tax->setTaxName($classes);
				$Taxes->setTaxes($Tax->getTax());
				$i++;
			}
	
		}

		return $this->response($Taxes->getTaxes());
	} // getTaxes
	
	# retrive all order status
	function getOrderStatus($username,$password)
	{
		global $version;
		
		$status=$this->auth_user($username,$password);
		if($status!='0')
		{
			return $status;
		}
		$OrderStatuses = new WG_OrderStatuses();
		$OrderStatuses->setStatusCode('0');
		$OrderStatuses->setStatusMessage('All Ok');	
		
		if($version > "2.2.0")
		{
			$orderStatus = wc_get_order_statuses();
			
			foreach($orderStatus as $id=>$name)
				{	
					$OrderStatus =new WG_OrderStatus();
					$OrderStatus->setOrderStatusID($id);
					$OrderStatus->setOrderStatusName($name);
					$OrderStatuses->setOrderStatuses($OrderStatus->getOrderStatus());
				}	
		}
		else
		{
			$orderStatus = (array) get_terms('shop_order_status', array('hide_empty' => 0, 'orderby' => 'id'));
			foreach($orderStatus as $statusdata)
				{	
					$OrderStatus =new WG_OrderStatus();
					$OrderStatus->setOrderStatusID($statusdata->term_id);
					$OrderStatus->setOrderStatusName($statusdata->name);
					$OrderStatuses->setOrderStatuses($OrderStatus->getOrderStatus());
				}

		}
		
		return $this->response($OrderStatuses->getOrderStatuses());			
	
	} //getOrderStatus
	
	#
	# function to return the store item list so synch with QB inventory
	#
	function getItems($username,$password,$start_item_no=0,$limit=500)
	{
		global $wpdb,$synch_sale_price;
		global $version;
		#check for authorisation
		$status = $this->auth_user($username,$password);
		if($status!==0)
		{
		  return $status;
		}
		$Items = new WG_Items();
		
		$items_query_raw = '';
        
		//$others[0]['ItemCode'] = "'test-product','testing_variable'";
        
        if(isset($others[0]['ItemCode']) && trim($others[0]['ItemCode'])!='')
        {
             //$items_query_raw = " AND pm.meta_value ='".trim($others[0]['ItemCode'])."' ";    
             $items_query_raw = " AND pm.meta_value in (".trim($others[0]['ItemCode']).") ";
        }
        
        
        $sql_total = "SELECT ID FROM $wpdb->posts p INNER JOIN $wpdb->postmeta pm ON p.ID = pm.post_id WHERE p.post_type = 'product' and p.post_status='publish' AND pm.meta_key='_sku' ".$items_query_raw." ORDER BY p.post_date";
        
        $sql = "SELECT * FROM $wpdb->posts p INNER JOIN $wpdb->postmeta pm ON p.ID = pm.post_id WHERE post_type = 'product' and post_status='publish' AND pm.meta_key='_sku' ".$items_query_raw." ORDER BY post_date ASC limit $start_item_no,$limit";
			
		
		$total_record = count($wpdb->get_results($sql_total));
		
		$product_list = $wpdb->get_results($sql);
		
		$weight_unit = get_option('woocommerce_weight_unit');
		if($product_list)
		{
	
			$Items->setStatusCode('0');
			$Items->setStatusMessage('All Ok');
			$Items->setTotalRecordFound($total_record?$total_record:'0');

			foreach ($product_list as $iInfo) 
			{			
			
			 
				$product = get_metadata('post',$iInfo->ID);
			//	$product = new woocommerce_get_product_terms( $iInfo->ID );
				    
				  
				$Item = new WG_Item();			
				
				$Item->setItemID($iInfo->ID);				
				$Item->setItemCode($product['_sku'][0]);
				$Item->setItemDescription(strip_tags(html_entity_decode($iInfo->post_title)));
				$Item->setItemShortDescr(strip_tags(html_entity_decode($iInfo->post_content)));
				//$Item->setManufacturer($manufacturer['manufacturer']);
				$Item->setQuantity($product['_stock'][0]);
				
				$to_unit = 'lbs';
				$weight_in_lbs = woocommerce_get_weight($product['_weight'][0], $to_unit);
				
				$Item->setWeight($weight_in_lbs);
				$Item->setLowQtyLimit('');
				$Item->setFreeShipping('');
				$Item->setDiscounted('');
				$Item->setShippingFreight('');
				
				$Item->setWeight_Symbol($weight_unit);
				$weight_symbol_grams ='453.6';
				$Item->setWeight_Symbol_Grams($weight_symbol_grams);
				
				if($product['_tax_status'][0] == 'none')
					$taxexempt = 'N';
				else 
					$taxexempt = 'Y';
				$Item->setTaxExempt($taxexempt);
				$categoriesI = 0;
				 
					$cat_array =$wpdb->get_results("SELECT t.term_id ,t.name, t.slug, tt.parent FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON tt.term_id = t.term_id INNER JOIN $wpdb->term_relationships AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id INNER JOIN $wpdb->posts AS p ON p.ID = tr.object_id  WHERE tt.taxonomy IN ('product_cat') AND p.post_type= 'product' AND p.ID = $iInfo->ID order by p.`ID` ASC" , ARRAY_A);
					foreach($cat_array as $catid)
					{
								unset($catArray);
								$catArray['CategoryId'] = $catid['term_id'];
								$catArray['CategoryName'] =  $catid['name'];
								$catArray['ParentId'] = $catid['parent'];
								$Item->setCategories($catArray);
								$categoriesI++;
						
					}
				//$iVariants = $xmlResponse->createTag("ItemVariants", array(), '',$itemNode);
				$Variants = new WG_Variants();
				if($version >'2.0.0.0'){
					$varient_product_class = new WC_Product_Variable($iInfo->ID);
					$product_variation = $varient_product_class->has_child();
				}else{
					  $product_variation = $product['_product_attributes'];
					  $variation_list = unserialize($product_variation[0]);
				}
				if($product_variation){
					if($synch_sale_price == true)
					{
						$Item->setUnitPrice('0.00');
						$Item->setListPrice('0.00');
					}
					else
					{
						
						$Item->setUnitPrice('0.00');
						$Item->setListPrice('0.00');
					}
				}else{
					if($synch_sale_price == true)
					{
						//$Item->setUnitPrice($product['_sale_price'][0]);
						if(is_infinite($product['_sale_price'][0]) || is_nan($product['_sale_price'][0])){
							$Item->setUnitPrice('0.00');
						}else{
							$Item->setUnitPrice($product['_sale_price'][0]);
						}
						$Item->setListPrice('0.00');
					}
					else
					{
						$regularprice = substr_count($product['_regular_price'][0], '.');
						if($regularprice>1){
							continue;
						}
						//$Item->setUnitPrice($product['_regular_price'][0]);
						//$Item->setListPrice('0.00');
						if(is_infinite($product['_regular_price'][0]) || is_nan($product['_regular_price'][0])){
							$Item->setUnitPrice('0.00');
						}else{
							$Item->setUnitPrice($product['_regular_price'][0]);
						}
						$Item->setListPrice('0.00');
					}
				}
				if($product_variation)
				{  
					
					$children_products = &get_children( 'post_parent='.$iInfo->ID.'&post_type=product_variation');
					$op=0;
				//	$Options = new WG_Options();
					foreach ($children_products as $ioInfo)
					{
								
						if($version >'2.0.0.0')
						{
							
							//$variation = new WC_Product_Variation( $ioInfo->ID );
							$_product = get_metadata('post',$ioInfo->ID); 
							 
							$VariantArray['ItemCode'] = $_product['_sku'][0];
							$VariantArray['VarientID'] = $ioInfo->ID; 
							if($_product['_stock'][0]>0)
								$VariantArray['Quantity'] = $_product['_stock'][0];  
							else 
								$VariantArray['Quantity'] = "0.0";
								
							 
						if($synch_sale_price == true)
						{ 
								if($_product['_sale_price'][0]>0){	
									$VariantArray['UnitPrice'] = $_product['_sale_price'][0];
								}else{
										$VariantArray['UnitPrice'] = "0.0";
								}
	
						}else{
								$regularvprice = substr_count($_product['_regular_price'][0], '.');
								if($regularvprice>1){
									continue;
								}
								if($_product['_regular_price'][0]>0){	
									$VariantArray['UnitPrice'] = $_product['_regular_price'][0];
								}else{
										$VariantArray['UnitPrice'] = "0.0";
								}
								
							}
							$to_unit = 'lbs';
							$Vweight_in_lbs = woocommerce_get_weight($_product['_weight'][0], $to_unit);
							$VariantArray['Weight'] = $Vweight_in_lbs?$Vweight_in_lbs:"0.0";
							$Item->setItemVariants($VariantArray);
							$op++;
												}
						else
						{
						
							$variation = new WC_Product_Variation( $ioInfo->ID );
							$VariantArray['ItemCode'] = $variation->sku;
							$VariantArray['VarientID'] = $ioInfo->ID ;
							$VariantArray['Quantity'] = $variation->stock?$variation->stock:"0.0";
							$VariantArray['UnitPrice'] = $variation->sale_price?$variation->sale_price:"0.0";
							
							$to_unit = 'lbs';
							$Vweight_in_lbs = woocommerce_get_weight($variation->weight, $to_unit);
							$VariantArray['Weight'] = $Vweight_in_lbs?$Vweight_in_lbs:"0.0";
							$Item->setItemVariants($VariantArray);
						
						}
						
						}
					}
					
									 
				$Items->setItems($Item->getItem()); 
				
			} // end items
			 
		}
	
		return $this->response($Items->getItems());
	} // getItems
	
	  /**
	   * This is one part of the code that displays the variation combination forms in the add and edit product pages.
	   * If this fails to find any data about the variation combinations, it runs "variations_add_grid_view" instead
	   */
	  function variations_grid_view($product_id, $variation_values = null) 
	  {  
			global $wpdb;
			$product_id = (int)$product_id;
			$product_data = $wpdb->get_row("SELECT `price`, `quantity_limited` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` IN ('{$product_id}') LIMIT 1", ARRAY_A);
			$product_price = $product_data['price'];    
			
			$associated_variations = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_VARIATION_ASSOC."` WHERE `type` IN ('product') AND `associated_id` = '{$product_id}' ORDER BY `id` ASC",ARRAY_A);
			$variation_count = count($associated_variations);
		   
			
			if($variation_count > 0) {
			  
			  foreach((array)$associated_variations as $key => $associated_variation) {
				$variation_id = (int)$associated_variation['variation_id'];
				$excluded_values = $wpdb->get_col("SELECT `value_id` FROM `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` WHERE `product_id` IN('{$associated_variation['associated_id']}') AND `variation_id` IN ('{$variation_id}') AND `visible` IN ('1')");
				
				$included_value_sql = "AND `b{$variation_id}`.`value_id`  IN('".implode("','", $excluded_values)."')";
			  
				// generate all the various bits of SQL to bind the tables together
				$join_selected_cols[] = "`b{$variation_id}`.`value_id` AS `value_id{$variation_id}`";
				$join_tables[] = "`".WPSC_TABLE_VARIATION_COMBINATIONS."` AS `b{$variation_id}`";
				$join_on[] = "`a`.`id` = `b{$variation_id}`.`priceandstock_id`";
				$join_conditions[] = "`b{$variation_id}`.`variation_id` = '{$variation_id}' AND `b{$variation_id}`.`all_variation_ids` IN (':all_variation_ids:') $included_value_sql";
				$join_order[] = "`value_id{$variation_id}` ASC";
				
				// also store the columns in which the value ID's are, because we need them later
				$table_columns[] = "value_id{$variation_id}";
				
				$selected_variations[] = $variation_id;
				
				$get_variation_names = $wpdb->get_results("SELECT `id`, `name` FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `variation_id` = '{$variation_id}'", ARRAY_A);
				
				foreach((array)$get_variation_names as $get_variation_name) {
				  $variation_names[$get_variation_name['id']] = $get_variation_name['name'];
				}
			  }
			  
			  // implode the SQL statment segments into bigger segments
			  $join_selected_cols = implode(", ", $join_selected_cols);
			  $join_tables = implode(" JOIN ", $join_tables);
			  $join_on = implode(" AND ", $join_on);
			  $join_conditions = implode(" AND ", $join_conditions);
			  $join_order = implode(", ", $join_order);
			  
			  
			  asort($selected_variations);      
			  $all_variation_ids = implode(",", $selected_variations);
			  $join_conditions = str_replace(":all_variation_ids:",$all_variation_ids, $join_conditions );
			  
			  // Assemble and execute the SQL query
			   $associated_variation_values = $wpdb->get_results("SELECT `a`.*, {$join_selected_cols} FROM  `".WPSC_TABLE_VARIATION_PROPERTIES."` AS `a` JOIN {$join_tables} ON {$join_on} WHERE `a`.`product_id` = '$product_id' AND {$join_conditions} ORDER BY {$join_order}", ARRAY_A);
			   
			
					// if there are no associated variations, run this function instead
			if(count($associated_variation_values) < 1) {
				$price = $wpdb->get_var("SELECT `price` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` ='{$product_id}' LIMIT 1");
				return variations_add_grid_view((array)$selected_variations, $variation_values, $price, $limited_stock, $product_id);
			 }
			 
			  $br=0;
			  foreach((array)$associated_variation_values as $key => $associated_variation_row) {
			  
				// generate the variation name and ID arrays
				$associated_variation_names = array();
				$associated_variation_ids = array();
				foreach((array)$table_columns as $table_column) {
				  $associated_variation_ids[] =  $associated_variation_row[$table_column];
				  $associated_variation_names[] =  $variation_names[$associated_variation_row[$table_column]];
				}
				$group_defining_class = '';
				
				if($associated_variation_ids[0] != $associated_variation_values[$key+1]["value_id{$selected_variations[0]}"]) {
				  $group_defining_class = "group_boundary";
				}
				$previous_row_id = $associated_variation_ids[0];
				
				// Implode them into a comma seperated string
				$associated_variation_names =  stripslashes(implode(", ",(array)$associated_variation_names));
				
				$associated_variation_ids = implode(",",(array)$associated_variation_ids);
				
				$variation_settings_uniqueid = $product_id."_".str_replace(",","_",$associated_variation_ids);
				
			  
				// Format the price nicely
				if(is_numeric($associated_variation_row['price'])) {
				  $product_price = number_format($associated_variation_row['price'],2,'.', '');
				}
				$file_checked = '';
				if((int)$associated_variation_row['file'] == 1) {
				  $file_checked = "checked='checked'";
				}
						
				$var[$br]['name']  = $associated_variation_names;
				$var[$br]['stock'] = $associated_variation_row['stock'];
				$var[$br]['price'] = $product_price;  
				$var[$br]['optionid'] = $associated_variation_ids;  
				
						   
				$br++;
			  }
			
			}
			return $var;
		}
	
	function variations_add_grid_view($variations, $variation_values = null, $default_price = null, $limited_stock = true, $product_id = 0) 
	{	
		global $wpdb;
		$variation_count = count($variations);
		if($variation_count < 1) 
		{
			return "";
			exit();
		}
		$stock_column_state = '';
		if($limited_stock == false) {
		  $stock_column_state = " style='display: none;'";
		}
			if((float)$default_price == 0) {
			  $default_price = 0;
			}
		$default_price = number_format($default_price,2,'.', '');
	
		// Need to join the wp_variation_values variation_values`table to itself multiple times with no condition for joining, resulting in every combination of values being extracted
			foreach((array)$variations as $variation) {
		  $variation = (int)$variation;
		  
				$excluded_value_sql = '';
				if($product_id > 0 ) {
				  $included_values = $wpdb->get_col("SELECT `value_id` FROM `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` WHERE `product_id` IN('{$product_id}') AND `variation_id` IN ('{$variation}') AND `visible` IN ('1')");
					$included_values_sql = "AND `a{$variation}`.`id` IN('".implode("','", $included_values)."')";
				} else if(count($variation_values) > 0) {
					$included_values_sql = "AND `a{$variation}`.`id` IN('".implode("','", $variation_values)."')";
				
				}
				
		  
		  // generate all the various bits of SQL to bind the tables together
		  $join_selected_cols[] = "`a{$variation}`.`id` AS `id_{$variation}`, `a{$variation}`.`name` AS `name_{$variation}`";
		  $join_tables[] = "`".WPSC_TABLE_VARIATION_VALUES."` AS `a{$variation}`";
		  $join_conditions[] = "`a{$variation}`.`variation_id` = '{$variation}' $included_values_sql";
		}
		
		// implode the SQL statment segments into bigger segments
		$join_selected_cols = implode(", ", $join_selected_cols);
		$join_tables = implode(" JOIN ", $join_tables);
		$join_conditions = implode(" AND ", $join_conditions);
		// Assemble and execute the SQL query
		$associated_variation_values = $wpdb->get_results("SELECT {$join_selected_cols} FROM {$join_tables} WHERE {$join_conditions}", ARRAY_A);
		
			
			$variation_sets = array();
			$i = 0;
			foreach((array)$associated_variation_values as $associated_variation_value_set) {
			  foreach($variations as $variation) {
				$value_id = $associated_variation_value_set["id_$variation"];
				$name_id = $associated_variation_value_set["name_$variation"];
				$variation_sets[$i][$value_id] = $name_id;
			  }
		  $i++;
			}
			$br=0;
		foreach((array)$variation_sets as $key => $variation_set) {
		  
		  $variation_names = implode(", ", $variation_set);
		  $variation_id_array = array_keys((array)$variation_set);
		  $variation_ids = implode(",", $variation_id_array);
		  $variation_settings_uniqueid = "0_".str_replace(",","_",$variation_ids);
		  
		  $group_defining_class = '';
		  
		  $next_id_set = array_keys((array)$variation_sets[$key+1]);
		 
		  if($variation_id_array[0] != $next_id_set[0]) {
			$group_defining_class = "group_boundary";
		  } 
		  
			$var[$br]['name']  = $variation_names;				  
			$var[$br]['optionid'] = $variation_ids;  		           
			$br++;
		}	
		return $var;
		}
	
	#
	# function to return the store Manufacturer list so synch with QB inventory
	#
	function getManufacturers($username,$password)
	{

		$status=$this->auth_user($username,$password);
		if($status!='0')
		{
			return $status;
		}
		$Manufacturers = new WG_Manufacturers();
		$Manufacturers->setStatusCode('0');
		$Manufacturers->setStatusMessage('All Ok');
		
		$Manufacturer =new WG_Manufacturer();
		
		$Manufacturers->setManufacturers($Manufacturer->getManufacturer());	
		$Manufacturer->setManufacturerID('');
		$Manufacturer->setManufacturerName('');
		return $this->response($Manufacturers->getManufacturers());
    }//getManufacturers
	
	
	
	#
	# Function to Sync the Items and the Varients with the QB
	#
	function synchronizeItems($username,$password,$data,$storeid,$others)	
	{
	
		global $wpdb, $synch_sale_price,$synch_regular_price,$version; 	
		
		$Items = new WG_Items();
		
		$requestArray = $data;	
	
		if (!is_array($requestArray))
		{
				$Items->setStatusCode('9997');
				$Items->setStatusMessage('Unknown request or request not in proper format');				
				return $this->response($Items->getItems());				
		}

		if (count($requestArray) == 0)
		{
				$Items->setStatusCode('9996');
				$Items->setStatusMessage('REQUEST array(s) doesnt have correct input format');				
				return $this->response($Items->getItems());				
		}
		$Items->setStatusCode('0');
		$Items->setStatusMessage('All Ok');
		$itemsCount = 0;
		$itemsProcessed = 0;
		
		 // Go throught items
		 $itemsCount = 0;
		 $_err_message_arr = Array();
		
		$pos = strpos($others,'/');
	    if($pos)
	    {
	        $array_others = explode("/",$others);
				   
	    }else{
				$array_others=array();
				$array_others[]=$others;       
		}
		 
		 foreach($requestArray as $k=>$v4)//request
		 {		
		 		$Item = new WG_Item();
				$productID = $v4['ProductID'];
			
				$sku = $v4['Sku'];
				$productName = $v4['ProductName'];
				$qty = $v4['Qty'];
				$price = $v4['Price'];
				foreach($array_others as $ot)
				{
					$updated_attrib=0;
					
					foreach($v4['ItemVariants'] as $key=>$v5)//request

					{ 
						$vsku =$v5['Sku'];   
						$varient_id = $v5['VarientID'];
						$varient_qty = $v5['Quantity'];
						$varient_price = $v5['UnitPrice'];
						$status = "Success";
   
						if($ot=="QTY" || $ot=="BOTH")
						{			

								if($varient_qty>0){
									$var_status = 'instock';
									//update_post_meta( $varient_id, '_stock_status', 'instock' );
									///update_post_meta( $productID, '_stock_status', 'instock' );
									wc_update_product_stock_status($varient_id,$var_status);
									wc_update_product_stock_status($productID,$var_status);
                                }else{
									$var_status = 'outofstock';
									wc_update_product_stock_status($varient_id,$var_status);
									//update_post_meta( $varient_id, '_stock_status', 'outofstock' );
									//update_post_meta( $productID, '_stock_status', 'outofstock' );
								}
								//$wpdb->query("UPDATE $wpdb->postmeta SET meta_value = '".$varient_qty."'  WHERE post_id = ".$this->mySQLSafe($varient_id)." AND meta_key = '_stock' ");
								wc_update_product_stock($varient_id,$varient_qty);
								
								
								
								#CHECK ALL VARIANTS PRODUCTS QTY TO UPDATE PRODUCT STATUS  
								/* $children_products = &get_children( 'post_parent='.$productID.'&post_type=product_variation');
								$op=0;
								foreach ($children_products as $ioInfo)
								{
									$_product = get_metadata('post',$ioInfo->ID); 
									$qty = $_product['_stock'][0];
									if($qty>0){
									update_post_meta( $productID, '_stock_status', 'instock' );
								}

								$op++;
								} */
								#===end===========
								
								$status = "Success";

							}

							if($ot=="PRICE" || $ot=="BOTH") 
							{	
							
								if($synch_sale_price==true){		

								
									if($synch_regular_price==true) {
									//$wpdb->query("UPDATE $wpdb->postmeta SET meta_value = ".$this->mySQLSafe($varient_price)."  WHERE post_id = ".$this->mySQLSafe($varient_id)." AND meta_key = '_regular_price' ");
									update_post_meta($variation_id, '_regular_price',$varient_price);
									}
								
								//$wpdb->query("UPDATE $wpdb->postmeta SET meta_value = ".$this->mySQLSafe($varient_price)."  WHERE post_id = ".$this->mySQLSafe($varient_id)." AND meta_key = '_sale_price' ");
								update_post_meta($variation_id, '_sale_price',$varient_price);
								
								//$wpdb->query("UPDATE $wpdb->postmeta SET meta_value = ".$this->mySQLSafe($varient_price)."  WHERE post_id = ".$this->mySQLSafe($varient_id)." AND meta_key = '_price' ");
								update_post_meta($variation_id, '_price',$varient_price);
							

								}else{
									
									$sql = "SELECT meta_value FROM  $wpdb->postmeta  WHERE post_id = ".$this->mySQLSafe($varient_id)." AND meta_key = '_sale_price' ";

									$product_sale_price = $wpdb->get_results($sql);
								
									if($product_sale_price[0]->meta_value=='') {
								
									//$wpdb->query("UPDATE $wpdb->postmeta SET meta_value = ".$this->mySQLSafe($varient_price)."  WHERE post_id = ".$this->mySQLSafe($varient_id)." AND meta_key = '_price' ");
									update_post_meta($variation_id, '_price', $varient_price);
									} 
									//$wpdb->query("UPDATE $wpdb->postmeta SET meta_value = ".$this->mySQLSafe($varient_price)."  WHERE post_id = ".$this->mySQLSafe($varient_id)." AND meta_key = '_regular_price' ");
									update_post_meta($variation_id, '_regular_price',$varient_price);
											

								}
								
								$children = get_posts( array(
									'post_parent' 	=> $v4['ProductID'],
									'posts_per_page'=> -1,
									'post_type' 	=> 'product_variation',
									'fields' 		=> 'ids',
									'post_status'	=> 'publish'
								));
								$min_variation_price = $min_variation_regular_price = $min_variation_sale_price = $max_variation_price = $max_variation_regular_price = $max_variation_sale_price = 0;
								$children =& get_children( 'post_parent='.$v4['ProductID'].'&post_type=product_variation');
								
								
									if ($children) {
										foreach ( $children as $child ) {
													
													$sql = "SELECT meta_value FROM $wpdb->postmeta pm   WHERE meta_key = '_price' and pm.post_id = ".$child->ID;
													$product_price = $wpdb->get_results($sql);
													$child_price 		= $product_price[0]->meta_value;

													if($min_variation_regular_price==0)
													{
														$min_variation_regular_price = $child_price;
														$max_variation_regular_price = $child_price;
													}
			
													// Low price
													
													if ($child_price < $min_variation_regular_price) 
													{	
														$min_variation_regular_price = $child_price;
													}
													// High price
													if ($child_price > $max_variation_regular_price) 
													{
														$max_variation_regular_price = $child_price;
													}

												
										}
										
										$min_variation_price = $min_variation_regular_price;
										$max_variation_price = $max_variation_regular_price;
									}
									
									update_post_meta( $v4['ProductID'], '_price', $min_variation_price );
									update_post_meta( $v4['ProductID'], '_min_variation_price', $min_variation_price );
									update_post_meta( $v4['ProductID'], '_max_variation_price', $max_variation_price );
									update_post_meta( $v4['ProductID'], '_min_variation_regular_price', $min_variation_regular_price );
									update_post_meta( $v4['ProductID'], '_max_variation_regular_price', $max_variation_regular_price );
										
								$status = "Success";

						} 

						$updated_attrib++;

						$Variant = new WG_Variant();
						$Variant->setStatus($status);
						$Variant->setVarientID($varient_id);
						$Variant->setVariantSku($vsku);							
						$Item->setItemVariants($Variant->getVariant());
						$Item->setStatus('Success');
						$Item->setProductID($v4['ProductID']);
						$Item->setSku($v4['Sku']);							
						$Items->setItems($Item->getItem());
					} 

					if ($updated_attrib ==0)
					{  
						
						//if($others=="QTY" || $others=="BOTH")
						if($ot=="QTY" || $ot=="BOTH" )
						{	
							
							if($qty>0){
								$status = 'instock';
								//$wpdb->query("UPDATE $wpdb->postmeta SET meta_value = 'instock'  WHERE post_id = ".$this->mySQLSafe($productID)." AND meta_key = '_stock_status' ");
							}else{
								$status = 'outofstock';
								//$wpdb->query("UPDATE $wpdb->postmeta SET meta_value = 'outofstock'  WHERE post_id = ".$this->mySQLSafe($productID)." AND meta_key = '_stock_status' ");
							}
							//$wpdb->query("UPDATE $wpdb->postmeta SET meta_value = '".$qty."'  WHERE post_id = '".$v4['ProductID'] ."' AND meta_key = '_stock' ");
							wc_update_product_stock($productID,$qty);
							wc_update_product_stock_status($productID,$status);
							
							$status = "Success";
							 
						}
						
						
						if($ot=="PRICE" || $ot=="BOTH" ) 
						{
							if($synch_sale_price==true){
								
								if($synch_regular_price==true) {
									//$wpdb->query("UPDATE $wpdb->postmeta SET meta_value = ".$this->mySQLSafe($price)."  WHERE post_id = ".$this->mySQLSafe($productID)." AND meta_key = '_regular_price' ");
									update_post_meta($productID, '_regular_price',$price);
									}
								
								//$wpdb->query("UPDATE $wpdb->postmeta SET meta_value = ".$this->mySQLSafe($price)."  WHERE post_id = ".$this->mySQLSafe($productID)." AND meta_key = '_sale_price' ");
								update_post_meta($productID, '_sale_price',$price);
								
								//$wpdb->query("UPDATE $wpdb->postmeta SET meta_value = ".$this->mySQLSafe($price)."  WHERE post_id = ".$this->mySQLSafe($productID)." AND meta_key = '_price' ");
								update_post_meta($productID, '_price',$price);
							}else{ 
								
								$sql = "SELECT meta_value FROM  $wpdb->postmeta  WHERE post_id = ".$this->mySQLSafe($productID)." AND meta_key = '_sale_price' ";

								$product_sale_price = $wpdb->get_results($sql);
								
								if($product_sale_price[0]->meta_value=='') {
								
									//$wpdb->query("UPDATE $wpdb->postmeta SET meta_value = ".$this->mySQLSafe($price)."  WHERE post_id = ".$this->mySQLSafe($productID)." AND meta_key = '_price' ");
									update_post_meta($productID, '_price',$price);
								} 
								//$wpdb->query("UPDATE $wpdb->postmeta SET meta_value = ".$this->mySQLSafe($price)."  WHERE post_id = ".$this->mySQLSafe($productID)." AND meta_key = '_regular_price' ");
								update_post_meta($productID, '_regular_price',$price);
											
							}
							$status = "Success";			
							
						}
						$itemsProcessed++; 	
							
					}
					else if($updated_attrib == $k1+1)
					{
						$itemsProcessed++; 
					}
				}
				if($version>='2.2.10'){
				wc_delete_product_transients($productID);
				}
							$Item->setStatus('Success');
							$Item->setProductID($v4['ProductID']);
							$Item->setSku($v4['Sku']);							
							$Items->setItems($Item->getItem());	
					
		 } 
		 
		return $this->response($Items->getItems());
	} //SynchronizationItems
	
	
	function getOrdersRemained($start_date,$start_order_no=0,$str_excl_status,$str_date_filter,$LastModifiedDate,$MaxOrderNoInBatch)
	{
	   global $wpdb;
	   
	   $previous_orders = 0;  

	  if($LastModifiedDate!=''){ 
	  	if(!isset($MaxOrderNoInBatch) || $MaxOrderNoInBatch=='')
        {
	  		$previous_orders = $wpdb->get_var("SELECT COUNT(p.ID) FROM $wpdb->posts AS p  WHERE ".$str_date_filter."  AND p.post_type= 'shop_order' AND  p.post_status!='trash' order by p.post_modified,p.`ID` ASC ");
		}
		else
		{
			$previous_orders = $wpdb->get_var("SELECT COUNT(p.ID)+(SELECT COUNT(p.ID) FROM $wpdb->posts AS p  WHERE ".$str_date_filter."  AND p.post_type= 'shop_order' AND  p.post_status!='trash' order by p.post_modified,p.`ID` ASC) FROM $wpdb->posts AS p  WHERE  ".$str_date_filter." AND p.ID > ".(int)$MaxOrderNoInBatch."    AND p.post_type= 'shop_order' AND  p.post_status!='trash' order by p.post_modified,p.`ID` ASC ");
		}
	  }else{
	  	$previous_orders = $wpdb->get_var("SELECT COUNT(p.ID) FROM $wpdb->posts AS p  WHERE ".$str_date_filter." AND p.`ID`>".$start_order_no." AND p.post_type= 'shop_order' AND  p.post_status!='trash' order by p.`ID` ASC ");
	  }
	   return $previous_orders;
	}

	#
	# Return the Orders to sync with the QB according to the date and the staus and order id.
	#
	function getOrders($username,$password,$datefrom,$start_order_no,$ecc_excl_list,$order_per_response=25,$LastModifiedDate,$storeid,$others,$ccdetails,$MaxOrderNoInBatch,$ProductType,$isRetrievalByLatestCount='false')
	{
		//unset($isRetrievalByLatestCount);
		//$isRetrievalByLatestCount  = 'true';
		
		//unset($order_per_response);
		//$order_per_response = 15;
		
		global $wpdb,$version,$woocommerce,$download_order_number;
		$plugins = get_option('active_plugins');
		$sequential_order_numbers_plugin = 'woocommerce-sequential-order-numbers/woocommerce-sequential-order-numbers.php';
		$sequential_order_numbers_plugin_pro = 'woocommerce-sequential-order-numbers-pro/woocommerce-sequential-order-numbers.php';

		$orderlist='';
		foreach($others as $k=>$v)
		{
			if($download_order_number==true){
				if(in_array($sequential_order_numbers_plugin_pro, $plugins )) {
					$order_id = wc_seq_order_number_pro()->find_order_by_order_number($v['OrderId']);
					$orderlist = $orderlist?($orderlist.",'".$order_id."'"):"'".$order_id."'";
				}elseif(in_array($sequential_order_numbers_plugin, $plugins )){
					$order_id = wc_sequential_order_numbers()->find_order_by_order_number($v['OrderId']);
					$orderlist = $orderlist?($orderlist.",'".$order_id."'"):"'".$order_id."'";
				}else{
					$order_id = wc_sequential_order_numbers()->find_order_by_order_number($v['OrderId']);
					$orderlist = $orderlist?($orderlist.",'".$order_id."'"):"'".$order_id."'";
				}
			}else{
				$orderlist = $orderlist?($orderlist.",'".$v['OrderId']."'"):"'".$v['OrderId']."'";
			}
		}
		if(!isset($LastModifiedDate) || $LastModifiedDate==null || $LastModifiedDate==''){

			$LastModifiedDate = $datefrom ;
		}
		
		if($LastModifiedDate){
			$datefrom2 = explode(" ",$LastModifiedDate);
			$datetime1 = explode("-",$datefrom2[0]);	
			$LastModifiedDate = $datetime1[2]."-".$datetime1[0]."-".$datetime1[1];			
			$LastModifiedDate .=" ".$datefrom2[1]; 
		}
		elseif(!isset($datefrom) && (!isset($LastModifiedDate)) )
		{
			$datefrom=date('Y-m-d H:i:s');
		}
		else
		{

			$datetime1 = explode("-",$datefrom);			
			$datefrom = $datetime1[2]."-".$datetime1[0]."-".$datetime1[1];			
			$datefrom .=" 00:00:00"; 
		}
	
		#check for authorisation
		$status = $this->auth_user($username,$password);
		if($status!='0')
		{
		  return $status;
		}
	
		define("QB_ORDERS_PER_RESPONSE",$order_per_response);  
		
		$ecc_excl_list_Ary=explode(',',$ecc_excl_list);
		$ecc_excl_list_id="";
		if($version > "2.2.0")
		{
			$orderStatus = wc_get_order_statuses();
			
			foreach($orderStatus as $k => $v)
			{
				
				foreach($ecc_excl_list_Ary as $key => $value)
				{
					$label="'".strtolower($v)."'";
					$ecc_list_val = array(strtolower($value));
			
					if(in_array($label,$ecc_list_val))
					{
						$ecc_excl_list_id.="'".$k."',";
						
						
					}
				}
				
			}			
		}
		else
		{
			$orderStatus = (array) get_terms('shop_order_status', array('hide_empty' => 0, 'orderby' => 'id'));
			foreach($orderStatus as $k => $v)
			{
				
				if(is_array($v))
				{
					$label="'".$orderStatus[$k]['name']."'";
				
					if(in_array($label,$ecc_excl_list_Ary))
					{
					$ecc_excl_list_id.=$orderStatus[$k]['order'].",";
					}
				}
				elseif(is_object($v))
				{
					$label="'".$orderStatus[$k]->name."'";
					
					if(in_array($label,$ecc_excl_list_Ary))
					{
						$ecc_excl_list_id.=$orderStatus[$k]->term_id.",";
					}
				}
				else
				{ 
				
					$label="'".$orderStatus[$k]->name."'";
					
					if(in_array($label,$ecc_excl_list_Ary))
					{
					$ecc_excl_list_id.=$orderStatus[$k]->order.",";
					}
				}
			}
		
		}
		
		$ecc_excl_list_id = substr($ecc_excl_list_id, 0, -1); 

		define("QB_ORDERS_DOWNLOAD_EXCL_LIST", $ecc_excl_list_id);
		
		
		
		if(!$orderlist && $LastModifiedDate){
		$last_final = explode(" ",$LastModifiedDate);
		
		//$str_date_filter = " and o.last_modified >='$LastModifiedDate' ";
			$str_date_filter = "  p.post_modified >'".$last_final[0]."' and p.post_status in (".$ecc_excl_list_id.")  ";
		}elseif(!$orderlist && $datefrom){
			$str_date_filter = "  p.post_date >='$datefrom' and p.post_status in (".$ecc_excl_list_id.")  ";
		}else{
			$str_date_filter = "  p.post_date >='$datefrom' and p.post_status in (".$ecc_excl_list_id.")  ";
		} 
		
		if($isRetrievalByLatestCount=='true'){
			$orders_remained = 0;
		}elseif(!$orderlist && $LastModifiedDate)
		{
			    $orders_remained =$this->getOrdersRemained($start_date,$start_order_no,$str_excl_status,$str_date_filter,$LastModifiedDate,$MaxOrderNoInBatch);
				$orders_remained =  ($orders_remained > 0)? $orders_remained:"0";
		
		}elseif(!$orderlist && $datefrom)
		{
		        $orders_remained =$this->getOrdersRemained($start_date,$start_order_no,$str_excl_status,$str_date_filter,$LastModifiedDate='',$MaxOrderNoInBatch=0);
				$orders_remained =  ($orders_remained > 0)? $orders_remained:"0";
		
		}  
		
	if($isRetrievalByLatestCount=='true'){
			$orders = $wpdb->get_results("SELECT  p.ID FROM $wpdb->posts AS p WHERE p.post_type= 'shop_order' AND p.post_status!='trash' order by p.post_modified,p.ID DESC ".(QB_ORDERS_PER_RESPONSE>0?"LIMIT 0, ".QB_ORDERS_PER_RESPONSE:''),ARRAY_A);
	}elseif($orderlist!=''){
	
		$orders = $wpdb->get_results("SELECT  p.ID FROM $wpdb->posts p WHERE p.`ID` in ($orderlist) order by p.`ID`  ASC ",ARRAY_A);
	}
	elseif(!$orderlist && $LastModifiedDate){

		if(!isset($MaxOrderNoInBatch) || $MaxOrderNoInBatch=='')
        {
			
   			$orders = $wpdb->get_results("SELECT  p.ID FROM $wpdb->posts AS p WHERE  '".$last_final[0]."' <= DATE_FORMAT( p.post_modified, '%Y-%m-%d' ) AND p.post_type= 'shop_order' AND p.post_status!='trash' AND p.post_status in (".$ecc_excl_list_id.")  order by p.post_modified,p.ID ASC ".(QB_ORDERS_PER_RESPONSE>0?"LIMIT 0, ".QB_ORDERS_PER_RESPONSE:''),ARRAY_A);
				
		
		}
		else
		{
			
			$orders = $wpdb->get_results("
			select * from (
			SELECT  p.ID,p.post_modified FROM $wpdb->posts AS p WHERE p.post_modified  = '".$LastModifiedDate."'  and p.ID >".(int)$MaxOrderNoInBatch."  AND p.post_type= 'shop_order' AND p.post_status!='trash' AND p.post_status in (".$ecc_excl_list_id.")
			union
			SELECT  p.ID,p.post_modified FROM $wpdb->posts AS p WHERE p.post_modified  > '".$LastModifiedDate."'  AND p.post_type= 'shop_order' AND p.post_status!='trash' AND p.post_status in (".$ecc_excl_list_id.")
			) as c 
			ORDER BY 2,1
			".(QB_ORDERS_PER_RESPONSE>0?"LIMIT 0, ".QB_ORDERS_PER_RESPONSE:''),ARRAY_A);
		
		}
	
	}
	else
	{
		
		$orders = $wpdb->get_results("SELECT  p.ID FROM $wpdb->posts AS p WHERE ".$str_date_filter." AND p.`ID`>".$start_order_no." AND p.post_type= 'shop_order' AND p.post_status!='trash' AND p.post_status in (".$ecc_excl_list_id.") order by p.`ID` ASC ".(QB_ORDERS_PER_RESPONSE>0?"LIMIT 0, ".QB_ORDERS_PER_RESPONSE:''),ARRAY_A);
	}	
		
		$currency = $wpdb->get_results("SELECT  option_value FROM $wpdb->options  WHERE option_name = 'woocommerce_currency' ");

		$no_orders = count($orders);
		$Orders = new WG_Orders();
		if ($no_orders<=0) {
			$no_orders = true;

			$Orders->setStatusCode($no_orders?"9999":"0");
			$Orders->setStatusMessage($no_orders?"No Orders returned":"Total Orders:".$orders_remained);
			return $this->response($Orders->getOrders());
		  } 
		 
		if($orders){
	
			$Orders->setStatusCode(0);
			$Orders->setStatusMessage("Total Orders:".$orders_remained);
			
			foreach($orders as $info) {
			
			
			$order = new WC_Order( $info['ID'] );
						
			$weightsymbol = 'lbs';
			$weight_symbol_grams ='453.6';
			
			$Order = new WG_Order();
			if($download_order_number==true){
				$order_id = $order->get_order_number();;
				$Order->setOrderId($order_id);
			}else{
				$Order->setOrderId($order->id);
			}
			$Order->setTitle("");
			$Order->setFirstName($order->billing_first_name);
			$Order->setLastName($order->billing_last_name);
			$old_date = date($order->order_date);
			$new_date = date('m-d-Y H:i:s', strtotime($old_date));
			$date_time = explode (' ',$new_date);
			
			$Order->setDate($date_time[0]);
			$Order->setTime($date_time[1]);
		
			$Order->setLastModifiedDate(date("m-d-Y H:i:s",strtotime($order->modified_date)));

			$Order->setStoreID('');
			$Order->setStoreName('');
			$Order->setCurrency($order->currency);
			$Order->setWeight_Symbol($weightsymbol);
			$Order->setWeight_Symbol_Grams($weight_symbol_grams);
			
			$Order->setComment(utf8_encode($order->customer_note)?utf8_encode($order->customer_note):"");
			if($version>='2.2.10'){
				$status = wc_get_order_status_name($order->post_status);
				$Order->setStatus($status);
			}else{
				$Order->setStatus($order->status);
			}
			unset($orderNote);
			$ordreNotesArr = $wpdb->get_results("SELECT comment_content 	FROM $wpdb->comments c WHERE c.comment_post_ID  = ".$order->id." order by c.comment_date  DESC ",ARRAY_A);
			foreach($ordreNotesArr as $k=>$Notes){
				$orderNote[] = $Notes['comment_content']; 
			}
			$Order->setNotes($orderNote[0]);
			$Order->setFax("");
			
			
				#Credit Memo
			$Order->setIsCreditMemoCreated("0");
			if($ProductType=='UNIFY')
			{ 
				$Refund = $wpdb->get_results("SELECT  p.ID,p.post_date FROM $wpdb->posts AS p WHERE  p.post_type= 'shop_order_refund'  AND p.post_parent =".$order->id." order by p.`ID` ASC ");
			
				if($Refund){
			
				foreach($Refund as $Reinfo) {
					
					$Order->setIsCreditMemoCreated("1");
					$Refund_ID =  $Reinfo->ID ;
					$post_date = $Reinfo->post_date ;
					
					$refundInfo = get_metadata('post',$Refund_ID);
					$total_refund =  abs($refundInfo['_order_total'][0]) ;
					$CreditMemo = new CreditMemo();
					
			
					$CreditMemo->setCreditMemoID($Refund_ID);
					$CreditMemo->setCreditMemoDate($post_date);
					$CreditMemo->setSubtotal($total_refund);
					
			 
						$refund = new WC_Order_Refund($Refund_ID);
					
					foreach($refund->get_items() as $item_id=>$refundId) {
							
							$RefundItemID = $refundId['product_id']; 
							$qty = abs($refundId['quantity']);
							$price = abs($refundId['total']);
							 $orderItemID = wc_get_order_item_meta($item_id,'_refunded_item_id', true);
							 
							 if(isset($refundId['variation_id']) && $refundId['variation_id']!='0') {
							    $RefundItemvariationID = $refundId['variation_id'];
                                $productv = get_metadata('post', $RefundItemvariationID );
								$sku = $productv['_sku'][0] ;
								$ItemID = $RefundItemvariationID; 								
							 }else {
							    $product = get_metadata('post', $RefundItemID );
							    $sku = $product['_sku'][0] ;
								$ItemID = $RefundItemID; 
							
						      }
							$product = get_metadata('post', $RefundItemID );
							$Productname = $product['_sku'][0] ;
							
							if($Productname=='')
								continue;
							
							$or_qty = wc_get_order_item_meta($orderItemID,'_qty', true); 
							$or_price = abs(wc_get_order_item_meta($orderItemID,'_line_total', true));
							
													 
							$CancelItemDetail = new CancelItemDetail();
							$CancelItemDetail->setItemID($ItemID);
							$CancelItemDetail->setItemSku($sku);
							$CancelItemDetail->setItemName($Productname);
							$CancelItemDetail->setQtyCancel($qty);
							$CancelItemDetail->setQtyInOrder($or_qty);
							//$CancelItemDetail->setPriceCancel($price);
							if(is_infinite($price) || is_nan($price))
								$CancelItemDetail->setPriceCancel('0.00');
							else
								$CancelItemDetail->setPriceCancel($price);
							//$CancelItemDetail->setItemPrice($or_price);
							if(is_infinite($or_price) || is_nan($or_price))
								$CancelItemDetail->setItemPrice('0.00');
							else
								$CancelItemDetail->setItemPrice($or_price);
							
							$CreditMemo->setCancelItemDetail($CancelItemDetail->getCancelItemDetail()); 
						}
						$Order->setCreditMemos($CreditMemo->getCreditMemo());
				}
			}
			 }else {
				
				$total_refund=0;
				$ccitemArray =  array();
				$totalccitemqty =0;
				
				$Refund = $wpdb->get_results("SELECT  p.ID,p.post_date FROM $wpdb->posts AS p WHERE  p.post_type= 'shop_order_refund'  AND p.post_parent =".$order->id." order by p.`ID` ASC ");
			
				if($Refund){
			
				    foreach($Refund as $Reinfo) {
				
					    $Order->setIsCreditMemoCreated("1");
					    $Refund_ID =  $Reinfo->ID ;
					    $post_date = $Reinfo->post_date ;
					
					    $refundInfo = get_metadata('post',$Refund_ID);
					    $total_refund = $total_refund + (abs($refundInfo['_order_total'][0])) ;
					
				 	    $refund = new WC_Order_Refund($Refund_ID);  
					
						foreach($refund->get_items() as $item_id=>$refundId) {
						
							$RefundItemID = $refundId['product_id']; 
							$qty = abs($refundId['quantity']);
							$price = abs($refundId['total']);
							
							 $orderItemID = wc_get_order_item_meta($item_id,'_refunded_item_id', true);
							 
							if(isset($refundId['variation_id']) && $refundId['variation_id']!='0') {
							    $RefundItemvariationID = $refundId['variation_id'];
                                $productv = get_metadata('post', $RefundItemvariationID );
								$sku = $productv['_sku'][0] ;
								$ItemID = $RefundItemvariationID; 								
							 }else {
							    $product = get_metadata('post', $RefundItemID );
							    $sku = $product['_sku'][0] ;
								$ItemID = $RefundItemID; 
							
						    }
							$product = get_metadata('post', $RefundItemID );
							$Productname = $product['_sku'][0] ;
							
							if($Productname=='')
								continue;
							
							$or_qty = wc_get_order_item_meta($orderItemID,'_qty', true); 
							$or_price = abs(wc_get_order_item_meta($orderItemID,'_line_total', true));
						    
							 
						    $totalccitemqty = $totalccitemqty+$qty;
						    if(array_key_exists($sku,$ccitemArray)) {
									$ccitemArray[$sku]["qty"] = $ccitemArray[$sku]["qty"]+$qty;
							
						    } else {

                                    $ccitemArray[$sku]["qty"] = $qty;
									$ccitemArray[$sku]["name"] = $Productname ;
									$ccitemArray[$sku]["ID"] = $ItemID;
									$ccitemArray[$sku]["or_qty"] = $or_qty;
									$ccitemArray[$sku]["or_price"] = $or_price;
								
							}	 
						}
					}
				}
						 
			    if($total_refund!=0) {
						$CreditMemo = new CreditMemo();
						$CreditMemo->setSubtotal($total_refund);
						$CreditMemo->setCreditMemoDate($post_date);
					
                    foreach($ccitemArray as $cckey=>$ccval) {
					
						$CancelItemDetail = new CancelItemDetail();
						$CancelItemDetail->setItemID($ccval["ID"]);
						$CancelItemDetail->setItemSku($cckey);
						$CancelItemDetail->setItemName($ccval["name"]);
						$CancelItemDetail->setQtyCancel($ccval["qty"]);
						$CancelItemDetail->setQtyInOrder($ccval["or_qty"]);
						
						if(is_infinite(round(($total_refund/$totalccitemqty),3)) || is_nan(round(($total_refund/$totalccitemqty),3)) )
							$CancelItemDetail->setPriceCancel('0.00');
						else
							$CancelItemDetail->setPriceCancel(round(($total_refund/$totalccitemqty),3));
						
						if(is_infinite(round(($total_refund/$totalccitemqty),3)) || is_nan(round(($total_refund/$totalccitemqty),3)))
							$CancelItemDetail->setItemPrice('0.00');
						else
							$CancelItemDetail->setItemPrice(round(($total_refund/$totalccitemqty),3));
						
						/* $CancelItemDetail->setPriceCancel(round(($total_refund/$totalccitemqty),3));
						$CancelItemDetail->setItemPrice(round(($total_refund/$totalccitemqty),3)); */						
						$CreditMemo->setCancelItemDetail($CancelItemDetail->getCancelItemDetail()); 
					}

						$Order->setCreditMemos($CreditMemo->getCreditMemo());
					}
				}
			
			
			# Orders/Bill/CreditCard info
			$shipto = '';
			$Bill = new WG_Bill();
			$CreditCard = new WG_CreditCard();
			$txn = "";
                        $txnArray = get_post_meta($order->id, '_transaction_id');
                        //print_r($txnArray);
                        //"TransactionId":["3LJ98950EK704654R"]
                        if(is_array($txnArray) && isset($txnArray[0])){
                            $txn = $txnArray[0];
                        }
			
			$CreditCard->setTransactionId($txn);
			$CreditCard->getCreditCard();
			$Bill->setCreditCardInfo($CreditCard->getCreditCard());
	
			$Bill->setPayMethod($order->payment_method_title?$order->payment_method_title:$order->payment_method);
			$Bill->setPayStatus('');
			$Bill->setTitle('');
			$Bill->setFirstName($order->billing_first_name);
			$Bill->setLastName($order->billing_last_name);
			$Bill->setCompanyName($order->billing_company);
			
			#Billing details
			$Bill->setAddress1($order->billing_address_1);				
			$Bill->setAddress2($order->billing_address_2);				
			$Bill->setCity($order->billing_city);				
			$Bill->setState($order->billing_state);				
			$Bill->setZip($order->billing_postcode);				
			$Bill->setCountry($order->billing_country);				
			$Bill->setEmail($order->billing_email);				
			$Bill->setPhone($order->billing_phone);				
			$Bill->setPONumber('');								
			$Order->setOrderBillInfo($Bill->getBill());	
			
			# Orders/Ship info
			$Ship =new WG_Ship();
			$shipping_methods = $woocommerce->shipping->load_shipping_methods();
			
			if($shipping_method=="")
			{
				$shipping_details = $order->get_shipping_methods();
				foreach ($shipping_details as $k=>$v)
				{
					
					$shipping_method = $v['name'];
					$method_id = $v['method_id'];
				}
				
			
			}
			
			$ship_carrier = explode ("_",$method_id);
			$shipping_carrir = "";
			for ($i=0;$i<sizeof($ship_carrier);$i++)
			{
				if($i==0)
				{
					$shipping_carrir .= ucfirst($ship_carrier[0]);
				}
				else
				{
					$shipping_carrir .= " ".ucfirst($ship_carrier[$i]);
				}
			}

			$Ship->setShipMethod($shipping_carrir?$shipping_carrir:$order->shipping_method_title);
			$Ship->setCarrier($shipping_method?$shipping_method:$order->shipping_method);
			$Ship->setTrackingNumber('');
			$Ship->setTitle("");
			$Ship->setFirstName($order->shipping_first_name);
			$Ship->setLastName($order->shipping_last_name);
			$Ship->setCompanyName($order->shipping_company);
			$Ship->setAddress1($order->shipping_address_1);
			$Ship->setAddress2($order->shipping_address_2);
			$Ship->setCity($order->shipping_city);
			$Ship->setState($order->shipping_state);
			$Ship->setZip($order->shipping_postcode);
			$Ship->setCountry($order->shipping_country);
			$Ship->setEmail('');
			$Ship->setPhone("");
			$Order->setOrderShipInfo($Ship->getShip());
			unset($shipping_carrir,$shipping_method);
			if(isset($order->order_custom_fields['_order_items'][0]))
			{
				$product_items= $order->order_custom_fields['_order_items'][0];
				$product_items_arr = unserialize($product_items);
			}
			else
			{
				$product_items_arr = "";
			}

			
			if($version >= '2.0.0')
			{
			
			foreach($order->get_items() as $cart_row) {
				
				$Item = new WG_Item();	
				if($cart_row['variation_id']>0 ){
					
				$_product = get_metadata('post',$cart_row['variation_id']);
					if(empty($_product['_sku'][0])){
						$varsku = get_post_meta($cart_row['product_id'],'_sku');
						$_product['_sku'][0] = $varsku[0];
					}
				}else{
					
						$_product = get_metadata('post',$cart_row['product_id']);
					
				}
				$item_meta = new WC_Order_Item_Meta( $cart_row['item_meta'] );

				$Item->setItemID($cart_row['product_id']);	
				$Item->setItemCode($_product['_sku'][0]?$_product['_sku'][0]:$cart_row['name']);		
				
				$product_fields = $wpdb->get_results("SELECT  p.post_title, p.post_content FROM $wpdb->posts p WHERE p.ID = ".$cart_row['product_id']."  ",ARRAY_A);
				
				$Item->setItemDescription(html_entity_decode(stripslashes($product_fields[0]['post_title'])? ($product_fields[0]['post_title']) : $cart_row['name'] ));
				
				$desc = "";
				if($cart_row['variation_id']>0 ){
					$desc=htmlentities(substr(html_entity_decode($_product['_variation_description'][0]),0,4000),ENT_QUOTES);	
				}else{
					$desc=htmlentities(substr(html_entity_decode($product_fields[0]['post_content']),0,4000),ENT_QUOTES);
				}
				
				$Item->setItemShortDescr(strip_tags(htmlentities($desc)));
				unset($desc);
				
				$Item->setQuantity($cart_row['qty']);
					
				$Dimention['Length']=(float)$_product['_length'][0];
				$Dimention['Width']=(float)$_product['_width'][0];
				$Dimention['Height']=(float)$_product['_height'][0];
				$Dimention['Unit'] = 'in';
				
				$Item->setDimention($Dimention);
				$unitPrice = $cart_row['line_subtotal']/$cart_row['qty'];
				#$Item->setUnitPrice($unitPrice);
				if(is_infinite($unitPrice) || is_nan($unitPrice))
					$Item->setUnitPrice('0.00');
				else
					$Item->setUnitPrice($unitPrice);
				
				$to_unit = 'lbs';
				$weight_in_lbs = woocommerce_get_weight($_product['_weight'][0], $to_unit);
				$Item->setWeight($weight_in_lbs);
				$Item->setFreeShipping('');
				$Item->setDiscounted('');
				$Item->setshippingFreight('');
				$Item->setWeight_Symbol($weightsymbol);
				$Item->setWeight_Symbol_Grams($weight_symbol_grams);
			
				if($cart_row['line_subtotal_tax']!="" && $cart_row['line_tax']!="" ){
					$Item->setTaxExempt('N');
				}else{
					$Item->setTaxExempt('Y');
				}
				$Item->setOneTimeCharge('');
				$Item->setItemTaxAmount($cart_row['line_subtotal_tax']);

				$Itemoption = new WG_Itemoption();
				
				foreach ($_product as $meta_k=>$meta_v)
				{
					$pos = strpos($meta_k, 'attribute_');
					
					if ($pos!==false) {
								$name = explode("_",$meta_k);
							$term = get_term_by('slug', $meta_value, esc_attr( str_replace( 'attribute_', '', $meta_key ) ) );
								if ( ! is_wp_error( $term ) && $term->name )
									$meta_value = $term->name;
								
								#if attrubute has blank value
								if(empty($meta_v[0]) && isset($item_meta->meta[$name[1]][0])){
									$meta_v[0] = $item_meta->meta[$name[1]][0];
								}
								$Itemoption->setOptionName($name[1]);
								$Itemoption->setOptionValue($meta_v[0]);	
								$Item->setItemOptions($Itemoption->getItemoption());					
					}
				}
				foreach ($item_meta->meta as $meta_key => $meta_value ) {

						if ( ! $meta_value || ( substr( $meta_key, 0, 1 ) == '_' ) )
							continue;
		
						// Get first value
						$meta_value = $meta_value[0];
		
						// If this is a term slug, get the term's nice name
						if ( taxonomy_exists( esc_attr( str_replace( 'attribute_', '', $meta_key ) ) ) ) {
							$term = get_term_by('slug', $meta_value, esc_attr( str_replace( 'attribute_', '', $meta_key ) ) );
							if ( ! is_wp_error( $term ) && $term->name )
								$meta_value = $term->name;
						}
		
						if ( $flat )
							$meta_list[] = esc_attr( attribute_label( str_replace( 'attribute_', '', $meta_key ) ) . ': ' . $meta_value );
						else
						{

							//$Itemoption->setOptionName(wp_kses_post( $meta_key ));
							//$Itemoption->setOptionValue(wp_kses_post( $meta_value ));
								
							//$Item->setItemOptions($Itemoption->getItemoption());
						}
		
				}
				
				 //CODE UPDATED BECAUSE OF TAX ISSUE WHILE USING DISCOUNT @ 2-SEP-2013
				 //$total_tax=$total_tax+$cart_row['line_subtotal_tax']+$order->get_shipping_tax();
				 $total_tax=$total_tax+$cart_row['line_tax'];
				 //END OF TAX CODE UPDATE
				
				$Order->setOrderItems($Item->getItem());
			} // end items 
						
			}
			else
			{	

				
				foreach($product_items_arr as $cart_row) {

		
				$Item = new WG_Item();	
			if($cart_row['variation_id']>0 ){
					//$item_price = $_product->sale_price;
					$_product = new WC_Product_Variation($cart_row['variation_id'] );
				}else{
					//$item_price = $_product->sale_price;
					$_product = new WC_Product( $cart_row['id'] );
				}
	


				$Item->setItemCode($_product->sku?$_product->sku:$cart_row['name']);		
				//echo "SELECT  p.post_title FROM $wpdb->posts p WHERE p.ID = ".$cart_row['id']."  ";
				$product_fields = $wpdb->get_results("SELECT  p.post_title, p.post_content FROM $wpdb->posts p WHERE p.ID = ".$cart_row['id']."  ",ARRAY_A);
				
				$Item->setItemDescription(html_entity_decode(stripslashes($product_fields[0]['post_title'])? ($product_fields[0]['post_title']) : $cart_row['name'] ));
				
				
				$Item->setQuantity($cart_row['qty']);
				
				 $sql = "SELECT meta_value FROM `".WPSC_TABLE_PRODUCTMETA."` AS `meta` WHERE `meta`.`product_id` = ".$cart_row['prodid']." and `meta_key`='sku'";
				$product_meta_sku = $wpdb->get_results($sql,ARRAY_A);	
				
				
					
				$Dimention['Length']=(float)$_product->length;
				$Dimention['Width']=(float)$_product->width;
				$Dimention['Height']=(float)$_product->height;
				$Dimention['Unit'] = 'cm';
				$Item->setDimention($Dimention);				
				$unitPrice = $cart_row['line_subtotal']/$cart_row['qty'];
				$Item->setUnitPrice($unitPrice);
				$to_unit = 'lbs';
				$weight_in_lbs = woocommerce_get_weight($_product->weight, $to_unit);
				$Item->setWeight($weight_in_lbs);
				$Item->setFreeShipping('');
				$Item->setDiscounted('');
				$Item->setshippingFreight('');
				$Item->setWeight_Symbol($weightsymbol);
				$Item->setWeight_Symbol_Grams($weight_symbol_grams);
			
				if($cart_row['line_subtotal_tax']!="" && $cart_row['line_tax']!="" ){
					$Item->setTaxExempt('N');
				}else{
					$Item->setTaxExempt('Y');
				}
				$Item->setOneTimeCharge('');
				$Item->setItemTaxAmount($cart_row['line_subtotal_tax']);

				$Itemoption = new WG_Itemoption();
				
					foreach($cart_row['item_meta'] as $option) 
					{ 
							
	
						$Itemoption->setOptionName(htmlentities(ucfirst(sanitize_title(str_replace('pa_', '', $option['meta_name'])))));
						$Itemoption->setOptionValue(htmlentities($option['meta_value']));
						
						$Item->setItemOptions($Itemoption->getItemoption());
					  }
				 
				 $total_tax=$total_tax+$cart_row['line_subtotal_tax'];

				$Order->setOrderItems($Item->getItem());
			} // end items 
			//Download fee as a line item
			
				
			}
			foreach($order->get_fees() as $order_fee) {
			
				if(isset($order_fee['total'])) {
					$Item = new WG_Item();
					
					$Item->setItemCode($order_fee['name']);
					$Item->setItemDescription($order_fee['name']);
					$Item->setItemShortDescr($order_fee['name']);
					$Item->setQuantity(intval(1));
					$Item->setUnitPrice($order_fee['total']);
					$Order->setOrderItems($Item->getItem());
						}
				}
			$charges =new WG_Charges();
			
			$order_tot_discount=$order->order_discount?$order->order_discount:0.00;
            if(isset($order->cart_discount) && $order->cart_discount > '0.00')
            {
                $order_tot_discount=$order_tot_discount+$order->cart_discount?$order->cart_discount:0.00;
            }
            $charges->setDiscount($order_tot_discount);
			
			$charges->setStoreCredit('0.00');
			$charges->setTax($total_tax+$order->get_shipping_tax()?$total_tax+$order->get_shipping_tax():'0.00');
			unset($total_tax);
		//	unset($totaltax,$custom_tax_val, $custom_tax_val_tot,$tax_calc,$basetax);
			$charges->setShipping($order->order_shipping?$order->order_shipping:'0.00');
			$charges->setTotal($order->order_total?$order->order_total:"0.00");
			
			$Order->setOrderChargeInfo($charges->getCharges());
					
			$Order->setShippedOn(date("m-d-Y",strtotime($order->order_date)));
			$Order->setShippedVia($order->$shipping_method_title?$shipping_method_title:$shipping_method);
			
			$MaxOrderNoInBatch=$order->id;
		
			$MaxdateNoInBatch=date("Y-m-d",strtotime($order->modified_date));
			 
			
				
			
			$Orders->setOrders($Order->getOrder());
			
			}
			  
		}
		
		$sql = "SELECT COUNT(*) AS total FROM $wpdb->posts AS p where p.`ID` >".(int)$MaxOrderNoInBatch."  and p.post_modified like '%".$MaxdateNoInBatch."%' ORDER BY p.post_modified,p.ID ASC";
		 $query = $wpdb->get_results($sql,ARRAY_A);

		if($query[0]['total']>0)
		{
			$Orders->setMaxOrderNoInBatch($MaxOrderNoInBatch); 
		}else
		{
				$Orders->setMaxOrderNoInBatch(""); 
		
		}
		
		return $this->response($Orders->getOrders());
	}  // getOrders
	
	function GetImage($username,$password,$data,$storeid=1,$others) {

		global $wpdb,$wp_query,$table_prefix;	
		#check for authorisation
		$status = $this->auth_user($username,$password);
		if($status!='0')
		{
		  return $status;
		}
		else
		{
			$Items = new WG_Items();
			$Items->setStatusCode('0');
			$Items->setStatusMessage('All Ok');		
		}
		
		$version = $this->getVersion();
		
		$requestArray = $data;

		if (!is_array($requestArray)) {
				$Items->setStatusCode('9997');
				$Items->setStatusMessage('Unknown request or request not in proper format');				
				return $this->response($Items->getItems());
			 }

		if (count($requestArray) == 0) {
				$Items->setStatusCode('9996');
				$Items->setStatusMessage('REQUEST tag(s) doesnt have correct input format');
				return $this->response($Items->getItems());
		}
		 $itemsCount = 0;
		 $itemsProcessed = 0;
		
		 // Go throught items
		 $itemsCount = 0;
		 $_err_message_arr = Array();
		
		 
		foreach($requestArray as $kv=>$vItem)//request
		{
		        $status ="Success";
				$productID = $vItem['ItemID'];
				
		$image_types = array(
		 "bmp" => "'image/bmp'",
         "gif" => "'image/gif'",
         "ief" => "'image/ief'",
         "jpeg" => "'image/jpeg'",
         "jpg" => "'image/jpeg'",
         "jpe" => "'image/jpeg'",
         "png" => "'image/png'",
         "tiff" => "'image/tiff'",
         "tif" => "'image/tif'",
         "djvu" => "'image/vnd.djvu'",
         "djv" => "'image/vnd.djvu'",
         "wbmp" => "'image/vnd.wap.wbmp'",
         "ras" => "'image/x-cmu-raster'",
         "pnm" => "'image/x-portable-anymap'",
         "pbm" => "'image/x-portable-bitmap'",
         "pgm" => "'image/x-portable-graymap'",
         "ppm" => "'image/x-portable-pixmap'",
         "rgb" => "'image/x-rgb'",
         "xbm" => "'image/x-xbitmap'",
         "xpm" => "'image/x-xpixmap'",
         "xwd" => "'image/x-windowdump'");
			$image_types = implode(",",$image_types);	
				
				$version = $this->getVersion();
				if($version > '3.7.8')
				{
				$sql = "SELECT SQL_CALC_FOUND_ROWS  posts.* FROM ".$table_prefix."posts as posts  WHERE posts.post_parent = '".$productID."' AND posts.post_type ='attachment' and posts.post_mime_type in (".$image_types.")";
					
				}
				else
				{
					$sql = "SELECT DISTINCT * FROM `".WPSC_TABLE_PRODUCT_IMAGES."` AS `products` WHERE `products`.`product_id`='".$productID."'";
					
				}

				$product_list = $wpdb->get_results($sql,ARRAY_A);
				if($product_list[0]['guid'])
				{
				$responseArray = array();
				$responseArray['ItemID']=	$productID;
				$responseArray['Image']	=	base64_encode(file_get_contents($product_list[0]['guid']));
				
				
				$Items->setItems($responseArray);
				break;
				}else{
				break;
				}
				
				
				
		} //End of Items foreach loop
		return $this->response($Items->getItems());
	
	}
	function addItemImage($itemid,$image,$image2,$storeid=1) {

		global $wpdb; 	
		require ( ABSPATH . 'wp-admin/includes/image.php' );
		$uploads	=	wp_upload_dir();
		
		define('DIR_IMAGE_FOR_UPLOAD', $uploads['path'].'/');
		//echo DIR_IMAGE_FOR_UPLOAD;
		chmod(DIR_IMAGE_FOR_UPLOAD, 0777);
		//$url = 'http://'.$_SERVER['HTTP_HOST'].'/'.substr($_SERVER['SCRIPT_NAME'], 1, strpos(substr($_SERVER['SCRIPT_NAME'],1), '/')).'/?wpsc-product=';
		$url = 'http://'.$_SERVER['HTTP_HOST'].'/'.substr($_SERVER['SCRIPT_NAME'], 1, strpos(substr($_SERVER['SCRIPT_NAME'],1), '/')).'/?p=';
		
		//$Items = new WG_Items();
		global $version;
		
		$timestamp_valu	=	time();
		
		$image_name = $timestamp_valu.'.jpg';
		if($image2) {
			$image_name2 = $timestamp_valu.'1'.'.jpg';
			
			$str2	=	base64_decode($image2);
		}
		//Base 64 encoded string $image
		$str	=	base64_decode($image);
		
	
		
		if(substr(decoct(fileperms(DIR_IMAGE_FOR_UPLOAD)),2) == '777') {
		
			$fp = fopen(DIR_IMAGE_FOR_UPLOAD.$image_name, 'w+');
		
			fwrite($fp, $str);
			fclose($fp);
			if($image2) {
				$fp2 = fopen(DIR_IMAGE_FOR_UPLOAD.$image_name2, 'w+');
		
				fwrite($fp2, $str2);
				fclose($fp2);
				
				$attachment2 = array(
				'post_mime_type' => 'image/jpeg',
				'guid' => $url.$timestamp_valu.'1',
				'post_parent' => $itemid,
				'post_title' => $timestamp_valu.'1',
				'post_content' => 'Product image post !!!',
				'post_type' => "attachment",
				'post_status' => 'inherit'		
				);
			
			
				// Save the data
				$postID2 = wp_insert_post($attachment2, $file, $itemid);
			}
			
			#create the item's image
			// Construct the attachment array
			$attachment = array(
				'post_mime_type' => 'image/jpeg',
				'guid' => $url.$timestamp_valu,
				'post_parent' => $itemid,
				'post_title' => $timestamp_valu,
				'post_content' => 'Product image post !!!',
				'post_type' => "attachment",
				'post_status' => 'inherit'		
			);
			
			
			// Save the data
			$postID = wp_insert_post($attachment, $file, $itemid);
			
			
			
			
			$time = current_time('mysql');
			$y = substr( $time, 0, 4 );
			$m = substr( $time, 5, 2 );
			$subdir = "$y/$m";

			add_post_meta($postID,'_wp_attached_file',$subdir.'/'.$image_name);	
                        add_post_meta($itemid,'_thumbnail_id',$postID);
				if($image2) {
				
					add_post_meta($postID2,'_wp_attached_file',$subdir.'/'.$image_name2);	
                        add_post_meta($itemid,'_product_image_gallery',$postID2);
						
						$file2=DIR_IMAGE_FOR_UPLOAD.$image_name2;
						wp_generate_attachment_metadata($postID2,$file2); 
			
						get_post_meta($postID2+1, '_wpsc_selected_image_size', true );
				}
			
			$file=DIR_IMAGE_FOR_UPLOAD.$image_name;
			wp_generate_attachment_metadata($postID,$file); 
			
			get_post_meta($postID+1, '_wpsc_selected_image_size', true );
			
			
			
			$image_node_array = array();
			$ImagePath	=	'http://'.$_SERVER['HTTP_HOST'].'/'.substr($_SERVER['SCRIPT_NAME'], 1, strpos(substr($_SERVER['SCRIPT_NAME'],1), '/')).'/wp-content/uploads'.$subdir.'/'.$image_name;
			
			$image_node_array['ItemImages']=array('ItemID'=>$itemid, 'ItemImageID'=>$itemid, 'ItemImageFileName'=>$image_name, 'ItemImageUrl'=>$ImagePath);

			$image_node_array2 = array();
			$ImagePath2	=	'http://'.$_SERVER['HTTP_HOST'].'/'.substr($_SERVER['SCRIPT_NAME'], 1, strpos(substr($_SERVER['SCRIPT_NAME'],1), '/')).'/wp-content/uploads'.$subdir.'/'.$image_name2;
			
			$image_node_array2['ItemImages']=array('ItemID'=>$itemid, 'ItemImageID'=>$itemid, 'ItemImageFileName'=>$image_name2, 'ItemImageUrl'=>$ImagePath2);
########################################################################################

$imagedetail=getimagesize($file);

	$attachment_id = $postID;
	$width =$imagedetail[0] ;
	$height =$imagedetail[1];
	$intermediate_size = '';

	if ( (($width >= 10) && ($height >= 10)) && (($width <= 1024) && ($height <= 1024)) ) {

		$intermediate_size = "wpsc-{$width}x{$height}";
		$generate_thumbnail = true;
	} else {
		$generate_thumbnail = false;
	}

	// If the attachment ID is greater than 0, and the width and height is greater than or equal to 10, and less than or equal to 1024
	if ( ($attachment_id > 0) && ($intermediate_size != '') ) {
		// Get all the required information about the attachment
		$uploads = wp_upload_dir();

		$image_meta = get_post_meta( $attachment_id, '' );
		$file_path = get_attached_file( $attachment_id );
                
                wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $file_path ) );

     }
$imagedetail2=getimagesize($file2);

	$attachment_id2 = $postID2;
	$width2 =$imagedetail2[0] ;
	$height2 =$imagedetail2[1];
	$intermediate_size2 = '';

	if ( (($width2 >= 10) && ($height2 >= 10)) && (($width2 <= 1024) && ($height2 <= 1024)) ) {

		$intermediate_size2 = "wpsc-{$width2}x{$height2}";
		$generate_thumbnail2 = true;
	} else {
		$generate_thumbnail2 = false;
	}

	// If the attachment ID is greater than 0, and the width and height is greater than or equal to 10, and less than or equal to 1024
	if ( ($attachment_id2 > 0) && ($intermediate_size2 != '') ) {
		// Get all the required information about the attachment
		$uploads2 = wp_upload_dir();

		$image_meta = get_post_meta( $attachment_id2, '' );
		$file_path2 = get_attached_file( $attachment_id2 );
                
                wp_update_attachment_metadata( $attachment_id2, wp_generate_attachment_metadata( $attachment_id2, $file_path2) );

     }


###########################################################################################

			return true;
			
		} else {
		
		    return false;

		
		}
		
		return $this->response($Items->getItems());
	}
	
#
	# Function to add the product in the store which found in QB
	#
	function addProduct($username,$password,$data)
	{	
		
		global $wpdb; 	
		global $version;
		#check for authorisation
		$status = $this->auth_user($username,$password);
		if($status!='0')
		{
		  return $status;
		}
		else
		{
			$Items = new WG_Items();
			$Items->setStatusCode('0');
			$Items->setStatusMessage('All Ok');		
		}
		
	
		$requestArray = $data;
		if (!is_array($requestArray)) {
				$Items->setStatusCode('9997');
				$Items->setStatusMessage('Unknown request or request not in proper format');				
				return $this->response($Items->getItems());
			 }

		if (count($requestArray) == 0) {
				$Items->setStatusCode('9996');
				$Items->setStatusMessage('REQUEST tag(s) doesnt have correct input format');
				return $this->response($Items->getItems());
		}
		 $itemsCount = 0;
		 $itemsProcessed = 0;
		
		 // Go throught items
		 $itemsCount = 0;
		 $_err_message_arr = Array();
		
		$user = wp_authenticate_username_password('WP_User', $username, $password);
		$userID = $user->data->ID; 
		
		$categories_arr = get_terms( 'product_cat', 'orderby=name&hide_empty=0' );
		$Item = new WG_Item();
		
		foreach($requestArray as $kv=>$vItem)//request
		{
				$Item = new WG_Item();
				$Item->setStatus("Success");
				
				$itemsCount++;
				$productcode=htmlentities($vItem['ItemCode']);
				$product=htmlentities($vItem['ItemName']);
				$descr=$vItem['ItemDesc'];
				$free_shipping=$vItem['FreeShipping'];
				$free_tax=$vItem['TaxExempt'];
				$tax_id=$vItem['TaxID'];
				$item_match=$vItem['ItemMatchBy'];
				$manufacturerid=$vItem['ManufacturerID'];
				$avail_qty=$vItem['Quantity'];
				$price=$vItem['ListPrice'];
				$weight=$vItem['Weight'];
				unset($categoryid);
				
				if(is_array($vItem['Categories']))
				{
					$arrayCategories=$vItem['Categories'];
					$categoryid = array();
					foreach($arrayCategories as $k3=>$vCategories)//Categories
					{ 
						if(isset($vCategories['CategoryId'])&& $vCategories['CategoryId']!='')
						{ 
							$taxnomyDetail = get_term($vCategories['CategoryId'],'product_cat');
							$categoryid[] =  $taxnomyDetail->term_taxonomy_id;
						}
					}
					
				}
				
					$now = time();
					$lock = "$now:$userID";
					
					if($avail_qty > 0){
						$stock_status = 'instock';
					}else{
						$stock_status = 'outofstock' ;
					}

				$sql = "SELECT id,post_title FROM $wpdb->posts p WHERE post_type = 'product' AND post_title='".addslashes($product)."'";
				$product_list = $wpdb->get_results($sql);	
				//print_r($product_list); die;
				$product_ID=$product_list[0]->id;
				if($vItem['ItemStatus']=='1')
				{
				$item_status='publish';
				}
				else
				{
				$item_status='draft';
				}
				
				if($product_ID == "")
				{
				
				$date = date('Y-m-d H:i:s');
				$post_data['post_title'] = $product;
				$post_data['post_content'] = $descr;
				//$post_data['post_date'] = $date;
				//$post_data['post_modified'] = $date;
				$post_data['original_post_status'] = 'auto-draft';
				$post_data['post_status'] = $item_status;
				$post_data['post_type'] = 'product';
				$post_data['product-type'] = 'simple';
				$post_data['post_author'] = $userID;
				$post_data['post_name'] = ''; 
				$post_data['guid'] = '';
				$post_data['tax_input']['product_cat'] =$categoryid;
				
				$product_ID = wp_insert_post($post_data, $wp_error = false);
				
				//add_meta( $product_ID );
				add_post_meta( $product_ID, '_edit_last', $userID );
				
				$attributes = (array) maybe_unserialize(get_post_meta($product_ID, '_product_attributes', false));
				
				if(sizeof($vItem['ItemVariants'])>0)
				{
					$post_meta = array(
				'_edit_lock' => $lock,
				'_sku' => $productcode,
				'_regular_price' =>$price,
				#'_sale_price' =>  $price,
				'_price' => $price,
				'_tax_status' => '',
				'_tax_class' => $tax_id,
				'_visibility' => $vItem['ItemVisibility'],
				'_purchase_note' => '',
				'_featured' => 'no' ,
				'_weight' => $weight,
				'_length' => '',
				'_width' => '',
				'_height' => '',
				'_product_attributes' => $attributes,
				'_downloadable' => 'no',
				'_virtual' => 'no',
				'_sale_price_dates_from' => $now,
				'_sale_price_dates_to' => '',
				'_backorders' => 'no',
				);
				}else{
				$post_meta = array(
				'_edit_lock' => $lock,
				'_sku' => $productcode,
				'_regular_price' =>$price,
				#'_sale_price' =>  $price,
				'_price' => $price,
				'_tax_status' => '',
				'_tax_class' => $tax_id,
				'_visibility' => $vItem['ItemVisibility'],
				'_purchase_note' => '',
				'_featured' => 'no' ,
				'_weight' => $weight,
				'_length' => '',
				'_width' => '',
				'_height' => '',
				'_product_attributes' => $attributes,
				'_downloadable' => 'no',
				'_virtual' => 'no',
				'_sale_price_dates_from' => $now,
				'_sale_price_dates_to' => '',
				'_stock' => $avail_qty,
				'_stock_status' => $stock_status,
				'_backorders' => 'no',
				'_manage_stock' => 'yes'	);
				}
				
				
				foreach($post_meta as $key=>$value){
					//update_post_meta( $product_ID, $key, $value );
					add_post_meta( $product_ID, $key, $value );
				}
				
				foreach($categoryid as $category){
					$wpdb->insert( $wpdb->term_relationships, array('object_id' => $product_ID, 'term_taxonomy_id' => $category) );
					
				}
				## product type is simple
				$pro_type = $wpdb->get_results("SELECT term_id from $wpdb->terms where name = 'simple' AND slug ='simple' ");

				
				If(!empty($pro_type[0]->term_id))
				{
					$wpdb->insert( $wpdb->term_relationships, array('object_id' => $product_ID, 'term_taxonomy_id' => $pro_type[0]->term_id) );
				}
				else
				{
				$wpdb->insert( $wpdb->term_relationships, array('object_id' => $product_ID, 'term_taxonomy_id' => '3') );
				}
				
				$Item->setStatus("Success");				
				$Item->setProductID($product_ID);
				$Item->setSku($productcode);
				$Item->setProductName($product);
				
				if(sizeof($vItem['ItemVariants'])>0)
				{
						wp_set_object_terms ($product_ID, 'variable', 'product_type');
						$save_array = array();
						$name="";
						foreach($vItem['ItemVariants'] as $kv=>$Itemvariants)
						{	
							
							$variation = array(
										'post_title' 	=> 'Product #' . $product_ID . ' Variation',
										'post_content' 	=> '',
										'post_status' 	=> 'publish',
										'post_author' 	=> get_current_user_id(),
										'post_parent' 	=> $product_ID,
										'post_type' 	=> 'product_variation'
									);
							
									$variation_id = wp_insert_post( $variation );
									
									update_post_meta($variation_id, '_sku', $Itemvariants['ItemCode'] );
									update_post_meta($variation_id, '_stock', $Itemvariants['Quantity'] );
									update_post_meta( $variation_id, '_price', $Itemvariants['UnitPrice'] );
									update_post_meta( $variation_id, '_regular_price', $Itemvariants['UnitPrice'] );
									update_post_meta( $variation_id, '_manage_stock','yes' );
									if($Itemvariants['Quantity']>0){
										update_post_meta( $variation_id, '_stock_status','instock' );
									}else{
										update_post_meta( $variation_id, '_stock_status','outofstock' );
									}
								
								
								$Variant = new WG_Variant();
								$Variant->setStatus('Success');
								$Variant->setVarientID($variation_id);
								$Variant->setVariantSku(htmlentities($Itemvariants['ItemCode']));
								//$Variant->setProductName(htmlentities($product));				
								$Item->setItemVariants($Variant->getVariant());
							
								$br=0;
								
								$all_options = '';

								$attributes = (array) maybe_unserialize(get_post_meta($product_ID, '_product_attributes', false));
								foreach($Itemvariants['ItemOptions'] as $k=>$optionsTag)
								{ 
									
									$value="";
									if($optionsTag['OptionName'] && $optionsTag['OptionName']!='')
									{
										wp_set_object_terms($product_ID, '', $optionsTag['OptionName']);									
									}
									$attribute_field_name = 'attribute_' . sanitize_title( $optionsTag['OptionName'] );
									update_post_meta( $variation_id, $attribute_field_name, $optionsTag['OptionValue'] );
									//foreach($attributes[0] as $k)			
									if($optionsTag['OptionValue'] && $optionsTag['OptionValue']!='')
									{	
											if(array_key_exists($optionsTag['OptionName'],$attributes[0]))
										{
											$option_val = explode("|",$attributes[0][$optionsTag['OptionName']]['value']);
											
											
											if(!in_array($optionsTag['OptionValue'],$option_val))
												{
												
													if($attributes[0][$optionsTag['OptionName']]['value'])
													{
														$attributes[0][$optionsTag['OptionName']]['value'] =$attributes[0][$optionsTag['OptionName']]['value']."|".$optionsTag['OptionValue'];
													}else
													{
														$attributes[0][$optionsTag['OptionName']]['value'] = $optionsTag['OptionValue'];
													}
												
												}	
																						
										}else
										{
											$thedata = array($optionsTag['OptionName']=>array(
														'name'=>$optionsTag['OptionName'],
														'value'=>$optionsTag['OptionValue'],
														'is_visible' => '1',
														'is_variation' => '1',
														'is_taxonomy' => '0'
														));		
									    	 
											$attributes[0] = array_merge($attributes[0], $thedata);
										}
										
																					
										
										
									}
									$name = $optionsTag['OptionName'];															
								$br++;	
								
								
							} 
							
							update_post_meta( $product_ID,'_product_attributes',$attributes[0]);
							
							$b++;
						}
					
				}
				update_post_meta( $product_ID, '_visibility', $vItem['ItemVisibility'] );
				update_post_meta( $product_ID, '_stock_status', 'instock');		
//               #Calling function for add image
				if($vItem['Image']) {
			
					$this->addItemImage($product_ID,$vItem['Image'],$vItem['Image2'],$storeid=1);
				}
				
				$Items->setItems($Item->getItem());
				}
				else
				{
				$post_id = intval( $product_ID );
		
		
				if(sizeof($vItem['ItemVariants'])>0)
				{
						wp_set_object_terms ($product_ID, 'variable', 'product_type');
						$save_array = array();
						$name="";
						foreach($vItem['ItemVariants'] as $kv=>$Itemvariants)
						{	
							
							$variation = array(
										'post_title' 	=> 'Product #' . $product_ID . ' Variation',
										'post_content' 	=> '',
										'post_status' 	=> 'publish',
										'post_author' 	=> get_current_user_id(),
										'post_parent' 	=> $product_ID,
										'post_type' 	=> 'product_variation'
									);
							
									$variation_id = wp_insert_post( $variation );
									
									update_post_meta($variation_id, '_sku', $Itemvariants['ItemCode'] );
									update_post_meta($variation_id, '_stock', $Itemvariants['Quantity'] );
									update_post_meta( $variation_id, '_price', $Itemvariants['UnitPrice'] );
									update_post_meta( $variation_id, '_regular_price', $Itemvariants['UnitPrice'] );
									update_post_meta( $variation_id, '_manage_stock','yes' );
									if($Itemvariants['Quantity']>0){
										update_post_meta( $variation_id, '_stock_status','instock' );
									}else{
										update_post_meta( $variation_id, '_stock_status','outofstock' );
									}
								
								
								$Variant = new WG_Variant();
								$Variant->setStatus('Success');
								$Variant->setVarientID($variation_id);
								$Variant->setVariantSku(htmlentities($Itemvariants['ItemCode']));			
								$Item->setItemVariants($Variant->getVariant());
							
								$br=0;
								
								$all_options = '';

								$attributes = (array) maybe_unserialize(get_post_meta($product_ID, '_product_attributes', false));
								foreach($Itemvariants['ItemOptions'] as $k=>$optionsTag)
								{ 
									
									$value="";
									if($optionsTag['OptionName'] && $optionsTag['OptionName']!='')
									{
										wp_set_object_terms($product_ID, '', $optionsTag['OptionName']);									
									}
									$attribute_field_name = 'attribute_' . sanitize_title( $optionsTag['OptionName'] );
									update_post_meta( $variation_id, $attribute_field_name, $optionsTag['OptionValue'] );
									//foreach($attributes[0] as $k)			
									if($optionsTag['OptionValue'] && $optionsTag['OptionValue']!='')
									{	
											if(array_key_exists($optionsTag['OptionName'],$attributes[0]))
										{
											$option_val = explode("|",$attributes[0][$optionsTag['OptionName']]['value']);
											
											
											if(!in_array($optionsTag['OptionValue'],$option_val))
												{
												
													if($attributes[0][$optionsTag['OptionName']]['value'])
													{
														$attributes[0][$optionsTag['OptionName']]['value'] =$attributes[0][$optionsTag['OptionName']]['value']."|".$optionsTag['OptionValue'];
													}else
													{
														$attributes[0][$optionsTag['OptionName']]['value'] = $optionsTag['OptionValue'];
													}
												
												}
																						
										}else
										{
											$thedata = array($optionsTag['OptionName']=>array(
														'name'=>$optionsTag['OptionName'],
														'value'=>$optionsTag['OptionValue'],
														'is_visible' => '1',
														'is_variation' => '1',
														'is_taxonomy' => '0'
														));		
									    	 
											$attributes[0] = array_merge($attributes[0], $thedata);
										}
																				
									}
									$name = $optionsTag['OptionName'];															
								$br++;	
								
							} 
							
							update_post_meta( $product_ID,'_product_attributes',$attributes[0]);
							
							$b++;
						}
					
				}
				update_post_meta( $product_ID, '_visibility', $vItem['ItemVisibility'] );
				update_post_meta( $product_ID, '_stock_status', 'instock');		
//               #Calling function for add image
				if($vItem['Image']) {
			
					$this->addItemImage($product_ID,$vItem['Image'],$vItem['Image2'],$storeid=1);
				}
				
				$Items->setItems($Item->getItem());	
				}
				
				
	
			} //End of Items foreach loop
			
		return $this->response($Items->getItems()); 
	
	} //addProduct
	#
	# Update Orders shipping status method
	# Will update Order Notes and tracking number of  order
	# Input parameter Username,Password, array (OrderID,ShippedVia,ServiceUsed,TrackingNumber,Notes)
	# Wordpress does not support status update emails. So email for status updation would not be sent from here.
	#
	function UpdateOrdersShippingStatus($username,$password,$data,$statustype,$others)
	{
		ini_set('display_errors' , 'Off');
		global  $wpdb,$download_order_number; 
		$woocommerce_email = new WC_Emails();
		#check for authorisation
		$status = $this->auth_user($username,$password,$xmlResponse,$root);
		$plugins = get_option('active_plugins');
		$sequential_order_numbers_plugin = 'woocommerce-sequential-order-numbers/woocommerce-sequential-order-numbers.php';
		$sequential_order_numbers_plugin_pro = 'woocommerce-sequential-order-numbers-pro/woocommerce-sequential-order-numbers.php';
		if($status!='0')
		{ 
		  return $status;
		}
		
		$Orders = new WG_Orders();	
		
		$requestArray=$data;
		
		//$requestArray = json_decode($data,true);
		
		if (!is_array($requestArray))
		{
			$Orders->setStatusCode("9997");
			$Orders->setStatusMessage("Unknown request or request not in proper format");	
			return $this->response($Orders->getOrders());exit();				
		}
		if (count($requestArray) == 0)
		{
			$Orders->setStatusCode("9996");
			$Orders->setStatusMessage("REQUEST array(s) doesnt have correct input format");				
			return $this->response($Orders->getOrders());exit();
		}
	
		if (count($requestArray) == 0) $no_orders = true; else $no_orders = false;
		
		$Orders->setStatusCode($no_orders?"1000":"0");
		$Orders->setStatusMessage($no_orders?"No new orders.":"All Ok");
			
		if ($no_orders){
			return $this->response($Orders->getOrders());
		}

		//$ordersNode = $xmlResponse->createTag("Orders", array(), '', $root);
		if($requestArray[0]['IsCreateRefund']==1){
			
				$orderrefund = $this->CreateOrderRefund($username, $password ,$requestArray);
				
				$Order = new WG_Order();
				$Order->setOrderID($orderrefund['OrderID']);
				$Order->setStatus($orderrefund['result']);
				$Order->setOrderNotes('');
				$Order->setLastModifiedDate($orderrefund['last_modfied_date']);
				$Order->setOrderStatus($orderrefund['OrderStatus']);
				$Orders->setOrders($Order->getOrder());	
			
		}else {
		
		foreach($requestArray as $k2=>$v)//request
		{
			$j=0;	
		 
			foreach($v as $k3=>$v3)
			{
			
				$order[$k3] = $v3;
			} 
			
			$update_note = $order['UpdateOrderNote'];
			if (preg_match('/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/si', $username)){
				$user = wp_authenticate_email_password('WP_User', $username, $password);	
			}else{
				$user = wp_authenticate_username_password('WP_User', $username, $password);	
			}
			$userID = $user->data->ID;
			$comment_author =  $user->data->display_name;
			$comment_author_email = $user->data->user_email;
			$comment_agent = $user->data->display_name;
			$comment_type = 'order_note';
	
			if($download_order_number==true){
				//$order_id =wc_sequential_order_numbers()->find_order_by_order_number($order['OrderID']);
				if(in_array($sequential_order_numbers_plugin_pro, $plugins )) {
						$order_id = wc_seq_order_number_pro()->find_order_by_order_number($order['OrderID']);
				}elseif(in_array($sequential_order_numbers_plugin, $plugins )){
						$order_id = wc_sequential_order_numbers()->find_order_by_order_number($order['OrderID']);
				}else{
					$order_id = wc_sequential_order_numbers()->find_order_by_order_number($order['OrderID']);
				}
			}else{
				$order_id =$order['OrderID'];
			}
		# Check for record Existence for the Order ID
			$query = "SELECT  p.ID FROM  $wpdb->posts AS p  WHERE p.ID =". $order_id ;
			$orderdata = $wpdb->get_results($query,ARRAY_A);
			$date = date('Y-m-d H:i:s');
			
			if($order)
			{ 
				
				if($update_note=="Y")
				{
				
					$execute = $wpdb->insert( $wpdb->comments, array('comment_post_ID' => $order_id, 'comment_author' => $comment_author, 'comment_author_email' => $comment_author_email, 'comment_date' => $date, 'comment_date_gmt' => $date, 'comment_content' =>$order['OrderNotes'] ,'comment_agent' => $comment_agent ,'comment_type' => $comment_type));
				$new_comment_id = $wpdb->insert_id;
				$wpdb->insert( $wpdb->commentmeta, array('comment_id' => $new_comment_id, 'meta_key' => 'is_customer_note', 'meta_value' => $meta_val));
				$result = 'Success'; 
				
				}
				else
				{
					$info = "\nOrder shipped ";
				
				if ($order['ShippedOn']!="")
				$info .= " on ". substr($order['ShippedOn'],0,10);
	
				if ($order['ServiceUsed']!="" )
				$info .= ". ".$order['ServiceUsed'];
				
				if ($order['TrackingNumber']!="")
				$info .= " Tracking Number is: ".$order['TrackingNumber'].".";
				if((!isset($note_val) && $note_val==""))
				{
					if ($order['OrderNotes']!="")
					{	
						$info .=" \n".$order['OrderNotes'];
						
					}
				}				
		
				if($order['IsNotifyCustomer']=='Y') 
					$meta_val = 1;
				else 	
					$meta_val = 0;
				$status_arr = array();
				
				$orderStatus = wc_get_order_statuses();
				foreach($orderStatus as $id=>$name)
				{	
					
					if($name==ucfirst($order['OrderStatus']))
					$set_status = $id;
				}
				
				$updateQuery = "UPDATE $wpdb->posts SET post_status  = '".$set_status."' WHERE ID  = ".$this->mySQLSafe($order['OrderID']).""; 
				
				$wpdb->query($updateQuery);

				//$orderStatusC = new WC_Order(  $order_id );
				//$orderStatusC->update_status($set_status);
				
				$execute = $wpdb->insert( $wpdb->comments, array('comment_post_ID' => $order_id, 'comment_author' => $comment_author, 'comment_author_email' => $comment_author_email, 'comment_date' => $date, 'comment_date_gmt' => $date, 'comment_content' =>$info ,'comment_agent' => $comment_agent ,'comment_type' => $comment_type));
				$new_comment_id = $wpdb->insert_id;
				
				$wpdb->insert( $wpdb->commentmeta, array('comment_id' => $new_comment_id, 'meta_key' => 'is_customer_note', 'meta_value' => $meta_val));
				
	
				$updatePostQuery = "UPDATE $wpdb->posts SET	post_modified  = '".$date."', post_modified_gmt = '".$date."'	WHERE ID   = ".$this->mySQLSafe($order_id).""; 
				
				$wpdb->query($updatePostQuery);
				
				$result = 'Success'; 
                       
					if($order['IsNotifyCustomer']=='Y' && $result == 'Success' && strtolower($order['OrderStatus'])=='completed')
					{
						$woocommerce_email->emails['WC_Email_Customer_Completed_Order']->trigger($order_id);
					}
					if($order['IsNotifyCustomer']=='Y' && $result == 'Success' && strtolower($order['OrderStatus'])=='cancelled')
					{
						
						$woocommerce_email->emails['WC_Email_Cancelled_Order']->trigger($order_id);
					}
				
				}
				
			}
			else
			{
				$result = 'Order not found';
			}

			$last_modfied_date = date("m-d-Y H:i:s",strtotime($date));
			unset($orderNote);
			$ordreNotesArr = $wpdb->get_results("SELECT comment_content FROM $wpdb->comments c WHERE c.comment_post_ID  = ".$order_id." order by c.comment_date  DESC ",ARRAY_A);
			
			foreach($ordreNotesArr as $k=>$Notes){
				$orderNote[] = $Notes['comment_content']; 
			}

			$Order = new WG_Order();
			$Order->setOrderID($order['OrderID']);
			$Order->setStatus($result);
			$Order->setOrderNotes($orderNote[0]);
			$Order->setLastModifiedDate($last_modfied_date);
			$Order->setOrderStatus($order['OrderStatus']);
			$Orders->setOrders($Order->getOrder());	
		
		$i++;
	   }
		}	   
		return $this->response($Orders->getOrders());
	}  //UpdateOrdersShippingStatus
	
		
	function UpdateOrdersStatusAcknowledge($username,$password,$data,$statustype)
	{ 
		global $wpdb;
		

		#check for authorisation
		$status = $this->auth_user($username,$password);
		if($status!='0')
		{ 
		  return $status;
		}
		
		$Orders = new WG_Orders();		
		$requestArray=$data;
		//$requestArray = json_decode($data,true);
		if (!is_array($requestArray))
		{
			$Orders->setStatusCode("9997");
			$Orders->setStatusMessage("Unknown request or request not in proper format");	
			return $this->response($Orders->getOrders());exit();				
		}
		if (count($requestArray) == 0)
		{
			$Orders->setStatusCode("9996");
			$Orders->setStatusMessage("REQUEST array(s) doesnt have correct input format");				
			return $this->response($Orders->getOrders());exit();
		}
	
		$Orders = new WG_Orders();		
		if (count($requestArray) == 0) $no_orders = true; else $no_orders = false;

		$Orders->setStatusCode($no_orders?"1000":"0");
		$Orders->setStatusMessage($no_orders?"No new orders.":"All Ok");	
		
		if ($no_orders){
			
			return $this->response($Orders->getOrders());
		}
	
		foreach($requestArray as $k2=>$v)//request
		{
					foreach($v as $k3=>$v3)
					{
				
						$order[$k3] = $v3;
					}
					
		# Check for record Existence for the Order ID
			
			$query = "SELECT  p.ID FROM  $wpdb->posts AS p  WHERE p.ID =". $order['OrderID'] ;
			$orderdata = $wpdb->get_results($query,ARRAY_A);
			$date = date('Y-m-d H:i:s');
			
			if($orderdata)
			{ 
				$status_arr = array();
				$orderStatus = wc_get_order_statuses();
				
				foreach($orderStatus as $id=>$name)
				{	
					
					if($name==ucwords($order['OrderStatus'])) //changes due to 2.2.10 version
					$set_status = $id;
				}
				
				$updateQuery = "UPDATE $wpdb->posts SET post_status  = '".$set_status."' WHERE ID  = ".$this->mySQLSafe($order['OrderID']).""; 
			    $wpdb->query($updateQuery);
				
				$updatePostQuery = "UPDATE $wpdb->posts SET	post_modified  = '".$date."', post_modified_gmt = '".$date."'	WHERE ID   = ".$this->mySQLSafe($order['OrderID']).""; 
				//die("hi");
				$wpdb->query($updatePostQuery);
				$result = 'Success';
			}
			else
			{
				$result = 'Order not found';
			}

			$last_modfied_date = date("m-d-Y H:i:s",strtotime($date));
			
			$ordreNotesArr = $wpdb->get_results("SELECT comment_content 	FROM $wpdb->comments c WHERE c.comment_post_ID  = ".$order['OrderID']." order by c.comment_date  DESC ",ARRAY_A);
			foreach($ordreNotesArr as $k=>$Notes){
				$orderNote[] = $Notes['comment_content']; 
			}

			$Order = new WG_Order();
			$Order->setOrderID($order['OrderID']);
			$Order->setStatus($result);
			$Order->setOrderNotes($orderNote[0]);
			$Order->setLastModifiedDate($last_modfied_date);
			$Order->setOrderStatus($order['OrderStatus']);
			$Orders->setOrders($Order->getOrder());	
	
		
		$i++;
	   }	 
		return $this->response($Orders->getOrders());
	}
	
		function getCustomersNew($username,$password,$datefrom,$customerid,$limit,$storeid=1,$others)
		{ 
			
			global $wpdb;
			$datefrom =$datefrom ?$datefrom:0;
			
			$status = $this->auth_user($username,$password);
			if($status !='0')
			{
				return $status;
			}
			$Customers = new WG_Customers();
			
			
			$start_no=0;
			//$user_arr = array();
			$users = $wpdb->get_results( "SELECT user_id, user_id AS ID, meta_value FROM $wpdb->users, $wpdb->usermeta WHERE {$wpdb->users}.user_status = '0' AND {$wpdb->users}.ID = {$wpdb->usermeta}.user_id AND meta_key = 'wp_capabilities' ORDER BY {$wpdb->usermeta}.user_id");
			foreach($users  as $k=>$userdata){
				$role_arr =  unserialize($userdata->meta_value);
				foreach($role_arr as $k2=>$value){
					if($k2 == 'customer'){
						$user_arr[] = $userdata->user_id;
					}
					if($k2 == 'administrator'){
						$admin_arr[] = $userdata->user_id;
					}
				}
			}
			$customer_count= count($user_arr);
			
//			
			$no_customer =false;
			if($customer_count<0)
			{
				$no_customer = true;
			}
			$Customers->setStatusCode($no_customer?"0":"0");
			$Customers->setStatusMessage($no_customer?"No Customer returned":"Total Customer:".$customer_count);
			$Customers->setTotalRecordFound($customer_count?$customer_count:'0');
			$Customers->setTotalRecordSent($customer_count?$customer_count:'0');
			
		//	$admin_arr[]= '4';
			
			foreach($user_arr as $uid){
					$ulist2 []= "'".$uid."'";
			
			}
			$ulist = implode(",",$ulist2);

 
			$customersArray = $wpdb->get_results(" SELECT u.* FROM $wpdb->users u WHERE u.ID IN (".$ulist.")order by u.ID ASC LIMIT ".$start_no.",".$limit." ");
	
		
			$customersMetadata = array();
			foreach($customersArray as $customer)
			{
				ini_set('display_errors' , 'Off');
				$CustomerObj = new WG_Customer();
				$CustomerObj->setCustomerId($customer->ID);
				$customersMetaArray = $wpdb->get_results(" SELECT um.* FROM $wpdb->usermeta um WHERE um.user_id = ".$customer->ID." ");
				
				foreach($customersMetaArray as $customersMeta){
					
					$customersMetadata[$customersMeta->meta_key] = $customersMeta->meta_value;
				}
				
				$CustomerObj->setFirstName($customersMetadata['first_name']);
				$CustomerObj->setMiddleName('');
				$CustomerObj->setLastName($customersMetadata['last_name']);
				$CustomerObj->setcompany($customersMetadata["billing_company"]);
				$CustomerObj->setemail($customer->user_email);
				$CustomerObj->setAddress1($customersMetadata['billing_address_1']);
				$CustomerObj->setAddress2($customersMetadata['billing_address_2']?$customersMetadata['billing_address_2']:"");
				$CustomerObj->setCity($customersMetadata["billing_city"]);
				$CustomerObj->setState($customersMetadata["billing_state"]);
				$CustomerObj->setZip($customersMetadata["billing_postcode"]);
				$CustomerObj->setCountry($customersMetadata["billing_country"]);
				$CustomerObj->setPhone($customersMetadata["billing_phone"]);
				if(!isset($customer->user_registered) || $customer->user_registered=='') {
				$customer->user_registered = '2007-01-01 00:00:00' ;
				}
				$CustomerObj->setCreatedAt($customer->user_registered);
				$CustomerObj->setUpdatedAt('2007-01-01 00:00:00');
				$CustomerObj->setsubscribedToEmail("false");
				
				$Customers->setCustomer($CustomerObj->getCustomer());
				unset($state,$country);
		
			}
		
			return $this->response($Customers->getCustomers());
		}
                
        ##FUNCTION TO TRANSFER CUTSOMER FROM QB TO ONLINE STORE
	function addCustomers($username,$password,$data,$storeid=1,$others='')
	{
            global $wpdb;
            $status = $this->auth_user($username,$password);
            if($status !='0')
            {
                return $status;
            }

            $Customers = new WG_Customers();
            $Customers->setStatusCode('0');
            $Customers->setStatusMessage('All Ok');
            $customerData = $data;
            if (!is_array($customerData)) {
                $Customers->setStatusCode('9997');
                $Customers->setStatusMessage('Unknown request or request not in proper format');
                return $this->response($Customers->getCustomers());
            }

            if (count($customerData) == 0) {
                $Customers->setStatusCode('9996');
                $Customers->setStatusMessage('REQUEST tag(s) doesnt have correct input format');
                return $this->response($Customers->getCustomers());
            }
            foreach($customerData as $k=>$vCustomer) {
				$email		=	$vCustomer['Email'];
				$CustomerId		=	$vCustomer['CustomerId'];
				$firstName		=	$vCustomer['FirstName'];
				//$middlename		=	$vCustomer[''];
				$lastName		=	$vCustomer['LastName'];
				$company		=	$vCustomer['Company'];
				$address1		=	$vCustomer['Address1'];
				$address2		=	$vCustomer['Address2'];
				$city               =	$vCustomer['City'];
				if($vCustomer['StateCode']=='') {
					 $state		=	$vCustomer['States'];
				}else {
					$state		=	$vCustomer['StateCode'];
				}
				$postCode		=	$vCustomer['Zip'];
				$countryCode	=	$vCustomer['CountryCode'];
				$phone		=	$vCustomer['Phone'];
				$membershipid	=	$vCustomer['CustomerGroup'];
				$password		=	wp_generate_password();
				$userName           =       $firstName;//$email;
				
				
				$id = wc_create_new_customer($email, '', $password);
				if ( is_wp_error( $id ) ) {
					//echo "Error : ".$id->get_error_message();
					$Customer = new WG_Customer();
					$Customer->setStatus($id->get_error_message());
					$Customer->setCustomerId('');
					$Customer->setFirstName($firstName);
					$Customer->setLastName($lastName);
					$Customer->setemail($email);
					$Customer->setCompany($company);
					$Customers->setCustomer($Customer->getCustomer());
				}
				else
				{
					update_user_meta($id,'first_name', wc_clean($firstName));
					update_user_meta($id,'last_name', wc_clean($lastName));

                $billingAddress = array('first_name' 	=> $firstName,
                                    'last_name' 	=> $lastName,
                                    'company' 		=> $company,
                                    'address_1' 	=> $address1,
                                    'address_2' 	=> $address2,
                                    'city' 			=> $city,
                                    'state' 		=> $state,
                                    'postcode' 		=> $postCode,
                                    'country' 		=> $countryCode,
                                    'email' 		=> $email,
                                    'phone' 		=> $phone
                                );
                //$shippingAddress = $billingAddress;
                ##PREPATER DATA FOR SHIPPING
                $billing_address = apply_filters( 'woocommerce_api_customer_billing_address', array(
                                                  'first_name',
                                                  'last_name',
                                                  'company',
                                                  'address_1',
                                                  'address_2',
                                                  'city',
                                                  'state',
                                                  'postcode',
                                                  'country',
                                                  'email',
                                                  'phone',
                                          ) );

                foreach ( $billing_address as $address ) {
                    if ( isset( $billingAddress[ $address ] ) ) {
                        update_user_meta( $id, 'billing_' . $address, wc_clean( $billingAddress[ $address ] ) );
                    }
                }


                $Customer = new WG_Customer();
                $Customer->setCustomerId($id);
                $Customer->setStatus('Success');
                $Customer->setFirstName($firstName);
                $Customer->setMiddleName("");
                $Customer->setLastName($lastName);
                $Customer->setCustomerGroup($group);
                $Customer->setemail($email);
                $Customer->setCompany($company);
                $Customer->setAddress1($address1);
                $Customer->setAddress2($address2);
                $Customer->setCity($city);
                $Customer->setState($state);
                $Customer->setZip($postCode);
                $Customer->setCountry($countryCode);
                $Customer->setPhone($phone);

                $Customers->setCustomer($Customer->getCustomer());
            }
          }
          return $this->response($Customers->getCustomers());
	}//END OF addCustomer
		
	#
	# Update Orders via status type method
	# Will update Order Notes and tracking number of  order
	# Input parameter Username,Password, array (OrderID,ShippedOn,ShippedVia,ServiceUsed,TrackingNumber)
	#
	function AutoSyncOrder($username,$password,$data,$statustype,$storeid,$others)
	{ 
		//ini_set('display_errors' , 'On');
		global $wpdb;
		$status = $this->auth_user($username,$password);
		if($status !='0')
		{
			return $status;
		}
		
		$Orders = new WG_Orders();		
		//$response_array = json_decode($Orders_json_array,true);
		$response_array = $data; 
		
		if (!is_array($response_array))
		{
			$Orders->setStatusCode("9997");
			$Orders->setStatusMessage("Unknown request or request not in proper format");	
			return $this->response($Orders->getOrders());exit();				
		}
		if (count($response_array) == 0)
		{
			$Orders->setStatusCode("9996");
			$Orders->setStatusMessage("REQUEST array(s) doesnt have correct input format");				
			return $this->response($Orders->getOrders());exit();
		}
		if(count($response_array) == 0) {
			$no_orders = true;
		}else {
			$no_orders = false;
		}
		$Orders->setStatusCode($no_orders?"1000":"0");
		$Orders->setStatusMessage($no_orders?"No new orders.":"All Ok");
		if ($no_orders){
			return json_encode($response_array);
		}

		$i=0;	
		
		
		unset($order_wg);
			
		foreach($response_array as $k=>$v)//request
		{
					
				if(isset($order_wg))
				{
					unset($order_wg);
				}
				foreach($v as $k1=>$v1)
				{
					$order_wg[$k1] = $v1;
				}
			//$order_id = $order_wg['Orderno'];	
			$order_id = $order_wg['OrderID'];	
			# Xcart does not send mail for order notes/ customer comment updation.
			//$order_wg['IsNotifyCustomer'] = 'Y';
			if($order_wg['IsNotifyCustomer']=='Y') 
					$customer_notified = 1;
				else 	
					$customer_notified = 0;
			
			
			
			switch ($statustype)
			{
				
			case 'paymentUpdate':
			### Xcart does not support Payment creation
			$isupdated = "error";
			
			
			break;

			case 'statusUpdate':
				break;
			case 'notesUpdate':
			$isupdated = "error";
			if(preg_match('/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/si', $username)){
				$user = wp_authenticate_email_password('WP_User', $username, $password);	
			}else{
				$user = wp_authenticate_username_password('WP_User', $username, $password);	
			}
			//print_r($user->data);
			$userID = $user->data->ID;
			
			$comment_author =  $user->data->display_name;
			$comment_author_email = $user->data->user_email;
			$comment_agent = $user->data->display_name;
			$comment_type = 'order_note';
			$date = date('Y-m-d H:i:s');
			
			$execute = $wpdb->insert( $wpdb->comments, array('comment_post_ID' => $order_id, 'comment_author' => $comment_author, 'comment_author_email' => $comment_author_email, 'comment_date' => $date, 'comment_date_gmt' => $date, 'comment_content' =>$order_wg['OrderNotes'] ,'comment_agent' => $comment_agent ,'comment_type' => $comment_type));
				$new_comment_id = $wpdb->insert_id;
				$wpdb->insert( $wpdb->commentmeta, array('comment_id' => $new_comment_id, 'meta_key' => 'is_customer_note', 'meta_value' => $customer_notified));
				

				$updatePostQuery = "UPDATE $wpdb->posts SET	post_modified  = '".$date."', post_modified_gmt = '".$date."'	WHERE ID   = ".$this->mySQLSafe($order_id).""; 
				//die("hi");
				$wpdb->query($updatePostQuery);
				if($order_wg['IsNotifyCustomer']=='Y'){
					$args= array('order_id' => $order_id , 'customer_note' => $order_wg['OrderNotes']);
					$woocommerce_email = new WC_Email();
					$woocommerce_email->customer_note($args);
				}
				
				
				$isupdated = 'success';
			
			break;
			
			case 'shipmentUpdate':
			### Xcart does not support Shippment creation
			$isupdated = "error";
			
				break;			
			}
			
			$last_modfied_date = date("m-d-Y H:i:s",strtotime($date));
			
			$ordreNotesArr = $wpdb->get_results("SELECT comment_content 	FROM $wpdb->comments c WHERE c.comment_post_ID  = ".$order_id." order by c.comment_date  DESC ",ARRAY_A);
			foreach($ordreNotesArr as $k=>$Notes){
			$orderNote[] = $Notes['comment_content']; 
			}
			
			$query = "SELECT t.term_id,t.slug,t.name, p.ID FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON tt.term_id = t.term_id INNER JOIN $wpdb->term_relationships AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id INNER JOIN $wpdb->posts AS p ON p.ID = tr.object_id  WHERE tt.taxonomy IN ('shop_order_status')  AND p.ID =". $order_id ;
			$orderdata = $wpdb->get_results($query,ARRAY_A);
			
			$Order = new WG_Order();
			$Order->setOrderID($order_id);
			$Order->setStatus($isupdated);
			$Order->setLastModifiedDate($last_modfied_date);
			$Order->setOrderNotes($orderNote[0]);
			$Order->setOrderStatus($orderdata[0]['slug']);
			//$Order->setOrderStatus($order_wg['OrderStatus']?$order_wg['OrderStatus']:$orderStatus[$status1[status]]);
			$Orders->setOrders($Order->getOrder());	
		
	   }
	  
	return $this->response($Orders->getOrders());
	}
	###########################################################################
	#
	# General utility functions
	#
	
	# function to escape html entity characters
	function parseSpecCharsA($arr){
	   foreach($arr as $k=>$v){
		 //$arr[$k] = htmlspecialchars($v, ENT_NOQUOTES);
			 $arr[$k] = addslashes(htmlentities($v, ENT_QUOTES));
	   }
	   return $arr;
	}
	///create order refund
	
	function CreateOrderRefund($username, $password ,$requestArray){
	
		global  $wpdb; 
			foreach($requestArray as $k2=>$v)//request
			{
				foreach($v as $k3=>$v3)
				{
					$order[$k3] = $v3;
				} 
			$Orders = new WG_Orders();	
			/* $update_note = $order['UpdateOrderNote'];
			$user = wp_authenticate_username_password('WP_User', $username, $password);
			$userID = $user->data->ID;
			$comment_author =  $user->data->display_name;
			$comment_author_email = $user->data->user_email;
			$comment_agent = $user->data->display_name;
			$comment_type = 'order_note';
			$date = date('Y-m-d H:i:s');
			if($order['IsNotifyCustomer']=='Y') 
					$meta_val = 1;
				else 	
					$meta_val = 0;
			 */
			/* $execute = $wpdb->insert( $wpdb->comments, array('comment_post_ID' => $order['OrderID'], 'comment_author' => $comment_author, 'comment_author_email' => $comment_author_email, 'comment_date' => $date, 'comment_date_gmt' => $date, 'comment_content' =>$order['OrderNotes'] ,'comment_agent' => $comment_agent ,'comment_type' => $comment_type));
			$new_comment_id = $wpdb->insert_id;
			$wpdb->insert( $wpdb->commentmeta, array('comment_id' => $new_comment_id, 'meta_key' => 'is_customer_note', 'meta_value' => $meta_val));
			 */
			$orderStatus = wc_get_order_statuses();
				foreach($orderStatus as $id=>$name)
				{	
					
					if($name==ucfirst($order['OrderStatus']))
					$set_status = $id;
				}
				
			
			$updateQuery = "UPDATE $wpdb->posts SET post_status  = '".$set_status."',post_modified  = '".$date."', post_modified_gmt = '".$date."' WHERE ID  = ".$this->mySQLSafe($order['OrderID']).""; 
			$wpdb->query($updateQuery);
			
			$retitle = sprintf( __( 'Refund &ndash; %s', 'woocommerce' ), strftime( _x( '%b %d, %Y @ %I:%M %p', 'Order date parsed by strftime', 'woocommerce' ) ) );
			$postname = "refund".date("F-j-Y-g:i-a");
			
			$post_data['post_title'] = $retitle;
			$post_data['post_content'] = "";
			$post_data['post_date'] = $date;
			$post_data['post_modified'] = $date;
			$post_data['post_date_gmt'] = $date;
			$post_data['post_modified_gmt'] = $date;
			$post_data['post_status'] = 'wc-completed';
			$post_data['comment_status'] = 'open';
			$post_data['post_type'] = 'shop_order_refund';
			$post_data['post_author'] = $userID;
			$post_data['post_name'] = $postname; 
			$post_data['guid'] = '';
			$post_data['post_excerpt'] = $order['OrderNotes'];
			$post_data['post_parent'] = $order['OrderID']; 
				
			$post_ID = wp_insert_post($post_data, $wp_error = false);
			
			$sqlDiscount = "SELECT order_item_id FROM wp_woocommerce_order_items  WHERE  order_id = '".$order['OrderID']."' and order_item_type = 'coupon' ";
			$orderDiscount = $wpdb->get_results($sqlDiscount);
			
			$orderDiscountID = $orderDiscount[0]->order_item_id ;
			if($orderDiscountID!='') {
				$sql = "SELECT meta_key,meta_value FROM wp_woocommerce_order_itemmeta  WHERE  order_item_id = '".$orderDiscountID."' AND meta_key = 'discount_amount' ";
				$Discount = $wpdb->get_results($sql);
				$Discout_value = $Discount[0]->meta_value ;
			}
			
			foreach($order['CancelItemDetail'] as $k4=>$v4) {
	  
				foreach($v4 as $k5=>$v5)
				{	
					$orderRefund[$k5]= $v5 ;
				} 
			
				$itemID = $orderRefund['ItemID'];
				$refundQty = $orderRefund['QtyCancel'];
				$itemPrice = $orderRefund['ItemPrice'];
				$SKU =$orderRefund['SKU'];
				
				
				if($refundQty>0) {
				
				//AND meta_key='_price' AND pm.meta_value !=' '
					$sql = "SELECT meta_key,meta_value FROM $wpdb->postmeta  WHERE  post_id = '".$itemID."' AND meta_key = '_stock' ";
					$product_list = $wpdb->get_results($sql);
					
				
					$ItemStock =$product_list[0]->meta_value ;
			
					$RefundPrice = $itemPrice * $refundQty ;
					$TotalRefundPrice = $TotalRefundPrice + $RefundPrice ;
			
					$totalQty = $refundQty + $ItemStock ; 
			//increase refund product qty
					update_post_meta($itemID, '_stock', $totalQty );
					
					$sql1 = "SELECT order_item_id FROM wp_woocommerce_order_items  WHERE  order_id = '".$order['OrderID']."' and order_item_type = 'line_item' ";
					$orderReID = $wpdb->get_results($sql1);
					
					foreach($orderReID as $orderItem)
					{
						$RefundItemID = $orderItem->order_item_id ;
						
						
						$items = array(
						'order_item_name' 		=> $SKU,
						'order_item_type' 		=> 'line_item',
						);
				
						$ordeitemID = wc_add_order_item($post_ID,$items );
						
			
						wc_add_order_item_meta($ordeitemID, '_refunded_item_id', $RefundItemID, $unique = false );
						wc_add_order_item_meta($ordeitemID, '_line_total', '-'.$itemPrice, $unique = false );
						wc_add_order_item_meta($ordeitemID, '_line_subtotal', '-'.$itemPrice, $unique = false );
						wc_add_order_item_meta($ordeitemID, '_product_id', $itemID, $unique = false );
						wc_add_order_item_meta($ordeitemID, '_qty', $refundQty, $unique = false );
				//_line_tax_data
					}
				
					}
			} 
			if($Discout_value!=0){
				
				$TotalRefundPrice =  $TotalRefundPrice -$Discout_value ;
			}
			$post_meta = array(
				'_order_total' => '-'.$TotalRefundPrice,
				'_cart_discount_tax' => '',
				'_cart_discount' =>'',
				'_order_shipping' =>  '',
				'_order_tax' => '',
				'_order_shipping_tax' => '',
				'_order_currency' => 'USD',
				'_refund_amount' => $TotalRefundPrice );
				
			foreach($post_meta as $key=>$value){
	
					add_post_meta( $post_ID, $key, $value );
				}
			$result = 'Success'; 
			$last_modfied_date = date("m-d-Y H:i:s",strtotime($date));
			
				$RefundResponse = array();
				$RefundResponse['OrderID']		=	$order['OrderID'];
				$RefundResponse['result']		=   $result ;
				$RefundResponse['OrderStatus'] =   $order['OrderStatus'] ;
				$RefundResponse['last_modfied_date'] =   $last_modfied_date ;
		
			}
			return $RefundResponse;	
}
	
	
	
	public function mySQLSafe($value, $quote = "'") 
	{
		//We are going to do this to keep the functions from contantly running
		if (empty($this->magic)) 
		{
			$this->magic = (bool)get_magic_quotes_gpc();
		}
		if (empty($this->escape)) 
		{

			if (function_exists('mysql_real_escape_string')) 
			{
				$this->escape = 'mysql_real_escape_string';
			} 
			else 
			{
				$this->escape = 'mysql_escape_string';
			}
		}

		if (empty($value)) 
		{
			return $quote.$quote;
		}

		## Stripslashes
		if ($this->magic) 
		{
			$value = stripslashes($value);
		}
		
		## Strip quotes if already in
		$value = str_replace(array("\\'","'"), "&#39;", $value);

		## Quote value
		if ($this->escape == 'mysql_real_escape_string' && !empty($this->db)) 
		{
			$value = mysql_real_escape_string($value, $this->db);
		} 
		else 
		{
			#$value = mysql_escape_string($value);
			$value = wc_clean($value);
		}

		$value = $quote . trim($value) . $quote;

		return $value;
	}
	
} // Class end

//ob_clean();ob_start();


if(isset($_REQUEST['request'])) 
{
	$wpObject = new Webgility_Ecc_WP();

	$wpObject->parseRequest();	
}

?>