<?php

if (!defined('ABSPATH')) {
    exit;
}

class OrderImpExpXML_OrderExpXMLGeneral extends XMLWriter {

    private $ids;

    public function __construct($ids) {

        $this->ids = $ids;
        $this->openMemory();
        $this->setIndent(TRUE);
        $xml_version = '1.0';
        $xml_encoding = 'UTF-8';
        //$xml_standalone = 'no';
        $this->startDocument($xml_version, $xml_encoding, $xml_standalone);
    }

    public function do_xml_export($filename, $xml) {



        global $wpdb;

        $settings = get_option( 'woocommerce_'.wf_all_imp_exp_ID.'_settings', null );
        $ftp_server = isset($settings['xml_ftp_server']) ? $settings['xml_ftp_server'] : '';
        $ftp_user = isset($settings['xml_ftp_user']) ? $settings['xml_ftp_user'] : '';
        $ftp_password = isset($settings['xml_ftp_password']) ? $settings['xml_ftp_password'] : '';
        $use_ftps = isset($settings['xml_use_ftps']) ? $settings['xml_use_ftps'] : '';
        $use_pasv = isset($settings['xml_use_pasv']) ? $settings['xml_use_pasv'] : '';
        $enable_ftp_ie = isset($settings['xml_enable_ftp_ie']) ? $settings['xml_enable_ftp_ie'] : '';


        $wpdb->hide_errors();
        @set_time_limit(0);
        if (function_exists('apache_setenv'))
            @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 0);
        @ob_clean();


        if ($enable_ftp_ie) {

            $file = $filename . "-export-" . date('Y_m_d_H_i_s', current_time('timestamp')) . ".xml";
            $fp = fopen($file, 'w');

            if ($use_ftps) {
                $ftp_conn = ftp_ssl_connect($ftp_server) or die("Could not connect to $ftp_server");
            } else {
                $ftp_conn = ftp_connect($ftp_server) or die("Could not connect to $ftp_server");
            }
            $login = ftp_login($ftp_conn, $ftp_user, $ftp_password);

            fwrite($fp, $xml);
            if($use_pasv) ftp_pasv($ftp_conn, TRUE);
            // upload file
            if (ftp_put($ftp_conn, $file, $file, FTP_ASCII)) {
                $wf_order_ie_msg = 1;
                if(function_exists('wp_redirect'))
                    wp_redirect(admin_url('/admin.php?page=wf_woocommerce_order_im_ex_xml&wf_order_ie_msg=' . $wf_order_ie_msg));
            } else {
                $wf_order_ie_msg = 2;
                if(function_exists('wp_redirect'))
                    wp_redirect(admin_url('/admin.php?page=wf_woocommerce_order_im_ex_xml&wf_order_ie_msg=' . $wf_order_ie_msg));
            }

            // close connection
            ftp_close($ftp_conn);
            fclose($fp);
            exit;
        } else {

            $charset = get_option('blog_charset');
            header(apply_filters('hf_order_import_export_xml_content_type', "Content-Type: application/xml; charset={$charset}"));
            header(sprintf('Content-Disposition: attachment; filename="%s"', $filename.".xml"));
            header('Pragma: no-cache');
            header('Expires: 0');
            if (version_compare(PHP_VERSION, '5.6', '<')) {
                iconv_set_encoding('output_encoding', $charset);
            } else {
                ini_set('default_charset', 'UTF-8');
            }

            echo $xml;
            exit;
        }
    }

    public function get_order_details_xml($data_array, $xmlns = NULL) {
        $xmlnsurl = $xmlns;
        $keys = array_keys($data_array);
        $root_tag = reset($keys);
        OrderImpExpXML_OrderExpXMLGeneral::array_to_xml($this, $root_tag, $data_array[$root_tag], $xmlnsurl);
        return $this->output_xml();
    }

    public static function array_to_xml($xml_writer, $element_key, $element_value = array(), $xmlnsurl = NULL) {

        if (!empty($xmlnsurl)) {
            $my_root_tag = $element_key;
            $xml_writer->startElementNS(null, $element_key, $xmlnsurl);
        } else {
            $my_root_tag = '';
        }

        if (is_array($element_value)) {
            //handle attributes
            if ('@attributes' === $element_key) {
                foreach ($element_value as $attribute_key => $attribute_value) {

                    $xml_writer->startAttribute($attribute_key);
                    $xml_writer->text($attribute_value);
                    $xml_writer->endAttribute();
                }
                return;
            }
            
            //handle order elements
        if (is_int($element_key)) {
            foreach ($element_value as $child_element_key => $child_element_value) {
//echo '<br>parent Element Key : '.$element_key;echo '<br>Paren Element Value : <pre>'; print_r($element_value).'<pre>'; 
                   //echo '<br>Element Key : '.$child_element_key;echo '<br>Element Value : '; print_r($child_element_value); 
                if(is_array($child_element_value)) {
                     if ($element_key !== $my_root_tag)
                    $xml_writer->startElement($child_element_key);
                
                    foreach ($child_element_value as $sibling_element_key => $sibling_element_value) {
                        self::array_to_xml($xml_writer, $sibling_element_key, $sibling_element_value);
                                          }
                    $xml_writer->endElement();
                }else{
                  self::array_to_xml($xml_writer, $child_element_key, $child_element_value);
              }

          }
      } else {

        if ($element_key !== $my_root_tag)
            $xml_writer->startElement($element_key);

        foreach ($element_value as $child_element_key => $child_element_value) {
            self::array_to_xml($xml_writer, $child_element_key, $child_element_value);
        }

        $xml_writer->endElement();
    }

} else {

            //handle single elements
    if ('@value' == $element_key) {

        $xml_writer->text($element_value);
    } else {

                //wrap element in CDATA tag if it contain illegal characters
        if (false !== strpos($element_value, '<') || false !== strpos($element_value, '>')) {

            $xml_writer->startElement($element_key);
            $xml_writer->writeCdata($element_value);
            $xml_writer->endElement();
        } else {

            $xml_writer->writeElement($element_key, $element_value);
        }
    }

    return;
}
}

