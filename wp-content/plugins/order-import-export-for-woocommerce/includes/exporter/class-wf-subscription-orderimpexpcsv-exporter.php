<?php

if (!defined('ABSPATH')) {
    exit;
}

class wf_subcription_orderImpExpCsv_Exporter {

    /**
     * Order Exporter Tool
     */
    public static function do_export($post_type = 'shop_subscription', $order_IDS = array()) {
        global $wpdb;

        if (!class_exists('WooCommerce')) :
            require ABSPATH . 'wp-content/plugins/woocommerce/woocommerce.php';
            require ABSPATH . 'wp-content/plugins/woocommerce/includes/class-wc-order-factory.php';
            WC()->init();
        endif;
        

        $export_limit = !empty($_POST['limit']) ? intval($_POST['limit']) : 999999999;
        $export_count = 0;
        $limit = 100;
        $export_offset = !empty($_POST['offset']) ? intval($_POST['offset']) : 0;
        $csv_columns = include( 'data/data-wf-post-subscription-columns.php' );

        $user_columns_name = !empty($_POST['columns_name']) ? $_POST['columns_name'] : $csv_columns;
        $export_columns = !empty($_POST['columns']) ? $_POST['columns'] : array();


        $end_date = empty($_POST['end_date']) ? date('Y-m-d 23:59', current_time('timestamp')) : $_POST['end_date'] . ' 23:59:59.99';
        $start_date = empty($_POST['start_date']) ? date('Y-m-d 00:00', 0) : $_POST['start_date'];
        $delimiter = !empty($_POST['delimiter']) ? $_POST['delimiter'] : ',';


        if ($limit > $export_limit)
            $limit = $export_limit;

        $settings = get_option('woocommerce_' . wf_all_imp_exp_ID . '_settings', null);
        $ftp_server = isset($settings['sbc_ftp_server']) ? $settings['sbc_ftp_server'] : '';
        $ftp_user = isset($settings['sbc_ftp_user']) ? $settings['sbc_ftp_user'] : '';
        $ftp_password = isset($settings['sbc_ftp_password']) ? $settings['sbc_ftp_password'] : '';
        $use_ftps = isset($settings['sbc_use_ftps']) ? $settings['sbc_use_ftps'] : '';
        $use_pasv = isset($settings['sbc_use_pasv']) ? $settings['sbc_use_pasv'] : '';
        $enable_ftp_ie = isset($settings['sbc_enable_ftp_ie']) ? $settings['sbc_enable_ftp_ie'] : '';

        $wpdb->hide_errors();
        @set_time_limit(0);
        if (function_exists('apache_setenv'))
            @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 0);
        @ob_clean();

        if ($enable_ftp_ie) {
            $file = $post_type . "-export-" . date('Y_m_d_H_i_s', current_time('timestamp')) . ".csv";
            $fp = fopen($file, 'w');
        } else {
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename=woocommerce-subscription-order-export-' . date('Y_m_d_H_i_s', current_time('timestamp')) . '.csv');
            header('Pragma: no-cache');
            header('Expires: 0');

            $fp = fopen('php://output', 'w');
        }


        // Headers


        if (empty($order_IDS)) {
            $query_args = array(
                'fields' => 'ids',
                'post_type' => 'shop_subscription',
                'posts_per_page' => $export_limit,
                'post_status' => 'any',
                'offset' => $export_offset,
                'date_query' => array(
                    array(
                        'before' => $end_date,
                        'after' => $start_date,
                        'inclusive' => true,
                    ),
                ),
            );
            if (!empty($_POST['order_status'])) {
                $statuses = $_POST['order_status'];
                if (!empty($statuses) && is_array($statuses)) {
                    $query_args['post_status'] = implode(',', $statuses);
                    if (!in_array($query_args['post_status'], array('any', 'trash'))) {
                        $query_args['post_status'] = wf_subcription_orderImpExpCsv_Exporter::hf_sanitize_subscription_status_keys($query_args['post_status']);
                    }
                }
            }


            $query_args = apply_filters('woocommerce_get_subscriptions_query_args', $query_args);
            $subscription_post_ids = get_posts($query_args);
            
            $subscriptions = array();
            foreach ($subscription_post_ids as $post_id) {
                $subscriptions[$post_id] = wf_subcription_orderImpExpCsv_Exporter::hf_get_subscription($post_id);
            }
            $subscriptions = apply_filters('hf_retrieved_subscriptions', $subscriptions);
        } else {
            foreach ($order_IDS as $post_id) {
                $subscriptions[$post_id] = wf_subcription_orderImpExpCsv_Exporter::hf_get_subscription($post_id);
            }
            $subscriptions = apply_filters('hf_retrieved_subscriptions', $subscriptions);
        }
        //echo '<pre>';print_r($subscriptions);exit;
        // Variable to hold the CSV data we're exporting
        $row = array();

