<?php

/*
  Plugin Name: WP E-Signature
  Description: Legally sign and collect signatures on documents, contracts, proposals, estimates and more using WP E-Signature.
  Version: 1.3.6
  Author: Approve Me
  Author URI: http://www.approveme.me
  Contributors: Kevin Michael Gray, Micah Blu, Michael Medaglia, Abu Shoaib, Earl Red, Pippin Williamson
  Text Domain: esig
  Domain Path:       /languages
  License/Terms and Conditions: http://www.approveme.me/terms-conditions/
  License/Terms of Use: http://www.approveme.me/terms-of-use/
  Privacy Policy: http://www.approveme.me/privacy-policy/
 */

ob_start();
// Establish OS dependant Directory Separator
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
    define('DS', "\\");
else
    define('DS', '/');

// Define global paths
define('ESIGN_PLUGIN_PATH', dirname(__FILE__));
define('ESIGN_VENDORS_PATH', ESIGN_PLUGIN_PATH . DS . 'vendors' . DS);
define('ESIGN_TEMPLATES_PATH', ESIGN_PLUGIN_PATH . DS . 'page-template' . DS);
define('ESIGN_SIGNATURES_PATH', ESIGN_PLUGIN_PATH . DS . 'e-signature-files'); // SECURITY option to be unique/random/custom
define('ESIGN_DIRECTORY_URI', plugins_url("/", __FILE__));
define('ESIGN_ASSETS_DIR_URI', plugins_url('assets', __FILE__));
define("ENCRYPTION_KEY", "!@#$%^&*");
$upload_dir = wp_upload_dir();
define('ESIG_LOG_DIR', $upload_dir['basedir'] . '/esig-logs/');


if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

include_once "lib/autoload.php";
include_once "models/BaseObject.php";



if (!class_exists('ESIG_License'))
    include( dirname(__FILE__) . '/vendors/WP_E_License_Handler.php' );


$license = new ESIG_License(__FILE__, __('WP E-Signature', 'esig'), '1.3.6', __('Approve Me', 'esig'));

