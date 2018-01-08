<?php
/**
 * Admin View: Notice - Update
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated woosl-message wc-connect">
	<p><?php _e( '<strong>WooCommerce Multistore Data Update Required</strong> &#8211; You&lsquo;re almost ready.', 'woonet' ); ?></p>
	<p class="submit"><a href="<?php echo admin_url( 'admin.php?page=woonet-setup' ) ?>" class="wc-update-now button-primary"><?php _e( 'Run the updater', 'woonet' ); ?></a></p>
</div>
<script type="text/javascript">
	jQuery( '.wc-update-now' ).click( 'click', function() {
		return window.confirm( '<?php echo esc_js( __( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'woocommerce' ) ); ?>' );
	});
</script>
