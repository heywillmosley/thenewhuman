<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! class_exists( 'IC_Commerce_Golden_Dashboard_Report' ) ) {
	require_once('ic_commerce_golden_functions.php');
	class IC_Commerce_Golden_Dashboard_Report extends IC_Commerce_Golden_Functions{
		
		public $per_page = 0;
		
		public $per_page_default = 5;
		
		public $constants 	=	array();
		
		public $request		=	array();
		
		public function __construct($file, $constants) {
			global $options;
			$this->constants				= $constants;
			$this->constants['plugin_file'] = $file;
						
			add_filter('cron_schedules',array($this, 'cron_schedules'),10, 1);
			add_action('wp', array($this, 'dashboad_report_setup_schedule'));
			add_action('dashboad_report_hourly_event', array($this, 'dashboad_report_hourly_event_function'));
			
			add_action('admin_init', array( $this, 'dashboad_report_force_email_report'));
			
			
		}
		
		function cron_schedules($schedules){
			$schedules['minnut']		= isset($schedules['minnut']) 		? $schedules['minnut'] 		:	array('interval'=>	MINUTE_IN_SECONDS,		'display'=> __('Once Minute'));
			$schedules['hourly']		= isset($schedules['hourly']) 		? $schedules['hourly'] 		:	array('interval'=>	HOUR_IN_SECONDS,		'display'=> __('Once Hourly'));
			$schedules['daily']			= isset($schedules['daily']) 		? $schedules['daily'] 		:	array('interval'=>	DAY_IN_SECONDS,			'display'=> __('Once Daily'));
			$schedules['weekly'] 		= isset($schedules['weekly']) 		? $schedules['weekly'] 		:	array('interval'=>	WEEK_IN_SECONDS,		'display'=> __('Once Weekly'));
			$schedules['twicedaily']	= isset($schedules['twicedaily']) 	? $schedules['twicedaily'] 	:	array('interval'=>	12 * HOUR_IN_SECONDS,	'display'=> __('Twice Daily'));
			return $schedules;
		}
		
		function dashboad_report_setup_schedule(){
			
			$this->constants['plugin_options'] 	= isset($this->constants['plugin_options'])? $this->constants['plugin_options'] : get_option($this->constants['plugin_key']);
			$dashboard_act_email_reporting		= $this->get_setting('dashboard_act_email_reporting',	$this->constants['plugin_options'], 0);
			$dashboard_email_schedule			= $this->get_setting('dashboard_email_schedule',	$this->constants['plugin_options'], 'daily');
			
			$original_args 			= array();
			$timestamp 				= time();
			
			if($dashboard_act_email_reporting == 1){
				if (!wp_next_scheduled( 'dashboad_report_hourly_event')){
					wp_schedule_event($timestamp, $dashboard_email_schedule, 'dashboad_report_hourly_event');
				}
			}else{
				wp_unschedule_event( $timestamp, 'dashboad_report_hourly_event', $original_args );
				wp_clear_scheduled_hook( 'dashboad_report_hourly_event', $original_args );
			}
		}
		
		function dashboad_report_hourly_event_function(){			
			$this->dashboad_report_hourly_event_send_mail();
			die;
		}
		
		function dashboad_report_force_email_report(){
			
			if(isset($_REQUEST['ic_dashboard_report_eamil']) and $_REQUEST['ic_dashboard_report_eamil'] == 1){
				$this->constants['dashboard_report_emailed'] = $this->dashboad_report_hourly_event_send_mail();
				add_action( 'admin_notices', array( $this, 'admin_notices'));
			}
		}
		
		public function admin_notices(){
			$message 				= NULL;				
			if(isset($this->constants['dashboard_report_emailed'])){
				if($this->constants['dashboard_report_emailed']){
					$msg = '<span>Email send successfully.</span>';
					$class = "updated";
				}else{
					$msg = '<span>Getting problum to sending mail.</span>';
					$class = "error";
				}
				
				$message .= "<div class=\"{$class}\">";
				$message .= '<p>'.$msg.'</p>';
				$message .= '</div>';
			}
			echo $message;
		}	
		
		public function dashboad_report_hourly_event_send_mail() {
				
				$this->constants['plugin_options'] 	= isset($this->constants['plugin_options'])? $this->constants['plugin_options'] : get_option($this->constants['plugin_key']);
				
				$dashboard_act_email_reporting	= $this->get_setting('dashboard_act_email_reporting',	$this->constants['plugin_options'], 0);
				
				if($dashboard_act_email_reporting == 0) return $output;
				
				$this->constants['plugin_options'] 	= get_option($this->constants['plugin_key']);				
				$this->constants['plugin_parent'] 	= array(
					"plugin_name"		=>"WooCommerce"
					,"plugin_slug"		=>"woocommerce/woocommerce.php"
					,"plugin_file_name"	=>"woocommerce.php"
					,"plugin_folder"	=>"woocommerce"
					,"order_detail_url"	=>"post.php?&action=edit&post="
				);
				
				$this->check_parent_plugin();					
				$this->define_constant();
				
				$this->today			= $this->constants['today_date'];
				$shop_order_status		= $this->get_set_status_ids();	
				$hide_order_status 		= $this->constants['hide_order_status'];
				$start_date 			= $this->constants['start_date'];
				$end_date 				= $this->constants['end_date'];
				
				$email_subject 			= $this->get_setting('dashboard_email_subject',$this->constants['plugin_options'], 'Autoemail HTML');
				$email_data 			= $this->get_all_items($shop_order_status,$hide_order_status,$start_date,$end_date,$email_subject);
				
				if(strlen($email_data)>0){						
					
					$email_send_to 		= $this->get_setting('dashboard_email_send_to',$this->constants['plugin_options'], '');
					$email_from_name 	= $this->get_setting('dashboard_email_from_name',$this->constants['plugin_options'], '');
					$email_from_email 	= $this->get_setting('dashboard_email_from_email',$this->constants['plugin_options'], '');
					$email_subject 		= $this->get_setting('dashboard_email_subject',$this->constants['plugin_options'], '');
					
					$email_send_to 		= $this->get_email_string($email_send_to);
					$email_from_email 	= $this->get_email_string($email_from_email);
					
					if($email_send_to || $email_from_email){							
						
						$subject = $email_subject;
							
						$headers  = 'MIME-Version: 1.0' . "\r\n";
						$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
						$headers .= 'From: '.$email_from_name.' <'.$email_from_email.'>'. "\r\n";
						
						$email_data = str_replace("! ","",$email_data);
						$email_data = str_replace("!","",$email_data);
						
						$message = $email_data;
						$to		 = $email_send_to;
						$result	 = false;
						
						if($_SERVER['SERVER_NAME'] != "p43"){
							
						}	
						$result = wp_mail( $to, $subject, $message, $headers); 					
						return $result;
					}
				}
		}
		
		function get_all_items($shop_order_status,$hide_order_status,$start_date,$end_date,$html_subject = 'Autoemail HTML'){
			
			$reports		= $this;			
			$output			= "";			
			$dashoard		= array();
			
			$this->constants['date_format'] = isset($this->constants['date_format']) ? $this->constants['date_format'] : get_option( 'date_format', "Y-m-d" );
			$date_format = $this->constants['date_format'];
			
			
			$dashboard_act_email_reporting	= $this->get_setting('dashboard_act_email_reporting',	$this->constants['plugin_options'], 0);
			
			if($dashboard_act_email_reporting == 0) return $output;
			
			$dashboard_total_summary 		= $this->get_setting('dashboard_total_summary',			$this->constants['plugin_options'], 0);
			$dashboard_today_summary 		= $this->get_setting('dashboard_today_summary',			$this->constants['plugin_options'], 0);
			$dashboard_order_summary 		= $this->get_setting('dashboard_order_summary',			$this->constants['plugin_options'], 0);
			$dashboard_sales_order_status 	= $this->get_setting('dashboard_sales_order_status',	$this->constants['plugin_options'], 0);
			$dashboard_top_products 		= $this->get_setting('dashboard_top_products',			$this->constants['plugin_options'], 0);
			$dashboard_top_billing_country 	= $this->get_setting('dashboard_top_billing_country',	$this->constants['plugin_options'], 0);
			$dashboard_top_payment_method 	= $this->get_setting('dashboard_top_payment_method',	$this->constants['plugin_options'], 0);
			$dashboard_top_coupons 			= $this->get_setting('dashboard_top_coupons',			$this->constants['plugin_options'], 0);
			$dashboard_top_customer 		= $this->get_setting('dashboard_top_customer',			$this->constants['plugin_options'], 0);
			$dashboard_top_recent_order 	= $this->get_setting('dashboard_top_recent_order',		$this->constants['plugin_options'], 0);
			
			
			if($dashboard_total_summary == 1){
				
				//Total Sales
				$_total_orders 			= $this->get_total_order('total',$shop_order_status,$hide_order_status,$start_date,$end_date);
				$total_orders 			= $this->get_value($_total_orders,'total_count',0);
				$total_sales 			= $this->get_value($_total_orders,'total_amount',0);
				$total_sales_avg		= $total_sales > 0 ? $total_sales/$total_orders : 0;
				
				$dashoard['total']['total_sales']['label'] 			= 'Total Sales';
				$dashoard['total']['total_sales']['first_value'] 	= $this->price($total_sales);
				$dashoard['total']['total_sales']['second_value'] 	= "#".$total_orders;
				$dashoard['total']['total_sales']['background'] 	= '#f37b53';
				
				
				$dashoard['total']['average_sales']['label'] 		= 'Average Sales';
				$dashoard['total']['average_sales']['first_value'] 	= $this->price($total_sales_avg);
				$dashoard['total']['average_sales']['second_value'] = '';
				$dashoard['total']['average_sales']['background'] 	= '#00a489';
				
				
				//Refund Start
				$total_refund 			= $this->get_total_by_status("total","refunded",$hide_order_status,$start_date,$end_date);
				$total_refund_amount 	= $this->get_value($total_refund,'total_amount',0);
				$total_refund_count 	= $this->get_value($total_refund,'total_count',0);
				
				$dashoard['total']['total_refund']['label'] 		= 'Total Refund';
				$dashoard['total']['total_refund']['first_value'] 	= $this->price($total_refund_amount);
				$dashoard['total']['total_refund']['second_value'] 	= "#".$total_refund_count;
				$dashoard['total']['total_refund']['background'] 	= '#f2b154';
				
				//Coupon Start
				$total_coupon 			= $this->get_total_of_coupon("total",$shop_order_status,$hide_order_status,$start_date,$end_date);
				$total_coupon_amount 	= $this->get_value($total_coupon,'total_amount',0);
				$total_coupon_count 	= $this->get_value($total_coupon,'total_count',0);
				
				$dashoard['total']['total_coupons']['label'] 		= 'Total Coupons';
				$dashoard['total']['total_coupons']['first_value'] 	= $this->price($total_coupon_amount);
				$dashoard['total']['total_coupons']['second_value'] = "#".$total_coupon_count;
				$dashoard['total']['total_coupons']['background'] 	= '#667ea3';
				
				$total_tax 				= $this->get_total_of_order("total","_order_tax","tax",$shop_order_status,$hide_order_status,$start_date,$end_date);
				$total_tax_amount 		= $this->get_value($total_tax,'total_amount',0);
				$total_tax_count 		= $this->get_value($total_tax,'total_count',0);
				
				$dashoard['total']['total_tax']['label'] 			= 'Total Tax';
				$dashoard['total']['total_tax']['first_value'] 		= $this->price($total_tax_amount);
				$dashoard['total']['total_tax']['second_value'] 	= "#".$total_tax_count;
				$dashoard['total']['total_tax']['background'] 		= '#959801';
				
				//Order Shipping Total
				$total_orders_shipping	= $this->get_total_order_shipping_sales('total',$shop_order_status,$hide_order_status,$start_date,$end_date);
				$dashoard['total']['total_shipping']['label'] 			= 'Order Shipping Total';
				$dashoard['total']['total_shipping']['first_value'] 	= $this->price($total_orders_shipping);
				$dashoard['total']['total_shipping']['second_value'] 	= "";
				$dashoard['total']['total_shipping']['background'] 		= '#61b9ff';
				
				//Total customers
				$users_of_blog 			= count_users();			
				$total_customer 		= isset($users_of_blog['avail_roles']['customer']) ? $users_of_blog['avail_roles']['customer'] : 0;
				$dashoard['total']['total_customer']['label'] 			= 'Total Customers';
				$dashoard['total']['total_customer']['first_value'] 	= $this->price($total_customer);
				$dashoard['total']['total_customer']['second_value'] 	= "";
				$dashoard['total']['total_customer']['background'] 		= '#de577b';
				
				//Last order Date
				$this->constants['datetime'] = date_i18n("Y-m-d H:i:s");
				$last_order_details 	= $this->get_last_order_details($shop_order_status,$hide_order_status,$start_date,$end_date);
				$last_order_date 		= $this->get_value($last_order_details,'last_order_date','');
				$last_order_time		= strtotime($last_order_date);			
				$date_format			= str_replace("F","M",$date_format);
				$current_time 			= strtotime($this->constants['datetime']);
				$last_order_time_diff	= $this->humanTiming($last_order_time, $current_time ,' ago');	
				
				
				$dashoard['total']['today_customer']['label'] 			= 'Last order Date';
				$dashoard['total']['today_customer']['first_value'] 	= isset($last_order_date)		? date_i18n($date_format,$last_order_time)	: 0;
				$dashoard['total']['today_customer']['second_value'] 	= isset($last_order_time_diff)	? $last_order_time_diff						: 0;
				$dashoard['total']['today_customer']['background'] 		= '#f35958';
			}
			
			if($dashboard_today_summary == 1){
				//Today Sales
				$_todays_orders 		= $this->get_total_order('today',$shop_order_status,$hide_order_status,$start_date,$end_date);			
				$total_today_order 		= $this->get_value($_todays_orders,'total_count',0);
				$total_today_sales 		= $this->get_value($_todays_orders,'total_amount',0);
				$total_today_avg		= $total_today_sales > 0 ? $total_today_sales/$total_today_order : 0;
				
				$dashoard['today']['total_sales']['label'] 			= 'Today Sales';
				$dashoard['today']['total_sales']['first_value'] 	= $this->price($total_today_sales);
				$dashoard['today']['total_sales']['second_value'] 	= "#".$total_today_order;
				$dashoard['today']['total_sales']['background'] 	= '#74b749';			
				
				$dashoard['today']['average_sales']['label'] 		= 'Today Average Sales';
				$dashoard['today']['average_sales']['first_value'] 	= $total_today_avg;
				$dashoard['today']['average_sales']['second_value'] = '';
				$dashoard['today']['average_sales']['background'] 	= '#ab8465';			
				
				//Refund Start
				$today_refund 			= $this->get_total_by_status("today","refunded",$hide_order_status,$start_date,$end_date);
				$todays_refund_amount 	= $this->get_value($today_refund,'total_amount',0);
				$todays_refund_count 	= $this->get_value($today_refund,'total_count',0);
				
				$dashoard['today']['total_refund']['label'] 		= 'Today Refund';
				$dashoard['today']['total_refund']['first_value'] 	= $this->price($todays_refund_amount);
				$dashoard['today']['total_refund']['second_value'] 	= "#".$todays_refund_count;
				$dashoard['today']['total_refund']['background'] 	= '#847cc5';
				
				
				//Coupon Start
				$today_coupon 			= $this->get_total_of_coupon("today",$shop_order_status,$hide_order_status,$start_date,$end_date);
				$today_coupon_amount 	= $this->get_value($today_coupon,'total_amount',0);
				$today_coupon_count 	= $this->get_value($today_coupon,'total_count',0);
				
				$dashoard['today']['total_coupons']['label'] 		= 'Today Coupons';
				$dashoard['today']['total_coupons']['first_value'] 	= $this->price($today_coupon_amount);
				$dashoard['today']['total_coupons']['second_value'] = "#".$todays_refund_count;
				$dashoard['today']['total_coupons']['background'] 	= '#43c3b8';
				
				//Tax Start
				$today_tax 				= $this->get_total_of_order("today","_order_tax","tax",$shop_order_status,$hide_order_status,$start_date,$end_date);
				$today_tax_amount 		= $this->get_value($today_tax,'total_amount',0);
				$today_tax_count 		= $this->get_value($today_tax,'total_count',0);
				
				$dashoard['today']['total_tax']['label'] 			= 'Today Tax';
				$dashoard['today']['total_tax']['first_value'] 		= $this->price($today_tax_amount);
				$dashoard['today']['total_tax']['second_value'] 	= "#".$today_tax_count;
				$dashoard['today']['total_tax']['background'] 		= '#77808a';
				
				$total_today_customer 	= $this->get_total_today_order_customer();
				$dashoard['today']['today_customer']['label'] 			= 'Today Customers';
				$dashoard['today']['today_customer']['first_value'] 	= $this->price($total_today_customer);
				$dashoard['today']['today_customer']['second_value'] 	= "";
				$dashoard['today']['today_customer']['background'] 		= '#f35958';
			}
			
			$output = '';
			if(isset($dashoard['total']) and count($dashoard['total'])>0){
				$output .= "\n";
				$output .= '							<tr><td valign="top" style="font-size:14px; font-weight:bold;padding-left:10px; padding-top:0px; padding-bottom:3px;">Total Summary</td></tr>';
				$output .= '							<tr>';
				$output .= '								<td valign="top">';			
				$output .= 										$this->summary_box($dashoard['total']);
				$output .= '                               </td>';
				$output .= '                            <tr>';
			}
			
			if(isset($dashoard['today']) and count($dashoard['today'])>0){
				$output .= "\n";
				$output .= '							<tr><td valign="top" style="font-size:14px; font-weight:bold;padding-left:10px; padding-top:8px; padding-bottom:3px;">Todays Summary</td></tr>';
				$output .= '							<tr>';
				$output .= '								<td valign="top">';			
				$output .= 										$this->summary_box($dashoard['today']);
				$output .= '                               </td>';
				$output .= '                            <tr>';
				$output .= '							<tr><td style="height:10px;"></td></tr>';
			}
			
			if($dashboard_order_summary == 1){
				$output .= "\n";
				$output .= '							<tr>';
				$output .= '								<td valign="top" style="padding:8px;">';			
				$output .= 										$reports->sales_order_count_value($shop_order_status,$hide_order_status,$start_date,$end_date);
				$output .= '                               </td>';
				$output .= '                            <tr>';
				$output .= '							<tr><td style="height:10px;"></td></tr>';
			}
			
			if($dashboard_sales_order_status == 1){
				$output .= "\n";
				$output .= '							<tr>';
				$output .= '								<td valign="top" style="padding:8px;">';			
				$output .= 										$reports->sales_order_status($shop_order_status,$hide_order_status,$start_date,$end_date);
				$output .= '                               </td>';
				$output .= '                            <tr>';
				$output .= '							<tr><td style="height:10px;"></td></tr>';
			}
			
			if($dashboard_top_products == 1){
				$output .= "\n";
				$output .= '							<tr>';
				$output .= '								<td valign="top" style="padding:8px;">';			
				$output .= 										$reports->top_product_list($shop_order_status,$hide_order_status,$start_date,$end_date);
				$output .= '                               </td>';
				$output .= '                            <tr>';
				$output .= '							<tr><td style="height:10px;"></td></tr>';
			}
			
			if($dashboard_top_billing_country == 1){
				$output .= "\n";
				$output .= '							<tr>';
				$output .= '								<td valign="top" style="padding:8px;">';			
				$output .= 										$reports->top_billing_country($shop_order_status,$hide_order_status,$start_date,$end_date);
				$output .= '                               </td>';
				$output .= '                            <tr>';
				$output .= '							<tr><td style="height:10px;"></td></tr>';
			}
			
			if($dashboard_top_payment_method == 1){
				$output .= "\n";
				$output .= '							<tr>';
				$output .= '								<td valign="top" style="padding:8px;">';			
				$output .= 										$reports->get_payment_gateway_list($shop_order_status,$hide_order_status,$start_date,$end_date);
				$output .= '                               </td>';
				$output .= '                            <tr>';
				$output .= '							<tr><td style="height:10px;"></td></tr>';
			}
			
			if($dashboard_top_coupons == 1){
				$output .= "\n";
				$output .= '							<tr>';
				$output .= '								<td valign="top" style="padding:8px;">';			
				$output .= 										$reports->get_top_coupon_list($shop_order_status,$hide_order_status,$start_date,$end_date);
				$output .= '                               </td>';
				$output .= '                            <tr>';
				$output .= '							<tr><td style="height:10px;"></td></tr>';
			}
			
			if($dashboard_top_customer == 1){
				$output .= "\n";
				$output .= '							<tr>';
				$output .= '								<td valign="top" style="padding:8px;">';			
				$output .= 										$reports->top_customer_list($shop_order_status,$hide_order_status,$start_date,$end_date);
				$output .= '                               </td>';
				$output .= '                            <tr>';
				$output .= '							<tr><td style="height:10px;"></td></tr>';
			}
			
			if($dashboard_top_recent_order == 1){
				$output .= "\n";
				$output .= '							<tr>';
				$output .= '								<td valign="top" style="padding:8px;">';			
				$output .= 										$reports->recent_orders($shop_order_status,$hide_order_status,$start_date,$end_date);
				$output .= '                               </td>';
				$output .= '                            <tr>';
				$output .= '							<tr><td style="height:10px;"></td></tr>';
			}
						
			$output_msg = $output;
			
			if(strlen($output_msg)>0){
				$this->constants['date_format'] = isset($this->constants['date_format']) ? $this->constants['date_format'] : get_option( 'date_format', "Y-m-d" );
				$date_format = $this->constants['date_format'];
				
				$order_start_date = date($date_format,strtotime($start_date));
				$order_end_date = date($date_format,strtotime($end_date));
											
				$output = '';
				$output .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
				$output .= '<html>';
				$output .= "\n";
				$output .= '<head>';
				$output .= '	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
				$output .= "	<title>{$html_subject}</title>";
				$output .= '</head>';
				$output .= "\n";
				$output .= '	<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">';
				$output .= '		<br />';
				$output .= '		<center>';
				$output .= "\n";
				$output .= '			<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">';
				$output .= '				<tr>';
				$output .= '					<td align="center" valign="top">';
				$output .= "\n";
				$output .= "&nbsp; <p>Order From: {$order_start_date} To: {$order_end_date}</p>";
				$output .= "\n";
				$output .= '						<table border="0" cellpadding="0" cellspacing="0" width="700" style="border:1px solid #dddddd;">';			
				$output .= 								$output_msg;
				$output .= '                         </table>';
				$output .= '					</td>';
				$output .= '				<tr>';
				$output .= '			</table>';
				$output .= "\n";
				$output .= '		</center>';
				$output .= "\n";
				$output .= '	</body>';
				$output .= '</html>';
			}
			
			return $output;
		}
		
		public function check_parent_plugin(){
			
				if(!isset($this->constants['plugin_parent'])) return '';
				$message 				= "";
				$msg 					= false;
				$this->plugin_parent 	= $this->constants['plugin_parent'];
				$action = "";
				
				
				$this->constants['plugin_parent_active'] 		=  false;
				$this->constants['plugin_parent_installed'] 	=  false;
				
				if(in_array( $this->plugin_parent['plugin_slug'], apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
					$this->constants['plugin_parent_active'] 		=  true;
					$this->constants['plugin_parent_installed'] 	=  true;
					
					//New Change ID 20140918
					$this->constants['parent_plugin_version']	= get_option('woocommerce_version',0);
					$this->constants['parent_plugin_db_version']= get_option('woocommerce_db_version',0);
					
					/*if(!defined('WOO_VERSION'))
					if(defined('WC_VERSION')) define('WOO_VERSION', WC_VERSION);else define('WOO_VERSION', '');
					
					if ( version_compare( $this->constants['parent_plugin_db_version'], '2.2.0', '>=' ) || WOO_VERSION == '2.2-bleeding' ) {
						if ( version_compare( $this->constants['parent_plugin_db_version'], '2.2.0', '<' ) || WOO_VERSION == '2.2-bleeding' ) {
							$this->constants['post_order_status_found']	= 0;
						}else{
							$this->constants['post_order_status_found']	= 1;
						}
					}else{
						$this->constants['post_order_status_found']	= 0;
					}*/	
					
					/*Added 2017-08-04*/
					$this->constants['post_order_status_found']	= apply_filters('ic_commerce_latest_woocommerce_version',1);				
					
				}else{
										
					$this->constants['plugin_parent_active'] =  false;
					if(is_dir(WP_PLUGIN_DIR.'/'.$this->plugin_parent['plugin_folder'] ) ) {
						$message = $this->constants['plugin_parent_installed'] =  true;
					}else{
						$message = $this->constants['plugin_parent_installed'] =  false;
					}
					return  $message;
				}
		}
		
		function define_constant(){
				global $icpluginkey, $icperpagedefault, $iccurrent_page, $wp_version;
				
				//New Change ID 20140918
				$this->constants['detault_stauts_slug'] 	= array("completed","on-hold","processing");
				$this->constants['detault_order_status'] 	= array("wc-completed","wc-on-hold","wc-processing");
				$this->constants['hide_order_status'] 		= array();
				
				$this->constants['sub_version'] 			= '20150129';
				$this->constants['last_updated'] 			= '20150129';
				$this->constants['customized'] 				= 'no';
				$this->constants['customized_date'] 		= '20150129';
				
				$this->constants['first_order_date'] 		= $this->first_order_date($this->constants['plugin_key']);
				$this->constants['total_shop_day'] 			= $this->get_total_shop_day($this->constants['plugin_key']);
				$this->constants['today_date'] 				= date_i18n("Y-m-d");
				
				$this->constants['post_status']				= $this->get_setting2('post_status',$this->constants['plugin_options'],array());
				$this->constants['hide_order_status']		= $this->get_setting2('hide_order_status',$this->constants['plugin_options'],$this->constants['hide_order_status']);
				$this->constants['start_date']				= $this->get_setting('start_date',$this->constants['plugin_options'],$this->constants['first_order_date']);
				$this->constants['end_date']				= $this->get_setting('end_date',$this->constants['plugin_options'],$this->constants['today_date']);
				
				$this->constants['wp_version'] 				= $wp_version;
				
				$file 										= $this->constants['plugin_file'];
				$this->constants['plugin_slug'] 			= plugin_basename( $file );
				$this->constants['plugin_file_name'] 		= basename($this->constants['plugin_slug']);
				$this->constants['plugin_file_id'] 			= basename($this->constants['plugin_slug'], ".php" );
				$this->constants['plugin_folder']			= dirname($this->constants['plugin_slug']);
				//$this->constants['plugin_url'] 			= WP_PLUGIN_URL ."/". $this->constants['plugin_folder'];//Removed 20141106
				$this->constants['plugin_url'] 				= plugins_url("", $file);//Added 20141106
				$this->constants['plugin_dir'] 				= WP_PLUGIN_DIR ."/". $this->constants['plugin_folder'];				
				$this->constants['http_user_agent'] 		= isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
				$this->constants['siteurl'] 				= site_url();//Added for SSL fix 20150212
				$this->constants['admin_page_url']			= $this->constants['siteurl'].'/wp-admin/admin.php';//Added for SSL fix 20150212
				
				
			}
			
			
		
		function get_total_order($type = 'total',$shop_order_status,$hide_order_status,$start_date,$end_date){
			global $wpdb;			
			$today_date 			= $this->today;
			
			$sql = "
			SELECT 
			count(*) AS 'total_count',
			SUM(postmeta1.meta_value) AS 'total_amount'	
			FROM {$wpdb->prefix}posts as posts ";
			if($this->constants['post_order_status_found'] == 0 ){
				if(count($shop_order_status)>0){
						$sql .= " 
						LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
						LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
				}
			}
			$sql .= " LEFT JOIN  {$wpdb->prefix}postmeta as postmeta1 ON postmeta1.post_id = posts.ID";
			$sql .= " WHERE  post_type='shop_order'";
			
			
			
			$sql .= " AND postmeta1.meta_key='_order_total'";
			
			if($type == "today") $sql .= " AND DATE(posts.post_date) = '{$today_date}'";
					
			if($this->constants['post_order_status_found'] == 0 ){
				if(count($shop_order_status)>0){
					$in_shop_order_status = implode(",",$shop_order_status);
					$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
				}
			}else{
				if(count($shop_order_status)>0){
					$in_shop_order_status		= implode("', '",$shop_order_status);
					$sql .= " AND  posts.post_status IN ('{$in_shop_order_status}')";
				}
			}
			
			if ($start_date != NULL &&  $end_date != NULL && $type != "today"){
				$sql .= " AND DATE(posts.post_date) BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			
			if(count($hide_order_status)>0){
				$in_hide_order_status		= implode("', '",$hide_order_status);
				$sql .= " AND  posts.post_status NOT IN ('{$in_hide_order_status}')";
			}
			
			$items =  $wpdb->get_row($sql);			
			return $items;
		}
		
		function get_total_by_status($type = 'today',$status = 'refunded',$hide_order_status,$start_date,$end_date)	{
			global $wpdb;
			$today = $this->today;
			$sql = "SELECT";
			
			$sql .= " SUM( postmeta.meta_value) As 'total_amount', count( postmeta.post_id) AS 'total_count'";
			$sql .= "  FROM {$wpdb->prefix}posts as posts";
			
			if($this->constants['post_order_status_found'] == 0 ){
				$sql .= "
				LEFT JOIN  {$wpdb->prefix}term_relationships as term_relationships ON term_relationships.object_id=posts.ID
				LEFT JOIN  {$wpdb->prefix}term_taxonomy as term_taxonomy ON term_taxonomy.term_taxonomy_id=term_relationships.term_taxonomy_id
				LEFT JOIN  {$wpdb->prefix}terms as terms ON terms.term_id=term_taxonomy.term_id";
				
				$date_field = ($status == 'refunded') ? "post_modified" : "post_date";
			}else{
				$status = "wc-".$status;
				$date_field = ($status == 'wc-refunded') ? "post_modified" : "post_date";
			}
			
			$sql .= "
			LEFT JOIN  {$wpdb->prefix}postmeta as postmeta ON postmeta.post_id=posts.ID
			WHERE postmeta.meta_key = '_order_total' AND posts.post_type='shop_order'";
			
			
						
			if($type == "today" || $type == "today") $sql .= " AND DATE(posts.{$date_field}) = '".$today."'";
			
			if ($start_date != NULL &&  $end_date != NULL && $type != "today"){
				$sql .= " AND DATE(posts.{$date_field}) BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			
			if($this->constants['post_order_status_found'] == 0 ){
				$sql .= " AND  terms.name IN ('{$status}')";
				if(strlen($status)>0){
					$sql .= " AND  terms.slug IN ('{$status}')";
				}
			}else{
				if(strlen($status)>0){
					$sql .= " AND  posts.post_status IN ('{$status}')";
				}
			}
			
			if(count($hide_order_status)>0){
				$in_hide_order_status		= implode("', '",$hide_order_status);
				$sql .= " AND  posts.post_status NOT IN ('{$in_hide_order_status}')";
			}
			
			if($this->constants['post_order_status_found'] == 0 ){
				$sql .= " Group BY terms.term_id ORDER BY total_amount DESC";
			}else{
				$sql .= " Group BY posts.post_status ORDER BY total_amount DESC";
			}
			
			return $wpdb->get_row($sql);
		
		}
		
		//New Change ID 20140918
		function get_total_of_coupon($type = "today",$shop_order_status,$hide_order_status,$start_date,$end_date){
				global $wpdb,$options;
				$today_date = $this->today;
				$sql = "SELECT
				SUM(woocommerce_order_itemmeta.meta_value) As 'total_amount', 
				Count(*) AS 'total_count' 
				FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items 
				LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id=woocommerce_order_items.order_item_id
				LEFT JOIN  {$wpdb->prefix}posts as posts ON posts.ID=woocommerce_order_items.order_id";
				
				if($this->constants['post_order_status_found'] == 0 ){
					if(count($shop_order_status)>0){
						$sql .= " 
						LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
						LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
					}
				}
				
				$sql .= "
				WHERE 
				woocommerce_order_items.order_item_type='coupon' 
				AND woocommerce_order_itemmeta.meta_key='discount_amount'
				AND posts.post_type='shop_order'
				";
				
				if($type == "today") $sql .= " AND DATE(posts.post_date) = '{$today_date}'";
				
				if($this->constants['post_order_status_found'] == 0 ){
					if(count($shop_order_status)>0){
						$in_shop_order_status = implode(",",$shop_order_status);
						$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
					}
				}else{
					if(count($shop_order_status)>0){
						$in_shop_order_status		= implode("', '",$shop_order_status);
						$sql .= " AND  posts.post_status IN ('{$in_shop_order_status}')";
					}
				}
				
				if ($start_date != NULL &&  $end_date != NULL && $type != "today"){
					$sql .= " AND DATE(posts.post_date) BETWEEN '{$start_date}' AND '{$end_date}'";
				}
				
				if(count($hide_order_status)>0){
					$in_hide_order_status		= implode("', '",$hide_order_status);
					$sql .= " AND  posts.post_status NOT IN ('{$in_hide_order_status}')";
				}
				
				
				return $order_items = $wpdb->get_row($sql); 
				
				///$this->print_array($order_items);
		}
		
		function get_total_of_order($type = "today", $meta_key="_order_tax",$order_item_type="tax",$shop_order_status,$hide_order_status,$start_date,$end_date){
			global $wpdb;
			$today_date = $this->today;
			
			$sql = "  SELECT";
			$sql .= " SUM(postmeta1.meta_value) AS 'total_amount'";
			$sql .= " ,count(woocommerce_order_items.order_id) AS 'total_count'";			
			$sql .= " FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items				
			LEFT JOIN  {$wpdb->prefix}postmeta as postmeta1 ON postmeta1.post_id=woocommerce_order_items.order_id";
			
			$sql .= " LEFT JOIN  {$wpdb->prefix}posts as posts ON posts.ID=	woocommerce_order_items.order_id";
			
			if($this->constants['post_order_status_found'] == 0 ){
				if(count($shop_order_status)>0){
					$sql .= " 
					LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
					LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
				}
			}
			
			$sql .= " WHERE postmeta1.meta_key = '{$meta_key}' AND woocommerce_order_items.order_item_type = '{$order_item_type}'";
			
			$sql .= " AND posts.post_type='shop_order' ";
			
			if($type == "today") $sql .= " AND DATE(posts.post_date) = '{$today_date}'";
			
			if($this->constants['post_order_status_found'] == 0 ){
				if(count($shop_order_status)>0){
					$in_shop_order_status = implode(",",$shop_order_status);
					$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
				}
			}else{
				if(count($shop_order_status)>0){
					$in_shop_order_status		= implode("', '",$shop_order_status);
					$sql .= " AND  posts.post_status IN ('{$in_shop_order_status}')";
				}
			}
			
			if ($start_date != NULL &&  $end_date != NULL && $type != "today"){
				$sql .= " AND DATE(posts.post_date) BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			
			if(count($hide_order_status)>0){
				$in_hide_order_status		= implode("', '",$hide_order_status);
				$sql .= " AND  posts.post_status NOT IN ('{$in_hide_order_status}')";
			}
			
			return $order_items = $wpdb->get_row($sql);
			
			
		}
		
		function get_total_order_shipping_sales($type = 'total',$shop_order_status,$hide_order_status,$start_date,$end_date){
			global $wpdb;
				
				$id = "_order_shipping";
				$sql = "
					SELECT 					
					SUM(postmeta2.meta_value)						as total
					,COUNT(posts.ID) 							as quantity
					FROM {$wpdb->prefix}posts as posts					
					LEFT JOIN	{$wpdb->prefix}postmeta as postmeta2 on postmeta2.post_id = posts.ID";
					
					if($this->constants['post_order_status_found'] == 0 ){
						if(count($shop_order_status)>0){
							$sql .= " 
							LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
							LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
						}
					}
					
					$sql .= " WHERE posts.post_type	= 'shop_order'";
					$sql .= " AND postmeta2.meta_value > 0";
					$sql .= " AND postmeta2.meta_key 	= '{$id}'";
					
					
					if($type == "today") $sql .= " AND DATE(posts.post_date) = '{$today_date}'";
					
					if($this->constants['post_order_status_found'] == 0 ){
						if(count($shop_order_status)>0){
							$in_shop_order_status = implode(",",$shop_order_status);
							$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
						}
					}else{
						if(count($shop_order_status)>0){
							$in_shop_order_status		= implode("', '",$shop_order_status);
							$sql .= " AND  posts.post_status IN ('{$in_shop_order_status}')";
						}
					}
					
					if ($start_date != NULL &&  $end_date != NULL && $type != "today"){
						$sql .= " AND DATE(posts.post_date) BETWEEN '{$start_date}' AND '{$end_date}'";
					}
					
					if(count($hide_order_status)>0){
						$in_hide_order_status		= implode("', '",$hide_order_status);
						$sql .= " AND  posts.post_status NOT IN ('{$in_hide_order_status}')";
					}
					
					$items =  $wpdb->get_row($sql);
					
					return isset($items->total) ? $items->total : 0;
		}
		
		function get_total_today_order_customer(){
			global $wpdb;
			//$sql = "SELECT count(postmeta.meta_value), posts.ID, posts.post_date, postmeta.meta_value as customer_user, users.user_registered
			$sql = "SELECT users.ID
			FROM {$wpdb->prefix}posts as posts
			LEFT JOIN  {$wpdb->prefix}postmeta as postmeta ON postmeta.post_id = posts.ID
			LEFT JOIN  {$wpdb->prefix}users as users ON users.ID = postmeta.meta_value
			WHERE  
			posts.post_type = 'shop_order' 
			AND postmeta.meta_value > 0
			AND postmeta.meta_key = '_customer_user'
			AND DATE(users.user_registered) = '{$this->today}'
			GROUP BY  postmeta.meta_value
			ORDER BY posts.post_date desc";
			
			$user =  $wpdb->get_results($sql);
			return count($user);
		}
		
		function get_last_order_details($shop_order_status,$hide_order_status,$start_date,$end_date){
			global $wpdb;
			
			$sql = "SELECT ";					
			$sql .= " posts.ID AS last_order_id, posts.post_date AS last_order_date, posts.post_status AS last_order_status, DATEDIFF('{$this->constants['datetime']}', posts.post_date) AS last_order_day, '{$this->constants['datetime']}' AS current_datetime" ;
			$sql .= " FROM {$wpdb->prefix}posts as posts";
			if($this->constants['post_order_status_found'] == 0 ){
				if(count($shop_order_status)>0){
					$sql .= " 
					LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
					LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
				}
			}
			$sql .= " WHERE  posts.post_type='shop_order'";
			
			if($this->constants['post_order_status_found'] == 0 ){
				if(count($shop_order_status)>0){
					$in_shop_order_status = implode(",",$shop_order_status);
					$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
				}
			}else{
				if(count($shop_order_status)>0){
					$in_shop_order_status		= implode("', '",$shop_order_status);
					$sql .= " AND  posts.post_status IN ('{$in_shop_order_status}')";
				}
			}
			
			if ($start_date != NULL &&  $end_date != NULL){
				$sql .= " AND DATE(posts.post_date) BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			
			if(count($hide_order_status)>0){
				$in_hide_order_status		= implode("', '",$hide_order_status);
				$sql .= " AND  posts.post_status NOT IN ('{$in_hide_order_status}')";
			}
			
			$sql .= " Order By posts.post_date DESC ";
			
			$sql .= " LIMIT 1";
			
			$wpdb->query("SET SQL_BIG_SELECTS=1");
			
			$order_items = $wpdb->get_row($sql);
			
			//$this->print_array($order_items);
			
			return $order_items;
		}
		
		function get_dasbboard_coumns($report_name = 'recent_order'){
			$columns		= array("page","Page Incorrect");
			if($report_name == "recent_order"){
				if($this->constants['post_order_status_found'] == 0 ){
					$columns 	= array(
						"order_id"									=> "Order ID"
						,"ic_commerce_order_billing_name"			=> "Name"
						,"billing_email"							=> "Email"
						,"order_date"								=> "Date"
						,"ic_commerce_order_status_name"			=> "Status"
						,"ic_commerce_order_item_count"				=> "Items"
						,"order_total"								=> "Net Amt."
					);
				}else{
					$columns 	= array(
						"order_id"									=> "Order ID"
						,"ic_commerce_order_billing_name"			=> "Name"
						,"billing_email"							=> "Email"
						,"order_date"								=> "Date"
						,"order_status"								=> "Status"
						,"ic_commerce_order_item_count"				=> "Items"
						,"order_total"								=> "Net Amt."
					);
				}
			}
			return $columns;
		}
		
		function get_rid($order_items = array(),$columns = array(),$output = "",$title = NULL){
			
			$colspan = count($columns);
			if(count($order_items) > 0):                	
					$output .= "\n";
					$output .= '<table  style="width:100%" class="widefat1"  style="border:1px solid #CACACA; width:100%; font-family:Arial;font-size:12px; color:#505050;" cellpadding="0" cellspacing="0">';
					$output .= "\n";
					$output .= '    <thead>';
					
					if($title){
						$output .= "\n";
						$output .= '<tr>';
						$output .= "\n\t<td colspan=\"{$colspan}\" style=\"background:#E8E8E8; padding:6px; font-weight:bold; font-size:14px; border-bottom:1px solid #CACACA;\">{$title}</td>";
						$output .= '</tr>';
					}
					
					$output .= "\n";
					$output .= '         <tr class="first">';                                
						$cells_status = array();
						foreach($columns as $key => $value):
							$td_class = $key;
							$th_align = "left";
							$td_width = "";
							switch($key):
								case "OrderTotal":
								case "order_total":
									$td_class .= " amount";
									$th_align = "right";
									$th_value = $value;
									$td_width = "width:120px;";
									break;
								case "OrderTotal":
								case "OrderCount":
								case "Qty":
								case "ic_commerce_order_status_id":
								case "ic_commerce_order_item_count":
								case "order_shipping":
								case "order_shipping_tax":
								case "order_tax":
								case "gross_amount":
								case "order_discount":
								case "order_total":										
								case "OrderTotal":
								case "item_count":
								case "OrderCount":
								case "Qty":
								case "coupon_amount":
									$td_class .= " amount";
									$th_align = "right";
									$th_value = $value;														
									break;
								default;
									$th_value = $value;
									$th_align = "left";
									break;
							endswitch;
							$output .= "\n\t<th class=\"{$td_class}\"  style=\"background:#F9EBCB; padding:6px; font-weight:bold; font-size:14px;{$td_width} text-align:{$th_align};\">{$th_value}</th>";
						endforeach;
					$output .= '		</tr>';
					$output .= "\n";
					$output .= '</thead>';
					$output .= "\n";
					$output .= '<tbody>';                            
					foreach ( $order_items as $key => $order_item ) {                                
						//$TotalAmount =  $TotalAmount + $order_item->order_total;
						//$TotalShipping = $TotalShipping + $order_item->order_shipping;
						$order_item->order_currency	=	isset($order_item->order_currency) ? $order_item->order_currency : $this->woocommerce_currency();
						$zero_prize[$order_item->order_currency] = isset($zero_prize[$order_item->order_currency]) ? $zero_prize[$order_item->order_currency] : $this->price(0, array('currency' => $order_item->order_currency));
						//$TotalOrderCount++;
						
						if($key%2 == 1){$alternate = "alternate ";}else{$alternate = "";};
						
						 $output .= "\n   <tr class=\"{$alternate}row_{$key}\">";
							
								foreach($columns as $key => $value):
									$td_class = $key;
									$td_align = "left";
									$td_width = "";
									switch($key):
										case "order_id":
										   //$td_value = '<a href="'.$admin_url.$order_item->order_id.'&detail_view=yes" target="'.$order_item->order_id.'_blank">' . $order_item->order_id  . '</a>';
											$td_value = isset($order_item->$key) ? $order_item->$key : 0;
											break;
										case "billing_name":
											$td_value = ucwords(stripslashes_deep($order_item->billing_name));
											break;
										case "billing_email":
											//$td_value = $this->emailLlink($order_item->billing_email,false);
											$td_value = isset($order_item->$key) ? $order_item->$key : '';
											break;													
										case "status":
										case "OrderStatus":
											//$td_value = '<span class="order-status order-status-'.sanitize_title($order_item->status_id).'">'.ucwords(__($order_item->$key, 'icwoocommerce_textdomains')).'</span>';
											$td_value = isset($order_item->$key) ? $order_item->$key : '';
											break;
										case "item_count":
										case "OrderCount":
										case "Qty":
										case "coupon_amount":
											$td_value = $order_item->$key;
											$td_class .= " amount";
											$td_align = "right";
											break;
										case "order_date":
											$this->constants['date_format'] = isset($this->constants['date_format']) ? $this->constants['date_format'] : get_option( 'date_format', "Y-m-d" );
											$date_format = $this->constants['date_format'];
											$td_value = date($date_format,strtotime($order_item->$key));
											break;
										case "OrderTotal":
										case "order_total":
											//$td_value = isset($order_item->$key) ? $order_item->$key : 0;
											//$td_value = $td_value > 0 ? $this->price($td_value, array('currency' => $order_item->order_currency)) : $zero_prize[$order_item->order_currency];
											//$td_class .= " amount";
											//$td_align = "right";
											//$td_width = "width:120px;";
											//break;
										case "order_shipping":
										case "order_shipping_tax":
										case "order_tax":
										case "gross_amount":
										case "order_discount":										
											$td_value = isset($order_item->$key) ? $order_item->$key : 0;
											$td_value = $td_value > 0 ? $this->price($td_value, array('currency' => $order_item->order_currency)) : $zero_prize[$order_item->order_currency];
											$td_class .= " amount";
											$td_align = "right";
											break;
										case "ic_commerce_order_status_name":
										case "ic_commerce_order_status_id":
											$td_value = isset($order_item->$key) ? $order_item->$key : $this->get_custom_field_data($order_item,$key);
											$td_value = '<span class="order-status order-status-'.sanitize_title($td_value).'">'.ucwords(__($td_value, 'icwoocommerce_textdomains')).'</span>';
											break;
										case "order_status":
											$td_value = $this->ic_get_order_status($order_item);
											$td_value = '<span class="order-status order-status-'.sanitize_title($td_value).'">'.ucwords(__($td_value, 'icwoocommerce_textdomains')).'</span>';
											break;
										case "ic_commerce_order_item_count":
											$td_value = isset($order_item->$key) ? $order_item->$key : $this->get_custom_field_data($order_item,$key);
											$td_class .= " amount";
											$td_align = "right";
											break;
										
										case "ic_commerce_order_billing_name":
										case "ic_commerce_order_tax_name":
										case "ic_commerce_order_coupon_codes":
										case "ic_commerce_order_item_count":
										case "ic_commerce_order_billing_sate":
										case "ic_commerce_order_shipping_sate":												
										case "ic_commerce_order_billing_country":
										case "ic_commerce_order_shipping_country":
											$td_value = isset($order_item->$key) ? $order_item->$key : $this->get_custom_field_data($order_item,$key);
											break;
										case "ic_commerce_order_status_name":
											$value = isset($order_item->$key) ? $order_item->$key : $this->get_custom_field_data($order_item,$key);
											$td_value = '<span class="order-status order-status-'.sanitize_title($value).'">'.ucwords(__($value, 'icwoocommerce_textdomains')).'</span>';
											break;
										case 'billing_country_code':
										case 'billing_country':
										case 'shipping_country':
											$country      	= $this->get_wc_countries();//Added 20150225														
											$td_value = isset($country->countries[$order_item->$key]) ? $country->countries[$order_item->$key]: $order_item->$key;
											break;
										case 'billing_state':
											$td_value =  $this->get_billling_state_name($order_item->billing_country,$order_item->billing_state);
											break;
										default:
											$td_value = isset($order_item->$key) ? $order_item->$key : $key.'';
											break;
									endswitch;
									$output .= "\n\t<td class=\"{$td_class}\" style=\"padding:6px; font-size:13px; border-bottom:1px solid #E7E6E6;{$td_width} text-align:{$td_align};\">{$td_value}</td>";
								endforeach;
						$output .= "\n";
						$output .=	"  </tr>";
					   $output .= "\n";
					} 
                    $output .= " </tbody>";
					$output .= "\n";
                    $output .= "</table>";
					$output .= "\n";
			else:
				$output .= "";
			endif;
			return $output;
		}
		
		function sales_order_count_value($shop_order_status,$hide_order_status,$start_date,$end_date){	
			global $wpdb;		
			$CDate = $this->today;
			$url_shop_order_status	= "";
			$in_shop_order_status	= "";
			
			$in_post_order_status	= "";
			
			if($this->constants['post_order_status_found'] == 0 ){
				if(count($shop_order_status)>0){
					$in_shop_order_status	= implode(",",$shop_order_status);
					$url_shop_order_status	= "&order_status_id=".$in_shop_order_status;
				}
			}else{
				if(count($shop_order_status)>0){
					$in_post_order_status	= implode("', '",$shop_order_status);
					
					$url_shop_order_status	= implode(",",$shop_order_status);
					$url_shop_order_status	= "&order_status=".$url_shop_order_status;
				}
				
			}
			
			
			$url_post_status = "";
			$in_post_status = "";
			$in_hide_order_status = "";
			$url_hide_order_status = "";
			if(count($hide_order_status)>0){
				$in_hide_order_status		= implode("', '",$hide_order_status);				
				
				$url_hide_order_status	= implode(",",$hide_order_status);
				$url_hide_order_status 	= "&hide_order_status=".$url_hide_order_status;						
			}	
			/*Today*/
			/*Today*/
			$sql = "SELECT 
					SUM(postmeta.meta_value)AS 'OrderTotal' 
					,COUNT(*) AS 'OrderCount'
					,'Today' AS 'SalesOrder'
					
					FROM {$wpdb->prefix}postmeta as postmeta 
					LEFT JOIN  {$wpdb->prefix}posts as posts ON posts.ID=postmeta.post_id";
					
					if(strlen($in_shop_order_status)>0){
						$sql .= " 
						LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
						LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
					}
					
					$sql .= " WHERE meta_key='_order_total' AND DATE(posts.post_date) = '".$CDate."'";
					
					$sql .= " AND posts.post_type IN ('shop_order')";
					
					if(strlen($in_shop_order_status)>0){
						$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
					}
					
					if(strlen($in_post_order_status)>0){
						$sql .= " AND  posts.post_status IN ('{$in_post_order_status}')";
					}
					
					if(strlen($in_hide_order_status)>0){
						$sql .= " AND  posts.post_status NOT IN ('{$in_hide_order_status}')";
					}
			$today_sql = $sql;
			$sql = '';
				 
			//$sql .= "	 UNION ";
			/*Yesterday*/
		    $sql = "	 SELECT 
					SUM(postmeta.meta_value)AS 'OrderTotal' 
					,COUNT(*) AS 'OrderCount'
					,'Yesterday' AS 'Sales Order'
					
					FROM {$wpdb->prefix}postmeta as postmeta 
					LEFT JOIN  {$wpdb->prefix}posts as posts ON posts.ID=postmeta.post_id";
					if(strlen($in_shop_order_status)>0){
						$sql .= " 
						LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
						LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
					}
					$sql .= " 					
					WHERE meta_key='_order_total' 
						AND  DATE(posts.post_date)= DATE(DATE_SUB(NOW(), INTERVAL 1 DAY))";
						
					$sql .= " AND posts.post_type IN ('shop_order')";
						
					if(strlen($in_shop_order_status)>0){
						$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
					}
					
					if(strlen($in_post_order_status)>0){
						$sql .= " AND  posts.post_status IN ('{$in_post_order_status}')";
					}
					
					if(strlen($in_hide_order_status)>0){
						$sql .= " AND  posts.post_status NOT IN ('{$in_hide_order_status}')";
					}
						
			$yesterday_sql = $sql;
			$sql = '';
				
			$sql = " SELECT 
					SUM(postmeta.meta_value)AS 'OrderTotal' 
					,COUNT(*) AS 'OrderCount'
					,'Week' AS 'Sales Order'
					
					FROM {$wpdb->prefix}postmeta as postmeta 
					LEFT JOIN  {$wpdb->prefix}posts as posts ON posts.ID=postmeta.post_id";
					if(strlen($in_shop_order_status)>0){
						$sql .= " 
						LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
						LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
					}
					$sql .= " 
					
					WHERE meta_key='_order_total' 
						
				 	AND WEEK(DATE(CURDATE())) = WEEK( DATE(posts.post_date))
					AND YEAR(DATE(CURDATE())) = YEAR(posts.post_date)
					";
					
					$sql .= " AND posts.post_type IN ('shop_order')";
					
					if(strlen($in_shop_order_status)>0){
						$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
					}
					
					if(strlen($in_post_order_status)>0){
						$sql .= " AND  posts.post_status IN ('{$in_post_order_status}')";
					}
					
					
					if(strlen($in_hide_order_status)>0){
						$sql .= " AND  posts.post_status NOT IN ('{$in_hide_order_status}')";
					}
					
			$week_sql = $sql;
			
			$sql = '';
			/*Month*/
			$sql = "SELECT 
					SUM(postmeta.meta_value)AS 'OrderTotal' 
					,COUNT(*) AS 'OrderCount'
					,'Month' AS 'Sales Order'
					
					FROM {$wpdb->prefix}postmeta as postmeta 
					LEFT JOIN  {$wpdb->prefix}posts as posts ON posts.ID=postmeta.post_id";
					if(strlen($in_shop_order_status)>0){
						$sql .= " 
						LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
						LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
					}
					$sql .= " 
					
					WHERE meta_key='_order_total' 
				 	AND MONTH(DATE(CURDATE())) = MONTH( DATE(posts.post_date))					
					AND YEAR(DATE(CURDATE())) = YEAR( DATE(posts.post_date))
					";
					
					$sql .= " AND posts.post_type IN ('shop_order')";
					
					if(strlen($in_shop_order_status)>0){
						$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
					}
					
					if(strlen($in_post_order_status)>0){
						$sql .= " AND  posts.post_status IN ('{$in_post_order_status}')";
					}
					
					
					if(strlen($in_hide_order_status)>0){
						$sql .= " AND  posts.post_status NOT IN ('{$in_hide_order_status}')";
					}
			$month_sql = $sql;
			$sql = '';
					
			/*Year*/
			$sql = "SELECT 
					SUM(postmeta.meta_value)AS 'OrderTotal' 
					,COUNT(*) AS 'OrderCount'
					,'Year' AS 'Sales Order'
					
					FROM {$wpdb->prefix}postmeta as postmeta 
					LEFT JOIN  {$wpdb->prefix}posts as posts ON posts.ID=postmeta.post_id";
					if(strlen($in_shop_order_status)>0){
						$sql .= " 
						LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
						LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
					}
					$sql .= " 					
					WHERE meta_key='_order_total' 
				 	AND YEAR(DATE(CURDATE())) = YEAR( DATE(posts.post_date))
					
					";
					
					$sql .= " AND posts.post_type IN ('shop_order')";
					
					if(strlen($in_shop_order_status)>0){
						$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
					}
					
					
					if(strlen($in_post_order_status)>0){
						$sql .= " AND  posts.post_status IN ('{$in_post_order_status}')";
					}
					
						
					if(strlen($in_hide_order_status)>0){
						$sql .= " AND  posts.post_status NOT IN ('{$in_hide_order_status}')";
					}
				$year_sql = $sql;
				
				
				$sql = '';				
				$sql .= $today_sql;
				$sql .= " UNION ";
				$sql .= $yesterday_sql;
				$sql .= " UNION ";
				$sql .= $week_sql;
				$sql .= " UNION ";
				$sql .= $month_sql;
				$sql .= " UNION ";
				$sql .= $year_sql;
				
				$order_items = $wpdb->get_results($sql );
				$output = '';
				if(count($order_items)>0):
					$output .= $this->get_rid($order_items, array("SalesOrder"=>"Sales Order","OrderCount"=>"Order Count","OrderTotal"=>"Amount"), NULL, "Order Summary");
				endif;
				
				return $output;
		}
		
		function sales_order_status($shop_order_status,$hide_order_status,$start_date,$end_date){
			
			global $wpdb;
			
			$sql = "SELECT
			
			COUNT(postmeta.meta_value) AS 'OrderCount'
			,SUM(postmeta.meta_value) AS 'OrderTotal'";
			
			if($this->constants['post_order_status_found'] == 0 ){
				$sql .= "  ,terms.name As 'Status', term_taxonomy.term_id AS 'StatusID'";
			
				$sql .= "  FROM {$wpdb->prefix}posts as posts";
				
				$sql .= "
				LEFT JOIN  {$wpdb->prefix}term_relationships as term_relationships ON term_relationships.object_id=posts.ID
				LEFT JOIN  {$wpdb->prefix}term_taxonomy as term_taxonomy ON term_taxonomy.term_taxonomy_id=term_relationships.term_taxonomy_id
				LEFT JOIN  {$wpdb->prefix}terms as terms ON terms.term_id=term_taxonomy.term_id";
			}else{
				$sql .= "  ,posts.post_status As 'Status' ,posts.post_status As 'StatusID'";
				$sql .= "  FROM {$wpdb->prefix}posts as posts";
			}
			
			$sql .= "
			LEFT JOIN  {$wpdb->prefix}postmeta as postmeta ON postmeta.post_id=posts.ID
			WHERE postmeta.meta_key = '_order_total'  AND posts.post_type='shop_order' ";
			
			if ($start_date != NULL &&  $end_date !=NULL){
				$sql .= " AND DATE(posts.post_date) BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			
				
			
			$url_hide_order_status = "";
			if(count($hide_order_status)>0){
				$in_hide_order_status		= implode("', '",$hide_order_status);
				$sql .= " AND  posts.post_status NOT IN ('{$in_hide_order_status}')";
				
				$url_hide_order_status	= implode(",",$hide_order_status);
				$url_hide_order_status = "&hide_order_status=".$url_hide_order_status;
			}
			
			if($this->constants['post_order_status_found'] == 0 ){
				$sql .= " AND  term_taxonomy.taxonomy = 'shop_order_status'";
			}
			
			if($this->constants['post_order_status_found'] == 0 ){
				$sql .= " Group BY terms.term_id ORDER BY OrderTotal DESC";
			}else{
				$sql .= " Group BY posts.post_status ORDER BY OrderTotal DESC";
			}
			
			$order_items = $wpdb->get_results($sql );
			if(count($order_items)>0):
					$admin_url 		= admin_url("admin.php?page=".$this->constants['plugin_key']."_details_page")."&end_date=".$end_date."&start_date=".$start_date."&detail_view=no{$url_hide_order_status}";	
					if($this->constants['post_order_status_found'] == 0 ){
						$admin_url 		.= "&order_status_id=";	
					}else{
						$admin_url 		.= "&order_status=";	
						
						if(function_exists('wc_get_order_statuses')){
							$order_statuses = wc_get_order_statuses();
						}else{
							$order_statuses = array();
						}
						
						foreach($order_items as $key  => $value){
							$order_status 					= isset($order_statuses[$value->Status]) ? $order_statuses[$value->Status] : $value->Status;
							$order_items[$key]->Status 		= $order_status;
							$order_items[$key]->OrderStatus = $order_status;							
							$order_items[$key]->status_id	= $value->StatusID;
						}
					}
			endif;
			$output = '';
			if(count($order_items)>0):
				$output .= $this->get_rid($order_items, array("OrderStatus"=>"Order Status","OrderCount"=>"Order Count","OrderTotal"=>"Amount"), NULL, "Sales Order Status");
			endif;			
			return $output;
						
		}
		
		function top_product_list($shop_order_status,$hide_order_status,$start_date,$end_date){
			global $wpdb,$options;
			
			$optionsid	= "top_product_per_page";					
			$per_page 	= $this->get_number_only($optionsid,$this->per_page_default);
			
			$sql = "
			SELECT 
			woocommerce_order_items.order_item_name			AS 'ItemName'
			,woocommerce_order_items.order_item_id
			,SUM(woocommerce_order_itemmeta.meta_value)		AS 'Qty'
			,SUM(woocommerce_order_itemmeta2.meta_value)	AS 'OrderTotal'
			,woocommerce_order_itemmeta3.meta_value			AS ProductID
									
			FROM 		{$wpdb->prefix}woocommerce_order_items 		as woocommerce_order_items
			LEFT JOIN	{$wpdb->prefix}posts						as posts 						ON posts.ID										=	woocommerce_order_items.order_id
			LEFT JOIN	{$wpdb->prefix}woocommerce_order_itemmeta 	as woocommerce_order_itemmeta 	ON woocommerce_order_itemmeta.order_item_id		=	woocommerce_order_items.order_item_id
			LEFT JOIN	{$wpdb->prefix}woocommerce_order_itemmeta 	as woocommerce_order_itemmeta2 	ON woocommerce_order_itemmeta2.order_item_id	=	woocommerce_order_items.order_item_id
			LEFT JOIN	{$wpdb->prefix}woocommerce_order_itemmeta 	as woocommerce_order_itemmeta3 	ON woocommerce_order_itemmeta3.order_item_id	=	woocommerce_order_items.order_item_id
			
			";
			if($this->constants['post_order_status_found'] == 0 ){
				if(count($shop_order_status)>0){
					$sql .= " 
					LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
					LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
				}
			}
			$sql .= "
			WHERE
			posts.post_type 								=	'shop_order'
			AND woocommerce_order_itemmeta.meta_key			=	'_qty'
			AND woocommerce_order_itemmeta2.meta_key		=	'_line_total' 
			AND woocommerce_order_itemmeta3.meta_key 		=	'_product_id'";
			
			$url_shop_order_status	= "";
			if($this->constants['post_order_status_found'] == 0 ){
				if(count($shop_order_status)>0){
					$in_shop_order_status = implode(",",$shop_order_status);
					$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
					
					$url_shop_order_status	= "&order_status_id=".$in_shop_order_status;
				}
			}else{
				if(count($shop_order_status)>0){
					$in_shop_order_status		= implode("', '",$shop_order_status);
					$sql .= " AND  posts.post_status IN ('{$in_shop_order_status}')";
					
					$url_shop_order_status	= implode(",",$shop_order_status);
					$url_shop_order_status	= "&order_status=".$url_shop_order_status;
				}
			}
			
			if ($start_date != NULL &&  $end_date !=NULL){
				$sql .= " AND DATE(posts.post_date) BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			
			$url_hide_order_status = "";
			if(count($hide_order_status)>0){
				$in_hide_order_status		= implode("', '",$hide_order_status);
				$sql .= " AND  posts.post_status NOT IN ('{$in_hide_order_status}')";
				
				$url_hide_order_status	= implode(",",$hide_order_status);
				$url_hide_order_status = "&hide_order_status=".$url_hide_order_status;
			}
			$sql .= " 
			
			GROUP BY  woocommerce_order_itemmeta3.meta_value
			Order By OrderTotal DESC
			LIMIT {$per_page}";
			$order_items = $wpdb->get_results($sql );
			
			$output = '';
			if(count($order_items)>0):
				$output .= $this->get_rid($order_items, array("ItemName"=>"Item Name","Qty"=>"Quantity","OrderTotal"=>"Amount"), NULL, "Top {$per_page} Products");
			endif;			
			return $output;
		}
		
		function top_billing_country($shop_order_status,$hide_order_status,$start_date,$end_date){
			global $wpdb,$options;
			$optionsid	= "top_billing_country_per_page";
			$per_page 	= $this->get_number_only($optionsid,$this->per_page_default);
		
			$sql = "
			SELECT SUM(postmeta1.meta_value) AS 'OrderTotal' 
			,postmeta2.meta_value AS 'billing_country'
			,Count(*) AS 'OrderCount'
			
			FROM {$wpdb->prefix}posts as posts
			LEFT JOIN  {$wpdb->prefix}postmeta as postmeta1 ON postmeta1.post_id=posts.ID
			LEFT JOIN  {$wpdb->prefix}postmeta as postmeta2 ON postmeta2.post_id=posts.ID";
			if($this->constants['post_order_status_found'] == 0 ){
				if(count($shop_order_status)>0){
					$sql .= " 
					LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
					LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
				}
			}
			$sql .= "
			WHERE
			posts.post_type			=	'shop_order'  
			AND postmeta1.meta_key	=	'_order_total' 
			AND postmeta2.meta_key	=	'_billing_country'";
			
			$url_shop_order_status	= "";
			if($this->constants['post_order_status_found'] == 0 ){
				if(count($shop_order_status)>0){
					$in_shop_order_status = implode(",",$shop_order_status);
					$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
					
					$url_shop_order_status	= "&order_status_id=".$in_shop_order_status;
				}
			}else{
				if(count($shop_order_status)>0){
					$in_shop_order_status		= implode("', '",$shop_order_status);
					$sql .= " AND  posts.post_status IN ('{$in_shop_order_status}')";
					
					$url_shop_order_status	= implode(",",$shop_order_status);
					$url_shop_order_status	= "&order_status=".$url_shop_order_status;
				}
			}
				
			if ($start_date != NULL &&  $end_date !=NULL){
				$sql .= " AND DATE(posts.post_date) BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			
			
			$url_hide_order_status = "";
			if(count($hide_order_status)>0){
				$in_hide_order_status		= implode("', '",$hide_order_status);
				$sql .= " AND  posts.post_status NOT IN ('{$in_hide_order_status}')";
				
				$url_hide_order_status	= implode(",",$hide_order_status);
				$url_hide_order_status = "&hide_order_status=".$url_hide_order_status;
			}
			$sql .= " 
			GROUP BY  postmeta2.meta_value 
			Order By OrderTotal DESC 						
			LIMIT {$per_page}";
			
			$order_items = $wpdb->get_results($sql); 
			
			
			$output = '';
			if(count($order_items)>0):
				$output .= $this->get_rid($order_items, array("billing_country"=>"Billing Country","OrderCount"=>"Order Count","OrderTotal"=>"Amount"), NULL, "Top {$per_page} Billing Country");
			endif;			
			return $output;
		}
		
		
		function top_billing_state($shop_order_status,$hide_order_status,$start_date,$end_date){
			global $wpdb,$options;
			$optionsid	= "top_billing_state_per_page";
			$per_page 	= $this->get_number_only($optionsid,$this->per_page_default);
		
			$sql = "
			SELECT SUM(postmeta1.meta_value) AS 'OrderTotal' 
			,postmeta2.meta_value AS 'billing_state'
			,postmeta3.meta_value AS 'billing_country'
			,Count(*) AS 'OrderCount'
			
			FROM {$wpdb->prefix}posts as posts
			LEFT JOIN  {$wpdb->prefix}postmeta as postmeta1 ON postmeta1.post_id=posts.ID
			LEFT JOIN  {$wpdb->prefix}postmeta as postmeta2 ON postmeta2.post_id=posts.ID
			LEFT JOIN  {$wpdb->prefix}postmeta as postmeta3 ON postmeta3.post_id=posts.ID";
			if($this->constants['post_order_status_found'] == 0 ){
				if(count($shop_order_status)>0){
					$sql .= " 
					LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
					LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
				}
			}
			$sql .= "
			WHERE
			posts.post_type			=	'shop_order'  
			AND postmeta1.meta_key	=	'_order_total' 
			AND postmeta2.meta_key	=	'_billing_state'
			AND postmeta3.meta_key	=	'_billing_country'";
			
			
			$url_shop_order_status	= "";
			if($this->constants['post_order_status_found'] == 0 ){
				if(count($shop_order_status)>0){
					$in_shop_order_status = implode(",",$shop_order_status);
					$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
					
					$url_shop_order_status	= "&order_status_id=".$in_shop_order_status;
				}
			}else{
				if(count($shop_order_status)>0){
					$in_shop_order_status		= implode("', '",$shop_order_status);
					$sql .= " AND  posts.post_status IN ('{$in_shop_order_status}')";
					
					$url_shop_order_status	= implode(",",$shop_order_status);
					$url_shop_order_status	= "&order_status=".$url_shop_order_status;
				}
			}
				
			if ($start_date != NULL &&  $end_date !=NULL){
				$sql .= " AND DATE(posts.post_date) BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			
			
			$url_hide_order_status = "";
			if(count($hide_order_status)>0){
				$in_hide_order_status		= implode("', '",$hide_order_status);
				$sql .= " AND  posts.post_status NOT IN ('{$in_hide_order_status}')";
				
				$url_hide_order_status	= implode(",",$hide_order_status);
				$url_hide_order_status = "&hide_order_status=".$url_hide_order_status;
			}
			$sql .= " 
			GROUP BY  postmeta2.meta_value 
			Order By OrderTotal DESC 						
			LIMIT {$per_page}";
			
			$order_items = $wpdb->get_results($sql); 
						
			$output = '';
			if(count($order_items)>0):
				$output .= $this->get_rid($order_items, array("billing_state"=>"Billing Country","OrderCount"=>"Order Count","OrderTotal"=>"Amount"), NULL, "Top {$per_page} Billing State");
			endif;			
			return $output;						
		}
		
		function get_payment_gateway_list($shop_order_status,$hide_order_status,$start_date,$end_date){
			global $wpdb,$options;
			$optionsid	= "top_payment_gateway_per_page";
			$per_page 	= $this->get_number_only($optionsid,$this->per_page_default);
			
			$sql = "
			SELECT postmeta2.meta_value AS 'payment_method_title' 
			,SUM(postmeta1.meta_value) AS 'OrderTotal'
			,COUNT(postmeta1.meta_value) As 'OrderCount'					
			FROM {$wpdb->prefix}posts as posts
			LEFT JOIN  {$wpdb->prefix}postmeta as postmeta1 ON postmeta1.post_id=posts.ID
			LEFT JOIN  {$wpdb->prefix}postmeta as postmeta2 ON postmeta2.post_id=posts.ID";
			if($this->constants['post_order_status_found'] == 0 ){
				if(count($shop_order_status)>0){
					$sql .= " 
					LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
					LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
				}
			}
				$sql .= "
			WHERE
			posts.post_type='shop_order'  
			AND postmeta1.meta_key='_order_total' 
			AND postmeta2.meta_key='_payment_method_title'
			";
					
				$url_shop_order_status	= "";
				if($this->constants['post_order_status_found'] == 0 ){
					if(count($shop_order_status)>0){
						$in_shop_order_status = implode(",",$shop_order_status);
						$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
						
						$url_shop_order_status	= "&order_status_id=".$in_shop_order_status;
					}
				}else{
					if(count($shop_order_status)>0){
						$in_shop_order_status		= implode("', '",$shop_order_status);
						$sql .= " AND  posts.post_status IN ('{$in_shop_order_status}')";
						
						$url_shop_order_status	= implode(",",$shop_order_status);
						$url_shop_order_status	= "&order_status=".$url_shop_order_status;
					}
				}
			
			if ($start_date != NULL &&  $end_date !=NULL){
				$sql .= " AND DATE(posts.post_date) BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			
			$url_hide_order_status = "";
			if(count($hide_order_status)>0){
				$in_hide_order_status		= implode("', '",$hide_order_status);
				$sql .= " AND  posts.post_status NOT IN ('{$in_hide_order_status}')";
				
				$url_hide_order_status	= implode(",",$hide_order_status);
				$url_hide_order_status = "&hide_order_status=".$url_hide_order_status;
			}
			$sql .= " 
			GROUP BY postmeta2.meta_value
			Order BY OrderTotal DESC LIMIT {$per_page}";
			
			$order_items = $wpdb->get_results($sql);
			
			$output = '';
			if(count($order_items)>0):
				$output .= $this->get_rid($order_items, array("payment_method_title"=>"Payment Method","OrderCount"=>"Order Count","OrderTotal"=>"Amount"), NULL, "Top {$per_page} Payment Method");
			endif;			
			return $output;
		}
		
		function recent_orders($shop_order_status,$hide_order_status,$start_date,$end_date){
			global $wpdb,$options;
			$optionsid	= "recent_order_per_page";
			$per_page 	= $this->get_number_only($optionsid,$this->per_page_default);
			
			$sql = "SELECT ";					
			$sql .= " posts.ID AS order_id, posts.post_date AS order_date, posts.post_status AS order_status";
			$sql .= " FROM {$wpdb->prefix}posts as posts";
			if($this->constants['post_order_status_found'] == 0 ){
				if(count($shop_order_status)>0){
					$sql .= " 
					LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
					LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
				}
			}
			$sql .= " WHERE  posts.post_type='shop_order'";
			
					$url_shop_order_status	= "";
					if($this->constants['post_order_status_found'] == 0 ){
						if(count($shop_order_status)>0){
							$in_shop_order_status = implode(",",$shop_order_status);
							$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
							
							$url_shop_order_status	= "&order_status_id=".$in_shop_order_status;
						}
					}else{
						if(count($shop_order_status)>0){
							$in_shop_order_status		= implode("', '",$shop_order_status);
							$sql .= " AND  posts.post_status IN ('{$in_shop_order_status}')";
							
							$url_shop_order_status	= implode(",",$shop_order_status);
							$url_shop_order_status	= "&order_status=".$url_shop_order_status;
						}
					}
			
			$url_hide_order_status = "";
			if(count($hide_order_status)>0){
				$in_hide_order_status		= implode("', '",$hide_order_status);
				$sql .= " AND  posts.post_status NOT IN ('{$in_hide_order_status}')";
				
				$url_hide_order_status	= implode(",",$hide_order_status);
				$url_hide_order_status = "&hide_order_status=".$url_hide_order_status;
			}
			
			$sql .= " GROUP BY posts.ID";
			
			$sql .= " Order By posts.post_date DESC ";
			$sql .= " LIMIT {$per_page}";
			
			$wpdb->query("SET SQL_BIG_SELECTS=1");
			$order_items = $wpdb->get_results($sql);
			
			if(count($order_items)>0){
				foreach ( $order_items as $key => $order_item ) {
						$order_id								= $order_item->order_id;
						
						if(!isset($order_meta[$order_id])){
							$order_meta[$order_id]					= $this->get_all_post_meta($order_id);
						}
						
						foreach($order_meta[$order_id] as $k => $v){
							$order_items[$key]->$k			= $v;
						}
						
						$order_items[$key]->billing_name	= $order_items[$key]->billing_first_name.' '.$order_items[$key]->billing_last_name;
						$order_items[$key]->gross_amount 	= ($order_items[$key]->order_total + $order_items[$key]->order_discount ) - ($order_items[$key]->order_shipping +  $order_items[$key]->order_shipping_tax + $order_items[$key]->order_tax );
				}						
			}
			$columns = $this->get_dasbboard_coumns('recent_order');
			
			$output = '';
			if(count($order_items)>0):
				$output .= $this->get_rid($order_items, $columns, NULL, "Top {$per_page} Recent Order");
			endif;			
			return $output;
		}
		
		function top_customer_list($shop_order_status,$hide_order_status,$start_date,$end_date){
			global $wpdb,$options;
				$optionsid	= "top_customer_per_page";
				$per_page 	= $this->get_number_only($optionsid,$this->per_page_default);
				
				$sql = "SELECT SUM(postmeta1.meta_value) AS 'OrderTotal' 
								,postmeta2.meta_value AS 'billing_email'
								,postmeta3.meta_value AS 'FirstName'
								,postmeta5.meta_value AS 'LastName'
								,CONCAT(postmeta3.meta_value, ' ',postmeta5.meta_value) AS billing_name
								,Count(postmeta2.meta_value) AS 'OrderCount'";
						
						//$sql .= " ,postmeta4.meta_value AS  customer_id";
						//
						$sql .= " FROM {$wpdb->prefix}posts as posts
						LEFT JOIN  {$wpdb->prefix}postmeta as postmeta1 ON postmeta1.post_id=posts.ID
						LEFT JOIN  {$wpdb->prefix}postmeta as postmeta2 ON postmeta2.post_id=posts.ID
						LEFT JOIN  {$wpdb->prefix}postmeta as postmeta3 ON postmeta3.post_id=posts.ID
						LEFT JOIN  {$wpdb->prefix}postmeta as postmeta5 ON postmeta5.post_id=posts.ID";
						
						//$sql .= " LEFT JOIN  {$wpdb->prefix}postmeta as postmeta4 ON postmeta4.post_id=posts.ID";
						
						if($this->constants['post_order_status_found'] == 0 ){
							if(count($shop_order_status)>0){
								$sql .= " 
								LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
								LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
							}
						}
						$sql .= " 
						WHERE  
							posts.post_type='shop_order'  
							AND postmeta1.meta_key='_order_total' 
							AND postmeta2.meta_key='_billing_email'  
							AND postmeta3.meta_key='_billing_first_name'
							AND postmeta5.meta_key='_billing_last_name'";
							
					//$sql .= " AND postmeta4.meta_key='_customer_user'";
							
						$url_shop_order_status	= "";
						if($this->constants['post_order_status_found'] == 0 ){
							if(count($shop_order_status)>0){
								$in_shop_order_status = implode(",",$shop_order_status);
								$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
								
								$url_shop_order_status	= "&order_status_id=".$in_shop_order_status;
							}
						}else{
							if(count($shop_order_status)>0){
								$in_shop_order_status		= implode("', '",$shop_order_status);
								$sql .= " AND  posts.post_status IN ('{$in_shop_order_status}')";
								
								$url_shop_order_status	= implode(",",$shop_order_status);
								$url_shop_order_status	= "&order_status=".$url_shop_order_status;
							}
						}
						
						if ($start_date != NULL &&  $end_date !=NULL){
							$sql .= " AND DATE(posts.post_date) BETWEEN '{$start_date}' AND '{$end_date}'";
						}
						
						
						$url_hide_order_status = "";
						if(count($hide_order_status)>0){
							$in_hide_order_status		= implode("', '",$hide_order_status);
							$sql .= " AND  posts.post_status NOT IN ('{$in_hide_order_status}')";
							
							$url_hide_order_status	= implode(",",$hide_order_status);
							$url_hide_order_status = "&hide_order_status=".$url_hide_order_status;
						}
						$sql .= " GROUP BY  postmeta2.meta_value
						Order By OrderTotal DESC
						LIMIT {$per_page}";
						
				$order_items = $wpdb->get_results($sql );
				
				$columns = array(
					"billing_name"	=>	"Billing Name",
					"billing_email"	=>	"Billing Email",
					"OrderCount"	=>	"Order Count",
					"OrderTotal"	=>	"Amount"
				);
				
				$output = '';
				if(count($order_items)>0):
					$output .= $this->get_rid($order_items, $columns, NULL, "Top {$per_page} Customer");
				endif;			
				return $output;
				
			}
			
			function get_top_coupon_list($shop_order_status,$hide_order_status,$start_date,$end_date){
					global $wpdb,$options;

					$optionsid	= "top_coupon_per_page";
					$per_page 	= $this->get_number_only($optionsid,$this->per_page_default);
					$sql = "SELECT *, 
					woocommerce_order_items.order_item_name, 
					SUM(woocommerce_order_itemmeta.meta_value) As 'OrderTotal', 
					woocommerce_order_itemmeta.meta_value AS 'coupon_amount' , 
					Count(*) AS 'OrderCount' 
					FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items 
					LEFT JOIN	{$wpdb->prefix}posts						as posts 						ON posts.ID										=	woocommerce_order_items.order_id
					LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta 	as woocommerce_order_itemmeta	ON woocommerce_order_itemmeta.order_item_id		=	woocommerce_order_items.order_item_id";
					if($this->constants['post_order_status_found'] == 0 ){
						if(count($shop_order_status)>0){
							$sql .= " 
							LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
							LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
						}
					}
					$sql .= "
					
					WHERE 
					posts.post_type 								=	'shop_order'
					AND woocommerce_order_items.order_item_type		=	'coupon' 
					AND woocommerce_order_itemmeta.meta_key			=	'discount_amount'";
							
					$url_shop_order_status	= "";
					if($this->constants['post_order_status_found'] == 0 ){
						if(count($shop_order_status)>0){
							$in_shop_order_status = implode(",",$shop_order_status);
							$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
							
							$url_shop_order_status	= "&order_status_id=".$in_shop_order_status;
						}
					}else{
						if(count($shop_order_status)>0){
							$in_shop_order_status		= implode("', '",$shop_order_status);
							$sql .= " AND  posts.post_status IN ('{$in_shop_order_status}')";
							//$this->print_array($shop_order_status);
							
							$url_shop_order_status	= implode(",",$shop_order_status);
							$url_shop_order_status	= "&order_status=".$url_shop_order_status;
						}
					}
					
					if ($start_date != NULL &&  $end_date !=NULL){
						$sql .= " AND DATE(posts.post_date) BETWEEN '{$start_date}' AND '{$end_date}'";
					}
					
					
					$url_hide_order_status = "";
					if(count($hide_order_status)>0){
						$in_hide_order_status		= implode("', '",$hide_order_status);
						$sql .= " AND  posts.post_status NOT IN ('{$in_hide_order_status}')";
						
						$url_hide_order_status	= implode(",",$hide_order_status);
						$url_hide_order_status = "&hide_order_status=".$url_hide_order_status;
					}
					$sql .= " 
					Group BY woocommerce_order_items.order_item_name
					ORDER BY OrderTotal DESC
					LIMIT {$per_page}";
					
					$columns = array(
						"order_item_name"	=>	"Coupon Code",
						"coupon_amount"	=>	"Coupon Amount",
						"OrderCount"	=>	"Coupon Count",
						"OrderTotal"	=>	"Amount"
					);
					
					$order_items = $wpdb->get_results($sql); 
					
					$output = '';
					if(count($order_items)>0):
						$output .= $this->get_rid($order_items, $columns, NULL, "Top {$per_page} Coupons");
					endif;			
					return $output;					
			}
			
			function summary_box($dashoard = array(), $row_per_column = 4, $output = "\n"){
				
				if(count($dashoard)>0){
					
					$cell			= 0;
					$cell_data		= array();
					$total_box 		= count($dashoard);				
					$td_width 		= 100/$row_per_column;				
					
					foreach($dashoard as $key => $value){
						$output_td =  "\n\t<td valign=\"top\" style=\"background:{$value['background']}; color:#fff; width:{$td_width}%; padding:5px; font-weight:bold;\">";				
						$output_td .=  "<p style=\"text-transform:uppercase; font-size:12px; margin:0; padding:0; margin-bottom:8px;\">{$value['label']}</p>";				
						$output_td .=  "<p style=\"font-size:16px; margin:0; padding:0; margin-bottom:8px;\">{$value['first_value']}</p>";
						$output_td .=  "<p style=\"text-align:right; margin:0; padding:0\">{$value['second_value']}</p>";
						$output_td .=  "</td>";				
						$cell_data[] = $output_td;
					}
					
					$output .= '<table cellpadding="0" cellspacing="8" style="font-family:Arial;font-size:12px; color:#505050; width:100%">';
					
					for($r=0;$r<=$total_box;$r++){
						$output .= "\n<tr>";
						for($c=1;$c<=$row_per_column;$c++){
							$output .= isset($cell_data[$cell]) ? $cell_data[$cell] : "\n\t<td></td>";
							$cell++;
						}
						$output .= "\n</tr>";					
						$r = $cell - 1;
					}
					
					$output .= "\n</table>";
				}
				
				return $output;
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
			
		
	}//End Class
}//End Check Class Exist