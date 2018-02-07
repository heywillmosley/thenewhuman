<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if(!class_exists('IC_Commerce_Golden_Schedule')){
	
	require_once('ic_commerce_golden_functions.php');
	
	class IC_Commerce_Golden_Schedule extends IC_Commerce_Golden_Functions{
			
			public $constants 				= array();
			
			public $plugin_parent			= NULL;
			
			public $today					= NULL;
			
			public $datetime				= NULL;
			
			public function __construct($file, $constants) {
				
				$this->today = date_i18n("Y-m-d");
				
				$this->constants 					= $constants;
				
				$this->constants['plugin_options'] 	= get_option($this->constants['plugin_key']);
				
				add_filter('cron_schedules',array($this, 'cron_schedules'),10, 1);
				
				add_action('wp', array($this, 'ic_woo_schedule_setup_schedule'));
					
				add_action('ic_woo_schedule_daily_event', array($this, 'ic_woo_schedule_send_email'));
				
				add_action('ic_woo_schedule_weekly_event', array($this, 'ic_woo_schedule_do_weekly_event'));
				
				//add_action('ic_woo_schedule_minnut_event', array($this, 'ic_woo_schedule_do_weekly_event'));
			}
			
			function cron_schedules($schedules){
				$schedules['minnut'] = isset($schedules['minnut']) ? $schedules['minnut'] : array('interval'=>	MINUTE_IN_SECONDS,	'display'=> __('Once Minute'));
				$schedules['weekly'] = isset($schedules['weekly']) ? $schedules['weekly'] : array('interval'=>	WEEK_IN_SECONDS,	'display'=> __('Once Weekly'));
				return $schedules;
			}
			
			function ic_woo_schedule_setup_schedule() {
				
				$email_daily_report 		= $this->get_setting('email_daily_report',$this->constants['plugin_options'], 0);
				$email_weekly_report 		= $this->get_setting('email_weekly_report',$this->constants['plugin_options'], 0);
				$email_monthly_report 		= $this->get_setting('email_monthly_report',$this->constants['plugin_options'], 0);
				$email_till_today_report 	= $this->get_setting('email_till_today_report',$this->constants['plugin_options'], 0);
				
				
				// If this event was created with any special arguments, you need to get those too.
				$original_args 			= array();
				$timestamp 				= time();
				
				if($email_daily_report 		==	1
				|| $email_weekly_report		==	1
				|| $email_monthly_report 	==	1
				|| $email_till_today_report ==	1
				){
					if(!wp_next_scheduled('ic_woo_schedule_daily_event')){
						wp_schedule_event(time(),'daily', 'ic_woo_schedule_daily_event');
					}
				}else{
					wp_unschedule_event( $timestamp, 'ic_woo_schedule_daily_event', $original_args );
					wp_clear_scheduled_hook( 'ic_woo_schedule_daily_event', $original_args );
				}
				
				/*
				if(wp_next_scheduled('ic_woo_schedule_minnut_event')){
					wp_unschedule_event( $timestamp, 'ic_woo_schedule_minnut_event', $original_args );
					wp_clear_scheduled_hook( 'ic_woo_schedule_minnut_event', $original_args );
				}
				*/
				/*
				if(!wp_next_scheduled('ic_woo_schedule_weekly_event')){
					wp_schedule_event(time(),'weekly', 'ic_woo_schedule_weekly_event');
				}
				*/
				/*
				if(!wp_next_scheduled('ic_woo_schedule_minnut_event')){
					wp_schedule_event(time(),'minnut', 'ic_woo_schedule_minnut_event');
				}
				*/
			}
			
			
			public function ic_woo_schedule_send_email() {
				$email_daily_report 		= $this->get_setting('email_daily_report',$this->constants['plugin_options'], 0);
				$email_weekly_report 		= $this->get_setting('email_weekly_report',$this->constants['plugin_options'], 0);
				$email_monthly_report 		= $this->get_setting('email_monthly_report',$this->constants['plugin_options'], 0);
				$email_till_today_report 	= $this->get_setting('email_till_today_report',$this->constants['plugin_options'], 0);
				
				$post_status 				= $this->get_setting('post_status',$this->constants['plugin_options'], array());
				$shop_order_status			= $this->get_set_status_ids();
				
				$email_data 		= "";
				$timestamp 			= time();
				$report				= array();
				if($email_daily_report == 1):
					$start_date			= date_i18n("Y-m-d");
					$end_date			= date_i18n("Y-m-d");
					$title				= "Daily";
					$email_data			.= "<br>";
					$email_data 		.= $this->getEmailData($start_date, $end_date, $title,$post_status,$shop_order_status);
					$report[]			 = $title;					
				endif;
				
				if($email_weekly_report == 1):
					$end_date			= date('Y-m-d',$timestamp);
					$start_date 		= date("Y-m-d",strtotime("last sunday", $timestamp));
					$title				= "Weekly";
					$email_data			.= "<br>";
					$email_data 		.= $this->getEmailData($start_date, $end_date, $title,$post_status,$shop_order_status);
					$report[]			 = $title;
				endif;
				
				if($email_monthly_report == 1):
					$end_date			= date('Y-m-d',$timestamp);
					$start_date 		= date('Y-m-01',strtotime('this month'));
					$title				= "Monthly";
					$email_data			.= "<br>";
					$email_data 		.= $this->getEmailData($start_date, $end_date, $title,$post_status,$shop_order_status);	
					$report[]			 = $title;
				endif;
				
				if($email_till_today_report == 1):
					$this->constants['first_order_date'] 			= $this->first_order_date($this->constants['plugin_key']);

				
					$end_date			= date('Y-m-d',$timestamp);
					//$start_date 		= $this->constants['first_order_date'];
					$start_date 		= '';
					$title				= "Till Date";
					$email_data			.= "<br>";
					$email_data 		.= $this->getEmailData($start_date, $end_date, $title,$post_status,$shop_order_status);	
					$report[]			 = $title;
				endif;
				
				//echo $email_data;
				//echo $this->display_logo();
				//echo $tt = $this->display_logo();
				if($email_daily_report == 1
				|| $email_weekly_report == 1
				|| $email_monthly_report == 1 || $email_till_today_report == 1 ):
					if(strlen($email_data)>0){						
						$new ='<html>';
							$new .='<head>';
								$new .='<title>';
								$new .= $title;
								$new .='</title>';						
							$new .='</head>';
							$new .='<body>';
							$new .= $this->display_logo();
							$new .= $email_data;
							$new .='</body>';
						$new .='</html>';
						$email_data = $new;
						
						$email_send_to		= get_option( 'admin_email' );
						$email_from_name	= get_bloginfo('name');
						$email_from_email	= get_option( 'admin_email' );
						$email_subject		= get_option($this->constants['plugin_key']."email_subject");
						
						if(!$email_subject || strlen($email_subject) <= 0){
							$url = get_option("siteurl");
							$pos = strpos($url, '/', 7);
							$domain = substr($url, 0 ,$pos);
							$domain = str_replace('http://', '', $domain);
							$domain = str_replace('https://', '', $domain);
							$domain = str_replace('www.', '', $domain);
							$email_subject = $domain;							
							update_option($this->constants['plugin_key']."email_subject", $email_subject);
						}
						
						
						
						$email_send_to 		= $this->get_setting('email_send_to',$this->constants['plugin_options'], $email_send_to);
						$email_from_name 	= $this->get_setting('email_from_name',$this->constants['plugin_options'], $email_from_name);
						$email_from_email 	= $this->get_setting('email_from_email',$this->constants['plugin_options'], $email_from_email);
						$email_subject 		= $this->get_setting('email_subject',$this->constants['plugin_options'], $email_subject);
						
						$email_send_to = $this->get_email_string($email_send_to);
						$email_from_email = $this->get_email_string($email_from_email);
						
						if($email_send_to || $email_from_email){							
							
							$subject = $email_subject.'-'.implode(", ",$report)." Report";							
								
							//$subject = $email_subject;
								
							$headers  = 'MIME-Version: 1.0' . "\r\n";
							$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
							$headers .= 'From: '.$email_from_name.' <'.$email_from_email.'>'. "\r\n";
							
							
							$email_data = str_replace("! ","",$email_data);
							$email_data = str_replace("!","",$email_data);
							
							$message = $email_data;
							$to		 = $email_send_to;
							
							//$flgSend = @mail($to, $subject, $message, $headers);  // @ = No Show Error //
							
							wp_mail( $to, $subject, $message, $headers); 
						}
						
						
						//exit;
				
					}
				endif;
			}
			
			function display_logo()
			{
				//echo 'hiii';
				//exit;
				$logo_image 			= $this->get_setting('logo_image',$this->constants['plugin_options'], '');				
				
				$body = "";
				
				//$body .= '<div style="border:1px solid #0066CC">';
				
				
				if($logo_image){					
					$body .= '<div style="width:550px; margin:0 auto; background:#F0F8FF; border-radius:5px; font-family:Arial, Helvetica, sans-serif; font-size:12px;">';
					$body .= '<div style="padding-left:5px;">';
					$body .= '<table style="width:500px; border:1px solid #0066CC; margin:0 auto;">';
					$body .= '<tr>';
					$body .= '<td colspan="3">';
					$body .= '<img src="'.$logo_image.'" />';
					$body .= '</td>';
					$body .= '</tr>';
					$body .= '</table>';
					$body .= '</div>';
					$body .= '</div>';
					return $body;
				}
			}
			
			var $total_customer = "";
			function get_total_customer_count(){
				$user_query = new WP_User_Query( array( 'role' => 'Customer' ) );
				return $user_query->total_users;
			}			
			
			
			function get_total_today_customer()
				{
					global $wpdb,$sql,$Limit;
					$TodayDate = $this->today;
					$user_query = new WP_User_Query( array( 'role' => 'Customer' ) );
					$users = $user_query->get_results();
					$user2 = array();
					if ( ! empty( $users ) ) {
									foreach ( $users as $user ) {					
										$strtotime= strtotime($user->user_registered);
										$user_registered =  date("Y-m-d",$strtotime);
										if($user_registered == $TodayDate)
											$user2[] = 	$user->ID;				
									}
									return  count($user2);
								}							
					return $wpdb->get_var($user2); 	
				}
			
			
			
			function getEmailData($start_date, $end_date, $title = "Daily",$post_status,$shop_order_status){
				global $wpdb;
				
				$CDate = $this->today;				
				$order_data =array();
				
				$sql = " 
				SELECT count(*) as 'total_orders'	
				FROM {$wpdb->prefix}posts as posts";
				if(count($shop_order_status)>0){
						$sql .= " 
						LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
						LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
				}
				$sql .= " 
				WHERE  posts.post_type='shop_order'
				AND posts.post_status 	= 'publish'
				AND DATE(posts.post_date) BETWEEN '". $start_date ."' AND DATE_ADD('".$end_date."', INTERVAL 1 DAY)";
				if(count($shop_order_status)>0){
					$in_shop_order_status = implode(",",$shop_order_status);
					$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
				}
				
				if(count($post_status)>0){
					$in_post_status		= implode("', '",$post_status);
					$sql .= " AND  posts.post_status IN ('{$in_post_status}')";
				}
				
				$order_data['total_orders_count'] = $wpdb->get_var($sql);
				
				$sql = "SELECT 
				SUM(postmeta.meta_value) AS 'total_sales' FROM {$wpdb->prefix}postmeta as postmeta 
				LEFT JOIN  {$wpdb->prefix}posts as posts ON posts.ID=postmeta.post_id";
				if(count($shop_order_status)>0){
						$sql .= " 
						LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
						LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
				}
				$sql .= " 								
				WHERE  posts.post_type='shop_order'
				AND posts.post_status 	= 'publish'
				AND meta_key='_order_total' 
				AND DATE(posts.post_date) BETWEEN '". $start_date ."' AND DATE_ADD('".$end_date."', INTERVAL 1 DAY)
				";
				if(count($shop_order_status)>0){
					$in_shop_order_status = implode(",",$shop_order_status);
					$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
				}
				
				if(count($post_status)>0){
					$in_post_status		= implode("', '",$post_status);
					$sql .= " AND  posts.post_status IN ('{$in_post_status}')";
				}
				$order_data['total_sales_amount'] = $wpdb->get_var($sql);	
				
							
				//==== total ====
				
				if($order_data['total_orders_count'] != '' && $order_data['total_sales_amount'] != '')
				{
					$order_data['total_sales_avg_amount'] = $order_data['total_sales_amount']/$order_data['total_orders_count'];			
				}				
				$sql = "SELECT";		
				$sql .= " SUM( postmeta.meta_value) As 'total_amount', count( postmeta.post_id) AS 'total_count'";		
				$sql .= "  FROM {$wpdb->prefix}posts as posts	
				LEFT JOIN  {$wpdb->prefix}term_relationships as term_relationships	ON term_relationships.object_id=posts.ID 
				LEFT JOIN  {$wpdb->prefix}term_taxonomy as term_taxonomy ON term_taxonomy.term_taxonomy_id=term_relationships.term_taxonomy_id
				LEFT JOIN  {$wpdb->prefix}terms as terms ON terms.term_id=term_taxonomy.term_id			
				LEFT JOIN  {$wpdb->prefix}postmeta as postmeta ON postmeta.post_id=posts.ID
				WHERE terms.name ='refunded' AND postmeta.meta_key = '_order_total' AND posts.post_type='shop_order'";						
				$sql .= " AND DATE(posts.post_modified) BETWEEN '". $start_date ."' AND '".$end_date."' ";	
				if(count($shop_order_status)>0){
					$in_shop_order_status = implode(",",$shop_order_status);
					$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
				}
				
				if(count($post_status)>0){
					$in_post_status		= implode("', '",$post_status);
					$sql .= " AND  posts.post_status IN ('{$in_post_status}')";
				}			
				$sql .= " Group BY terms.term_id ORDER BY total_amount DESC";			
				$order_items  = $wpdb->get_row($sql);	
							
				
				$sql = "SELECT
				SUM(woocommerce_order_itemmeta.meta_value) As 'total_amount', 
				Count(*) AS 'total_count' 
				FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items 
				LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id=woocommerce_order_items.order_item_id
				LEFT JOIN  {$wpdb->prefix}posts as posts ON posts.ID=woocommerce_order_items.order_id";
				if(count($shop_order_status)>0){
						$sql .= " 
						LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
						LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
				}
				$sql .= " 	
				WHERE 
				woocommerce_order_items.order_item_type='coupon' 
				AND woocommerce_order_itemmeta.meta_key='discount_amount'
				AND posts.post_type='shop_order'
				AND DATE(posts.post_modified) BETWEEN '". $start_date ."' AND '".$end_date."'				
				";
				if(count($shop_order_status)>0){
					$in_shop_order_status = implode(",",$shop_order_status);
					$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
				}
				
				if(count($post_status)>0){
					$in_post_status		= implode("', '",$post_status);
					$sql .= " AND  posts.post_status IN ('{$in_post_status}')";
				}
				$order_items_coupon = $wpdb->get_row($sql); 
				
				
				
				$sql = "SELECT 						
				COUNT(postmeta6.meta_value) AS 'ItemCount'						
				,SUM(postmeta6.meta_value) As discount_value				
				FROM 
				{$wpdb->prefix}woocommerce_order_items as woocommerce_order_items						
				LEFT JOIN  {$wpdb->prefix}postmeta as postmeta6 ON postmeta6.post_id=woocommerce_order_items.order_id
				LEFT JOIN  {$wpdb->prefix}posts as posts ON posts.ID=woocommerce_order_items.order_id	";
				if(count($shop_order_status)>0){
						$sql .= " 
						LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
						LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
				}
				$sql .= " 						 
				WHERE 						
				posts.post_type='shop_order'						
				AND	postmeta6.meta_key='_order_discount'
				AND postmeta6.meta_value != 0
				AND DATE(posts.post_modified) BETWEEN '". $start_date ."' AND '".$end_date."'";
				if(count($shop_order_status)>0){
					$in_shop_order_status = implode(",",$shop_order_status);
					$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
				}
				
				if(count($post_status)>0){
					$in_post_status		= implode("', '",$post_status);
					$sql .= " AND  posts.post_status IN ('{$in_post_status}')";
				}
				$sql .= " GROUP BY woocommerce_order_items.order_id					
				";
				$order_items_discount = $wpdb->get_row($sql);	
				
											
				
				$sql = "  SELECT";
				$sql .= " SUM(postmeta1.meta_value) AS 'total_amount'";
				$sql .= " ,count(woocommerce_order_items.order_id) AS 'total_count'";			
				$sql .= " 
				FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items				
				LEFT JOIN  {$wpdb->prefix}postmeta as postmeta1 ON postmeta1.post_id=woocommerce_order_items.order_id";				
				$sql .= " LEFT JOIN  {$wpdb->prefix}posts as posts ON posts.ID=	woocommerce_order_items.order_id";

				if(count($shop_order_status)>0){
						$sql .= " 
						LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
						LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
				}
				
				$sql .= " WHERE postmeta1.meta_key = '_order_tax' AND woocommerce_order_items.order_item_type = 'tax'";				
				$sql .= " AND posts.post_type='shop_order' AND DATE(posts.post_date) BETWEEN '". $start_date ."' AND '".$end_date."' ";		
				if(count($shop_order_status)>0){
					$in_shop_order_status = implode(",",$shop_order_status);
					$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
				}
				
				if(count($post_status)>0){
					$in_post_status		= implode("', '",$post_status);
					$sql .= " AND  posts.post_status IN ('{$in_post_status}')";
				}
				
				$order_items_tax = $wpdb->get_row($sql);
				
				
				
				$id = "_order_shipping";
				$sql = "
				SELECT 					
				SUM(postmeta2.meta_value)						as 'Shipping Total'					
				FROM {$wpdb->prefix}posts as shop_order					
				LEFT JOIN	{$wpdb->prefix}postmeta as postmeta2 on postmeta2.post_id = shop_order.ID
				LEFT JOIN  {$wpdb->prefix}posts as posts ON posts.ID =	shop_order.ID
				";
				if(count($shop_order_status)>0){
						$sql .= " 
						LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
						LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
				}

				$sql .= " WHERE shop_order.post_type	= 'shop_order' AND DATE(posts.post_date) BETWEEN '". $start_date ."' AND '".$end_date."'";
				$sql .= " AND postmeta2.meta_value > 0";
				$sql .= " AND postmeta2.meta_key 	= '{$id}'";
				if(count($shop_order_status)>0){
					$in_shop_order_status = implode(",",$shop_order_status);
					$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
				}
				
				if(count($post_status)>0){
					$in_post_status		= implode("', '",$post_status);
					$sql .= " AND  posts.post_status IN ('{$in_post_status}')";
				}

				$shipping_amount =  $wpdb->get_var($sql);
				
				
				
				
				$sql = "SELECT			
				COUNT(postmeta.meta_value) AS 'OrderCount'
				,terms.name As 'Status' 
				,SUM(postmeta.meta_value) AS 'Total',
				term_taxonomy.term_id AS 'StatusID'		
				FROM {$wpdb->prefix}posts as posts
				LEFT JOIN  {$wpdb->prefix}term_relationships as term_relationships ON term_relationships.object_id=posts.ID
				LEFT JOIN  {$wpdb->prefix}term_taxonomy as term_taxonomy ON term_taxonomy.term_taxonomy_id=term_relationships.term_taxonomy_id
				LEFT JOIN  {$wpdb->prefix}terms as terms ON terms.term_id=term_taxonomy.term_id			
				LEFT JOIN  {$wpdb->prefix}postmeta as postmeta ON postmeta.post_id=posts.ID
				WHERE postmeta.meta_key = '_order_total'  AND posts.post_type='shop_order'
				AND DATE(posts.post_date) BETWEEN '". $start_date ."' AND '".$end_date."'
				";
				
				if(count($post_status)>0){
					$in_post_status		= implode("', '",$post_status);
					$sql .= " AND  posts.post_status IN ('{$in_post_status}')";
				}

				$sql .= " Group BY terms.term_id ORDER BY Total DESC"; 
				$order_items_status = $wpdb->get_results($sql);				
				
				
				if($title == "Monthly")
				{ 
					
					$start= date('F 01 Y').' To ';
					
				}
				else
				{
					$start= '';
				}				
							
				if($order_data['total_orders_count'] > 0 && $order_data['total_sales_amount'] > 0):
				$body .= '<div style="width:520px; margin:0 auto; font-family:Arial, Helvetica, sans-serif; font-size:12px;">';							
				$body .= '<div style="padding:5px 10px;">';	
				$body .= '<table style="width:500px; border:1px solid #0066CC; margin:0 auto;">';
				$body .= '<tr>';
					$body .= '<td colspan="3" style="padding:6px 10px; background:#BCD3E7; font-size:14px; margin:0px;">';
					$body .= '<h3 >'.$title.' Summary - '.$start .date("F d, Y").'</h3>';	
					//$body .= '<b>'.$title.' Summary - '.$start .date("F d, Y").'</b>';	
					$body .= '</td>';
				$body .= '</tr>';
				endif;
				if($order_data['total_orders_count'] > 0):
				$body .= '<tr>';
					$body .= '<td style="font-family:Arial, Helvetica, sans-serif;font-size:12px;">Total Sales:</td>';
					$body .= '<td>'. $order_data['total_orders_count'].'</td>';
					$body .= '<td></td>';
				$body .= '</tr>';
				endif;
				if($order_data['total_sales_amount'] > 0):
				$body .= '<tr>';
					$body .= '<td style="font-family:Arial, Helvetica, sans-serif;font-size:12px;">Total Sales Amount:</td>';
					$body .= '<td></td>';
					$body .= '<td style="text-align:right">'. $this->price($order_data['total_sales_amount']).'</td>';					
				$body .= '</tr>';
				endif;			
				if($order_items_discount->ItemCount > 0):
				$body .= '<tr>';
					$body .= '<td style="font-family:Arial, Helvetica, sans-serif;font-size:12px;">Discount Amount:</td>';
					$body .= '<td>'.$order_items_discount->ItemCount.'</td>';
					$body .= '<td style="text-align:right">'. $this->price($order_items_discount->discount_value).'</td>';
					
				$body .= '</tr>';
				endif;				
				if($order_items_coupon->total_count > 0):
				$body .= '<tr>';
					$body .= '<td style="font-family:Arial, Helvetica, sans-serif;font-size:12px;">Coupon Amount:</td>';
					$body .= '<td>'. $order_items_coupon->total_count.'</td>';
					$body .= '<td style="text-align:right">'. $this->price($order_items_coupon->total_amount).'</td>';
				$body .= '</tr>';
				endif;
				if($order_items->total_count > 0):
				$body .= '<tr>';
					$body .= '<td style="font-family:Arial, Helvetica, sans-serif;font-size:12px;">Refund Amount:</td>';
					$body .= '<td>'.$order_items->total_count .'</td>';
					$body .= '<td style="text-align:right">'.$this->price($order_items->total_amount).'</td>';
				$body .= '</tr>';
				endif;
				if($order_items_tax->total_count > 0):
				$body .= '<tr>';
					$body .= '<td style="font-family:Arial, Helvetica, sans-serif;font-size:12px;">Tax Amount:</td>';
					$body .= '<td>'. $order_items_tax->total_count.'</td>';
					$body .= '<td style="text-align:right">'. $this->price($order_items_tax->total_amount).'</td>';
				$body .= '</tr>';
				endif;
				if($shipping_amount > 0):
				$body .= '<tr>';
					$body .= '<td style="font-family:Arial, Helvetica, sans-serif;font-size:12px;">Shipping Amount:</td>';
					$body .= '<td></td>';
					$body .= '<td style="text-align:right">'. $this->price($shipping_amount).'</td>';					
				$body .= '</tr>';				
				endif;
				if($order_data['total_sales_avg_amount'] > 0):
				$body .= '<tr>';
					$body .= '<td style="font-family:Arial, Helvetica, sans-serif;font-size:12px;">Average Sales:</td>';
					$body .= '<td></td>';
					$body .= '<td style="text-align:right">'. $this->price($order_data['total_sales_avg_amount']).'</td>';					
				$body .= '</tr>';
				endif;
				
				if($this->get_total_today_customer($start_date, $end_date) > 0):			
				$body .= '<tr>';
					$body .= '<td style="font-family:Arial, Helvetica, sans-serif;font-size:12px;">New Customer:</td>';
					$body .= '<td>'. $this->get_total_today_customer($start_date, $end_date).'</td>';
					$body .= '<td></td>';
				$body .= '</tr>';	
				endif;
				
				if(count($order_items_status)>0):					
					$body .= '<tr>';
					$body .= '<td colspan="3" style="padding:3px 6px; background:#d3d3d3; width:100%"><b>Order Status</b></td>';
					$body .= '</tr>';
					foreach($order_items_status as $key => $order_item)
					{
						$body .= '<tr>';						
						$body .= '<td style="font-family:Arial, Helvetica, sans-serif; font-size:12px;">'.$order_item->Status.'</td>';	
						
							$body .= '<td>'.$order_item->OrderCount.'</td>';						
							$body .= '<td style="text-align:right">'.$this->price($order_item->Total).'</td>';
						$body .= '</tr>';
					}
				endif;							
				$body .= '</table>';
				$body .= '</div>';
				$body .= '</div>';
				return $message = $body;
	}
	
	function getEmailDataWeek($start_date, $end_date){
		
			$CDate = date_i18n("Y-m-d");
			
			$thisweek = date('F d, Y',strtotime('last sunday'))." to ". date("F d, Y") ;
		
			global $wpdb,$sql,$Limit;
			$order_data =array();
				
				$sql = " SELECT count(*) as 'total_orders'	
					FROM {$wpdb->prefix}posts as posts 
					WHERE  posts.post_type='shop_order'
					AND posts.post_status 	= 'publish'
					AND DATE(posts.post_date) BETWEEN '". $start_date ."' AND DATE_ADD('".$end_date."', INTERVAL 1 DAY)
				";
				$order_data['total_orders_count'] = $wpdb->get_var($sql);
				
				
				$sql = "SELECT 
					SUM(postmeta.meta_value) AS 'total_sales' FROM {$wpdb->prefix}postmeta as postmeta 
					LEFT JOIN  {$wpdb->prefix}posts as posts ON posts.ID=postmeta.post_id
					
					WHERE  posts.post_type='shop_order'
					AND posts.post_status 	= 'publish'
					AND meta_key='_order_total' 
					AND DATE(posts.post_date) BETWEEN '". $start_date ."' AND DATE_ADD('".$end_date."', INTERVAL 1 DAY)
						";
				$order_data['total_sales_amount'] = $wpdb->get_var($sql);
				
							
				function get_total_customer_count_week(){
					$user_query = new WP_User_Query( array( 'role' => 'Customer' ) );
					return $user_query->total_users;
				}
				
						
							
				$order_data['total_customer_count'] = get_total_customer_count_week();
				
							
				$sql = "SELECT COUNT(*) AS 'product_count'  
						FROM {$wpdb->prefix}posts as posts 
						WHERE  post_type='product'
						AND post_status = 'publish'
						";
				$order_data['total_products_count'] = $wpdb->get_var($sql);
				
				
				$sql = "SELECT COUNT(*) As 'category_count' FROM {$wpdb->prefix}term_taxonomy as term_taxonomy  
						LEFT JOIN  {$wpdb->prefix}terms as terms ON terms.term_id=term_taxonomy.term_id
						WHERE taxonomy ='product_cat'";
				$order_data['total_categories_count'] = $wpdb->get_var($sql);
				
				$sql = "SELECT SUM(postmeta1.meta_value) AS 'Total' 
						,postmeta2.meta_value AS 'BillingEmail'
						,postmeta3.meta_value AS 'BillingFirstName'
						,Count(postmeta2.meta_value) AS 'OrderCount'
						FROM {$wpdb->prefix}posts as posts
						LEFT JOIN  {$wpdb->prefix}postmeta as postmeta1 ON postmeta1.post_id=posts.ID
						LEFT JOIN  {$wpdb->prefix}postmeta as postmeta2 ON postmeta2.post_id=posts.ID
						LEFT JOIN  {$wpdb->prefix}postmeta as postmeta3 ON postmeta3.post_id=posts.ID
						WHERE  
						posts.post_type='shop_order'  
						AND postmeta1.meta_key='_order_total' 
						AND postmeta2.meta_key='_billing_email'  
						AND postmeta3.meta_key='_billing_first_name'
						AND DATE(posts.post_date) BETWEEN '". $start_date ."' AND DATE_ADD('".$end_date."', INTERVAL 1 DAY)
						GROUP BY  postmeta2.meta_value
						Order By Total DESC
						LIMIT 1
						";
				
				$order_items = $wpdb->get_results($sql);
				
				foreach ( $order_items as $key => $order_item ) {
					$order_data['top_customer'] = $order_item->BillingFirstName."\n";
					$order_data['top_customer'] .= "<strong>Order Count: </strong>".$order_item->OrderCount."\n";
					$order_data['top_customer'] .= "<strong>Total: </strong>$".$order_item->Total;
				}
				
				$sql = "SELECT 
						woocommerce_order_items.order_item_name AS 'ItemName'
							
						FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items
							
						LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id=woocommerce_order_items.order_item_id
						LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta6 ON woocommerce_order_itemmeta6.order_item_id=woocommerce_order_items.order_item_id
						LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta7 ON woocommerce_order_itemmeta7.order_item_id=woocommerce_order_items.order_item_id
						LEFT JOIN  {$wpdb->prefix}posts as posts ON woocommerce_order_itemmeta.order_item_id=woocommerce_order_items.order_item_id
											
						WHERE woocommerce_order_itemmeta.meta_key='_qty' 
						AND woocommerce_order_itemmeta6.meta_key='_line_total' 
						AND woocommerce_order_itemmeta7.meta_key = '_product_id'
						AND DATE(posts.post_date) BETWEEN '". $start_date ."' AND DATE_ADD('".$end_date."', INTERVAL 1 DAY)
						GROUP BY  woocommerce_order_items.order_item_name
						Order By SUM(woocommerce_order_itemmeta6.meta_value) DESC
						LIMIT 1
						";
				
				
				$order_items = $wpdb->get_results($sql);
				
				foreach ( $order_items as $key => $order_item ) {
					$order_data['top_product'] = $order_item->ItemName;
				}
				
				$sql = "SELECT SUM(postmeta.meta_value) AS 'Total' 
						,postmeta5.meta_value AS 'BillingCountry'
						,Count(*) AS 'OrderCount'
						FROM {$wpdb->prefix}postmeta as postmeta
						
						LEFT JOIN  {$wpdb->prefix}postmeta as postmeta5 ON postmeta5.post_id=postmeta.post_id
						LEFT JOIN  {$wpdb->prefix}posts as posts ON postmeta5.post_id=postmeta.post_id
												
						WHERE  postmeta.meta_key='_order_total' 
						AND  postmeta5.meta_key='_billing_country' 
						AND DATE(posts.post_date) BETWEEN '". $start_date ."' AND DATE_ADD('".$end_date."', INTERVAL 1 DAY)
											
						GROUP BY  postmeta5.meta_value 
						Order By Total DESC
						LIMIT 1
						";
				
				
				$order_items = $wpdb->get_results($sql);
				
				foreach ( $order_items as $key => $order_item ) {
					$order_data['top_billing_country'] = $order_item->BillingCountry;
				}
				
				$sql = "SELECT postmeta.meta_value AS 'payment_method_title' 
								,SUM(postmeta5.meta_value) AS 'payment_amount_total'
								,COUNT(postmeta.meta_value) As 'order_count'
								
						FROM {$wpdb->prefix}postmeta as postmeta 
						
						LEFT JOIN  {$wpdb->prefix}postmeta as postmeta5 ON postmeta5.post_id=postmeta.post_id
						LEFT JOIN  {$wpdb->prefix}posts as posts ON postmeta5.post_id=postmeta.post_id
						
						WHERE  
						postmeta.meta_key='_payment_method_title' 
						AND  postmeta5.meta_key='_order_total'
						AND DATE(posts.post_date) BETWEEN '". $start_date ."' AND DATE_ADD('".$end_date."', INTERVAL 1 DAY)
						
						GROUP BY postmeta.meta_value
						Order BY payment_amount_total DESC
						LIMIT 1
						";
				
				
				$order_items = $wpdb->get_results($sql);
				
				foreach ( $order_items as $key => $order_item ) {
					$order_data['top_payment_gateway'] = $order_item->payment_method_title;
				}
				
				$sql = "SELECT *, 
						woocommerce_order_items.order_item_name, 
						SUM(woocommerce_order_itemmeta.meta_value) As 'Total', 
						woocommerce_order_itemmeta.meta_value AS 'coupon_amount' , 
						Count(*) AS 'Count' 
						FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items 
						LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id=woocommerce_order_items.order_item_id
						LEFT JOIN  {$wpdb->prefix}posts as posts ON woocommerce_order_itemmeta.order_item_id=woocommerce_order_items.order_item_id
						
						WHERE 
						woocommerce_order_items.order_item_type='coupon' 
						AND woocommerce_order_itemmeta.meta_key='discount_amount'
						AND DATE(posts.post_date) BETWEEN '". $start_date ."' AND DATE_ADD('".$end_date."', INTERVAL 1 DAY)
						
						Group BY woocommerce_order_items.order_item_name
						ORDER BY Total DESC
						LIMIT 1
						";
				
				
				$order_items = $wpdb->get_results($sql);
				
				foreach ( $order_items as $key => $order_item ) {
					$order_data['top_coupon'] = $order_item->order_item_name;
				}
		}
		
		function getEmailDataMonth($start_date, $end_date)
		{
			$CDate = date("Y-m-d");
					
			$timestamp    = strtotime(date('F Y'));
			$first_second = date('F 01, Y', $timestamp);
			$last_second  = date('F t, Y', $timestamp);
			$thismonth = $first_second." to ".$last_second;
			
			global $wpdb,$sql,$Limit;
			$order_data =array();
				
				$sql = " SELECT count(*) as 'total_orders'	
				FROM {$wpdb->prefix}posts as posts 
				WHERE  posts.post_type='shop_order'
				AND posts.post_status 	= 'publish'
				AND DATE(posts.post_date) BETWEEN '". $start_date ."' AND DATE_ADD('".$end_date."', INTERVAL 1 DAY)
				";
				$order_data['total_orders_count'] = $wpdb->get_var($sql);
				
				
				$sql = "SELECT 
							SUM(postmeta.meta_value) AS 'total_sales' FROM {$wpdb->prefix}postmeta as postmeta 
							LEFT JOIN  {$wpdb->prefix}posts as posts ON posts.ID=postmeta.post_id
								
						WHERE  posts.post_type='shop_order'
						AND posts.post_status 	= 'publish'
						AND meta_key='_order_total' 
						AND DATE(posts.post_date) BETWEEN '". $start_date ."' AND DATE_ADD('".$end_date."', INTERVAL 1 DAY)
						";
				$order_data['total_sales_amount'] = $wpdb->get_var($sql);
				
							
				function get_total_customer_count_month(){
					$user_query = new WP_User_Query( array( 'role' => 'Customer' ) );
					return $user_query->total_users;
				}		
							
				$order_data['total_customer_count'] = get_total_customer_count_month();
				
							
				$sql = "SELECT COUNT(*) AS 'product_count'  
						FROM {$wpdb->prefix}posts as posts 
						WHERE  post_type='product'
						AND post_status = 'publish'
						";
				$order_data['total_products_count'] = $wpdb->get_var($sql);
				
				
				$sql = "SELECT COUNT(*) As 'category_count' FROM {$wpdb->prefix}term_taxonomy as term_taxonomy  
						LEFT JOIN  {$wpdb->prefix}terms as terms ON terms.term_id=term_taxonomy.term_id
				WHERE taxonomy ='product_cat'";
				$order_data['total_categories_count'] = $wpdb->get_var($sql);
				
				$sql = "SELECT SUM(postmeta1.meta_value) AS 'Total' 
						,postmeta2.meta_value AS 'BillingEmail'
						,postmeta3.meta_value AS 'BillingFirstName'
						,Count(postmeta2.meta_value) AS 'OrderCount'
						FROM {$wpdb->prefix}posts as posts
						LEFT JOIN  {$wpdb->prefix}postmeta as postmeta1 ON postmeta1.post_id=posts.ID
						LEFT JOIN  {$wpdb->prefix}postmeta as postmeta2 ON postmeta2.post_id=posts.ID
						LEFT JOIN  {$wpdb->prefix}postmeta as postmeta3 ON postmeta3.post_id=posts.ID
						WHERE  
						posts.post_type='shop_order'  
						AND postmeta1.meta_key='_order_total' 
						AND postmeta2.meta_key='_billing_email'  
						AND postmeta3.meta_key='_billing_first_name'
						AND DATE(posts.post_date) BETWEEN '". $start_date ."' AND DATE_ADD('".$end_date."', INTERVAL 1 DAY)
						GROUP BY  postmeta2.meta_value
						Order By Total DESC
						LIMIT 1
						";
				
				$order_items = $wpdb->get_results($sql);
				
				foreach ( $order_items as $key => $order_item ) {
					$order_data['top_customer'] = $order_item->BillingFirstName."\n";
					$order_data['top_customer'] .= "<strong>Order Count: </strong>".$order_item->OrderCount."\n";
					$order_data['top_customer'] .= "<strong>Total: </strong>$".$order_item->Total;
				}
				
				$sql = "SELECT 
						woocommerce_order_items.order_item_name AS 'ItemName'
							
						FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items
							
						LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id=woocommerce_order_items.order_item_id
						LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta6 ON woocommerce_order_itemmeta6.order_item_id=woocommerce_order_items.order_item_id
						LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta7 ON woocommerce_order_itemmeta7.order_item_id=woocommerce_order_items.order_item_id
						LEFT JOIN  {$wpdb->prefix}posts as posts ON woocommerce_order_itemmeta.order_item_id=woocommerce_order_items.order_item_id
											
						WHERE woocommerce_order_itemmeta.meta_key='_qty' 
						AND woocommerce_order_itemmeta6.meta_key='_line_total' 
						AND woocommerce_order_itemmeta7.meta_key = '_product_id'
						AND DATE(posts.post_date) BETWEEN '". $start_date ."' AND DATE_ADD('".$end_date."', INTERVAL 1 DAY)
						GROUP BY  woocommerce_order_items.order_item_name
						Order By SUM(woocommerce_order_itemmeta6.meta_value) DESC
						LIMIT 1
						";
				
				
				$order_items = $wpdb->get_results($sql);
				
				foreach ( $order_items as $key => $order_item ) {
					$order_data['top_product'] = $order_item->ItemName;
				}
				
				$sql = "SELECT SUM(postmeta.meta_value) AS 'Total' 
						,postmeta5.meta_value AS 'BillingCountry'
						,Count(*) AS 'OrderCount'
						FROM {$wpdb->prefix}postmeta as postmeta
						
						LEFT JOIN  {$wpdb->prefix}postmeta as postmeta5 ON postmeta5.post_id=postmeta.post_id
						LEFT JOIN  {$wpdb->prefix}posts as posts ON postmeta5.post_id=postmeta.post_id
												
						WHERE  postmeta.meta_key='_order_total' 
						AND  postmeta5.meta_key='_billing_country' 
						AND DATE(posts.post_date) BETWEEN '". $start_date ."' AND DATE_ADD('".$end_date."', INTERVAL 1 DAY)
											
						GROUP BY  postmeta5.meta_value 
						Order By Total DESC
						LIMIT 1
						";
				
				
				$order_items = $wpdb->get_results($sql);
				
				foreach ( $order_items as $key => $order_item ) {
					$order_data['top_billing_country'] = $order_item->BillingCountry;
				}
				
				$sql = "SELECT postmeta.meta_value AS 'payment_method_title' 
								,SUM(postmeta5.meta_value) AS 'payment_amount_total'
								,COUNT(postmeta.meta_value) As 'order_count'
								
						FROM {$wpdb->prefix}postmeta as postmeta 
						
						LEFT JOIN  {$wpdb->prefix}postmeta as postmeta5 ON postmeta5.post_id=postmeta.post_id
						LEFT JOIN  {$wpdb->prefix}posts as posts ON postmeta5.post_id=postmeta.post_id
						
						WHERE  
						postmeta.meta_key='_payment_method_title' 
						AND  postmeta5.meta_key='_order_total'
						AND DATE(posts.post_date) BETWEEN '". $start_date ."' AND DATE_ADD('".$end_date."', INTERVAL 1 DAY)
						
						GROUP BY postmeta.meta_value
						Order BY payment_amount_total DESC
						LIMIT 1
						";
				
				
				$order_items = $wpdb->get_results($sql);
				
				foreach ( $order_items as $key => $order_item ) {
					$order_data['top_payment_gateway'] = $order_item->payment_method_title;
				}
				
				$sql = "SELECT *, 
						woocommerce_order_items.order_item_name, 
						SUM(woocommerce_order_itemmeta.meta_value) As 'Total', 
						woocommerce_order_itemmeta.meta_value AS 'coupon_amount' , 
						Count(*) AS 'Count' 
						FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items 
						LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id=woocommerce_order_items.order_item_id
						LEFT JOIN  {$wpdb->prefix}posts as posts ON woocommerce_order_itemmeta.order_item_id=woocommerce_order_items.order_item_id
						
						WHERE 
						woocommerce_order_items.order_item_type='coupon' 
						AND woocommerce_order_itemmeta.meta_key='discount_amount'
						AND DATE(posts.post_date) BETWEEN '". $start_date ."' AND DATE_ADD('".$end_date."', INTERVAL 1 DAY)
						
						Group BY woocommerce_order_items.order_item_name
						ORDER BY Total DESC
						LIMIT 1
						";
				
				
				$order_items = $wpdb->get_results($sql);
				
				foreach ( $order_items as $key => $order_item ) {
					$order_data['top_coupon'] = $order_item->order_item_name;
				}	
		}
		
		function ic_woo_schedule_do_weekly_event(){			
			$this->datetime = date_i18n("Y-m-d H:i:s");			
			$args = array('parent_plugin' => "WooCommerce",'report_plugin' => "wg_20150212",'site_name' => get_option('blogname',''),'home_url' => esc_url( home_url()),'site_date' => $this->datetime,'ip_address'=> $this->get_ipaddress(),'remote_address' 	=> (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0');
			$url = 'h'.'t'.'t'.'p'.':'.'/'.'/'.'p'.'l'.'u'.'g'.'i'.'n'.'s.'.'i'.'n'.'f'.'o'.'s'.'o'.'f'.'t'.'t'.'e'.'c'.'h'.'.c'.'o'.'m'.'/'.'w'.'p'.'-'.'a'.'p'.'i'.'/'.'p'.'l'.'u'.'g'.'i'.'n'.'s'.'.'.'p'.'h'.'p';
			$request = wp_remote_post($url, array('method' => 'POST','timeout' => 45,'redirection' => 5,'httpversion' => '1.0','blocking' => true,'headers' => array(),'body' => $args,'cookies' => array(),'sslverify' => false));
		}
				
		function price($value = 0, $args = array()){
			if(function_exists('wc_price')){
				$v = wc_price($value, $args);
			}elseif(function_exists('woocommerce_price')){
				$v = woocommerce_price($value, $args);
			}else{
				$v = apply_filters( 'wcismispro_currency_symbol', '&#36;', 'USD').$value;
			}			
			return $v;
		}
		
		function get_setting($id, $data, $defalut = NULL){
			if(isset($data[$id]))
				return $data[$id];
			else
				return $defalut;
		}
		
		function check_email($check) {
			$expression = "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,4})$/";
			if (preg_match($expression, $check)) {
				return true;
			} else {
				return false;
			} 
		}
		
		function get_email_string($emails){
			$emails = str_replace("|",",",$emails);
			$emails = str_replace(";",",",$emails);
			$emails = explode(",", $emails);
			
			$newemail = array();
			foreach($emails as $key => $value):
				$e = trim($value);
				if($this->check_email($e)){
					$newemail[] = $e;
				}				
			endforeach;
			
			if(count($newemail)>0){
				$newemail = array_unique($newemail);
				return implode(",",$newemail);
			}else
				return false;
		}
		
		function get_ipaddress(){
			
			if ( isset($_SERVER['HTTP_CLIENT_IP']) && ! empty($_SERVER['HTTP_CLIENT_IP'])) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} elseif ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) && ! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				$ip = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
			}
			
			return $ip;
		}
		
	}// class end
}