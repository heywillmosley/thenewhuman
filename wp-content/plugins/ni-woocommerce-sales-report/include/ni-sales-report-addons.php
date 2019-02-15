<?php
if ( ! defined( 'ABSPATH' ) ) { exit;}
if( !class_exists( 'ni_sales_report_addons' ) ) {
	class ni_sales_report_addons{
		public function __construct(){
			
		}
		function page_init(){
		?>
        <style>
        .ni-container-addons {
			width:98%;
	 		margin: auto;
	 		background-color:#FFF;
	 		margin-top:10px;
		}
		.ni-container-addons .ni-addons-content { 
			width:100%;  
			margin: 0 auto;
		}
		.ni-container-addons  .ni-addons-content .ni-addons-row {
		  overflow:hidden;
		}
		
		.ni-container-addons .ni-addons-row .ni-addons-column {
		  width:300px;
		  float:left;
		  margin:10px;
		  padding:10px;
		  position: relative;
		  
		}
		.ni-container-addons .ni-addons-row .ni-column-height {
			height:200px;
			border:2px solid #00BCD4;
		    
		}
		.ni-addons-column .ni-addons-lable { 
			font-weight:bold;
			font-size:16px;
			border-bottom:1px solid #00BCD4; 
			padding-bottom:5px
		 }
        </style>
        <div class="ni-container-addons">
        	<div class="ni-addons-content">
                <div class="ni-addons-row">
                	<div class="ni-addons-column" style="width:100%;">
                    <div  style="width:100%;text-align:center; font-size:24px;"><strong><?php _e(  'Hire us for plugin Development and Customization', 'nisalesreport') ?></strong></div>
                    <p>
                    <?php _e(  ' Our area of expertise is WordPress and custom plugins development. We specialize in creating custom plugin solutions for your business needs', 'nisalesreport') ?>
                   .</p>
                    <p><?php _e(  'Email us', 'nisalesreport') ?>: <strong><a href="mailto:support@naziinfotech.com">support@naziinfotech.com</a></strong></p>
                    <p style="font-weight:bold; font-size:24px; margin:0;"><?php _e(  'Our Other Free Wordpress Plugins', 'nisalesreport') ?></p>
                	</div>
                </div>
                <div style="clear:both"></div>
                 <div style="clear:both"></div>
                 <div class="ni-addons-row">
                    <div class="ni-addons-column ni-column-height">
                        <div class="ni-addons-lable">Ni WooCommerce Cost Of Goods</div>
                        <div class="ni-addons-value">
                        	<ul>
                      		<li><strong>Ability to add the cost of goods for simple and variation product</strong></li>
                            <li><strong>Dashboard report provide the total, monthly, yearly and daily sales amount, sales count, tax, and coupon</strong></li>
                      		<li><strong>Show sold product profit report</strong></li>
                        </ul>
                        <a href="https://wordpress.org/plugins/ni-woocommerce-cost-of-goods" target="_blank">View</a> 
                        <a href="https://downloads.wordpress.org/plugin/ni-woocommerce-cost-of-goods.zip" target="_blank">Download</a> 
                        </div>
                    </div>
                    <div class="ni-addons-column ni-column-height">
                        <div class="ni-addons-lable">Ni WooCommerce Custom Order Status</div>
                        <div class="ni-addons-value">
                        <ul>
                      		<li><strong>Add/Edit/Delete new WooCommerce order status</strong></li>
                      		<li><strong>Set Color to the order status</strong></li>
                            <li><strong>Display order status list</strong></li>
                            <li><strong>Add order status slug </strong></li>
                    	</ul>
                        <a href="https://wordpress.org/plugins/ni-woocommerce-custom-order-status" target="_blank">View</a> 
                        <a href="https://downloads.wordpress.org/plugin/ni-woocommerce-custom-order-status.zip" target="_blank">Download</a>
                         </div>
                    </div>
                    <div class="ni-addons-column ni-column-height">
                        <div class="ni-addons-lable">Ni Woocommerce Product Enquiry</div>
                        <div class="ni-addons-value">
                        	<ul>
                      		<li><strong>Display simple enquiry dashboard </strong></li>
                            <li><strong>Email Setting option</strong></li>
                      		<li><strong>Display enquiry form on the product page </strong></li>
                            <li><strong>Send email to client or admin</strong></li>
                    	</ul>
                        <a href="https://wordpress.org/plugins/ni-woocommerce-product-enquiry" target="_blank">View</a> 
                        <a href="https://downloads.wordpress.org/plugin/ni-woocommerce-product-enquiry.zip" target="_blank">Download</a>
                    </div>
                </div>
           		<div style="clear:both"></div>
            </div>
            	<div style="clear:both"></div>
                <div class="ni-addons-row">
                    <div class="ni-addons-column ni-column-height">
                        <div class="ni-addons-lable"  style="color:#D81B60">Ni WooCommerce Order Export</div>
                        <div class="ni-addons-value">
                        	<ul>
                      		<li><strong>Show the order summary on dashboard </strong></li>
                            <li><strong>Export customer billing details like billing name,customer email address, billing address details etc.</strong></li>
                      		<li><strong>Order Status Report</strong></li>
                            <li><strong>Payment Gateway report</strong></li>
                    	</ul>
                        <a href="https://wordpress.org/plugins/ni-woocommerce-sales-report-email" target="_blank">View</a> 
                        <a href="https://downloads.wordpress.org/plugin/ni-woocommerce-sales-report-email.zip" target="_blank">Download</a> 
                        </div>
                    </div>
                    <div class="ni-addons-column ni-column-height">
                        <div class="ni-addons-lable">Ni WooCommerce Dashboard Report</div>
                        <div class="ni-addons-value">
                        <ul>
                      		<li><strong>Display the sales summary on WordPress admin dashboard.</strong></li>
                      		<li><strong>Display the recent order on dashboard </strong></li>
                            <li><strong>Order status summary report</strong></li>
                            <li><strong>Show the sales analysis report on dashboard</strong></li>
                    	</ul>
                        	<a href="https://wordpress.org/plugins/ni-woocommerce-invoice" target="_blank">View</a> 
                        	<a href="https://downloads.wordpress.org/plugin/ni-woocommerce-invoice.zip" target="_blank">Download</a> 
                         </div>
                    </div>
                    <div class="ni-addons-column ni-column-height">
                        <div class="ni-addons-lable" style="color:#D81B60">Ni WooCommerce Sales Report By User Role</div>
                        <div class="ni-addons-value">
                        <ul>
                      		<li>Ability to create new sales agent or sales person</li>
                      		<li>Assign order to sales agent or vendor</li>
                            <li>Display the list of sales order with sales agent or <strong>sales person</strong> name</li>
                            <li>Filter the sales order by sales person or sales agent.</li>
                    	</ul>
                        <a href="https://wordpress.org/plugins/ni-woocommerce-sales-report-by-user-role/" target="_blank">View</a> 
                        <a href="https://downloads.wordpress.org/plugin/ni-woocommerce-sales-report-by-user-role.zip" target="_blank">Download</a> 
                    </div>
                </div>
           		<div style="clear:both"></div>
            </div>	   
            	<div style="clear:both"></div>
                <div class="ni-addons-row">
                    <div class="ni-addons-column ni-column-height">
                        <div class="ni-addons-lable">Ni WooCommerce Sales Report Email</div>
                        <div class="ni-addons-value">
                        	<ul>
                      		<li><strong>Display simple sales dashboard </strong></li>
                            <li><strong>Automatically email the daily sales report.</strong></li>
                      		<li><strong>Email WooCommerce sales order list</strong></li>
                            <li><strong>Email setting option and enable/disable cron job</strong></li>
                    	</ul>
                        <a href="https://wordpress.org/plugins/ni-woocommerce-sales-report-email" target="_blank">View</a> 
                        <a href="https://downloads.wordpress.org/plugin/ni-woocommerce-sales-report-email.zip" target="_blank">Download</a> 
                        </div>
                    </div>
                    <div class="ni-addons-column ni-column-height">
                        <div class="ni-addons-lable">Ni WooCommerce Invoice</div>
                        <div class="ni-addons-value">
                        <ul>
                      		<li><strong>Filter sales order by date range</strong></li>
                      		<li><strong>Export sales order invoice PDF </strong></li>
                            <li><strong>Display sales order list</strong></li>
                            <li><strong>Setting option for store name and footer notes</strong></li>
                    	</ul>
                        	<a href="https://wordpress.org/plugins/ni-woocommerce-invoice" target="_blank">View</a> 
                        	<a href="https://downloads.wordpress.org/plugin/ni-woocommerce-invoice.zip" target="_blank">Download</a> 
                         </div>
                    </div>
                    <div class="ni-addons-column ni-column-height">
                        <div class="ni-addons-lable">Ni CRM Lead</div>
                        <div class="ni-addons-value">
                        <ul>
                      		<li>Add/Edit/update and delete New Lead</li>
                      		<li>Display the lead list</li>
                            <li>Add Update, Delete Follow Up</li>
                            <li>Add, Delete, Service, Product and status</li>
                    	</ul>
                        <a href="https://wordpress.org/plugins/ni-crm-lead" target="_blank">View</a> 
                        <a href="https://downloads.wordpress.org/plugin/ni-crm-lead.zip" target="_blank">Download</a> 
                    </div>
                </div>
           		<div style="clear:both"></div>
            </div>	   
            	<div style="clear:both"></div>
                <div class="ni-addons-row">
                    <div class="ni-addons-column ni-column-height">
                        <div class="ni-addons-lable">Ni WooCommerce Product Editor</div>
                        <div class="ni-addons-value">
                        	<ul>
                      		<li><strong>Display simple, variable and variation product in tabular format</strong></li>
                            <li><strong>Provide the product filter by product name, mange backorder </strong></li>
                      		<li><strong>Ajax pagination and Ajax filter and Ajax data saving for better user experience </strong></li>
                        </ul>
                        <a href="https://wordpress.org/plugins/ni-woocommerce-product-editor" target="_blank">View</a> 
                        <a href="https://downloads.wordpress.org/plugin/ni-woocommerce-product-editor.zip" target="_blank">Download</a> 
                        </div>
                    </div>
                   
           		<div style="clear:both"></div>
            </div>
            
            	 <h3><?php _e(  'All Free Plugins', 'nisalesreport') ?></h3>
                <div>
                	<ul class="ni_other_plugin_link_ul">
	
    <li><a href="https://wordpress.org/plugins/ni-woocommerce-cost-of-goods/"  class="ni_other_plugin_link" target="_blank">Ni WooCommerce Cost Of Goods</a>  </li>
    <li><a href="https://wordpress.org/plugins/ni-woocommerce-product-enquiry/"  class="ni_other_plugin_link" target="_blank">Ni WooCommerce Product Enquiry</a>  </li>
    <li><a href="https://wordpress.org/plugins/ni-woocommerce-payment-gateway-charges/"  class="ni_other_plugin_link" target="_blank">Ni WooCommerce Payment Gateway Charges</a>  </li>
    <li><a href="https://wordpress.org/plugins/ni-woocommerce-custom-order-status/"  class="ni_other_plugin_link" target="_blank">Ni WooCommerce Custom Order Status</a>  </li>
    <li><a href="https://wordpress.org/plugins/ni-woocommerce-product-variations-table/"  class="ni_other_plugin_link" target="_blank">Ni WooCommerce Product Variations Table</a>  </li>
    <li><a href="https://wordpress.org/plugins/ni-woocommerce-sales-report-by-user-role/"  class="ni_other_plugin_link" target="_blank">Ni WooCommerce Sales Report By User Role</a>  </li>
    <li><a href="https://wordpress.org/plugins/ni-woocommerce-product-editor/"  class="ni_other_plugin_link" target="_blank">Ni WooCommerce Product Editor</a>  </li>
    <li><a href="https://wordpress.org/plugins/ni-woocommerce-order-export/"  class="ni_other_plugin_link" target="_blank">Ni WooCommerce Order Export</a>  </li>
    <li><a href="https://wordpress.org/plugins/ni-woocommerce-dashboard-report/"  class="ni_other_plugin_link" target="_blank">Ni WooCommerce Dashboard Sales Report</a>  </li>
    <li><a href="https://wordpress.org/plugins/ni-woocommerce-sales-report-email/"  class="ni_other_plugin_link" target="_blank">Ni WooCommerce Sales Report Email</a>  </li>
    <li><a href="https://wordpress.org/plugins/ni-woocommerce-admin-order-columns/"  class="ni_other_plugin_link" target="_blank">Ni WooCommerce Admin Order Columns</a>  </li>
    <li><a href="https://wordpress.org/plugins/ni-woocommerce-invoice/"  class="ni_other_plugin_link" target="_blank">Ni WooCommerce Invoice</a>  </li>
    <li><a href="https://wordpress.org/plugins/ni-crm-lead/"  class="ni_other_plugin_link" target="_blank">Ni CRM Lead</a>  </li>
    <li><a href="https://wordpress.org/plugins/ni-woocommerce-order-delivery/"  class="ni_other_plugin_link" target="_blank">Ni WooCommerce Order Delivery</a>  </li>
    <li><a href="https://wordpress.org/plugins/ni-woocommerce-stock/"  class="ni_other_plugin_link" target="_blank">Ni WooCommerce Stock</a>  </li>   
    <li><a href="https://wordpress.org/plugins/ni-woocommerce-product-editor/"  class="ni_other_plugin_link" target="_blank">Ni WooCommerce Product Editor</a>  </li>
    
</ul>
                </div>	    	    
        </div>	
        <?php	
		}
	}
}
?>