<?php
    
    class WOO_MSTORE_admin_orders 
        {
            
            var $licence;
            
            var $network_dashboard_url;
            
            public function __construct() 
                {
                    
                    $this->licence                  =   new WOO_MSTORE_licence();
                    
                    $this->network_dashboard_url    =   network_admin_url('admin.php?page=woonet-woocommerce');
                    
                    if( !   $this->licence->licence_key_verify()    ) 
                        return;
                    
                    if (isset($_GET['page']) && $_GET['page'] == 'woonet-woocommerce')
                        {
                            add_action( 'wp_loaded', array($this, 'orders_interface_form_submit'), 1 );
                        }
                                           
                    add_action( 'network_admin_menu', array($this, 'network_admin_menu') );
                                      
                    add_filter('set-screen-option', array($this, 'set_screen_options'), 10, 3);
                }
                         
            function network_admin_menu()
                {
                    $menus_hooks    =   array();
                    
                    $menus_hooks[] =    add_menu_page( __( 'WooCommerce', 'woocommerce' ), __( 'WooCommerce', 'woocommerce' ), 'manage_woocommerce', 'woonet-woocommerce', null, null, '55.5' );
                    add_submenu_page( 'woonet-woocommerce', __( 'Orders', 'woocommerce' ), __( 'Orders', 'woocommerce' ), 'manage_product_terms', 'woonet-woocommerce', array($this, 'orders_interface') ); 
                    
                    
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
                    wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.js', array( 'jquery' ), WC_VERSION, true );
                    wp_enqueue_script('jquery-tiptip');
                    
                    wp_register_script( 'woocommerce_admin', WC()->plugin_url() . '/assets/js/admin/woocommerce_admin.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip' ), WC_VERSION );
                    wp_enqueue_script('woocommerce_admin');
                }
              

            function screen_options()
                {
 
                    $screen = get_current_screen();
                 
                    if(is_object($screen) && $screen->id == 'toplevel_page_woonet-woocommerce-network')
                        {
                            $args = array(
                                'label'     => __('Orders per Page', 'woonet'),
                                'default'   => 10,
                                'option'    => 'orders_per_page'
                            );
                            add_screen_option( 'per_page', $args );    
                        }
                 
                }
                
             function set_screen_options($status, $option, $value) 
                {
                    if ( 'orders_per_page' == $option ) 
                        return $value;
                }
            
              
            function get_all_sites_orders($per_page = 10, $paged = 1, $post_status  =   '')
                {
                    global $wpdb;
                    
                    $mysql_query    =   '
                    
                        SELECT SQL_CALC_FOUND_ROWS * FROM (
                        ';
                    
                    $network_sites  =   get_sites(array('limit'  =>  999));
                    foreach($network_sites as $network_site)
                        {
                            $blog_details   =   get_blog_details($network_site->blog_id);
                            
                            $mysql_site_id =  $blog_details->blog_id;
                            if($mysql_site_id < 2)
                                $mysql_site_table  =   '';
                                else
                                $mysql_site_table  =   $blog_details->blog_id . '_';    
                            
                            if($blog_details->blog_id > 1)
                                $mysql_query    .=   '
                                                        UNION ALL
                                                        ';    
                                                        
                            $mysql_query    .=          "(SELECT ID, post_date, '". $blog_details->blog_id ."' as blog_id FROM ". $wpdb->base_prefix . $mysql_site_table . "posts  
                                                            WHERE post_type = 'shop_order'";
                            
                            if(!empty($post_status))
                                {
                                    $mysql_query    .=  " AND post_status   =   '". $post_status ."'";
                                }
                            
                            if($post_status !=  'trash')    
                                {
                                    $mysql_query    .=  " AND post_status NOT IN('trash')";           
                                }
                                                                
                            $mysql_query    .=              ")";
                            
                            
                        }
                    
                    $mysql_query    .=   ') results
                        ORDER BY post_date DESC
                        LIMIT ' . ($per_page * ($paged - 1)) . ', '. $per_page ;
                    
                    $results        =   $wpdb->get_results($mysql_query);
                    $total_records  =   $wpdb->get_var("SELECT FOUND_ROWS()");
                    
                    $data = array(  
                                    'results'       =>  $results,
                                    'total_records' =>  $total_records
                                    );
                    
                    return $data;
                }
                
            function get_all_sites_orders_statuses()
                {
                    global $wpdb;
                    
                    $mysql_query    =   '
                    
                        SELECT post_status, count FROM (
                        ';
                    
                    $network_sites  =   get_sites(array('limit'  =>  999));
                    foreach($network_sites as $network_site)
                        {
                            $blog_details   =   get_blog_details($network_site->blog_id);
                            
                            $mysql_site_id =  $blog_details->blog_id;
                            if($mysql_site_id < 2)
                                $mysql_site_table  =   '';
                                else
                                $mysql_site_table  =   $blog_details->blog_id . '_';    
                            
                            if($blog_details->blog_id > 1)
                                $mysql_query    .=   '
                                                        UNION ALL
                                                        ';    
                                                        
                            $mysql_query    .=          "(SELECT post_status, COUNT(*) as count FROM ". $wpdb->base_prefix . $mysql_site_table . "posts  WHERE post_type = 'shop_order'
                                                                GROUP BY post_status
                                                                )";
                            
                        }
                    
                    $mysql_query    .=   ') results 
                                    ' ;
                    
                    $results        =   $wpdb->get_results($mysql_query);
                    
                    $statuses   =   array();
                    foreach ($results   as  $result)
                        {
                            if(!isset($statuses[$result->post_status]))
                                $statuses[$result->post_status] =   0;
                                
                            $statuses[$result->post_status] +=  $result->count;
                        }
         
                    return $statuses;
                }  
              
            function orders_interface_form_submit()
                {
                    $action     =   isset($_POST['action']) ?   $_POST['action']    :   '';
                    $data_set   =   $_POST;
                    
                    if(empty($action))
                        {
                            $action     =   isset($_GET['action']) ?   $_GET['action']    :   '';
                            $data_set   =   $_GET;
                        }
                    
                    //bulk actions
                    if(! empty($action))
                        {
                            global $WOO_SL_messages;
                            
                            switch($action)
                                {
                                    case 'trash'    :
                                                        $posts_list =   $data_set['post'];
                                                        
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
                                                                
                                                                //restore original blog
                                                                restore_current_blog();
                                                                
                                                            }
                                                        
                                                        $WOO_SL_messages[]  =   array(
                                                                                        'status'    =>  'updated',
                                                                                        'message'   =>  sprintf( _n( '1 post', '%s posts', count($posts_list), 'woonet' ), count($posts_list) ) . ' moved to Trash.'
                                                                                        );
                                                        
                                                        break;
                                
                                    case 'untrash'  :
                                                        $posts_list =   $data_set['post'];

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
                                                                
                                                                //restore original blog
                                                                restore_current_blog();
                                                                
                                                            }
                                                        
                                                        $WOO_SL_messages[]  =   array(
                                                                                        'status'    =>  'updated',
                                                                                        'message'   =>  sprintf( _n( '1 post', '%s posts', count($posts_list), 'woonet' ), count($posts_list) ) . ' restored from the Trash.'
                                                                                        );
                                                        
                                                        break;
                                                        
                                                        
                                    case 'delete'       :
                                                        
                                                        $posts_list =   $data_set['post'];

                                                        foreach($posts_list as  $post_data)
                                                            {
                                                                list($blog_id, $post_id)    =   explode("_", $post_data);
                                                                
                                                                switch_to_blog( $blog_id );
                                                                
                                                                //WC_API_Orders::delete_order( $post_id, TRUE );
                                                                
                                                                //restore original blog
                                                                restore_current_blog();
                                                            }
                                                            
                                                                                                                
                                                        $WOO_SL_messages[]  =   array(
                                                                                        'status'    =>  'updated',
                                                                                        'message'   =>  sprintf( _n( '1 post', '%s posts', count($posts_list), 'woonet' ), count($posts_list) ) . ' deleted the Trash.'
                                                                                        );
                                                        
                                                        
                                                        break;
                                                        
                                    case 'mark_processing':
                                    
                                                        $posts_list =   (array)$data_set['post'];

                                                        foreach($posts_list as  $post_data)
                                                            {
                                                                list($blog_id, $post_id)    =   explode("_", $post_data);
                                                                
                                                                switch_to_blog( $blog_id );
                                                                
                                                                $order = new WC_Order($post_id);
                                                                $order->update_status('processing');
                                                                
                                                                //restore original blog
                                                                restore_current_blog();
                                                            }
                                                        
                                                        $WOO_SL_messages[]  =   array(
                                                                                        'status'    =>  'updated',
                                                                                        'message'   =>  sprintf( _n( '1 post', '%s posts', count($posts_list), 'woonet' ), count($posts_list) ) . ' marked as Processing.'
                                                                                        );
                                    
                                                        break;
                                
                                    case 'mark_on-hold':
                                    
                                                        $posts_list =   $data_set['post'];

                                                        foreach($posts_list as  $post_data)
                                                            {
                                                                list($blog_id, $post_id)    =   explode("_", $post_data);
                                                                
                                                                switch_to_blog( $blog_id );
                                                                
                                                                $order = new WC_Order($post_id);
                                                                $order->update_status('on-hold');
                                                                
                                                                //restore original blog
                                                                restore_current_blog();
                                                                
                                                            }
                                                                                                                
                                                        $WOO_SL_messages[]  =   array(
                                                                                        'status'    =>  'updated',
                                                                                        'message'   =>  sprintf( _n( '1 post', '%s posts', count($posts_list), 'woonet' ), count($posts_list) ) . ' marked as On Hold.'
                                                                                        );
                                    
                                                        break;
                                                        
                                    case 'mark_completed':
                                    
                                                        $posts_list =   (array)$data_set['post'];

                                                        foreach($posts_list as  $post_data)
                                                            {
                                                                list($blog_id, $post_id)    =   explode("_", $post_data);
                                                                
                                                                switch_to_blog( $blog_id );
                                                                
                                                                $order = new WC_Order($post_id);
                                                                $order->update_status('completed');
                                                                
                                                                //restore original blog
                                                                restore_current_blog();
                                                                
                                                            }
                                                            
                                                                                                                
                                                        $WOO_SL_messages[]  =   array(
                                                                                        'status'    =>  'updated',
                                                                                        'message'   =>  sprintf( _n( '1 post', '%s posts', count($posts_list), 'woonet' ), count($posts_list) ) . ' marked as Completed.'
                                                                                        );
                                    
                                                        break;
                                }   
                            
                        }
                    
                }
                
            function orders_interface()
                {
                                        
                    $user           = get_current_user_id();
                    $screen         = get_current_screen();
                    $screen_option  = $screen->get_option('per_page', 'option');
                    $per_page       = get_user_meta($user, $screen_option, true);
                    if ( empty ( $per_page) || $per_page < 1 ) 
                        {
                            $per_page = $screen->get_option( 'per_page', 'default' );
                        }
                        
                    $paged          =   isset($_GET['paged']) ? $_GET['paged']  :   1;
                    $post_status    =   isset($_GET['post_status']) ? $_GET['post_status']  :   '';
                    
                    $data =   $this->get_all_sites_orders($per_page, $paged, '');
                    
                    $orders         =   $data['results'];
                    $total_records  =   $data['total_records'];
                    $current_post_status_records  =   $data['total_records'];
                    
                    if($post_status !=  '')
                        {
                            $data =   $this->get_all_sites_orders($per_page, $paged, $post_status);
                            $orders         =   $data['results'];
                            $current_post_status_records  =   $data['total_records'];
                        }
                    
                    
                    $wc_order_statuses  =   wc_get_order_statuses();
                    $wc_order_statuses  =   array_merge($wc_order_statuses, get_post_statuses());
                    //add the trash
                    $wc_order_statuses['trash'] =   'Trash';
                    
                    $order_statuses     =   $this->get_all_sites_orders_statuses();
                    
                                        
                    ?>
                        <div id="woonet" class="wrap">
                            <h2>Orders </h2>
                            
                            <ul class="subsubsub">
    <li class="all"><a class="<?php if($post_status ==  '') {echo 'current'; } ?>" href="admin.php?page=woonet-woocommerce">All <span class="count">(<?php echo $total_records ?>)</span></a><?php
    
        if (count($order_statuses) > 0)
            {
                ?> | </li><?php
                
                $remaining  =   count($order_statuses);
                foreach($order_statuses as  $order_status   =>  $count)
                    {
                        $remaining--;
                        ?><li class="wc-processing"><a class="<?php if($post_status ==  $order_status) {echo 'current'; } ?>" href="admin.php?page=woonet-woocommerce&post_status=<?php echo $order_status ?>"><?php echo $wc_order_statuses[$order_status] ?> <span class="count">(<?php echo $count; ?>)</span></a><?php if ($remaining > 0) {echo ' |';} ?></li><?php                       
                    }
            }
            else
            {
                ?> </li><?php
            }
    
    ?>
</ul>
                            
                            <form id="posts-filter" method="post" action="<?php
                            
                                $current_url    = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
                                $current_url    = remove_query_arg( array( 'hotkeys_highlight_last', 'hotkeys_highlight_first', 'paged' ), $current_url );
                                echo $current_url;
                                 
                            ?>">
                            <div class="tablenav top">
                                
                                <div class="alignleft actions bulkactions">
                                    <?php $this->bulk_action($post_status) ; ?>
                                </div>
                                
                            
                                <?php
                                    $this->pagination($current_post_status_records, $per_page, $paged, 'top');
                                ?>  
                            </div>
                            <table class="wp-list-table widefat fixed posts">
                                <thead>
                                    <tr>
                                        <th style="" class="manage-column column-cb check-column" id="cb" scope="col"><label for="cb-select-all-1" class="screen-reader-text"><?php _e( 'Select All', 'woocommerce' );  ?></label><input type="checkbox" id="cb-select-all-1"></th>
                                        <th style="" class="manage-column column-order_status" id="order_status" scope="col"><span class="status_head tips" data-tip="Status"><?php _e( 'Status', 'woocommerce' );  ?></span></th>
                                        <th style="" class="manage-column column-order_blog" id="order_blog" scope="col"><?php _e( 'Blog Title', 'woocommerce' );  ?></th>
                                        <th style="" class="manage-column column-order_title" id="order_title" scope="col"><?php _e( 'Order', 'woocommerce' );  ?></th>
                                        <th style="" class="manage-column column-order_items" id="order_items" scope="col"><?php _e( 'Purchased', 'woocommerce' );  ?></th>
                                        <th style="" class="manage-column column-shipping_address" id="shipping_address" scope="col"><?php _e( 'Ship to', 'woocommerce' );  ?></th>
                                        <th style="" class="manage-column column-customer_message" id="customer_message" scope="col"><span class="notes_head tips" data-tip="Customer Message"><?php _e( 'Customer Message', 'woocommerce' );  ?></span></th>
                                        <th style="" class="manage-column column-order_notes" id="order_notes" scope="col"><span class="order-notes_head tips" data-tip="Order Notes"><?php _e( 'Order Notes', 'woocommerce' );  ?></span></th>
                                        <th style="" class="manage-column column-order_date" id="order_date" scope="col"><?php _e( 'Date', 'woocommerce' );  ?></th>
                                        <th style="" class="manage-column column-order_total" id="order_total" scope="col"><?php _e( 'Total', 'woocommerce' );  ?></th>
                                        <th style="" class="manage-column column-order_actions" id="order_actions" scope="col"><?php _e( 'Actions', 'woocommerce' );  ?></th>    
                                    </tr>
                                </thead>

                                <tfoot>
                                    <tr>
                                        <th style="" class="manage-column column-cb check-column" id="cb" scope="col"><label for="cb-select-all-1" class="screen-reader-text"><?php _e( 'Select All', 'woocommerce' );  ?></label><input type="checkbox" id="cb-select-all-1"></th>
                                        <th style="" class="manage-column column-order_status" id="order_status" scope="col"><span class="status_head tips" data-tip="Status"><?php _e( 'Status', 'woocommerce' );  ?></span></th>
                                        <th style="" class="manage-column column-order_blog" id="order_blog" scope="col"><?php _e( 'Blog Title', 'woocommerce' );  ?></th>
                                        <th style="" class="manage-column column-order_title" id="order_title" scope="col"><?php _e( 'Order', 'woocommerce' );  ?></th>
                                        <th style="" class="manage-column column-order_items" id="order_items" scope="col"><?php _e( 'Purchased', 'woocommerce' );  ?></th>
                                        <th style="" class="manage-column column-shipping_address" id="shipping_address" scope="col"><?php _e( 'Ship to', 'woocommerce' );  ?></th>
                                        <th style="" class="manage-column column-customer_message" id="customer_message" scope="col"><span class="notes_head tips" data-tip="Customer Message"><?php _e( 'Customer Message', 'woocommerce' );  ?></span></th>
                                        <th style="" class="manage-column column-order_notes" id="order_notes" scope="col"><span class="order-notes_head tips" data-tip="Order Notes"><?php _e( 'Order Notes', 'woocommerce' );  ?></span></th>
                                        <th style="" class="manage-column column-order_date" id="order_date" scope="col"><?php _e( 'Date', 'woocommerce' );  ?></th>
                                        <th style="" class="manage-column column-order_total" id="order_total" scope="col"><?php _e( 'Total', 'woocommerce' );  ?></th>
                                        <th style="" class="manage-column column-order_actions" id="order_actions" scope="col"><?php _e( 'Actions', 'woocommerce' );  ?></th>    
                                    </tr>
                                </tfoot>

                                <tbody id="the-list">
        
                                <?php
                                    
                                    $alt    =   '';
                                    
                                    foreach($orders as  $order_data)
                                        {
                                            $alt    =   ($alt    ==  (string)'alternate') ? ''  :   'alternate';
                                            
                                            switch_to_blog( $order_data->blog_id );
                                            $order =    new WC_Order($order_data->ID);
                                    ?>

                                        <tr class="post-<?php echo $order_data->blog_id ?>_<?php echo $order_data->ID ?> type-shop_order status-<?php echo $order->get_status() ?> post-password-required hentry <?php echo $alt ?>" id="post-<?php echo $order_data->blog_id ?>_<?php echo $order_data->ID ?>">
                                                <th class="check-column" scope="row"><input type="checkbox" value="<?php echo $order_data->blog_id ?>_<?php echo $order_data->ID ?>" name="post[]" id="cb-select-<?php echo $order_data->blog_id ?>_<?php echo $order_data->ID ?>"><div class="locked-indicator"></div></th>
                                                <td class="order_status column-order_status"><?php  $this->render_shop_order_columns('order_status', $order) ?></td>
                                                <td class="order_title column-order_title"><?php  $this->render_shop_order_columns('order_blog', $order) ?></td>
                                                <td class="order_title column-order_title"><?php  $this->render_shop_order_columns('order_title', $order) ?></td>
                                                <td class="order_items column-order_items"><?php  $this->render_shop_order_columns('order_items', $order) ?></td>
                                                <td class="shipping_address column-shipping_address"><?php  $this->render_shop_order_columns('shipping_address', $order) ?></td>
                                                <td class="customer_message column-customer_message"><?php  $this->render_shop_order_columns('customer_message', $order) ?></td>
                                                <td class="order_notes column-order_notes"><?php  $this->render_shop_order_columns('order_notes', $order) ?></td>
                                                <td class="order_date column-order_date"><?php  $this->render_shop_order_columns('order_date', $order) ?></td>
                                                <td class="order_total column-order_total"><?php  $this->render_shop_order_columns('order_total', $order) ?></td>
                                                <td class="order_actions column-order_actions"><?php  $this->render_shop_order_columns('order_actions', $order) ?></td>
                                        </tr>
                                    <?php
                                            restore_current_blog();
                                        }
                                    
                                    ?>
                                            
               
                                </tbody>
                            </table>
                            
                            <div class="tablenav bottom">
                                      
                                <?php
                                    $this->pagination($current_post_status_records, $per_page, $paged, 'bottom');
                                ?>
                                      
                            </div>
                            </form>

                        </div> 
                    <?php  
                  
     
                }
                
            public function render_shop_order_columns( $column, $the_order ) 
                {
                    global $post, $woocommerce;
             
                    $post =  get_post($the_order->get_id());
             
                    switch ( $column ) 
                        {
                            case 'order_status' :

                                printf( '<mark class="%s tips" data-tip="%s">%s</mark>', sanitize_title( $the_order->get_status() ), wc_get_order_status_name( $the_order->get_status() ), wc_get_order_status_name( $the_order->get_status() ) );

                            break;
                            case 'order_blog' :
                                
                                global $blog_id;
                                
                                $blog_details   =   get_blog_details($blog_id);
                                
                                echo '<span class="na">'. $blog_details->blogname .'</span>';

                            break;
                            case 'order_date' :

                                if ( '0000-00-00 00:00:00' == $post->post_date ) {
                                    $t_time = $h_time = __( 'Unpublished', 'woocommerce' );
                                } else {
                                    $t_time    = get_the_time( __( 'Y/m/d g:i:s A', 'woocommerce' ), $post );
                                    $gmt_time  = strtotime( $post->post_date_gmt . ' UTC' );
                                    $time_diff = current_time( 'timestamp', 1 ) - $gmt_time;
                                    $h_time    = get_the_time( __( 'Y/m/d', 'woocommerce' ), $post );
                                }

                                echo '<abbr title="' . esc_attr( $t_time ) . '">' . esc_html( apply_filters( 'post_date_column_time', $h_time, $post ) ) . '</abbr>';

                            break;
                            case 'customer_message' :

                                if ( $the_order->get_customer_note() )
                                    echo '<span class="note-on tips" data-tip="' . esc_attr( $the_order->get_customer_note() ) . '">' . __( 'Yes', 'woocommerce' ) . '</span>';
                                else
                                    echo '<span class="na">&ndash;</span>';

                            break;
                            case 'order_items' :

                                echo '<a href="#" class="show_order_items">' . apply_filters( 'woocommerce_admin_order_item_count', sprintf( _n( '%d item', '%d items', $the_order->get_item_count(), 'woocommerce' ), $the_order->get_item_count() ), $the_order ) . '</a>';

                                if ( sizeof( $the_order->get_items() ) > 0 ) {

                                    echo '<table class="order_items" cellspacing="0">';

                                    foreach ( $the_order->get_items() as $item ) {
                                        $_product       = apply_filters( 'woocommerce_order_item_product', $the_order->get_product_from_item( $item ), $item );
                                        $item_meta      = new WC_Order_Item_Meta( $item, $_product );
                                        $item_meta_html = $item_meta->display( true, true );
                                        ?>
                                        <tr class="<?php echo apply_filters( 'woocommerce_admin_order_item_class', '', $item ); ?>">
                                            <td class="qty"><?php echo absint( $item['qty'] ); ?></td>
                                            <td class="name">
                                                <?php if ( wc_product_sku_enabled() && $_product && $_product->get_sku() ) echo $_product->get_sku() . ' - '; ?><?php echo apply_filters( 'woocommerce_order_item_name', $item['name'], $item ); ?>
                                                <?php if ( $item_meta_html ) : ?>
                                                    <a class="tips" href="#" data-tip="<?php echo esc_attr( $item_meta_html ); ?>">[?]</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }

                                    echo '</table>';

                                } else echo '&ndash;';
                            break;
                            case 'shipping_address' :
                                if ( $the_order->get_formatted_shipping_address() )
                                    echo '<a target="_blank" href="' . esc_url( 'http://maps.google.com/maps?&q=' . urlencode( $the_order->get_formatted_shipping_address() ) . '&z=16' ) . '">'. esc_html( preg_replace( '#<br\s*/?>#i', ', ', $the_order->get_formatted_shipping_address() ) ) .'</a>';
                                else
                                    echo '&ndash;';

                                if ( $the_order->get_shipping_method() )
                                    echo '<small class="meta">' . __( 'Via', 'woocommerce' ) . ' ' . esc_html( $the_order->get_shipping_method() ) . '</small>';

                            break;
                            case 'order_notes' :

                                if ( $post->comment_count ) {

                                    // check the status of the post
                                    ( $post->post_status !== 'trash' ) ? $status = '' : $status = 'post-trashed';

                                    $latest_notes = get_comments( array(
                                        'post_id'    => $post->ID,
                                        'number'    => 1,
                                        'status'    =>  $status,
                                        'post_type' =>  'any'
                                    ) );

                                    $latest_note = current( $latest_notes );
                                    if($latest_note === FALSE)
                                        {
                                            echo '<span class="na">&ndash;</span>';
                                            return;   
                                        }

                                    if ( $post->comment_count == 1 ) {
                                        echo '<span class="note-on tips" data-tip="' . esc_attr( $latest_note->comment_content ) . '">' . __( 'Yes', 'woocommerce' ) . '</span>';
                                    } else {
                                        $note_tip = isset( $latest_note->comment_content ) ? esc_attr( $latest_note->comment_content . '<small style="display:block">' . sprintf( _n( 'plus %d other note', 'plus %d other notes', ( $post->comment_count - 1 ), 'woocommerce' ), ( $post->comment_count - 1 ) ) . '</small>' ) : sprintf( _n( '%d note', '%d notes', $post->comment_count, 'woocommerce' ), $post->comment_count );

                                        echo '<span class="note-on tips" data-tip="' . $note_tip . '">' . __( 'Yes', 'woocommerce' ) . '</span>';
                                    }

                                } else {
                                    echo '<span class="na">&ndash;</span>';
                                }

                            break;
                            case 'order_total' :
                                echo esc_html( strip_tags( $the_order->get_formatted_order_total() ) );

                                if ( $the_order->get_payment_method_title() ) {
                                    echo '<small class="meta">' . __( 'Via', 'woocommerce' ) . ' ' . esc_html( $the_order->get_payment_method_title() ) . '</small>';
                                }
                            break;
                            case 'order_title' :

                                $customer_tip = '';

                                if ( $address = $the_order->get_formatted_billing_address() ) {
                                    $customer_tip .= __( 'Billing:', 'woocommerce' ) . ' ' . $address . '<br/><br/>';
                                }

                                if ( $the_order->get_billing_phone() ) {
                                    $customer_tip .= __( 'Tel:', 'woocommerce' ) . ' ' . $the_order->get_billing_phone();
                                }

                                echo '<div class="tips" data-tip="' . esc_attr( $customer_tip ) . '">';

                                if ( $the_order->get_user_id() ) {
                                    $user_info = get_userdata( $the_order->get_user_id() );
                                }

                                if ( ! empty( $user_info ) ) {

                                    $username = '<a href="'. get_admin_url() .'user-edit.php?user_id=' . absint( $user_info->ID ) . '">';

                                    if ( $user_info->first_name || $user_info->last_name ) {
                                        $username .= esc_html( ucfirst( $user_info->first_name ) . ' ' . ucfirst( $user_info->last_name ) );
                                    } else {
                                        $username .= esc_html( ucfirst( $user_info->display_name ) );
                                    }

                                    $username .= '</a>';

                                } else {
                                    if ( $the_order->get_billing_first_name() || $the_order->get_billing_last_name() ) {
                                        $username = trim( $the_order->get_billing_first_name() . ' ' . $the_order->get_billing_last_name() );
                                    } else {
                                        $username = __( 'Guest', 'woocommerce' );
                                    }
                                }

                                printf( __( '%s by %s', 'woocommerce' ), '<a href="' . admin_url( 'post.php?post=' . absint( $post->ID ) . '&action=edit' ) . '"><strong>' . esc_attr( $the_order->get_order_number() ) . '</strong></a>', $username );

                                if ( $the_order->get_billing_email() ) {
                                    echo '<small class="meta email"><a href="' . esc_url( 'mailto:' . $the_order->get_billing_email() ) . '">' . esc_html( $the_order->get_billing_email() ) . '</a></small>';
                                }

                                echo '</div>';

                            break;
                            case 'order_actions' :

                                ?><p>
                                    <?php
                                        do_action( 'woocommerce_admin_order_actions_start', $the_order );

                                        global $blog_id;
                                        
                                        $actions = array();
                                                       
                                        if ( $the_order->has_status( array( 'pending', 'on-hold' ) ) ) {
                                            $actions['processing'] = array(
                                                'url'         => add_query_arg( array( 'action' =>  'mark_processing', 'post' => $blog_id . '_' . $post->ID  ), $this->network_dashboard_url ),
                                                'name'         => __( 'Processing', 'woocommerce' ),
                                                'action'     => "processing"
                                            );
                                        }

                                        if ( $the_order->has_status( array( 'pending', 'on-hold', 'processing' ) ) ) {
                                            $actions['complete'] = array(
                                                'url'         => add_query_arg( array( 'action' =>  'mark_completed', 'post' => $blog_id . '_' . $post->ID  ), $this->network_dashboard_url ),
                                                'name'         => __( 'Complete', 'woocommerce' ),
                                                'action'     => "complete"
                                            );
                                        }

                                        $actions['view'] = array(
                                            'url'         => admin_url( 'post.php?post=' . $post->ID . '&action=edit' ),
                                            'name'         => __( 'View', 'woocommerce' ),
                                            'action'     => "view"
                                        );

                                        $actions = apply_filters( 'woocommerce_admin_order_actions', $actions, $the_order );

                                        foreach ( $actions as $action ) {
                                            printf( '<a class="button tips %s" href="%s" data-tip="%s">%s</a>', esc_attr( $action['action'] ), esc_url( $action['url'] ), esc_attr( $action['name'] ), esc_attr( $action['name'] ) );
                                        }

                                        do_action( 'woocommerce_admin_order_actions_end', $the_order );
                                    ?>
                                </p><?php

                            break;
                        }
                        
                    wp_reset_postdata();
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
                
                
            function bulk_action($post_status)
                {
                    ?>
                        <label class="screen-reader-text" for="bulk-action-selector-top"><?php _e( 'Select bulk action', 'woonet' );  ?></label>
                        
                        <select id="bulk-action-selector-top" name="action">
                            <option selected="selected" value="-1"><?php _e( 'Bulk Actions', 'woonet' );  ?></option>
                            <?php
                            
                                if($post_status ==  'trash')
                                    {
                            ?>
                            <option value="untrash"><?php _e( 'Restore', 'woonet' );  ?></option>
                            <option value="delete"><?php _e( 'Delete Permanently', 'woonet' );  ?></option>
                            <?php } else { ?>
                            <option value="trash"><?php _e( 'Move to Trash', 'woonet' );  ?></option>
                            <?php } ?>
                            <option value="mark_processing"><?php _e( 'Mark processing', 'woonet' );  ?></option>
                            <option value="mark_on-hold"><?php _e( 'Mark on-hold', 'woonet' );  ?></option>
                            <option value="mark_completed"><?php _e( 'Mark complete', 'woonet' );  ?></option>
                        </select>
                        
                        <input type="submit" value="Apply" class="button action" id="doaction" name="">
                                
                    <?php   
                    
                    
                }
                
                         
        }
        
?>