if (!class_exists('WP_E_Digital_Signature')) :

    final class WP_E_Digital_Signature extends WP_E_BaseObject {

        protected static $_instance = null;
        public $setting;
        protected $main_screen = 'esign-docs'; // Main Menu screen
        protected $about_screen = 'esign-about';
        protected $screen_prefix = 'esign-'; // Used for admin screens

        /**
         * Class Constructor
         * 
         * @param null
         * @return void
         * @since 0.1.0
         */

        public function __construct() {

            add_action('admin_enqueue_scripts', array(&$this, 'enqueueAdminScripts'));

            add_action('admin_menu', array(&$this, 'adminMenu'));
            add_action('admin_menu', array($this, 'welcome_menus'));
            add_action('admin_init', array(&$this, 'adminInitHook'));
            add_action('admin_bar_menu', array(&$this, "e_sign_links"), 100);

            add_action('template_redirect', array(&$this, 'remove_other_plugin_force_ssl'), 8);

            add_action('template_redirect', array(&$this, 'esign_force_ssl'), 100);


            $this->shortcode = new WP_E_Shortcode();
            $this->setting = new WP_E_Setting();
            $this->General = new WP_E_General();
            $this->esigrole = new WP_E_Esigrole();

            add_shortcode('wp_e_signature', array($this->shortcode, 'e_sign_document'));


            //add_filter('template_include', array(&$this, 'documentTemplateHook'),-29);
            //add_filter('show_admin_bar' , array(&$this, 'adminBarHook'));
            add_action('wp_ajax_wp_e_signature_ajax', 'wp_e_signature_ajax');
            add_action('wp_ajax_nopriv_wp_e_signature_ajax', 'wp_e_signature_ajax_nopriv');
            add_filter('admin_footer_text', 'e_sign_admin_footer');

            add_filter('all_plugins', array($this->esigrole, 'prepare_plugins'), 10, 1);
        }

        /**
         *
         * URL Route requests to controller methods
         *
         * @param null
         * @return void
         * @since 0.1.0
         * 
         * page $_GET var is constructed as follows:
         *
         * 'controller_method'-'controller_name'
         * So if the page var is: documents-add
         * the contoller would be: documentsController 
         * and the method would be: add()
         */
        function route() {

            $method = 'index'; // default Controller method
            $setting = new WP_E_Setting();
            $user = new WP_E_User();

            $wpid = get_current_user_id();
            // call an action when esignature initialize . 
            do_action('esig-init');
            //Allow users that have not yet saved their settings to still access their System Status #273
            if (!$this->settingsEstablished() && isset($_GET['page']) && $_GET['page'] == 'esign-systeminfo-about') {
                $about = new WP_E_aboutsController();
                $about->systeminfo();
            } elseif (!$this->settingsEstablished() && $_GET['page'] == $this->about_screen) {
                $about = new WP_E_aboutsController();
                $about->index();
            }
            // No settings. New installation
            elseif (!$this->settingsEstablished()) {

                $settings = new WP_E_SettingsController();
                if (count($_POST) == 0) {
                    $alert = array(
                        'type' => 'alert e-sign-alert esig-updated',
                        'title' => '',
                        'message' => __('<strong>Let\'s get this party started</strong> :  Fill in the form below to setup WP E-Signature.', 'esig')
                    );
                    $settings->view->setAlert($alert);
                }

                if ($_GET['page'] != 'esign-settings') {
                    wp_redirect("admin.php?page=esign-settings");
                    exit;
                }

                $settings->index();

                // User not logged in
            } elseif (!$user->checkEsigAdmin($wpid) && $user->getUserTotal() > 0) {

                $admin_user_id = $setting->get_generic('esig_superadmin_user');

                $user_details = get_userdata($admin_user_id);

                $esig_admin = '<div class="esig-updated" style="padding: 11px;width: 515px;margin-top: 17px;">' . __('Super admin is', 'esig') . ' : <span>' . esc_html($user_details->display_name) . '-<a href="mailto:' . $user_details->user_email . '">' . __('Send an email', 'esig') . '</a></span></div>';

                // Currently only administrators have access to this plugin
                $settings = new WP_E_SettingsController();
                $data = array(
                    "feature" => __('Multiple Users', 'esig'),
                    "esig_user_role" => $esig_admin,
                );

                if (current_user_can('manage_options')) {

                    $setting->set("initialized", "false");
                    wp_redirect("admin.php?page=esign-settings");
                    exit;
                }
                $invite_message = $settings->view->renderPartial('upgrade-roles', $data, true, 'settings');
            } else {

                $page = $_GET['page'];

                // Main screen (documents)
                if ($page == $this->main_screen) {

                    $controllerClass = 'WP_E_DocumentsController';
                } else {

                    // Strip out the prefix from $page
                    $pattern = '/^' . $this->screen_prefix . '/';
                    $page = preg_replace($pattern, '', $_GET['page']);

                    // Has hyphen. Call the view
                    if (preg_match("/\-/", $page)) {

                        list($method, $controllerName) = explode("-", $page);
                        // - TODO: this->plural() should be used, and tested
                        $controllerClass = 'WP_E_' . $controllerName . ($controllerName == "settings" ? "" : "s") . "Controller";

                        // No hyphen. Call the index of this controller
                    } else {
                        $controllerClass = 'WP_E_' . $page . (!$this->isPlural($page) ? 's' : '') . "Controller";
                    }
                }

                $controller = new $controllerClass();
                $controller->$method();
            }
        }

        private function settingsEstablished() {

            $setting = new WP_E_Setting();


            if ($setting->get("initialized") == 'true') {
                return true;
            } else {
                return false;
            }
        }

        /**
         * Determines if a string is plural or not
         *
         * @since 1.0.1
         * @param String $str
         * @return Boolean
         */
        private function isPlural($str) {
            if (substr($str, -1) == "s") {
                return true;
            } else {
                return false;
            }
        }

        /*
          welcome about esign menus
         */

        public function welcome_menus() {

            $about = add_dashboard_page('', '', 'manage_options', 'esign-about', array($this, 'route'));
            remove_submenu_page('index.php', 'esign-about');
        }

        /**
         * Register our admin pages with WP
         * 
         * @since 1.0.1
         * @param null
         * @return void
         */
        public function adminMenu() {

            $prefix = $this->screen_prefix;

            // Sidebar Menu Items
            $update_bubble = $this->esigrole->update_bubble(true);

            if ($this->setting->esign_hide_esig_menus()) {
                add_menu_page(__('E-Signature', 'esig'), __('E-Signature' . $update_bubble, 'esig'), 'read', $this->main_screen, array(&$this, 'route'), ESIGN_ASSETS_DIR_URI . '/images/pen_icon.svg');
            }

            add_submenu_page($this->main_screen, __('My Documents', 'esig'), __('My Documents', 'esig'), 'read', $this->main_screen);


            add_submenu_page($this->main_screen, __('Add New Document', 'esig'), __('Add New Document', 'esig'), 'read', $prefix . 'view-document', array(&$this, 'route'));
            add_submenu_page(null, __('Add New Default page', 'esig'), __('Add New Default page', 'esig'), 'read', $prefix . 'pdefault-document', array(&$this, 'route'));

            add_submenu_page($this->main_screen, __('Settings', 'esig'), __('Settings', 'esig'), 'read', $prefix . 'settings', array(&$this, 'route'));


            add_submenu_page($this->main_screen, __('System Status', 'esig'), __('System Status', 'esig'), 'read', $prefix . 'systeminfo-about', array(&$this, 'route'));
            add_submenu_page(null, __('Update Settings', 'esig'), __('Update Settings', 'esig'), 'read', $prefix . 'update-settings', array(&$this, 'route'));

            // Action Items
            if ($this->esigrole->esig_current_user_can('edit_document')) {
                add_submenu_page('edit.php?post_type=esign', __('Add Document', 'esig'), __('Add New Document', 'esig'), 'read', $prefix . 'add-document', array(&$this, 'route'));
                add_submenu_page('edit.php?post_type=esign', __('Edit Document', 'esig'), __('Edit Document', 'esig'), 'read', $prefix . 'edit-document', array(&$this, 'route'));
            }

            add_submenu_page(null, __('Preview Document', 'esig'), __('Preview Document', 'esig'), 'read', $prefix . 'preview-document', array(&$this, 'route'));

            add_submenu_page(null, __('Trash Document', 'esig'), __('Trash Document', 'esig'), 'read', $prefix . 'trash-document', array(&$this, 'route'));
            add_submenu_page(null, __('Delete Document', 'esig'), __('Delete Document', 'esig'), 'read', $prefix . 'delete-document', array(&$this, 'route'));

            add_submenu_page(null, __('Archived Documents', 'esig'), __('Archived Documents', 'esig'), 'read', $prefix . 'archive-document', array(&$this, 'route'));
            add_submenu_page(null, __('UnArchive Document', 'esig'), __('UnArchive Document', 'esig'), 'read', $prefix . 'unarchive-document', array(&$this, 'route'));
            add_submenu_page(null, __('Restore Document', 'esig'), __('Restore Document', 'esig'), 'read', $prefix . 'restore-document', array(&$this, 'route'));
            add_submenu_page(null, __('Resend Document', 'esig'), __('Resend Document', 'esig'), 'read', $prefix . 'resend_invite-document', array(&$this, 'route'));

            // Tab Menu Items
            if ($this->esigrole->esig_current_user_can('have_licenses')) {
                add_submenu_page(null, __('Licenses', 'esig'), __('Licenses', 'esig'), 'read', $prefix . 'licenses-general', array(&$this, 'route'));
                //add_submenu_page(null, __( 'Premium Support', 'esig' ), __( 'Premium Support', 'esig' ), 'read', $prefix.'support-general', array(&$this, 'route'));

                if (is_esig_super_admin()) {
                    $update_bubble = $this->esigrole->update_bubble();

                    add_submenu_page($this->main_screen, __('Add-ons', 'esig'), __('Add-ons' . $update_bubble, 'esig'), 'read', $prefix . 'addons', array(&$this, 'route'));
                }
            }

            if ($this->esigrole->esig_current_user_can('edit_document')) {
                add_submenu_page(null, __('E-mails', 'esig'), __('E-mails', 'esig'), 'read', $prefix . 'mails-general', array(&$this, 'route'));
                add_submenu_page(null, __('Misc', 'esig'), __('Misc', 'esig'), 'read', $prefix . 'misc-general', array(&$this, 'route'));

                add_submenu_page(null, __('E-mail Advanced Settings', 'esig'), __('E-mail Advanced Settings', 'esig'), 'read', $prefix . 'email-general', array(&$this, 'route'));
            }

            add_submenu_page(null, __('About', 'esig'), __('About', 'esig'), 'read', $prefix . 'about-general', array(&$this, 'route'));
            add_submenu_page(null, __('Terms Documents', 'esig'), __('Terms Documents', 'esig'), 'read', $prefix . 'terms-general', array(&$this, 'route'));
            add_submenu_page(null, __('Esig Privacy Policy', 'esig'), __('Esig Privacy Policy', 'esig'), 'read', $prefix . 'privacy-general', array(&$this, 'route'));
        }

        /**
         * Adds new global menu, if $href is false menu is added but registred as submenuable
         *
         * $name String
         * $id String
         * $href Bool/String
         *
         * */
        function add_root_menu($name, $id, $href) {
            global $wp_admin_bar;


            $wp_admin_bar->add_node(array(
                'id' => $id,
                'meta' => array(),
                'title' => $name,
                'href' => $href));
        }

        /**
         * Add's new submenu where additinal $meta specifies class, id, target or onclick parameters
         *
         * $name String
         * $link String
         * $root_menu String
         * $id String
         * $meta Array
         *
         * @return void
         * */
        function add_sub_menu($name, $link, $root_menu, $id, $meta = FALSE) {
            global $wp_admin_bar;


            $wp_admin_bar->add_node(array(
                'parent' => $root_menu,
                'id' => $id,
                'title' => $name,
                'href' => $link,
                'meta' => $meta
            ));
        }

        function e_sign_links() {
            if ($this->setting->esign_hide_esig_menus()) {
                $this->add_root_menu(__('E-Signature', 'esig'), "esign", site_url() . "/wp-admin/admin.php?page=esign-docs");
                $this->add_sub_menu(__('My Documents', 'esig'), site_url() . "/wp-admin/admin.php?page=esign-docs", "esign", "esign-docsa");
                $this->add_sub_menu(__('Add New Document', 'esig'), site_url() . "/wp-admin/admin.php?page=esign-view-document", "esign", "esign-docsb");
                $this->add_sub_menu(__('Settings', 'esig'), site_url() . "/wp-admin/admin.php?page=esign-settings", "esign", "esign-docsc");
                if (is_esig_super_admin()) {

                    $this->add_sub_menu(__('Add-Ons', 'esig'), site_url() . "/wp-admin/admin.php?page=esign-addons", "esign", "esign-docsd");
                    $this->add_sub_menu(__('Premium Support', 'esig'), "https://www.approveme.me/wp-digital-signature-plugin-docs/priority-support/", "esign", "esign-docse");
                }
            }
        }

        /**
         * Enqueue stylesheets and scripts for admin pages
         * 
         * @since 1.0.1
         * @param null
         * @return void
         */
        public function enqueueAdminScripts() {

            $screen = get_current_screen();
            $current_screen = isset($_GET['page']) ? $_GET['page'] : '';

            // If one of the prefixes match, queue the style
            if ($this->isAdminScreen($current_screen)) {

                wp_enqueue_style("wp-jquery-ui-dialog");
                wp_enqueue_style('style', plugins_url('assets/css/style.css', __FILE__));
            }

            // Settings page
            $signature_screens = array(
                'esign-add-document',
                'esign-settings',
                'esign-edit-document',
                'esign-docs',
                'esign-view-document'
            );

            if (in_array($current_screen, $signature_screens)) {

                // Required for signaturepad
                wp_enqueue_script('json2', plugins_url('assets/js/json2.min.js', __FILE__), false, null, true);
                wp_enqueue_script('signaturepad', plugins_url('assets/js/jquery.signaturepad.min.js', __FILE__), array('jquery', 'json2'), null, true);
                wp_enqueue_script('esig-tab', plugins_url('assets/js/jquery.smartTab.js', __FILE__), array('jquery'), null, true);

                // registering and loading bootstrap
                //	wp_enqueue_style( 'e-signature' .'bootstrap', plugins_url('assets/css/bootstrap.min.css', __FILE__), array(), '3.3.4',false );
                //	wp_register_script( 'esig-bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js', array('jquery'),'3.3.4',true);
                //	wp_enqueue_script('esig-bootstrap');
            }

            $admin_screens = array(
                'esign-add-document',
                'esign-settings',
                'esign-edit-document',
                'esign-view-document',
                'esign-misc-general',
                'esign-mails-general',
                'esign-unlimited-sender-role',
                'esign-docs',
                'esign-addons',
                'esign-upload-logo-branding',
                'esign-upload-success-page',
                'esign-woocommerce',
                'esign-email-general',
                'esign-about-general'
            );

            if (in_array($current_screen, $admin_screens)) {

                wp_enqueue_style('e-signature' . '-document-styles', plugins_url('assets/css/chosen.min.css', __FILE__), array(), null, false);

                wp_enqueue_script('jquery-ui-dialog');

                wp_enqueue_script('e-signature' . '-admin-script', plugins_url('assets/js/chosen.jquery.js', __FILE__), array('jquery', 'jquery-ui-dialog'), '1.0.1', true);

                // adding select 2 scripts and css 
                wp_enqueue_style('e-signature' . '-select2-styles', plugins_url('assets/css/select2.css', __FILE__), array(), null, false);
                wp_enqueue_script('e-signature' . '-select2-script', plugins_url('assets/js/select2.js', __FILE__), array('jquery', 'jquery-ui-dialog'), '1.0.13', true);

                wp_enqueue_script('e-signature' . '-admin-script1', plugins_url('assets/js/prism.js', __FILE__), array('jquery', 'jquery-ui-dialog'), '1.0.1', true);
                wp_enqueue_script('esig-tooltip-jquery', plugins_url('assets/js/tooltip.js', __FILE__), array('jquery-ui-tooltip'), '', true);

                wp_enqueue_script('e-signature' . '-admin-script2', plugins_url('assets/js/form.style.js', __FILE__), array('jquery', 'jquery-ui-dialog'), '1.0.1', true);
                wp_enqueue_script('e-signature' . '-common-script2', plugins_url('assets/js/common_admin.js', __FILE__), array('jquery', 'jquery-ui-dialog'), '1.0.1', true);
                wp_localize_script('e-signature' . '-admin-script2', 'esigAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
                wp_localize_script('e-signature' . '-admin-script2', 'esig_tool_tip_script', array('imgurl' => plugins_url('assets/images/callout_black.gif', __FILE__)));
            }
        }

        /**
         * Returns true/false if current admin screen is an esignature screen
         * 
         * @param $screen_id (defaults to current)
         * @return void
         */
        public function isAdminScreen($current_screen_id) {

            if (!$current_screen_id) {
                $screen = get_current_screen();
                $current_screen_id = $screen->id;
            }

            // All esign admin screen prefixes should go here
            $admin_screens = array(
                'esign-add-document',
                'esign-settings',
                'esign-edit-document',
                'esign-view-document',
                'esign-misc-general',
                'esign-mails-general',
                'esign-unlimited-sender-role',
                'esign-docs',
                'esign-addons-general',
                'esign-support-general',
                'esign-licenses-general',
                'esign-addons',
                'esign-upload-logo-branding',
                'esign-upload-success-page',
                'esign-email-general',
                'esign-about-general'
            );

            $found = 0;
            foreach ($admin_screens as $ptrn) {
                $pattern = '/^' . $ptrn . '/';
                preg_match($pattern, $current_screen_id, $matches);
                $found += count($matches);
            }
            return ($found > 0) ? true : false;
        }

        /**
         * Use our page template for documents
         * 
         * @since 1.0.1
         * @param null
         * @return void
         */
        public static function documentTemplateHook($template) {

            if (is_page()) {

                $setting = new WP_E_Setting();
                $esig_doc_id = $setting->get_generic('default_display_page');
                $current_page = get_queried_object_id();

                if (is_page($current_page) && $esig_doc_id && $esig_doc_id == $current_page) {
                    /* global $thesis;
                      if(isset($thesis))
                      {
                      remove_filter( 'template_include', array($thesis->skin, '_skin') );
                      } */

                    $template = ESIGN_TEMPLATES_PATH . "default/index.php";
                }

                $template = apply_filters('esig_document_template', $template, $esig_doc_id, $current_page);
            }

            return $template;
        }

        // removing other plugin enforce especially wocommerce 
        public function remove_other_plugin_force_ssl() {
            global $wpdb;
            $setting = new WP_E_Setting();
            $force_ssl_enabled = $setting->get_generic('force_ssl_enabled');
            $default_display_page = $setting->get_generic('default_display_page');



            if (is_page($default_display_page)) {
                if ($force_ssl_enabled == 1) {
                    remove_action('template_redirect', array('WC_HTTPS', 'unforce_https_template_redirect'));
                }
            }

            $current_page = get_queried_object_id();
            $table = $wpdb->prefix . 'esign_documents_stand_alone_docs';
            $default_page = array();
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") == $table) {
                $default_page = $wpdb->get_col("SELECT page_id FROM {$table}");
            }
            if (is_page($current_page) && in_array($current_page, $default_page)) {
                if ($force_ssl_enabled == 1) {
                    remove_action('template_redirect', array('WC_HTTPS', 'unforce_https_template_redirect'));
                }
            }
        }

        /**
         * Handle redirects before content is output - hooked into template_redirect so is_page works.
         *
         * @access public
         * @return void
         */
        public function esign_force_ssl() {
            global $wpdb;
            $setting = new WP_E_Setting();
            $force_ssl_enabled = $setting->get_generic('force_ssl_enabled');
            $default_display_page = $setting->get_generic('default_display_page');

            $esig_ssl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

            if ($esig_ssl == 'https') {
                return false;
            }

            if (is_page($default_display_page)) {
                if ($force_ssl_enabled == 1 && !is_ssl()) {

                    if (0 === strpos($_SERVER['REQUEST_URI'], 'http')) {
                        wp_safe_redirect(preg_replace('|^http://|', 'https://', $_SERVER['REQUEST_URI']));
                        exit;
                    } else {
                        wp_safe_redirect('https://' . (!empty($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST'] ) . $_SERVER['REQUEST_URI']);
                        exit;
                    }
                }
            }

            $current_page = get_queried_object_id();
            $table = $wpdb->prefix . 'esign_documents_stand_alone_docs';
            $default_page = array();
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") == $table) {
                $default_page = $wpdb->get_col("SELECT page_id FROM {$table}");
            }
            if (is_page($current_page) && in_array($current_page, $default_page)) {
                if ($force_ssl_enabled == 1 && !is_ssl()) {

                    if (0 === strpos($_SERVER['REQUEST_URI'], 'http')) {
                        wp_safe_redirect(preg_replace('|^http://|', 'https://', $_SERVER['REQUEST_URI']));
                        exit;
                    } else {
                        wp_safe_redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
                        exit;
                    }
                }
            }
        }

        /**
         * Hide admin bar for docs
         * 
         * @since 1.0.1
         * @param null
         * @return void
         */
        public function adminBarHook($content) {
            if (is_page()) {
                $setting = new WP_E_Setting();
                $doc_id = $setting->get_generic('default_display_page');

                // We're on a document page
                if (is_page($doc_id)) {

                    if (is_super_admin()) {
                        $content = "none";
                        show_admin_bar(true);
                    } else {
                        $content = "";
                    }
                }
            }
            return $content;
        }

        /**
         * Admin Init Hook
         * 
         * @since 1.0.1
         * @param null
         * @return void
         */
        public function adminInitHook() {
            global $pagenow;

            if ('media-upload.php' == $pagenow || 'async-upload.php' == $pagenow) {
                add_filter('gettext', array(&$this, 'replaceThickboxText'), 1, 3);
            }
        }

        /**
         * Change thickbox text for Admin Settings form
         * 
         * @since 1.0.1
         * @param null
         * @return void
         */
        public function replaceThickboxText($translated_text, $text, $domain) {
            if ('Insert into Post' == $text) {
                $referer = strpos(wp_get_referer(), 'e-signature');
                if ($referer != '') {
                    return __('Use as my company logo', 'esig');
                }
            }
            return $translated_text;
        }

        /**
         * Creates singleton instance of the class
         * 
         * @since 1.0.1
         * @param null
         * @return void
         */
        public static function instance() {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
                // defining other variable . 
                self::$_instance->view = new WP_E_View();
                self::$_instance->invite = new WP_E_Invite;
                self::$_instance->document = new WP_E_Document;
                self::$_instance->user = new WP_E_User;
                self::$_instance->signer = new WP_E_Signer();
                self::$_instance->setting = new WP_E_Setting;
                self::$_instance->validation = new WP_E_Validation();
                self::$_instance->notice = new WP_E_Notice();
                self::$_instance->email = new WP_E_Email();
                self::$_instance->meta = new WP_E_Meta;
                self::$_instance->common = new WP_E_Common();
                self::$_instance->role = new WP_E_Esigrole();
                self::$_instance->signature = new WP_E_Signature;
            }
            return self::$_instance;
        }
        
        /**
         * Throw error on object clone
         *
         * The whole idea of the singleton design pattern is that there is a single
         * object therefore, we don't want the object to be cloned.
         *
         * @since 1.3.1
         * @access protected
         * @return void
         */
        public function __clone() {
            // Cloning instances of the class is forbidden
            _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'esig'), '1.6');
        }

        /**
         * Disable unserializing of the class
         *
         * @since 1.3.1
         * @access protected
         * @return void
         */
        public function __wakeup() {
            // Unserializing instances of the class is forbidden
            _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'esig'), '1.6');
        }

    }

    endif; // Ends if class exists