private function output_xml() {
    $this->endDocument();
    return $this->outputMemory();
}

public function get_orders($order_ids) {


    $order_data = array();

    if (!class_exists('WooCommerce')) :
        require_once ABSPATH . 'wp-content/plugins/woocommerce/woocommerce.php';
    require_once ABSPATH . 'wp-content/plugins/woocommerce/includes/class-wc-order-factory.php';
    WC()->init();
    endif;

    $wc_countries = new WC_Countries();
    $base_country = $wc_countries->get_base_country();
    foreach ($order_ids as $order_id) {

        $order = wc_get_order($order_id);

        $shipping_methods = $shipping_methods_ids = array();

        foreach ($order->get_shipping_methods() as $method) {

            $shipping_methods[] = $method['name'];
            $shipping_methods_ids[] = $method['method_id'];
        }

        $fee_total = 0;
        foreach ($order->get_fees() as $fee_id => $fee) {
            $fee_total += $fee['line_total'];
        }
        if((WC()->version < '2.7.0')){
            
        $order_data[] = apply_filters('hf_order_import_export_xml_format', array(
            'OrderId' => $order->id,
            'OrderNumber' => $order->get_order_number(),
            'OrderDate' => $order->order_date,
            'OrderStatus' => $order->get_status(),
            'BillingFirstName' => $order->billing_first_name,
            'BillingLastName' => $order->billing_last_name,
            'BillingFullName' => $order->billing_first_name . ' ' . $order->billing_last_name,
            'BillingCompany' => $order->billing_company,
            'BillingAddress1' => $order->billing_address_1,
            'BillingAddress2' => $order->billing_address_2,
            'BillingCity' => $order->billing_city,
            'BillingState' => $order->billing_state,
            'BillingPostCode' => $order->billing_postcode,
            'BillingCountry' => $order->billing_country,
            'BillingPhone' => $order->billing_phone,
            'BillingEmail' => $order->billing_email,
            'ShippingFirstName' => $order->shipping_first_name,
            'ShippingLastName' => $order->shipping_last_name,
            'ShippingFullName' => $order->shipping_first_name . ' ' . $order->shipping_last_name,
            'ShippingCompany' => $order->shipping_company,
            'ShippingAddress1' => $order->shipping_address_1,
            'ShippingAddress2' => $order->shipping_address_2,
            'ShippingCity' => $order->shipping_city,
            'ShippingState' => $order->shipping_state,
            'ShippingPostCode' => $order->shipping_postcode,
            'ShippingCountry' => $order->shipping_country,
            'ShippingMethodId' => implode(',', $shipping_methods_ids),
            'ShippingMethod' => implode(', ', $shipping_methods),
            'PaymentMethodId' => $order->payment_method,
            'PaymentMethod' => $order->payment_method_title,
            'OrderDiscountTotal' => (defined('WC_VERSION') && (WC_VERSION >= 2.3)) ? $order->get_total_discount() : $order->get_order_discount(),
            'CartDiscountTotal' => (defined('WC_VERSION') && (WC_VERSION >= 2.3)) ? $order->get_total_discount() : $order->get_cart_discount(),
            'DiscountTotal' => $order->get_total_discount(),
            'ShippingTotal' => $order->get_total_shipping(),
            'ShippingTaxTotal' => $order->get_shipping_tax(),
            'OrderTotal' => $order->get_total(),
            'FeeTotal' => $fee_total,
            'TaxTotal' => $order->get_total_tax(),
            'CompletedDate' => $order->completed_date,
            'CustomerNote' => $order->customer_note,
            'CustomerId' => $order->get_user_id(),
            'OrderLineItems' => $this->get_line_items($order),
            'StoreCountry' => $base_country
            ), $order);
        }else{
            
            $order_data[] = apply_filters('hf_order_import_export_xml_format', array(
            'OrderId' => $order->get_id(),
            'OrderNumber' => $order->get_order_number(),
            'OrderDate'  => date('Y-m-d H:i:s' ,strtotime($order->get_date_created())),
            'OrderStatus' => $order->get_status(),
            'BillingFirstName' => $order->get_billing_first_name(),
            'BillingLastName' => $order->get_billing_last_name(),
            'BillingFullName' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'BillingCompany' => $order->get_billing_company(),
            'BillingAddress1' => $order->get_billing_address_1(),
            'BillingAddress2' => $order->get_billing_address_2(),
            'BillingCity' => $order->get_billing_city(),
            'BillingState' => $order->get_billing_state(),
            'BillingPostCode' => $order->get_billing_postcode(),
            'BillingCountry' => $order->get_billing_country(),
            'BillingPhone' => $order->get_billing_phone(),
            'BillingEmail' => $order->get_billing_email(),
            'ShippingFirstName' => $order->get_shipping_first_name(),
            'ShippingLastName' => $order->get_shipping_last_name(),
            'ShippingFullName' => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
            'ShippingCompany' => $order->get_shipping_company(),
            'ShippingAddress1' => $order->get_shipping_address_1(),
            'ShippingAddress2' => $order->get_shipping_address_2(),
            'ShippingCity' => $order->get_shipping_city(),
            'ShippingState' => $order->get_shipping_state(),
            'ShippingPostCode' => $order->get_shipping_postcode(),
            'ShippingCountry' => $order->get_shipping_country(),
            'ShippingMethodId' => implode(',', $shipping_methods_ids),
            'ShippingMethod' => implode(', ', $shipping_methods),
            'PaymentMethodId' => $order->get_payment_method(),
            'PaymentMethod' => $order->get_payment_method_title(),
            'OrderDiscountTotal' => (defined('WC_VERSION') && (WC_VERSION >= 2.3)) ? $order->get_total_discount() : $order->get_order_discount(),
            'CartDiscountTotal' => (defined('WC_VERSION') && (WC_VERSION >= 2.3)) ? $order->get_total_discount() : $order->get_cart_discount(),
            'DiscountTotal' => $order->get_total_discount(),
            'ShippingTotal' => $order->get_total_shipping(),
            'ShippingTaxTotal' => $order->get_shipping_tax(),
            'OrderTotal' => $order->get_total(),
            'FeeTotal' => $fee_total,
            'TaxTotal' => $order->get_total_tax(),
            'CompletedDate' => date('Y-m-d H:i:s' ,strtotime($order->get_date_completed())),
            'CustomerNote' => $order->get_customer_note(),
            'CustomerId' => $order->get_user_id(),
            'OrderLineItems' => $this->get_line_items($order),
            'StoreCountry' => $base_country
            ), $order);
        }
    }
    return $order_data;
}

