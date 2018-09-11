<?php
define( "BeRocket_sales_report_domain", 'BeRocket_sales_report_domain'); 
define( "sales_report_TEMPLATE_PATH", plugin_dir_path( __FILE__ ) . "templates/" );
load_plugin_textdomain('BeRocket_sales_report_domain', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
require_once(plugin_dir_path( __FILE__ ).'berocket/framework.php');
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

class BeRocket_sales_report extends BeRocket_Framework {
    public static $settings_name = 'br-sales_report-options';
    public $info, $defaults, $values, $notice_array, $conditions;
    protected static $instance;
    function __construct () {
        $this->info = array(
            'id'          => 17,
            'version'     => BeRocket_sales_report_version,
            'plugin'      => '',
            'slug'        => '',
            'key'         => '',
            'name'        => '',
            'plugin_name' => 'sales_report',
            'full_name'   => 'WooCommerce Sales Report',
            'norm_name'   => 'Sales Report',
            'price'       => '',
            'domain'      => 'BeRocket_sales_report_domain',
            'templates'   => sales_report_TEMPLATE_PATH,
            'plugin_file' => BeRocket_sales_report_file,
            'plugin_dir'  => __DIR__,
        );
        $this->defaults = array(
            'time'              => '03:00',
            'wptime'            => '',
            'email'             => '',
            'interval'          => array(),
            'status'            => '',
            'daily_date'        => '',
            'send_empty'        => '',
            'starttime'         => '',
            'sort_product'      => '',
            'custom_css'        => '',
            'plugin_key'        => '',
        );
        $this->values = array(
            'settings_name' => 'br-sales_report-options',
            'option_page'   => 'br-sales_report',
            'premium_slug'  => 'woocommerce-sales-report-email',
            'free_slug'     => 'sales-report-for-woocommerce',
        );
        $this->feature_list = array();
        parent::__construct( $this );

        if ( $this->init_validation() ) {
            add_shortcode( 'br_sales_report_part', array( $this, 'shortcode' ) );
        }
    }
    function new_version_changes() {
        $version = get_option('br-sales_report-version');
        if( empty($version) ) {
            wp_clear_scheduled_hook( 'berocket_get_orders' );
            $options = $this->get_option();
            $BeRocket_sales_report_custom_post = BeRocket_sales_report_custom_post::getInstance();
            if( empty($options['time']) ) {
                $send_time = array('0', '0');
            } else {
                $send_time = explode(':', $options['time']);
            }
            if( empty($options['starttime']) ) {
                $start_end_time = array('0', '0');
            } else {
                $start_end_time = $send_time;
            }
            $week_send_wait = 8 - date('N');
            $month_send_wait = (date('t') - date('j')) + 1;
            if( empty($options['status']) ) {
                $status_set = array(
                    '0' => 'pending',
                    '1' => 'processing',
                    '2' => 'on-hold',
                    '3' => 'completed',
                    '4' => 'cancelled',
                    '5' => 'refunded',
                    '6' => 'failed'
                );
            } else {
                $status_set = array();
                switch($options['status']) {
                    case 'pending':
                        $status_set['0'] = 'pending';
                    case 'processing':
                        $status_set['1'] = 'processing';
                    case 'completed':
                        $status_set['2'] = 'completed';
                }
            }
            if( isset($options['emails']) && is_array($options['emails']) && count(isset($options['emails'])) > 0 ) {
                foreach($options['emails'] as $email) {
                    if( ! empty($email['email']) && isset($email['interval']) && is_array($email['interval']) ) {
                        if( in_array('day', $email['interval'])
                        && isset($email['day']['blocks']) && is_array($email['day']['blocks']) && count($email['day']['blocks']) > 0 ) {
                            $send_options = array(
                                'send_time'     => array('hours' => $send_time[0], 'minutes' => $send_time[1]),
                                'start_time'    => array('hours' => $start_end_time[0], 'minutes' => $start_end_time[1]),
                                'end_time'      => array('hours' => $start_end_time[0], 'minutes' => $start_end_time[1]),
                                'emails'        => $email['email'],
                                'status'        => $status_set,
                                'send_empty'    => @$options['send_empty'],
                            );
                            $content = '[br_sales_report_part content="header"]';
                            foreach($email['day']['blocks'] as $block) {
                                $content .= '[br_sales_report_part content="'.$block.'"]';
                            }
                            $BeRocket_sales_report_custom_post->create_new_post(array(
                                'post_title'    => 'Daily for: '.$email['email'],
                                'post_content'  => $content
                            ), $send_options);
                        }
                        if( in_array('week', $email['interval'])
                        && isset($email['week']['blocks']) && is_array($email['week']['blocks']) && count($email['week']['blocks']) > 0 ) {
                            $send_options = array(
                                'send_time'         => array('hours' => $send_time[0], 'minutes' => $send_time[1]),
                                'start_time'        => array('hours' => $start_end_time[0], 'minutes' => $start_end_time[1]),
                                'end_time'          => array('hours' => $start_end_time[0], 'minutes' => $start_end_time[1]),
                                'emails'            => $email['email'],
                                'send_wait'         => $week_send_wait,
                                'periodicity'       => 7,
                                'start_date_type'   => 'prev_week',
                                'end_date_type'     => 'week',
                                'status'            => $status_set,
                                'send_empty'        => @$options['send_empty'],
                            );
                            $content = '[br_sales_report_part content="header"]';
                            foreach($email['week']['blocks'] as $block) {
                                $content .= '[br_sales_report_part content="'.$block.'"]';
                            }
                            $BeRocket_sales_report_custom_post->create_new_post(array(
                                'post_title'    => 'Weekly for: '.$email['email'],
                                'post_content'  => $content
                            ), $send_options);
                        }
                        if( in_array('week', $email['interval'])
                        && isset($email['month']['blocks']) && is_array($email['month']['blocks']) && count($email['month']['blocks']) > 0 ) {
                            $send_options = array(
                                'send_time'         => array('hours' => $send_time[0], 'minutes' => $send_time[1]),
                                'start_time'        => array('hours' => $start_end_time[0], 'minutes' => $start_end_time[1]),
                                'end_time'          => array('hours' => $start_end_time[0], 'minutes' => $start_end_time[1]),
                                'emails'            => $email['email'],
                                'send_wait'         => $month_send_wait,
                                'periodicity_type'  => 'month',
                                'periodicity'       => 1,
                                'start_date_type'   => 'prev_month',
                                'end_date_type'     => 'month',
                                'status'            => $status_set,
                                'send_empty'        => @$options['send_empty'],
                            );
                            $content = '[br_sales_report_part content="header"]';
                            foreach($email['month']['blocks'] as $block) {
                                $content .= '[br_sales_report_part content="'.$block.'"]';
                            }
                            $BeRocket_sales_report_custom_post->create_new_post(array(
                                'post_title'    => 'Monthly for: '.$email['email'],
                                'post_content'  => $content
                            ), $send_options);
                        }
                    }
                }
            }
            update_option('br-sales_report-version', $this->info['version']);
        }
    }
    function init_validation() {
        return ( ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) && 
            br_get_woocommerce_version() >= 2.1 );
    }
    public function init () {
        parent::init();
        $this->new_version_changes();
    }
    public function shortcode($atts = array()) {
        $default_atts = array(
            'content' => 'sales',
            'extend'  => ''
        );
        $html = '';
        global $br_current_notice_post;
        if( ! empty($br_current_notice_post) && ! empty($br_current_notice_post['date_data']) && ! empty($br_current_notice_post['date_string']) ) {
            $atts = array_merge($default_atts, $atts);
            if( in_array($atts['content'], array('sales', 'order_count', 'products', 'header')) ) {
                $date_data = $br_current_notice_post['date_data'];
                $date_string = $br_current_notice_post['date_string'];
                if( $atts['content'] == 'header' ) {
                    $html_data = array( 'blocks' => array('show_header') );
                } else {
                    $html_data = array( 'blocks' => array('hide_header', $atts['content'] ) );
                }
                $html_data['status'] = $br_current_notice_post['status'];
                $html_data['extend']  = explode( ',', $atts['extend'] );
                if( ! empty($atts['sort']) ) {
                    $is_asc = ( $atts['sort'] == 'name_asc' || $atts['sort'] == 'qty_asc' );
                    $is_name = ( $atts['sort'] == 'name_asc' || $atts['sort'] == 'name_desc' );
                    $sort_array = array();
                    foreach($ready_products as $product_id => $product_info) {
                        if( $is_name ) {
                            $sort_array[$product_id] = $product_info['name'];
                        } else {
                            $sort_array[$product_id] = $product_info['quantity'];
                        }
                    }
                    $html_data['sort_product'] = array('is_asc' => $is_asc, 'is_name' => $is_name);
                }
                $html = $this->get_html_order($html_data, $date_data, $date_string);
            } else {
                $html = apply_filters('br_sales_report_part_'.$atts['content'], '', $br_current_notice_post, $atts);
            }
        }
        return $html;
    }
    public function admin_settings( $tabs_info = array(), $data = array() ) {
        $time_array = array();
        for($i = 0; $i < 24; $i++) {
            for($j = 0; $j < 12; $j++) {
                $i_text = sprintf("%02d", $i);
                $j_text = sprintf("%02d", ($j * 5));
                $time_array[] = array('value' => $i_text . ':' . $j_text, 'text' => $i_text . ':' . $j_text);
            }
        }
        parent::admin_settings(
            array(
                'General' => array(
                    'icon' => 'cog',
                ),
                'Report Variant' => array(
                    'icon' => 'plus-square',
                    'link' => admin_url( 'edit.php?post_type=br_sale_report' ),
                ),
                'Custom CSS' => array(
                    'icon' => 'css3'
                ),
                'License' => array(
                    'icon' => 'unlock-alt',
                    'link' => admin_url( 'admin.php?page=berocket_account' )
                ),
            ),
            array(
            'General' => array(
                /*'emails_settings' => array(
                    'section'   => 'emails_settings',
                    "value"     => "1",
                ),
                'status' => array(
                    "label"     => __( "Status", 'BeRocket_sales_report_domain' ),
                    "name"     => 'status',   
                    "type"     => "selectbox",
                    "options"  => array(
                        array('value' => '', 'text' => __('All', 'BeRocket_sales_report_domain')),
                        array('value' => 'pending', 'text' => __('Pending, Processing, Completed', 'BeRocket_sales_report_domain')),
                        array('value' => 'processing', 'text' => __('Processing, Completed', 'BeRocket_sales_report_domain')),
                        array('value' => 'completed', 'text' => __('Completed', 'BeRocket_sales_report_domain')),
                    ),
                    "value"    => '',
                ),
                'daily_date' => array(
                    "label"     => __( "Daily message date", 'BeRocket_sales_report_domain' ),
                    "name"     => 'daily_date',   
                    "type"     => "selectbox",
                    "options"  => array(
                        array('value' => '', 'text' => __('Statistic start day', 'BeRocket_sales_report_domain')),
                        array('value' => 'send', 'text' => __('Send day', 'BeRocket_sales_report_domain')),
                        array('value' => 'fromto', 'text' => __('From start to send day', 'BeRocket_sales_report_domain')),
                        array('value' => 'fromto_time', 'text' => __('From start to send day with time', 'BeRocket_sales_report_domain')),
                    ),
                    "value"    => '',
                    "label_for"=> __('Date that will be displayed in message for daily report', 'BeRocket_sales_report_domain'),
                ),
                'time' => array(
                    "label"     => __( "Time", 'BeRocket_sales_report_domain' ),
                    "name"     => 'time',   
                    "type"     => "selectbox",
                    "options"  => $time_array,
                    "value"    => '',
                ),
                'starttime' => array(
                    "label"     => __('Start time', 'BeRocket_sales_report_domain'),
                    "type"      => "checkbox",
                    "name"      => "starttime",
                    "value"     => "1",
                    "label_for" => __('Use time as start and end of the day', 'BeRocket_sales_report_domain')
                ),
                'wptime' => array(
                    "label"     => __('WordPress Time', 'BeRocket_sales_report_domain'),
                    "type"      => "checkbox",
                    "name"      => "wptime",
                    "value"     => "1",
                    "class"     => "br_use_wptime",
                    "label_for" => __('Use WordPress time instead UTC', 'BeRocket_sales_report_domain')
                ),
                'current_times' => array(
                    'section'   => 'current_times',
                    "value"     => "1",
                ),
                'send_empty' => array(
                    "label"     => __('Send empty report', 'BeRocket_sales_report_domain'),
                    "type"      => "checkbox",
                    "name"      => "send_empty",
                    "value"     => "1",
                    "label_for" => __('Send reports without orders', 'BeRocket_sales_report_domain')
                ),*/
                'subject' => array(
                    "type"     => "text",
                    "label"    => __('Subject', 'BeRocket_sales_report_domain'),
                    "label_for"=> "Default: <strong>Your reports for WooCommerce ( From: {dtf:Y-m-d} To: {dtt:Y-m-d} )</strong>",
                    "name"     => "subject",
                    "extra"    => 'placeholder="Your reports for WooCommerce ( From: {dtf:Y-m-d} To: {dtt:Y-m-d} )"',
                    "value"    => '',
                ),
                'emails' => array(
                    "type"     => "text",
                    "label"    => __('Emails', 'BeRocket_sales_report_domain'),
                    "label_for"=> __('Use comma to separate emails', 'BeRocket_sales_report_domain'),
                    "name"     => "email",
                    "value"    => '',
                ),
            ),
            'Custom CSS' => array(
                array(
                    "label"   => "Custom CSS",
                    "name"    => "custom_css",
                    "type"    => "textarea",
                    "value"   => "",
                ),
            ),
        ) );
    }
    public function get_wordpress_timezone() {
        $timezone_string = get_option('timezone_string');
        if( empty($timezone_string) ) {
            $gmt_offset = get_option('gmt_offset');
            if( empty($gmt_offset) ) {
                $timezone_string = 'UTC';
            } else {
                $timezone_string = sprintf("%+03d",$gmt_offset).($gmt_offset != intval($gmt_offset) ? '30' : '00');
            }
        }
        return $timezone_string;
    }
    public function time_to_wordpress($time_string) {
        $timezone_string = $this->get_wordpress_timezone();
        $time = new DateTime($time_string, new DateTimeZone('UTC'));
        $time->setTimeZone(new DateTimeZone($timezone_string));
        return $time;
    }
    public function time_to_php($time_string) {
        $timezone_string = $this->get_wordpress_timezone();
        $time = new DateTime($time_string, new DateTimeZone($timezone_string));
        $time->setTimeZone(new DateTimeZone('UTC'));
        return $time;
    }
    public function get_correct_time($time_string, $wptime) {
        if( empty($wptime) ) {
            $time = new DateTime($time_string);
        } else {
            $timezone_string = $this->get_wordpress_timezone();
            $time = new DateTime($time_string, new DateTimeZone($timezone_string));
            $time->setTimeZone(new DateTimeZone('UTC'));
        }
        return $time;
    }
    public function get_html_head($date_string) {
        set_query_var( 'date_string', $date_string );
        ob_start ();
        $this->br_get_template_part( 'email_head' );
        $html = ob_get_clean ();
        return $html;
    }
    public function get_html_foot() {
        ob_start ();
        $this->br_get_template_part( 'email_foot' );
        $html = ob_get_clean ();
        return $html;
    }
    public function get_order_ids($html_data, $date) {
        $options = $this->get_option();
        if( ! empty($html_data['status']) ) {
            $status = $html_data['status'];
        } else {
            if( empty($options['status']) ) {
                $status = array(
                    'pending','processing','on-hold','completed','cancelled','refunded','failed',
                    'wc-pending','wc-processing','wc-on-hold','wc-completed','wc-cancelled','wc-refunded','wc-failed'
                );
            } else {
                $status = array('completed', 'wc-completed');
                if( in_array($options['status'], array('processing', 'pending')) ) {
                    $status[] = 'processing';
                    $status[] = 'wc-processing';
                }
                if( in_array($options['status'], array('pending')) ) {
                    $status[] = 'pending';
                    $status[] = 'wc-pending';
                }
            }
        }
        $args = array(
            'date_query' => $date,
            'post_type' => 'shop_order',
            'post_status' =>  $status,
            'posts_per_page' => '-1'
        );
        $query = new WP_Query( $args );
        $orders = $query->posts;
        return $orders;
    }
    public function get_html_order($html_data, $date, $date_string) {
        $options = $this->get_option();
        $orders = $this->get_order_ids($html_data, $date);
        $ready_products = array();
        $total_price = 0;
        $order_count = 0;
        foreach($orders as $order) {
            $order_count++;
            $wc_order = new WC_Order($order->ID);
            $total_price += $wc_order->get_total();
            $products = $wc_order->get_items();
            foreach($products as $product) {
                if( isset($ready_products[$product['product_id']]) ) {
                    $ready_products[$product['product_id']]['quantity'] += $product['qty'];
                } else {
                    $ready_products[$product['product_id']] = array('name' => $product['name'], 'quantity' => $product['qty']);
                    if ( is_array( $html_data['extend'] ) and in_array( 'sku', $html_data['extend'] ) or $html_data['extend'] == 'sku' ) {
                        $ready_products[$product['product_id']]['sku'] = get_post_meta( $product['product_id'], '_sku', true );
                    }
                }
            }
        }
        if( ! empty($html_data['sort_product']) ) {
            $sort_array = array();
            foreach($ready_products as $ready_product) {
                if( $html_data['sort_product']['is_name'] ) {
                    $sort_array[] = $ready_product['name'];
                } else {
                    $sort_array[] = $ready_product['quantity'];
                }
            }
            array_multisort($sort_array, ($html_data['sort_product']['is_asc'] ? SORT_ASC : SORT_DESC), ($html_data['sort_product']['is_name'] ? SORT_REGULAR : SORT_NUMERIC), $ready_products);
        }
        if( $order_count > 0 && count($ready_products) > 0 ) {
            set_query_var( 'total_price', $total_price );
            set_query_var( 'order_count', $order_count );
            set_query_var( 'ready_products', $ready_products );
            set_query_var( 'date_string', $date_string );
            set_query_var( 'html_data', $html_data );
            ob_start ();
            $this->br_get_template_part( 'email' );
            $html = ob_get_clean ();
            return $html;
        } else {
            return FALSE;
        }
    }
    public function send_mail($email, $html, $date_string) {
        $options = $this->get_option();
        $header = $date_string;
        $wp_mail_headers = array('Content-Type: text/html; charset=UTF-8;');
        if( ! empty($email) ) {
            if( $html !== FALSE ) {
                add_filter('wp_mail_content_type', array( $this, 'wp_mail_content_type' ) );
                wp_mail ($email, $header, $html, $wp_mail_headers);
            } elseif( ! empty($options['send_empty']) ) {
                add_filter('wp_mail_content_type', array( $this, 'wp_mail_content_type' ) );
                $html = $this->get_html_head($date_string);
                ob_start ();
                $this->br_get_template_part( 'send_empty' );
                $html .= ob_get_clean ();
                $html .= $this->get_html_foot();
                wp_mail ($email, $header, $html, $wp_mail_headers);
            }
        }
    }
    public function wp_mail_content_type($content_type) {
        return 'text/html';
    }
    public function admin_init () {
        parent::admin_init();
        global $pagenow, $typenow, $post;
        if ( in_array($pagenow, array('post.php', 'post-new.php')) && ("br_sale_report" == $typenow || ( ! empty($_GET['post']) && "br_sale_report" == get_post_type($_GET['post']) ) ) ) {
            add_filter( 'mce_buttons', array($this, 'register_tinymce_button') );
            add_filter( 'mce_external_plugins', array($this, 'add_tinymce_button') );
        }
        wp_enqueue_script( 'berocket_sales_report_admin', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), BeRocket_sales_report_version );
        wp_register_style( 'berocket_sales_report_admin_style', plugins_url( 'css/admin.css', __FILE__ ), "", BeRocket_sales_report_version );
        wp_enqueue_style( 'berocket_sales_report_admin_style' );
    }
    public function register_tinymce_button( $buttons ) {
        array_push( $buttons, "berocket_sale_report" );
        return $buttons;
    }
    public function add_tinymce_button( $plugin_array ) {
        $plugin_array['berocket_sale_report'] = plugins_url( 'js/tiny_mce.js', __FILE__ ) ;
        return $plugin_array;
    }
    public function admin_menu() {
        if ( parent::admin_menu() ) {
            add_submenu_page(
                'woocommerce',
                __( $this->info[ 'norm_name' ]. ' Settings', $this->info[ 'domain' ] ),
                __( $this->info[ 'norm_name' ], $this->info[ 'domain' ] ),
                'manage_options',
                $this->values[ 'option_page' ],
                array(
                    $this,
                    'option_form'
                )
            );
        }
    }
}

new BeRocket_sales_report;
