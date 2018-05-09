<?php
define( "BeRocket_LMP_domain", 'BeRocket_LMP_domain');
load_plugin_textdomain('BeRocket_LMP_domain', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
require_once(plugin_dir_path( __FILE__ ).'berocket/framework.php');
require_once(plugin_dir_path( __FILE__ ).'includes/functions.php');
/*
 * Class BeRocket_LMP
 * REPLACE
 * BeRocket_LMP - plugin name
 * BeRocket_Load_More_Products - normal plugin name
 * BeRocket_Load_More_Products - full plugin name
 * 3 - id on BeRocket
 * woocommerce-load-more-products - slug on BeRocket
 * %PREMIUM_PRICE% - price on BeRocket
 */
class BeRocket_LMP extends BeRocket_Framework {
    public static $settings_name = 'br_load_more_products';
    protected static $instance;

    public $info, $defaults, $values;

    function __construct () {
        $this->info = array(
            'id'          => 3,
            'version'     => BeRocket_Load_More_Products_version,
            'plugin'      => '',
            'slug'        => '',
            'key'         => '',
            'name'        => '',
            'plugin_name' => 'BeRocket_LMP',
            'full_name'   => 'BeRocket Load More Products',
            'norm_name'   => 'Load More Products',
            'price'       => '',
            'domain'      => 'BeRocket_LMP_domain',
            'templates'   => '',
            'plugin_file' => __DIR__ . '/load-more-products.php',
            'plugin_dir'  => __DIR__,
        );

        $this->defaults = array(
            'br_lmp_general_settings'   => array(
                'type'                      => 'infinity_scroll',
                'update_url'                => '',
                'use_mobile'                => '',
                'mobile_type'               => 'more_button',
                'mobile_width'              => '767',
                'products_per_page'         => '',
                'choose_loading'            => 'icons',
                'loading_image'             => 'fa-spinner',
                'rotate_image'              => '1',
                'buffer'                    => '50',
                'use_wpml'                  => '0',
            ),
            'br_lmp_button_settings'    => array(
                'button_text'               => 'Load More',
                'custom_class'              => '',
                'background-color'          => '#aaaaff',
                'color'                     => '#333333',
                'border-color'              => '#000',
                'font-size'                 => '22',
                'padding-left'              => '25',
                'padding-right'             => '25',
                'padding-top'               => '15',
                'padding-bottom'            => '15',
                'hover'                     => array(
                    'background-color'          => '#9999ff',
                    'color'                     => '#111111',
                ),
            ),
            'br_lmp_prev_settings'    => array(
                'enable_prev'               => '0',
                'button_text'               => 'Load Previous',
                'custom_class'              => '',
                'background-color'          => '#aaaaff',
                'color'                     => '#333333',
                'border-color'              => '#000',
                'font-size'                 => '22',
                'padding-left'              => '25',
                'padding-right'             => '25',
                'padding-top'               => '15',
                'padding-bottom'            => '15',
                'hover'                     => array(
                    'background-color'          => '#9999ff',
                    'color'                     => '#111111',
                ),
            ),
            'br_lmp_selectors_settings' => array(
                'products'                  => 'ul.products',
                'item'                      => 'li.product',
                'pagination'                => '.woocommerce-pagination',
                'next_page'                 => '.woocommerce-pagination a.next',
                'prev_page'                 => '.woocommerce-pagination a.prev',
            ),
            'br_lmp_lazy_load_settings' => array(
                'use_lazy_load'             => '',
                'use_lazy_load_mobile'      => '',
                'animation'                 => '',
            ),
            'br_lmp_messages_settings'  => array(
                'loading'                   => 'Loading...',
                'loading_class'             => '',
                'end_text'                  => 'No more products',
                'end_text_class'            => '',
            ),
            'br_lmp_javascript_settings'=> array(
                'before_update'             => '',
                'after_update'              => '',
            ),
            'br_lmp_license_settings'   => array(
                'plugin_key'                => '',
            ),
            'custom_css' => '',
            'script'     => array(
                'js_page_load'      => '',
            ),
            'plugin_key' => '',
        );

        $this->values = array(
            'settings_name' => 'br_load_more_products',
            'option_page'   => 'br_load_more_products',
            'premium_slug'  => 'woocommerce-load-more-products',
            'free_slug'     => 'load-more-products-for-woocommerce',
        );
        
        // List of the features missed in free version of the plugin
        $this->feature_list = array();

        parent::__construct( $this );
        
        $options = $this->get_option();
        if(empty($options)) {
            $options = array();
            $options['br_lmp_general_settings'] = get_option('br_lmp_general_settings');
            $options['br_lmp_button_settings'] = get_option('br_lmp_button_settings');
            $options['br_lmp_selectors_settings'] = get_option('br_lmp_selectors_settings');
            $options['br_lmp_lazy_load_settings'] = get_option('br_lmp_lazy_load_settings');
            $options['br_lmp_messages_settings'] = get_option('br_lmp_messages_settings');
            $options['br_lmp_javascript_settings'] = get_option('br_lmp_javascript_settings');
            $options['br_lmp_license_settings'] = get_option('br_lmp_license_settings');
            
            update_option($this->values['settings_name'], $options);
            
            delete_option('br_lmp_general_settings');
            delete_option('br_lmp_button_settings');
            delete_option('br_lmp_selectors_settings');
            delete_option('br_lmp_lazy_load_settings');
            delete_option('br_lmp_messages_settings');
            delete_option('br_lmp_javascript_settings');
            delete_option('br_lmp_license_settings');
        }

        if ( $this->init_validation() ) {
           // add_filter( 'BeRocket_updater_add_plugin', array( $this, 'updater_info' ) );
            if ( ! @ is_network_admin() ) {

                if ( ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) && br_get_woocommerce_version() >= 2.1 ) {
                    load_plugin_textdomain('BeRocket_LMP_domain', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
                    add_action ( 'init', array( $this, 'init' ) );
                    add_action ( 'wp_head', array( $this, 'wp_header' ) );
                    add_action ( 'wp_head', array( $this, 'check_shop' ) );
                    add_action ( 'admin_init', array( $this, 'include_admin' ) );
                    add_filter ( 'berocket_aapf_user_func', array( $this, 'filters_compatibility' ) );
                    add_filter ( 'berocket_lgv_user_func', array( $this, 'list_grid_compatibility' ) );
                    add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
                    $plugin_base_slug = plugin_basename( __FILE__ );
                    add_filter( 'plugin_action_links_' . $plugin_base_slug, array( $this, 'plugin_action_links' ) );
                    add_action( "wp_ajax_save_br_selectors", array ( $this, 'save_br_selectors' ) );
                    add_action('woocommerce_before_template_part', array(__CLASS__, 'woocommerce_before_template_part'), 1);
                    do_action( 'lmp_construct_end' );
                }
            }
        }
    }

    public function init() {
        $options = $this->get_option();
        if ( ! empty($options['br_lmp_selectors_settings']['use_filters_settings']) && (br_is_plugin_active( 'filters', '2.0.5' ) || br_is_plugin_active( 'AJAX_filters', '2.0.5' )) ) {
            $filters_settings = apply_filters( 'berocket_aapf_listener_br_options', get_option('br_filters_options') );
            $options['br_lmp_selectors_settings']['products'] = @ $filters_settings['products_holder_id'];
            $options['br_lmp_selectors_settings']['item'] = '.product';
            $options['br_lmp_selectors_settings']['pagination'] = @ $filters_settings['woocommerce_pagination_class'];
            $options['br_lmp_selectors_settings']['next_page'] = '.next';
            $options['br_lmp_selectors_settings']['prev_page'] = '.prev';
            unset($options['br_lmp_selectors_settings']['use_filters_settings']);
            update_option($this->values['settings_name'], $options);
        }
        parent::init();
    }

    /**
     * Framework class will use this function to check it plugin is activated. For example if we need
     * woocommerce installed to run the plugin we can check here and return false if we need to stop
     *
     * @return boolean
     */
    public function init_validation() {
        return true;
    }
    
    /**
     * Function add options button to admin panel if parent will not do it self
     *
     * @access public
     *
     * @return void
     */
    public function admin_menu() {
        if ( parent::admin_menu() ) {
            add_submenu_page(
                'woocommerce',
                $this->info[ 'norm_name' ] . ' ' . __( 'Settings', 'BeRocket_LMP_domain' ),
                $this->info[ 'full_name' ],
                'manage_options',
                $this->values[ 'option_page' ],
                array(
                    $this,
                    'option_form'
                )
            );
        }
    }
    
    public function check_shop()
    {
        $options = parent::get_option();
        $general_options = $options['br_lmp_general_settings'];
        if(is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() || empty($general_options['only_woo_pages']))
        {
            $this->include_front();
        }        
    }

	public static function woocommerce_before_template_part($template_name) {
        if( $template_name == 'loop/result-count.php' ) {
            add_filter('ngettext', array(__CLASS__, 'load_more_products_count_additional'), 1, 9999);
            add_filter('ngettext_with_context', array(__CLASS__, 'load_more_products_count_additional'), 1, 9999);
        }
    }

    public static function load_more_products_count_additional($gettext) {
        remove_filter('ngettext', array(__CLASS__, 'load_more_products_count_additional'), 1, 9999);
        remove_filter('ngettext_with_context', array(__CLASS__, 'load_more_products_count_additional'), 1, 9999);
        if( class_exists('WC_Query') &&  method_exists('WC_Query', 'product_query') && function_exists('wc_get_loop_prop') ) {
            $total      = wc_get_loop_prop( 'total' );
            $per_page   = wc_get_loop_prop( 'per_page' );
            $paged      = wc_get_loop_prop( 'current_page' );
            $first      = ( $per_page * $paged ) - $per_page + 1;
            $last       = min( $total, $per_page * $paged );
        } else {
            global $wp_query;
            $paged    = max( 1, $wp_query->get( 'paged' ) );
            $per_page = $wp_query->get( 'posts_per_page' );
            $total    = $wp_query->found_posts;
            $first    = ( $per_page * $paged ) - $per_page + 1;
            $last     = min( $total, $wp_query->get( 'posts_per_page' ) * $paged );
        }
		echo '<span class="br_product_result_count" style="display: none;" data-text="';
		if ( $total <= $per_page || -1 === $per_page ) {
			/* translators: %d: total results */
			printf( _n( 'Showing the single result', 'Showing all %d results', $total, 'woocommerce' ), $total );
		} else {
			/* translators: 1: first result 2: last result 3: total results */
			printf( _nx( 'Showing the single result', 'Showing %1$d&ndash;%2$d of %3$d results', $total, 'with first and last result', 'woocommerce' ), -1, -2, $total );
		}
		echo '" data-start="', $first, '" data-end="', $last, '" data-laststart=', $first, ' data-lastend=', $last, '></span>';
        return $gettext;
    }
    
    public function wp_header() {
        $options = parent::get_option();
        $options_btn = $options['br_lmp_button_settings'];
        $options_btn_prev = $options['br_lmp_prev_settings'];
        echo '<style>
            .lmp_load_more_button.br_lmp_button_settings .lmp_button:hover {
                background-color: '.$options_btn['hover']['background-color'].'!important;
                color: '.$options_btn['hover']['color'].'!important;
            }
            .lmp_load_more_button.br_lmp_prev_settings .lmp_button:hover {
                background-color: '.$options_btn_prev['hover']['background-color'].'!important;
                color: '.$options_btn_prev['hover']['color'].'!important;
            }
            .lazy{opacity:0;}
        </style>';
    }
    
    public function list_grid_compatibility( $user_func ) {
        $options = get_option('br_load_more_products');
        $selectors_options = $options['br_lmp_selectors_settings'];
        $item_selector = $selectors_options['item'];
        $after_set = 'jQuery(window).scrollTop(jQuery(window).scrollTop() + 1).scrollTop(jQuery(window).scrollTop() - 1);';
        $after_set .= "jQuery( '{$item_selector}.animated').trigger('lazyshow');";
        $user_func['after_style_set'] = $after_set . $user_func['after_style_set'];
        return $user_func;
    }
    
    public function filters_compatibility( $user_func ) {
        $after_update = "lmp_update_state();";
        $user_func['after_update'] = $after_update . $user_func['after_update'];
        return $user_func;
    }
    
    public function get_load_more_button($option_name = 'br_lmp_button_settings') {
        $options = parent::get_option();
        $options_btn = $options[$option_name];
        $general_options = $options['br_lmp_general_settings'];
        if ( $general_options['use_wpml'] ) {
            $text = __( 'Load More', 'BeRocket_LMP_domain' );
        } else {
            $text = $options_btn['button_text'];
        }
        $button = '<div class="lmp_load_more_button ' . $option_name . '">';
        $button .= '<a class="lmp_button '.$options_btn['custom_class'].'" style="';
        $button = apply_filters('berocket_lmp_button_style', $button, $option_name, $options_btn);
        $button .= '" href="#load_next_page">'.$text.'</a>';
        $button .= '</div>';
        return $button;
    }
    
    public function add_javascript_data() {
        /* theme scripts */
        if( defined('THE7_VERSION') && THE7_VERSION ) {
            wp_enqueue_script( 'berocket_ajax_fix-the7', plugins_url( 'js/themes/the7.js', __FILE__ ), array( 'jquery' ) );
            add_filter('berocket_lmp_user_func', array(__CLASS__, 'the7_fix'));
        }
        $options = parent::get_option();
        $general_options = $options['br_lmp_general_settings'];
        $button_options = $options['br_lmp_button_settings'];
        $prev_options = $options['br_lmp_prev_settings'];
        $selectors_options = $options['br_lmp_selectors_settings'];
        $lazy_load_options = $options['br_lmp_lazy_load_settings'];
        $messages_options = $options['br_lmp_messages_settings'];
        $javascript_options = $options['br_lmp_javascript_settings'];
        $products_selector = $selectors_options['products'];
        $item_selector = $selectors_options['item'];
        $pagination_selector = $selectors_options['pagination'];
        $next_page_selector = $selectors_options['next_page'];
        $prev_page_selector = $selectors_options['prev_page'];
        $image_class = '';
        if( $general_options['rotate_image'] ) {
            $image_class = 'lmp_rotate';
        }
        $image = '<div class="lmp_products_loading">';
        if ( $general_options['loading_image'] ) {
            if ( substr( $general_options['loading_image'], 0, 3) == 'fa-' ) {
                $image .= '<i class="fa '.$general_options['loading_image'].' '.$image_class.'"></i>';
            } else {
                $image .= '<i class="fa '.$image_class.'"><img class="berocket_widget_icon" src="'.$general_options['loading_image'].'" alt=""></i>';
            }
        } else {
            $image .= '<i class="fa fa-spinner '.$image_class.'"></i>';
        }
        if ( $general_options['use_wpml'] ) {
            $image .= '<span class="'.$messages_options['loading_class'].'">'.__( 'Loading...', 'BeRocket_LMP_domain' ).'</span></div>';
            $end_text = '<div class="lmp_products_loading"><span class="'.$messages_options['end_text_class'].'">'.__( 'No more products', 'BeRocket_LMP_domain' ).'</span></div>';
        } else {
            $image .= '<span class="'.$messages_options['loading_class'].'">'.$messages_options['loading'].'</span></div>';
            $end_text = '<div class="lmp_products_loading"><span class="'.$messages_options['end_text_class'].'">'.$messages_options['end_text'].'</span></div>';
        }
        $load_more_button = $this->get_load_more_button();
        $load_prev_button = $this->get_load_more_button('br_lmp_prev_settings');

        wp_localize_script(
            'berocket_lmp_js',
            'the_lmp_js_data',
            array(
                'type'          => $general_options['type'],
                'update_url'    => empty($general_options['update_url']), // if $general_options['update_url'] is set it means stop updating
                'use_mobile'    => $general_options['use_mobile'],
                'mobile_type'   => $general_options['mobile_type'],
                'mobile_width'  => $general_options['mobile_width'],
                'is_AAPF'       => ( br_is_plugin_active( 'filters', '2.0.5' ) || br_is_plugin_active( 'AJAX_filters', '2.0.5' ) ),
                'buffer'        => $general_options['buffer'],
                'use_prev_btn'  => $prev_options['enable_prev'],

                'load_image'    => $image,
                'load_img_class'=> '.lmp_products_loading',

                'load_more'     => $load_more_button,
                'load_prev'     => $load_prev_button,

                'lazy_load'     => $lazy_load_options['use_lazy_load'],
                'lazy_load_m'   => $lazy_load_options['use_lazy_load_mobile'],
                'LLanimation'   => $lazy_load_options['animation'],

                'end_text'      => $end_text,

                'javascript'    => apply_filters( 'berocket_lmp_user_func', $javascript_options ),

                'products'      => $products_selector,
                'item'          => $item_selector,
                'pagination'    => $pagination_selector,
                'next_page'     => $next_page_selector,
                'prev_page'     => $prev_page_selector,
            )
        );
    }

    public static function the7_fix($scripts) {
        $scripts['after_update'] = 'fixWooIsotope();fixWooOrdering(); '.$scripts['after_update'];
        return $scripts;
    }
    
    public function include_front() {
        wp_enqueue_script( 'berocket_lmp_js', plugins_url( 'js/load_products.js', __FILE__ ), array( 'jquery' ), $this->info['version'] );
        wp_register_style( 'berocket_lmp_style', plugins_url( 'css/load_products.css', __FILE__ ), "", $this->info['version'] );
        wp_enqueue_style( 'berocket_lmp_style' );
        wp_register_style( 'font-awesome', plugins_url( 'css/font-awesome.min.css', __FILE__ ) );
        wp_enqueue_style( 'font-awesome' );
        $this->add_javascript_data();
    }
    
    public function include_admin() {
        if( ! empty($_GET['page']) && $_GET['page'] == 'br_load_more_products' ) {
            wp_register_style( 'berocket_lmp_admin_style', plugins_url( 'css/admin.css', __FILE__ ), "", $this->info['version'] );
            wp_enqueue_style( 'berocket_lmp_admin_style' );
            wp_register_style( 'berocket_lmp_fa_select_style', plugins_url( 'css/select_fa.css', __FILE__ ), "", $this->info['version'] );
            wp_enqueue_style( 'berocket_lmp_fa_select_style' );
            wp_enqueue_script( 'berocket_lmp_admin', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), $this->info['version'] );
            wp_enqueue_script( 'berocket_lmp_admin_fa', plugins_url( 'js/admin_select_fa.js', __FILE__ ), array( 'jquery' ), $this->info['version'] );
            wp_enqueue_script( 'berocket_aapf_widget-colorpicker', plugins_url( 'js/colpick.js', __FILE__ ), array( 'jquery' ) );
            wp_register_style( 'berocket_aapf_widget-colorpicker-style', plugins_url( 'css/colpick.css', __FILE__ ) );
            wp_enqueue_style( 'berocket_aapf_widget-colorpicker-style' );
        }
    }
    
    /**
     * Function add provide data for the settings page generation
     *
     * Data structure:
     * First level - tabs names => content
     * Second level - fields with their options
     *
     * section - field(default) | header | license | <custom section name>
     *
     * field:
     * \ type - text, checkbox, radio, selectbox, textarea, color, image. Default: text
     * \ label - text displayed to the user
     * \ label_for - text from right side of checkbox, radio
     * \ name - field name for the saving purpose. Required
     * \ value - field value will be used only if isset( options[field] ) will return false
     * \ class - field classes( splitted by 1 space, eg 'class1 class2 class3' )
     * \ extra - need to add placeholder or data-something="5"? use this field. It will add data as is
     * \ items - multiple fields in one line(table block). label, class
     *      array(
     *          array('type' => 'color', 'value' => '#FFEEBB'),
     *          array('type' => 'checkbox', 'value' => '1', 'label_for' => 'Use color'),
     *      )
     * \ options - for selectbox, options to choose from
     *      array(
     *          array( 'value' => 'US', 'text' => 'United States' ),
     *          array( 'value' => 'UA', 'text' => 'Ukraine' ),
     *          ........
     *          array( 'value' => 'IT', 'text' => 'Italy' ),
     *      )
     * \ default_button - for color, button to set color to default value
     * \ remove_button - for image, button to remove image
     * All default values can be set in $defaults variable
     *
     * header:
     * \ type - 1-6 <h{$type}>
     * \ label - text inside the h tag
     * \ class - h tag classes( splitted by 1 space, eg 'class1 class2 class3' )
     * \ extra - need to add placeholder or data-something="5"? use this field. It will add data as is
     *
     * license:
     * \ test - If show test button for the license section
     *
     * <custom section name>:
     * \ <custom section name> - will be used to get a function section_<custom section name>
     * \ variables - function section_<custom section name> will get 2 vars:
     *      \ $item - all the data about section provided in admin_settings array
     *      \ $options - return of $this->get_option();
     * Function must return value, not output.
     *
     * @access public
     *
     * @return void
     */
    public function admin_settings( $tabs_info = array(), $data = array() ) {
        $options = parent::get_option();
        $Selectors = array();
        $Selectors[] = array(
            "label"     => __( 'Products Container Selector', "BeRocket_LMP_domain" ),
            "name"      => array("br_lmp_selectors_settings", "products"),
            "type"      => "text",
            "value"     => $this->defaults["br_lmp_selectors_settings"]["products"],
            "class"     => "lmp_button_selectors lmp_button_selectorsfalse lmp_button_selector_products",
        );
        $Selectors[] = array(
            "label"     => __( 'Product Item Selector', "BeRocket_LMP_domain" ),
            "name"      => array("br_lmp_selectors_settings", "item"),
            "type"      => "text",
            "value"     => $this->defaults["br_lmp_selectors_settings"]["item"],
            "class"     => "lmp_button_selectors lmp_button_selectorsfalse lmp_button_selector_item",
        );
        $Selectors[] = array(
            "label"     => __( 'Pagination Selector', "BeRocket_LMP_domain" ),
            "name"      => array("br_lmp_selectors_settings", "pagination"),
            "type"      => "text",
            "value"     => $this->defaults["br_lmp_selectors_settings"]["pagination"],
            "class"     => "lmp_button_selectors lmp_button_selectorsfalse lmp_button_selector_pagination",
        );
        $Selectors[] = array(
            "label"     => __( 'Next Page Selector', "BeRocket_LMP_domain" ),
            "name"      => array("br_lmp_selectors_settings", "next_page"),
            "type"      => "text",
            "value"     => $this->defaults["br_lmp_selectors_settings"]["next_page"],
            "class"     => "lmp_button_selectors lmp_button_selectorsfalse lmp_button_selector_next_page",
        );
        $Selectors[] = array(
            "label"     => __( 'Previous Page Selector', "BeRocket_LMP_domain" ),
            "name"      => array("br_lmp_selectors_settings", "prev_page"),
            "type"      => "text",
            "value"     => $this->defaults["br_lmp_selectors_settings"]["prev_page"],
            "class"     => "lmp_button_selectors lmp_button_selectorsfalse lmp_button_selector_prev_page",
        );
        $Selectors[] = array(
            "section"   => "autoselector_set",
            "value"     => "",
        );
        parent::admin_settings(
            array(
                'General' => array(
                    'icon' => 'cog',
                ),
                'Button-Settings' => array(
                    'icon' => 'square',
                ),
                'Previous-Settings' => array(
                    'icon' => 'square',
                ),
                'Selectors' => array(
                    'icon' => 'circle-o',
                ),
                'JavaScript' => array(
                    'icon' => 'code',
                ),
                'CSS'     => array(
                    'icon' => 'css3',
                ),
                'License' => array(
                    'icon' => 'unlock-alt'
                ),
            ),
            array(
            'General' => array(
                'general_type' => array(
                    "label"     => __( "Products Loading Type", 'BeRocket_LMP_domain' ),
                    "name"     => array("br_lmp_general_settings", "type"),   
                    "type"     => "selectbox",
                    "options"  => array(
                        array('value' => 'none', 'text' => __('None', 'BeRocket_LMP_domain')),
                        array('value' => 'infinity_scroll', 'text' => __('Infinity Scroll', 'BeRocket_LMP_domain')),
                        array('value' => 'more_button', 'text' => __('Load More Button', 'BeRocket_LMP_domain')),
                        array('value' => 'pagination', 'text' => __('AJAX Pagination', 'BeRocket_LMP_domain')),
                    ),
                    "value"    => 'infinity_scroll',
                ),
                'load_image' => array(
                    "label"     => __( 'Loading Image', 'BeRocket_LMP_domain' ),
                    "items"     => array(
                        'image' => array(
                            "type"      => "fontawesome",
                            "name"      => array("br_lmp_general_settings", "loading_image"),
                            "value"     => "fa-spinner",
                        ),
                        array(
                            "type"      => "checkbox",
                            "name"      => array("br_lmp_general_settings", "rotate_image"),
                            "value"     => "1",
                            "label_for" => __( 'Rotating image on load' , "BeRocket_LMP_domain" ),
                        ),
                    ),
                ),
                array(
                    "label"     => __( "Buffer Pixels", "BeRocket_LMP_domain" ),
                    "type"      => "number",
                    "name"      => array("br_lmp_general_settings", "buffer"),
                    "value"     => $this->defaults["br_lmp_general_settings"]["buffer"]
                ),
                'update_url' => array(
                    "label"     => __( "Don't update url when next page shown", "BeRocket_LMP_domain" ),
                    "type"      => "checkbox",
                    "name"      => array("br_lmp_general_settings", "update_url"),
                    "value"     => "1"
                ),
                'only_woo_pages' => array(
                    "label"     => __( "JavaScript and CSS uses only on WooCommerce pages", "BeRocket_LMP_domain" ),
                    "type"      => "checkbox",
                    "name"      => array("br_lmp_general_settings", "only_woo_pages"),
                    "value"     => "1",
                ),
            ),
            'Button-Settings' => array(
                'btn_custom_class' => array(
                    "label"     => __( "Custom Class", "BeRocket_LMP_domain" ),
                    "name"      => "custom_class",
                    "section"   => "btn_custom_class",
                    "value"     => "",
                ),
                'button_text' =>array(
                    "label"     => __( "Text on button", "BeRocket_LMP_domain" ),
                    "name"      => array("br_lmp_button_settings", "button_text"),
                    "type"      => "text",
                    "value"     => $this->defaults["br_lmp_button_settings"]["button_text"],
                    "class"     => "lmp_button_settings",
                    "extra"     => 'data-default="'.$this->defaults["br_lmp_button_settings"]["button_text"].'" data-style="text"'
                ),
                array(
                    "section"   => "btn_default",
                    "value"     => "",
                ),
            ),
            'Previous-Settings' => array(
                'enable_prev' => array(
                    "label"     => __( "Enable Previous Button", "BeRocket_LMP_domain" ),
                    "type"      => "checkbox",
                    "name"      => array("br_lmp_prev_settings", "enable_prev"),
                    "value"     => "1"
                ),
                'btn_prev_custom_class' => array(
                    "label"     => __( "Custom Class", "BeRocket_LMP_domain" ),
                    "name"      => "custom_class",
                    "section"   => "btn_prev_custom_class",
                    "value"     => "",
                ),
                'button_prev_text' =>array(
                    "label"     => __( "Text on button", "BeRocket_LMP_domain" ),
                    "name"      => array("br_lmp_prev_settings", "button_text"),
                    "type"      => "text",
                    "value"     => $this->defaults["br_lmp_prev_settings"]["button_text"],
                    "class"     => "lmp_button_settings",
                    "extra"     => 'data-default="'.$this->defaults["br_lmp_prev_settings"]["button_text"].'" data-style="text"'
                ),
                array(
                    "section"   => "btn_prev_default",
                    "value"     => "",
                ),
            ),
            'Selectors' => $Selectors,
            'JavaScript' => array(
                array(
                    "type"  => "textarea",
                    "label" => __( "Before Update", "BeRocket_LMP_domain" ),
                    "name"  => array("br_lmp_javascript_settings", "before_update"),
                    "value" => "",
                ),
                array(
                    "type"  => "textarea",
                    "label" => __( "After Update", "BeRocket_LMP_domain" ),
                    "name"  => array("br_lmp_javascript_settings", "after_update"),
                    "value" => "",
                ),
            ),
            'CSS'     => array(
                array(
                    "type"  => "textarea",
                    "label" => __( "Custom CSS", "BeRocket_LMP_domain" ),
                    "name"  => "custom_css",
                    "value" => "",
                ),
            ),
            'License' => array(
                array(
                    "section" => "license",
                    "label"   => "Plugin key",
                    "name"    => "plugin_key",
                    "value"   => "",
                    "test"    => true
                ),
            ),
        ) );
    }
    
    /*
     *  SECTIONS
     */
      
    //BUTTON
    
    public function section_btn_custom_class ( $item, $options )
    {
        $html = "<th>" . $item['label'] . "</th>".
            "<td>".
                "<input class='lmp_button_settings' data-style='custom_css' name='" . $this->values['settings_name'] . "[br_lmp_button_settings][" . $item['name'] . "]' value='" . $options['br_lmp_button_settings']['custom_class'] . "' type='text'>".
            "</td>".
            "<td class='btn-preview-td'>".
                "<h1 style='text-align: center;'>Preview</h1>".
                "<div class='btn-preview-block'>" . $this->get_load_more_button() . "</div>".
            "</td>";
        return $html;
    }

   
    public function section_btn_default ( $item, $options )
    {
       $html = '<th></th>'.
                '<td>'.
                    '<input type="button" value="' . __( 'Set all to default', 'BeRocket_LMP_domain' ) . '" class="all_theme_default_lmp button">'.
                '</td>';
        return $html;
    }
    
    public function section_btn_prev_custom_class ( $item, $options )
    {
        $html = "<th>" . $item['label'] . "</th>".
            "<td>".
                "<input class='lmp_button_settings' data-style='custom_css' name='" . $this->values['settings_name'] . "[br_lmp_prev_settings][" . $item['name'] . "]' value='" . $options['br_lmp_prev_settings']['custom_class'] . "' type='text'>".
            "</td>".
            "<td class='btn-prev-preview-td'>".
                "<h1 style='text-align: center;'>Preview</h1>".
                "<div class='btn-preview-block'>" . $this->get_load_more_button('br_lmp_prev_settings') . "</div>".
            "</td>";
        return $html;
    }

   
    public function section_btn_prev_default ( $item, $options )
    {
       $html = '<th></th>'.
                '<td>'.
                    '<input type="button" value="' . __( 'Set all to default', 'BeRocket_LMP_domain' ) . '" class="all_theme_default_lmp button">'.
                '</td>';
        return $html;
    }
    
    //SELECTORS
    
    public function section_autoselector_set ( $item, $options )
    {
        do_action('BeRocket_wizard_javascript');
        $html  = '</tr><tr><td colspan="2" class="lmp_button_selectors lmp_button_selectorsfalse" style=" font-size: 32px; font-weight: 600; text-align: center; padding-top: 40px;">' . __('OR', 'BeRocket_LMP_domain') . '</td></tr><tr>'.
                            '<th class="row">' . __('Get Selectors automatically (BETA)', 'BeRocket_LMP_domain') . '</th>'.
                            '<td>' . BeRocket_wizard_generate_autoselectors(array('products' => '.lmp_button_selector_products', 'product' => '.lmp_button_selector_item', 'pagination' => '.lmp_button_selector_pagination', 'next_page' => '.lmp_button_selector_next_page', 'prev_page' => '.lmp_button_selector_prev_page')) . '</td>'.
                  '</tr><tr>'.
                            '<th class="row"></th>
                            <td>' . __('Please do not use it on live sites. If something went wrong write us.', 'BeRocket_LMP_domain') . '</td>'.
                  '</tr><tr>';
        return $html;
    }
    /*
     *  SECTIONS END
     */ 
    public function save_br_selectors() {
        if( current_user_can( 'manage_options' ) ) {
			$products = @$_POST['products'];
			$product = @$_POST['product'];
			$pagination = @$_POST['pagination'];
			$next = @$_POST['next'];
			$next = $pagination.' '.$next;
            $options = parent::get_option();
            $options['br_lmp_selectors_settings'] = array(
                    'products'              => $products,
                    'item'                  => $product,
                    'pagination'            => $pagination,
                    'next_page'             => $next,
            );
            update_option( $this->values['settings_name'] , $options );
			echo admin_url( 'admin.php?page=' . $this->values['option_page'] . '&tab=selectors' );
			wp_die();
        }
	}
}new BeRocket_LMP;
