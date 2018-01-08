<div class="wrap woocommerce">
	<div class="icon32" id="icon-woocommerce-importer"><br></div>
         <h2><b><?php _e('Order/Coupon/Subscription CSV/XML Import Export Settings', 'wf_order_import_export'); ?></b></h2>
    <h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
        <a href="<?php echo admin_url('admin.php?page=wf_woocommerce_order_im_ex') ?>" class="nav-tab <?php echo ($tab == 'import') ? 'nav-tab-active' : ''; ?>"><?php _e('Order', 'wf_order_import_export'); ?></a>
        <a href="<?php echo admin_url('admin.php?page=wf_coupon_csv_im_ex&tab=coupon') ?>" class="nav-tab <?php echo ($tab == 'coupon') ? 'nav-tab-active' : ''; ?>"><?php _e('Coupon', 'wf_order_import_export'); ?></a>
        <a href="<?php echo admin_url('admin.php?page=wf_woocommerce_subscription_order_im_ex&tab=subscription') ?>" class="nav-tab <?php echo ($tab == 'subscription') ? 'nav-tab-active' : ''; ?>"><?php _e('Subscription', 'wf_order_import_export'); ?></a>
         <a href="<?php echo admin_url('admin.php?page=wf_woocommerce_order_im_ex_xml&tab=importxml') ?>" class="nav-tab <?php echo ($tab == 'importxml') ? 'nav-tab-active' : ''; ?>"><?php _e('Order XML', 'wf_order_import_export'); ?></a>
        <a href="<?php echo admin_url('admin.php?page=wf_woocommerce_order_im_ex_xml&tab=help') ?>" class="nav-tab <?php echo ($tab == 'help') ? 'nav-tab-active' : ''; ?>"><?php _e('Order XML Help', 'wf_order_import_export'); ?></a>
	<a href="<?php echo admin_url('admin.php?page=wf_woocommerce_order_im_ex&tab=settings') ?>" class="nav-tab <?php echo ($tab == 'settings') ? 'nav-tab-active' : ''; ?>"><?php _e('Import/Export Settings', 'wf_order_import_export'); ?></a>
		
    </h2>

	<?php
		switch ($tab) {
			case "export" :
				$this->admin_export_page();
			break;
			case "settings" :
				$this->admin_settings_page();
			break;
			case "coupon" :
				$this->admin_coupon_page();
			break;
			case "subscription" :
				$this->admin_subscription_page();
			break;
			case "importxml":
				$this->admin_import_page();
				break;
			case "help";
				$this->admin_help_page();
				break;
			default :
				$this->admin_import_page();
			break;
		}
	?>
</div>