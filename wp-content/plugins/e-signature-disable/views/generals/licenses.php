<?php 

if ( ! defined( 'ABSPATH' ) ) { 
	exit; // Exit if accessed directly
}

?>

<?php include($this->rootDir . DS . 'partials/_tab-nav.php'); ?>
	
<h3><?php // _e('Licenses', 'esig' );?></h3>

 <?php 
 
 echo $data['message']; 

 echo WP_E_Sig()->notice->esig_print_notice();
	
 ?>
<form name="settings_form" class="settings-form license-form" method="post" action="<?php echo $data['post_action']; ?>">	
<table class="form-table">
	<tbody>
   

       <!-- Display Only When No License is Entered -->
       
       <?php
       
        $result = Esign_licenses::check_license() ; 
        

       if($result->license !='valid') { ?>
       <p><h3 class="activate-license esign-feedback-alert"><span class="icon-esig-alert"></span> <?php
        Esign_licenses::esig_super_admin(true); ?>, <?php _e('you will need to activate your license to get started!','esig');?></h3></p>
        
       	<?php  } // invalid end here 
          
           // display form 
           Esign_licenses::get_license_form($result);
           
           if($result->license = 'valid' &&  $result->license_type != 'business-license')
           {
                ?>
                        <div class="esig-add-on-block esig-pro-pack open">
					     <?php echo sprintf(__('<h3>Get the E-Signature Buisness Pack</h3>
					        <p style="display:block;">The Business Pack gets you access to WP E-Signature add-ons that unlock so much more functionality and features that WP E-Signature can do... like Dropbox Sync, Signing Reminders, Save as PDF, Stand Alone Documents, URL Redirect After Signing, Custom Fields and more. With the Business Pack, you get access to all our ApproveMe built WP E-Signature Add-ons plus any more we build in the next year (which will be a ton).</p>
					        <a class="esig-btn-pro" href="http://www.approveme.me/e-signature-upgrade-license/" target="_blank">Get all our add-ons for $%d</a> ','esig'),$result->license_price[0]->amount); ?>

				        </div>
       <?php  
       
           }
           
           
           // if license invalid then show license content. 
           if($result->license !='valid') {
           
           ?>
           
           
        <tr><td colspan="3"><br><br>
        <div id="esig-settings-col1">
        <span class="license-login"></span><img src="<?php echo $data['ESIGN_ASSETS_DIR_URI']; ?>/images/licensing/login.jpg" class="onboarding-screenshots" />
        <br /><strong><?php _e('1. Log into your account','esig') ; ?> </strong><br />
        <p><?php _e('Login to your account at','esig'); ?> <a href="https://www.approveme.me/profile/" target="_blank"><?php _e('ApproveMe','esig'); ?></a><?php _e('. An email was sent with your username and password at the time of purchase.','esig'); ?></p>
        </div>
   
        <div class="steps-chevron">
        <span class="icon-chevron-right"></span>
        </div>
       
        <div id="esig-settings-col1">
        <img src="<?php echo $data['ESIGN_ASSETS_DIR_URI']; ?>/images/licensing/purchase-history.jpg" class="onboarding-screenshots" />
        <br /><strong><?php _e('2. Click on Purchase History','esig'); ?> </strong><br />
        <p><?php _e('Once you\'ve logged in.  Click on "My Downloads" and then click "Purchase History".','esig') ?></p>
        </div>
       
        <div class="steps-chevron">
        <span class="icon-chevron-right"></span>
        </div>
       
        <div id="esig-settings-col1">
        <img src="<?php echo $data['ESIGN_ASSETS_DIR_URI']; ?>/images/licensing/license-key.jpg" class="onboarding-screenshots" />
        <br /><strong><?php _e('3. Click the Key icon','esig'); ?> </strong><br />
        <p><?php _e('Clicking on the magical "Key Icon" will reveal your top secret license key.  An <em>active</em> license key is required for updates and support.','esig'); ?></p>
        </div>
        <!-- End of Display When No License is Entered -->
        </tr>
        <?php 
          }// license content logic end here 
        ?>
	</tbody>
</table>
		

	
</form>

 <!-- Addons progress bar -->
 <div class="esig-addon-devbox" style="display:none;">
  <div class="esig-addons-wrap">
    <div class="progress-wrap">
      <div class="progress">
        <span class="countup"></span>
      </div>  
    </div>
  </div>
</div>