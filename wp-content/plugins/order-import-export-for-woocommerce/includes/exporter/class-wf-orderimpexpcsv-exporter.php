<?php

if (!defined('ABSPATH')) {
    exit;
}

class WF_OrderImpExpCsv_Exporter {

    /**
     * Order Exporter Tool
     */
    public static function wf_order_xml_general_case_export_format($formated_orders, $raw_orders) {
        $order_details = array();


        foreach ($raw_orders as $order) {


            foreach ($order as $key => $value) {
                $order_data[$key] = trim($value, '"');
            }

            $order_details[] = $order_data;
        }
        $formated_orders = array('Orders' => array('Order' => $order_details));
        return apply_filters('hf_general_order_export', $formated_orders);
    }

    public static function do_export($post_type = 'shop_order', $order_IDS = array(), $xmldata = '0') {
        global $wpdb;
        ini_set('max_execution_time', -1);
        ini_set('memory_limit', -1);
        $export_limit = !empty($_POST['limit']) ? intval($_POST['limit']) : 999999999;
        $export_count = 0;
        $limit = 100;
        $export_offset = !empty($_POST['offset']) ? intval($_POST['offset']) : 0;
        $csv_columns = include( 'data/data-wf-post-columns.php' );

        $user_columns_name = !empty($_POST['columns_name']) ? $_POST['columns_name'] : $csv_columns;
        $export_columns = !empty($_POST['columns']) ? $_POST['columns'] : array();

        $export_order_statuses = !empty($_POST['order_status']) ? $_POST['order_status'] : 'any';
        $end_date = empty($_POST['end_date']) ? date('Y-m-d 23:59', current_time('timestamp')) : $_POST['end_date'] . ' 23:59:59.99';
        $start_date = empty($_POST['start_date']) ? date('Y-m-d 00:00', 0) : $_POST['start_date'];
        $delimiter = !empty($_POST['delimiter']) ? $_POST['delimiter'] : ',';


        if ($limit > $export_limit)
            $limit = $export_limit;

        $settings = get_option('woocommerce_' . wf_all_imp_exp_ID . '_settings', null);
        $ftp_server = isset($settings['ord_ftp_server']) ? $settings['ord_ftp_server'] : '';
        $ftp_user = isset($settings['ord_ftp_user']) ? $settings['ord_ftp_user'] : '';
        $ftp_password = isset($settings['ord_ftp_password']) ? $settings['ord_ftp_password'] : '';
        $use_ftps = isset($settings['ord_use_ftps']) ? $settings['ord_use_ftps'] : '';
        $use_pasv = isset($settings['ord_use_pasv']) ? $settings['ord_use_pasv'] : '';
        $enable_ftp_ie = isset($settings['ord_enable_ftp_ie']) ? $settings['ord_enable_ftp_ie'] : '';

        $wpdb->hide_errors();
        @set_time_limit(0);
        if (function_exists('apache_setenv'))
            @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 0);
        @ob_clean();

