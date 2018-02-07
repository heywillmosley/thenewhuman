<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('IC_Commerce_Golden_Label')){
	class IC_Commerce_Golden_Label{
		
		public $constants 	=	array();
		
		public $labels 		=	NULL;
		
		public function __construct($constants) {
			global $options;			
			$this->constants		= $constants;	
			
			if(!$this->labels){
				$this->get_labels();
			}	
		}
		
		function get_labels(){
			
			$labels = array(
				'dashboard_summary'					=> 'Summary'
				,'dashboard_today_summary'			=> 'Today Summary'
				,'dashboard_sales_summary'			=> 'Sales Summary'
				,'dashboard_audience_overview' 		=> 'Audience Overview'
				,'dashboard_order_summary' 			=> 'Order Summary'
				,'dashboard_sales_order_status' 	=> 'Sales Order Status'
				
				,'dashboard_top_products' 			=> 'Top %s Products'
				,'dashboard_top_billing_country' 	=> 'Top %s Billing Country'
				,'dashboard_top_payment_gateway' 	=> 'Top %s Payment Gateway'
				,'dashboard_top_recent_orders' 		=> 'Recent %s Orders'
				,'dashboard_top_customer' 			=> 'Top %s Customer'
				,'dashboard_top_coupons' 			=> 'Top %s Coupons'
				
				
				,'customer_page' 					=> 'Customer'
			);
			
			$new_label = apply_filters('icwoocommercegolden_labels',$labels);
			
			$this->labels = $new_label;
			
			return $new_label;
		}
		
		function get_label($key){
			
			
			if(isset($this->labels[$key])){
				//echo $key;
				$label = $this->labels[$key];
			}else{
				$label = str_replace("_"," " , $key);
				$label = ucfirst($label);
			}
			
			return $label;
		}
		
	}//End Class
}