add_action("plugins_loaded", "init_wp_e_signature");


function WP_E_Sig() {
    return WP_E_Digital_Signature::instance();
}


// register activation / deactivation hooks'
register_activation_hook(__FILE__, 'wp_e_signature_activate_network');
register_deactivation_hook(__FILE__, 'wp_e_signature_deactivate_network');
register_uninstall_hook(__FILE__, 'wp_e_signature_uninstall');

// run this scripts when a new blog is created. 
add_action('wpmu_new_blog', 'esign_new_blog', 10, 6);

function esign_new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta) {
    global $wpdb;
    if (is_plugin_active_for_network(plugin_basename(__FILE__))) {
        $old_blog = $wpdb->blogid;
        switch_to_blog($blog_id);
        wp_e_signature_activate();
        switch_to_blog($old_blog);
    }
}

function wp_e_signature_deactivate_network($network_wide) {
    global $wpdb;

    if (function_exists('is_multisite') && is_multisite()) {
        // check if it is a network activation - if so, run the activation function 
        // for each blog id
        if ($network_wide) {
            $old_blog = $wpdb->blogid;
            // Get all blog ids
            $blogids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
            foreach ($blogids as $blog_id) {
                switch_to_blog($blog_id);
                wp_e_signature_deactivate();
            }
            switch_to_blog($old_blog);
            return;
        }
    }
    wp_e_signature_deactivate();
}