        // Export header rows
        foreach ($csv_columns as $column => $value) {

            $temp_head = esc_attr($user_columns_name[$column]);
            if (!$export_columns || in_array($column, $export_columns))
                $row[] = $temp_head;
        }

        //Alter CSV Header 
        $row = apply_filters('hf_alter_subscription_csv_header_columns' , $row); 
        $row = array_map('wf_subcription_orderImpExpCsv_Exporter::wrap_column', $row);
        fwrite($fp, implode($delimiter, $row) . "\n");
        unset($row);
        
         // Loop orders
        foreach ($subscriptions as $order_id) {
            
            $data = wf_subcription_orderImpExpCsv_Exporter::get_subscriptions_csv_row($order_id, $csv_columns);
            // Add to csv
            $row = array_map('wf_subcription_orderImpExpCsv_Exporter::wrap_column', $data);
            fwrite($fp, implode($delimiter, $row) . "\n");
            unset($row);
            unset($data);
        }

        if ($enable_ftp_ie) {
            if ($use_ftps) {
                $ftp_conn = ftp_ssl_connect($ftp_server) or die("Could not connect to $ftp_server");
            } else {
                $ftp_conn = ftp_connect($ftp_server) or die("Could not connect to $ftp_server");
            }
            $login = ftp_login($ftp_conn, $ftp_user, $ftp_password);

            if($use_pasv) ftp_pasv($ftp_conn, TRUE);
            // upload file
            if (ftp_put($ftp_conn, $file, $file, FTP_ASCII)) {
                $wf_subcription_order_ie_msg = 1;
                wp_redirect(admin_url('/admin.php?page=wf_woocommerce_subscription_order_im_ex&wf_subcription_order_ie_msg=' . $wf_subcription_order_ie_msg));
            } else {
                $wf_subcription_order_ie_msg = 2;
                wp_redirect(admin_url('/admin.php?page=wf_woocommerce_subscription_order_im_ex&wf_subcription_order_ie_msg=' . $wf_subcription_order_ie_msg));
            }

            // close connection
            ftp_close($ftp_conn);
        }

