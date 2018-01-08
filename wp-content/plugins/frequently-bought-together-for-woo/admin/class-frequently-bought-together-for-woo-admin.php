<?php

class Frequently_Bought_Together_For_Woo_Admin {

    private $plugin_name;
    
    private $version;

    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/frequently-bought-together-for-woo-admin.css', array(), $this->version, 'all');
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/frequently-bought-together-for-woo-admin.js', array('jquery'), $this->version, false);
    }

    public function sa_plugin_upgrade() {

        if (!class_exists('StoreApps_Upgrade_1_4')) {
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/sa-includes/class-storeapps-upgrade-v-1-4.php';
        }

        $sku = 'FBTW';
        $prefix = $this->plugin_name;
        $plugin_name = 'Frequently Bought Together For Woo';
        $text_domain = $this->plugin_name;
        $documentation_link = 'http://www.storeapps.org/knowledgebase_category/frequently-bought-together-for-woocommerce/';

        $fbtw_upgrader = new StoreApps_Upgrade_1_4 (FREQUENTLY_BOUGHT_TOGETHER_FOR_WOO_PLUGIN_FILE, $sku, $prefix, $plugin_name, $text_domain, $documentation_link);
    }

    public function add_frequently_bought_together_tab($tabs) {

        $tabs['sa-fbtfw'] = array(
            'label' => _x('Frequently Bought Together', 'tab in product data box', 'frequently-bought-together-for-woo'),
            'target' => 'fbtfw_data_option',
            'class' => array('hide_if_grouped', 'hide_if_external'),
        );

        return $tabs;
    }

    public function add_frequently_bought_together_panel() {

        global $post;
        ?>

        <div id="fbtfw_data_option" class="panel woocommerce_options_panel">

            <div class="options_group">

                <p class="form-field"><label for="fbtfw_ids"><?php _e('Select products', 'frequently-bought-together-for-woo'); ?></label>
                    <input type="hidden" class="wc-product-search" style="width: 50%;" id="fbtfw_ids" name="fbtfw_ids" data-placeholder="<?php _e('Search for a product&hellip;', 'frequently-bought-together-for-woo'); ?>" data-multiple="true" data-action="fbtfw_ajax_search_product" data-selected="<?php
                    $product_ids = array_filter(array_map('absint', (array) get_post_meta($post->ID, '_fbtfw_ids', true)));
                    $json_ids = array();

                    foreach ($product_ids as $product_id) {
                        $product = wc_get_product($product_id);
                        if (is_object($product)) {
                            $json_ids[$product_id] = wp_kses_post(html_entity_decode($product->get_formatted_name()));
                        }
                    }

                    echo esc_attr(json_encode($json_ids));
                    ?>" value="<?php echo implode(',', array_keys($json_ids)); ?>" />
                    <?php if(function_exists('wc_help_tip')) {
                            echo wc_help_tip( __( 'Select products for "Frequently bought together" group', 'frequently-bought-together-for-woo') );
                        } else { ?>
                    <img class="help_tip" data-tip='<?php _e('Select products for "Frequently bought together" group', 'frequently-bought-together-for-woo') ?>' src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
                    <?php } ?>
                </p>

            </div>

        </div>

        <?php
    }

    public function fbtfw_ajax_search_product() {

        ob_start();

        check_ajax_referer('search-products', 'security');
        $term = (string) urldecode(stripslashes(strip_tags($_GET['term'])));

        $post_types = array('product', 'product_variation');

        if (empty($term)) {
            die();
        }

        $args = array(
            'post_type' => $post_types,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            's' => $term,
            'fields' => 'ids'
        );

        if (is_numeric($term)) {

            $args2 = array(
                'post_type' => $post_types,
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'post__in' => array(0, $term),
                'fields' => 'ids'
            );

            $args3 = array(
                'post_type' => $post_types,
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'post_parent' => $term,
                'fields' => 'ids'
            );

            $args4 = array(
                'post_type' => $post_types,
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => '_sku',
                        'value' => $term,
                        'compare' => 'LIKE'
                    )
                ),
                'fields' => 'ids'
            );

            $posts = array_unique(array_merge(get_posts($args), get_posts($args2), get_posts($args3), get_posts($args4)));
        } else {

            $args2 = array(
                'post_type' => $post_types,
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => '_sku',
                        'value' => $term,
                        'compare' => 'LIKE'
                    )
                ),
                'fields' => 'ids'
            );

            $posts = array_unique(array_merge(get_posts($args), get_posts($args2)));
        }

        $found_products = array();

        if ($posts) {
            foreach ($posts as $post) {
                $product = wc_get_product($post);
                if ($product->product_type == 'variable') {
                    continue;
                }

                $found_products[$post] = rawurldecode($product->get_formatted_name());
            }
        }

        wp_send_json($found_products);
    }

    public function save_frequently_bought_together_products($post_id) {

        // save products group
        $products = isset($_POST['fbtfw_ids']) ? array_filter(array_map('intval', explode(',', $_POST['fbtfw_ids']))) : array();
        update_post_meta($post_id, '_fbtfw_ids', $products);
    }

    /*
    * Filter to add Quick Help Widget
    *
    * @since 1.1
    */
    function fbtw_active_plugins_for_quick_help( $active_plugins = array(), $upgrader = null ) {

        global $pagenow, $typenow;

        if ( ( !empty( $typenow ) && $typenow == 'product' && !empty( $pagenow ) ) && ( ( $pagenow == 'edit.php' ) || ( $pagenow == 'post.php' ) || ( $pagenow == 'post-new.php' ) ) ) {
            $active_plugins['fbtw'] = 'frequently-bought-together-for-woocommerce'; 
        } elseif ( array_key_exists( 'fbtw', $active_plugins ) ) {
            unset( $active_plugins['fbtw'] );
        }

        return $active_plugins;
    }

}
