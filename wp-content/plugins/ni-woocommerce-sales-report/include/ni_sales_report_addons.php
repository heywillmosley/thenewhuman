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
                    <div  style="width:100%;text-align:center; font-size:24px;"><strong>Hire us for plugin Development and Customization</strong></div>
                    <p>Our area of expertise is WordPress and custom plugins development. We specialize in creating custom plugin solutions for your business needs.</p>
                    <p>Email us: <strong><a href="mailto:i.anzar14@gmail.com">i.anzar14@gmail.com</a></strong></p>
                    <p style="font-weight:bold; font-size:24px; margin:0;">Our Other Free Wordpress Plugins</p>
                	</div>
                </div>
                <div style="clear:both"></div>
                 <div class="ni-addons-row">
                 dsadsa
                 </div>
                <div class="ni-addons-row">
                    <div class="ni-addons-column ni-column-height">
                        <div class="ni-addons-lable">Ni WooCommerce Sales Report</div>
                        <div class="ni-addons-value">
                        	<ul>
                      		<li><strong>Display simple sales dashboard </strong></li>
                            <li><strong>Filter sales order product by date range</strong></li>
                      		<li><strong>Print WooCommerce sales order list</strong></li>
                            <li><strong>Display sales order list</strong></li>
                    	</ul>
                        <a href="https://wordpress.org/plugins/ni-woocommerce-sales-report" target="_blank">View</a> 
                        <a href="https://downloads.wordpress.org/plugin/ni-woocommerce-sales-report.zip" target="_blank">Download</a> 
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
        </div>	
        <?php	
		}
	}
}
?>