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
        add_filter('template_include', array('WP_E_Digital_Signature', 'documentTemplateHook'),-29);
        
   }

  
   add_action("template_redirect","esig_remove_template_include_filter",-100);
   

?>