function wp_e_signature_activate_network($network_wide) {
    global $wpdb;

    if (function_exists('is_multisite') && is_multisite()) {
        // check if it is a network activation - if so, run the activation function for each blog id
        if ($network_wide) {
            $old_blog = $wpdb->blogid;
            // Get all blog ids
            $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
            foreach ($blogids as $blog_id) {
                switch_to_blog($blog_id);
                wp_e_signature_activate();
            }
            switch_to_blog($old_blog);
            return;
        }
    }

    wp_e_signature_activate();
}

/* * *
 * e-signatuer deactivation hook 
 * Since 1.0.13 
 * */

function wp_e_signature_deactivate() {

    // removing e-signature config settings . 
    $setting = new WP_E_Setting();
    $esign_remove_all_data = $setting->get_generic('esign_remove_all_data');
    /* if($esign_remove_all_data==1)
      {
      $wp_config_write=new WP_E_Adminenvironment();
      $wp_config_write->esign_config_remove_directive();
      } */
}

/**
 * Activation function; creates db tables
 * 
 * @since 1.0
 * @param null
 * @return void
 */
function wp_e_signature_activate() {

    global $esig_db_version;
    $esig_db_version = "4.0";

    include ESIGN_PLUGIN_PATH . DS . "install.php";

    $doc_page_found = get_page_by_path('e-signature-document');

    $doc_page = array(
        'post_content' => '[wp_e_signature]',
        'post_name' => 'e-signature-document',
        'post_title' => 'E-Signature-Document',
        'post_status' => 'publish',
        'post_type' => 'page',
        'ping_status' => 'closed',
        'comment_status' => 'closed',
    );

    // Update instead of insert
    if ($doc_page_found) {
        $doc_page['ID'] = $doc_page_found->ID;
        wp_insert_post($doc_page, $wp_error);
        $doc_id = $doc_page_found->ID;
    } else {
        $doc_id = wp_insert_post($doc_page, $wp_error);
    }

    $setting = new WP_E_Setting();
    // setting initialized if not inserted . 
    if (!$setting->get_generic('initialized')) {
        // set initialized false . 
        $setting->set("initialized", 'false');
    }
    $setting->set("default_display_page", $doc_id);

    if (!get_option("esig_db_version")) {
        add_option("esig_db_version", $esig_db_version);
    } else {
        update_option("esig_db_version", $esig_db_version);
    }


    set_transient('_esign_activation_redirect', true, 30);
}

