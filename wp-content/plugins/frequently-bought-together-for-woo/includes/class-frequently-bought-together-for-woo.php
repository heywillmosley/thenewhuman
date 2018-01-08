<?php

class Frequently_Bought_Together_For_Woo {

    protected $loader;

    protected $plugin_name;

    protected $version;

    public function __construct() {

        $this->plugin_name = 'frequently-bought-together-for-woo';
        $this->version = '1.2.3';

        $this->define_constants();
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    function define_constants() {
        define('MAX_FREQUENTLY_BOUGHT_TOGETHER_PRODUCTS_DISPLAY', 3);
    }

    private function load_dependencies() {

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-frequently-bought-together-for-woo-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-frequently-bought-together-for-woo-i18n.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-frequently-bought-together-for-woo-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-frequently-bought-together-for-woo-public.php';

        $this->loader = new Frequently_Bought_Together_For_Woo_Loader();
    }
    
    private function set_locale() {

        $plugin_i18n = new Frequently_Bought_Together_For_Woo_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    private function define_admin_hooks() {

        $plugin_admin = new Frequently_Bought_Together_For_Woo_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('plugins_loaded', $plugin_admin, 'sa_plugin_upgrade');

        $this->loader->add_action('woocommerce_product_data_tabs', $plugin_admin, 'add_frequently_bought_together_tab');
        $this->loader->add_action('woocommerce_product_data_panels', $plugin_admin, 'add_frequently_bought_together_panel');
        
        $this->loader->add_action('wp_ajax_fbtfw_ajax_search_product', $plugin_admin, 'fbtfw_ajax_search_product');
        $this->loader->add_action('wp_ajax_nopriv_fbtfw_ajax_search_product', $plugin_admin, 'fbtfw_ajax_search_product');

        $this->loader->add_action('woocommerce_process_product_meta_simple', $plugin_admin, 'save_frequently_bought_together_products');
        $this->loader->add_action('woocommerce_process_product_meta_variable', $plugin_admin, 'save_frequently_bought_together_products');
        $this->loader->add_action('woocommerce_process_product_meta_grouped', $plugin_admin, 'save_frequently_bought_together_products');
        $this->loader->add_action('woocommerce_process_product_meta_external', $plugin_admin, 'save_frequently_bought_together_products');

        // Filter to add Quick Help Widget
        $this->loader->add_filter('sa_active_plugins_for_quick_help', $plugin_admin, 'fbtw_active_plugins_for_quick_help',10,2);
    }

    private function define_public_hooks() {

        $plugin_public = new Frequently_Bought_Together_For_Woo_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

        $this->loader->add_action('woocommerce_after_single_product_summary', $plugin_public, 'display_frequently_bought_together_on_product_detail');
        $this->loader->add_action('woocommerce_after_cart', $plugin_public, 'display_frequently_bought_together_on_cart');

        $this->loader->add_filter('template_include', $plugin_public, 'bought_together_include');
    }
    
    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }
    
    public function get_loader() {
        return $this->loader;
    }

    public function get_version() {
        return $this->version;
    }

}