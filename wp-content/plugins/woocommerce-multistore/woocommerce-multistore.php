<?php
/*
Plugin Name: WooCommerce Multistore
Description: WooCommerce Multistore
Author: Lykke Media AS
Author URI: http://www.lykkemedia.no
Version: 1.7
*/
    
    define('WOO_MSTORE_PATH',   plugin_dir_path(__FILE__));
    define('WOO_MSTORE_URL',    plugins_url('', __FILE__));
    define('WOO_MSTORE_APP_API_URL',      'http://woomultistore.com/index.php');
    
    define('WOO_MSTORE_VERSION', '1.7');
    define('WOO_MSTORE_DB_VERSION', '1.0'); 
    
    define('WOO_MSTORE_PRODUCT_ID',           'WCMSTORE');
    define('WOO_MSTORE_INSTANCE',             str_replace(array ("https://" , "http://"), "", network_site_url()));
    
    include(WOO_MSTORE_PATH . '/include/class.functions.php');
    
    if ( ! function_exists( 'woothemes_queue_update' ) || ! function_exists( 'is_woocommerce_active' ) ) 
        {
            require_once( 'woo-includes/woo-functions.php' );
        }
       
    register_deactivation_hook(__FILE__, 'WOO_MSTORE_deactivated');
    register_activation_hook(__FILE__, 'WOO_MSTORE_activated');
    
    include_once(WOO_MSTORE_PATH . '/include/licence.php');
    include_once(WOO_MSTORE_PATH . '/include/class.updater.php');
    
    function WOO_MSTORE_activated() 
        {
        
            $WOO_SL = new WOO_MSTORE_functions(); 
            $WOO_SL->create_tables();

        }

    function WOO_MSTORE_deactivated() 
        {

        }
    
    //load language files
    add_action( 'plugins_loaded', 'WOO_MSTORE_load_textdomain'); 
    function WOO_MSTORE_load_textdomain() 
        {
            load_plugin_textdomain('woonet', FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang');
        }
    
    
    add_action('network_admin_notices', 'WOO_MSTORE_network_admin_notices');
    function WOO_MSTORE_network_admin_notices()
        {
            if ( current_user_can( 'manage_woocommerce' ) )
                WOO_MSTORE_setup_wizard_nottice();
                
            if ( current_user_can( 'manage_woocommerce' ) )
                WOO_MSTORE_update_wizard_nottice();
            
        }
    
    add_action( 'admin_notices', 'WOO_MSTORE_admin_notices' );
    function WOO_MSTORE_admin_notices()
        {
            if ( !is_multisite() ) 
                { 
                    echo '<div class="updated"><p>' . __('WooCommerce Multistore requires WordPress MultiSite environment', 'woonet') . '</p></div>';
                }
                
            if ( ! is_woocommerce_active() || version_compare( get_option( 'woocommerce_db_version' ), '2.1', '<' ) ) 
                {
                    $wc_url = 'http://www.woothemes.com/woocommerce/';
                    printf('<div class="updated"><p>' . __('WooCommerce Multistore requires', 'woonet') . ' <a href="%s">WooCommerce</a> '. __('to be installed', 'woonet') .'</p></div>', $wc_url);
                }
            
            if ( current_user_can( 'manage_woocommerce' ) ) 
                WOO_MSTORE_setup_wizard_nottice();    
        }
    
    /**
    * First time usage require a setip
    *     
    */
    function WOO_MSTORE_setup_wizard_nottice()
        {
            $setup_wizard_completed = get_site_option('mstore_setup_wizard_completed');
            if ( is_multisite() &&  is_woocommerce_active() &&  empty($setup_wizard_completed)) 
                {
                    include( WOO_MSTORE_PATH . '/include/admin/views/html-notice-setup.php' );
                }   
            
        }
    
    
    /**
    * Updates routines
    *     
    */
    function WOO_MSTORE_update_wizard_nottice()
        {
            global $WOO_MSTORE;
            
            $current_page   =   isset($_GET['page']) ?      $_GET['page']   :   '';
            
            if( $WOO_MSTORE->upgrade_require    === TRUE    &&  $current_page   !=  'woonet-upgrade')
                include( WOO_MSTORE_PATH . '/include/admin/views/html-notice-update.php' );

        }
    
    //check for other dependencies
    $options    =   WOO_MSTORE_functions::get_options();
    if($options['sequential-order-numbers'] ==  'yes')
        {
            include_once(WOO_MSTORE_PATH . '/include/class.sequential-order-numbers.php');
            
            new WOO_SON();
        }
        
    //init actions
    add_action('init',  'WOO_MSTORE_init');
    function WOO_MSTORE_init()
        {
            
            WOO_MSTORE_functions::init();
            
            if(is_admin())
                {
                    // Setup wizard
                    if ( ! empty( $_GET['page'] ) ) 
                        {
                            switch ( $_GET['page'] ) 
                                {
                                    case 'woonet-setup' :
                                                        include_once( WOO_MSTORE_PATH . '/include/admin/class-wc-admin-setup-wizard.php' );
                                                        new WC_Admin_Setup_Wizard();
                                                        break;
                                }   
                        }

                }
        }
    
    if(defined('DOING_AJAX'))
        {
            include(WOO_MSTORE_PATH . '/include/class.ajax.php');    
            new WOO_MSTORE_ajax();
        }
             
    if(is_admin())
        {
            //admin
            include(WOO_MSTORE_PATH . '/include/class.admin.php');
            include(WOO_MSTORE_PATH . '/include/class.admin.network-orders.php');
            include(WOO_MSTORE_PATH . '/include/class.admin.network-products.php');
            include(WOO_MSTORE_PATH . '/include/class.admin.product.php');
            include(WOO_MSTORE_PATH . '/include/class.options.php');
            
            global $WOO_MSTORE;
            $WOO_MSTORE = new WOO_MSTORE_admin();
            $WOO_MSTORE->init();
            
            $WOO_MSTORE_options_interface   =   new WOO_MSTORE_options_interface(); 
            
            
            //export functionality
            include(WOO_MSTORE_PATH . '/include/class.admin.export.php');
            new WOO_MSTORE_EXPORT();
        }


?>