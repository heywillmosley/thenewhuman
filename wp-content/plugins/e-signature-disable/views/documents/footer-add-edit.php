</form>	
				
    
                            <?php if (array_key_exists('form_tail', $data)) { echo $data['form_tail']; } ?>
				
				
				<div class="af-inner_edit" id="standard_view_popup_edit" style="display:none;">
							 
			<span class="invitations-container_ajax">	
				<div align="center"><img src="<?php if (array_key_exists('ESIGN_ASSETS_DIR_URI', $data)) { echo $data['ESIGN_ASSETS_DIR_URI'];} ?>/images/logo.png" width="200px" height="45px" alt="Sign Documents using WP E-Signature" width="100%" style="text-align:center;"></div>
					<h2 class="esign-form-header"><?php _e('Who needs to sign this document?', 'esig'); ?></h2>
		
					<div class="af-inner">
						<div id="recipient_emails">
							<?php if (array_key_exists('recipient_emails_ajax', $data)) { echo $data['recipient_emails_ajax'];} ?>
						</div><!-- [data-group=recipient-emails] -->
                        
                       <div class="esig-signer-container">
                             <span class="esig-signer-left"> <?php if (array_key_exists('esig-signer-order', $data)) { echo $data['esig-signer-order']; } ?>  &nbsp;</span>
                             <span class="esig-signer-right"><a href="#" id="addRecipient"><?php _e('+ Add Signer', 'esig'); ?></a></span>
                        </div> 
					
					</div>
			</span>
                        <p align="center">
                                    <input type="button" value="Save Changes" class="submit button button-primary button-large" id="submit_signer_save" name="signersave">

                        </p>
			</div>		
	
		
		
		 <!-- Report bug form include here -->
    <?php 
    
    $common =new WP_E_Common();
    
    $common->esig_report_bug_form();
      
    ?>

<!--E-signature dialog content here -->	
	
	<div id="esig-dialog-content" style="display: none;"> </div>
