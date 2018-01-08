<?php

    class WOO_MSTORE_ajax
        {
                  
            function __construct()
                {
                    add_action( 'wp_ajax_woosl_setup_get_process_list', array($this, 'woosl_setup_get_process_list' ));
                    add_action( 'wp_ajax_woosl_setup_process_batch', array($this, 'woosl_setup_process_batch' ));
                    
                    add_action( 'wp_ajax_woosl-inline-save', array($this, 'inline_save' ));
                    add_action( 'wp_ajax_woosl-bulk-edit-save', array($this, 'bulk_edit_save' ));
                    
                    //when a variations is saved individually
                    add_action( 'woocommerce_ajax_save_product_variations', array($this, 'woocommerce_ajax_save_product_variations' ));
                    
                }
                
            function __destruct()
                {
                
                }
                
            function woosl_setup_get_process_list()
                {
                    $site_id    =   intval($_POST['site_id']);
                    
                    switch_to_blog( $site_id );
                    
                    //get all products
                    $args   =   array(
                                        'post_type'         =>  'product',
                                        'posts_per_page'    =>  '-1',
                                        'fields'            =>  'ids'
                                        );
                    
                    $custom_query   =   new WP_Query($args);
                    
                    $post_list  =   $custom_query->get_posts();
                    
                    restore_current_blog();
                    
                    $response   =   array();
                    $response['status'] =   'completed';
                    $response['data']   =   $post_list;
                    
                    echo json_encode($response);
                    die();
                }
                
            function woosl_setup_process_batch()
                {
                    $site_id    =   intval($_POST['site_id']);
                    $batch      =   (array)$_POST['batch'];
                    
                    switch_to_blog( $site_id );
                    
                    foreach($batch  as  $post_id)
                        {
                            //check if the product include the required meta fields
                            $is_main_product       =   get_post_meta($post_id, '_woonet_network_main_product', TRUE);
                            $is_child_product      =   get_post_meta($post_id, '_woonet_network_is_child_product_id', TRUE);
                            
                            if(!empty($is_child_product)    ||  !empty($is_main_product))
                                continue;
                                
                            //add as main product
                            update_post_meta($post_id, '_woonet_network_main_product', 'true');
                        }
                    
                    restore_current_blog();
                    
                    $response   =   array();
                    $response['status'] =   'completed';
                    
                    echo json_encode($response);
                    die();   
                    
                }
  
  
            /**
             * Ajax handler for Quick Edit saving a post from a list table.
             *
             * @since 3.1.0
             *
             * @global WP_List_Table $wp_list_table
             */
            function inline_save() 
                {
                    global $wp_list_table, $mode;

                    check_ajax_referer( 'inlineeditnonce', '_inline_edit' );

                    if ( ! isset($_POST['post_ID']) || ! ( $post_ID = (int) $_POST['post_ID'] ) )
                        wp_die();
   
                    $blog_id    =   (int) $_POST['blog_id'];
                    
                    switch_to_blog( $blog_id);
                    
                    $post = get_post( $post_ID );

                    do_action( 'save_post', $post_ID, $post, TRUE );
                    
                    restore_current_blog();

                    $product_data               =   new stdClass();
                    $product_data->ID           =   $post_ID;
                    $product_data->post_title   =   $post->post_title;
                    $product_data->blog_id      =   $blog_id;
                    
                    global $WOO_MSTORE;
                    
                    $WOO_MSTORE->network_products_interface->output_table_row($product_data, $blog_id);

                    wp_die();
                }
                
                
            /**
             * Ajax handler for Quick Edit saving a post from a list table.
             *
             * @since 3.1.0
             *
             * @global WP_List_Table $wp_list_table
             */
            function bulk_edit_save() 
                {
                    $post_data  =   $_POST;
                               
                    $post_IDs = array_map( 'intval', (array) $post_data['ids'] );
                    $blog_IDs = array_map( 'intval', (array) $post_data['blog_ids'] );
                    
                    unset($post_data['ids']);
                    unset($post_data['blog_ids']);
                    
                    
                    //check for  any multistore change oterwise set the ignore
                    $found_ms_update    =   FALSE;
                    $network_sites  =   get_sites(array('limit'  =>  999));
                    foreach($network_sites as $network_site)
                        {
                            if (isset($_REQUEST['_woonet_publish_to_' . $network_site->blog_id]))
                                {
                                    $found_ms_update    =   TRUE;
                                    break;   
                                }
                        }
                    
                    if($found_ms_update === FALSE)
                        $_POST['WOO_MSTORE_ignore_quick_edit_save'] =   true;
                    
                    $shared_post_data = $post_data;
                    
                    foreach ( $post_IDs as $key =>  $post_ID ) 
                        {
                            $blog_id    =   $blog_IDs[$key];
                            
                            switch_to_blog( $blog_id);

                            // Start with fresh post data with each iteration.
                            $post_data = $shared_post_data;

                            $post_type_object = get_post_type_object( get_post_type( $post_ID ) );

                            if ( !isset( $post_type_object ) || ( isset($children) && in_array($post_ID, $children) ) || !current_user_can( 'edit_post', $post_ID ) ) 
                                {
                                    $skipped[] = $post_ID;
                                    continue;
                                }

                            if ( wp_check_post_lock( $post_ID ) ) 
                                {
                                    $locked[] = $post_ID;
                                    continue;
                                }

                            $post = get_post( $post_ID );
                            $tax_names = get_object_taxonomies( $post );
                            foreach ( $tax_names as $tax_name ) {
                                $taxonomy_obj = get_taxonomy($tax_name);
                                if ( isset( $tax_input[$tax_name]) && current_user_can( $taxonomy_obj->cap->assign_terms ) )
                                    $new_terms = $tax_input[$tax_name];
                                else
                                    $new_terms = array();

                                if ( $taxonomy_obj->hierarchical )
                                    $current_terms = (array) wp_get_object_terms( $post_ID, $tax_name, array('fields' => 'ids') );
                                else
                                    $current_terms = (array) wp_get_object_terms( $post_ID, $tax_name, array('fields' => 'names') );

                                $post_data['tax_input'][$tax_name] = array_merge( $current_terms, $new_terms );
                            }

                            if ( isset($new_cats) && in_array( 'category', $tax_names ) ) {
                                $cats = (array) wp_get_post_categories($post_ID);
                                $post_data['post_category'] = array_unique( array_merge($cats, $new_cats) );
                                unset( $post_data['tax_input']['category'] );
                            }

                            $post_data['post_type'] = $post->post_type;
                            $post_data['post_mime_type'] = $post->post_mime_type;
                            $post_data['guid'] = $post->guid;

                            foreach ( array( 'comment_status', 'ping_status', 'post_author' ) as $field ) {
                                if ( ! isset( $post_data[ $field ] ) ) {
                                    $post_data[ $field ] = $post->$field;
                                }
                            }

                            $post_data['ID'] = $post_ID;
                            $post_data['post_ID'] = $post_ID;

                            $post_data = _wp_translate_postdata( true, $post_data );
                            if ( is_wp_error( $post_data ) ) {
                                $skipped[] = $post_ID;
                                continue;
                            }

                            $updated[] = wp_update_post( $post_data );
              
                            restore_current_blog();
                        }
                    
                    
                    
                    die();
                }
                
            
            /**
            * A vairation has been changed, trigger network update for this product
            *     
            * @param mixed $product_id
            */
            function woocommerce_ajax_save_product_variations( $product_id )
                {
                    global $WOO_MSTORE, $blog_id;
                    
                    //run stock syncronysation
                    $this->save_product_variations_stock_syncronysation($product_id);
                    
                    //if main product, then replicate the variation changes to other child variations
                    $_woonet_network_main_product   =    get_post_meta($product_id, '_woonet_network_main_product', 'true');
                    if(!empty($_woonet_network_main_product)    &&  $_woonet_network_main_product   ==  'true')
                        {
                            
                            $variable_blog_id   =   $blog_id;
                            
                            $variable_post_ids  =   $_POST['variable_post_id'];
                            if(count($variable_post_ids)    >   0)
                                {
                                    foreach($variable_post_ids  as  $parent_variable_id)
                                        {
                                            if(!in_array($parent_variable_id, $variable_post_ids))
                                                continue;
                                            
                                            //retrieve the meta
                                            $variation_product_meta   =   get_post_meta($parent_variable_id);
                                            $variation_product_meta   =   $WOO_MSTORE->functions->filter_product_meta($variation_product_meta);
                                    
                                            //relocate the _Stock_status to end to allow WooCommerce to syncronyze on actual stock value
                                            $data =     isset($variation_product_meta['_stock_status']) ?   $variation_product_meta['_stock_status']    :   '';
                                            if(!empty($data))
                                                {
                                                    unset($variation_product_meta['_stock_status']);
                                                    $variation_product_meta['_stock_status'] = $data;
                                                }
                                                
                                            //loop the sites to see any childs variables to update
                                            $network_sites  =   get_sites(array('limit'  =>  999));
                                            foreach($network_sites as $network_site)
                                                {
                                                    
                                                    $publish_to  =   get_post_meta( $product_id, '_woonet_publish_to_'. $network_site->blog_id, true );
                                                    if(empty($publish_to))
                                                        continue;
                                                            
                                                    switch_to_blog( $network_site->blog_id );
                                                    
                                                    //identify the variation
                                                    $args   =   array(
                                                                        'post_type'     =>  'product_variation',
                                                                        'post_status'   =>  'any',
                                                                        'meta_query'    => array(
                                                                                                    'relation' => 'AND',
                                                                                                    array(
                                                                                                            'key'     => '_woonet_network_is_child_site_id',
                                                                                                            'value'   => $variable_blog_id,
                                                                                                            'compare' => '=',
                                                                                                        ),
                                                                                                    array(
                                                                                                            'key'     => '_woonet_network_is_child_product_id',
                                                                                                            'value'   => $parent_variable_id,
                                                                                                            'compare' => '=',
                                                                                                        ),
                                                                                                ),
                                                                        );
                                                    $custom_query       =   new WP_Query($args);
                                                    
                                                    if($custom_query->found_posts   >   0)
                                                        {
                                                            //product previously created, this is an update
                                                            $child_variation =   $custom_query->posts[0];
                                                            
                                                            
                                                            //chekc if _woonet_child_inherit_updates
                                                            $child_product_id   =   $child_variation->post_parent;
                                                            $_woonet_child_inherit_updates  =   get_post_meta($child_product_id, '_woonet_child_inherit_updates', TRUE);
                                                            if($_woonet_child_inherit_updates != 'yes')
                                                                {
                                                                    restore_current_blog();
                                                                    continue;
                                                                }
                                                                                                                    
                                                            //replicate the meta
                                                           
                                                            $WOO_MSTORE->functions->save_meta_to_post($variation_product_meta, array(), $child_variation->ID , $network_site->blog_id);

                                                            
                                                            
                                                        }
                                                    
                                                    restore_current_blog();
                                                    
                                                }
                                            
                                            
                                        }
                                }

                        }
                    
                }
                
                
            function save_product_variations_stock_syncronysation( $product_id )
                {
                    
                    //check if is networg parent product
                    $_woonet_network_main_product   =    get_post_meta($product_id, '_woonet_network_main_product', 'true');
                    
                    if(!empty($_woonet_network_main_product)    &&  $_woonet_network_main_product   ==  'true')
                        {
                            global $blog_id;
                            
                            WOO_MSTORE_functions::update_stock_across_network( $product_id, $blog_id, array( $blog_id ) );
                            
                            return;
                        }
                        
                    global $WOO_MSTORE;
                        
                    //check if is network child product
                    $network_parent_product_id    =   get_post_meta($product_id, '_woonet_network_is_child_product_id', TRUE);
                    if($network_parent_product_id   >   0)
                        {
                            
                            $options    =   $WOO_MSTORE->functions->get_options();
                            
                            $_woonet_child_inherit_updates          =   get_post_meta($product_id , '_woonet_child_inherit_updates', TRUE);
                            $_woonet_child_stock_synchronize        =   get_post_meta($product_id , '_woonet_child_stock_synchronize', TRUE);
                            
                            //chck for Always maintain stock synchronization;  If set, it also modify any stock change within child product to parent
                            if($options['synchronize-stock']    !=  'yes'   &&  $_woonet_child_inherit_updates  !=  'yes'  &&  $_woonet_child_stock_synchronize    !=  'yes')
                                return;
                            
                            list($_woonet_network_is_child_product_id, $_woonet_network_is_child_site_id, $ignore_blogs) = $WOO_MSTORE->functions->on_child_product_change__update_parent( $product_id );
                            
                            //syncronize all network
                            WOO_MSTORE_functions::update_stock_across_network($_woonet_network_is_child_product_id, $_woonet_network_is_child_site_id, array($blog_id));
                            
                            return;
                        }   
                    
                }
                
        }

?>