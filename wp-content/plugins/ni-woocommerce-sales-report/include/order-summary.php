<?php 
include_once('report-function.php');  
if( !class_exists( 'Summary' ) ) {
	class Summary extends ReportFunction{
		public function __construct(){
			//$this->get_low_in_stock();
			//$this->init();
			
		}
		function init(){
		?>
		<div class="parent_content">
			<div class="content" >
            	<div style="height:50px;">
				
			</div>
            	<div class="ni-pro-info" style="border:2px solid #43A047">
            	<h3 style="text-align:center; font-size:20px; padding:0px; margin:10px; color:#78909C ">
            	Monitor your sales and grow your online business
                </h3>
				
				<h1 style="text-align:center; color:#2cc185">Buy Ni WooCommerce Sales Report Pro @ $24.00</h1>
				<div style="width:33%; float:left; padding:5px">
					<ul>
						<li>Dashboard order Summary</li>
						<li>Order List - Display order list</li>
						<li>Order Detail - Display Product information</li>
                        <li style="font-weight:bold; color:#2cc185">Sold Product variation Report</li>
						<li>Customer Sales Report</li>
					</ul>
				</div>
				<div style="width:33%;padding:5px; float:left">
					<ul>
						<li>Payment Gateway Sales Report</li>
						<li>Country Sales Report</li>
						<li>Coupon Sales Report</li>
						<li>Order Status Sales Report</li>
                        <li style="font-weight:bold; color:#2cc185">Stock Report(Simple, Variable and Variation Product)</li>
					</ul>
				</div>
				<div>
					<ul>
						<li><span style="color:#26A69A">Email at: <a href="mailto:support@naziinfotech.com">support@naziinfotech.com</a></span></li>
						<li><a href="http://demo.naziinfotech.com?demo_login=woo_sales_report" target="_blank">View Demo</a>  </li>
						<li><a href="http://naziinfotech.com/?product=ni-woocommerce-sales-report-pro" target="_blank">Buy Now</a>  </li>
						<li>Coupon Code: <span style="color:#26A69A; font-size:16px"><span style="font-size:24px; font-weight:bold;color:#F00">ni10</span>Get 10% OFF</span></li>
						
					</ul>
				 </div>
				 
			   
				  <div style="clear:both"></div>
				  <div style="width:100%;padding:5px; float:left">
                  <p  style="width:100%;padding:5px; font-size:16px;color:#F00">
                  <strong>
                  We will create new custom report as per custom requirement, if you require more analytic report or require any customization in this report then please feel free to contact with us.
                  </strong>
                  </p>
               
				  <b> For any WordPress or woocommerce customization, queries and support please email at : <strong><a href="mailto:support@naziinfotech.com">support@naziinfotech.com</a></strong></b>
				  </div>
				  <div style="clear:both"></div>
				  
			</div>
            	<div style="height:50px;">
				
			</div>
				<div class="box-title"><i class="fa fa-tachometer" aria-hidden="true"></i><?php _e('Dashboard - Sales Analysis', 'nisalesreport'); ?> </div>
				<div style="border-bottom:4px solid #2cc185;"></div>
				<div class="box-data">
					<div class="columns-box" style="border-top:4px solid #BA68C8">
						<div class="columns-title"><?php _e('Total Sales', 'nisalesreport'); ?></div>
						<div>
							<div class="columns-icon" style="color:#BA68C8"><i class="fa fa-cart-plus fa-4x"></i></div>
							<div class="columns-value" style="color:#BA68C8;"><?php  echo wc_price( $this->get_total_sales("ALL")); ?></div>	
						</div>
					</div>
					<div class="columns-box"  style="border-top:4px solid #EF6C00">
						<div class="columns-title"><?php _e('This Year Sales', 'nisalesreport'); ?></div>
						<div>
							<div class="columns-icon"  style="color:#EF6C00"><i class="fa fa-cart-plus fa-4x"></i></div>
							<div class="columns-value"  style="color:#EF6C00"><?php  echo wc_price( $this->get_total_sales("YEAR")); ?></div>	
						</div>
					</div>
					<div class="columns-box" style="border-top:4px solid #00897B">
						<div class="columns-title"><?php _e('This Month Sales', 'nisalesreport'); ?></div>
						<div>
							<div class="columns-icon"  style="color:#00897B"><i class="fa fa-cart-plus fa-4x"></i></div>
							<div class="columns-value" style="color:#00897B"><?php  echo wc_price( $this->get_total_sales("MONTH")); ?></div>	
						</div>
					</div>
					<div class="columns-box"  style="border-top:4px solid #039BE5">
						<div class="columns-title"><?php _e('This Week Sales', 'nisalesreport'); ?></div>
						<div>
							<div class="columns-icon" style="color:#039BE5"><i class="fa fa-cart-plus fa-4x"></i></div>
							<div class="columns-value" style="color:#039BE5"><?php  echo wc_price( $this->get_total_sales("WEEK")); ?></div>	
						</div>
					</div>
					<div class="columns-box"  style="border-top:4px solid #795548">
						<div class="columns-title"><?php _e('Yesterday Sales', 'nisalesreport'); ?></div>
						<div>
							<div class="columns-icon"  style="color:#795548"><i class="fa fa-cart-plus fa-4x"></i></div>
							<div class="columns-value"  style="color:#795548"><?php  echo wc_price( $this->get_total_sales("YESTERDAY")); ?></div>	
						</div>
					</div>
					<div style="clear:both"></div>
				</div>
                <div class="box-data">
					<div class="columns-box"  style="border-top:4px solid #BA68C8">
						<div class="columns-title"><?php _e('Total Sales Count', 'nisalesreport'); ?></div>
						<div>
							<div class="columns-icon" style="color:#BA68C8"><i class="fa fa-line-chart fa-3x"></i></div>
							<div class="columns-value" style="color:#BA68C8"><?php echo $this->get_total_sales_count("ALL"); ?></div>	
						</div>
					</div>
					<div class="columns-box" style="border-top:4px solid #EF6C00">
						<div class="columns-title"><?php _e('This Year Sales Count', 'nisalesreport'); ?></div>
						<div>
							<div class="columns-icon" style="color:#EF6C00"><i class="fa fa-line-chart fa-3x"></i></div>
							<div class="columns-value" style="color:#EF6C00"><?php echo $this->get_total_sales_count("YEAR"); ?></div>	
						</div>
					</div>
					<div class="columns-box" style="border-top:4px solid #00897B">
						<div class="columns-title"><?php _e('This Month Sales Count', 'nisalesreport'); ?></div>
						<div>
							<div class="columns-icon"  style="color:#00897B"><i class="fa fa-line-chart fa-3x"></i></div>
							<div class="columns-value"  style="color:#00897B"><?php echo $this->get_total_sales_count("MONTH"); ?></div>
						</div>
					</div>
					<div class="columns-box" style="border-top:4px solid #039BE5">
						<div class="columns-title"><?php _e('This Week Sales Count', 'nisalesreport'); ?></div>
						<div>
							<div class="columns-icon" style="color:#039BE5"><i class="fa fa-line-chart fa-3x"></i></div>
							<div class="columns-value" style="color:#039BE5"><?php echo $this->get_total_sales_count("WEEK"); ?></div>
						</div>
					</div>
					<div class="columns-box"  style="border-top:4px solid #795548">
						<div class="columns-title"><?php _e('Yesterday Sales Count', 'nisalesreport'); ?></div>
						<div>
							<div class="columns-icon" style="color:#795548"><i class="fa fa-line-chart fa-3x"></i></div>
							<div class="columns-value" style="color:#795548"><?php echo $this->get_total_sales_count("YESTERDAY"); ?></div>
						</div>
					</div>
					<div style="clear:both"></div>
				</div>
				<div class="box-title"><i class="fa fa-pie-chart" aria-hidden="true"></i> <?php _e('Customer Analysis', 'nisalesreport'); ?> </div>
				<div style="border-bottom:4px solid #2cc185;"></div>
                <div class="box-data">
					<div class="columns-box" style="border-top:4px solid #BA68C8">
						<div class="columns-title"><?php _e('Total Customer Count', 'nisalesreport'); ?></div>
						<div>
							<div class="columns-icon" style="color:#BA68C8"><i class="fa fa-user-circle-o fa-3x"></i></div>
							<div class="columns-value" style="color:#BA68C8">
							
							<?php echo $this->get_customer(); ?>
                            </div>	
						</div>
					</div>
					<div class="columns-box" style="border-top:4px solid #EF6C00">
						<div class="columns-title"><?php _e('Today Customer Count', 'nisalesreport'); ?></div>
						<div>
							<div class="columns-icon" style="color:#EF6C00"><i class="fa fa-user-circle-o fa-3x"></i></div>
							<div class="columns-value" style="color:#EF6C00">
							<?php echo$this->get_customer(date_i18n("Y-m-d"),date_i18n("Y-m-d")); ?>
                            </div>	
						</div>
					</div>
					<div class="columns-box" style="border-top:4px solid #00897B">
						<div class="columns-title"><?php _e('Total Guest Customer Count', 'nisalesreport'); ?></div>
						<div>
							<div class="columns-icon"  style="color:#00897B"><i class="fa fa-user-o fa-3x"></i></div>
							<div class="columns-value"  style="color:#00897B">
							<?php echo $this->get_guest_customer(); ?>
                            </div>
						</div>
					</div>
					<div class="columns-box" style="border-top:4px solid #039BE5">
						<div class="columns-title"><?php _e('Today Guest Cust. Count', 'nisalesreport'); ?></div>
						<div>
							<div class="columns-icon" style="color:#039BE5"><i class="fa fa-user-o fa-3x"></i></div>
							<div class="columns-value" style="color:#039BE5">
							<?php echo $this->get_guest_customer(date_i18n("Y-m-d"),date_i18n("Y-m-d")); ?>
                            </div>
						</div>
					</div>
					
					<div style="clear:both"></div>
				</div>

                <div class="box-title"><i class="fa fa-pie-chart" aria-hidden="true"></i><?php _e('Today  Sales Analysis', 'nisalesreport'); ?> </div>
				<div style="border-bottom:4px solid #2cc185;"></div>
                <div class="box-data">
					<div class="columns-box" style="border-top:4px solid #BA68C8">
						<div class="columns-title"><?php _e('Today Order Count', 'nisalesreport'); ?></div>
						<div>
							<div class="columns-icon" style="color:#BA68C8"><i class="fa fa-user-circle fa-3x"></i></div>
							<div class="columns-value" style="color:#BA68C8">
							
							<?php echo $this->get_total_sales_count("DAY"); ?>
                            </div>	
						</div>
					</div>
					<div class="columns-box" style="border-top:4px solid #EF6C00">
						<div class="columns-title"><?php _e('Today Sales', 'nisalesreport'); ?></div>
						<div>
							<div class="columns-icon" style="color:#EF6C00"><i class="fa fa-user fa-3x"></i></div>
							<div class="columns-value" style="color:#EF6C00"><?php echo wc_price( $this->get_total_sales("DAY")); ?></div>	
						</div>
					</div>
					<div class="columns-box" style="border-top:4px solid #00897B">
						<div class="columns-title"><?php _e('Today Product Sold', 'nisalesreport'); ?></div>
						<div>
							<div class="columns-icon"  style="color:#00897B"><i class="fa fa-product-hunt fa-3x"></i></div>
							<div class="columns-value"  style="color:#00897B"><?php echo $this->get_sold_product_count( date_i18n("Y-m-d"), date_i18n("Y-m-d")); ?></div>
						</div>
					</div>
					<div class="columns-box"  style="border-top:4px solid #039BE5">
						<div class="columns-title"><?php _e('Today Discount', 'nisalesreport'); ?></div>
						<div>
							<div class="columns-icon" style="color:#039BE5"><i class="fa fa-minus-square fa-3x"></i></div>
							<div class="columns-value" style="color:#039BE5"><?php echo wc_price($this->get_total_discount(date_i18n("Y-m-d"), date_i18n("Y-m-d"))); ?></div>
						</div>
					</div>
					<div class="columns-box"  style="border-top:4px solid #795548">
						<div class="columns-title"><?php _e('Today Tax', 'nisalesreport'); ?></div>
						<div>
							<div class="columns-icon" style="color:#795548"><i class="fa fa-plus-square fa-3x"></i></div>
							<div class="columns-value" style="color:#795548"><?php echo  wc_price($this->get_total_tax( date_i18n("Y-m-d"), date_i18n("Y-m-d"))); ?></div>
						</div>
					</div>
					<div style="clear:both"></div>
				</div>
                
                
                <div class="box-title"><i class="fa fa-pie-chart" aria-hidden="true"></i> <?php _e('Stock Analysis', 'nisalesreport'); ?> </div>
				<div style="border-bottom:4px solid #2cc185;"></div>
                <div class="box-data">
					<div class="columns-box" style="border-top:4px solid #BA68C8">
						<div class="columns-title"><?php _e('Low in stock', 'nisalesreport'); ?></div>
						<div>
							<div class="columns-icon" style="color:#BA68C8"><i class="fa fa-truck fa-3x"></i></div>
							<div class="columns-value" style="color:#BA68C8">
							
                         
                            <a href="<?php echo admin_url("admin.php")."?page=wc-reports&tab=stock&report=low_in_stock"; ; ?>"><?php   echo $this->get_low_in_stock(); ?></a>
							
                            </div>	
						</div>
					</div>
					<div class="columns-box" style="border-top:4px solid #EF6C00">
						<div class="columns-title"><?php _e('Out of stock', 'nisalesreport'); ?></div>
						<div>
							<div class="columns-icon" style="color:#EF6C00"><i class="fa fa-truck fa-3x"></i></div>
							<div class="columns-value" style="color:#EF6C00">
							<?php //echo $this->get_out_of_stock(); ?>
                            
                              <a href="<?php echo admin_url("admin.php")."?page=wc-reports&tab=stock&report=out_of_stock"; ; ?>"><?php   echo $this->get_out_of_stock(); ?></a>
                            
                            
                            </div>	
						</div>
					</div>
					<div class="columns-box" style="border-top:4px solid #00897B">
						<div class="columns-title"><?php _e('Most Stocked', 'nisalesreport'); ?></div>
						<div>
							<div class="columns-icon"  style="color:#00897B"><i class="fa fa-truck fa-3x"></i></div>
							<div class="columns-value"  style="color:#00897B">
							<?php //echo $this->get_most_stock(); ?>
                            
                            <a href="<?php echo admin_url("admin.php")."?page=wc-reports&tab=stock&report=most_stocked"; ; ?>"><?php   echo $this->get_most_stock(); ?></a>
                            
                            </div>
						</div>
					</div>
					<div style="clear:both"></div>
				</div>
                
                
                <div style="clear:both"></div>
                <?php do_action("ni_sales_report_dashboard_after_today_summary"); ?>
			</div>
			<div class="content">
				<div class="box-title"><i class="fa fa-pie-chart" aria-hidden="true"></i> <?php _e( 'recent orders', 'nisalesreport'); ?> </div>
				<div style="border-bottom:4px solid #2cc185;"></div>
				<div class="box-data">
					<table class="wooreport_default_table">
                    	<thead>
                        	<tr>
                           <th><?php _e( 'Order ID', 'nisalesreport'); ?></th>
                            <th><?php _e( 'Order Date', 'nisalesreport'); ?> </th>
                            <th><?php _e( 'First Name', 'nisalesreport'); ?> </th>
                            <th><?php _e( 'Billing Email', 'nisalesreport'); ?></th>
                            <th><?php _e( 'Country', 'nisalesreport'); ?> </th>
                            <th><?php _e( 'Order Status', 'nisalesreport'); ?>  </th>
                            <th><?php _e( 'Currency', 'nisalesreport'); ?> </th>
                            <th><?php _e( 'Order Total', 'nisalesreport'); ?>  </th>
						</tr>
                        </thead>
						
					   <?php $order_data = $this->get_recent_order_list();   ?>
					   <?php foreach($order_data as $key=>$v){ ?>
                       <tr>
                            <td><?php echo $v->order_id; ?></td>
                            <td><?php echo $v->order_date; ?></td>
                            <td><?php echo $v->billing_first_name; ?></td>
                            <td><?php echo $v->billing_email; ?></td>
                            <td><?php echo  $this->get_country_name( $v->billing_country); ?></td>
                            <td><?php echo  ucfirst ( str_replace("wc-","", $v->order_status)); ?></td>
                            <td><?php echo $v->order_currency; ?></td>
                            <td style="text-align:right"><?php echo wc_price($v->order_total); ?></td>
                        </tr>
                        <?php } ?>
					</table>
				</div>
			</div>
			<div style="height:20px;">
				
			</div>
			<div class="content">
				<div style="width:49%; float:right;">
					<div class="box-title"><i class="fa fa-credit-card" aria-hidden="true"></i> <?php _e(' Order Status Pie Chart', 'nisalesreport'); ?></div>
					<div style="border-bottom:4px solid #2cc185;"></div>
				    <div class="box-data">
                    <?php $data = array(); ?>
                    <?php $data = $this->get_order_status(); ?>	
                    <?php 
					$total  = 0;
					foreach($data as $k=>$v){
						$total = $total +  $v->order_total;
					}
					foreach($data as $k=>$v){
						$data[$k]->value =  ( $v->order_total /$total ) *100;
						
						$data[$k]->order_status =  ucfirst ( str_replace("wc-","", $v->order_status));
						$data[$k]->order_total = wc_price($v->order_total);
					} 
					?>
                    <script type="text/javascript">
                     var data  =  <?php echo  json_encode ($data )?>;
					 var chart = AmCharts.makeChart( "order_status2", {
						
						autoMargins: false,
						marginTop: 0,
						marginBottom: 0,
						marginLeft: 0,
						marginRight: 0,
						pullOutRadius: 0,
						"type": "pie",
						"theme": "light",
						"dataProvider": data,
						"valueField": "value",
						"titleField": "order_status",
						"outlineAlpha": 0.4,
						"depth3D": 15,
						"balloonText": "[[title]]<br><span style='font-size:14px'><b>[[order_total]]</b> ([[percents]]%)</span>",
						"angle": 30,
						
						"maxLabelWidth": 100,
    					"innerRadius": "0%",
						
						"export": {
						"enabled": false
						}
					} );
                    </script>
					<div id="order_status2" style="width:100%; height:250px"></div>	
					</div>
				</div>
				<div style="width:49%;">
					<div class="box-title"><i class="fa fa-credit-card" aria-hidden="true"></i><?php _e('Order Status Report', 'nisalesreport'); ?> </div>
					<div style="border-bottom:4px solid #2cc185;"></div>
					<div class="box-data">
						<table class="wooreport_default_table">
                        	<thead>
                            	<tr>
                                <th><?php _e('Order Status', 'nisalesreport'); ?></th>
                                <th><?php _e('Order Count', 'nisalesreport'); ?></th>
                                <th><?php _e('Order Total', 'nisalesreport'); ?></th>
                            	</tr>
                            </thead>
							
                            <?php $results = $this->get_order_status();?>
                            <?php foreach($results as $key=>$value){ ?>
							<tr>
                                <td><?php echo  ucfirst ( str_replace("wc-","", $value->order_status)); ?></td>
                                <td style="text-align:right"><?php echo $value->order_count; ?></td>
                                <td style="text-align:right"><?php echo wc_price($value->order_total); ?></td>
                            </tr>
                            <?php }?>
						</table>
					</div>
				</div>
				<div style="clear:both"></div>
			</div>
            <div style="height:20px;">
				
			</div>
            <div class="content">
				<div style="width:49%; float:right;">
					<div class="box-title"><i class="fa fa-credit-card" aria-hidden="true"></i><?php _e('payment gateway pie chart', 'nisalesreport'); ?></div>
					<div style="border-bottom:4px solid #2cc185;"></div>
					<div class="box-data">
                    <?php $data = array(); ?>
                    <?php $data = $this->get_payment_gateway(); ?>	
                    <?php 
					$total  = 0;
					foreach($data as $k=>$v){
						$total = $total +  $v->order_total;
					}
					foreach($data as $k=>$v){
						$data[$k]->value =  ( $v->order_total /$total ) *100;
						//$data[$k]->payment_method_title =  "A";
						$data[$k]->order_total = wc_price($v->order_total);
						
					} 
					?>
                    <script type="text/javascript">
                     var data  =  <?php echo  json_encode ($data )?>;
					 var chart = AmCharts.makeChart( "_payment_gateway_pie_chart", {
						labelsEnabled: true,
						autoMargins: false,
						marginTop: 0,
						marginBottom: 0,
						marginLeft: 0,
						marginRight: 0,
						pullOutRadius: 0,
						"type": "pie",
						"theme": "light",
						"dataProvider": data,
						"valueField": "value",
						"titleField": "payment_method_title",
						"outlineAlpha": 0.4,
						"depth3D": 15,
						"balloonText": "[[title]]<br><span style='font-size:14px'><b>[[order_total]]</b> ([[percents]]%)</span>",
						"angle": 30,
						
						"maxLabelWidth": 100,
    					"innerRadius": "0%",
						
						"export": {
						"enabled": false
						}
					} );
                    </script>
					<div id="_payment_gateway_pie_chart" style="width:100%; height:250px"></div>	
					</div>
				</div>
				<div style="width:49%;">
					<div class="box-title"><i class="fa fa-credit-card" aria-hidden="true"></i> <?php _e( 'payment gateway report', 'nisalesreport'); ?> </div>
					<div style="border-bottom:4px solid #2cc185;"></div>
					<div class="box-data">
						<table class="wooreport_default_table">
                        	<thead>
                            	<tr>
                                <th><?php _e( 'Payment Method', 'nisalesreport'); ?> </th>
                                <th><?php _e( 'Order Count', 'nisalesreport'); ?></th>
                                <th><?php _e( 'Order Total', 'nisalesreport'); ?></th>
                            </tr>
                            </thead>
							<?php $data  = $this->get_payment_gateway(); ?>
                            <?php  foreach($data  as $k=>$v){ ?>
							<tr>
                                <td><?php echo $v->payment_method_title; ?></td>
                                <td><?php echo $v->order_count; ?></td>
                                <td><?php echo wc_price($v->order_total); ?></td>
                            </tr>
                           <?php } ?> 
						</table>
					</div>
				</div>
				<div style="clear:both"></div>
			</div>
            <div style="height:20px;">
				
			</div>
            <!-- Customer Report -->
            <div class="content">
				<div style="width:49%; float:right;">
					<div class="box-title"><i class="fa fa-credit-card" aria-hidden="true"></i><?php _e('Top 5 Customer Report', 'nisalesreport'); ?> </div>
					<div style="border-bottom:4px solid #2cc185;"></div>
					<div class="box-data">
						<table class="wooreport_default_table">
                        	<thead>
                            	<tr>
                                <th><?php _e('First Name', 'nisalesreport'); ?>  </th>
                                <th><?php _e('Email Address', 'nisalesreport'); ?></th>
                                <th><?php _e('Order Count', 'nisalesreport'); ?></th>
                                <th><?php _e('Order Total', 'nisalesreport'); ?></th>
                            </tr>
                            </thead>
							
                            <?php $data  = $this->get_customer_report(); ?>
                            <?php 
							if (count($data)==0){
							?>
                            <tr>
                            	<td colspan="4"><?php _e('No Customer found', 'nisalesreport'); ?></td>
                            </tr>
                            <?php
							} 
							 ?>
                            <?php  foreach($data  as $k=>$v){ ?>
							<tr>
                                <td><?php echo $v->billing_first_name; ?></td>
                                <td><?php echo $v->billing_email; ?></td>
                                 <td><?php echo $v->order_count; ?></td>
                                <td><?php echo $this->get_price($v->order_total); ?></td>
                            </tr>
                           <?php } ?> 
						</table>
					</div>
				</div>
				<div style="width:49%;">
					<div class="box-title"><i class="fa fa-credit-card" aria-hidden="true"></i><?php _e('TOP 5 Country REPORT', 'nisalesreport'); ?> </div>
					<div style="border-bottom:4px solid #2cc185;"></div>
					<div class="box-data">
						<table class="wooreport_default_table">
                        	<thead>
                            	<tr>
                                <th><?php _e('Country', 'nisalesreport'); ?></th>
                                <th><?php _e('Order Count', 'nisalesreport'); ?></th>
                                <th><?php _e('Order Total', 'nisalesreport'); ?></th>
                            </tr>
                            </thead>
							
                            <?php $data  = $this->get_country_report(); ?>
                            <?php  foreach($data  as $k=>$v){ ?>
							<tr>
                                <td><?php echo $this->get_country_name( $v->billing_country); ?></td>
                                <td><?php echo $v->order_count; ?></td>
                                <td><?php echo $this->get_price($v->order_total); ?></td>
                            </tr>
                           <?php } ?> 
						</table>
					</div>
				</div>
				<div style="clear:both"></div>
			</div>
            
            
        </div>
		<?php
		}
		
		function get_total_sales($period="CUSTOM",$start_date=NULL,$end_date=NULL){
			global $wpdb;
			$today_date = date_i18n("Y-m-d");	
			$query = "SELECT
					SUM(order_total.meta_value)as 'total_sales'
					FROM {$wpdb->prefix}posts as posts			
					LEFT JOIN  {$wpdb->prefix}postmeta as order_total ON order_total.post_id=posts.ID 
					
					WHERE 1=1
					AND posts.post_type ='shop_order' 
					AND order_total.meta_key='_order_total' ";
					
			$query .= " AND posts.post_status IN ('wc-pending','wc-processing','wc-on-hold', 'wc-completed')
						
						";
			if ($period =="YESTERDAY"){
					$query .= " AND   date_format( posts.post_date, '%Y-%m-%d') = DATE_SUB('$today_date', INTERVAL 1 DAY) "; 
			}
			if ($period =="DAY"){		
				$query .= " AND   date_format( posts.post_date, '%Y-%m-%d') = '{$today_date}' "; 
				$query .= " GROUP BY  date_format( posts.post_date, '%Y-%m-%d') ";
			
			
			}
			if ($period =="WEEK"){		
				//$query .= " AND   date_format( posts.post_date, '%Y-%m-%d') > DATE_SUB(date_format(NOW(), '%Y-%m-%d'), INTERVAL 1 WEEK) "; 
				$query .= "  AND  YEAR(date_format( posts.post_date, '%Y-%m-%d')) = YEAR(CURRENT_DATE()) AND 
      WEEK(date_format( posts.post_date, '%Y-%m-%d')) = WEEK(CURRENT_DATE()) ";
			}
			if ($period =="MONTH"){		
				//$query .= " AND   date_format( posts.post_date, '%Y-%m-%d') > DATE_SUB(date_format(NOW(), '%Y-%m-%d'), INTERVAL 1 MONTH) "; 
				$query .= "  AND  YEAR(date_format( posts.post_date, '%Y-%m-%d')) = YEAR(CURRENT_DATE()) AND 
      MONTH(date_format( posts.post_date, '%Y-%m-%d')) = MONTH(CURRENT_DATE()) ";
			}
			if ($period =="YEAR"){		
				//$query .= " AND YEAR(date_format( posts.post_date, '%Y-%m-%d')) = YEAR(date_format(NOW(), '%Y-%m-%d')) "; 
				$query .= " AND YEAR(date_format( posts.post_date, '%Y-%m-%d')) = YEAR(date_format(NOW(), '%Y-%m-%d')) "; 
			}
			
			
			//echo $query;		
					
			//$query .=" AND   date_format( posts.post_date, '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}'";
					
			$results = $wpdb->get_var($query);
			$results = isset($results) ? $results : "0";
			return $results;
		}
		function get_total_sales_count($period="CUSTOM",$start_date=NULL,$end_date=NULL){
			$today_date = date_i18n("Y-m-d");
			global $wpdb;	
			$query = "SELECT
					count(order_total.meta_value)as 'sales_count'
					FROM {$wpdb->prefix}posts as posts			
					LEFT JOIN  {$wpdb->prefix}postmeta as order_total ON order_total.post_id=posts.ID 
					
					WHERE  1 = 1
					AND posts.post_type ='shop_order' 
					AND order_total.meta_key='_order_total' ";
					//if ($start_date!=NULL && $end_date!=NULL)
					//$query .=" AND   date_format( posts.post_date, '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}'";
			$query .= " AND posts.post_status IN ('wc-pending','wc-processing','wc-on-hold', 'wc-completed') ";
			
			if ($period =="YESTERDAY"){
				$query .= " AND   date_format( posts.post_date, '%Y-%m-%d') = DATE_SUB('$today_date', INTERVAL 1 DAY) "; 
			}
			if ($period =="DAY"){		
				$query .= " AND   date_format( posts.post_date, '%Y-%m-%d') = '{$today_date}' "; 
				$query .= " GROUP BY  date_format( posts.post_date, '%Y-%m-%d') ";
			
			
			}
			if ($period =="WEEK"){		
				//$query .= " AND   date_format( posts.post_date, '%Y-%m-%d') > DATE_SUB(date_format(NOW(), '%Y-%m-%d'), INTERVAL 1 WEEK) "; 
				$query .= "  AND  YEAR(date_format( posts.post_date, '%Y-%m-%d')) = YEAR(CURRENT_DATE()) AND 
      WEEK(date_format( posts.post_date, '%Y-%m-%d')) = WEEK(CURRENT_DATE()) ";
			}
			if ($period =="MONTH"){		
			//	$query .= " AND   date_format( posts.post_date, '%Y-%m-%d') > DATE_SUB(date_format(NOW(), '%Y-%m-%d'), INTERVAL 1 MONTH) "; 
			
			$query .= "  AND  YEAR(date_format( posts.post_date, '%Y-%m-%d')) = YEAR(CURRENT_DATE()) AND 
      MONTH(date_format( posts.post_date, '%Y-%m-%d')) = MONTH(CURRENT_DATE()) ";
			
			}
			if ($period =="YEAR"){		
				//$query .= " AND YEAR(date_format( posts.post_date, '%Y-%m-%d')) = YEAR(date_format(NOW(), '%Y-%m-%d')) "; 
				$query .= " AND YEAR(date_format( posts.post_date, '%Y-%m-%d')) = YEAR(date_format(NOW(), '%Y-%m-%d')) "; 
			}
			//echo $query;
			$results = $wpdb->get_var($query);	
			$results = isset($results) ? $results : "0";	
			return $results;
		}
		function get_recent_order_list(){
			global $wpdb;
			$query = "SELECT
				posts.ID as order_id
				,posts.post_status as order_status
				
				, date_format( posts.post_date, '%Y-%m-%d') as order_date 
				
				FROM {$wpdb->prefix}posts as posts			
				
				WHERE 
						posts.post_type ='shop_order' 
						
						AND posts.post_status IN ('wc-pending','wc-processing','wc-on-hold', 'wc-completed' ,'wc-cancelled' ,  'wc-refunded' ,'wc-failed')
						
						";
			$query .= " order by posts.post_date DESC";	
			$query .= " LIMIT 10 ";
			$order_data = $wpdb->get_results( $query);	
			if(count($order_data)> 0){
				foreach($order_data as $k => $v){
					
					/*Order Data*/
					$order_id =$v->order_id;
					$order_detail = $this->get_order_detail($order_id);
					foreach($order_detail as $dkey => $dvalue)
					{
							$order_data[$k]->$dkey =$dvalue;
						
					}
					
				}
			}
			else
			{
				echo "No Record Found";
			}
			return $order_data;
		}
		function get_order_status($start_date = NULL, $end_date= NULL ){
			global $wpdb;
			$query = "
				SELECT 
				posts.ID as order_id
				,posts.post_status as order_status
				,date_format( posts.post_date, '%Y-%m-%d') as order_date 
				,SUM(postmeta.meta_value) as 'order_total'
				,count(posts.post_status) as order_count
				FROM {$wpdb->prefix}posts as posts	";		
				
			$query .=
				"	LEFT JOIN  {$wpdb->prefix}postmeta as postmeta ON postmeta.post_id=posts.ID ";
			
			$query .= " WHERE 1=1 ";
			
			if ($start_date && $end_date)	
			
			$query .= " AND date_format( posts.post_date, '%Y-%m-%d') BETWEEN  '{$start_date}' AND '{$end_date}'";	
			
			
			
			$query .= " AND postmeta.meta_key ='_order_total' ";
			$query .= " AND posts.post_type ='shop_order' ";
			
			$query .= " GROUP BY posts.post_status ";
			
			
			$results = $wpdb->get_results( $query);	
			return $results;
		}
		function get_payment_gateway(){
			global $wpdb;	
			$query = "
				SELECT 
				payment_method_title.meta_value as 'payment_method_title'
				
				,SUM(order_total.meta_value) as 'order_total'
				,count(*) as order_count
				FROM {$wpdb->prefix}posts as posts	";		
				
		
				
			$query .= "	LEFT JOIN  {$wpdb->prefix}postmeta as order_total ON order_total.post_id=posts.ID ";
			
			$query .= "	LEFT JOIN  {$wpdb->prefix}postmeta as payment_method_title ON payment_method_title.post_id=posts.ID ";
			
			
			$query .=	"WHERE 1=1 ";
				
			$query .= " AND posts.post_type ='shop_order' ";
			$query .= " AND order_total.meta_key ='_order_total' ";
			$query .= " AND payment_method_title.meta_key ='_payment_method_title' ";
			$query .= " GROUP BY payment_method_title.meta_value";
			
			$data = $wpdb->get_results($query);	
			
			return $data;	
		}
		function get_sold_product_count($start_date=NULL,$end_date =NULL){
			  global $wpdb;
			  $query = " SELECT  SUM(qty.meta_value) as sold_product_count  ";
			  $query .= " FROM {$wpdb->prefix}posts as posts ";
			  $query .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_items as line_item ON line_item.order_id=posts.ID  " ;
			  
			   $query .= "  LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as qty ON qty.order_item_id=line_item.order_item_id  ";
			  
			  $query .= " WHERE 1=1 ";
			  
			  
			  $query .= " AND posts.post_type ='shop_order' ";
			  $query .= " AND qty.meta_key ='_qty' ";
			  $query .= " AND line_item.order_item_type ='line_item' ";
			  $query .= " AND posts.post_status IN ('wc-processing','wc-on-hold', 'wc-completed')";
			 /*Wooc Include refund item in sold product count*/
			// $query .= " AND posts.post_status IN ('wc-processing','wc-on-hold', 'wc-completed','wc-refunded')";
			  
			  if ($start_date && $end_date)
				$query .= " AND date_format( posts.post_date, '%Y-%m-%d') BETWEEN  '{$start_date}' AND '{$end_date}'";	
				
			$results = $wpdb->get_var($query);	
			$results = isset($results) ? $results : "0";	
			return $results;
			  
		}
		function get_total_discount($start_date= NULL ,$end_date=NULL){
			 global $wpdb;	
			 $query = "";
			 $query = " SELECT
					
					SUM(woocommerce_order_itemmeta.meta_value) as total_discount
					
					FROM {$wpdb->prefix}posts as posts			
					LEFT JOIN  {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items ON woocommerce_order_items.order_id=posts.ID 
					
					LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id=woocommerce_order_items.order_item_id 
					
					
					WHERE 1=1
					AND posts.post_type ='shop_order'  ";
					
			$query .= " AND woocommerce_order_items.order_item_type ='coupon' ";	
			
			$query .= " AND woocommerce_order_itemmeta.meta_key ='discount_amount' ";	
				
			$query .= " AND posts.post_status IN ('wc-processing','wc-on-hold', 'wc-completed') ";
			if ($start_date && $end_date)
				$query .= " AND date_format( posts.post_date, '%Y-%m-%d') BETWEEN  '{$start_date}' AND '{$end_date}'";	
			
			$results = $wpdb->get_var( $query);	
			return $results ;
			//$this->print_data($results);
	    }
		 function get_total_tax($start_date =NULL, $end_date=NULL){
			 global $wpdb;	
			 $query = "";
			 //shipping_tax_amount
			$query = " SELECT " ;
			
			//10.13		
			$query .= "	(ROUND(SUM(woocommerce_order_itemmeta.meta_value),2)+  ROUND(SUM(shipping_tax_amount.meta_value),2)) as total_tax ";
			
			//10.12
			//$query .= "	(SUM(ROUND(woocommerce_order_itemmeta.meta_value,2))+  SUM(ROUND(shipping_tax_amount.meta_value,2))) as total_tax ";
					
				$query .= "	 FROM {$wpdb->prefix}posts as posts			
					LEFT JOIN  {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items ON woocommerce_order_items.order_id=posts.ID 
					
					LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id=woocommerce_order_items.order_item_id 
					
					
					LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as shipping_tax_amount ON shipping_tax_amount.order_item_id=woocommerce_order_items.order_item_id 
					
					
					
					WHERE 1=1
					AND posts.post_type ='shop_order'  ";
					
			$query .= " AND woocommerce_order_items.order_item_type ='tax' ";	
			
			$query .= " AND woocommerce_order_itemmeta.meta_key ='tax_amount' ";
			
			$query .= " AND shipping_tax_amount.meta_key ='shipping_tax_amount' ";	
				
			//$query .= " AND posts.post_status IN ('wc-processing','wc-on-hold', 'wc-completed','wc-pending') ";
			
			$query .= " AND posts.post_status IN ('wc-processing','wc-on-hold', 'wc-completed')";
			/*
			if ($this->report_order_status ==""){
					$query .= " AND posts.post_status IN ('wc-processing','wc-on-hold', 'wc-completed','wc-refunded')";
			}else{
				 $query .= " AND posts.post_status IN ('{$this->report_order_status}')";
			}
			*/
			
			if ($start_date && $end_date)
				$query .= " AND date_format( posts.post_date, '%Y-%m-%d') BETWEEN  '{$start_date}' AND '{$end_date}'";	
			
			$results = $wpdb->get_var( $query);	
			return $results;
			//$this->print_data($results);
		 }
		function get_order_detail($order_id){
			$order_detail	= get_post_meta($order_id);
			$order_detail_array = array();
			foreach($order_detail as $k => $v)
			{
				$k =substr($k,1);
				$order_detail_array[$k] =$v[0];
			}
			return 	$order_detail_array;
		}
		function get_customer($start_date =NULL, $end_date=NULL){
			 global $wpdb;	
			 $query = "";
			 $query .= " SELECT COUNT(customer_user.meta_value) as count ";
			 
			 $query .= "	 FROM {$wpdb->prefix}posts as posts		";
			 $query .= "	LEFT JOIN  {$wpdb->prefix}postmeta as customer_user ON customer_user.post_id=posts.ID ";
			 $query .= "	WHERE 1=1 ";
			 $query .= " AND posts.post_type ='shop_order'  ";
			 $query .= " AND customer_user.meta_key ='_customer_user' ";	
			 
			 if ($start_date && $end_date){
				$query .= " AND date_format( posts.post_date, '%Y-%m-%d') BETWEEN  '{$start_date}' AND '{$end_date}'";	 
			  }
			 
			 $query .= " AND customer_user.meta_value >0 ";
			 
			 	
			$row = $wpdb->get_var($query);	
			
			//$this->print_data($row);
			return $row;
		}
		function get_guest_customer($start_date =NULL, $end_date=NULL){
			 global $wpdb;	
			 $query = "";
			 $query .= " SELECT COUNT(customer_user.meta_value) as count ";
			 
			 $query .= "	 FROM {$wpdb->prefix}posts as posts		";
			 $query .= "	LEFT JOIN  {$wpdb->prefix}postmeta as customer_user ON customer_user.post_id=posts.ID ";
			 $query .= "	WHERE 1=1 ";
			 $query .= " AND posts.post_type ='shop_order'  ";
			 $query .= " AND customer_user.meta_key ='_customer_user' ";	
			 
			 $query .= " AND customer_user.meta_value=0 ";
			
			  if ($start_date && $end_date){
				$query .= " AND date_format( posts.post_date, '%Y-%m-%d') BETWEEN  '{$start_date}' AND '{$end_date}'";	 
			  }
			 	
			$row = $wpdb->get_var($query);	
			
			//$this->print_data($row);
			return $row;
		}
	}
}
?>