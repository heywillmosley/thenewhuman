<?php

/*
  Plugin Name: Order XML File Export Import for WooCommerce(BASIC)
  Plugin URI: https://wordpress.org/plugins/order-xml-file-export-import-for-woocommerce/
  Description: Import and Export Order detail including line items, From and To your WooCommerce Store as Endicia XML, FedEx XML, UPS WorldShip XML and also WooCommerce XML.
  Author: WebToffee
  Author URI: http://www.webtoffee.com/
  Version: 1.2.0
  WC tested up to: 3.4.4
  Text Domain: wf_order_import_export_xml
  License: GPLv3
  License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

if (!defined('ABSPATH') || !is_admin()) {
    return;
}

define("WF_ORDER_IMP_EXP_XML_ID", "wf_order_imp_exp_xml");
define("wf_woocommerce_order_im_ex_xml", "wf_woocommerce_order_im_ex_xml");

if( class_exists( 'WooCommerce' ) && ! defined('WC_VERSION') )
{
    /**
     * Woocommerce version
     */
    define('WC_VERSION', WC()->version);
}

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    if (!class_exists('OrderImpExpXML_Basic')) :

        class OrderImpExpXML_Basic {

            public function __construct() {
                define('WF_OrderImpExpXML_FILE',  plugin_dir_url( __FILE__ ) . 'sample-files/');
                add_filter('woocommerce_screen_ids', array($this, 'woocommerce_screen_ids'));
                add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'wf_plugin_action_links'));
                add_action('init', array($this, 'load_plugin_textdomain'));
                add_action('init', array($this, 'catch_export_request'), 20);
                add_action('admin_init', array($this, 'register_importers'));

                include_once( 'includes/class-OrderImpExpXML-system-status-tools.php' );
                include_once( 'includes/class-OrderImpExpXML-admin-screen.php' );
                include_once( 'includes/importer/class-OrderImpExpXML-importer.php' );

                if (defined('DOING_AJAX')) {
                    include_once( 'includes/class-OrderImpExpXML-ajax-handler.php' );
                }
            }

            public function wf_plugin_action_links($links) {
                $plugin_links = array(
                    '<a href="' . admin_url('admin.php?page=wf_woocommerce_order_im_ex_xml') . '">' . __('Import Export', 'wf_order_import_export_xml') . '</a>',
                    '<a href="http://www.xadapter.com/product/order-import-export-plugin-for-woocommerce/" target="_blank" style="color:#3db634;">' . __('Premium Upgrade', 'wf_order_import_export_xml') . '</a>',
                    '<a href="https://wordpress.org/support/plugin/order-xml-file-export-import-for-woocommerce">' . __('Support', 'wf_order_import_export_xml') . '</a>',
                );
                return array_merge($plugin_links, $links);
            }

           

            public function woocommerce_screen_ids($ids) {
                $ids[] = 'admin'; // For import screen
                return $ids;
            }

            public function load_plugin_textdomain() {
                load_plugin_textdomain('wf_order_import_export_xml', false, dirname(plugin_basename(__FILE__)) . '/lang/');
            }

            public function catch_export_request() {
                if (!empty($_GET['action']) && !empty($_GET['page']) && $_GET['page'] == 'wf_woocommerce_order_im_ex_xml') {
                    switch ($_GET['action']) {
                        case "export" :
                            $user_ok = $this->hf_user_permission();
                            if ($user_ok) {
                            include_once( 'includes/exporter/class-OrderImpExpXML-base-exporter.php' );
                            $order_imp_exp_obj = new OrderImpExpXMLBase_Exporter();
                            $order_imp_exp_obj->do_export('shop_order');
                            } else {
                                wp_redirect(wp_login_url());
                            }
                            break;
                    }
                }
            }

            
            public function register_importers() {
                register_importer('woocommerce_wf_import_order_xml', 'WooCommerce Order XML', __('Import <strong>Orders</strong> details to your store via a xml file.', 'wf_order_import_export_xml'), 'OrderImpExpXML_Importer::order_importer');
            }
            private function hf_user_permission() {
                // Check if user has rights to export
                $current_user = wp_get_current_user();
                $user_ok = false;
                $wf_roles = apply_filters('hf_user_permission_roles', array('administrator', 'shop_manager'));
                if ($current_user instanceof WP_User) {
		    $can_users = array_intersect($wf_roles, $current_user->roles);	
                    if (!empty($can_users)) {
                        $user_ok = true;
                    }
                }
                return $user_ok;
            }

        }

    endif;

    new OrderImpExpXML_Basic();
}