/**
 * Database upgrade method if database has been updated
 *
 */
function wp_e_signature_update_db_check() {

    $installed_esig_db_ver = get_option("esig_db_version");

    if (empty($esig_db_version))
        $esig_db_version = '5.0';

    if (version_compare($installed_esig_db_ver, $esig_db_version, '<')) {

        include ESIGN_PLUGIN_PATH . DS . "db_upgrade.php";

        if (!get_option("esig_db_version")) {
            add_option("esig_db_version", $esig_db_version);
        } else {
            update_option("esig_db_version", $esig_db_version);
        }
    }
}

add_action('plugins_loaded', 'wp_e_signature_update_db_check');

/**
 * Uninstall function; drops db tables
 * 
 * @since 1.0
 * @param null
 * @return void
 */
function wp_e_signature_uninstall() {
    // initializing to write wp config file 
    include ESIGN_PLUGIN_PATH . DS . "uninstall.php";

    // Delete the created page
    $doc_page_found = get_page_by_path('e-signature-document');
    if ($doc_page_found) {
        wp_delete_post($doc_page_found->ID, true);
    }
}

function init_wp_e_signature() {
    if (class_exists("WP_E_Digital_Signature")) {
        new WP_E_Digital_Signature();
    }
}

function esign_after_install() {
    global $pagenow;

    if (!is_admin())
        return;

    // Delete the transient

    if (delete_transient('_esign_activation_redirect')) {
        wp_safe_redirect(admin_url('index.php?page=esign-about'));
        exit;
    }
}

