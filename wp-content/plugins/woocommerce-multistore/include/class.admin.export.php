<?php


    class WOO_MSTORE_EXPORT
        {

            var $system_messages    =   array();
                       
            function __construct()
                {
                    
                    add_action( 'network_admin_menu', array($this, 'network_admin_menu') );       
                    add_action('init',                  array($this,    'init'));
                }

                 
            function network_admin_menu()
                {
                    //only if superadmin
                    if(!    current_user_can('manage_sites'))
                        return;
                    
                    
                    $menus_hooks    =   array();

                    $menus_hooks[] =    add_submenu_page( 'woonet-woocommerce', __( 'Orders Export', 'woocommerce' ), __( 'Orders Export', 'woocommerce' ), 'manage_product_terms', 'woonet-woocommerce-orders-export', array($this, 'interface_orders_export_page') ); 
                    
                    
                    foreach($menus_hooks    as  $menus_hook)
                        {
                            add_action('load-' . $menus_hook , array($this, 'load_dependencies'));
                            add_action('load-' . $menus_hook , array($this, 'admin_notices'));
                            
                            add_action('admin_print_styles-' . $menus_hook , array($this, 'admin_print_styles'));
                            add_action('admin_print_scripts-' . $menus_hook , array($this, 'admin_print_scripts'));
                        }
                    
                }
            
            
            function admin_print_styles()
                {
                    
                    wp_register_style('woonet-woocommerce-orders-export', WOO_MSTORE_URL . '/assets/css/woosl-export.css');
                    wp_enqueue_style( 'woonet-woocommerce-orders-export');  
                    
                    wp_register_style('jquery-ui', '//code.jquery.com/ui/1.9.1/themes/eggplant/jquery-ui.css');
                    wp_enqueue_style( 'jquery-ui');    
                    
                }
                
            
            function admin_print_scripts()
                {
                    
                    wp_enqueue_script( 'jquery'); 
                    wp_enqueue_script( 'jquery-ui-datepicker'); 
                    
                    wp_register_script('woonet-woocommerce-orders-export', WOO_MSTORE_URL . '/assets/js/woosl-export.js');
                    wp_enqueue_script( 'woonet-woocommerce-orders-export');   
                    
                }
            
            
            function init()
                {
                    //turn on buffering
                    ob_start();
                     
                    //check for any forms save
                    if(isset($_POST['evcoe_form_submit'])  &&  $_POST['evcoe_form_submit']    ==  'export')
                        $this->form_submit_settings();  
     
                }
            
            
            function load_dependencies()
                {
                    
                    
                }
            
            
            
            function form_submit_settings()
                {
                    $nonce  =   $_POST['woonet-orders-export-interface-nonce'];
                    if ( ! wp_verify_nonce( $nonce, 'woonet-orders-export/interface-export' ) )
                        {
                            $this->system_messages[]    =   array(
                                                                    'type'      =>  'error',
                                                                    'message'   =>  'Invalid nonce'
                                                                    );
                            return;
                        }
                    
                    $settings['export_format']            =   trim(stripslashes($_POST['export_format']));
                    $settings['export_time_after']        =   trim(stripslashes($_POST['export_time_after']));
                    $settings['export_time_before']       =   trim(stripslashes($_POST['export_time_before']));
                    $settings['site_filter']              =   trim(stripslashes($_POST['site_filter']));
                    $settings['order_status']             =   trim(stripslashes($_POST['order_status']));
                                        
                    //include the export class
                    include(WOO_MSTORE_PATH . '/include/class.admin.export.engine.php');
                    
                    $export =   new WOO_MSTORE_EXPORT_ENGINE();
                    
                    $export->process( $settings );
                    if( $export->errors )
                        {
                            foreach($export->errors_log as  $error_log)
                                {
                            
                                    $this->system_messages[]    =   array(
                                                                            'type'      =>  'error',
                                                                            'message'   =>  $error_log
                                                                            );   
                                }
                        }
                    
                }
                
            
            function admin_notices()
                {

                    if(count($this->system_messages)    < 1)
                        return;
                        
                    foreach($this->system_messages  as  $system_message)
                        {
                            echo "<div class='notice " .  $system_message['type'] ."'><p>" .  $system_message['message'] ."</p></div>";
                        }
                    
                }
            
            
                            
            
            
            function interface_orders_export_page()
                {
                   
                    
                    ?>
                        <div id="evcoe" class="wrap"> 
                            <div id="icon-settings" class="icon32"></div>
                            <h2><?php _e( "WooCommerce Orders Export", 'apto' ) ?></h2>
         
                                                       
                            <form id="form_data" name="form" method="post" action="admin.php?page=woonet-woocommerce-orders-export">
                                
                                <?php wp_nonce_field( 'woonet-orders-export/interface-export', 'woonet-orders-export-interface-nonce' ); ?>
                                
                                <p>&nbsp;</p>
                                
                                <table class="form-table">
                                    <tbody>
                            
                                            
                                        <tr valign="top">
                                            <th scope="row" class="label">
                                                <label>Format</label>
                                            </th>
                                            <td>
                                                <label><input type="radio" checked="checked" value="csv" name="export_format"> <span class="date-time-text format-i18n">CSV</label>
                                                <br />
                                                <label><input type="radio" value="xls" name="export_format"> <span class="date-time-text format-i18n">XLS</label>
                                                <p class="description">Export file type format.</p>
                                            </td>
                                        </tr>
                                        
                                        <tr valign="top">
                                            <th scope="row" class="label">
                                                <label>Date Interval</label>
                                            </th>
                                            <td>
                                                <p><label><input type="text" value="" id="export_time_after" name="export_time_after"> <span class="dashicons dashicons-calendar-alt"></span> <span class="date-time-text format-i18n">After</span></label></p>

                                                <p><label><input type="text" value="" id="export_time_before" name="export_time_before"> <span class="dashicons dashicons-calendar-alt"></span> <span class="date-time-text format-i18n">Before</span></label></p>
                                                <p class="description">Timeframe for export. Any option or both can be used</p>
                                            </td>
                                        </tr>
                                        
                                        <tr valign="top">
                                            <th scope="row" class="label">
                                                <label>Order Status</label>
                                            </th>
                                            <td>
                                                <p><select id="order_status" name="order_status">
                                                    <option value="">All</option>
                                                    <?php
                                                    
                                                        $order_statuses =   wc_get_order_statuses();
                                                        foreach($order_statuses as $key =>  $order_status)
                                                            {
                                                                ?><option value="<?php echo $key ?>"><?php echo $order_status ?></option><?php
                                                            }

                                                    ?>
                                                </select></p>
                                                
                                            </td>
                                        </tr>
                                        
                                        <tr valign="top">
                                            <th scope="row" class="label">
                                                <label>Site Filter</label>
                                            </th>
                                            <td>
                                                <p><select id="site_filter" name="site_filter">
                                                    <option value="">All</option>
                                                    <?php
                                                    
                                                    
                                                        $network_sites  =   get_sites(array('limit'  =>  999));
                                                        foreach($network_sites as $network_site)
                                                            {
                                                                $blog_details   =   get_blog_details($network_site->blog_id);
                                                                ?><option value="<?php echo $blog_details->blog_id ?>"><?php echo $blog_details->blogname ?></option><?php
                                                            }

                                                    ?>
                                                </select></p>
                                                
                                            </td>
                                        </tr>
                                                                 
                                    </tbody>
                                </table>
                   
                                <p class="submit">
                                    <input type="submit" name="Submit" class="button-primary" value="Export">
                                </p>
                            
                                <input type="hidden" name="evcoe_form_submit" value="export" />
                                
                            </form>
                        </div>                                  
                        <?php        
                    
                    
                }
                    
            
        }



?>