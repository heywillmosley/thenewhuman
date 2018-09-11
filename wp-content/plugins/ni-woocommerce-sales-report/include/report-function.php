<?php 
if( !class_exists( 'ReportFunction' ) ) {
class ReportFunction{
	
		public function __construct(){
			
		}
		function get_request($request, $default = NULL)
		{
			$v = $_REQUEST[$request];
			$r = isset($v) ? $v : $default;
		 return $r;
		}
		function print_data($r)
		{
			echo '<pre>',print_r($r,1),'</pre>';	
		}
		function get_price($price)
		{
			//echo '<pre>',print_r($r,1),'</pre>';	
			return	wc_price($price);
		}
		function get_country_name($code)
		{	$name = "";
			if (strlen($code)>0){
				$name= WC()->countries->countries[ $code];	
				$name  = isset($name) ? $name : $code;
			}
			return $name;
		}
		function get_currency($default = "SYMBOL")
		{
			$r = '';
			 $currency_name = get_woocommerce_currency();
			 $symbol = get_woocommerce_currency_symbol($currency_name);
			 
			 if ($default=="NAME")
			 $r =  $currency_name;
			 if ($default=="SYMBOL")
			 $r =  $symbol;
			 
			 return $r;
		}
		/*Dashboard function*/
		function get_customer_report(){
			global $wpdb;
			$row = array();
			$query = "";
			$query = "SELECT
					SUM(order_total.meta_value)as 'order_total'
					,COUNT(*)as 'order_count'
					,billing_first_name.meta_value as billing_first_name
					,billing_email.meta_value as billing_email
					FROM {$wpdb->prefix}posts as posts			
					LEFT JOIN  {$wpdb->prefix}postmeta as order_total ON order_total.post_id=posts.ID 
					LEFT JOIN  {$wpdb->prefix}postmeta as billing_first_name ON billing_first_name.post_id=posts.ID 
					LEFT JOIN  {$wpdb->prefix}postmeta as billing_email ON billing_email.post_id=posts.ID 
					
					WHERE 1=1
					AND posts.post_type ='shop_order' 
					AND order_total.meta_key='_order_total' 
					AND billing_first_name.meta_key='_billing_first_name' 
					AND billing_email.meta_key='_billing_email' 
					
					GROUP BY  billing_email.meta_value 
					
					ORDER BY SUM(order_total.meta_value) DESC
					
					LIMIT 5
					";
			$row = $wpdb->get_results($query);
			//$this->print_data($row );
			//$row = array();
			return $row;
		}
		function get_country_report(){
			global $wpdb;
			$row = array();
			$query = "";
			$query = "SELECT
					SUM(order_total.meta_value)as 'order_total'
					,COUNT(*)as 'order_count'
					,billing_country.meta_value as billing_country
					FROM {$wpdb->prefix}posts as posts			
					LEFT JOIN  {$wpdb->prefix}postmeta as order_total ON order_total.post_id=posts.ID 
					LEFT JOIN  {$wpdb->prefix}postmeta as billing_country ON billing_country.post_id=posts.ID 
		
					
					WHERE 1=1
					AND posts.post_type ='shop_order' 
					AND order_total.meta_key='_order_total' 
					AND billing_country.meta_key='_billing_country' 
					
					
					GROUP BY  billing_country.meta_value 
					
					ORDER BY SUM(order_total.meta_value) DESC
					
					LIMIT 5
					";
			$row = $wpdb->get_results($query);
			//$this->print_data($row );
			//$row = array();
			return $row;
		}
		function get_low_in_stock(){
			global $wpdb;
			$row = array();
			$query = "";
			$stock   = absint( max( get_option( 'woocommerce_notify_low_stock_amount' ), 1 ) );
			$nostock = absint( max( get_option( 'woocommerce_notify_no_stock_amount' ), 0 ) );
		
			$query =  "SELECT COUNT( DISTINCT posts.ID ) as low_in_stock  FROM wp_posts as posts
			INNER JOIN wp_postmeta AS postmeta ON posts.ID = postmeta.post_id
			INNER JOIN wp_postmeta AS postmeta2 ON posts.ID = postmeta2.post_id
			WHERE 1=1
			AND posts.post_type IN ( 'product', 'product_variation' )
			AND posts.post_status = 'publish'
			AND postmeta2.meta_key = '_manage_stock' AND postmeta2.meta_value = 'yes'
			AND postmeta.meta_key = '_stock' AND CAST(postmeta.meta_value AS SIGNED) <= '{$stock}'
			AND postmeta.meta_key = '_stock' AND CAST(postmeta.meta_value AS SIGNED) > '{$nostock}'";
			
			$row = $wpdb->get_var($query);
			
			//$this->print_data($wpdb);
			//$this->print_data($row);
			
		
			return $row;
			
		}
		function get_out_of_stock(){
			global $wpdb;
			$row = array();
			$query = "";
			$stock = absint( max( get_option( 'woocommerce_notify_no_stock_amount' ), 0 ) );
		
			$query =  "SELECT COUNT( DISTINCT posts.ID ) as out_of_stock FROM wp_posts as posts
			INNER JOIN wp_postmeta AS postmeta ON posts.ID = postmeta.post_id
			INNER JOIN wp_postmeta AS postmeta2 ON posts.ID = postmeta2.post_id
			WHERE 1=1
			AND posts.post_type IN ( 'product', 'product_variation' )
			AND posts.post_status = 'publish'
			AND postmeta2.meta_key = '_manage_stock' AND postmeta2.meta_value = 'yes'
			AND postmeta.meta_key = '_stock' AND CAST(postmeta.meta_value AS SIGNED) <= '{$stock}'";
			
			$row = $wpdb->get_var($query);
			
			//$this->print_data($wpdb);
			//$this->print_data($row);
			
		
			return $row;
			
		}
		function get_most_stock(){
			global $wpdb;
			$row = array();
			$query = "";
			$stock = absint( max( get_option( 'woocommerce_notify_low_stock_amount' ), 0 ) );
		
			$query =  " SELECT COUNT( DISTINCT posts.ID ) FROM wp_posts as posts
			INNER JOIN wp_postmeta AS postmeta ON posts.ID = postmeta.post_id
			INNER JOIN wp_postmeta AS postmeta2 ON posts.ID = postmeta2.post_id
			WHERE 1=1
			AND posts.post_type IN ( 'product', 'product_variation' )
			AND posts.post_status = 'publish'
			AND postmeta2.meta_key = '_manage_stock' AND postmeta2.meta_value = 'yes'
			AND postmeta.meta_key = '_stock' AND CAST(postmeta.meta_value AS SIGNED) > '{stock}'";
			
			$row = $wpdb->get_var($query);
			
			//$this->print_data($wpdb);
			//$this->print_data($row);
			
		
			return $row;
			
		}
		/*End Dashbaord function*/
	}
}
//new ReportFunction();  
?>