add_action('admin_init', 'esign_after_install');

/**
 * Ajax handler for plugin. Routes ajax calls to the appropriate class/method
 * 
 * @since 1.0.1
 * @param null
 * @return void
 */
function wp_e_signature_ajax() {

    if (isset($_POST['className']) && isset($_POST['method'])) {
        $className = $_POST['className'];
        $method = $_POST['method'];
    } else if (isset($_GET['className']) && isset($_GET['method'])) {
        $className = $_GET['className'];
        $method = $_GET['method'];
    } else {
        //return ; 
    }
    if (method_exists($className, $method)) {
        $class = new $className;
        $class->$method();
    } else {
        error_log(__FILE__ . "wp_e_signature_ajax could not find method $className : $method");
    }

    die();
}

function wp_e_signature_ajax_nopriv() {

    if (isset($_POST['className']) && isset($_POST['method'])) {
        $className = $_POST['className'];
        $method = $_POST['method'];
    } else if (isset($_GET['className']) && isset($_GET['method'])) {
        $className = $_GET['className'];
        $method = $_GET['method'];
    } else {
        return;
    }

    // Only some classes allowed
    if (method_exists($className, $method) && $className == 'WP_E_Shortcode') {
        $class = new $className;
        if ($method == 'get_footer_ajax') {
            $class->$method();
        }
    } else {
        error_log(__FILE__ . "wp_e_signature_ajax could not find method $className : $method");
    }

    die();
}