private function get_line_items($order) {

    $items = array();

    $weight = 0;
    $length = 0;
    $width = 0;
    $height = 0; 
    $qty = 0;
    $weight_unit = get_option('woocommerce_weight_unit'); 

    foreach ($order->get_items() as $item_id => $item) {

        $item['id'] = $item_id;

        if (isset($item['type']) && 'line_item' !== $item['type']) {
            continue;
        }
        $product = $order->get_product_from_item($item);

        $item_meta = new WC_Order_Item_Meta((defined('WC_VERSION') && (WC_VERSION >= 2.4)) ? $item : $item['item_meta'] );
        $item_meta = $item_meta->display(true, true);

        $item_meta = preg_replace('/<[^>]*>/', ' ', $item_meta);
        $item_meta = str_replace(array("\r", "\n", "\t"), '', $item_meta);
        $item_meta = strip_tags($item_meta);


        if (!empty($product) && !$product->is_virtual()) {
            $weight += $product->get_weight() * $item['qty'];
        }

        if (!empty($product) && !$product->is_virtual()) {
            $length += (WC()->version < '2.7.0')?$product->length:$product->get_length() * $item['qty'];
        }

        if (!empty($product) && !$product->is_virtual()) {
            $height += (WC()->version < '2.7.0')?$product->height:$product->get_height() * $item['qty'];
        }

        if (!empty($product) && !$product->is_virtual()) {
            $width += (WC()->version < '2.7.0')?$product->width:$product->get_width() * $item['qty'];
        }

        $qty+=$item['qty'];

        $item_format = array();
        $item_format['SKU'] = $product ? $product->get_sku() : '';
        $item_format['ExternalID'] = $product ? ((WC()->version < '2.7.0')?$product->id:$product->get_id()) : 0;
        $item_format['Name'] = html_entity_decode($product ? $product->get_title() : $item['name'], ENT_NOQUOTES, 'UTF-8');
        $item_format['Price'] = $order->get_item_total($item);
        $item_format['Quantity'] = $item['qty'];
        $item_format['Total'] = $item['line_total'];

        if ('yes' === get_option('woocommerce_calc_taxes') && 'yes' === get_option('woocommerce_prices_include_tax')) {
            $item_format['PriceInclTax'] = $order->get_item_total($item, true);
            $item_format['LineTotalInclTax'] = $item['line_total'] + $item['line_tax'];
        }

        $item_format['Meta'] = $item_meta;

        $items[] = apply_filters('hf_order_stamps_xml_export_line_item_format', $item_format, $order, $item);
    }
    $items['total_weight']  = $weight;
    $items['total_qty']     = $qty;
    $items['weight_unit']   = $weight_unit;
    $items['total_height']  = $height;
    $items['total_width']   = $width;
    $items['total_length']  = $length;
    return $items;
}



}