        fclose($fp);
        exit;
    }

    public static function format_data($data) {
        //if (!is_array($data));
        //$data = (string) urldecode($data);
        $enc = mb_detect_encoding($data, 'UTF-8, ISO-8859-1', true);
        $data = ( $enc == 'UTF-8' ) ? $data : utf8_encode($data);
        return $data;
    }

    /**
     * Wrap a column in quotes for the CSV
     * @param  string data to wrap
     * @return string wrapped data
     */
    public static function wrap_column($data) {
        return '"' . str_replace('"', '""', $data) . '"';
    }

    public static function hf_sanitize_subscription_status_keys($status_key) {
        if (!is_string($status_key) || empty($status_key)) {
            return '';
        }
        $status_key = ( 'wc-' === substr($status_key, 0, 3) ) ? $status_key : sprintf('wc-%s', $status_key);
        return $status_key;
    }

    public static function hf_get_subscription($subscription) {

        if (is_object($subscription) && self::hf_is_subscription($subscription)) {
            $subscription = $subscription->id;
        }
       
        if (!class_exists('WC_Subscription')):
            require ABSPATH . 'wp-content/plugins/woocommerce-subscriptions/wcs-functions.php';
            require ABSPATH . 'wp-content/plugins/woocommerce-subscriptions/includes/class-wc-subscription.php';
        endif;
        
        $subscription = new WC_Subscription($subscription);
        
        //$subscription = WC()->order_factory->get_order($subscription);
        //print_r($subscription);exit;
        
        if (!self::hf_is_subscription($subscription)) {
            $subscription = false;
        }
        return apply_filters('hf_get_subscription', $subscription);
    }

    public static function hf_is_subscription($subscription) {

        if (is_object($subscription) && is_a($subscription, 'WC_Subscription')) {
            $is_subscription = true;
        } elseif (is_numeric($subscription) && 'shop_subscription' == get_post_type($subscription)) {
            $is_subscription = true;
        } else {
            $is_subscription = false;
        }

        return apply_filters('hf_is_subscription', $is_subscription, $subscription);
    }

    /*
     * Takes the subscription and builds the CSV row based on the headers which have been set by user, 
     * return ready to write row.
     * @param WC_Subscription $subscription
     * @param $csv_columns array selected of columns to export
     */

    public static function get_subscriptions_csv_row($subscription, $csv_columns) {
       
        $fee_total = $fee_tax_total = 0;
        $fee_items = array();

        if (0 != sizeof(array_intersect(array_keys($csv_columns), array('fee_total', 'fee_tax_total', 'fee_items')))) {
            foreach ($subscription->get_fees() as $fee_id => $fee) {

                $fee_items[] = implode('|', array(
                    'name:' . $fee['name'],
                    'total:' . wc_format_decimal($fee['line_total'], 2),
                    'tax:' . wc_format_decimal($fee['line_tax'], 2),
                    'tax_class:' . $fee['tax_class'],
                ));

                $fee_total += $fee['line_total'];
                $fee_tax_total += $fee['line_tax'];
            }
        }

        if (isset($csv_columns['payment_method_post_meta']) || isset($csv_columns['payment_method_user_meta'])) {
            $payment_method_table = apply_filters('woocommerce_subscription_payment_meta', array(), $subscription);

            if (is_array($payment_method_table) && !empty($payment_method_table[$subscription->payment_method])) {
                $post_meta = $user_meta = array();

                foreach ($payment_method_table[$subscription->payment_method] as $meta_table => $meta) {
                    foreach ($meta as $meta_key => $meta_data) {
                        switch ($meta_table) {
                            case 'post_meta':
                            case 'postmeta':
                                $post_meta[] = $meta_key . ':' . $meta_data['value'];
                                break;
                            case 'usermeta':
                            case 'user_meta':
                                $user_meta[] = $meta_key . ':' . $meta_data['value'];
                                break;
                        }
                    }
                }

                $payment_post_meta = implode('|', $post_meta);
                $payment_user_meta = implode('|', $user_meta);
            }
        }
        
        if (!function_exists('get_user_by')) {
            require ABSPATH . 'wp-includes/pluggable.php';
        }

        $user_values = get_user_by('ID',  $subscription->customer_user);
        
        foreach ($csv_columns as $header_key => $_) {
            switch ($header_key) {
                case 'subscription_id':
                    $value = $subscription->id;
                    break;
                case 'subscription_status':
                    $value = $subscription->post_status;
                    break;
                case 'customer_id':
                    $value = $subscription->customer_user;
                    break;
                case 'customer_username':
                    $value = $user_values->user_login;
                    break;
                case 'customer_email':
                    $value = $user_values->user_email;
                    break;
                case 'fee_total':
                case 'fee_tax_total':
                    $value = ${$header_key};
                    break;
                case 'order_shipping':
                case 'order_shipping_tax':
                case 'order_tax':
                case 'cart_discount':
                case 'cart_discount_tax':
                case 'order_total':
                    $value = empty($subscription->{$header_key}) ? 0 : $subscription->{$header_key};
                    break;
                case 'billing_period':
                case 'billing_interval':
                case 'start_date':
                case 'trial_end_date':
                case 'next_payment_date':
                case 'last_payment_date':
                case 'end_date':
                case 'payment_method':
                case 'payment_method_title':
                case 'billing_first_name':
                case 'billing_last_name':
                case 'billing_email':
                case 'billing_phone':
                case 'billing_address_1':
                case 'billing_address_2':
                case 'billing_postcode':
                case 'billing_city':
                case 'billing_state':
                case 'billing_country':
                case 'billing_company':
                case 'shipping_first_name':
                case 'shipping_last_name':
                case 'shipping_address_1':
                case 'shipping_address_2':
                case 'shipping_postcode':
                case 'shipping_city':
                case 'shipping_state':
                case 'shipping_country':
                case 'shipping_company':
                case 'customer_note':
                case 'order_currency':
                    $value = $subscription->{$header_key};
                    break;
                case 'post_parent':
                    if(!empty($subscription->order)) $value = $subscription->order->id; else $value = 0;
                    break;
                case 'payment_method_post_meta':
                    $value = (!empty($payment_post_meta) ) ? $payment_post_meta : '';
                    break;
                case 'payment_method_user_meta':
                    $value = (!empty($payment_user_meta) ) ? $payment_user_meta : '';
                    break;
                case 'order_notes':
                    remove_filter('comments_clauses', array('WC_Comments', 'exclude_order_comments'));
                    $notes = get_comments(array('post_id' => $subscription->id, 'approve' => 'approve', 'type' => 'order_note'));
                    add_filter('comments_clauses', array('WC_Comments', 'exclude_order_comments'));

                    $order_notes = array();

                    foreach ($notes as $note) {
                        $order_notes[] = str_replace(array("\r", "\n"), ' ', $note->comment_content);
                    }

                    if (!empty($order_notes)) {
                        $value = implode(';', $order_notes);
                    } else {
                        $value = '';
                    }

                    break;
                case 'order_items':
                    $value = '';
                    $line_items = array();

                    foreach ($subscription->get_items() as $item_id => $item) {

                        $meta_string = '';

                        foreach ($subscription->has_meta($item_id) as $meta_id => $meta_data) {

                            // Skip hidden core fields
                            if (in_array($meta_data['meta_key'], apply_filters('woocommerce_hidden_order_itemmeta', array(
                                        '_qty',
                                        '_tax_class',
                                        '_product_id',
                                        '_variation_id',
                                        '_line_subtotal',
                                        '_line_subtotal_tax',
                                        '_line_total',
                                        '_line_tax',
                                        '_line_tax_data',
                                    )))) {
                                continue;
                            }

                            // add a custom delimeter to separate meta
                            if (!empty($meta_string)) {
                                $meta_string .= '+';
                            }

                            $meta_string .= sprintf('%s=%s', $meta_data['meta_key'], $meta_data['meta_value']);
                        }

                        $line_item = array(
                            'product_id' => self::hf_get_canonical_product_id($item),
                            'name' => html_entity_decode($item['name'], ENT_NOQUOTES, 'UTF-8'),
                            'quantity' => $item['qty'],
                            'total' => wc_format_decimal($subscription->get_line_total($item), 2),
                            'meta' => html_entity_decode(str_replace(array("\r", "\r\n", "\n", ': ', ':', ';', '|'), '', $meta_string), ENT_NOQUOTES, 'UTF-8'),
                        );

                        // add line item tax
                        $line_tax_data = isset($item['line_tax_data']) ? $item['line_tax_data'] : array();
                        $tax_data = maybe_unserialize($line_tax_data);
                        $line_item['tax'] = isset($tax_data['total']) ? wc_format_decimal(wc_round_tax_total(array_sum((array) $tax_data['total'])), 2) : '';

                        foreach ($line_item as $name => $value) {
                            $line_item[$name] = $name . ':' . $value;
                        }
                        $line_item = implode('|', $line_item);

                        if ($line_item) {
                            $line_items[] = $line_item;
                        }
                    }

                    if (!empty($line_items)) {
                        $value = implode(';', $line_items);
                    }
                    break;

                case 'coupon_items':
                    $coupon_items = array();

                    foreach ($subscription->get_items('coupon') as $_ => $coupon_item) {

                        $coupon = new WC_Coupon($coupon_item['name']);

                        $coupon_post = get_post($coupon->id);

                        $coupon_items[] = implode('|', array(
                            'code:' . $coupon_item['name'],
                            'description:' . ( is_object($coupon_post) ? $coupon_post->post_excerpt : '' ),
                            'amount:' . wc_format_decimal($coupon_item['discount_amount'], 2),
                                )
                        );
                    }

                    if (!empty($coupon_items)) {
                        $value = implode(';', $coupon_items);
                    } else {
                        $value = '';
                    }

                    break;
                case 'download_permissions':
                    $value = $subscription->download_permissions_granted ? $subscription->download_permissions_granted : 0;
                    break;
                case 'shipping_method':
                    $shipping_lines = array();

                    foreach ($subscription->get_shipping_methods() as $shipping_item_id => $shipping_item) {
                        $shipping_lines[] = implode('|', array(
                            'method_id:' . $shipping_item['method_id'],
                            'method_title:' . $shipping_item['name'],
                            'total:' . wc_format_decimal($shipping_item['cost'], 2),
                                )
                        );
                    }

                    if (!empty($shipping_lines)) {
                        $value = implode(';', $shipping_lines);
                    } else {
                        $value = '';
                    }

                    break;
                case 'fee_items':
                    $value = implode(';', $fee_items);
                    break;
                case 'tax_items':
                    $tax_items = array();

                    foreach ($subscription->get_tax_totals() as $tax_code => $tax) {
                        $tax_items[] = implode('|', array(
                            'id:' . $tax->rate_id,
                            'code:' . $tax->label,
                            'total:' . wc_format_decimal($tax->amount, 2),
                        ));
                    }

                    if (!empty($tax_items)) {
                        $value = implode(';', $tax_items);
                    } else {
                        $value = '';
                    }
                    break;
                default :
                    $value = '';
            }

            $csv_row[$header_key] = $value;
        }

        $data = array();

        foreach ($csv_columns as $header_key => $_) {

            if (!isset($csv_row[$header_key])) {
                $csv_row[$header_key] = '';
            }

            // Strict string comparison, as values like '0' are valid
            $value = ( '' !== $csv_row[$header_key] ) ? $csv_row[$header_key] : '';
            $data[] = $value;
        }
        return apply_filters('hf_alter_subscription_data', $data);
    }

    public static function hf_get_canonical_product_id($item) {
        return (!empty($item['variation_id']) ) ? $item['variation_id'] : $item['product_id'];
    }

}