/**
 * Admin Footer
 */
function e_sign_admin_footer($footer_text) {
    if (!empty($_GET['page'])) {

        $page = $_GET['page'];

        if (preg_match("/esign/", $page)) {
            $esign_rate_text = sprintf(__('Thank you a million for choosing <a href="http://www.approveme.me/wp-digital-e-signature/" target="_blank">WP E-Signature</a> by ApproveMe to build, track, and sign your contracts.', 'esig'), 'https://www.approveme.me/wp-digital-e-signature/', 'http://wordpress.org/support/plugins/'
            );

            return str_replace('</span>', '', $footer_text) . ' | ' . $esign_rate_text . '</span>';
        } else {
            return $footer_text;
        }
    }
}

function esig_plugin_name_get_version() {
    if (!function_exists("get_plugin_data"))
        require ABSPATH . 'wp-admin/includes/plugin.php';

    $plugin_data = get_plugin_data(__FILE__);
    $plugin_version = $plugin_data['Version'];
    return $plugin_version;
}

// wp esignature language pack
add_action('plugins_loaded', 'esignature_load_textdomain');

/**
 * Load plugin textdomain.
 *
 * @since 1.1.3
 */
function esignature_load_textdomain() {

    load_plugin_textdomain('esig', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

/**
 * Add "Add-On" hook to core if/when add-ons are installed
 * @param undefined $links
 * 
 * @return
 */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'my_plugin_action_links');

function my_plugin_action_links($links) {

    $settings = new WP_E_Setting();

    if (!$settings->esign_super_admin()) {
        return $links;
    }
    $esigrole = new WP_E_Esigrole();
    $update_bubble = $esigrole->update_bubble(true);
    if ($update_bubble) {
        $text = '<span style="width:20px;text-align:center;display: inline-block;background-color: #d54e21;color: #fff;font-size: 9px;line-height: 17px;font-weight: 600;margin: 1px 0 0 2px;vertical-align: top;-webkit-border-radius: 10px;border-radius: 10px;z-index: 26;">' . $update_bubble . '</span>';
    } else {
        $text = '';
    }

    $links[] = '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=esign-addons')) . '"> ' . __("Add-Ons By Approve Me", "esig") . '</a>' . $text;
    return $links;
}

