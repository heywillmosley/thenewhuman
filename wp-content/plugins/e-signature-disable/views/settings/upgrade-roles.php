<?php 

if ( ! defined( 'ABSPATH' ) ) { 
	exit; // Exit if accessed directly
}

?>
<div class="esig-error-message-wrap">
<a href='http://www.approveme.me/wp-digital-e-signature' target='_blank' style='text-decoration:none;'>
				<img src='<?php echo $data['assets_dir']; ?>/images/logo.png' alt='WP E-Signature'>
</a>
<h1><?php _e('Access Denied', 'esig' );?></h1>
<p><?php echo sprintf( __( 'Woah tiger! %s for WP E-Signature is a pro feature. Because security is a big deal, only the WordPress admin user who first saves the settings for WP E-Signature can have access to the E-Signature documents & settings page.', 'esig'), $data['feature'])?></p> 

<p><?php _e('If additional users need to upload, send, and manage documents we recommend you install our <a href="https://www.approveme.me/downloads/unlimited-sender-roles/">E-Signature Unlimited Sender Roles</a> Premium Add-On.', 'esig' );?></p>

<p><?php _e('Please checkout our awesome list of extensions <a href="https://www.approveme.me/wordpress-electronic-digital-signature-add-ons/">here</a>', 'esig' ); ?></p>
</div>

<?php echo $data['esig_user_role']; ?>