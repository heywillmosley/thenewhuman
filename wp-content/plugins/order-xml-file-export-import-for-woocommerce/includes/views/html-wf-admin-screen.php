<?php include('market.php'); ?>
<div class="wrap woocommerce">
	<div class="icon32" id="icon-woocommerce-importer"><br></div>
    <h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
        <a href="<?php echo admin_url('admin.php?page=wf_woocommerce_order_im_ex_xml') ?>" class="nav-tab <?php echo ($tab == 'import') ? 'nav-tab-active' : ''; ?>"><?php _e('Order Import / Export', 'wf_order_import_export_xml'); ?></a>
        <a href="<?php echo admin_url('admin.php?page=wf_woocommerce_order_im_ex_xml&tab=help') ?>" class="nav-tab <?php echo ($tab == 'help') ? 'nav-tab-active' : ''; ?>"><?php _e('Help', 'wf_order_import_export_xml'); ?></a>
		
    </h2>

	<?php
		switch ($tab) {
			case "export" :
				$this->admin_export_page();
			break;
			case "help" :
				$this->admin_help_page();
			break;
			default :
				$this->admin_import_page();
			break;
		}
	?>
</div>