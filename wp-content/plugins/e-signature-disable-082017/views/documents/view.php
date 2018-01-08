<?php 

if ( ! defined( 'ABSPATH' ) ) { 
	exit; // Exit if accessed directly
}

?>

<div id="esig-settings-container">
    
	<div id="esig-settings-col_head">
		<img src="<?php echo $data['ESIGN_ASSETS_DIR_URI']; ?>/images/logo.png" width="243px" height="55px" alt="Sign Documents using WP E-Signature" width="84%" style="float:right;">
	</div>
	
    <div id="esig-settings-col_head">
		<img src="<?php echo $data['ESIGN_ASSETS_DIR_URI']; ?>/images/approveme-badge.svg" alt="Powered by Approve Me" width="125px" style="margin-left:90px;">
	</div>
	
	<div id="esig-settings-col4" class="esig-settings-title"><h2><?php _e('What kind of document are you creating?', 'esig'); ?></h2></div>
	
	<div id="esig_view-main" align="center">
	<div id="esig-view-page" align="center">
	
	 <div id="esig-settings-col3">
	 		
		
			<div class="esign-signing-options-col1 esign-signing-options">	
            <a href="#" id="basic_view">
				<div id="esig-add-basic" class="esig-doc-options esig-add-document-hover">
					<div class="icon"></div>
					<div class="text"><?php _e('+ Basic', 'esig'); ?></div>
				</div>
             </a> 
                <!-- basic document benefits start -->
               <div class="benefits">
					<p><?php _e('Basic Benefits','esig'); ?></p>
					<div class="plus-li"><?php _e('1 or more signers','esig'); ?></div>
					<div class="plus-li"><?php _e('Customizable for each recipient','esig'); ?></div>
					<div class="plus-li"><?php _e('Send signer invites email with WordPress','esig'); ?></div>
					<div class="plus-li"><?php _e('Perfect for sales contracts, estimates, etc.','esig'); ?></div>
				</div>  
			</div>
	 	
		</div>
	
		<?php echo $data['more_option_page']; ?>
		
	</div>
	
	</div> <!-- esig page center end here  -->
	
	<div id="esig-settings-col4" style="text-align: center;">
		<p align="center"><img src="<?php echo $data['ESIGN_ASSETS_DIR_URI']; ?>/images/mini-boss.svg" alt="eSign Boss" width="75px"> <span><?php _e('Quit paying monthly fees and start signing with WP E-Signature -', 'esig'); ?> <a href="https://www.approveme.me/wordpress-electronic-digital-signature-add-ons/" target="_blank" class="esig-extension-headlink"><?php _e('Browse add-ons', 'esig'); ?></a></span></p>
	</div>

</div>

<div id="standard_view_popup" class="esign-form-panel" style="display:none">
					<form name="esig-view-document" id="esig-view-form" action="" method="post">
					<input type="hidden" name="document_action" value="save">
					<input type="hidden" name="document_id" value="<?php echo $data['document_id']; ?>">
				<span class="invitations-container">	
				<div align="center"><img src="<?php echo $data['ESIGN_ASSETS_DIR_URI']; ?>/images/logo.png" width="200px" height="45px" alt="Sign Documents using WP E-Signature" width="100%" style="text-align:center;"></div>
					<h2 class="esign-form-header"><?php _e('Who needs to sign this document?', 'esig'); ?></h2>
		
					<div class="af-inner">
						<div id="recipient_emails">
							<div id="signer_main">
							   <input type="text" class="recipient_fnames" name="recipient_fnames[]" placeholder="Signers Name" />
								<input type="text" class="recipient_emails" name="recipient_emails[]" placeholder="email@address.com" />
								<!--<input type="text" name="recipient_lnames[]" placeholder="Signers last name" /> -->
                                                                <?php $esig_second_layer_verification = apply_filters("esig_second_layer_verification", ""); ?> 
                                                                <?php echo $esig_second_layer_verification; ?>
							</div>
						</div><!-- [data-group=recipient-emails] -->
                        
                                                <div class="esig-signer-container">
                                                      <span class="esig-signer-left"> <?php if (array_key_exists('esig-signer-order', $data)) { echo $data['esig-signer-order']; } ?>  &nbsp;</span>
                                                      <span class="esig-signer-right"><a href="#" id="addRecipient_view"><?php _e('+ Add Signer', 'esig'); ?></a></span>
                                                    </div><br> 
					
					</div>
                                        
                                        <?php echo apply_filters("esig_cc_users", ""); ?>
					
				</span>
				<p align="center" class="esig_nextstep">
					<input type="submit" value="Next Step" class="submit button button-primary button-large" id="submit_send"  name="nextstep">
					
				</p>
				
				</form>
  <span class="settings-title"></span>
</div>

<?php $tail= apply_filters('esig-document-footer-content', '', array()); 
      echo $tail ; 
?>