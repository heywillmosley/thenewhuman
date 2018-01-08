<?php


	/**
	*  excldue css handler 
	*  plugin compatibility check with others 
	*/
	
   add_action('admin_init','esig_dequeue_other_plugin',20);
   function esig_dequeue_other_plugin()
   {
   	   $page = (isset($_GET['page']))?$_GET['page']:null ;  
   	   if(!empty($page))
   	   {
   	   	  if(preg_match('/^esign/',$page))
   	   	  {
		  	 wp_dequeue_style( 'jquery-ui-lightness');
                         remove_all_actions( "admin_notices" );
		  }
	   	  
	   }
   	  
   }



   function esig_older_version($document_id)
   {
       $document = new WP_E_Document();
       
       $upload_event = $document->get_upload_event($document_id);
       
       if($upload_event)
       {
           return true ;
       }
       else
       {
           return false ; 
       }
       
   }
   
   function esig_remove_template_include_filter()
   {
       
                $setting = new WP_E_Setting();
		$default_display_page= $setting->get_generic('default_display_page');
                
                 $current_page_id = get_queried_object_id();
                 
                 if(class_exists('esig_sad_document'))
                 {
                       $sad = new esig_sad_document();
                     
                       $sad_doc_id = $sad->get_sad_id($current_page_id);
                       if($sad_doc_id)
                       {
                           $default_display_page = $current_page_id ; 
                       }
                 }
                 
                
                if(!is_page($default_display_page))
                            return ; 
	    
                $hook_name = 'template_include';
                global $wp_filter;
                
                if(array_key_exists($hook_name,$wp_filter))
                {
                    foreach ( $wp_filter[$hook_name] as $priority => $filter )
                    {
                        foreach ( $filter as $identifier => $function )
                        {

                            if ( is_array( $function))
                            {
                                remove_filter(
                                    $hook_name,
                                    array ( $function['function'][0], $function['function'][1] ),
                                    $priority
                                );
                            }
                        }
                    }
                }
                // if nexus theme remove nexus template include filter. 
				if (defined('NXS_FRAMEWORKLOADED'))
				{
					remove_filter( 'template_include', 'nxs_template_include', 9999 );
				}
        add_filter('template_include', array('Esign_core_load', 'documentTemplateHook'),-29);
        
   }

  
   add_action("template_redirect","esig_remove_template_include_filter",-100);
   
   function files_to_delete(){
       Esig_Addons::backward_addons_delete();
   }
   // delete backward comppitable files 
   add_action("admin_init","files_to_delete",15) ;
   
   function esig_upgrade_complete($options,$extra_hook){
      
		if (!array_key_exists('plugins', $extra_hook)) {
			return false;
		}
		
       $plugin_file = $extra_hook['plugins'][0] ;
      
	   
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . "/" . $plugin_file) ; 
       
        if($plugin_data['Name'] == "WP E-Signature"){
            
            $subject = " ( IMPORTANT ) ". home_url() ." is at risk and needs to be updated";
             $url = admin_url() . "/admin.php?page=esign-addons";
             
             $userdata = get_userdata(get_current_user_id()) ;
            $body = "Hey {$userdata->user_login},<br>

<p>You are receiving this email because you are the super admin user for the plugin WP eSignature.

We love security and keeping you and your signers as safe as possible.</p>

<p>It seems that while you have updated your core WP eSignature plugin your add-ons are still out of date and require a critical update.<br>

You can (quickly) run this update by logging into the account associated with this email...</p>

<p>Then visiting the following url: <br>

{$url} </p>

<p>P.S. This is an auto-generated email that gets sent if a user does not have the latest critical updates installed.

If you have questions about WP eSignature or this email you can reach out to our support team at: www.approveme.me/support </p>

<p>Thank you for being our customer! </p>

<p>Regards,<br>


The ApproveMe Team</p>"; 
            
            
                    
            WP_E_Sig()->email->esig_mail("ApproveMe Team","supportp@approveme.me",$userdata->user_email,$subject,$body);
            
        }
       
      
   }
   
   add_action("upgrader_process_complete","esig_upgrade_complete",9,2);
   
   
   function esig_update_notice(){
       
        
       if (Esig_Addons::is_updates_available()) {
           
           echo "<link rel='stylesheet' id='open-sans-css'  href='". ESIGN_ASSETS_DIR_URI ."/css/style.css' type='text/css' media='all' />";
            
           echo '<div class="error">
                
        <div style="width:80%;display:inline-block;"><span class="icon-esig-alert"></span> <h4>'. __('UPDATE REQUIRED ASAP: WP E-Signature add-ons require a MAJOR critical updates.  <a href="https://www.approveme.me/wordpress-contract-plugin/wp-online-contract-e-signature/">Read all about here</a>','esig') .'</h4></div> <div style="width:18%;display:inline-block;text-align:right;" ><a href="'. admin_url(). 'admin.php?page=esign-addons' .'"  class="esig-alert-btn"> Update Now </a></div>
    </div>';
        }
   }
   
   add_action( 'admin_notices', 'esig_update_notice' );

?>