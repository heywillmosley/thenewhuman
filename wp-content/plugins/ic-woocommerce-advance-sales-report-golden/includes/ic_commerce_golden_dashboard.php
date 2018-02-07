<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
require_once('ic_commerce_golden_functions.php');


if ( ! class_exists( 'IC_Commerce_Golden_Dashboard' ) ) {
	class IC_Commerce_Golden_Dashboard extends IC_Commerce_Golden_Functions{
		
		public $per_page = 0;
		
		public $per_page_default = 5;
		
		public $constants 	=	array();
		
		public $today 		=	'';		
		
		public function __construct($constants) {
			global $options;
			
			$this->constants		= $constants;	
			$options				= $this->constants['plugin_options'];			
			$this->per_page			= $this->constants['per_page_default'];
			$this->per_page_default	= $this->constants['per_page_default'];
			$this->today			= $this->constants['today_date'];//New Change ID 20140918
			$this->constants['datetime']= date_i18n("Y-m-d H:i:s");//New Change ID 20140918
			//$this->get_refund_amount(49);
		}
		
		function init(){
			global $options;
			
			if(!isset($_REQUEST['page'])) return false;
			
			//if(!$this->constants['plugin_parent_active']) return false;
			
			$this->is_active();
			
			global $start_date, $end_date, $woocommerce, $wpdb, $wp_locale;
			//New Change ID 20140918
			
			if(isset($_REQUEST['start_date']) and empty($_REQUEST['start_date']) == false){
				update_option($this->constants['plugin_key'].'_dashboard_page_saved_start_date',$_REQUEST['start_date']);
			}
			
			$saved_start_date 		= get_option($this->constants['plugin_key'].'_dashboard_page_saved_start_date',$this->constants['start_date']);
			
			$start_date				= empty($_REQUEST['start_date']) ? $saved_start_date : $_REQUEST['start_date'];
			$end_date				= empty($_REQUEST['end_date']) ? $this->constants['end_date'] : $_REQUEST['end_date'];
			
			//New Change ID 20140918
			$shop_order_status		= apply_filters('ic_commerce_dashboard_page_default_order_status',$this->get_set_status_ids(),$this->constants);	
			$hide_order_status 		= apply_filters('ic_commerce_dashboard_page_default_hide_order_status',$this->constants['hide_order_status'],$this->constants);
			$start_date 			= apply_filters('ic_commerce_dashboard_page_default_start_date',$start_date,$this->constants);
			$end_date 				= apply_filters('ic_commerce_dashboard_page_default_end_date',$end_date,$this->constants);
			
			$this->yesterday 		= '';//date("Y-m-d",strtotime("-1 day",strtotime($this->today)));
			$summary_start_date 	= $start_date;//New Change ID 20150209
			$summary_end_date 		= $end_date;//New Change ID 20150209
			
			$total_part_refund_amt	= $this->get_part_order_refund_amount('total',$shop_order_status,$hide_order_status,$start_date,$end_date);
			$today_part_refund_amt	= $this->get_part_order_refund_amount('today',$shop_order_status,$hide_order_status,$start_date,$end_date);
			
			$_total_orders 			= $this->get_total_order('total',$shop_order_status,$hide_order_status,$start_date,$end_date);
			$total_orders 			= $this->get_value($_total_orders,'total_count',0);
			$total_sales 			= $this->get_value($_total_orders,'total_amount',0);
			$total_sales			= $total_sales - $total_part_refund_amt;			
			//$total_sales_avg		= $total_sales > 0 ? $total_sales/$total_orders : 0;
			$total_sales_avg		= $this->get_average($total_sales,$total_orders);//Modified Change ID 20150210
			
			$_todays_orders 		= $this->get_total_order('today',$shop_order_status,$hide_order_status,$start_date,$end_date);			
			$total_today_order 		= $this->get_value($_todays_orders,'total_count',0);
			$total_today_sales 		= $this->get_value($_todays_orders,'total_amount',0);
			$total_today_sales		= $total_today_sales - $today_part_refund_amt;
			//$total_today_avg		= $total_today_sales > 0 ? $total_today_sales/$total_today_order : 0;
			$total_today_avg		= $this->get_average($total_today_sales,$total_today_order);//Modified Change ID 20150210
			
			//$total_categories  		= $this->get_total_categories_count();
			//$total_products  		= $this->get_total_products_count();
			$total_orders_shipping	= $this->get_total_order_shipping_sales('total',$shop_order_status,$hide_order_status,$start_date,$end_date);
		
			
			$total_refund 			= $this->get_total_by_status("total","refunded",$hide_order_status,$start_date,$end_date);
			$today_refund 			= $this->get_total_by_status("today","refunded",$hide_order_status,$start_date,$end_date);
			
			
			$full_refund_amount 	= $this->get_value($total_refund,'total_amount',0);
			$total_refund_count 	= $this->get_value($total_refund,'total_count',0);
			
			$total_refund_amount	= $full_refund_amount + $total_part_refund_amt;
			
			$todays_refund_amount 	= $this->get_value($today_refund,'total_amount',0);
			$todays_refund_count 	= $this->get_value($today_refund,'total_count',0);
			
			$todays_refund_amount	= $todays_refund_amount + $today_part_refund_amt;
			
			$today_coupon 			= $this->get_total_of_coupon("today",$shop_order_status,$hide_order_status,$start_date,$end_date);
			$total_coupon 			= $this->get_total_of_coupon("total",$shop_order_status,$hide_order_status,$start_date,$end_date);
			
			$today_coupon_amount 	= $this->get_value($today_coupon,'total_amount',0);
			$today_coupon_count 	= $this->get_value($today_coupon,'total_count',0);
			
			$total_coupon_amount 	= $this->get_value($total_coupon,'total_amount',0);
			$total_coupon_count 	= $this->get_value($total_coupon,'total_count',0);
			
			$today_tax 				= $this->get_total_of_order("today","_order_tax","tax",$shop_order_status,$hide_order_status,$start_date,$end_date);
			$total_tax 				= $this->get_total_of_order("total","_order_tax","tax",$shop_order_status,$hide_order_status,$start_date,$end_date);
			
			$today_tax_amount 		= $this->get_value($today_tax,'total_amount',0);
			$today_tax_count 		= $this->get_value($today_tax,'total_count',0);
			
			$total_tax_amount 		= $this->get_value($total_tax,'total_amount',0);
			$total_tax_count 		= $this->get_value($total_tax,'total_count',0);
			
			//New Change ID 20140918 Start
			$last_order_details 	= $this->get_last_order_details($shop_order_status,$hide_order_status,$start_date,$end_date);
			
			$last_order_date 		= $this->get_value($last_order_details,'last_order_date','');
			$last_order_time		= strtotime($last_order_date);
			
			//$last_order_day 		= $this->get_value($last_order_details,'last_order_day','0');			
			$date_format			= str_replace("F","M",get_option( 'date_format', "Y-m-d" ));
			
			
			$current_time 			= strtotime($this->constants['datetime']);
			$last_order_time_diff	= $this->humanTiming($last_order_time, $current_time ,' ago');			
			
			//Total customers
			$users_of_blog 			= count_users();			
			$total_customer 		= isset($users_of_blog['avail_roles']['customer']) ? $users_of_blog['avail_roles']['customer'] : 0;
			$total_today_customer 	= $this->get_total_today_order_customer();
			//New Change ID 20140918 END
			
			//global $ic_commerce_schedule_golden;
			//echo $ic_commerce_schedule_golden->getEmailData('20140901','20140901','Monthly',$shop_order_status,$hide_order_status);
			
			
			
			//$default_date_rage_start_date	= isset($this->constants['default_date_rage_start_date']) ? $this->constants['default_date_rage_start_date'] : $this->constants['start_date'];
			//$default_date_rage_end_date		= isset($this->constants['default_date_rage_end_date']) ? $this->constants['default_date_rage_end_date'] : date_i18n('Y-12-31',strtotime('this month'));			
			//$current_date 					= date_i18n("Y-m-d");
			//$quick_date_change 				= $this->get_quick_dates($default_date_rage_start_date,$default_date_rage_end_date,$current_date);
			
			//$label = $this->get_labels();
			
			//$this->print_array($this->constants['plugin_options']);
			$show_sections				= $this->get_setting('show_sections',			$this->constants['plugin_options'], 0);
			$show_graph					= $this->get_setting('show_graph',				$this->constants['plugin_options'], 0);
			$show_order_summary			= $this->get_setting('show_order_summary',		$this->constants['plugin_options'], 0);
			$show_sales_order_status	= $this->get_setting('show_sales_order_status',	$this->constants['plugin_options'], 0);
			$show_top_products			= $this->get_setting('show_top_products',		$this->constants['plugin_options'], 0);
			$show_top_countries			= $this->get_setting('show_top_countries',		$this->constants['plugin_options'], 0);
			$show_top_payments			= $this->get_setting('show_top_payments',		$this->constants['plugin_options'], 0);
			$show_recent_orders			= $this->get_setting('show_recent_orders',		$this->constants['plugin_options'], 0);
			$show_top_customers			= $this->get_setting('show_top_customers',		$this->constants['plugin_options'], 0);
			$show_top_coupons			= $this->get_setting('show_top_coupons',		$this->constants['plugin_options'], 0);
			
			?>
				
				<div class="dashboard_filters success">
					<form method="post">                            
						<div class="form-table">
							<div class="form-group">
								<div class="FormRow">
									<div class="label-text"><label for="start_date">Start Date:</label></div>
									<div class="input-text"><input type="text" name="start_date" id="start_date" value="<?php echo $start_date?>" /></div>
								</div>
								<div class="FormRow">
									<div class="label-text"><label for="end_date">End Date:</label></div>
									<div class="input-text"><input type="text" name="end_date" id="end_date" value="<?php echo $end_date?>" /></div>
								</div>
								<div class="submit_buttons"><input type="submit" name="dashboard_btn" class="button onformprocess" id="dashboard_btn" value="Submit" /></div>
							</div>
						</div>                               
						
						<script type="text/javascript">
							jQuery(document).ready(function($) {
								jQuery( "#start_date" ).datepicker({
									dateFormat : 'yy-mm-dd',
									changeMonth: true,
									changeYear: true,
									maxDate:ic_commerce_vars['max_date_start_date'],
									onClose: function( selectedDate ) {
										$( "#end_date" ).datepicker( "option", "minDate", selectedDate );
									},beforeShow: function() {
										setTimeout(function(){
											$('.ui-datepicker').css('z-index', 99999999999999);
										}, 0);
									}
								});							
								
								jQuery( "#end_date" ).datepicker({
									dateFormat : 'yy-mm-dd',
									changeMonth: true,
									changeYear: true,
									onClose: function( selectedDate ) {
										$( "#start_date" ).datepicker( "option", "maxDate", selectedDate );
									},beforeShow: function() {
										setTimeout(function(){
											$('.ui-datepicker').css('z-index', 99999999999999);
										}, 0);
									}
								}); 
							});
						</script>
					</form>
				</div>
				 <div id="poststuff" class="woo_cr-reports-wrap">
					<div class="woo_cr-reports-top">
                    	<div class="row">
                        	<div class="icpostbox">
                            	<h3><span><?php _e( 'Summary', 'icwoocommerce_textdomains' ); ?></span></h3>
                                <div class="clearfix"></div>
								<div class="SubTitle"><span><?php echo sprintf(__('Summary From %1$s To %2$s'), date($date_format, strtotime($summary_start_date)),date($date_format, strtotime($summary_end_date))); ?></span></div>
								<div class="clearfix"></div>
                                
                                <!--<div class="ic_dashboard_summary_title_form">
                                	<div class="ic_dashboard_summary_title">
                                        <label for="">Start Date:</label> <strong><?php //echo $default_date_rage_start_date;?></strong>
                                        <label for="">End Date:</label> <strong><?php //echo $default_date_rage_end_date;?></strong>                                        
                                    </div>
                                    <div class="ic_dashboard_summary_form">
                                        <label for="">Start Date:</label> <input type="text" name="default_date_rage_start_date" id="default_date_rage_start_date" value="<?php //echo $default_date_rage_start_date;?>" />
                                        <label for="">End Date:</label> <input type="text" name="default_date_rage_end_date" id="default_date_rage_end_date" value="<?php //echo $default_date_rage_end_date;?>" />
                                        <select class="quick_date_change" id="quick_date_change" name="quick_date_change">
                                        	<option value="<?php //echo $default_date_rage_start_date."to".$default_date_rage_end_date;?>" data-start_date="<?php //echo $default_date_rage_start_date;?>" data-end_date="<?php //echo $default_date_rage_end_date;?>">Default</option>
											<?php
                                            	/*foreach($quick_date_change as $key => $value):
													echo "\n<option value=\"{$value['start_date']}to{$value['end_date']}\" data-start_date=\"{$value['start_date']}\" data-end_date=\"{$value['end_date']}\">{$key}</option>";
												endforeach;*/
											?>
                                            <option value="custom" data-start_date="" data-end_date="">Custom</option>
                                        </select>
                                    </div>
                                    <div class="ic_dashboard_summary_change">
                                    	<span>Change</span>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>-->
                                
                                <div class="block block-orange">
                                	<div class="block-content">
                                        <img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/sales-icon.png" alt="" />
                                        <p class="stat">
											<?php if ( $total_sales > 0 ) echo $this->price($total_sales); else _e( '0', 'icwoocommerce_textdomains' ); ?>
                                        	<span class="count">#<?php if ( $total_orders > 0 ) echo $total_orders; else _e( '0', 'icwoocommerce_textdomains' ); ?></span>
                                        </p>
                                   	</div>
                                    <h2><span><?php _e( 'Total Sales', 'icwoocommerce_textdomains' ); ?></span></h2>
                                </div>
                                <div class="block block-green">
                                	<div class="block-content">
                                        <img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/average-icon.png" alt="" />
                                        <p class="stat"><?php if ( $total_sales_avg > 0 ) echo $this->price($total_sales_avg); else _e( '0', 'icwoocommerce_textdomains' ); ?></p>
                                   	</div>
                                    <h2><span><?php _e( 'Average Sales', 'icwoocommerce_textdomains' ); ?></span></h2>
                                </div>
                                
                                <div class="block block-blue-light">
                                	<div class="block-content">
                                        <img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/coupon-icon.png" alt="" />
                                        <p class="stat">
											<?php if ( $total_coupon_amount > 0 ) echo $this->price($total_coupon_amount); else _e( '0', 'icwoocommerce_textdomains' ); ?>
                                            <span class="count">#<?php if ( $total_coupon_count > 0 ) echo $total_coupon_count; else _e( '0', 'icwoocommerce_textdomains' ); ?></span>
                                        </p>
                                   	</div>
                                    <h2><span><?php _e( 'Total Coupons', 'icwoocommerce_textdomains' ); ?></span></h2>
                                </div>
                                <div class="block block-green2">
                                	<div class="block-content">
                                        <img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/tax-icon.png" alt="" />
                                        <p class="stat">
											<?php if ( $total_tax_amount > 0 ) echo $this->price($total_tax_amount); else _e( '0', 'icwoocommerce_textdomains' ); ?>
                                        	<span class="count">#<?php if ( $total_tax_count > 0 ) echo $total_tax_count; else _e( '0', 'icwoocommerce_textdomains' ); ?></span>
                                        </p>
                                   	</div>
                                    <h2><span><?php _e( 'Total Order Tax', 'icwoocommerce_textdomains' ); ?></span></h2>
                                </div>
                                 <div class="block block-skyblue-light">
                                 	<div class="block-content">
                                        <img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/order-icon.png" alt="" />
                                        <p class="stat">										
											<?php if ( $total_orders_shipping > 0 ) echo $this->price($total_orders_shipping); else _e( '0', 'icwoocommerce_textdomains' ); ?>                                        	
                                        </p>
                                   	</div>
                                    <h2><span><?php _e( 'Order Shipping Total', 'icwoocommerce_textdomains' ); ?></span></h2>
                                </div>
                                <div class="block block-pink">
                                	<div class="block-content">
                                        <img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/customers-icon.png" alt="" />
                                        <p class="stat">#<?php if ( $total_customer > 0 ) echo $total_customer; else _e( '0', 'icwoocommerce_textdomains' ); ?></p>
                                        
                                   	</div>
                                    <h2><?php _e( 'Total Customers', 'icwoocommerce_textdomains' ); ?></span></h2>
                                </div>
                                <!--//New Change ID 20140918-->
                                <div class="block block-red">
                                	<div class="block-content">
                                        <img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/calendar-icon.png" alt="" />
                                        <p class="stat">
											<?php if ( $last_order_date) echo date_i18n($date_format,$last_order_time); 	  else _e( '0', 'icwoocommerce_textdomains' ); ?>
                                            <!--<span class="count">#<?php //if ( $last_order_day > 0 ) echo $last_order_day; else _e( '0', 'icwoocommerce_textdomains' ); ?></span>-->
                                            <span class="count"><?php if ( $last_order_time_diff) echo $last_order_time_diff; else _e( '0', 'icwoocommerce_textdomains' ); ?></span>
                                        </p>
                                   	</div>
                                    <h2><?php _e( 'Last Order Date', 'icwoocommerce_textdomains' ); ?></span></h2>
                                </div>
                               
                                <div class="block block-pink">
                                	<div class="block-content">
                                        <img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/refund-icon.png" alt="" />
                                        <p class="stat">
											<?php if ( $total_part_refund_amt > 0 ) echo $this->price($total_part_refund_amt); else _e( '0', 'icwoocommerce_textdomains' ); ?>                                            
                                        </p>
                                   	</div>
                                    <h2><span><?php _e( 'Part Refund', 'icwoocommerce_textdomains' ); ?></span></h2>
                                </div>
                                
                                <div class="block block-green2">
                                	<div class="block-content">
                                        <img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/refund-icon.png" alt="" />
                                        <p class="stat">
											<?php if ( $full_refund_amount > 0 ) echo $this->price($full_refund_amount); else _e( '0', 'icwoocommerce_textdomains' ); ?>
                                           
                                        </p>
                                   	</div>
                                    <h2><span><?php _e( 'Full Refund', 'icwoocommerce_textdomains' ); ?></span></h2>
                                </div>
                                
                                <div class="block block-green3">
                                	<div class="block-content">
                                        <img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/refund-icon.png" alt="" />
                                        <p class="stat">
											<?php if ( $total_refund_amount > 0 ) echo $this->price($total_refund_amount); else _e( '0', 'icwoocommerce_textdomains' ); ?>
                                            <span class="count">#<?php if ( $total_refund_count > 0 ) echo $total_refund_count; else _e( '0', 'icwoocommerce_textdomains' ); ?></span>
                                        </p>
                                   	</div>
                                    <h2><span><?php _e( 'Total Refund', 'icwoocommerce_textdomains' ); ?></span></h2>
                                </div>
                                
                                <div class="clearfix"></div>
                                
                                
                                <div class="SubTitle"><span><?php _e( 'Todays Summary', 'icwoocommerce_textdomains' ); ?></span></div>
                                <div class="block block-light-green">
                                	<div class="block-content">
                                        <img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/sales-icon.png" alt="" />
                                        <p class="stat">
											<?php if ( $total_today_sales > 0 ) echo $this->price($total_today_sales); else _e( '0', 'icwoocommerce_textdomains' ); ?>
                                        	<span class="count">#<?php echo $total_today_order; ?></span>
                                        </p>
                                   	</div>
                                    <h2><span><?php _e( 'Todays Total Sales', 'icwoocommerce_textdomains' ); ?></span></h2>
                                </div>
                                <div class="block block-brown">
                                	<div class="block-content">
                                        <img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/average-icon.png" alt="" />
                                        <p class="stat"><?php if ( $total_today_avg > 0 ) echo $this->price($total_today_avg); else _e( '0', 'icwoocommerce_textdomains' ); ?></p>
                                   	</div>
                                    <h2><span><?php _e( 'Todays Average Sales', 'icwoocommerce_textdomains' ); ?></span></h2>
                                </div>
                                <div class="block block-purple">
                                	<div class="block-content">
                                        <img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/refund-icon.png" alt="" />
                                        <p class="stat">
											<?php if ( $todays_refund_amount > 0 ) echo $this->price($todays_refund_amount); else _e( '0', 'icwoocommerce_textdomains' ); ?>
                                            <span class="count">#<?php if ( $todays_refund_count > 0 ) echo $todays_refund_count; else _e( '0', 'icwoocommerce_textdomains' ); ?></span>
                                        </p>
                                   	</div>
                                    <h2><span><?php _e( 'Todays Total Refund', 'icwoocommerce_textdomains' ); ?></span></h2>
                                </div>
                                <div class="block block-green3">
                                	<div class="block-content">
                                        <img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/coupon-icon.png" alt="" />
                                        <p class="stat">
											<?php if ( $today_coupon_amount > 0 ) echo $this->price($today_coupon_amount); else _e( '0', 'icwoocommerce_textdomains' ); ?>
                                        	<span class="count">#<?php if ( $today_coupon_count > 0 ) echo $today_coupon_count; else _e( '0', 'icwoocommerce_textdomains' ); ?></span>
                                        </p>
                                   	</div>
                                    <h2><span><?php _e( 'Todays Total Coupons', 'icwoocommerce_textdomains' ); ?></span></h2>
                                </div>
                                
                                <div class="block block-grey">
                                	<div class="block-content">
                                        <img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/tax-icon.png" alt="" />
                                        <p class="stat">
											<?php if ( $today_tax_amount > 0 ) echo $this->price($today_tax_amount); else _e( '0', 'icwoocommerce_textdomains' ); ?>
                                        	<span class="count">#<?php if ( $today_tax_count > 0 ) echo $today_tax_count; else _e( '0', 'icwoocommerce_textdomains' ); ?></span>
                                        </p>
                                   	</div>
                                    <h2><span><?php _e( 'Todays Order Total Tax', 'icwoocommerce_textdomains' ); ?></span></h2>
                                </div>
                                <div class="block block-red">
                                	<div class="block-content">
                                        <img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/customers-icon.png" alt="" />
                                        <p class="stat">#<?php echo $total_today_customer; ?></p>
                                   	</div>
                                    <h2><span><?php _e( "Today's New Customers", 'icwoocommerce_textdomains' ); ?></span></h2>
                                </div>                                
                                
                                <!--<div class="left">
                                    <h3><span><img src="<?php //echo $this->constants['plugin_url']?>/assets/images/icons/product-icon.png" alt="" /><?php //_e( 'Total Products Count', 'icwoocommerce_textdomains' ); ?></span></h3>
                                    <div class="inside Overflow">
                                        <p class="stat"><?php //echo $total_products; ?></p>
                                    </div>
                                </div>                            
                                
                                <div class="left">
                                    <h3><span><img src="<?php //echo $this->constants['plugin_url']?>/assets/images/icons/category-icon.png" alt="" /><?php //_e( 'Total Categories Count', 'icwoocommerce_textdomains' ); ?></span></h3>
                                    <div class="inside Overflow">
                                        <p class="stat"><?php //echo $total_categories; ?></p>
                                    </div>
                                </div>-->
                            	<div class="clearfix"></div>
                        	</div>
						</div>
						<!--<div class="clearfix"></div>-->
					</div>
					<?php //return; 
					if($this->is_product_active == 1):?>
                    <link href="<?php echo $this->constants['plugin_url']?>/assets/css/responsive-tabs.css" rel="stylesheet" type="text/css" media="all" />
                    <script type="text/javascript" src="<?php echo $this->constants['plugin_url']?>/assets/js/responsive-tabs.js"></script>

                    <!--Tab Interface-->
					<?php if($show_sections == 1){ ?>
						<?php if($show_graph == 1): ?>
                    		<div class="responsive-tabs-default">
								<ul class="responsive-tabs">
									<li><a href="#tab-1" id="tablink1" data-tab="#tab-1" target="_self"><?php _e( 'Sales Summary', 'icwoocommerce_textdomains' ); ?></a></li>
									<!--<li><a href="#tab-2" id="tablink2" data-tab="#tab-2" target="_self"><?php //_e( 'Audience Overview', 'icwoocommerce_textdomains' ); ?></a></li>-->
								</ul>
								<div class="clearfix"></div>
								<div class="responsive-tabs-content">
									<div id="tab-1" class="responsive-tabs-panel">                              
										<span class="progress_status" style="display:none"></span>                                    
										<div class="GraphList">
											<a href="#" class="box_tab_report activethis"	data-doreport="sales_by_months" 	data-content="barchart"		data-inside_id="top_tab_graphs"><?php _e( 'Sales By Months', 'icwoocommerce_textdomains' ); ?></a>
											<a href="#" class="box_tab_report"				data-doreport="sales_by_days" 		data-content="linechart"	data-inside_id="top_tab_graphs"><?php _e( 'Sales By Days', 'icwoocommerce_textdomains' ); ?></a>
											<a href="#" class="box_tab_report "				data-doreport="sales_by_week" 		data-content="linechart"	data-inside_id="top_tab_graphs"><?php _e( 'Sales By Week', 'icwoocommerce_textdomains' ); ?></a>
											<a href="#" class="box_tab_report hidden-phone"	data-doreport="top_product"			data-content="piechart"		data-inside_id="top_tab_graphs"><?php _e( 'Top Products', 'icwoocommerce_textdomains' ); ?></a>
											<div class="cleafix"></div>
										</div>
										<div class="inside Overflow" id="top_tab_graphs">
											<div class="chart" id="top_tab_graphs_chart"></div>
										</div>
									</div>
									
									<div id="tab-2" class="responsive-tabs-panel-" style="display:none">
										<div class="responsive-tab-title"></div>
										<div class="stats-overview-list">
											<p><?php _e( 'Fetching data from Google Analytics...', 'icwoocommerce_textdomains' ); ?></p>
											<ul class="stats-overview" style="display:none;"><li><?php _e( 'Please Wait!', 'icwoocommerce_textdomains' ); ?></li></ul>                                
										</div>
										<div class="GraphList">
											<a href="#" class="box_tab_report" style="display:none"	data-doreport="thirty_days_visit"	data-content="linechart"	data-inside_id="top_tab_graphs2"><?php _e( 'Last 30 day visit', 'icwoocommerce_textdomains' ); ?></a>
											<div class="cleafix"></div>
										</div>
										<div class="inside Overflow" id="top_tab_graphs2">
											<div class="chart" id="top_tab_graphs_chart2"></div>
										</div>
									</div>
								</div>
							</div>
						<?php endif; ?>
					
                    	<div class="row">
							<?php if($show_order_summary == 1): ?>
								<div class="col-md-12">
									<div class="icpostbox">
										<h3>
											<span class="title"><?php _e( 'Order Summary', 'icwoocommerce_textdomains' ); ?></span>
											<span class="progress_status"></span>
										</h3>
										<div class="inside Overflow" id="sales_order_count_value">
											<div class="grid"><?php $this->sales_order_count_value($shop_order_status,$hide_order_status,$start_date,$end_date);//New Change ID 20140918?></div>
										</div>
									</div>
								</div>
							<?php endif; ?>
							
							<?php if($show_sales_order_status == 1): ?>
								<div class="col-md-12">
									<div class="icpostbox">
										<h3>
											<span class="title"><?php _e( 'Sales Order Status', 'icwoocommerce_textdomains' ); ?></span>
											<span class="progress_status"></span>
											<div class="Icons">
												<a href="#" class="box_tab_report Table active" data-doreport="sales_order_status" 	data-content="table"		data-inside_id="sales_order_status"><?php _e( 'Sales Order Status', 'icwoocommerce_textdomains' ); ?></a>
												<a href="#" class="box_tab_report BarChart" 	data-doreport="sales_order_status" 	data-content="barchart"		data-inside_id="sales_order_status"><?php _e( 'Sales Order Status', 'icwoocommerce_textdomains' ); ?></a>
												<a href="#" class="box_tab_report PieChart" 	data-doreport="sales_order_status" 	data-content="piechart"		data-inside_id="sales_order_status"><?php _e( 'Sales Order Status', 'icwoocommerce_textdomains' ); ?></a>                                    
											</div>
										</h3>
										<div class="inside Overflow" id="sales_order_status">
											<div class="chart_parent">
												<div class="chart" id="sales_order_status_chart"></div>
											</div>
											<div class="grid"><?php $this->sales_order_status($shop_order_status,$hide_order_status,$start_date,$end_date);//New Change ID 20140918?></div>
										</div>
									</div>
								</div>
							<?php endif; ?>
						</div>
                    
                    	<div class="row ThreeCol_Boxes">
							<?php if($show_top_products == 1): ?>
								<div class="col-md-12">
									<div class="icpostbox">
										<h3>
											<span class="title"><?php echo sprintf(__( 'Top %s Products','icwoocommerce_textdomains' ),$this->get_number_only('top_product_per_page',$this->per_page_default)); ?></span>
											<span class="progress_status"></span>
											<div class="Icons">
												<a href="#" class="box_tab_report Table active" data-doreport="top_product_status" 	data-content="table"		data-inside_id="top_product_status"><?php _e( 'Top Product Status', 'icwoocommerce_textdomains' ); ?></a>
												<a href="#" class="box_tab_report BarChart" 	data-doreport="top_product_status" 	data-content="barchart"		data-inside_id="top_product_status"><?php _e( 'Top Product Status', 'icwoocommerce_textdomains' ); ?></a>
												<a href="#" class="box_tab_report PieChart" 	data-doreport="top_product_status" 	data-content="piechart"		data-inside_id="top_product_status"><?php _e( 'Top Product Status', 'icwoocommerce_textdomains' ); ?></a>                                    
											</div>
										</h3>                                
									   
										<div class="inside Overflow" id="top_product_status">
											<div class="chart_parent">
												<div class="chart" id="top_product_status_chart"></div>
											</div>
											<div class="grid"><?php $this->top_product_list($shop_order_status,$hide_order_status,$start_date,$end_date);//New Change ID 20140918?></div>
										</div>
									</div>                    	
								</div>
							<?php endif; ?>
                        	
							<?php if($show_top_countries == 1): ?>
								<div class="col-md-12">
									<div class="icpostbox">
										<h3>
											<span class="title"><?php echo sprintf(__( 'Top %s Billing Country' ),$this->get_number_only('top_billing_country_per_page',$this->per_page_default)); ?></span>
											<span class="progress_status"></span>
											<div class="Icons">
												<a href="#" class="box_tab_report Table active" data-doreport="top_billing_country" 	data-content="table"		data-inside_id="top_billing_country"><?php _e( 'Top Billing Country', 'icwoocommerce_textdomains' ); ?></a>
												<a href="#" class="box_tab_report BarChart" 	data-doreport="top_billing_country" 	data-content="barchart"		data-inside_id="top_billing_country"><?php _e( 'Top Billing Country', 'icwoocommerce_textdomains' ); ?></a>
												<a href="#" class="box_tab_report PieChart" 	data-doreport="top_billing_country" 	data-content="piechart"		data-inside_id="top_billing_country"><?php _e( 'Top Billing Country', 'icwoocommerce_textdomains' ); ?></a>                                    
											</div>
										</h3>
										<div class="inside Overflow" id="top_billing_country">
											<div class="chart_parent">
												<div class="chart" id="top_billing_country_chart"></div>
											</div>
											<div class="grid"><?php $this->top_billing_country($shop_order_status,$hide_order_status,$start_date,$end_date);//New Change ID 20140918?></div>
										</div>
									</div>
								</div>
							<?php endif; ?>
							
                        	<?php if($show_top_payments == 1): ?>
								<div class="col-md-12">
								<div class="icpostbox">
									<h3>
										<span class="title"><?php echo sprintf(__( 'Top %s Payment Gateway' ),$this->get_number_only('top_payment_gateway_per_page',$this->per_page_default)); ?></span>
										<span class="progress_status"></span>
										<div class="Icons">
											<a href="#" class="box_tab_report Table active" data-doreport="top_payment_gateway" 	data-content="table"		data-inside_id="top_payment_gateway"><?php _e( 'Top Payment Gateway', 'icwoocommerce_textdomains' ); ?></a>
											<a href="#" class="box_tab_report BarChart" 	data-doreport="top_payment_gateway" 	data-content="barchart"		data-inside_id="top_payment_gateway"><?php _e( 'Top Payment Gateway', 'icwoocommerce_textdomains' ); ?></a>
											<a href="#" class="box_tab_report PieChart" 	data-doreport="top_payment_gateway" 	data-content="piechart"		data-inside_id="top_payment_gateway"><?php _e( 'Top Payment Gateway', 'icwoocommerce_textdomains' ); ?></a>
										</div>
									</h3>
									<div class="inside Overflow" id="top_payment_gateway">
										<div class="chart_parent">
											<div class="chart" id="top_payment_gateway_chart"></div>
										</div>
										<div class="grid"><?php $this->get_payment_gateway_list($shop_order_status,$hide_order_status,$start_date,$end_date);//New Change ID 20140918?></div>
									</div>
								</div>						
							</div>
							<?php endif; ?>
                    	</div>
                    	
						<?php if($show_recent_orders == 1): ?>
							<div class="row">
								<div class="icpostbox">
									<h3>
										<span><?php echo sprintf(__( 'Recent %s Orders' ),$this->get_number_only('recent_order_per_page',$this->per_page_default)); ?></span>
									</h3>
									<div class="inside Overflow">                            
										<div class="grid"><?php $this->recent_orders($shop_order_status,$hide_order_status,$start_date,$end_date);?></div>
									</div>
								</div>
							</div>
						<?php endif; ?>
                    
                    	<div class="row">
							<?php if($show_top_customers == 1): ?>
								<div class="col-md-12">
									<div class="icpostbox">
										<h3>
											<span class="title"><?php echo sprintf(__( 'Top %s Customers' ),$this->get_number_only('top_customer_per_page',$this->per_page_default)); ?></span>
											<span class="progress_status"></span>
											<div class="Icons">
												<a href="#" class="box_tab_report Table active" data-doreport="top_customer_list" 	data-content="table"		data-inside_id="top_customer_list"><?php _e( 'Top Customers', 'icwoocommerce_textdomains' ); ?></a>
												<a href="#" class="box_tab_report BarChart" 	data-doreport="top_customer_list" 	data-content="barchart"		data-inside_id="top_customer_list"><?php _e( 'Top Customers', 'icwoocommerce_textdomains' ); ?></a>
												<a href="#" class="box_tab_report PieChart" 	data-doreport="top_customer_list" 	data-content="piechart"		data-inside_id="top_customer_list"><?php _e( 'Top Customers', 'icwoocommerce_textdomains' ); ?></a>                                    
											</div>
										</h3>
										<div class="inside Overflow" id="top_customer_list">
											<div class="chart_parent">
												<div class="chart" id="top_customer_list_chart"></div>
											</div>
											<div class="grid"><?php $this->top_customer_list($shop_order_status,$hide_order_status,$start_date,$end_date);//New Change ID 20140918?></div>
										</div>
									</div>
								</div>
							<?php endif; ?>
                        	
							<?php if($show_top_coupons == 1): ?>
								<div class="col-md-12">
									<div class="icpostbox">
										<h3>
											<span class="title"><?php echo sprintf(__( 'Top %s Coupons' ),$this->get_number_only('top_coupon_per_page',$this->per_page_default)); ?></span>
											<span class="progress_status"></span>
											<div class="Icons">
												<a href="#" class="box_tab_report Table active" data-doreport="top_coupon_list" 	data-content="table"		data-inside_id="top_coupon_list"><?php _e( 'Top Coupons', 'icwoocommerce_textdomains' ); ?></a>
												<a href="#" class="box_tab_report BarChart" 	data-doreport="top_coupon_list" 	data-content="barchart"		data-inside_id="top_coupon_list"><?php _e( 'Top Coupons', 'icwoocommerce_textdomains' ); ?></a>
												<a href="#" class="box_tab_report PieChart" 	data-doreport="top_coupon_list" 	data-content="piechart"		data-inside_id="top_coupon_list"><?php _e( 'Top Coupons', 'icwoocommerce_textdomains' ); ?></a>
											</div>
										</h3>
										<div class="inside Overflow" id="top_coupon_list">
											<div class="chart_parent">
												<div class="chart" id="top_coupon_list_chart"></div>
											</div>
											<div class="grid"><?php $this->get_top_coupon_list($shop_order_status,$hide_order_status,$start_date,$end_date);//New Change ID 20140918?></div>
										</div>
									   
									</div>
								</div>
							<?php endif; ?>                        
                    	</div>
					<?php }else{ ?>
						<div class="responsive-tabs-default">
                        <ul class="responsive-tabs">
                            <li><a href="#tab-1" id="tablink1" data-tab="#tab-1" target="_self"><?php _e( 'Sales Summary', 'icwoocommerce_textdomains' ); ?></a></li>
                            <!--<li><a href="#tab-2" id="tablink2" data-tab="#tab-2" target="_self"><?php //_e( 'Audience Overview', 'icwoocommerce_textdomains' ); ?></a></li>-->
                        </ul>
            			<div class="clearfix"></div>
                        <div class="responsive-tabs-content">
                            <div id="tab-1" class="responsive-tabs-panel">                              
                                <span class="progress_status" style="display:none"></span>                                    
                                <div class="GraphList">
                                    <a href="#" class="box_tab_report activethis"	data-doreport="sales_by_months" 	data-content="barchart"		data-inside_id="top_tab_graphs"><?php _e( 'Sales By Months', 'icwoocommerce_textdomains' ); ?></a>
                                    <a href="#" class="box_tab_report"				data-doreport="sales_by_days" 		data-content="linechart"	data-inside_id="top_tab_graphs"><?php _e( 'Sales By Days', 'icwoocommerce_textdomains' ); ?></a>
                                    <a href="#" class="box_tab_report "				data-doreport="sales_by_week" 		data-content="linechart"	data-inside_id="top_tab_graphs"><?php _e( 'Sales By Week', 'icwoocommerce_textdomains' ); ?></a>
                                    <a href="#" class="box_tab_report hidden-phone"	data-doreport="top_product"			data-content="piechart"		data-inside_id="top_tab_graphs"><?php _e( 'Top Products', 'icwoocommerce_textdomains' ); ?></a>
                                    <div class="cleafix"></div>
                                </div>
                                <div class="inside Overflow" id="top_tab_graphs">
                                    <div class="chart" id="top_tab_graphs_chart"></div>
                                </div>
                            </div>
                            
                            <div id="tab-2" class="responsive-tabs-panel-" style="display:none">
                                <div class="responsive-tab-title"></div>
                                <div class="stats-overview-list">
                                	<p><?php _e( 'Fetching data from Google Analytics...', 'icwoocommerce_textdomains' ); ?></p>
                               		<ul class="stats-overview" style="display:none;"><li><?php _e( 'Please Wait!', 'icwoocommerce_textdomains' ); ?></li></ul>                                
                                </div>
                                <div class="GraphList">
                                    <a href="#" class="box_tab_report" style="display:none"	data-doreport="thirty_days_visit"	data-content="linechart"	data-inside_id="top_tab_graphs2"><?php _e( 'Last 30 day visit', 'icwoocommerce_textdomains' ); ?></a>
                                    <div class="cleafix"></div>
                                </div>
                                <div class="inside Overflow" id="top_tab_graphs2">
                                    <div class="chart" id="top_tab_graphs_chart2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
					
                    	<div class="row">
                        <div class="col-md-6">
                            <div class="icpostbox">
                                <h3>
                                    <span class="title"><?php _e( 'Order Summary', 'icwoocommerce_textdomains' ); ?></span>
                                    <span class="progress_status"></span>
                                </h3>
                                <div class="inside Overflow" id="sales_order_count_value">
                                    <div class="grid"><?php $this->sales_order_count_value($shop_order_status,$hide_order_status,$start_date,$end_date);//New Change ID 20140918?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="icpostbox">
                                <h3>
                                    <span class="title"><?php _e( 'Sales Order Status', 'icwoocommerce_textdomains' ); ?></span>
                                    <span class="progress_status"></span>
                                    <div class="Icons">
                                        <a href="#" class="box_tab_report Table active" data-doreport="sales_order_status" 	data-content="table"		data-inside_id="sales_order_status"><?php _e( 'Sales Order Status', 'icwoocommerce_textdomains' ); ?></a>
                                        <a href="#" class="box_tab_report BarChart" 	data-doreport="sales_order_status" 	data-content="barchart"		data-inside_id="sales_order_status"><?php _e( 'Sales Order Status', 'icwoocommerce_textdomains' ); ?></a>
                                        <a href="#" class="box_tab_report PieChart" 	data-doreport="sales_order_status" 	data-content="piechart"		data-inside_id="sales_order_status"><?php _e( 'Sales Order Status', 'icwoocommerce_textdomains' ); ?></a>                                    
                                    </div>
                                </h3>
                                <div class="inside Overflow" id="sales_order_status">
                                	<div class="chart_parent">
                                    	<div class="chart" id="sales_order_status_chart"></div>
                                    </div>
                                    <div class="grid"><?php $this->sales_order_status($shop_order_status,$hide_order_status,$start_date,$end_date);//New Change ID 20140918?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    	<div class="row ThreeCol_Boxes">
                        <div class="col-md-4">
                            <div class="icpostbox">
                                <h3>
                                   	<span class="title"><?php echo sprintf(__( 'Top %s Products','icwoocommerce_textdomains' ),$this->get_number_only('top_product_per_page',$this->per_page_default)); ?></span>
                                    <span class="progress_status"></span>
                                    <div class="Icons">
                                        <a href="#" class="box_tab_report Table active" data-doreport="top_product_status" 	data-content="table"		data-inside_id="top_product_status"><?php _e( 'Top Product Status', 'icwoocommerce_textdomains' ); ?></a>
                                        <a href="#" class="box_tab_report BarChart" 	data-doreport="top_product_status" 	data-content="barchart"		data-inside_id="top_product_status"><?php _e( 'Top Product Status', 'icwoocommerce_textdomains' ); ?></a>
                                        <a href="#" class="box_tab_report PieChart" 	data-doreport="top_product_status" 	data-content="piechart"		data-inside_id="top_product_status"><?php _e( 'Top Product Status', 'icwoocommerce_textdomains' ); ?></a>                                    
                                    </div>
                                </h3>                                
                               
                                <div class="inside Overflow" id="top_product_status">
                                	<div class="chart_parent">
                                    	<div class="chart" id="top_product_status_chart"></div>
                                    </div>
                                    <div class="grid"><?php $this->top_product_list($shop_order_status,$hide_order_status,$start_date,$end_date);//New Change ID 20140918?></div>

                                </div>
                            </div>                    	
                        </div>
                        
                        <div class="col-md-4">
                            <div class="icpostbox">
                                <h3>
                                    <span class="title"><?php echo sprintf(__( 'Top %s Billing Country' ),$this->get_number_only('top_billing_country_per_page',$this->per_page_default)); ?></span>
                                    <span class="progress_status"></span>
                                    <div class="Icons">
                                        <a href="#" class="box_tab_report Table active" data-doreport="top_billing_country" 	data-content="table"		data-inside_id="top_billing_country"><?php _e( 'Top Billing Country', 'icwoocommerce_textdomains' ); ?></a>
                                        <a href="#" class="box_tab_report BarChart" 	data-doreport="top_billing_country" 	data-content="barchart"		data-inside_id="top_billing_country"><?php _e( 'Top Billing Country', 'icwoocommerce_textdomains' ); ?></a>
                                        <a href="#" class="box_tab_report PieChart" 	data-doreport="top_billing_country" 	data-content="piechart"		data-inside_id="top_billing_country"><?php _e( 'Top Billing Country', 'icwoocommerce_textdomains' ); ?></a>                                    
                                    </div>
                                </h3>
                                <div class="inside Overflow" id="top_billing_country">
                                	<div class="chart_parent">
                                    	<div class="chart" id="top_billing_country_chart"></div>
                                    </div>
                                    <div class="grid"><?php $this->top_billing_country($shop_order_status,$hide_order_status,$start_date,$end_date);//New Change ID 20140918?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="icpostbox">
                                <h3>
                                    <span class="title"><?php echo sprintf(__( 'Top %s Payment Gateway' ),$this->get_number_only('top_payment_gateway_per_page',$this->per_page_default)); ?></span>
                                    <span class="progress_status"></span>
                                    <div class="Icons">
                                        <a href="#" class="box_tab_report Table active" data-doreport="top_payment_gateway" 	data-content="table"		data-inside_id="top_payment_gateway"><?php _e( 'Top Payment Gateway', 'icwoocommerce_textdomains' ); ?></a>
                                        <a href="#" class="box_tab_report BarChart" 	data-doreport="top_payment_gateway" 	data-content="barchart"		data-inside_id="top_payment_gateway"><?php _e( 'Top Payment Gateway', 'icwoocommerce_textdomains' ); ?></a>
                                        <a href="#" class="box_tab_report PieChart" 	data-doreport="top_payment_gateway" 	data-content="piechart"		data-inside_id="top_payment_gateway"><?php _e( 'Top Payment Gateway', 'icwoocommerce_textdomains' ); ?></a>
                                    </div>
                                </h3>
                                <div class="inside Overflow" id="top_payment_gateway">
                                	<div class="chart_parent">
                                    	<div class="chart" id="top_payment_gateway_chart"></div>
                                    </div>
                                    <div class="grid"><?php $this->get_payment_gateway_list($shop_order_status,$hide_order_status,$start_date,$end_date);//New Change ID 20140918?></div>
                                </div>
                            </div>						
                        </div>
                    </div>
                    
                    	<div class="row">
                        <div class="icpostbox">
                            <h3>
                                <span><?php echo sprintf(__( 'Recent %s Orders' ),$this->get_number_only('recent_order_per_page',$this->per_page_default)); ?></span>
                            </h3>
                            <div class="inside Overflow">                            
                                <div class="grid"><?php $this->recent_orders($shop_order_status,$hide_order_status,$start_date,$end_date);?></div>
                            </div>
                        </div>
                    </div>
                    
                    	<div class="row">
                        <div class="col-md-6">
                            <div class="icpostbox">
                                <h3>
                                    <span class="title"><?php echo sprintf(__( 'Top %s Customers' ),$this->get_number_only('top_customer_per_page',$this->per_page_default)); ?></span>
                                    <span class="progress_status"></span>
                                    <div class="Icons">
                                        <a href="#" class="box_tab_report Table active" data-doreport="top_customer_list" 	data-content="table"		data-inside_id="top_customer_list"><?php _e( 'Top Customers', 'icwoocommerce_textdomains' ); ?></a>
                                        <a href="#" class="box_tab_report BarChart" 	data-doreport="top_customer_list" 	data-content="barchart"		data-inside_id="top_customer_list"><?php _e( 'Top Customers', 'icwoocommerce_textdomains' ); ?></a>
                                        <a href="#" class="box_tab_report PieChart" 	data-doreport="top_customer_list" 	data-content="piechart"		data-inside_id="top_customer_list"><?php _e( 'Top Customers', 'icwoocommerce_textdomains' ); ?></a>                                    
                                    </div>
                                </h3>
                                <div class="inside Overflow" id="top_customer_list">
                                	<div class="chart_parent">
                                    	<div class="chart" id="top_customer_list_chart"></div>
                                    </div>
                                    <div class="grid"><?php $this->top_customer_list($shop_order_status,$hide_order_status,$start_date,$end_date);//New Change ID 20140918?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="icpostbox">
                                <h3>
                                    <span class="title"><?php echo sprintf(__( 'Top %s Coupons' ),$this->get_number_only('top_coupon_per_page',$this->per_page_default)); ?></span>
                                    <span class="progress_status"></span>
                                    <div class="Icons">
                                        <a href="#" class="box_tab_report Table active" data-doreport="top_coupon_list" 	data-content="table"		data-inside_id="top_coupon_list"><?php _e( 'Top Coupons', 'icwoocommerce_textdomains' ); ?></a>
                                        <a href="#" class="box_tab_report BarChart" 	data-doreport="top_coupon_list" 	data-content="barchart"		data-inside_id="top_coupon_list"><?php _e( 'Top Coupons', 'icwoocommerce_textdomains' ); ?></a>
                                        <a href="#" class="box_tab_report PieChart" 	data-doreport="top_coupon_list" 	data-content="piechart"		data-inside_id="top_coupon_list"><?php _e( 'Top Coupons', 'icwoocommerce_textdomains' ); ?></a>
                                    </div>
                                </h3>
                                <div class="inside Overflow" id="top_coupon_list">
                                	<div class="chart_parent">
                                    	<div class="chart" id="top_coupon_list_chart"></div>

                                    </div>
                                    <div class="grid"><?php $this->get_top_coupon_list($shop_order_status,$hide_order_status,$start_date,$end_date);//New Change ID 20140918?></div>
                                </div>
                               
                            </div>
                        </div>                        
                    </div>
					<?php } ?>
                    
                    <?php endif;?>
				</div>
			
			<?php
		}
		
		//New Change ID 20140918
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
			
			//$this->print_sql($sql);
					
			return $items;
		}
		
		//New Change ID 20140918
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
		
		//New Change ID 20140918
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
		
		//New Change ID 20140918	
		function get_total_of_order($type = "today", $meta_key="_order_tax",$order_item_type="tax",$shop_order_status,$hide_order_status,$start_date,$end_date){
			global $wpdb;
			$today_date 			= $this->today;
			$yesterday_date 		= $this->yesterday;
			
			$sql = "  SELECT";
			$sql .= " SUM(postmeta1.meta_value) 	AS 'total_amount'";
			$sql .= " ,count(posts.ID) 				AS 'total_count'";
			$sql .= " FROM {$wpdb->prefix}posts as posts";			
			$sql .= " LEFT JOIN  {$wpdb->prefix}postmeta as postmeta1 ON postmeta1.post_id=posts.ID";			
			
			//$sql .= " FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items";			
			//$sql .= " LEFT JOIN  {$wpdb->prefix}postmeta as postmeta1 ON postmeta1.post_id=woocommerce_order_items.order_id";			
			
			//$sql .= " LEFT JOIN  {$wpdb->prefix}posts as posts ON posts.ID=	woocommerce_order_items.order_id";
			
			if($this->constants['post_order_status_found'] == 0 ){
				if(count($shop_order_status)>0){
					$sql .= " 
					LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
					LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
				}
			}
			
			//$sql .= " WHERE postmeta1.meta_key = '{$meta_key}' AND woocommerce_order_items.order_item_type = '{$order_item_type}'";
			$sql .= " WHERE postmeta1.meta_key = '{$meta_key}' AND posts.post_type = 'shop_order' AND postmeta1.meta_value > 0";
			//$sql .= " AND woocommerce_order_items.order_item_type = '{$order_item_type}'";
			
			$sql .= " AND posts.post_type='shop_order' ";
			
			if($type == "today") $sql .= " AND DATE(posts.post_date) = '{$today_date}'";
			if($type == "yesterday") 	$sql .= " AND DATE(posts.post_date) = '{$yesterday_date}'";
			
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
		
		//New Change ID 20140918
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
		
		function get_total_products_count(){
			global $wpdb,$sql,$Limit;
			$sql = "SELECT COUNT(*) AS 'product_count'  FROM {$wpdb->prefix}posts as posts WHERE  post_type='product' AND post_status = 'publish'";
			return $wpdb->get_var($sql);
		}	
		
		function get_total_categories_count(){
			global $wpdb,$sql,$Limit;
			$sql = "SELECT COUNT(*) As 'category_count' FROM {$wpdb->prefix}term_taxonomy as term_taxonomy  
					LEFT JOIN  {$wpdb->prefix}terms as terms ON terms.term_id=term_taxonomy.term_id
			WHERE taxonomy ='product_cat'";
			return $wpdb->get_var($sql);
			//print_array($order_items);		
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
		
		//New Change ID 20140918	
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
				if($order_items>0):
					$admin_url = admin_url("admin.php?page=".$this->constants['plugin_key']."_details_page")."{$url_hide_order_status}{$url_shop_order_status}&detail_view=no&";
					?>	
                     <table style="width:100%" class="widefat">
                        <thead>
                            <tr class="first">
                                <th><?php _e( 'Sales Order', 'icwoocommerce_textdomains'); ?></th>
                                <th class="order_count"><?php _e( 'Order Count', 'icwoocommerce_textdomains'); ?></th>
                                <th class="amount_column"><?php _e( 'Amount', 'icwoocommerce_textdomains'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php					
                                foreach ( $order_items as $key => $order_item ) {
                                if($key%2 == 1){$alternate = "alternate ";}else{$alternate = "";};
                            ?>
                                <tr class="<?php echo $alternate."row_".$key;?>">
                                	<?php if($order_item->OrderCount> 0):?>
                                    <td><a href="<?php echo $admin_url."sales_order=".strtolower($order_item->SalesOrder)."&page_title=".$order_item->SalesOrder; ?>"><?php echo $order_item->SalesOrder?></a></td>
                                    <?php else:?>
                                    <td><?php echo $order_item->SalesOrder?></td>
                                    <?php endif;?>
                                    <td class="order_count"><?php echo $order_item->OrderCount?></td>
                                    <td class="amount_column amount"><?php echo $this->price($order_item->OrderTotal);?></td>
                                </tr>
                             <?php } ?>	
                        <tbody>           
                    </table>		
                    <?php
				else:
					echo '<p>'.__("No order found.", 'icwoocommerce_textdomains').'</p>';
				endif;
		}
		
		//New Change ID 20140918
		function sales_order_status($shop_order_status,$hide_order_status,$start_date,$end_date){
			
			global $wpdb;
			
			$sql = "SELECT
			
			COUNT(postmeta.meta_value) AS 'Count'
			,SUM(postmeta.meta_value) AS 'Total'";
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
			
			//Added 20150217
			$show_seleted_order_status	= $this->get_setting('show_seleted_order_status',$this->constants['plugin_options'], 0);
			if($show_seleted_order_status == 1){
				$url_shop_order_status	= "";
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
			}
			
			if($this->constants['post_order_status_found'] == 0 ){
				$sql .= " Group BY terms.term_id ORDER BY Total DESC";
			}else{
				$sql .= " Group BY posts.post_status ORDER BY Total DESC";
			}
			
			$order_items = $wpdb->get_results($sql);
			
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
							$order_items[$key]->Status = isset($order_statuses[$value->Status]) ? $order_statuses[$value->Status] : $value->Status;
						}
					}
					$all_admin_url 	= admin_url("admin.php?page=".$this->constants['plugin_key']."_report_page")."&end_date=".$end_date."&start_date=".$start_date."{$url_hide_order_status}&report_name=order_status";
					
					
					
					?>
               		
                    <table style="width:100%" class="widefat">
						<thead>
							<tr class="first">
								<th><?php _e( 'Order Status', 'icwoocommerce_textdomains'); ?></th>
								<th class="order_count"><?php _e( 'Order Count', 'icwoocommerce_textdomains'); ?></th>   	                        
								<th class="amount_column"><?php _e( 'Amount', 'icwoocommerce_textdomains'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php					
							foreach ( $order_items as $key => $order_item ) {
							if($key%2 == 1){$alternate = "alternate ";}else{$alternate = "";};
							?>
								<tr class="<?php echo $alternate."row_".$key;?>">
									<td><a href="<?php echo $admin_url.$order_item->StatusID."&page_title=".$order_item->Status."_orders"; ?>"><?php echo '<span class="order-status '.sanitize_title($order_item->Status).'">'.ucwords(__($order_item->Status, 'icwoocommerce_textdomains')).'</span>'; ?><?php //echo Ucfirst($order_item->Status);?></a></td>
									<td class="order_count"><?php echo $order_item->Count?></td>
									<td class="amount_column amount"><?php echo $this->price($order_item->Total);?></td>
								 <?php } ?>		
								</tr>
						<tbody>           
					</table>
                    <span class="ViewAll"><a href="<?php echo $all_admin_url;?>"><?php _e("View All",'icwoocommerce_textdomains');?></a></span>
					<?php 
				else:
					echo '<p>'.__("No status found.", 'icwoocommerce_textdomains').'</p>';
				endif;
			
			}
			
			//New Change ID 20140918
			function top_product_list($shop_order_status,$hide_order_status,$start_date,$end_date){
					global $wpdb,$options;
					
					$optionsid	= "top_product_per_page";					
					$per_page 	= $this->get_number_only($optionsid,$this->per_page_default);
					$product_status 		= $this->get_setting('product_status',$this->constants['plugin_options'], array());
					$sql = "
						SELECT 
						
						woocommerce_order_items.order_item_id
						,SUM(woocommerce_order_itemmeta.meta_value)		AS 'Qty'
						,SUM(woocommerce_order_itemmeta2.meta_value)	AS 'Total'
						,woocommerce_order_itemmeta3.meta_value			AS ProductID";
					
					if(count($product_status)>0){
						$sql .= " ,products.post_title 	AS 'ItemName'";
					}else{
						$sql .= " ,woocommerce_order_items.order_item_name			AS 'ItemName'";
					}
												
					$sql .= "
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
						
						if(count($product_status)>0){
							$sql .= " LEFT JOIN {$wpdb->prefix}posts AS products ON products.ID = woocommerce_order_itemmeta3.meta_value";
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
						
						if(count($product_status)>0){
							$in_product_status = implode("','",$product_status);
							$sql .= " AND products.post_type IN ('product')";
							$sql .= " AND products.post_status IN ('{$in_product_status}')";
						}
						
						$sql .= " 
						
						GROUP BY  woocommerce_order_itemmeta3.meta_value
						Order By Total DESC
						LIMIT {$per_page}";
						
						$order_items = $wpdb->get_results($sql );
						if($wpdb->last_error){
							echo $wpdb->last_error;
							return "";
						}
							if(count($order_items)>0):
								$admin_url		= admin_url("admin.php?page=".$this->constants['plugin_key']."_details_page")."&end_date=".$end_date."&start_date=".$start_date."{$url_hide_order_status}{$url_shop_order_status}&detail_view=yes&product_id=";
								$all_admin_url 	= admin_url("admin.php?page=".$this->constants['plugin_key']."_report_page")."&end_date=".$end_date."&start_date=".$start_date."{$url_hide_order_status}{$url_shop_order_status}&report_name=product_page";
								?>							 	
                                
                                
                                    <table style="width:100%" class="widefat">
                                        <thead>
                                            <tr class="first">
                                                <th><?php _e( 'Item Name', 'icwoocommerce_textdomains'); ?></th>
                                                <th class="order_count"><?php _e( 'Qty', 'icwoocommerce_textdomains'); ?></th>                           
                                                <th class="amount_column"><?php _e( 'Amount', 'icwoocommerce_textdomains'); ?></th>
                                            </tr>
                                        </thead>
                                       
                                        <tbody>
                                            <?php					
                                            foreach ( $order_items as $key => $order_item ) {
                                                if($key%2 == 1){$alternate = "alternate ";}else{$alternate = "";};?>
                                                <tr class="<?php echo $alternate."row_".$key;?>">
                                                    <td><a href="<?php echo $admin_url.$order_item->ProductID;?>"><?php echo $order_item->ItemName?></a></td>
                                                    <td class="order_count"><?php echo $order_item->Qty?></td>
                                                    <td class="amount_column amount"><?php echo $this->price($order_item->Total)?></td>
                                                </tr>
                                            <?php } ?>	
                                        <tbody> 
                                             
                                    </table>	
                                    <span class="ViewAll"><a href="<?php echo $all_admin_url;?>"><?php _e("View All",'icwoocommerce_textdomains');?></a></span>
                                <?php					
                            else:
								echo '<p>'.__("No product found.", 'icwoocommerce_textdomains').'</p>';
                            endif;
		}
		
		//New Change ID 20140918	
		function top_billing_country($shop_order_status,$hide_order_status,$start_date,$end_date)
		{
			global $wpdb,$options;
					$optionsid	= "top_billing_country_per_page";
					$per_page 	= $this->get_number_only($optionsid,$this->per_page_default);
				
						$sql = "
						SELECT SUM(postmeta1.meta_value) AS 'Total' 
						,postmeta2.meta_value AS 'BillingCountry'
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
						Order By Total DESC 						
						LIMIT {$per_page}";
						
						$order_items = $wpdb->get_results($sql); 
						if(count($order_items)>0):
							$country      = $this->get_wc_countries();//Added 20150225
							$admin_url 		= admin_url("admin.php?page=".$this->constants['plugin_key']."_details_page")."&end_date=".$end_date."&start_date=".$start_date."&detail_view=no{$url_hide_order_status}{$url_shop_order_status}&country_code=";	
							$all_admin_url	= admin_url("admin.php?page=".$this->constants['plugin_key']."_report_page")."&end_date=".$end_date."&start_date=".$start_date."{$url_hide_order_status}{$url_shop_order_status}&report_name=billing_country_page";
							?>
                            
						<table style="width:100%" class="widefat">
							<thead>
								<tr class="first">
									<th><?php _e( 'Billing Country', 'icwoocommerce_textdomains'); ?></th>
									<th class="order_count"><?php _e( 'Order Count', 'icwoocommerce_textdomains'); ?></th>                           
									<th class="order_count"><?php _e( 'Amount', 'icwoocommerce_textdomains'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php					
								foreach ( $order_items as $key => $order_item ) {
								if($key%2 == 1){$alternate = "alternate ";}else{$alternate = "";};
								?>
									<tr class="<?php echo $alternate."row_".$key;?>">
										<td><a href="<?php echo $admin_url.$order_item->BillingCountry;?>"><?php echo isset($country->countries[$order_item->BillingCountry])  ? $country->countries[$order_item->BillingCountry] : $order_item->BillingCountry;?></a></td></td>
										<td class="order_count"><?php echo $order_item->OrderCount?></td>
										<td class="amount_column amount"><?php echo $this->price($order_item->Total)?></td>
									 <?php } ?>		
									</tr>
							<tbody>           
						</table>
                        <span class="ViewAll"><a href="<?php echo $all_admin_url;?>"><?php _e("View All",'icwoocommerce_textdomains');?></a></span>
						<?php 
						else:
							echo '<p>'.__("No Country found.", 'icwoocommerce_textdomains').'</p>';
						endif;							
		}
		
		//New Change ID 20141119
		function top_billing_state($shop_order_status,$hide_order_status,$start_date,$end_date)
		{
			global $wpdb,$options;
					$optionsid	= "top_billing_state_per_page";
					$per_page 	= $this->get_number_only($optionsid,$this->per_page_default);
				
						$sql = "
						SELECT SUM(postmeta1.meta_value) AS 'Total' 
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
						Order By Total DESC 						
						LIMIT {$per_page}";
						
						//echo $sql;
						
						$order_items = $wpdb->get_results($sql); 
						if(count($order_items)>0):
							$country      = $this->get_wc_countries();//Added 20150225
							$admin_url 		= admin_url("admin.php?page=".$this->constants['plugin_key']."_details_page")."&end_date=".$end_date."&start_date=".$start_date."&detail_view=no{$url_hide_order_status}{$url_shop_order_status}&country_code=";	
							$all_admin_url	= admin_url("admin.php?page=".$this->constants['plugin_key']."_report_page")."&end_date=".$end_date."&start_date=".$start_date."{$url_hide_order_status}{$url_shop_order_status}&report_name=billing_state_page";
							?>
                            
						<table style="width:100%" class="widefat">
							<thead>
								<tr class="first">
									<th><?php _e( 'Billing State', 'icwoocommerce_textdomains'); ?></th>
									<th class="order_count"><?php _e( 'Order Count', 'icwoocommerce_textdomains'); ?></th>                           
									<th class="amount_column"><?php _e( 'Amount', 'icwoocommerce_textdomains'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php					
								foreach ( $order_items as $key => $order_item ) {
									
									$billing_state =  $this->get_billling_state_name($order_item->billing_country,$order_item->billing_state);
									if($key%2 == 1){$alternate = "alternate ";}else{$alternate = "";};
								?>
									<tr class="<?php echo $alternate."row_".$key;?>">
										<td><a href="<?php echo $admin_url.$order_item->billing_country."&state_code=".$order_item->billing_state;?>"><?php echo $billing_state;?></a></td>
										<td class="order_count"><?php echo $order_item->OrderCount?></td>
										<td class="amount_column amount"><?php echo $this->price($order_item->Total)?></td>
									 <?php } ?>		
									</tr>
							<tbody>           
						</table>
                        <span class="ViewAll"><a href="<?php echo $all_admin_url;?>"><?php _e("View All",'icwoocommerce_textdomains');?></a></span>
						<?php 
						else:
							echo '<p>'.__("No State found.", 'icwoocommerce_textdomains').'</p>';
						endif;							
		}
		
		
		//New Change ID 20140918
		function get_payment_gateway_list($shop_order_status,$hide_order_status,$start_date,$end_date){
					global $wpdb,$options;
					$optionsid	= "top_payment_gateway_per_page";
					$per_page 	= $this->get_number_only($optionsid,$this->per_page_default);
					
					$sql = "
					SELECT postmeta2.meta_value AS 'payment_method_title' 
					,SUM(postmeta1.meta_value) AS 'payment_amount_total'
					,COUNT(postmeta1.meta_value) As 'order_count'					
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
					Order BY payment_amount_total DESC LIMIT {$per_page}";
					
					$order_items = $wpdb->get_results($sql);
					
					if(count($order_items)>0):
							$admin_url 		= admin_url("admin.php?page=".$this->constants['plugin_key']."_details_page")."&end_date=".$end_date."&start_date=".$start_date."&detail_view=no{$url_hide_order_status}{$url_shop_order_status}&payment_method=";							
							$all_admin_url 	= admin_url("admin.php?page=".$this->constants['plugin_key']."_report_page")."&end_date=".$end_date."&start_date=".$start_date."{$url_hide_order_status}{$url_shop_order_status}&report_name=payment_gateway_page";
						?>
							
						<table style="width:100%" class="widefat">
						<thead>
							<tr class="first">
								<th><?php _e( 'Payment Method', 'icwoocommerce_textdomains'); ?></th>
								<th class="order_count"><?php _e( 'Order Count', 'icwoocommerce_textdomains'); ?></th>
								<th class="amount_column"><?php _e( 'Amount', 'icwoocommerce_textdomains'); ?></th>                           
							</tr>
						</thead>
						<tbody>
							<?php					
							foreach ( $order_items as $key => $order_item ) {
								if($key%2 == 1){$alternate = "alternate ";}else{$alternate = "";};
								?>
								<tr class="<?php echo $alternate."row_".$key;?>">
									<td><a href="<?php echo $admin_url.$order_item->payment_method_title?>"><?php echo $order_item->payment_method_title;?></a></td>
									<td class="order_count"><?php echo $order_item->order_count?></td>
									<td class="amount_column amount"><?php echo $this->price($order_item->payment_amount_total);?></td>
								</tr>
								 <?php } ?>						
							<tbody>
						</table>
                        <span class="ViewAll"><a href="<?php echo $all_admin_url;?>"><?php _e("View All",'icwoocommerce_textdomains');?></a></span>
						<?php 
					else:
						echo '<p>'.__("No payment found.", 'icwoocommerce_textdomains').'</p>';
					endif;
		}
		
		//New Change ID 20140918
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
				//$this->print_array($order_items );
				if(count($order_items)>0){
					foreach ( $order_items as $key => $order_item ) {
							$order_id								= $order_item->order_id;
							
							if(!isset($order_meta[$order_id])){
								$order_meta[$order_id]					= $this->get_all_post_meta($order_id);
							}
							
							foreach($order_meta[$order_id] as $k => $v){
								$order_items[$key]->$k			= $v;
							}
							
							//Added 20150205
							$order_items[$key]->cart_discount		= isset($order_item->cart_discount)		? $order_item->cart_discount 	: 0;
							$order_items[$key]->order_discount		= isset($order_item->order_discount)	? $order_item->order_discount 	: 0;
							$order_items[$key]->total_discount 		= isset($order_item->total_discount)	? $order_item->total_discount 	: ($order_items[$key]->cart_discount + $order_items[$key]->order_discount);
							
							//Added 20150206
							$order_items[$key]->order_tax 			= isset($order_item->order_tax)			? $order_item->order_tax : 0;
							$order_items[$key]->order_shipping_tax 	= isset($order_item->order_shipping_tax)? $order_item->order_shipping_tax : 0;
							$order_items[$key]->total_tax 			= isset($order_item->total_tax)			? $order_item->total_tax 	: ($order_items[$key]->order_tax + $order_items[$key]->order_shipping_tax);
							
							$transaction_id = "ransaction ID";
							$order_items[$key]->transaction_id		= isset($order_item->$transaction_id) 	? $order_item->$transaction_id		: (isset($order_item->transaction_id) ? $order_item->transaction_id : '');//Added 20150203
							$order_items[$key]->gross_amount 		= ($order_items[$key]->order_total + $order_items[$key]->total_discount) - ($order_items[$key]->order_shipping +  $order_items[$key]->order_shipping_tax + $order_items[$key]->order_tax );
							
							//Added 20150206
							$order_items[$key]->billing_first_name	= isset($order_item->billing_first_name)? $order_item->billing_first_name 	: '';
							$order_items[$key]->billing_last_name	= isset($order_item->billing_last_name)	? $order_item->billing_last_name 	: '';
							$order_items[$key]->billing_name		= $order_items[$key]->billing_first_name.' '.$order_items[$key]->billing_last_name;
							
							//$order_items[$key]->refund_amount		= $this->get_refund_amount($order_item->order_id);
					}						
				}
				if(count($order_items) > 0):
					$TotalOrderCount 	= 0;
					$TotalAmount 		= 0;
					$TotalShipping 		= 0;
					$zero				= $this->price(0);
					$ToDate 			= $this->today;
					$FromDate 			= $this->first_order_date($this->constants['plugin_key']);
					$admin_url 			= admin_url("admin.php?page=".$this->constants['plugin_key']."_details_page")."&end_date=".$ToDate."&start_date=".$FromDate."{$url_hide_order_status}{$url_shop_order_status}&order_id=";	
					
					$columns 			= $this->get_coumns();
					$all_admin_url 		= admin_url("admin.php?page=".$this->constants['plugin_key']."_report_page")."{$url_hide_order_status}{$url_shop_order_status}&report_name=recent_order";
					$zero_prize			= array();
					$this->constants['date_format'] = isset($this->constants['date_format']) ? $this->constants['date_format'] : get_option( 'date_format', "Y-m-d" );
					$date_format = $this->constants['date_format'];
					
					
					$grid_object		= $this->get_grid_object();//Added 20150223
					$order_items		= $grid_object->create_grid_items($columns,$order_items);//Added 20150223
					//$this->print_array($columns);
					?>
                	
                    <table style="width:100%" class="widefat">
                        <thead>
								<tr class="first">
                                	<?php 
										$cells_status = array();
										$output = "";
										foreach($columns as $key => $value):
											$td_class = $key;
											$td_width = "";
											switch($key):
												case "order_date":
													$date_format		= get_option( 'date_format' );
													break;
												case "order_item_count":
												case "gross_amount":
												case "order_discount":
												case "cart_discount":
												case "total_discount":
												case "order_shipping":
												case "order_shipping_tax":
												case "order_tax":
												case "part_order_refund_amount":
												case "total_tax":
												case "order_total":
													$td_class .= " amount";												
													break;							
												default;
													break;
											endswitch;
											$th_value 			= $value;
											$output 			.= "\n\t<th class=\"{$td_class}\">{$th_value}</th>";											
										endforeach;
										echo $output ;
										?>
								</tr>
							</thead>
                        <tbody>
                            <?php					
                            foreach ( $order_items as $key => $order_item ) {
                                
                                $TotalAmount =  $TotalAmount + $order_item->order_total;
                                $TotalShipping = $TotalShipping + $order_item->order_shipping;
								$zero_prize[$order_item->order_currency] = isset($zero_prize[$order_item->order_currency]) ? $zero_prize[$order_item->order_currency] : $this->price(0, array('currency' => $order_item->order_currency));
                                $TotalOrderCount++;
								$date_format = get_option( 'date_format' );
                                //date_i18n($date_format,strtotime($order_item->product_date));
                                if($key%2 == 1){$alternate = "alternate ";}else{$alternate = "";};
                                ?>
                                <tr class="<?php echo $alternate."row_".$key;?>">
                                    <?php
                                        foreach($columns as $key => $value):
                                            $td_class = $key;
                                            //$td_style = $cells_status[$key];
                                            $td_value = "";
                                            switch($key):
                                                case "order_id":
                                                   $td_value = '<a href="'.$admin_url.$order_item->order_id.'&detail_view=yes" target="'.$order_item->order_id.'_blank">' . $order_item->order_id  . '</a>';
                                                    break;
                                                case "billing_name":
                                                    $td_value = ucwords(stripslashes_deep($order_item->billing_name));
                                                    break;
                                                case "billing_email":
                                                    $td_value = $this->emailLlink($order_item->billing_email,false);
                                                    break;
                                                case "item_count":
												case "transaction_id":
												case "order_item_count":
                                                    $td_value = $order_item->$key;
                                                    $td_class .= " amount";
                                                    break;
												case "order_date":
                                                    $td_value = date($date_format,strtotime($order_item->$key));
                                                    break;
                                                case "order_shipping":
                                                case "order_shipping_tax":
                                                case "order_tax":
                                                case "total_tax":
												case "gross_amount":
                                                case "order_discount":
												case "cart_discount":
												case "total_discount":
                                                case "order_total":
												case "refund_amount":
												case "part_order_refund_amount":
												case "order_refund_amount":
                                                    $td_value = isset($order_item->$key) ? $order_item->$key : 0;
													$td_value = $td_value > 0 ? $this->price($td_value, array('currency' => $order_item->order_currency)) : $zero_prize[$order_item->order_currency];
													$td_class .= " amount";
                                                    break;
                                                case "order_status"://New Change ID 20140918
												case "order_status_name"://New Change ID 20150225
													$td_value = isset($order_item->$key) ? $order_item->$key : '';
													$td_value = '<span class="order-status order-status-'.sanitize_title($td_value).'">'.ucwords(__($td_value, 'icwoocommerce_textdomains')).'</span>';
													break;												
                                                default:
                                                    $td_value = isset($order_item->$key) ? $order_item->$key : '';
                                                    break;
                                            endswitch;
                                            $td_content = "<td class=\"{$td_class}\">{$td_value}</td>\n";
                                            echo $td_content;
                                        endforeach;                                        	
                                    ?>
                                </tr>
                                <?php 
                            } ?>
                        </tbody>           
                    </table>
                    <style type="text/css">
                    	.iccommercepluginwrap th.order_date {
							width:auto;
						}
                    </style>
                    <span class="ViewAll"><a href="<?php echo $all_admin_url;?>"><?php _e("View All",'icwoocommerce_textdomains');?></a></span>
				<?php 
					else:
						echo '<p>'.__("No Order found.", 'icwoocommerce_textdomains').'</p>';
					endif;
		}	
		
		//New Change ID 20141125
		function top_customer_list($shop_order_status,$hide_order_status,$start_date,$end_date){
			global $wpdb,$options;
				$optionsid	= "top_customer_per_page";
				$per_page 	= $this->get_number_only($optionsid,$this->per_page_default);
				
				$sql = "SELECT SUM(postmeta1.meta_value) AS 'Total' 
								,postmeta2.meta_value AS 'BillingEmail'
								,postmeta3.meta_value AS 'FirstName'
								,postmeta5.meta_value AS 'LastName'
								,postmeta6.meta_value AS 'CompanyName'
								,CONCAT(postmeta3.meta_value, ' ',postmeta5.meta_value) AS BillingName
								,Count(postmeta2.meta_value) AS 'OrderCount'";
						
						//$sql .= " ,postmeta4.meta_value AS  customer_id";
						//
						$sql .= " FROM {$wpdb->prefix}posts as posts
						LEFT JOIN  {$wpdb->prefix}postmeta as postmeta1 ON postmeta1.post_id=posts.ID
						LEFT JOIN  {$wpdb->prefix}postmeta as postmeta2 ON postmeta2.post_id=posts.ID
						LEFT JOIN  {$wpdb->prefix}postmeta as postmeta3 ON postmeta3.post_id=posts.ID
						LEFT JOIN  {$wpdb->prefix}postmeta as postmeta5 ON postmeta5.post_id=posts.ID
						LEFT JOIN  {$wpdb->prefix}postmeta as postmeta6 ON postmeta6.post_id=posts.ID";
						
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
							AND postmeta5.meta_key='_billing_last_name'
							AND postmeta6.meta_key='_billing_company'";
							
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
						Order By Total DESC
						LIMIT {$per_page}";
						
				$order_items = $wpdb->get_results($sql );
				
				//$this->print_array($order_items);
				if(count($order_items)>0):
				
				$admin_url 		= admin_url("admin.php?page=".$this->constants['plugin_key']."_details_page")."&end_date=".$end_date."&start_date=".$start_date."&detail_view=no{$url_hide_order_status}{$url_shop_order_status}&";				
				$all_admin_url 	= admin_url("admin.php?page=".$this->constants['plugin_key']."_report_page")."&end_date=".$end_date."&start_date=".$start_date."{$url_hide_order_status}{$url_shop_order_status}&report_name=customer_page";
				$admin_user = admin_url("user-edit.php");
				
				//
				
					?>
                
                
				<table style="width:100%" class="widefat">
					<thead>
						<tr class="first">
							<th><?php _e( 'Billing Name', 'icwoocommerce_textdomains'); ?></th>
							<th><?php _e( 'Company Name', 'icwoocommerce_textdomains'); ?></th>
							<th><?php _e( 'Billing Email', 'icwoocommerce_textdomains'); ?></th>
                            <th class="order_count"><?php _e( 'Order Count', 'icwoocommerce_textdomains'); ?></th>
							<th class="amount_column"><?php _e( 'Amount', 'icwoocommerce_textdomains'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php					
							foreach ( $order_items as $key => $order_item ) {
								$user_name = '-';
								$first_name = $order_item->FirstName;
								$billing_name = $order_item->BillingName;
								
								
									
								if(isset($order_item->customer_id) and strlen($order_item->customer_id) > 0 and $order_item->customer_id > 0){
									$user_details = $this->get_user_details($order_item->customer_id);
									
									
									$user_name = $user_details->user_name;
									$first_name = $user_details->first_name;
									
									$user_name = '<a href="'.$admin_user."?user_id=".$order_item->customer_id.'" target="_blank">'.$user_name.'</a>';
								}
								
								
								if($key%2 == 1){$alternate = "alternate ";}else{$alternate = "";};
								
								?>
								
								<tr class="<?php echo $alternate."row_".$key;?>">
                                    <td><a href="<?php echo $admin_url."paid_customer=".$order_item->BillingEmail;?>"><?php echo $billing_name;?></a></td>
									<td><?php echo $order_item->CompanyName?></td>
                                    <td><a href="<?php echo $admin_url."paid_customer=".$order_item->BillingEmail;?>"><?php echo $order_item->BillingEmail?></a></td>
									<td class="order_count"><?php echo $order_item->OrderCount?></td>                                    
									<td class="amount_column amount"><?php echo $this->price($order_item->Total)?></td>
								</tr>
							 <?php } ?>	
					<tbody>           
				</table>
                <span class="ViewAll"><a href="<?php echo $all_admin_url;?>"><?php _e("View All",'icwoocommerce_textdomains');?></a></span>
				<?php
				else:
					echo '<p>'.__("No Customer found.", 'icwoocommerce_textdomains').'</p>';
				endif;		
			}
			
			
			//New Change ID 20140918
			function get_top_coupon_list($shop_order_status,$hide_order_status,$start_date,$end_date){
					global $wpdb,$options;

					$optionsid	= "top_coupon_per_page";
					$per_page 	= $this->get_number_only($optionsid,$this->per_page_default);
					$sql = "SELECT *, 
					woocommerce_order_items.order_item_name, 
					SUM(woocommerce_order_itemmeta.meta_value) As 'Total', 
					woocommerce_order_itemmeta.meta_value AS 'coupon_amount' , 
					Count(*) AS 'Count' 
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
					ORDER BY Total DESC
					LIMIT {$per_page}";
					 
					$order_items = $wpdb->get_results($sql); 
					if(count($order_items)>0):
                    	$all_admin_url = admin_url("admin.php?page=".$this->constants['plugin_key']."_report_page")."&end_date=".$end_date."&start_date=".$start_date."{$url_hide_order_status}{$url_shop_order_status}&report_name=coupon_page";
						$admin_url 		= admin_url("admin.php?page=".$this->constants['plugin_key']."_details_page")."&end_date=".$end_date."&start_date=".$start_date."&detail_view=no{$url_hide_order_status}{$url_shop_order_status}&";
						?>
                     
                        <table style="width:100%" class="widefat">
                            <thead>
                                <tr class="first">
                                    <th><?php _e( 'Coupon Code', 'icwoocommerce_textdomains'); ?></th>
                                    <!--<th><?php //_e( 'Coupon Amount', 'icwoocommerce_textdomains'); ?></th>-->
                                    <th class="order_count"><?php _e( 'Coupon Count', 'icwoocommerce_textdomains'); ?></th>
                                    <th class="amount_column"><?php _e( 'Coupon Amount', 'icwoocommerce_textdomains'); ?></th>                           
                                </tr>
                            </thead>
                            <tbody>
                                <?php					
                                foreach ( $order_items as $key => $order_item ) {
                                if($key%2 == 1){$alternate = "alternate ";}else{$alternate = "";};
                                ?>
                                    <tr class="<?php echo $alternate."row_".$key;?>">                                        
                                        <td><a href="<?php echo $admin_url."coupon_code={$order_item->order_item_name}"?>" target="<?php echo $order_item->order_item_name?>_blank"><?php echo $order_item->order_item_name?></a><?php //echo $order_item->order_item_name?></td>
                                        <!--<td><?php //echo $order_item->coupon_amount?></td>-->
                                        <td class="order_count"><?php echo $order_item->Count?></td>
                                        <td class="amount_column amount"><?php echo $this->price($order_item->Total);?></td>
                                     <?php } ?>		
                                    </tr>
                            <tbody>           
                        </table>
                        <span class="ViewAll"><a href="<?php echo $all_admin_url;?>"><?php _e("View All",'icwoocommerce_textdomains');?></a></span>
				<?php  
					else:
						echo '<p>'.__("No Coupons found.", 'icwoocommerce_textdomains').'</p>';
					endif;
			}
			
			function get_part_order_refund_amount($type = "today",$shop_order_status,$hide_order_status,$start_date,$end_date){
				global $wpdb;
				
				$today_date 			= $this->today;
				$yesterday_date 		= $this->yesterday;
				
				$sql = " SELECT SUM(postmeta.meta_value) 		as total_amount
						
				FROM {$wpdb->prefix}posts as posts
								
				LEFT JOIN  {$wpdb->prefix}postmeta as postmeta ON postmeta.post_id	=	posts.ID";
				
				$sql .= " LEFT JOIN  {$wpdb->prefix}posts as shop_order ON shop_order.ID	=	posts.post_parent";
				
				if($this->constants['post_order_status_found'] == 0 ){
					if(count($shop_order_status)>0){
						$sql .= " 
						LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
						LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
					}
				}
				
				$sql .= " WHERE posts.post_type = 'shop_order_refund' AND  postmeta.meta_key='_refund_amount'";
				
				$sql .= " AND shop_order.post_type = 'shop_order'";
						
				if($this->constants['post_order_status_found'] == 0 ){
					$refunded_id 	= $this->get_old_order_status(array('refunded'), array('wc-refunded'));
					$refunded_id    = implode(",".$refunded_id);
					$sql .= " AND terms2.term_id NOT IN (".$refunded_id .")";
					
					if(count($shop_order_status)>0){
						$in_shop_order_status = implode(",",$shop_order_status);
						$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
					}
				}else{
					$sql .= " AND shop_order.post_status NOT IN ('wc-refunded')";
					
					if(count($shop_order_status)>0){
						$in_shop_order_status		= implode("', '",$shop_order_status);
						$sql .= " AND  shop_order.post_status IN ('{$in_shop_order_status}')";
					}
				}
				
				if ($start_date != NULL &&  $end_date != NULL && $type == "total"){
					$sql .= " AND DATE(posts.post_date) BETWEEN '{$start_date}' AND '{$end_date}'";
				}
				
				if($type == "today") $sql .= " AND DATE(posts.post_date) = '{$today_date}'";
				
				if($type == "yesterday") 	$sql .= " AND DATE(posts.post_date) = '{$yesterday_date}'";
				
				if(count($hide_order_status)>0){
					$in_hide_order_status		= implode("', '",$hide_order_status);
					$sql .= " AND  shop_order.post_status NOT IN ('{$in_hide_order_status}')";
				}
				
				$sql .= " LIMIT 1";
				
				//$this->print_sql($sql);
			
				$wpdb->query("SET SQL_BIG_SELECTS=1");
				
				$order_items = $wpdb->get_var($sql);
				
				return $order_items;
				
			}
			
			public $is_product_active = NULL;
			public function is_active(){
				$r = false;
				if($this->is_product_active == NULL){					
					$actived_product = get_option($this->constants['plugin_key'] . '_activated');
					$this->is_product_active = 0;
					if($actived_product)
					foreach($actived_product as $key => $value){
						if($this->constants['plugin_file_id'] == $key && $value == 1){
							$r = true;
							$this->is_product_active = 1;
						}
					}
				}
				return $r;
			}
			
			function get_coumns($report_name = 'recent_order'){
				$grid_column 	= $this->get_grid_columns();				
				return $grid_column->get_dasbboard_coumns($report_name);
			}
	}
}
