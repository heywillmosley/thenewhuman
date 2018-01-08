<?php
/**
 * Admin View: Quick Edit Product
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>

    <fieldset id="woocommerce-multistore-fields" class="inline-edit-col">


        <h4><?php _e( 'Multisite - Publish to', 'woocommerce' ); ?></h4>
         
        <div class="inline-edit-col">
     
            <?php
            
                global $post, $blog_id;
                
                global $WOO_MSTORE;
                $options    =   $WOO_MSTORE->functions->get_options();
                     
                $main_blog_id =   $blog_id;
            
                $network_sites  =   get_sites(array('limit'  =>  999));
                foreach($network_sites as $network_site)
                    {
                        $blog_details   =   get_blog_details($network_site->blog_id);
                        
                        $value  =   get_post_meta( $post->ID, '_woonet_publish_to_' . $network_site->blog_id, true );
                        
                        switch_to_blog( $blog_details->blog_id );
                        
                        //check if plugin active
                        if( !   $this->functions->is_plugin_active('woocommerce/woocommerce.php') || ! $this->functions->is_plugin_active('woocommerce-multistore/woocommerce-multistore.php'))
                            {
                                restore_current_blog();
                                continue;
                            }
                        
                        if($blog_details->blog_id   ==  $main_blog_id)
                            {
                                restore_current_blog();
                                continue;   
                            }
                        
                        ?>
                            <label class="alignleft">
                            
                                    <input type="checkbox" value="yes" data-blog-id="<?php echo $network_site->blog_id ?>" name="_woonet_publish_to_<?php echo $network_site->blog_id ?>">
                                    <span class="checkbox-title"><?php echo $blog_details->blogname ?></span>
                            
                            
                            </label>
                            <br class="clear">
                            <label class="alignleft pl">
                  
                                    <input type="checkbox" value="yes" data-blog-id="<?php echo $network_site->blog_id ?>" name="_woonet_publish_to_<?php echo $network_site->blog_id ?>_child_inheir">
                                    <span class="checkbox-title"><?php _e( 'Child product inherit Parent changes', 'woonet' ) ?></span>
                  
                            </label>
                            <br class="clear">
                            <label class="alignleft pl">
                  
                                    <input type="checkbox" <?php  if ($options['synchronize-stock']    ==  'yes')  { echo 'disabled="disabled"';}  ?> value="yes" data-blog-id="<?php echo $network_site->blog_id ?>" name="_woonet_<?php echo $network_site->blog_id ?>_child_stock_synchronize">
                                    <span class="checkbox-title"><?php _e( 'If checked, any stock change will syncronize across product tree.', 'woonet') ?></span>
                  
                            </label>
                            <br class="clear">
                        <?php
               
                                                 
                        restore_current_blog();
                    }
            
            
            ?>

    
        </div>

        <input type="hidden" name="woocommerce_multisite_bulk_edit" value="1" />
        <input type="hidden" name="woocommerce_multisite_bulk_edit_nonce" value="<?php echo wp_create_nonce( 'woocommerce_multisite_bulk_edit_nonce' ); ?>" />
        
    </fieldset>
