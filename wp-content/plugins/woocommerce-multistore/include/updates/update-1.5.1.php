<?php
    
    ?><p><?php _e( 'Applying update 1.5.1.', 'woonet' ); ?></p><?php
    
    global $wpdb;
    
    //this fix all variations on child products which held the wrong parent blog id.
    $network_sites  =   get_sites(array('limit'  =>  999));
    foreach($network_sites as $network_site)
        {
            
            switch_to_blog( $network_site->blog_id);
            
            /**
            *   Check if the plguin in active for this blog
            * 
            * 
            *   We may not want to check if plugin active.. to ensure all sites are actually accurate after the woocommerce is turned back on
            * 
            */
            /*
            if( ! is_plugin_active( 'woocommerce/woocommerce.php' ))
                return;
            */
            
            //retrieve all child products
            
            //use a loop to prevent memory exhaust
            while($variations   =   update_1_5_1_get_objects())
                {
                    foreach($variations as  $variation)
                        {
                            //make the update   
                            $mysql  =   "UPDATE ". $wpdb->postmeta ."
                                             SET meta_value   =   ". $variation->origin_blog_id ."
                                             
                                             WHERE `meta_key`   =   '_woonet_network_is_child_site_id' AND post_id = '". $variation->ID ."'";
                            $results         =  $wpdb->get_results($mysql);
                        }
                    
                    
                }
            
            restore_current_blog();   
            
            
        }
        
        
        
    function update_1_5_1_get_objects()
        {
            global $wpdb;
            
            $mysql_query    =   "SELECT ID, post_parent, (

                                        SELECT meta_value from ". $wpdb->postmeta ."
                                            WHERE meta_key = '_woonet_network_is_child_site_id' and post_id = p.post_parent

                                    ) AS origin_blog_id, pm1.meta_value AS current_blog_id FROM " . $wpdb->posts ." AS p
                                    JOIN ". $wpdb->postmeta ." AS pm ON pm.post_id = p.ID
                                    JOIN ". $wpdb->postmeta ." AS pm1 ON pm1.post_id = p.ID
                                    
                                    WHERE pm.meta_key   =   '_woonet_network_is_child_product_id'
                                                AND p.post_type =   'product_variation'    AND p.post_parent > 0
                                                AND pm1. meta_key   =   '_woonet_network_is_child_site_id'  and pm1.meta_value != (

                                        SELECT meta_value from ". $wpdb->postmeta ."
                                            WHERE meta_key = '_woonet_network_is_child_site_id' and post_id = p.post_parent

                                    )  
                                    LIMIT 10
                                    ";
            $results        =                   $wpdb->get_results($mysql_query); 
            
            return $results;
            
        }
    
?>