/**
 *  setting core update msg . 
 */
add_action("admin_init", "esig_core_update_msg");

function esig_core_update_msg() {

    $current = get_site_transient('update_plugins');
    $file = plugin_basename(__FILE__);

    if (!isset($current->response[$file])) {
        if (get_option('esig-core-update')) {
            delete_option('esig-core-update');
            delete_option('esig-core-update-url');
        }
        return false;
    }
    $r = $current->response[$file];
    $addon_id = 100;
    if (version_compare(esig_plugin_name_get_version(), $r->new_version, '<')) {


        //$details_url = self_admin_url('plugin-install.php?tab=plugin-information&plugin=' . $r->slug . '&section=changelog&TB_iframe=true&width=600&height=800');
        $details_url = wp_nonce_url(self_admin_url('update.php?action=upgrade-plugin&plugin=') . $file, 'upgrade-plugin_' . $file);
        $msg = sprintf(__('WP E-Signature core %s Updates is available  <a href="https://www.approveme.me/downloads/wp-e-signature-2/" target="_blank">Change Log</a>'), $r->new_version);

        if (!get_transient('esign-message')) {
            $message = array();
            $message[$addon_id] = $msg;

            set_transient('esign-message', json_encode($message), 300);
            add_option('esig-core-update', $msg);
            add_option('esig-core-update-url', $details_url);
        } else {
            $message = json_decode(get_transient('esign-message'));
            if (empty($message)) {
                $message = array();
                $message[$addon_id] = $msg;
            } elseif (!property_exists($message, $addon_id)) {
                $message->$addon_id = $msg;
            }
            delete_transient('esign-message');
            set_transient('esign-message', json_encode($message), 300);
            update_option('esig-core-update', $msg);
            update_option('esig-core-update-url', $details_url);
        }
    } else {
        if (get_option('esig-core-update')) {
            delete_transient('esign-message');
            //set_transient('esign-message',json_encode($message), 300);
            delete_option('esig-core-update');
            delete_option('esig-core-update-url');
        }
    }
}

// laods some other files . 	
include( dirname(__FILE__) . '/vendors/core-load.php');
include( dirname(__FILE__) . '/vendors/common-function.php');
include( dirname(__FILE__) . '/vendors/plugin-compatibility.php');
include( dirname(__FILE__) . '/vendors/addon/esig-access-control-admin.php');
include( dirname(__FILE__) . '/vendors/addon/esig-access-control-shortcode.php');