        if ($xmldata == '1') {
            if (empty($order_IDS)) {
                $query_args = array(
                    'fields' => 'ids',
                    'post_type' => 'shop_order',
                    'post_status' => $export_order_statuses,
                    'posts_per_page' => $export_limit,
                    'offset' => $export_offset,
                    'date_query' => array(
                        array(
                            'before' => $end_date,
                            'after' => $start_date,
                            'inclusive' => true,
                        ),
                    ),
                );

                $query = new WP_Query($query_args);
                $order_ids = $query->posts;
            } else {
                $order_ids = $order_IDS;
            }
            // Export header rows
            include_once( 'class-OrderImpExpXML-order-exp-xml-gen.php' );
            $export = new OrderImpExpXML_OrderExpXMLGeneral_map($order_ids);
            $order_details = $export->get_orders($order_ids);
            $data_array = array('Orders' => array('Order' => $order_details));
            $data_array = WF_OrderImpExpCsv_Exporter::wf_order_xml_general_case_export_format($data_array, $order_details);
            $filename = 'wc_order_xml';
            $export->do_xml_export($filename, $export->get_order_details_xml($data_array));
        } else {
            if ($enable_ftp_ie) {
                $file = $post_type . "-export-" . date('Y_m_d_H_i_s', current_time('timestamp')) . ".csv";
                $fp = fopen($file, 'w');
            } else {
                header('Content-Type: text/csv; charset=UTF-8');
                header('Content-Disposition: attachment; filename=woocommerce-order-export-' . date('Y_m_d_H_i_s', current_time('timestamp')) . '.csv');
                header('Pragma: no-cache');
                header('Expires: 0');

                $fp = fopen('php://output', 'w');
            }

            // Headers


            if (empty($order_IDS)) {
                $query_args = array(
                    'fields' => 'ids',
                    'post_type' => 'shop_order',
                    'post_status' => $export_order_statuses,
                    'posts_per_page' => $export_limit,
                    'offset' => $export_offset,
                    'date_query' => array(
                        array(
                            'before' => $end_date,
                            'after' => $start_date,
                            'inclusive' => true,
                        ),
                    ),
                );

                $query = new WP_Query($query_args);
                $order_ids = $query->posts;
            } else {
                $order_ids = $order_IDS;
            }

            // Variable to hold the CSV data we're exporting
            $row = array();




            // Export header rows
            foreach ($csv_columns as $column => $value) {

                $temp_head = esc_attr($user_columns_name[$column]);
                if (!$export_columns || in_array($column, $export_columns))
                    $row[] = $temp_head;
            }


            $max_line_items = WF_OrderImpExpCsv_Exporter::get_max_line_items($order_ids);
            for ($i = 1; $i <= $max_line_items; $i++) {
                $row[] = "line_item_{$i}";
            }

            $row = apply_filters('hf_alter_csv_header', $row); //Alter CSV Header

            $row = array_map('WF_OrderImpExpCsv_Exporter::wrap_column', $row);
            fwrite($fp, implode($delimiter, $row) . "\n");
            unset($row);
            // Loop orders
            foreach ($order_ids as $order_id) {
                //$row = array();   
                $data = WF_OrderImpExpCsv_Exporter::get_orders_csv_row($order_id, $export_columns, $max_line_items);
                // Add to csv
                $row = array_map('WF_OrderImpExpCsv_Exporter::wrap_column', $data);
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
                    $wf_order_ie_msg = 1;
                    wp_redirect(admin_url('/admin.php?page=wf_woocommerce_order_im_ex&wf_order_ie_msg=' . $wf_order_ie_msg));
                } else {
                    $wf_order_ie_msg = 2;
                    wp_redirect(admin_url('/admin.php?page=wf_woocommerce_order_im_ex&wf_order_ie_msg=' . $wf_order_ie_msg));
					}

                // close connection
                ftp_close($ftp_conn);
            }

