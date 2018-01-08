<?php
    
    class WOO_MSTORE_admin_products 
        {
            
            var $licence;
            
            var $table_columns  =   array();
            
            var $current_url;
            
            public function __construct() 
                {
                    $this->licence              =   new WOO_MSTORE_licence();
                    
                    if( !   $this->licence->licence_key_verify()    ) 
                        return;
                                            
                    $this->current_url    =   'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                    
                    $this->set_table_columns();
                                  
                    if (isset($_GET['page']) && $_GET['page'] == 'woonet-woocommerce-products')
                        {
                            add_action( 'wp_loaded',                array($this, 'products_interface_form_submit'), 1 );
                        }  
                        
                    add_action( 'network_admin_menu', array($this, 'network_admin_menu') );
                                      
                    add_filter('set-screen-option', array($this, 'set_screen_options'), 10, 3);
                    
                    add_filter('manage_woocommerce_page_woonet-woocommerce-products-network_columns', array($this, 'manage_screen_columns'));
                    
                    
                    global $WOO_MSTORE;
                    
                    
                    add_action( 'manage_product_posts_custom_column', array( $WOO_MSTORE, 'render_product_columns' ), 10 );
                    
                    
                    //allow woocommerce to run on this screen
                    add_filter('woocommerce_screen_ids', array($this, 'woocommerce_screen_ids'));
                    
                    add_action( 'admin_init',                               array(  $this, 'admin_init'), 1);
                }
                
                
            
            function admin_init()
                {
                    global $WOO_MSTORE;
                    
                    
                    add_action( 'bulk_edit_custom_box',     array( $WOO_MSTORE, 'bulk_edit' ), 20, 2 );
                    //add_action( 'quick_edit_custom_box',    array( $WOO_MSTORE, 'quick_edit' ), 20, 2 );
                    add_action( 'save_post',                array( $WOO_MSTORE, 'bulk_and_quick_edit_save_post' ), 999, 2 );
                    
                    //hide certain menus and forms if don't have enough access
                    if( $WOO_MSTORE->functions->publish_capability_user_can() )
                        {

                            
                        
                        }
                        
                }
            
            
            function set_table_columns()
                {
                    
                    $this->table_columns    =   array(
                                                        'cb'                    =>  array(
                                                                                        'title_data'    =>  '<label for="cb-select-all-1" class="screen-reader-text">'. __( "Select All", "woocommerce" ) .'</label><input type="checkbox" id="cb-select-all-1">',
                                                                                        'class'         =>  array('check-column'),
                                                                                        'column_row_tag'    =>  'th'
                                                                                        ),
                                                        'network_sites'         =>  array(
                                                                                        'title_data'    =>  __( 'Network Sites', 'woonet' )
                                                                                        ),
                                                        'thumb'                 =>  array(
                                                                                        'title_data'    =>  '<span class="wc-image tips" data-tip="Image">'. __( 'Image', 'woonet' ) .'</span>'
                                                                                        ),
                                                        'name'                  =>  array(
                                                                                        'title_data'    =>   __( 'Name', 'woonet' ),
                                                                                        'sortable'      =>  TRUE,
                                                                                        'class'         =>  array('column-primary', 'column-name')
                                                                                        
                                                                                        ),
                                                        'in_stock'              =>  array(
                                                                                        'title_data'    =>   __( 'Stock', 'woonet' )
                                                                                        ),
                                                        'price'              =>  array(
                                                                                        'title_data'    =>   __( 'Price', 'woonet' )
                                                                                        ),
                                                        'categories'              =>  array(
                                                                                        'title_data'    =>   __( 'Categories', 'woonet' )
                                                                                        ),
                                                        'product_type'       =>  array(
                                                                                        'title_data'    =>   '<span data-tip="Type" class="wc-type parent-tips">'. __( 'Type', 'woonet' ).'</span>'
                                                                                        ),
                                                        'date'                  =>  array(
                                                                                        'title_data'    =>   __( 'Date', 'woonet'),
                                                                                        'sortable'      =>  TRUE, 
                                                                                        ),
                                                        );   
                    
                    
                }
            
            
            function woocommerce_screen_ids($screen_ids)
                {
                    $screen_ids[]   =   'woocommerce_page_woonet-woocommerce-products-network';
                    $screen_ids[]   =   'edit-woocommerce_page_woonet-woocommerce-products-network';
                     
                    return $screen_ids;    
                }
            
            
            public function manage_screen_columns( $existing_columns ) 
                {
                    if ( empty( $existing_columns ) && ! is_array( $existing_columns ) ) 
                        {
                            $existing_columns = array();
                        }

                    unset( $existing_columns['name'], $existing_columns['comments'], $existing_columns['date'] );

                    $columns                    = array();
                    $columns['cb']              = '<input type="checkbox" />';
                    $columns['network_sites']   = __( 'Network Sites', 'woonet' );
                    $columns['thumb']           = '<span class="wc-image tips" data-tip="' . esc_attr__( 'Image', 'woonet' ) . '">' . __( 'Image', 'woonet' ) . '</span>';
                 
                    $columns['in_stock']        = __( 'Stock', 'woonet' );
                    $columns['price']           = __( 'Price', 'woonet' );
                    $columns['categories']      = __( 'Categories', 'woonet' );
                    $columns['product_type']    = '<span class="wc-type parent-tips" data-tip="' . esc_attr__( 'Type', 'woonet' ) . '">' . __( 'Type', 'woonet' ) . '</span>';
                    $columns['date']            = __( 'Date', 'woonet' );

                    return array_merge( $columns, $existing_columns );

                }
            
                         
            function network_admin_menu()
                {
                    $menus_hooks    =   array();

                    $menus_hooks[] =    add_submenu_page( 'woonet-woocommerce', __( 'Products', 'woonet' ), __( 'Products', 'woonet' ), 'manage_product_terms', 'woonet-woocommerce-products', array($this, 'network_products_interface') ); 
                    
                    
                    foreach($menus_hooks    as  $menus_hook)
                        {
                            add_action('load-' . $menus_hook , array($this, 'load_dependencies'));
                            add_action('load-' . $menus_hook , array($this, 'admin_notices'));
                            add_action('load-' . $menus_hook , array($this, 'screen_options'));
                            
                            add_action('admin_print_styles-' . $menus_hook , array($this, 'admin_print_styles'));
                            add_action('admin_print_scripts-' . $menus_hook , array($this, 'admin_print_scripts'));
                        }
                    
                    
                }
           
            
            function load_dependencies()
                {

                }
                
            function admin_notices()
                {
                    global $WOO_SL_messages;
            
                    if(!is_array($WOO_SL_messages) || count($WOO_SL_messages) < 1)
                        return;
                    
                    foreach($WOO_SL_messages    as $message_data) 
                        {
                            echo "<div id='notice' class='". $message_data['status'] ." fade'><p>". $message_data['message'] ."</p></div>";                            
                        }

                }
                  
            function admin_print_styles()
                {
                    wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
                }
                
            function admin_print_scripts()
                {
                    $screen       = get_current_screen();
                    
                    wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.js', array( 'jquery' ), WC_VERSION, true );
                    wp_enqueue_script('jquery-tiptip');
                    
                    wp_enqueue_script('inline-edit-post');
                               
                    wp_register_script( 'woocommerce_quick-edit', WC()->plugin_url() . '/assets/js/admin/quick-edit.min.js', array( 'jquery' ), WC_VERSION );
                    wp_enqueue_script( 'woocommerce_quick-edit' );
    
                }
              

            function screen_options()
                {
 
                    $screen = get_current_screen();
           
                    if(is_object($screen) && $screen->id == 'woocommerce_page_woonet-woocommerce-products-network')
                        {
                            $args = array(
                                'label'     => __('Products per Page', 'woonet'),
                                'default'   => 10,
                                'option'    => 'products_per_page'
                            );
                            add_screen_option( 'per_page', $args );    
                        }
                    
                }
                
             function set_screen_options($status, $option, $value) 
                {
                    if ( 'products_per_page' == $option ) 
                        return $value;
                }
      
            
            function pagination ($total_items, $per_page, $paged, $which    =   'top')
                {
                    $total_pages    = ceil($total_items / $per_page);
                   
                    $output         = '<span class="displaying-num">' . sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

                    $current        = $paged;

                    $current_url    = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

                    $current_url    = remove_query_arg( array( 'hotkeys_highlight_last', 'hotkeys_highlight_first' ), $current_url );

                    $page_links     = array();

                    $disable_first  = $disable_last = '';
                    if ( $current == 1 ) 
                        {
                            $disable_first = ' disabled';
                        }
                    
                    if ( $current == $total_pages ) 
                        {
                            $disable_last = ' disabled';
                        }
                    
                    $page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
                                                                                            'first-page' . $disable_first,
                                                                                            esc_attr__( 'Go to the first page' ),
                                                                                            esc_url( remove_query_arg( 'paged', $current_url ) ),
                                                                                            '&laquo;'
                                                                                        );

                    $page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
                                                                                            'prev-page' . $disable_first,
                                                                                            esc_attr__( 'Go to the previous page' ),
                                                                                            esc_url( add_query_arg( 'paged', max( 1, $current-1 ), $current_url ) ),
                                                                                            '&lsaquo;'
                                                                                        );

                    if ( 'bottom' == $which ) 
                        {
                            $html_current_page = $current;
                        } 
                    else 
                        {
                            $html_current_page = sprintf( "%s<input class='current-page' id='current-page-selector' title='%s' type='text' name='paged' value='%s' size='%d' />",
                                '<label for="current-page-selector" class="screen-reader-text">' . __( 'Select Page' ) . '</label>',
                                esc_attr__( 'Current page' ),
                                $current,
                                strlen( $total_pages )
                            );
                        }
                        
                    $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
                    $page_links[] = '<span class="paging-input">' . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . '</span>';

                    $page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
                                                                                            'next-page' . $disable_last,
                                                                                            esc_attr__( 'Go to the next page' ),
                                                                                            esc_url( add_query_arg( 'paged', min( $total_pages, $current+1 ), $current_url ) ),
                                                                                            '&rsaquo;'
                                                                                        );

                    $page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
                                                                                            'last-page' . $disable_last,
                                                                                            esc_attr__( 'Go to the last page' ),
                                                                                            esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
                                                                                            '&raquo;'
                                                                                        );

                    $pagination_links_class = 'pagination-links';
                    if ( ! empty( $infinite_scroll ) ) 
                        {
                            $pagination_links_class = ' hide-if-js';
                        }
                    
                    $output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

                    if ( $total_pages ) 
                        {
                            $page_class = $total_pages < 2 ? ' one-page' : '';
                        } 
                    else 
                        {
                            $page_class = ' no-pages';
                        }
                    
                    $_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

                    echo $_pagination;
        
                }
                
    
                
            function get_all_sites_products($per_page = 10, $paged = 1, $post_status  =   '', $other_arguments  =   array())
                {
                    global $wpdb, $WOO_MSTORE;
                    
                    $mysql_query    =   '
                    
                        SELECT SQL_CALC_FOUND_ROWS ID, post_title, post_date, blog_id FROM (
                        ';
                    
                    /*
                    
                    SELECT * FROM (
    
                    (
                    SELECT ID, 1 as site_id  FROM wp_posts as p1
                     JOIN wp_postmeta as pm1 on pm1.post_id = p1.ID
                     WHERE p1.post_type = 'product' AND (pm1.meta_key = '_woonet_network_main_product')
                     )

                    UNION ALL 

                    (
                         SELECT ID, 2 as site_id  FROM wp_4_posts as p1
                     JOIN wp_4_postmeta as pm1 on pm1.post_id = p1.ID
                     WHERE p1.post_type = 'product' AND (pm1.meta_key = '_woonet_network_main_product')
                     )

                    ) results

                    */
                    
                    
                    $network_sites  =   get_sites(array('limit'  =>  999));
                    foreach($network_sites as $network_site)
                        {
                            if(isset($other_arguments['product_from_shops'])    &&  $other_arguments['product_from_shops']  != $network_site->blog_id)
                                continue;
                            
                            $blog_details   =   get_blog_details($network_site->blog_id);
                            
                            $mysql_site_id =  $blog_details->blog_id;
                            if($mysql_site_id < 2)
                                $mysql_site_table  =   '';
                                else
                                $mysql_site_table  =   $blog_details->blog_id . '_';    
                            
                            if($blog_details->blog_id > 1   && !isset($other_arguments['product_from_shops']))
                                $mysql_query    .=   '
                                                        UNION ALL
                                                        ';    
                                                        
                            $mysql_query    .=          "(SELECT ID, ". $WOO_MSTORE->functions->get_field_collation('post_title') .", post_date, '". $blog_details->blog_id ."' as blog_id FROM ". $wpdb->base_prefix . $mysql_site_table . "posts as p
                                                            JOIN ". $wpdb->base_prefix . $mysql_site_table . "postmeta as pm on pm.post_id = p.ID
                                                            WHERE p.post_type = 'product' ";
                            
                            if(!isset($other_arguments['product_from_shops']))
                                {
                                    $mysql_query    .=    " AND (pm.meta_key = '_woonet_network_main_product')";
                                }
                                else
                                {
                                    
                                }
                            
                            if(!empty($post_status))
                                {
                                    $mysql_query    .=  " AND post_status   =   '". $post_status ."'";
                                }
                            
                            if($post_status !=  'trash')    
                                {
                                    $mysql_query    .=  " AND post_status NOT IN('trash')";           
                                }
                                
                            if(isset($other_arguments['search']))
                                {
                                    $mysql_query    .=  " AND (post_title LIKE '%"  .   $other_arguments['search']  .   "%' OR post_content LIKE '"  .   $other_arguments['search']  .   "')";    
                                }
                            
                            if(isset($other_arguments['product_from_shops']))
                                {
                                    $mysql_query    .=     " GROUP BY ID";
                                }                            
                                
                            $mysql_query    .=              ")";
                        }
                    
                    $mysql_query    .=   ') results  ';
                    
                    if(isset($other_arguments['orderby']))
                        {
                            $mysql_query    .=   ' ORDER BY ';
                            switch($other_arguments['orderby'])
                                {
                                
                                    case 'name'    :   
                                                        $mysql_query    .=  ' post_title ';
                                                        break;
                                                        
                                    case 'date'    :   
                                                        $mysql_query    .=  ' post_date ';
                                                        break;
                                }
                                
                            switch($other_arguments['order'])
                                {
                                
                                    case 'asc'    :   
                                                        $mysql_query    .=  ' ASC ';
                                                        break;
                                                        
                                    case 'desc'    :   
                                                        $mysql_query    .=  ' DESC ';
                                                        break;
                                }      
                        }
                       
                    $mysql_query    .=   '    LIMIT ' . ($per_page * ($paged - 1)) . ', '. $per_page ;
                    
                    $results        =   $wpdb->get_results($mysql_query);
                    $total_records  =   $wpdb->get_var("SELECT FOUND_ROWS()");
                    
                    $data = array(  
                                    'results'       =>  $results,
                                    'total_records' =>  $total_records
                                    );
                    
                    return $data;
                }    
                
                
            function get_all_sites_products_statuses($other_arguments  =   array(), $ignore =   array())
                {
                    global $wpdb, $WOO_MSTORE;
                    
                    $mysql_query    =   '
                    
                        SELECT post_status FROM (
                        ';
                    
                    $network_sites  =   get_sites(array('limit'  =>  999));
                    foreach($network_sites as $network_site)
                        {
                            if(isset($other_arguments['product_from_shops'])    &&  $other_arguments['product_from_shops']  != $network_site->blog_id)
                                continue;
                            
                            $blog_details   =   get_blog_details($network_site->blog_id);
                            
                            $mysql_site_id =  $blog_details->blog_id;
                            if($mysql_site_id < 2)
                                $mysql_site_table  =   '';
                                else
                                $mysql_site_table  =   $blog_details->blog_id . '_';    
                            
                            if($blog_details->blog_id > 1   && !isset($other_arguments['product_from_shops']))
                                $mysql_query    .=   '
                                                        UNION ALL
                                                        ';     
                                                        
                            $mysql_query    .=          "(SELECT ". $WOO_MSTORE->functions->get_field_collation('post_status') ." FROM ". $wpdb->base_prefix . $mysql_site_table . "posts AS p ";
                            
                            if(!isset($other_arguments['product_from_shops']))
                                {
                                    $mysql_query    .=          "                          JOIN ". $wpdb->base_prefix . $mysql_site_table . "postmeta as pm on pm.post_id = p.ID ";
                                }
                                
                            $mysql_query    .=          "                          WHERE p.post_type = 'product'";
                                                                
                            if(!isset($other_arguments['product_from_shops']))
                                {
                                    $mysql_query    .=    " AND (pm.meta_key = '_woonet_network_main_product')";
                                }
                                else
                                {
                                    
                                }
                                                 
                            if(isset($other_arguments['search']))
                                {
                                    $mysql_query    .=  " AND (post_title LIKE '%"  .   $other_arguments['search']  .   "%' OR post_content LIKE '"  .   $other_arguments['search']  .   "')";    
                                }
                                               
                            //$mysql_query    .=   " GROUP BY post_status )";
                            $mysql_query    .=   " )";
                            
                        }
                    
                    $mysql_query    .=   ') results 
                                    ' ;
                    
                    $results        =   $wpdb->get_results($mysql_query);
                    
                    $statuses   =   array();
                    foreach ($results   as  $result)
                        {
                            if(count($ignore)   >   0   &&  in_array($result->post_status, $ignore))
                                continue;
                            
                            if(!isset($statuses[$result->post_status]))
                                $statuses[$result->post_status] =   0;
                                
                            $statuses[$result->post_status] =  $statuses[$result->post_status] + 1;
                        }
         
                    return $statuses;
                } 
                
            
            function bulk_action_products($post_status)
                {
                    ?>
                        <label class="screen-reader-text" for="bulk-action-selector-top"><?php _e( 'Select bulk action', 'woonet' );  ?></label>
                        
                        <select id="bulk-action-selector-top" name="action">
                            <option selected="selected" value="-1"><?php _e( 'Bulk Actions', 'woonet' );  ?></option>
                            <option class="hide-if-no-js" value="edit"><?php _e( 'Edit', 'woonet' );  ?></option>
                            <?php
                            
                                if($post_status ==  'trash')
                                    {
                            ?>
                            <option value="untrash"><?php _e( 'Restore', 'woonet' );  ?></option>
                            <option value="delete"><?php _e( 'Delete Permanently', 'woonet' );  ?></option>
                            <?php } else { ?>
                            <option value="trash"><?php _e( 'Move to Trash', 'woonet' );  ?></option>
                            <?php } ?>
          
                        </select>
                        
                        <input type="submit" value="Apply" class="button action" id="ms_doaction" name="">
                                
                    <?php   
                    
                    
                }
                
            function products_interface_form_submit()
                {
                    $action     =   isset($_GET['action']) ?   $_GET['action']    :   '';
                    
                    //bulk actions
                    if(! empty($action))
                        {
                            global $WOO_SL_messages;
                            
                            switch($action)
                                {
                                    case 'trash'    :
                                                        $posts_list =   isset($_GET['post'])    ?   $_GET['post']   :   array();
                                                        
                                                        if(!is_array($posts_list)    || count($posts_list) < 1)
                                                            break; 
                                                            
                                                        foreach($posts_list as  $post_data)
                                                            {
                                                                list($blog_id, $post_id)    =   explode("_", $post_data);
                                                                
                                                                switch_to_blog( $blog_id );
                                                                
                                                                $post_data = get_post($post_id);
                                                                
                                                                //save the current status for later restore
                                                                update_post_meta($post_data->ID, '_wp_trash_meta_status', $post_data->post_status);
                                                                
                                                                $post_data->post_status =   'trash';
                                                                
                                                                //update the modified post
                                                                wp_update_post( $post_data );
                                                            }
                                                            
                                                        //restore original blog
                                                        restore_current_blog();
                                                        
                                                        $WOO_SL_messages[]  =   array(
                                                                                        'status'    =>  'updated',
                                                                                        'message'   =>  sprintf( _n( '1 post', '%s posts', count($posts_list), 'woonet' ), count($posts_list) ) . ' moved to Trash.'
                                                                                        );
                                                        
                                                        break;
                                
                                    case 'untrash'  :
                                                        $posts_list =   isset($_GET['post'])    ?   $_GET['post']   :   array();
                                                        if(!is_array($posts_list)    || count($posts_list) < 1)
                                                            break;
                                                            
                                                        foreach($posts_list as  $post_data)
                                                            {
                                                                list($blog_id, $post_id)    =   explode("_", $post_data);
                                                                
                                                                switch_to_blog( $blog_id );
                                                                
                                                                $post_data = get_post($post_id);
                                                                
                                                                $previous_post_status   =   get_post_meta($post_data->ID, '_wp_trash_meta_status', $post_data->post_status);
                                                                
                                                                $post_data->post_status =   $previous_post_status;
                                                                
                                                                //update the modified post
                                                                wp_update_post( $post_data );
                                                                
                                                                delete_post_meta($post_data->ID, '_wp_trash_meta_status');
                                                            }
                                                            
                                                        //restore original blog
                                                        restore_current_blog();
                                                        
                                                        $WOO_SL_messages[]  =   array(
                                                                                        'status'    =>  'updated',
                                                                                        'message'   =>  sprintf( _n( '1 post', '%s posts', count($posts_list), 'woonet' ), count($posts_list) ) . ' restored from the Trash.'
                                                                                        );
                                                        
                                                        break;
                                                        
                                                        
                                    case 'delete'       :
                                                        
                                                        $posts_list =   $_GET['post'];

                                                        foreach($posts_list as  $post_data)
                                                            {
                                                                list($blog_id, $post_id)    =   explode("_", $post_data);
                                                                
                                                                switch_to_blog( $blog_id );
                                                                
                                                                //WC_API_Orders::delete_order( $post_id, TRUE );
                                                            }
                                                            
                                                        //restore original blog
                                                        restore_current_blog();
                                                        
                                                        $WOO_SL_messages[]  =   array(
                                                                                        'status'    =>  'updated',
                                                                                        'message'   =>  sprintf( _n( '1 post', '%s posts', count($posts_list), 'woonet' ), count($posts_list) ) . ' deleted the Trash.'
                                                                                        );
                                                        
                                                        
                                                        break;
                                                        
                               
                                }   
                            
                        }
                    
                }    
                
            function network_products_interface()
                {
                                        
                    $user           = get_current_user_id();
                    $screen         = get_current_screen();
                    $screen_option  = $screen->get_option('per_page', 'option');
                    $per_page       = get_user_meta($user, $screen_option, true);
                                                                    
                    if ( empty ( $per_page) || $per_page < 1 ) 
                        {
                            $per_page = $screen->get_option( 'per_page', 'default' );
                        }
                    
                    $other_arguments    =   array();
                    
                    $orderby            =   isset($_GET['orderby']) ? trim($_GET['orderby'])    :   '';
                    $order              =   isset($_GET['order']) ? trim($_GET['order'])        :   '';
                    if(!empty($orderby))
                        {
                            $other_arguments['orderby']     =   $orderby;
                            $other_arguments['order']       =   $order;
                        }
                    
                    
                    $search             =   isset($_GET['s']) ? trim($_GET['s'])  :   '';
                    if(!empty($search))
                        {
                            $other_arguments['search']  =   $search;
                        }
                        
                    $product_from_shops =   isset($_GET['product_from_shops']) ? trim($_GET['product_from_shops'])  :   '';
                    if(!empty($product_from_shops))
                        {
                            $other_arguments['product_from_shops']  =   $product_from_shops;
                        }
                        
                    $paged          =   isset($_GET['paged']) ? $_GET['paged']  :   1;
                    $post_status    =   isset($_GET['post_status']) ? $_GET['post_status']  :   '';
                    
                    
                                        
                    $data =   $this->get_all_sites_products($per_page, $paged, '', $other_arguments);
                    
                    $products         =   $data['results'];
                    $total_records  =   $data['total_records'];
                    $current_post_status_records  =   $data['total_records'];
                    
                    if($post_status !=  '')
                        {
                            $data =   $this->get_all_sites_products($per_page, $paged, $post_status, $other_arguments);
                            $products         =   $data['results'];
                            $current_post_status_records  =   $data['total_records'];
                        }
                    
                    
                    $wc_product_statuses  =   get_post_statuses();
                    //add the trash
                    $wc_product_statuses['trash'] =   'Trash';
                    
                    $ignore =   array(
                                    'auto-draft'
                                    );
                    $product_statuses     =   $this->get_all_sites_products_statuses(   $other_arguments, $ignore );
                    $product_statuses       =   $this->sort_statuses($product_statuses);
                    
                                        
                    ?>
                        <div id="woonet" class="wrap">
                            <h2>Products</h2>
                            
                            <ul class="subsubsub">
    <li class="all"><a class="<?php if($post_status ==  '') {echo 'current'; } ?>" href="admin.php?page=woonet-woocommerce-products">All <span class="count">(<?php echo $total_records ?>)</span></a><?php
    
        if (count($product_statuses) > 0)
            {
                ?> | </li><?php
                
                $remaining  =   count($product_statuses);
                foreach($product_statuses as  $product_status   =>  $count)
                    {

                        $remaining--;
                        ?><li class="wc-processing"><a class="<?php if($post_status ==  $product_status) {echo 'current'; } ?>" href="<?php echo add_query_arg( array('post_status' =>  $product_status), $this->current_url ) ?>"><?php echo $wc_product_statuses[$product_status] ?> <span class="count">(<?php echo $count; ?>)</span></a><?php if ($remaining > 0) {echo ' |';} ?></li><?php                       
                    }
            }
            else
            {
                ?> </li><?php
            }
    
    ?>
</ul>
                            
                            <form id="posts-filter" method="get" action="<?php
                            
                                $current_url    = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
                                echo $current_url;
                                 
                            ?>">
                            
                            <input type="hidden" name="page" value="woonet-woocommerce-products" />
                            
                            <p class="search-box">
                                <label for="post-search-input" class="screen-reader-text">Search Products:</label>
                                <input type="search" value="<?php if (isset($_GET['s'])) {echo $_GET['s'];} ?>" name="s" id="post-search-input">
                                <input type="submit" value="Search Products" class="button" id="search-submit">
                            </p>
                            
                            <div class="tablenav top">
                                
                                <div class="alignleft actions bulkactions">
                                    <?php $this->bulk_action_products($post_status) ; ?>
                                </div>
                                
                                <select id="dropdown_product_type" name="product_from_shops">
                                    <option <?php selected("", $product_from_shops);  ?>  value="">Show products from all shops</option>
                                    <?php
                                        
                                        //send the fields to other sites
                                        $network_sites  =   get_sites(array('limit'  =>  999));
                                        foreach($network_sites as $network_site)
                                            {
                                                $blog_details   =   get_blog_details($network_site->blog_id);
                                                
                                                ?><option <?php selected($blog_details->blog_id, $product_from_shops);  ?> value="<?php echo $blog_details->blog_id ?>"><?php echo $blog_details->blogname ?></option><?php
                                                
                                            }     
                                        
                                    ?>
                                    
                                <input type="submit" value="Filter" class="button" id="post-query-submit" name="filter_action">
                            
                                <?php
                                    $this->pagination($current_post_status_records, $per_page, $paged, 'top');
                                ?>  
                            </div>
                            
                            <table class="wp-list-table widefat fixed posts">
                                <thead>
                                    <tr>
                                        <?php   $this->output_table_headers(TRUE);  ?>
                                    </tr>
                                </thead>
                                                            
                                <tbody id="the-list">
        
                                <?php
                                    
                                    $alt    =   '';
                                    
                                    if(count($products) > 0)
                                        {
                                            foreach($products as  $product_data)
                                                {
                                                    $alt    =   ($alt    ==  (string)'alternate') ? ''  :   'alternate';
                                                    
                                                    $this->output_table_row($product_data, $product_data->blog_id, $alt);

                                                }
                                        }
                                        else
                                        {
                                            ?><tr class="no-items"><td colspan="<?php echo count($this->table_columns) ?>" class="colspanchange">No Products found</td></tr><?php   
                                        }

                                    ?>
                                            
               
                                </tbody>
                                
                                <tfoot>
                                    <tr>
                                        <?php   $this->output_table_headers(FALSE);  ?>
                                    </tr>
                                </tfoot>
                                
                            </table>
                            
                            <div class="tablenav bottom">
                                      
                                <?php
                                    $this->pagination($current_post_status_records, $per_page, $paged, 'bottom');
                                ?>
                                      
                            </div>
                            </form>

                            <?php $this->inline_edit(); ?>
                            
                        </div> 
                    <?php  
                  
     
                }
     
     
            function output_table_headers($top  =   TRUE)
                {
                    //get user preferance
                    $columnshidden  =   get_user_option( "managewoocommerce_page_woonet-woocommerce-products-networkcolumnshidden", get_current_user_id() ); 
                    if(!is_array($columnshidden))
                        $columnshidden  =   array();
                    
                    foreach($this->table_columns    as  $column_id  =>  $column_data)
                        {
                            $classes    =   (isset($column_data['class'])   && is_array($column_data['class']) )?   $column_data['class']   :   array();
                            $classes[]  =   'manage-column';
                            $classes[]  =   'column-'   .   $column_id;
                            
                            if(is_array($columnshidden) && in_array($column_id, $columnshidden))
                                $classes[]  =   'hidden';
                            
                            if(isset($column_data['sortable'])  &&  $column_data['sortable']    === TRUE)
                                {
                                    $order = 'desc';
                                    
                                    if(!isset($_GET['orderby']))
                                        $classes[]  =   'sortable ';
                                        else if ($_GET['orderby'] ==  $column_id)
                                            {
                                                $classes[]  = 'sorted ';
                                                $order   =    $_GET['order'];
                                            }
                                    $classes[]  = $order;  
                                }
                            
                            $row_tag    =   isset($column_data['column_row_tag']) ?   $column_data['column_row_tag']  :   'th';
                            
                            ?><<?php echo $row_tag ?> <?php
                            
                            if($top === TRUE)
                                echo 'id="'.   $column_id .'" ';
                            
                            ?>class="<?php echo implode(" " , $classes);   ?>" scope="col"><?php  
                                
                                if(isset($column_data['sortable'])  &&  $column_data['sortable']    === TRUE)
                                    {
                                        ?><a href="<?php 
                    
                                            $new_order  =   ($order ==  'desc')?    'asc'   :   'desc';
                                            echo add_query_arg( array('orderby' =>  $column_id, 'order' => $new_order), $this->current_url );
                                        
                                        ?>"><span><?php   
                                    }
                                
                                echo $column_data['title_data'];
                                
                                if(isset($column_data['sortable'])  &&  $column_data['sortable']    === TRUE)
                                    {
                                        ?></span> <span class="sorting-indicator"></span></a><?php   
                                    } 
                                
                            ?></<?php echo $row_tag ?>><?php
                            
                        }    
                }
     
     
     
            function output_table_row($product_data, $blog_id, $alt = '')
                {
                    $columnshidden  =   get_user_option( "managewoocommerce_page_woonet-woocommerce-products-networkcolumnshidden", get_current_user_id() ); 
                    if(!is_array($columnshidden))
                        $columnshidden  =   array();
                        
                    switch_to_blog( $blog_id );
                    $product =    get_post($product_data->ID);
                    
                    ?>

                        <tr class="post-<?php echo $product->ID ?> type-shop_order status-<?php echo $product->post_status ?> post-password-required hentry <?php echo $alt ?>" data-ms-id="<?php echo $product_data->blog_id ?>_<?php echo $product->ID ?>" id="post-<?php echo $product->ID ?>">
                                <?php 
                                
                                    foreach($this->table_columns    as  $column_id  =>  $column_data)
                                        {
                                            $row_tag    =   isset($column_data['column_row_tag']) ?   $column_data['column_row_tag']  :   'td';
                            
                                            $classes    =   (isset($column_data['class'])   && is_array($column_data['class']) )?   $column_data['class']   :   array();
                                            $classes[]  =   $column_id;
                            
                                            ?><<?php echo $row_tag ?> class="<?php echo implode(" " , $classes) ?> column-<?php echo $column_id ?><?php if(in_array($column_id, $columnshidden)) {echo ' hidden';} ?>"><?php  $this->render_shop_product_columns($column_id, $product_data, $product) ?></<?php echo $row_tag ?>><?php
                                        
                                        }    
                                
                                ?>
                        </tr>
                    <?php
                
                    restore_current_blog();    
                    
                }
                
     
            public function render_shop_product_columns( $column, $product_data, $product ) 
                {
                    global $post, $woocommerce, $blog_id;
             
                    $mode   =   'excerpt';
             
                    $post =  $product;
                    $the_product = wc_get_product( $post );
             
                    switch ( $column ) 
                    {
                        case 'cb' :
                            
                            ?><input type="checkbox" value="<?php echo $product_data->blog_id ?>_<?php echo $product->ID ?>" name="post[]" id="cb-select-<?php echo $product_data->blog_id ?>_<?php echo $product->ID ?>"><div class="locked-indicator"></div><?php    
           
                            break;
                        
                        case 'network_sites' :
                            
                            $blog_details   =   get_blog_details($blog_id);
                            echo '<span class="id">'. $blog_details->blogname .' </span>';
                                                         
                            $network_sites  =   get_sites(array('limit'  =>  999));
                            foreach($network_sites as $network_site)
                                {
                                    $product_is_published_to    =   get_post_meta($product->ID, '_woonet_publish_to_'   .   $network_site->blog_id    , TRUE);
                                    
                                    if($product_is_published_to !=  'yes')
                                        continue;
                                    
                                    $blog_details   =   get_blog_details($network_site->blog_id);
                                    echo '<br /><span class="id">'. $blog_details->blogname .' </span>';
                                }    
           
                            break;
                        case 'thumb' :
                            echo '<a href="' . get_edit_post_link( $post->ID ) . '">' . $the_product->get_image( 'thumbnail' ) . '</a>';
                            break;
                        case 'name' :
                            $edit_link        = get_edit_post_link( $post->ID );
                            $title            = _draft_or_post_title();
                            $post_type_object = get_post_type_object( $post->post_type );
                            $can_edit_post    = current_user_can( $post_type_object->cap->edit_post, $post->ID );

                            /*
                            echo '<strong class="333"><a class="row-title" href="' . esc_url( $edit_link ) .'">' . $title.'</a>';

                            _post_states( $post );

                            echo '</strong>';
                            */

                            if ( $post->post_parent > 0 ) {
                                echo '&nbsp;&nbsp;&larr; <a href="'. get_edit_post_link( $post->post_parent ) .'">'. get_the_title( $post->post_parent ) .'</a>';
                            }
                            
                            
                            //output the inlindeform data
                            $column_name    =   'name';
                            if ( is_post_type_hierarchical( $post->post_type ) ) 
                                {

                                    /**
                                     * Fires in each custom column on the Posts list table.
                                     *
                                     * This hook only fires if the current post type is hierarchical,
                                     * such as pages.
                                     *
                                     * @since 2.5.0
                                     *
                                     * @param string $column_name The name of the column to display.
                                     * @param int    $post_id     The current post ID.
                                     */
                                    do_action( 'manage_pages_custom_column', $column_name, $post->ID );
                                } 
                                else 
                                    {

                                        /**
                                         * Fires in each custom column in the Posts list table.
                                         *
                                         * This hook only fires if the current post type is non-hierarchical,
                                         * such as posts.
                                         *
                                         * @since 1.5.0
                                         *
                                         * @param string $column_name The name of the column to display.
                                         * @param int    $post_id     The current post ID.
                                         */
                                        do_action( 'manage_posts_custom_column', $column_name, $post->ID );
                                    }

                            /**
                             * Fires for each custom column of a specific post type in the Posts list table.
                             *
                             * The dynamic portion of the hook name, `$post->post_type`, refers to the post type.
                             *
                             * @since 3.1.0
                             *
                             * @param string $column_name The name of the column to display.
                             * @param int    $post_id     The current post ID.
                             */
                            do_action( "manage_{$post->post_type}_posts_custom_column", $column_name, $post->ID );

                                
                                
                                
                            // Excerpt view
                            if ( isset( $_GET['mode'] ) && 'excerpt' == $_GET['mode'] ) {
                                echo apply_filters( 'the_excerpt', $post->post_excerpt );
                            }
                            
                            // Get actions
                            $actions = array();

                            $actions['id'] = 'ID: ' . $post->ID;

                            if ( $can_edit_post && 'trash' != $post->post_status ) {
                                $actions['edit'] = '<a href="' . get_edit_post_link( $post->ID, true ) . '" title="' . esc_attr( __( 'Edit this item', 'woonet' ) ) . '">' . __( 'Edit', 'woonet' ) . '</a>';
                                
                                $actions['inline'] = '<a href="#" title="Edit this item inline" class="editinline">' . __( 'Quick Edit', 'woonet' ) . '</a>';

                            }
                 
                            if ( $post_type_object->public ) {
                                if ( in_array( $post->post_status, array( 'pending', 'draft', 'future' ) ) ) {
                                    if ( $can_edit_post )
                                        $actions['view'] = '<a href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) . '" title="' . esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;', 'woonet' ), $title ) ) . '" rel="permalink">' . __( 'Preview', 'woonet' ) . '</a>';
                                } elseif ( 'trash' != $post->post_status ) {
                                    $actions['view'] = '<a href="' . get_permalink( $post->ID ) . '" title="' . esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'woonet' ), $title ) ) . '" rel="permalink">' . __( 'View', 'woonet' ) . '</a>';
                                }
                            }
         
                            echo '<div class="row-actions">';

                            $i = 0;
                            $action_count = sizeof( $actions );

                            foreach ( $actions as $action => $link ) {
                                ++$i;
                                ( $i == $action_count ) ? $sep = '' : $sep = ' | ';
                                echo '<span class="' . $action . '">' . $link . $sep . '</span>';
                            }
                            echo '</div>';
                            
                            $main_blog_id = $blog_id; 
                            $network_sites  =   get_sites(array('limit'  =>  999));
                            foreach($network_sites as $network_site)
                                {
                                    if ($blog_id    == $network_site->blog_id)
                                        continue; 
                                    
                                    $product_is_published_to    =   get_post_meta($product->ID, '_woonet_publish_to_'   .   $network_site->blog_id    , TRUE);
                                    
                                    if($product_is_published_to !=  'yes')
                                        continue;
                                    
                                    switch_to_blog( $network_site->blog_id);
                                    
                                    // Get actions
                                    $actions = array();

                                    $args   =   array(
                                                        'post_type'     =>  'product',
                                                        'post_status'   =>  'any',
                                                        'meta_query'    => array(
                                                                                    'relation' => 'AND',
                                                                                    array(
                                                                                            'key'     => '_woonet_network_is_child_product_id',
                                                                                            'value'   => $post->ID,
                                                                                            'compare' => '=',
                                                                                        ),
                                                                                    array(
                                                                                            'key'     => '_woonet_network_is_child_site_id',
                                                                                            'value'   => $main_blog_id,
                                                                                            'compare' => '=',
                                                                                        )
                                                                                ),
                                                        );
                                    $custom_query       =   new WP_Query($args);
                                    if($custom_query->found_posts   >   0)
                                        {
                                            //product previously created, this is an update
                                            $child_post =   $custom_query->posts[0];
                                
                                        }
                                        else
                                        continue; 
                                    
                                    $blog_details   =   get_blog_details($network_site->blog_id);        
                                    $actions['id'] = 'ID: ' . $child_post->ID . ' ' . $blog_details->blogname;

                                    if ( $can_edit_post && 'trash' != $child_post->post_status ) {
                                        $actions['edit'] = '<a href="' . get_edit_post_link( $child_post->ID, true ) . '" title="' . esc_attr( __( 'Edit this item', 'woonet' ) ) . '">' . __( 'Edit', 'woonet' ) . '</a>';
                                    }
                         
                                    if ( $post_type_object->public ) 
                                        {
                                            if ( in_array( $child_post->post_status, array( 'pending', 'draft', 'future' ) ) ) {
                                                if ( $can_edit_post )
                                                    $actions['view'] = '<a href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $child_post->ID ) ) ) . '" title="' . esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;', 'woonet' ), $title ) ) . '" rel="permalink">' . __( 'Preview', 'woonet' ) . '</a>';
                                            } elseif ( 'trash' != $child_post->post_status ) {
                                                $actions['view'] = '<a href="' . get_permalink( $child_post->ID ) . '" title="' . esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'woonet' ), $title ) ) . '" rel="permalink">' . __( 'View', 'woonet' ) . '</a>';
                                            }
                                        }
                 
                                    echo '<div class="row-actions">';

                                    $i = 0;
                                    $action_count = sizeof( $actions );

                                    foreach ( $actions as $action => $link ) 
                                        {
                                            ++$i;
                                            ( $i == $action_count ) ? $sep = '' : $sep = ' | ';
                                            echo '<span class="' . $action . '">' . $link . $sep . '</span>';
                                        }
                                    echo '</div>';
                                    
                                    restore_current_blog();
                                }
                            

                        break;
               
                        case 'product_type' :
                            if ( 'grouped' == $the_product->get_type() ) {
                                echo '<span class="product-type tips grouped" data-tip="' . __( 'Grouped', 'woonet' ) . '"></span>';
                            } elseif ( 'external' == $the_product->get_type() ) {
                                echo '<span class="product-type tips external" data-tip="' . __( 'External/Affiliate', 'woonet' ) . '"></span>';
                            } elseif ( 'simple' == $the_product->get_type() ) {

                                if ( $the_product->is_virtual() ) {
                                    echo '<span class="product-type tips virtual" data-tip="' . __( 'Virtual', 'woonet' ) . '"></span>';
                                } elseif ( $the_product->is_downloadable() ) {
                                    echo '<span class="product-type tips downloadable" data-tip="' . __( 'Downloadable', 'woonet' ) . '"></span>';
                                } else {
                                    echo '<span class="product-type tips simple" data-tip="' . __( 'Simple', 'woonet' ) . '"></span>';
                                }

                            } elseif ( 'variable' == $the_product->get_type() ) {
                                echo '<span class="product-type tips variable" data-tip="' . __( 'Variable', 'woonet' ) . '"></span>';
                            } else {
                                // Assuming that we have other types in future
                                echo '<span class="product-type tips ' . $the_product->get_type() . '" data-tip="' . ucfirst( $the_product->get_type() ) . '"></span>';
                            }
                            break;
                        case 'price' :
                            echo $the_product->get_price_html() ? $the_product->get_price_html() : '<span class="na">&ndash;</span>';
                            break;
                            
                        case 'categories' :
                            
                            $args   =   array(
                                                'fields'    =>  'names'
                                                );
                            $categories =   wp_get_object_terms(array($the_product->get_id()),'product_cat', $args);
                            if(count($categories)   >   0)
                                echo implode(", ", $categories); 
                            
                            
                            break;
                       
                        case 'in_stock' :

                            if ( $the_product->is_in_stock() ) {
                                echo '<mark class="instock">' . __( 'In stock', 'woonet' ) . '</mark>';
                            } else {
                                echo '<mark class="outofstock">' . __( 'Out of stock', 'woonet' ) . '</mark>';
                            }

                            if ( $the_product->managing_stock() ) {
                                echo ' &times; ' . $the_product->get_stock_quantity();
                            }

                            break;
                            
                        case 'date':
                            if ( '0000-00-00 00:00:00' == $post->post_date ) {
                                $t_time = $h_time = __( 'Unpublished' );
                                $time_diff = 0;
                            } else {
                                $t_time = get_the_time( __( 'Y/m/d g:i:s a' ) );
                                $m_time = $post->post_date;
                                $time = get_post_time( 'G', true, $post );

                                $time_diff = time() - $time;

                                if ( $time_diff > 0 && $time_diff < DAY_IN_SECONDS )
                                    $h_time = sprintf( __( '%s ago' ), human_time_diff( $time ) );
                                else
                                    $h_time = mysql2date( __( 'Y/m/d' ), $m_time );
                            }

                            
                            if ( 'excerpt' == $mode ) {

                                /**
                                 * Filter the published time of the post.
                                 *
                                 * If $mode equals 'excerpt', the published time and date are both displayed.
                                 * If $mode equals 'list' (default), the publish date is displayed, with the
                                 * time and date together available as an abbreviation definition.
                                 *
                                 * @since 2.5.1
                                 *
                                 * @param array   $t_time      The published time.
                                 * @param WP_Post $post        Post object.
                                 * @param string  $column_name The column name.
                                 * @param string  $mode        The list display mode ('excerpt' or 'list').
                                 */
                                echo apply_filters( 'post_date_column_time', $t_time, $post, $column, $mode );
                            } else {

                                /** This filter is documented in wp-admin/includes/class-wp-posts-list-table.php */
                                echo '<abbr title="' . $t_time . '">' . apply_filters( 'post_date_column_time', $h_time, $post, $column, $mode ) . '</abbr>';
                            }
                            echo '<br />';
                            if ( 'publish' == $post->post_status ) {
                                _e( 'Published' );
                            } elseif ( 'future' == $post->post_status ) {
                                if ( $time_diff > 0 )
                                    echo '<strong class="attention">' . __( 'Missed schedule' ) . '</strong>';
                                else
                                    _e( 'Scheduled' );
                            } else {
                                _e( 'Last Modified' );
                            }
                           
                        break;     
                        

                        default :
                            break;
                    }
                        
                    wp_reset_postdata();
            }
            
            
            
            /**
            * Output required html for inline and bulk edit
            * 
            */
            function inline_edit()
                {
                    global $mode;

                    $post_type  =   'product';

                    $post = get_default_post_to_edit( 'product' );
                    $post_type_object = get_post_type_object( 'product' );

                    $taxonomy_names = get_object_taxonomies( 'product' );
                    $hierarchical_taxonomies = array();
                    $flat_taxonomies = array();
                  
                    $m = ( isset( $mode ) && 'excerpt' === $mode ) ? 'excerpt' : 'list';
                    $can_publish = current_user_can( $post_type_object->cap->publish_posts );
                    $core_columns = array( 'cb' => true, 'date' => true, 'name' => true, 'categories' => true, 'tags' => true, 'comments' => true, 'author' => true );

                ?>

                <form action="" method="get"><table style="display: none"><tbody id="inlineedit">
                    <?php
                    $hclass = count( $hierarchical_taxonomies ) ? 'post' : 'page';
                    $bulk = 0;
                    while ( $bulk < 2 ) { ?>

                    <tr id="<?php echo $bulk ? 'bulk-edit' : 'inline-edit'; ?>" class="inline-edit-row inline-edit-row-<?php echo "$hclass inline-edit-product";
                        echo $bulk ? " bulk-edit-row bulk-edit-row-$hclass bulk-edit-product" : " quick-edit-row quick-edit-row-$hclass inline-edit-product";
                    ?>" style="display: none"><td colspan="<?php echo count($this->table_columns) ?>" class="colspanchange">
   
                    
                    
                    
                    <?php if ( $bulk ) {    ?>
                    <fieldset class="inline-edit-col-left">
                        <legend class="inline-edit-legend">Bulk Edit</legend>
                        <div class="inline-edit-col">
                            <div id="bulk-title-div">
                            <div id="bulk-titles"></div>
                        </div>
                    </div></fieldset>
                    <?php } ?>
                        
                        
                        

                <?php

                    foreach ( $this->table_columns as $column_name => $column_data ) 
                        {
        
                            if ( $bulk ) {

                                /**
                                 * Fires once for each column in Bulk Edit mode.
                                 *
                                 * @since 2.7.0
                                 *
                                 * @param string  $column_name Name of the column to edit.
                                 * @param WP_Post $post_type   The post type slug.
                                 */
                                do_action( 'bulk_edit_custom_box', $column_name, 'product' );
                            } else {

                                /**
                                 * Fires once for each column in Quick Edit mode.
                                 *
                                 * @since 2.7.0
                                 *
                                 * @param string $column_name Name of the column to edit.
                                 * @param string $post_type   The post type slug.
                                 */
                                do_action( 'quick_edit_custom_box', $column_name, 'product' );
                            }

                        }
                ?>
                    <p class="submit inline-edit-save">
                        <button type="button" class="button-secondary cancel alignleft"><?php _e( 'Cancel' ); ?></button>
                        <?php if ( ! $bulk ) {
                            wp_nonce_field( 'inlineeditnonce', '_inline_edit', false );
                            ?>
                            <button type="button" class="button-primary ms-save alignright"><?php _e( 'Update' ); ?></button>
                            <span class="spinner"></span>
                        <?php } else {
                            submit_button( __( 'Update' ), 'button-primary alignright', 'bulk_edit', false, array ( 'id'    =>  'ms_bulk_update') );
                        } ?>
                  
                        <span class="error" style="display:none"></span>
                        <br class="clear" />
                    </p>
                    </td></tr>
                <?php
                    $bulk++;
                    }
            ?>
                    </tbody></table></form>
            <?php    
                    
                }
            
            
            function sort_statuses($product_statuses)
                {
                    //always put the publish in front
                    if(isset($product_statuses['publish']))
                        {
                            $publish =  $product_statuses['publish'];
                            
                            unset($product_statuses['publish']);
                            $product_statuses   =   array_merge(array('publish' =>   $publish ), $product_statuses);
                        }    
                    
                    return $product_statuses;   
                }
             
        }
        
?>