<?php
/**
 * Admin View: Notice - Update
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated woocommerce-message wc-connect">
	<p><strong><?php _e( 'WooCommerce Multistore Data Update', 'woonet' ); ?></strong> &#8211; <?php _e( 'We need to update your store\'s to the latest code version.', 'woonet' ); ?></p>
    <?php   
    
    $update_wizard_started = get_site_option('mstore_update_wizard_started');
    if(!empty($update_wizard_started))
        {
    ?>
    <p><?php _e( 'The update process didn\'t completed last time, this require more time. Just click again the update button and the process will resume.', 'woonet' ); ?></p>
    <?php } ?>
	<p class="submit"><a href="<?php echo esc_url( add_query_arg( 'do_update_woocommerce', 'true', network_admin_url( 'admin.php?page=woonet-upgrade' ) ) ); ?>" class="wc-update-now button-primary"><?php _e( 'Run the updater', 'woonet' ); ?></a></p>
</div>
<script type="text/javascript">
	jQuery( '.wc-update-now' ).click( 'click', function() {
		return window.confirm( '<?php echo esc_js( __( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'woocommerce' ) ); ?>' ); // jshint ignore:line
	});
</script>