            fclose($fp);
            exit;
        }
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

    public static function get_max_line_items($order_ids) {

        $max_line_items = 0;

        foreach ($order_ids as $order_id) {

            $order = wc_get_order($order_id);

            $line_items_count = count($order->get_items());

            if ($line_items_count >= $max_line_items) {
                $max_line_items = $line_items_count;
            }
        }

        return $max_line_items;
    }

    public static function get_orders_csv_row($order_id, $export_columns, $max_line_items) {

        $order = wc_get_order($order_id);

        $line_items = $shipping_items = $fee_items = $tax_items = $coupon_items = array();

        // get line items
        foreach ($order->get_items() as $item_id => $item) {

            $product = $order->get_product_from_item($item);

            if (!is_object($product)) {
                $product = new WC_Product(0);
            }

            $item_meta = function_exists('wc_get_order_item_meta') ? wc_get_order_item_meta($item_id, '', false) : $order->get_item_meta($item_id);


            $line_item = array(
                'name' => html_entity_decode($product->get_title() ? $product->get_title() : $item['name'], ENT_NOQUOTES, 'UTF-8'),
                'product_id' => (WC()->version < '2.7.0')?$product->id:$product->get_id(),
                'sku' => $product->get_sku(),
                'quantity' => $item['qty'],
                'total' => wc_format_decimal($order->get_line_total($item), 2),
                    //'meta' => html_entity_decode($meta, ENT_NOQUOTES, 'UTF-8'),
            );

            $meta_string = '';
            
            foreach ($item_meta as $key => $value) {
                switch ($key) {
                    case '_qty':
                    case '_variation_id':
                    case '_product_id':
                    case '_line_total':
                    case '_line_subtotal':
                    case '_tax_class':
                    case '_line_tax':
                    case '_line_tax_data':
                    case '_line_subtotal_tax':
                        continue;

                    default:
                        if(is_array($value))
                        $value = implode(',', $value);
                        $line_item[$key] = $value;
                        break;
                }
            }

            $refunded = wc_format_decimal($order->get_total_refunded_for_item($item_id), 2);
            if ($refunded != '0.00') {
                $line_item['refunded'] = $refunded;
            }

            $prod_type = $product->get_type();
            if ($prod_type === 'variable' || $prod_type === 'variation') {
                $line_item['_variation_id'] = $product->variation_id;
            }

            //add line item tax
            $line_tax_data = isset($item['line_tax_data']) ? $item['line_tax_data'] : array();
            $tax_data = maybe_unserialize($line_tax_data);
            $tax_detail = isset($tax_data['total']) ? wc_format_decimal(wc_round_tax_total(array_sum((array) $tax_data['total'])), 2) : '';
            if ($tax_detail != '0.00' && !empty($tax_detail)) {
                $line_item['tax'] = $tax_detail;
            }

            $line_items[] = $line_item;
        }

        /*
        foreach ($order->get_shipping_methods() as $_ => $shipping_item) {

            $shipping_items[] = implode('|', array(
                'method:' . $shipping_item['name'],
                'total:' . wc_format_decimal($shipping_item['cost'], 2),
            ));
        }
         * 
         */
        //shipping items is just product x qty under shipping method
        $line_items_shipping = $order->get_items( 'shipping' );
        foreach ($line_items_shipping as $item_id => $item) {
            if(is_object($item)){
            if ($meta_data = $item->get_formatted_meta_data('')) :
                foreach ($meta_data as $meta_id => $meta) :
                    if (in_array($meta->key, $line_items_shipping)) {
                        continue;
                    }
                    $shipping_items[] = implode('|', array('item:' . wp_kses_post($meta->display_key), 'value:' . $meta->display_value));
                endforeach;
            endif;
            }
        }

        //get fee and total
        $fee_total = 0;
        $fee_tax_total = 0;

        foreach ($order->get_fees() as $fee_id => $fee) {

            $fee_items[] = implode('|', array(
                'name:' . $fee['name'],
                'total:' . wc_format_decimal($fee['line_total'], 2),
                'tax:' . wc_format_decimal($fee['line_tax'], 2),
            ));

            $fee_total += $fee['line_total'];
            $fee_tax_total += $fee['line_tax'];
        }

        // get tax items
        foreach ($order->get_tax_totals() as $tax_code => $tax) {

            $tax_items[] = implode('|', array(
                'code:' . $tax_code,
                'total:' . wc_format_decimal($tax->amount, 2),
            ));
        }

        // add coupons
        foreach ($order->get_items('coupon') as $_ => $coupon_item) {

            $coupon = new WC_Coupon($coupon_item['name']);

            $coupon_post = get_post((WC()->version < '2.7.0')?$coupon->id:$coupon->get_id());
            $discount_amount = !empty($coupon_item['discount_amount']) ? $coupon_item['discount_amount'] : 0;
            $coupon_items[] = implode('|', array(
                'code:' . $coupon_item['name'],
                'description:' . ( is_object($coupon_post) ? $coupon_post->post_excerpt : '' ),
                'amount:' . wc_format_decimal($discount_amount, 2),
            ));
        }


        if (version_compare(WC_VERSION, '2.7', '<')) {

            $order_data = array(
                'order_id' => $order->id,
                'order_number' => $order->get_order_number(),
                'order_date' => $order->order_date,
                'status' => $order->get_status(),
                'shipping_total' => $order->get_total_shipping(),
                'shipping_tax_total' => wc_format_decimal($order->get_shipping_tax(), 2),
                'fee_total' => wc_format_decimal($fee_total, 2),
                'fee_tax_total' => wc_format_decimal($fee_tax_total, 2),
                'tax_total' => wc_format_decimal($order->get_total_tax(), 2),
                'cart_discount' => (defined('WC_VERSION') && (WC_VERSION >= 2.3)) ? wc_format_decimal($order->get_total_discount(), 2) : wc_format_decimal($order->get_cart_discount(), 2),
                'order_discount' => (defined('WC_VERSION') && (WC_VERSION >= 2.3)) ? wc_format_decimal($order->get_total_discount(), 2) : wc_format_decimal($order->get_order_discount(), 2),
                'discount_total' => wc_format_decimal($order->get_total_discount(), 2),
                'order_total' => wc_format_decimal($order->get_total(), 2),
                'refunded_total' => wc_format_decimal($order->get_total_refunded(), 2),
                'order_currency' => $order->get_order_currency(),
                'payment_method' => $order->payment_method,
                'shipping_method' => $order->get_shipping_method(),
                'customer_id' => $order->get_user_id(),
                'customer_user' => $order->get_user_id(),
                'billing_first_name' => $order->billing_first_name,
                'billing_last_name' => $order->billing_last_name,
                'billing_company' => $order->billing_company,
                'billing_email' => $order->billing_email,
                'billing_phone' => $order->billing_phone,
                'billing_address_1' => $order->billing_address_1,
                'billing_address_2' => $order->billing_address_2,
                'billing_postcode' => $order->billing_postcode,
                'billing_city' => $order->billing_city,
                'billing_state' => $order->billing_state,
                'billing_country' => $order->billing_country,
                'shipping_first_name' => $order->shipping_first_name,
                'shipping_last_name' => $order->shipping_last_name,
                'shipping_company' => $order->shipping_company,
                'shipping_address_1' => $order->shipping_address_1,
                'shipping_address_2' => $order->shipping_address_2,
                'shipping_postcode' => $order->shipping_postcode,
                'shipping_city' => $order->shipping_city,
                'shipping_state' => $order->shipping_state,
                'shipping_country' => $order->shipping_country,
                'customer_note' => $order->customer_note,
                'shipping_items' => implode(';', $shipping_items),
                'fee_items' => implode(';', $fee_items),
                'tax_items' => implode(';', $tax_items),
                'coupon_items' => implode(';', $coupon_items),
                'order_notes' => implode('|', WF_OrderImpExpCsv_Exporter::get_order_notes($order)),
                'download_permissions' => $order->download_permissions_granted ? $order->download_permissions_granted : 0,
            );
        } else {

            $order_data = array(
                'order_id' => $order->get_id(),
                'order_number' => $order->get_order_number(),
                'order_date' => date('Y-m-d H:i:s' ,  strtotime($order->get_date_created())),
                'status' => $order->get_status(),
                'shipping_total' => $order->get_total_shipping(),
                'shipping_tax_total' => wc_format_decimal($order->get_shipping_tax(), 2),
                'fee_total' => wc_format_decimal($fee_total, 2),
                'fee_tax_total' => wc_format_decimal($fee_tax_total, 2),
                'tax_total' => wc_format_decimal($order->get_total_tax(), 2),
                'cart_discount' => (defined('WC_VERSION') && (WC_VERSION >= 2.3)) ? wc_format_decimal($order->get_total_discount(), 2) : wc_format_decimal($order->get_cart_discount(), 2),
                'order_discount' => (defined('WC_VERSION') && (WC_VERSION >= 2.3)) ? wc_format_decimal($order->get_total_discount(), 2) : wc_format_decimal($order->get_order_discount(), 2),
                'discount_total' => wc_format_decimal($order->get_total_discount(), 2),
                'order_total' => wc_format_decimal($order->get_total(), 2),
                'refunded_total' => wc_format_decimal($order->get_total_refunded(), 2),
                'order_currency' => $order->get_currency(),
                'payment_method' => $order->get_payment_method(),
                'shipping_method' => $order->get_shipping_method(),
                'customer_id' => $order->get_user_id(),
                'customer_user' => $order->get_user_id(),
                'billing_first_name' => $order->get_billing_first_name(),
                'billing_last_name' => $order->get_billing_last_name(),
                'billing_company' => $order->get_billing_company(),
                'billing_email' => $order->get_billing_email(),
                'billing_phone' => $order->get_billing_phone(),
                'billing_address_1' => $order->get_billing_address_1(),
                'billing_address_2' => $order->get_billing_address_2(),
                'billing_postcode' => $order->get_billing_postcode(),
                'billing_city' => $order->get_billing_city(),
                'billing_state' => $order->get_billing_state(),
                'billing_country' => $order->get_billing_country(),
                'shipping_first_name' => $order->get_shipping_first_name(),
                'shipping_last_name' => $order->get_shipping_last_name(),
                'shipping_company' => $order->get_shipping_company(),
                'shipping_address_1' => $order->get_shipping_address_1(),
                'shipping_address_2' => $order->get_shipping_address_2(),
                'shipping_postcode' => $order->get_shipping_postcode(),
                'shipping_city' => $order->get_shipping_city(),
                'shipping_state' => $order->get_shipping_state(),
                'shipping_country' => $order->get_shipping_country(),
                'customer_note' => $order->get_customer_note(),
                'shipping_items' => implode(';', $shipping_items),
                'fee_items' => implode(';', $fee_items),
                'tax_items' => implode(';', $tax_items),
                'coupon_items' => implode(';', $coupon_items),
                'order_notes' => implode('|', WF_OrderImpExpCsv_Exporter::get_order_notes($order)),
                'download_permissions' => $order->is_download_permitted() ? $order->is_download_permitted() : 0,
            );
        }
        foreach ($order_data as $key => $value) {
            if (!$export_columns || in_array($key, $export_columns)) {
                // need to modify code
            } else {
                unset($order_data[$key]);
            }
        }


        $li = 1;
        foreach ($line_items as $line_item) {
            foreach ($line_item as $name => $value) {
                $line_item[$name] = $name . ':' . $value;
            }
            $line_item = implode('|', $line_item);
            $order_data["line_item_{$li}"] = $line_item;
            $li++;
        }

        for ($i = 1; $i <= $max_line_items; $i++) {
            $order_data["line_item_{$i}"] = !empty($order_data["line_item_{$i}"]) ? $order_data["line_item_{$i}"] : '';
        }
        return apply_filters('hf_alter_csv_order_data', $order_data);
    }

    public static function get_order_notes($order) {

        $callback = array('WC_Comments', 'exclude_order_comments');

        $args = array(
            'post_id' => (WC()->version < '2.7.0')?$order->id:$order->get_id(),
            'approve' => 'approve',
            'type' => 'order_note'
        );

        remove_filter('comments_clauses', $callback);
        $notes = get_comments($args);
        add_filter('comments_clauses', $callback);
        $order_notes = array();
        foreach ($notes as $note) {
            if (get_comment_meta($note->comment_ID, 'is_customer_note', '1'))
                $order_notes[] = str_replace(array("\r", "\n"), ' ', $note->comment_content . ':is_customer_note');
            else
                $order_notes[] = str_replace(array("\r", "\n"), ' ', $note->comment_content);
        }
        return $order_notes;
    }

}